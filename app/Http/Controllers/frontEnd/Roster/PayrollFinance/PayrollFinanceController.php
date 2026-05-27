<?php

namespace App\Http\Controllers\frontEnd\Roster\PayrollFinance;

use App\Http\Controllers\Controller;
use App\Models\ScheduledShift;
use Illuminate\Http\Request;
use App\User;
use App\Models\HomeManagement\PayRate;
use App\Models\HomeManagement\PayRateType;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Services\Invoice\InvoiceService;

class PayrollFinanceController extends Controller
{
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function index()
    {
        $homeId = \Illuminate\Support\Facades\Auth::user()->home_id;

        // 1. Current Week Summary Header
        $now = \Carbon\Carbon::now();
        $startOfWeek = $now->copy()->startOfWeek();
        $endOfWeek = $now->copy()->endOfWeek();
        $weekLabel = "Week " . $now->format('W') . " - " . $now->format('F Y');
        $weekRange = $startOfWeek->format('M d') . " - " . $endOfWeek->format('M d, Y');
        $payDate = $endOfWeek->copy()->addDays(5)->format('M d, Y');

        // 2. Fetch Dashboard stats (v3: expanded to include all metrics)
        $timesheets = \App\Models\Timesheet::where('home_id', $homeId)
            ->whereIn('status', ['approved', 'processed'])
            ->with(['staff', 'category', 'shift.shiftCategory'])
            ->get();

        $totalMinutes = 0;
        $totalGross = 0;

        foreach ($timesheets as $t) {
            $date = $t->shift ? $t->shift->start_date : $t->created_at->format('Y-m-d');
            $start = \Carbon\Carbon::parse($date . ' ' . $t->clock_in);
            $end = \Carbon\Carbon::parse($date . ' ' . $t->clock_out);

            if ($end->lessThan($start)) {
                $end->addDay();
            }

            $itemMinutes = $start->diffInMinutes($end);
            $totalMinutes += $itemMinutes;

            // Rate logic (standardized)
            $categoryName = ($t->category->name ?? $t->shift->shiftCategory->name ?? 'general');
            $normalizedCategory = strtolower(trim($categoryName));
            $rate = 0;

            if ($t->shift && $t->shift->hourly_rate > 0) {
                $rate = $t->shift->hourly_rate;
            } elseif ($t->staff) {
                if ($normalizedCategory == 'general' || empty($normalizedCategory)) {
                    $rate = $t->staff->hourly_rate ?? 0;
                } else {
                    $payRateType = PayRateType::where('type_name', $categoryName)
                        ->where('home_id', $homeId)
                        ->where('is_deleted', 0)
                        ->first();

                    if ($payRateType) {
                        $payRate = PayRate::where('rate_type_id', $payRateType->id)
                            ->where('access_level_id', $t->staff->access_level)
                            ->where('home_id', $homeId)
                            ->where('is_deleted', 0)
                            ->first();
                        $rate = $payRate ? $payRate->pay_rate : ($t->staff->hourly_rate ?? 0);
                    } else {
                        $rate = $t->staff->hourly_rate ?? 0;
                    }
                }
            }
            $t->item_gross = ($itemMinutes / 60) * $rate;
            $totalGross += $t->item_gross;
        }

        $totalHours = number_format($totalMinutes / 60, 1);
        $staffCount = $timesheets->pluck('staff_id')->unique()->count();
        $totalGross = number_format($totalGross, 2);

        // 3. Pending & Invoicing
        $pendingCount = \App\Models\Timesheet::where('home_id', $homeId)->where('status', 'pending')->count();
        $weekStatus = ($timesheets->where('status', 'approved')->count() > 0) ? 'draft' : 'processed';

        $invoices = \App\Models\Invoice\Invoice::where('home_id', $homeId)->where('status', '!=', 'paid')->get();
        $outstandingAmount = number_format($invoices->sum('outstanding'), 0);
        $outstandingCount = $invoices->count();

        // 4. Recent Activity
        $recentActivity = $timesheets->where('status', 'processed')
            ->sortByDesc('updated_at')
            ->take(5)
            ->values();

        return view('frontEnd.roster.payroll_finance.index', compact(
            'totalHours',
            'staffCount',
            'pendingCount',
            'totalGross',
            'weekLabel',
            'weekRange',
            'payDate',
            'weekStatus',
            'outstandingAmount',
            'outstandingCount',
            'recentActivity'
        ));
    }
    public function payrollprocessing(Request $request)
    {
        $homeId = \Illuminate\Support\Facades\Auth::user()->home_id;

        // Fetch Approved/Processed Timesheets
        $timesheets = \App\Models\Timesheet::where('home_id', $homeId)
            ->whereIn('status', ['approved', 'processed'])
            ->with(['staff', 'category', 'shift.shiftCategory'])
            ->get()
            ->map(function ($t) use ($homeId) {
                $date = $t->shift ? $t->shift->start_date : $t->created_at->format('Y-m-d');
                $start = \Carbon\Carbon::parse($date . ' ' . $t->clock_in);
                $end = \Carbon\Carbon::parse($date . ' ' . $t->clock_out);

                if ($end->lessThan($start)) {
                    $end->addDay();
                }

                $t->duration_hours = $start->diffInMinutes($end) / 60;
                $t->week_key = $start->startOfWeek()->format('Y-m-d');
                $t->week_label = "Week " . $start->format('W') . " - " . $start->format('F Y');
                $t->week_range = $start->startOfWeek()->format('M d') . " - " . $start->endOfWeek()->format('M d, Y');

                // Get category name for rate calculation
                $categoryName = '';
                if ($t->category_id && $t->category) {
                    $categoryName = $t->category->name;
                } elseif ($t->shift_id && $t->shift && $t->shift->shiftCategory) {
                    $categoryName = $t->shift->shiftCategory->name;
                }

                $rate = 0;
                $normalizedCategory = strtolower(trim($categoryName));

                if ($t->shift && $t->shift->hourly_rate > 0) {
                    $rate = $t->shift->hourly_rate;
                } elseif ($t->staff) {
                    if ($normalizedCategory == 'general' || empty($normalizedCategory)) {
                        $rate = $t->staff->hourly_rate ?? 0;
                    } else {
                        // Try to match the category name with pay_rate_types
                        $payRateType = PayRateType::where('type_name', $categoryName)
                            ->where('home_id', $homeId)
                            ->where('is_deleted', 0)
                            ->first();

                        if ($payRateType) {
                            // Match with pay_rates using the rate_type_id and user's access level
                            $payRate = PayRate::where('rate_type_id', $payRateType->id)
                                ->where('access_level_id', $t->staff->access_level) // 'access_level' is the ID field on user table
                                ->where('home_id', $homeId)
                                ->where('is_deleted', 0)
                                ->first();

                            $rate = $payRate ? $payRate->pay_rate : ($t->staff->hourly_rate ?? 0);
                        } else {
                            // Fallback to staff's standard hourly rate if no special rate type found
                            $rate = $t->staff->hourly_rate ?? 0;
                        }
                    }
                }

                $t->gross_pay = $t->duration_hours * $rate;
                return $t;
            });

        // Fetch Unapproved Scheduled Shifts for Pending Hours
        $pendingShifts = \App\Models\ScheduledShift::where('home_id', $homeId)
            ->where('status', '!=', 'approved')
            ->get()
            ->map(function ($s) {
                $start = \Carbon\Carbon::parse($s->start_date . ' ' . $s->start_time);
                $end = \Carbon\Carbon::parse($s->start_date . ' ' . $s->end_time);
                if ($end->lessThan($start)) $end->addDay();

                $s->duration_hours = $start->diffInMinutes($end) / 60;
                $s->week_key = $start->startOfWeek()->format('Y-m-d');
                return $s;
            });

        $allWeekKeys = $timesheets->pluck('week_key')->merge($pendingShifts->pluck('week_key'))->unique();

        $payrollGroups = $allWeekKeys->map(function ($key) use ($timesheets, $pendingShifts) {
            $weekItems = $timesheets->where('week_key', $key);
            $weekPending = $pendingShifts->where('week_key', $key);

            if ($weekItems->isEmpty() && $weekPending->isEmpty()) return null;

            $ref = $weekItems->first() ?? $weekPending->first();
            // Since $pendingShifts map doesn't add labels to every item if empty labels, let's fix that
            $start = \Carbon\Carbon::parse($key);
            $label = "Week " . $start->format('W') . " - " . $start->format('F Y');
            $range = $start->startOfWeek()->format('M d') . " - " . $start->endOfWeek()->format('M d, Y');

            $hasApproved = $weekItems->where('status', 'approved')->count() > 0;
            $weekStatus = ($weekItems->count() > 0 && !$hasApproved) ? 'processed' : 'pending';

            $staffBreakdown = $weekItems->groupBy('staff_id')->map(function ($staffItems) {
                $staff = $staffItems->first()->staff;
                $hours = $staffItems->sum('duration_hours');
                $gross = $staffItems->sum('gross_pay');
                return [
                    'id'    => $staff ? $staff->id : 0,
                    'name'  => $staff ? $staff->name : 'Unknown',
                    'hours' => number_format($hours, 1),
                    'gross' => number_format($gross, 2),
                    'categories' => $staffItems->pluck('category.name')->unique()->filter()->implode(', ')
                ];
            })->values();

            return [
                'week_label' => $label,
                'week_range' => $range,
                'total_gross' => $weekItems->sum('gross_pay'),
                'total_hours' => $weekItems->sum('duration_hours'),
                'pending_hours' => $weekPending->sum('duration_hours'),
                'staff_count' => $weekItems->pluck('staff_id')->unique()->count(),
                'timesheet_count' => $weekItems->count(),
                'status' => $weekStatus,
                'pay_date' => \Carbon\Carbon::parse($key)->endOfWeek()->addDays(5)->format('l, M d, Y'),
                'week_key' => $key,
                'categories' => $weekItems->pluck('category.name')->unique()->filter()->implode(', '),
                'staff_breakdown' => $staffBreakdown
            ];
        })->filter()->sortByDesc('week_key');

        return view('frontEnd/roster/payroll_finance/payroll_processing', compact('payrollGroups'));
    }

