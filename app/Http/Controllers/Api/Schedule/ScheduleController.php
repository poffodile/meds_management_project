<?php

namespace App\Http\Controllers\Api\Schedule;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ScheduledShift;

class ScheduleController extends Controller
{
    public function schedule_shifts(Request $request)
    {
        // Validate staff_id – must be present and correspond to an existing staff user
        $validated = $request->validate([
            'staff_id' => ['required', 'integer', 'exists:user,id'],
            'date' => ['required', 'date'],
        ]);

        $staffId = $validated['staff_id'];

        $shifts = ScheduledShift::where('staff_id', $staffId)
            ->select('id', 'start_date', 'start_time', 'end_time', 'shift_type', 'notes', 'tasks')
            ->where('start_date', $validated['date'])
            ->orderBy('start_time')
            ->get();
        $data = $shifts->map(function ($shift) {
            $shift->day = \Carbon\Carbon::parse($shift->start_date)->format('l');
            $shift->start_time = \Carbon\Carbon::parse($shift->start_time)->format('H:i');
            $shift->end_time   = \Carbon\Carbon::parse($shift->end_time)->format('H:i');
            return $shift;
        });

        return response()->json([
            'success' => true,
            'message' => 'Shifts retrieved successfully.',
            'data' => $data,
            'total_shifts' => $data->count(),
        ]);
    }

    public function schedule_shifts_details(Request $request)
    {
        $validated = $request->validate([
            'shift_id' => ['required', 'integer', 'exists:scheduled_shifts,id'],
        ]);

        $shiftId = $validated['shift_id'];

        $shifts = ScheduledShift::with(['recurrence', 'assessments', 'documents'])
            ->where('id', $shiftId)
            ->get();

        $data = $shifts->map(function ($shift) {
            $shift->day = \Carbon\Carbon::parse($shift->start_date)->format('l');
            $shift->start_time = \Carbon\Carbon::parse($shift->start_time)->format('H:i');
            $shift->end_time   = \Carbon\Carbon::parse($shift->end_time)->format('H:i');
            $shift->current_date = date('Y-m-d');

            if ($shift->assessments) {
                foreach ($shift->assessments as $assessment) {
                    $assessment->assessment_doc_url = $assessment->assessment_doc ? url('public/' . $assessment->assessment_doc) : '';
                }
            }

            if ($shift->documents) {
                foreach ($shift->documents as $document) {
                    $document->doc_file_url = $document->doc_file ? url('public/' . $document->doc_file) : '';
                    $document->fileType = 'Document';
                    $document->form_url = '';
                    if ($document->form_id) {
                        $document->fileType = 'Form';
                        $document->form_url = url('roster/schedule-shift/form_template/view/' . $document->id);
                    }
                }
            }

            return $shift;
        });

        return response()->json([
            'success' => true,
            'message' => 'Shift details retrieved successfully.',
            'data' => $data->first(),
            'current_date' => date('Y-m-d'),
        ]);
    }

    public function schedule_shifts_update_status(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'shift_id' => ['required', 'integer', 'exists:scheduled_shifts,id'],
            'status' => ['required', 'string', 'in:completed,in_progress'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 200);
        }

        $validated = $validator->validated();
        $shiftId = $validated['shift_id'];
        $status = $validated['status'];