    public function processPayrollWeek(Request $request)
    {
        $week_start = $request->week_start;
        $homeId = \Illuminate\Support\Facades\Auth::user()->home_id;

        $start = \Carbon\Carbon::parse($week_start)->startOfWeek();
        $end = \Carbon\Carbon::parse($week_start)->endOfWeek();

        $timesheets = \App\Models\Timesheet::where('home_id', $homeId)
            ->where('status', 'approved')
            ->with('shift')
            ->get();

        $processedIds = [];

        foreach ($timesheets as $t) {
            $date = $t->shift ? $t->shift->start_date : $t->created_at->format('Y-m-d');
            $shiftCarbon = \Carbon\Carbon::parse($date);

            if ($shiftCarbon->between($start, $end)) {
                $processedIds[] = $t->id;
            }
        }

        if (count($processedIds) > 0) {
            \App\Models\Timesheet::whereIn('id', $processedIds)->update(['status' => 'processed']);

            // AUTOMATED INVOICE GENERATION
            try {
                $this->invoiceService->generateInvoicesFromProcessedTimesheets($week_start, $homeId);
            } catch (\Exception $e) {
                Log::error('Automated Invoice Error: ' . $e->getMessage());
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Processed ' . count($processedIds) . ' timesheets and generated client invoices.'
        ]);
    }
    public function timesheetreconciliation(Request $request)
    {
        $users = User::getHomeActiveUsers();
        $userId = \Illuminate\Support\Facades\Auth::user()->id;
        $homeId = \Illuminate\Support\Facades\Auth::user()->home_id;
        $categories = \App\Models\ShiftCategory::orderBy('name')->get();

        $status_filter = $request->status;
        $date_filter = $request->date;
        $staff_filter = $request->staff_id;

        // Default to current week
        $startOfWeek = \Carbon\Carbon::now()->startOfWeek();
        $endOfWeek = \Carbon\Carbon::now()->endOfWeek();

        // Fetch all shifts for the home to provide a comprehensive reconciliation view
        $shiftsQuery = \App\Models\ScheduledShift::where('home_id', $homeId)
            ->with(['staff', 'shiftCategory', 'timesheet']);

        if ($date_filter) {
            $shiftsQuery->where('start_date', $date_filter);
        }

        if ($staff_filter) {
            $shiftsQuery->where('staff_id', $staff_filter);
        }

        $shifts = $shiftsQuery->get()
            ->sortByDesc('start_date')
            ->map(function ($shift) {
                $shiftStart = \Carbon\Carbon::parse($shift->start_date . ' ' . $shift->start_time);
                $shiftEnd = \Carbon\Carbon::parse($shift->start_date . ' ' . $shift->end_time);

                // If it ends the next day (e.g. 22:00 to 06:00), add a day to end_time
                if ($shiftEnd->lessThan($shiftStart)) {
                    $shiftEnd->addDay();
                }

                $shift->scheduled_duration_minutes = $shiftStart->diffInMinutes($shiftEnd);

                // Initialize duration variables
                $actualDuration = 0;
                $shift->login_activities = collect();

                if ($shift->timesheet && $shift->timesheet->clock_in && $shift->timesheet->clock_out) {
                    $checkIn = \Carbon\Carbon::parse($shift->timesheet->clock_in);
                    $checkOut = \Carbon\Carbon::parse($shift->timesheet->clock_out);

                    // Handle crossing midnight
                    if ($checkOut->lessThan($checkIn)) {
                        $checkOut->addDay();
                    }

                    $actualDuration = $checkIn->diffInMinutes($checkOut);
                } elseif ($shift->staff_id) {
                    $bufferStart = $shiftStart->copy()->subHours(2);
                    $bufferEnd = $shiftEnd->copy()->addHours(2);

                    $shift->login_activities = \App\LoginInActivity::where('user_id', $shift->staff_id)
                        ->whereBetween('check_in_time', [$bufferStart, $bufferEnd])
                        ->get();

                    if ($shift->login_activities->count() > 0) {
                        $firstCheckIn = \Carbon\Carbon::parse($shift->login_activities->min('check_in_time'));
                        $lastCheckOut = $shift->login_activities->max('check_out_time') ? \Carbon\Carbon::parse($shift->login_activities->max('check_out_time')) : null;

                        if ($lastCheckOut) {
                            $actualDuration = $firstCheckIn->diffInMinutes($lastCheckOut);
                        }
                    }
                }
                $shift->actual_duration_minutes = $actualDuration;
                $shift->variance_minutes = $actualDuration - $shift->scheduled_duration_minutes;

                // Assign reconciliation status
                $status = strtolower($shift->status);
                if ($status == 'approved') {
                    $shift->reconciliation_status = 'Approved';
                } elseif ($status == 'rejected') {
                    $shift->reconciliation_status = 'Rejected';
                } elseif (empty($shift->staff_id)) {
                    $shift->reconciliation_status = 'Unscheduled';
                } else {
                    // Rule: variance > 60 mins -> Needs Adjustment, otherwise Matched
                    if (abs($shift->variance_minutes) <= 60) {
                        $shift->reconciliation_status = 'Matched';
                    } else {
                        $shift->reconciliation_status = 'Needs Adjustment';
                    }
                }

                return $shift;
            });

        // Filter manual records by date and staff if filters are set
        $manualQuery = \App\Models\Timesheet::whereNull('shift_id')->where('home_id', $homeId);

        if ($date_filter) {
            $manualQuery->whereDate('created_at', $date_filter);
        }
        if ($staff_filter) {
            $manualQuery->where('staff_id', $staff_filter);
        }

        $manual_timesheets = $manualQuery->whereBetween('created_at', [$startOfWeek, $endOfWeek])->get();

        // Calculate aggregate counts for the TOP SUMMARY CARDS (Full set for current filters)
        $matchedCount = $shifts->where('reconciliation_status', 'Matched')->count();
        $needsAdjustmentCount = $shifts->where('reconciliation_status', 'Needs Adjustment')->count();
        $unscheduledCount = $shifts->where('reconciliation_status', 'Unscheduled')->count();
        $approvedCount = $shifts->where('reconciliation_status', 'Approved')->count() + $manual_timesheets->count(); // Already filtered manual
        $rejectedCount = $shifts->where('reconciliation_status', 'Rejected')->count();

        // Apply Status Filter for final display
        if ($status_filter) {
            $shifts = $shifts->where('reconciliation_status', $status_filter);

            // If status filter is NOT 'Approved', hide manual records from the view
            if ($status_filter !== 'Approved') {
                $manual_timesheets = collect();
            }

            // Recalculate section counts to reflect what is actually visible
            $matchedCount = $shifts->where('reconciliation_status', 'Matched')->count();
            $needsAdjustmentCount = $shifts->where('reconciliation_status', 'Needs Adjustment')->count();
            $unscheduledCount = $shifts->where('reconciliation_status', 'Unscheduled')->count();
            $approvedCount = $shifts->where('reconciliation_status', 'Approved')->count() + $manual_timesheets->count();
            $rejectedCount = $shifts->where('reconciliation_status', 'Rejected')->count();
        }

        $shift_options = $shifts->values()->map(function ($s) {
            return [
                'id' => $s->id,
                'staff_id' => (int)$s->staff_id,
                'date' => \Carbon\Carbon::parse($s->start_date)->format('D, M d'),
                'time' => $s->start_time . ' - ' . $s->end_time,
                'category' => $s->shiftCategory ? $s->shiftCategory->name : 'No Category'
            ];
        });

        // 5. Fetch Unscheduled Logs (shift_id = 0)
        $unscheduledLogsQuery = \App\LoginInActivity::where('home_id', $homeId)
            ->where('shift_id', 0)
            ->where('is_deleted', 0)
            ->with('user');

        if ($date_filter) {
            $unscheduledLogsQuery->whereDate('check_in_time', $date_filter);
        }
        if ($staff_filter) {
            $unscheduledLogsQuery->where('user_id', $staff_filter);
        }

        $unscheduled_activities = $unscheduledLogsQuery->get();

        $unscheduled_logs = $unscheduled_activities->groupBy(function ($log) {
            return $log->user_id . '_' . \Carbon\Carbon::parse($log->check_in_time)->format('Y-m-d');
        })->map(function ($group) {
            $firstLog = $group->first();
            $totalMinutes = 0;
            foreach ($group as $log) {
                if ($log->check_in_time && $log->check_out_time) {
                    $in = \Carbon\Carbon::parse($log->check_in_time);
                    $out = \Carbon\Carbon::parse($log->check_out_time);
                    if ($out->lessThan($in)) $out->addDay();
                    $totalMinutes += $in->diffInMinutes($out);
                }
            }

            return (object)[
                'id' => 'log_' . $firstLog->user_id . '_' . \Carbon\Carbon::parse($firstLog->check_in_time)->format('Y-m-d'),
                'staff_id' => $firstLog->user_id,
                'staff' => $firstLog->user,
                'start_date' => \Carbon\Carbon::parse($firstLog->check_in_time)->format('Y-m-d'),
                'actual_duration_minutes' => $totalMinutes,
                'scheduled_duration_minutes' => 0,
                'variance_minutes' => $totalMinutes,
                'reconciliation_status' => 'Unscheduled',
                'login_activities' => $group,
                'is_unscheduled_log' => true
            ];
        })->values();

        // Update counts
        $unscheduledCount += $unscheduled_logs->count();

        return view('frontEnd/roster/payroll_finance/timesheetreconciliation', compact(
            'shifts',
            'matchedCount',
            'needsAdjustmentCount',
            'unscheduledCount',
            'approvedCount',
            'rejectedCount',
            'users',
            'shift_options',
            'categories',
            'manual_timesheets',
            'unscheduled_logs'
        ));
    }

    public function saveTimesheet(Request $request)
    {
        // Require either shift_id OR staff_id
        if (!$request->shift_id && !$request->staff_id) {
            return back()->with('error', 'Please select either a staff member or a shift.');
        }

        $data = [
            'staff_id'    => $request->staff_id,
            'category_id' => $request->category_id,
            'home_id'     => \Illuminate\Support\Facades\Auth::user()->home_id,
            'clock_in'    => $request->clock_in,
            'clock_out'   => $request->clock_out,
            'notes'       => $request->notes,
            'status'      => 'approved'
        ];

        if ($request->timesheet_id) {
            \App\Models\Timesheet::where('id', $request->timesheet_id)->update($data);
        } elseif ($request->shift_id) {
            $data['shift_id'] = $request->shift_id;

            // If shift_id is provided, ensure staff_id is set from the shift if missing
            $shift = \App\Models\ScheduledShift::find($request->shift_id);
            if ($shift) {
                if (!$request->staff_id) $data['staff_id'] = $shift->staff_id;
                if (!$request->category_id) $data['category_id'] = $shift->shift_category_id;
            }

            \App\Models\Timesheet::updateOrCreate(
                ['shift_id' => $request->shift_id],
                $data
            );

            // Also update the shift status to approved
            if ($shift) {
                $shift->status = 'approved';
                $shift->save();
            }
        } else {
            // Purely manual entry without shift association
            \App\Models\Timesheet::create($data);
        }

        return back()->with('success', 'Timesheet record saved successfully.');
    }