        try {
            $updated = ScheduledShift::where('id', $shiftId)
                ->update(['status' => $status]);

            if ($updated) {
                return response()->json([
                    'success' => true,
                    'message' => 'Shift status updated successfully.',
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Shift status was not updated.',
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating shift: ' . $e->getMessage(),
            ], 200);
        }
    }
    public function get_unassigned_shifts(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'home_id' => ['required', 'integer', 'exists:home,id'],
            'user_id' => ['nullable', 'integer', 'exists:user,id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 200);
        }

        $validated = $validator->validated();
        $homeId = $validated['home_id'];
        $userId = $validated['user_id'] ?? null;

        $shiftsQuery = ScheduledShift::whereNull('staff_id')
            ->where('home_id', $homeId)
            ->select('id', 'start_date', 'start_time', 'end_time', 'shift_type', 'notes', 'tasks')
            ->orderBy('start_date', 'desc')
            ->orderBy('start_time', 'desc');

        $shifts = $shiftsQuery->get();

        if ($shifts->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'No unassigned shifts found.',
                'data'    => [],
                'total_shifts' => 0,
            ], 200);
        }

        // Filter out shifts where the user is already assigned to another shift at the same time
        if ($userId) {
            $shifts = $shifts->filter(function ($unassignedShift) use ($userId) {
                $overlap = ScheduledShift::where('staff_id', $userId)
                    ->where('start_date', $unassignedShift->start_date)
                    ->where(function ($q) use ($unassignedShift) {
                        $q->whereTime('start_time', '<', $unassignedShift->end_time)
                            ->whereTime('end_time', '>', $unassignedShift->start_time);
                    })
                    ->exists();

                return !$overlap; // Keep the shift ONLY if there is no overlap
            });
        }

        $data = $shifts->values()->map(function ($shift) {
            $shift->day = \Carbon\Carbon::parse($shift->start_date)->format('l');
            $shift->start_time = \Carbon\Carbon::parse($shift->start_time)->format('H:i');
            $shift->end_time   = \Carbon\Carbon::parse($shift->end_time)->format('H:i');
            $shift->client_name = $shift->client->name ?? 'N/A';
            return $shift;
        });

        return response()->json([
            'success' => true,
            'message' => 'Unassigned shifts retrieved successfully.',
            'data' => $data,
            'total_shifts' => $data->count(),
        ]);
    }

    public function assignShift(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'shift_id' => ['required', 'integer', 'exists:scheduled_shifts,id'],
            'staff_id' => ['nullable', 'integer', 'exists:user,id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 200);
        }

        $shift = ScheduledShift::find($request->shift_id);

        if ($request->staff_id) {
            $formattedStart = date('H:i:s', strtotime($shift->start_time));
            $formattedEnd = date('H:i:s', strtotime($shift->end_time));

            // 1. Double Booking Check (against other shifts)
            $overlap = ScheduledShift::where('staff_id', $request->staff_id)
                ->where('start_date', $shift->start_date)
                ->where('id', '!=', $shift->id)
                ->where(function ($q) use ($formattedStart, $formattedEnd) {
                    $q->whereTime('start_time', '<', $formattedEnd)
                        ->whereTime('end_time', '>', $formattedStart);
                })
                ->exists();

            if ($overlap) {
                return response()->json([
                    'success' => false,
                    'message' => 'This carer is already assigned to another shift at this time.',
                ], 200);
            }

            // 2. Complete Availability Check (against unavailability table)
            $carerUnavail = \App\Models\ClientCareUnavailableDate::where('carer_id', $request->staff_id)
                ->where('start_date', '<=', $shift->start_date)
                ->where('end_date', '>=', $shift->start_date)
                ->exists();
            if ($carerUnavail) {
                return response()->json([
                    'success' => false,
                    'message' => 'The selected carer is marked as unavailable on this date.',
                ], 200);
            }

            // --- ADDED: Regular Availability Check ---
            $availCheck = $this->checkStaffWorkingHours($request->staff_id, $shift->start_date, $shift->start_time, $shift->end_time);
            if ($availCheck !== true) {
                return response()->json([
                    'success' => false,
                    'message' => $availCheck,
                ], 200);
            }

            // Check Client Unavailability
            if ($shift->service_user_id) {
                $clientUnavail = \App\Models\ClientCareUnavailableDate::where('service_user_id', $shift->service_user_id)
                    ->where('start_date', '<=', $shift->start_date)
                    ->where('end_date', '>=', $shift->start_date)
                    ->exists();
                if ($clientUnavail) {
                    return response()->json([
                        'success' => false,
                        'message' => 'The client is marked as unavailable on the selected date/time.',
                    ], 200);
                }
            }
        }

        $shift->staff_id = $request->staff_id;
        $shift->status   = $request->staff_id ? 'assigned' : 'unfilled';
        $shift->save();

        return response()->json([
            'success' => true,
            'message' => 'Shift assigned successfully.',
        ], 200);
    }

    /**
     * Helper to check if a staff member is available according to their regular schedule or specific dates.
     */
    private function checkStaffWorkingHours($staffId, $startDate, $startTime, $endTime)
    {
        $shiftStart = \Carbon\Carbon::parse($startDate . ' ' . $startTime);
        $shiftEnd = \Carbon\Carbon::parse($startDate . ' ' . $endTime);
        $dayOfWeek = $shiftStart->format('l');

        // 1. Check Specific Dates (Overrides/Supplements)
        $specific = \App\Models\ClientCareScheduleDate::where('carer_id', $staffId)
            ->whereDate('start_date', $startDate)
            ->first();

        if ($specific) {
            if ($specific->is_working == 0) {
                return "The carer is marked as not working on this specific date ($startDate).";
            }
            $specStart = \Carbon\Carbon::parse($specific->start_date);
            $specEnd = \Carbon\Carbon::parse($specific->end_date);
            if ($shiftStart < $specStart || $shiftEnd > $specEnd) {
                return "Shift is outside the carer's defined working hours for this specific date (" . $specStart->format('H:i') . " - " . $specEnd->format('H:i') . ").";
            }
            return true;
        }

        // 2. Check Weekly Schedule (including alternate weeks)
        $firstOfMonth = $shiftStart->copy()->firstOfMonth();
        $weekOfMonth = (int)ceil(($shiftStart->day + $firstOfMonth->dayOfWeek) / 7);
        $isOddWeek = ($weekOfMonth % 2 !== 0);

        $schedules = \App\Models\ClientCareScheduleDay::where('carer_id', $staffId)
            ->where('day', strtolower($dayOfWeek))
            ->where('is_working', 1)
            ->get();

        if ($schedules->isEmpty()) {
            return "The carer has no regular schedule defined for $dayOfWeek.";
        }

        $fits = false;
        $activeScheduleFound = false;

        foreach ($schedules as $sched) {
            // Handle standard vs alternate
            if ($sched->type === 'alternate') {
                $isTargetWeek = ($sched->week_number == 1 && $isOddWeek) || ($sched->week_number == 2 && !$isOddWeek);
                if (!$isTargetWeek) {
                    continue;
                }
            }

            $activeScheduleFound = true;
            $schedStart = \Carbon\Carbon::parse($startDate . ' ' . $sched->start_time);
            $schedEnd = \Carbon\Carbon::parse($startDate . ' ' . $sched->end_time);

            if ($shiftStart >= $schedStart && $shiftEnd <= $schedEnd) {
                $fits = true;
                break;
            }
        }

        if (!$activeScheduleFound) {
            return "The carer is not scheduled to work on this specific " . ($isOddWeek ? "odd" : "even") . " week ($dayOfWeek).";
        }

        if (!$fits) {
            $ranges = $schedules->filter(function ($s) use ($isOddWeek) {
                if ($s->type === 'alternate') {
                    return ($s->week_number == 1 && $isOddWeek) || ($s->week_number == 2 && !$isOddWeek);
                }
                return true;
            })->map(fn($s) => substr($s->start_time, 0, 5) . "-" . substr($s->end_time, 0, 5))->implode(', ');

            return "Shift is outside the carer's scheduled hours for this $dayOfWeek ($ranges).";
        }

        return true;
    }
}