    public function approveShift(Request $request)
    {
        try {
            $shiftIds = $request->shift_id;

            if (empty($shiftIds)) {
                return response()->json(['success' => false, 'message' => 'No shifts provided.'], 400);
            }

            if (!is_array($shiftIds)) {
                $shiftIds = [$shiftIds];
            }

            $approvedCount = 0;
            foreach ($shiftIds as $shiftId) {
                $shift = \App\Models\ScheduledShift::find($shiftId);

                if (!$shift) continue;

                // Fetch actual clock times from activities
                $clockIn = null;
                $clockOut = null;

                if ($shift->staff_id) {
                    // Buffer times same as in timesheetreconciliation method
                    $shiftStart = \Carbon\Carbon::parse($shift->start_date . ' ' . $shift->start_time);
                    $shiftEnd = \Carbon\Carbon::parse($shift->start_date . ' ' . $shift->end_time);
                    if ($shiftEnd->lessThan($shiftStart)) $shiftEnd->addDay();

                    $bufferStart = $shiftStart->copy()->subHours(2);
                    $bufferEnd = $shiftEnd->copy()->addHours(2);

                    $activities = \App\LoginInActivity::where('user_id', $shift->staff_id)
                        ->whereBetween('check_in_time', [$bufferStart, $bufferEnd])
                        ->get();

                    if ($activities->count() > 0) {
                        $clockIn = \Carbon\Carbon::parse($activities->min('check_in_time'))->format('H:i');
                        $maxOut = $activities->max('check_out_time');
                        $clockOut = $maxOut ? \Carbon\Carbon::parse($maxOut)->format('H:i') : null;
                    }
                }

                // If no clock out but we're approving, default to planned if actual is missing.
                $finalClockIn = $clockIn ?? $shift->start_time;
                $finalClockOut = $clockOut ?? $shift->end_time;

                $data = [
                    'staff_id'    => $shift->staff_id,
                    'category_id' => $shift->shift_category_id,
                    'home_id'     => $shift->home_id,
                    'clock_in'    => $finalClockIn,
                    'clock_out'   => $finalClockOut,
                    'status'      => 'approved',
                    'shift_id'    => $shift->id,
                    'notes'       => 'Automatically approved from reconciliation dashboard.'
                ];

                \App\Models\Timesheet::updateOrCreate(
                    ['shift_id' => $shift->id],
                    $data
                );

                $shift->status = 'approved';
                $shift->save();
                $approvedCount++;
            }

            if ($approvedCount === 0) {
                return response()->json(['success' => false, 'message' => 'No matching shifts found to approve.']);
            }

            return response()->json([
                'success' => true,
                'message' => $approvedCount > 1 ? "$approvedCount shifts approved successfully." : "Shift approved successfully."
            ]);
        } catch (\Exception $e) {
            Log::error('Approve Shift Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function approveUnscheduledLog(Request $request)
    {
        try {
            $userId = $request->staff_id;
            $date = $request->date;
            $homeId = \Illuminate\Support\Facades\Auth::user()->home_id;

            if (!$userId || !$date) {
                return response()->json(['success' => false, 'message' => 'Missing required data.'], 400);
            }

            // Fetch logs to get clock times
            $logs = \App\LoginInActivity::where('user_id', $userId)
                ->whereDate('check_in_time', $date)
                ->where('shift_id', 0)
                ->where('is_deleted', 0)
                ->get();

            if ($logs->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No unscheduled logs found for this user/date.'], 404);
            }

            $firstIn = \Carbon\Carbon::parse($logs->min('check_in_time'))->format('H:i');
            $maxOut = $logs->max('check_out_time');
            $lastOut = $maxOut ? \Carbon\Carbon::parse($maxOut)->format('H:i') : null;

            // Create a pseudo shift to maintain consistency in timesheet reconciliation
            $shift = \App\Models\ScheduledShift::create([
                'staff_id' => $userId,
                'home_id' => $homeId,
                'start_date' => $date,
                'start_time' => $firstIn,
                'end_time' => $lastOut ?? $firstIn,
                'status' => 'approved',
                'assignment' => 'Location',
                'care_type_id' => 1,
                'shift_category_id' => $request->category_id ?? 1,
            ]);

            // Create Timesheet record
            \App\Models\Timesheet::create([
                'staff_id'    => $userId,
                'home_id'     => $homeId,
                'clock_in'    => $firstIn,
                'clock_out'   => $lastOut ?? $firstIn,
                'status'      => 'approved',
                'notes'       => 'Approved from unscheduled logs dashboard.',
                'shift_id'    => $shift->id,
                'category_id' => $request->category_id ?? 1
            ]);

            // Update login activities with this new shift ID
            \App\LoginInActivity::where('user_id', $userId)
                ->whereDate('check_in_time', $date)
                ->where('shift_id', 0)
                ->update(['shift_id' => $shift->id]);

            return response()->json([
                'success' => true,
                'message' => 'Unscheduled work approved and added to payroll.'
            ]);
        } catch (\Exception $e) {
            Log::error('Approve Unscheduled Log Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
    public function downloadReport(Request $request, $week_key)
    {
        $homeId = \Illuminate\Support\Facades\Auth::user()->home_id;
        $timesheets = \App\Models\Timesheet::where('home_id', $homeId)
            ->where('status', 'processed')
            ->whereHas('shift', function ($query) use ($week_key) {
                $query->whereBetween('start_date', [
                    \Carbon\Carbon::parse($week_key)->startOfWeek(),
                    \Carbon\Carbon::parse($week_key)->endOfWeek()
                ]);
            })
            ->with(['staff', 'category', 'shift.shiftCategory'])
            ->get()
            ->map(function ($t) {
                $date = $t->shift ? $t->shift->start_date : $t->created_at->format('Y-m-d');
                $start = \Carbon\Carbon::parse($date . ' ' . $t->clock_in);
                $end = \Carbon\Carbon::parse($date . ' ' . $t->clock_out);
                if ($end->lessThan($start)) $end->addDay();
                $t->duration_hours = $start->diffInMinutes($end) / 60;

                $rate = 0;
                if ($t->shift && $t->shift->hourly_rate > 0) {
                    $rate = $t->shift->hourly_rate;
                } else {
                    $rate = $t->staff->hourly_rate ?? 0;
                }
                $t->gross_pay = $t->duration_hours * $rate;
                return $t;
            });

        if ($timesheets->isEmpty()) {
            return back()->with('error', 'No processed timesheets found for this week.');
        }

        $start = \Carbon\Carbon::parse($week_key);
        $group = [
            'week_label' => "Week " . $start->format('W') . " - " . $start->format('F Y'),
            'week_range' => $start->startOfWeek()->format('M d') . " - " . $start->endOfWeek()->format('M d, Y'),
            'week_key' => $week_key,
            'total_gross' => $timesheets->sum('gross_pay'),
            'pay_date' => $start->endOfWeek()->addDays(5)->format('l, M d, Y'),
            'home_name' => \App\Home::getHomeById($homeId),
            'generated_by' => \Illuminate\Support\Facades\Auth::user()->name ?? 'Administrator',
            'staff_breakdown' => $timesheets->groupBy('staff_id')->map(function ($items) {
                $staff = $items->first()->staff;
                return [
                    'name'  => $staff ? $staff->name : 'Unknown',
                    'hours' => number_format($items->sum('duration_hours'), 1),
                    'gross' => number_format($items->sum('gross_pay'), 2)
                ];
            })->values()
        ];

        // Passing a flag to ensure the download template is rendered without buttons
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('frontEnd.roster.payroll_finance.payroll_report', compact('group'));
        return $pdf->download('Payroll-Report-' . $week_key . '.pdf');
    }

    public function weeklyReport(Request $request, $week_key)
    {
        $homeId = \Illuminate\Support\Facades\Auth::user()->home_id;

        // This logic is copied from payrollprocessing and filtered for one week
        $timesheets = \App\Models\Timesheet::where('home_id', $homeId)
            ->where('status', 'processed')
            ->whereHas('shift', function ($query) use ($week_key) {
                $query->whereBetween('start_date', [
                    \Carbon\Carbon::parse($week_key)->startOfWeek(),
                    \Carbon\Carbon::parse($week_key)->endOfWeek()
                ]);
            })
            ->with(['staff', 'category', 'shift.shiftCategory'])
            ->get()
            ->map(function ($t) {
                $date = $t->shift ? $t->shift->start_date : $t->created_at->format('Y-m-d');
                $start = \Carbon\Carbon::parse($date . ' ' . $t->clock_in);
                $end = \Carbon\Carbon::parse($date . ' ' . $t->clock_out);
                if ($end->lessThan($start)) $end->addDay();

                $t->duration_hours = $start->diffInMinutes($end) / 60;

                // Rate logic
                $rate = 0;
                if ($t->shift && $t->shift->hourly_rate > 0) {
                    $rate = $t->shift->hourly_rate;
                } else {
                    $rate = $t->staff->hourly_rate ?? 0;
                }
                $t->gross_pay = $t->duration_hours * $rate;
                return $t;
            });

        if ($timesheets->isEmpty()) {
            return back()->with('error', 'No processed timesheets found for this week.');
        }

        $start = \Carbon\Carbon::parse($week_key);
        $group = [
            'week_label' => "Week " . $start->format('W') . " - " . $start->format('F Y'),
            'week_range' => $start->startOfWeek()->format('M d') . " - " . $start->endOfWeek()->format('M d, Y'),
            'week_key' => $week_key,
            'total_gross' => $timesheets->sum('gross_pay'),
            'pay_date' => $start->endOfWeek()->addDays(5)->format('l, M d, Y'),
            'home_name' => \App\Home::getHomeById($homeId),
            'generated_by' => \Illuminate\Support\Facades\Auth::user()->name ?? 'Administrator',
            'staff_breakdown' => $timesheets->groupBy('staff_id')->map(function ($items) {
                $staff = $items->first()->staff;
                return [
                    'name'  => $staff ? $staff->name : 'Unknown',
                    'hours' => number_format($items->sum('duration_hours'), 1),
                    'gross' => number_format($items->sum('gross_pay'), 2)
                ];
            })->values()
        ];

        return view('frontEnd.roster.payroll_finance.payroll_report', compact('group'));
    }

    public function staffPayslip($staff_id, $week_key)
    {
        // This is for the API webview requirement. 
        // It fetches a single user's data for a specific week.
        $timesheets = \App\Models\Timesheet::where('staff_id', $staff_id)
            ->where('status', 'processed')
            ->whereHas('shift', function ($query) use ($week_key) {
                $query->whereBetween('start_date', [
                    \Carbon\Carbon::parse($week_key)->startOfWeek(),
                    \Carbon\Carbon::parse($week_key)->endOfWeek()
                ]);
            })
            ->with(['staff', 'category', 'shift.shiftCategory'])
            ->get()
            ->map(function ($t) {
                $date = $t->shift ? $t->shift->start_date : $t->created_at->format('Y-m-d');
                $start = \Carbon\Carbon::parse($date . ' ' . $t->clock_in);
                $end = \Carbon\Carbon::parse($date . ' ' . $t->clock_out);
                if ($end->lessThan($start)) $end->addDay();
                $t->duration_hours = $start->diffInMinutes($end) / 60;

                $rate = 0;
                if ($t->shift && $t->shift->hourly_rate > 0) {
                    $rate = $t->shift->hourly_rate;
                } else {
                    $rate = $t->staff->hourly_rate ?? 0;
                }
                $t->gross_pay = $t->duration_hours * $rate;
                return $t;
            });

        if ($timesheets->isEmpty()) abort(404, 'Payslip not found.');

        $start = \Carbon\Carbon::parse($week_key);
        $group = [
            'week_label' => "Week " . $start->format('W') . " - " . $start->format('F Y'),
            'week_range' => $start->startOfWeek()->format('M d') . " - " . $start->endOfWeek()->format('M d, Y'),
            'week_key' => $week_key,
            'total_gross' => $timesheets->sum('gross_pay'),
            'pay_date' => $start->endOfWeek()->addDays(5)->format('l, M d, Y'),
            'home_name' => \App\Home::getHomeById($timesheets->first()->staff->home_id ?? null),
            'generated_by' => $timesheets->first()->staff->name ?? 'Staff Member',
            'staff_breakdown' => [
                [
                    'name'  => $timesheets->first()->staff->name ?? 'Unknown',
                    'hours' => number_format($timesheets->sum('duration_hours'), 1),
                    'gross' => number_format($timesheets->sum('gross_pay'), 2)
                ]
            ]
        ];

        return view('frontEnd.roster.payroll_finance.payroll_report', compact('group'));
    }

    public function downloadPayslip($staff_id, $week_key)
    {
        $timesheets = \App\Models\Timesheet::where('staff_id', $staff_id)
            ->where('status', 'processed')
            ->whereHas('shift', function ($query) use ($week_key) {
                $query->whereBetween('start_date', [
                    \Carbon\Carbon::parse($week_key)->startOfWeek(),
                    \Carbon\Carbon::parse($week_key)->endOfWeek()
                ]);
            })
            ->with(['staff', 'category', 'shift.shiftCategory'])
            ->get()
            ->map(function ($t) {
                $date = $t->shift ? $t->shift->start_date : $t->created_at->format('Y-m-d');
                $start = \Carbon\Carbon::parse($date . ' ' . $t->clock_in);
                $end = \Carbon\Carbon::parse($date . ' ' . $t->clock_out);
                if ($end->lessThan($start)) $end->addDay();
                $t->duration_hours = $start->diffInMinutes($end) / 60;

                $rate = 0;
                if ($t->shift && $t->shift->hourly_rate > 0) {
                    $rate = $t->shift->hourly_rate;
                } else {
                    $rate = $t->staff->hourly_rate ?? 0;
                }
                $t->gross_pay = $t->duration_hours * $rate;
                return $t;
            });

        if ($timesheets->isEmpty()) abort(404, 'Payslip not found.');

        $start = \Carbon\Carbon::parse($week_key);
        $group = [
            'week_label' => "Week " . $start->format('W') . " - " . $start->format('F Y'),
            'week_range' => $start->startOfWeek()->format('M d') . " - " . $start->endOfWeek()->format('M d, Y'),
            'week_key' => $week_key,
            'total_gross' => $timesheets->sum('gross_pay'),
            'pay_date' => $start->endOfWeek()->addDays(5)->format('l, M d, Y'),
            'home_name' => \App\Home::getHomeById($timesheets->first()->staff->home_id ?? null),
            'generated_by' => $timesheets->first()->staff->name ?? 'Staff Member',
            'staff_breakdown' => [
                [
                    'name'  => $timesheets->first()->staff->name ?? 'Unknown',
                    'hours' => number_format($timesheets->sum('duration_hours'), 1),
                    'gross' => number_format($timesheets->sum('gross_pay'), 2)
                ]
            ]
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('frontEnd.roster.payroll_finance.payroll_report', compact('group'));
        return $pdf->download('Payslip-' . $week_key . '.pdf');
    }
}
