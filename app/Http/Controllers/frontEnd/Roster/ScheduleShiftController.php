<?php

namespace App\Http\Controllers\frontEnd\Roster;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CompanyDepartment;
use App\Services\ServiceUser\ServiceUserServices;
use App\DynamicFormBuilder;
use App\Models\ScheduledShift;
use App\Models\ShiftRecurrence;
use App\Models\ShiftAssessment;
use App\Models\ShiftDocument;
use App\Models\ShiftCategory;
use Carbon\Carbon;
use App\User;
use App\Models\HomeArea;
use App\Services\Staff\StaffTaskService;
use Illuminate\Support\Facades\Validator;

class ScheduleShiftController extends Controller
{
    protected $serviceUserService;
    protected $stafftask;

    public function __construct(ServiceUserServices $serviceUserService, StaffTaskService $stafftask)
    {
        $this->serviceUserService = $serviceUserService;
        $this->stafftask = $stafftask;
    }

    public function index()
    {
        $data['company_department'] = CompanyDepartment::getActiveCompanyDepartment();
        $data['service_users'] = $this->serviceUserService->getAllserviceUser();
        $data['dynamic_form_builder'] = DynamicFormBuilder::getFormList();
        $data['scheduled_shifts_by_group'] = ScheduledShift::with(['client', 'staff'])
            ->where('home_id', Auth::user()->home_id)
            ->orderBy('start_date', 'desc')
            ->get();

        $data['scheduled_shifts'] = ScheduledShift::with(['client', 'staff'])
            ->where('home_id', Auth::user()->home_id)
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(function ($shift) {
                $shift->client_name = $shift->client->name ?? 'Unknown Client';
                $shift->staff_name = $shift->staff->name ?? 'Unassigned';
                return $shift;
            });
        $data['home_title'] = \App\Home::getHomeById(Auth::user()->home_id);
        $data['home_areas'] = HomeArea::where('home_id', Auth::user()->home_id)->where('is_deleted', 0)->get();

        $data['shift_categories'] = ShiftCategory::where('home_id', Auth::user()->home_id)->where('is_deleted', 0)->get();

        return view('frontEnd.roster.schedule.schedule_shift', $data);
    }

    public function store(Request $request)
    {
        // Basic validation
        $request->validate([
            'client_id'  => 'nullable',
            'start_date' => 'required',
            'carer_id'   => 'nullable',
            'start_time' => 'required',
            'end_time'   => 'required|after:start_time',
        ]);

        // 1. Double Booking Check (against other shifts)
        if ($request->carer_id) {
            $formattedStart = date('H:i:s', strtotime($request->start_time));
            $formattedEnd = date('H:i:s', strtotime($request->end_time));

            $overlap = ScheduledShift::where('staff_id', $request->carer_id)
                ->where('start_date', $request->start_date)
                ->where(function ($q) use ($formattedStart, $formattedEnd) {
                    $q->whereTime('start_time', '<', $formattedEnd)
                        ->whereTime('end_time', '>', $formattedStart);
                })
                ->exists();

            if ($overlap) {
                return redirect()->back()->with('error', 'The selected carer is already assigned to another shift at this time.')->withInput();
            }
        }

        // 2. Complete Availability Check (against unavailability table)
        $shiftStartStr = $request->start_date . ' ' . $request->start_time;
        $shiftEndStr = $request->start_date . ' ' . $request->end_time;

        // Check Carer Unavailability
        if ($request->carer_id) {
            $carerUnavail = \App\Models\ClientCareUnavailableDate::where('carer_id', $request->carer_id)
                ->where('start_date', '<=', $request->start_date)
                ->where('end_date', '>=', $request->start_date)
                ->exists();
            if ($carerUnavail) {
                return redirect()->back()->with('error', 'The selected carer is marked as unavailable on this date.')->withInput();
            }

            // --- ADDED: Regular Availability Check ---
            $availCheck = $this->checkStaffWorkingHours($request->carer_id, $request->start_date, $request->start_time, $request->end_time);
            if ($availCheck !== true) {
                return redirect()->back()->with('error', $availCheck)->withInput();
            }
        }

        // // Check Client Unavailability
        // if ($request->client_id) {
        //     $clientUnavail = \App\Models\ClientCareUnavailableDate::where('service_user_id', $request->client_id)
        //         ->where('start_date', '<', $shiftEndStr)
        //         ->where('end_date', '>', $shiftStartStr)
        //         ->exists();
        //     if ($clientUnavail) {
        //         return redirect()->back()->with('error', 'The client is marked as unavailable on the selected date/time.')->withInput();
        //     }
        // }
        // Determine shift status based on staff assignment
        $status = isset($request->carer_id) ? 'assigned' : 'unfilled';

        $locationName = $request->location_name;
        if ($request->home_area_id) {
            $locationName = \App\Models\HomeArea::where('id', $request->home_area_id)->value('name');
        }

        try {
            // 1. Create the main shift record using Eloquent
            $shift = ScheduledShift::create([
                'home_id'           => Auth::user()->home_id,
                'care_type_id'      => $request->care_type,
                'assignment'        => $request->assignment ?? 'Client',
                'service_user_id'   => $request->client_id,
                'home_area_id'      => $request->home_area_id,
                'property_id'       => $request->property_id,
                'location_name'     => $locationName,
                'location_address'  => $request->location_address,
                'staff_id'          => $request->carer_id,
                'start_date'        => $request->start_date,
                'start_time'        => $request->start_time,
                'end_time'          => $request->end_time,
                'status'            => $status,
                'shift_type'        => $request->shift_type,
                'shift_category_id' => $request->shift_category,
                'tasks'             => $request->tasks,
                'notes'             => $request->notes,
                'is_recurring'      => $request->has('is_recurring') ? 1 : 0,
            ]);

            $shiftId = $shift->id;

            // 1.1 Insert into shift_recurrences if recurring
            if ($request->has('is_recurring')) {
                ShiftRecurrence::create([
                    'shift_id'           => $shiftId,
                    'frequency'          => $request->frequency,
                    'week_days'          => $request->week_days,
                    'end_recurring_date' => $request->end_date,
                ]);
            }

            // 2. Handle Multiple Assessment Documents
            if ($request->hasFile('assessment_doc_files')) {
                foreach ($request->file('assessment_doc_files') as $key => $file) {
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $destinationPath = public_path('uploads/shifts/assessments');
                    $file->move($destinationPath, $filename);
                    $path = 'uploads/shifts/assessments/' . $filename;

                    $type = $request->assessment_types[$key] ?? 'other';

                    ShiftAssessment::create([
                        'shift_id'        => $shiftId,
                        'assessment_doc'  => $path,
                        'assessment_type' => $type,
                    ]);
                }
            }

            // 3. Handle Regular Attachment
            if ($request->hasFile('doc_file')) {
                $file = $request->file('doc_file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $destinationPath = public_path('uploads/shifts/documents');
                $file->move($destinationPath, $filename);
                $doc_file_path = 'uploads/shifts/documents/' . $filename;

                ShiftDocument::create([
                    'shift_id'     => $shiftId,
                    'doc_name'     => $request->doc_name,
                    'doc_type'     => $request->doc_type,
                    'doc_file'     => $doc_file_path,
                    'doc_required' => $request->has('doc_required') ? 1 : 0,
                ]);
            }

            // 4. Handle System Forms if selected
            if ($request->has('form_ids') && is_array($request->form_ids)) {
                foreach ($request->form_ids as $key => $formId) {
                    $form = DynamicFormBuilder::where('id', $formId)->first();
                    ShiftDocument::create([
                        'shift_id'   => $shiftId,
                        'form_id'    => $formId,
                        'doc_name'   => $request->form_names[$key] ?? 'System Form',
                        'pattern'    => $form->pattern,
                    ]);
                }
            } elseif ($request->form_id) {
                $form = DynamicFormBuilder::where('id', $request->form_id)->first();
                ShiftDocument::create([
                    'shift_id'   => $shiftId,
                    'form_id'    => $request->form_id,
                    'doc_name'   => $request->form_name ?? 'System Form',
                    'pattern'    => $form->pattern,
                ]);
            }

            return redirect()->back()->with('success', 'Shift saved successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error saving shift: ' . $e->getMessage());
        }
    }

    public function updateShift(Request $request, $id)
    {
        $shift = ScheduledShift::findOrFail($id);

        $request->validate([
            'client_id'  => 'nullable',
            'start_date' => 'required',
            'carer_id'   => 'nullable',
            'start_time' => 'required',
            'end_time'   => 'required|after:start_time',
        ]);

        // 1. Double Booking Check (against other shifts)
        if ($request->carer_id) {
            $formattedStart = date('H:i:s', strtotime($request->start_time));
            $formattedEnd = date('H:i:s', strtotime($request->end_time));

            $overlap = ScheduledShift::where('staff_id', $request->carer_id)
                ->where('start_date', $request->start_date)
                ->where('id', '!=', $id)
                ->where(function ($q) use ($formattedStart, $formattedEnd) {
                    $q->whereTime('start_time', '<', $formattedEnd)
                        ->whereTime('end_time', '>', $formattedStart);
                })
                ->exists();

            if ($overlap) {
                return redirect()->back()->with('error', 'The selected carer is already assigned to another shift at this time.')->withInput();
            }
        }

        // 2. Complete Availability Check (against unavailability table)
        $shiftStartStr = $request->start_date . ' ' . $request->start_time;
        $shiftEndStr = $request->start_date . ' ' . $request->end_time;

        // Check Carer Unavailability
        if ($request->carer_id) {
            $carerUnavail = \App\Models\ClientCareUnavailableDate::where('carer_id', $request->carer_id)
                ->where('start_date', '<=', $request->start_date)
                ->where('end_date', '>=', $request->start_date)
                ->exists();
            if ($carerUnavail) {
                return redirect()->back()->with('error', 'The selected carer is marked as unavailable on this date.')->withInput();
            }

            // --- ADDED: Regular Availability Check ---
            $availCheck = $this->checkStaffWorkingHours($request->carer_id, $request->start_date, $request->start_time, $request->end_time);
            if ($availCheck !== true) {
                return redirect()->back()->with('error', $availCheck)->withInput();
            }
        }

        // Check Client Unavailability
        if ($request->client_id) {
            $clientUnavail = \App\Models\ClientCareUnavailableDate::where('service_user_id', $request->client_id)
                ->where('start_date', '<', $shiftEndStr)
                ->where('end_date', '>', $shiftStartStr)
                ->exists();
            if ($clientUnavail) {
                return redirect()->back()->with('error', 'The client is marked as unavailable on the selected date/time.')->withInput();
            }
        }

        $status = isset($request->carer_id) ? 'assigned' : 'unfilled';

        $locationName = $request->location_name;
        if ($request->home_area_id) {
            $locationName = \App\Models\HomeArea::where('id', $request->home_area_id)->value('name');
        }

        try {
            $shift->update([
                'care_type_id'      => $request->care_type,
                'assignment'        => $request->assignment ?? 'Client',
                'service_user_id'   => $request->client_id,
                'home_area_id'      => $request->home_area_id,
                'property_id'       => $request->property_id,
                'location_name'     => $locationName,
                'location_address'  => $request->location_address,
                'staff_id'          => $request->carer_id,
                'start_date'        => $request->start_date,
                'start_time'        => $request->start_time,
                'end_time'          => $request->end_time,
                'status'            => $status,
                'shift_type'        => $request->shift_type,
                'shift_category_id' => $request->shift_category,
                'tasks'             => $request->tasks,
                'notes'             => $request->notes,
                'is_recurring'      => $request->has('is_recurring') ? 1 : 0,
            ]);

            if ($request->has('is_recurring')) {
                ShiftRecurrence::updateOrCreate(
                    ['shift_id' => $shift->id],
                    [
                        'frequency'          => $request->frequency,
                        'week_days'          => $request->week_days,
                        'end_recurring_date' => $request->end_date,
                    ]
                );
            } else {
                ShiftRecurrence::where('shift_id', $shift->id)->delete();
            }

            // --- Document & Assessment Updates ---

            // 1. Delete removed documents and assessments
            $keptDocIds = $request->existing_document_ids ?? [];
            ShiftDocument::where('shift_id', $shift->id)->whereNotIn('id', $keptDocIds)->delete();

            $keptAssIds = $request->existing_assessment_ids ?? [];
            ShiftAssessment::where('shift_id', $shift->id)->whereNotIn('id', $keptAssIds)->delete();

            // 2. Update types for existing assessments
            if ($request->has('existing_assessment_types')) {
                foreach ($request->existing_assessment_types as $id => $type) {
                    ShiftAssessment::where('id', $id)->update(['assessment_type' => $type]);
                }
            }

            // 3. Handle NEW Multiple Assessment Documents
            if ($request->hasFile('assessment_doc_files')) {
                foreach ($request->file('assessment_doc_files') as $key => $file) {
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $destinationPath = public_path('uploads/shifts/assessments');
                    $file->move($destinationPath, $filename);
                    $path = 'uploads/shifts/assessments/' . $filename;

                    $type = $request->assessment_types[$key] ?? 'other';

                    ShiftAssessment::create([
                        'shift_id'        => $shift->id,
                        'assessment_doc'  => $path,
                        'assessment_type' => $type,
                    ]);
                }
            }

            // 4. Handle NEW Regular Attachment
            if ($request->hasFile('doc_file')) {
                $file = $request->file('doc_file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $destinationPath = public_path('uploads/shifts/documents');
                $file->move($destinationPath, $filename);
                $doc_file_path = 'uploads/shifts/documents/' . $filename;

                ShiftDocument::create([
                    'shift_id'     => $shift->id,
                    'doc_name'     => $request->doc_name,
                    'doc_type'     => $request->doc_type,
                    'doc_file'     => $doc_file_path,
                    'doc_required' => $request->has('doc_required') ? 1 : 0,
                ]);
            }

            // 5. Handle NEW System Forms
            if ($request->has('form_ids') && is_array($request->form_ids)) {
                foreach ($request->form_ids as $key => $formId) {
                    ShiftDocument::create([
                        'shift_id'   => $shift->id,
                        'form_id'    => $formId,
                        'doc_name'   => $request->form_names[$key] ?? 'System Form',
                    ]);
                }
            } elseif ($request->form_id) {
                ShiftDocument::create([
                    'shift_id'   => $shift->id,
                    'form_id'    => $request->form_id,
                    'doc_name'   => $request->form_name ?? 'System Form',
                ]);
            }

            return redirect()->back()->with('success', 'Shift updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error updating shift: ' . $e->getMessage());
        }
    }

    public function getWeeklyData(Request $request)
    {
        $startOfWeek = $request->week
            ? Carbon::parse($request->week)->startOfWeek()
            : Carbon::now()->startOfWeek();

        $endOfWeek = $startOfWeek->copy()->endOfWeek();

        $home_id = auth()->user()->home_id;

        $staff = User::where('home_id', $home_id)
            ->where('status', 1)
            ->where('is_deleted', 0)
            ->get();

        $shifts = ScheduledShift::with(['recurrence', 'documents', 'assessments'])->where('home_id', $home_id)
            ->whereBetween('start_date', [
                $startOfWeek->format('Y-m-d'),
                $endOfWeek->format('Y-m-d')
            ])
            ->get()
            ->map(function ($shift) {
                $startTime = \Carbon\Carbon::parse($shift->start_time);
                $endTime = \Carbon\Carbon::parse($shift->end_time);

                return [
                    'id'               => $shift->id,
                    'staff_id'         => $shift->staff_id,
                    'start_date'       => Carbon::parse($shift->start_date)->format('Y-m-d'),
                    'start_time'       => $shift->start_time,
                    'end_time'         => $shift->end_time,
                    'shift_type'       => $shift->shift_type,
                    'location'         => $shift->location_name,
                    'client_id'        => $shift->service_user_id,
                    'home_area_id'    => $shift->home_area_id,
                    'property_id'      => $shift->property_id,
                    'location_name'    => $shift->location_name,
                    'location_address' => $shift->location_address,
                    'start_time_raw'   => $startTime->format('H:i'),
                    'end_time_raw'     => $endTime->format('H:i'),
                    'shift_type_raw'   => $shift->shift_type,
                    'shift_category_id' => $shift->shift_category_id,
                    'care_type_id'     => $shift->care_type_id,
                    'assignment'       => $shift->assignment,
                    'notes'            => $shift->notes,
                    'tasks'            => $shift->tasks,
                    'is_recurring'     => $shift->is_recurring,
                    'recurrence'       => $shift->recurrence,
                    'documents'        => $shift->documents,
                    'assessments'      => $shift->assessments,
                ];
            });

        return response()->json([
            'staff'  => $staff,
            'shifts' => $shifts,
            'start'  => $startOfWeek->format('Y-m-d'),
            'end'    => $endOfWeek->format('Y-m-d'),
        ]);
    }

    public function scheduleShiftByGroup()
    {
        $homeId = auth()->user()->home_id;

        $staffs = User::with(['shifts' => function ($query) use ($homeId) {
            $query->where('home_id', $homeId)
                ->orderBy('start_date', 'desc');
        }, 'shifts.client', 'shifts.recurrence', 'shifts.documents', 'shifts.assessments'])
            ->where('home_id', $homeId)
            ->where('status', 1)
            ->get();

        // Map shift UI attributes
        $staffs->transform(function ($staff) {
            $staff->shifts->transform(function ($shift) {
                $startTime = \Carbon\Carbon::parse($shift->start_time);
                $endTime = \Carbon\Carbon::parse($shift->end_time);

                $shift->start_time_raw = $startTime->format('H:i');
                $shift->end_time_raw = $endTime->format('H:i');
                $shift->shift_type_raw = $shift->shift_type;
                $shift->client_id = $shift->service_user_id;

                return $shift;
            });
            return $staff;
        });

        return response()->json($staffs);
    }

    public function get90DaysData(Request $request)
    {
        $startDate = Carbon::parse($request->date ?? now());
        $endDate = $startDate->copy()->addDays(90);

        $shifts = ScheduledShift::whereBetween('start_date', [$startDate, $endDate])->get();

        $total = $shifts->count();
        $filled = $shifts->where('status', 'assigned')->count();
        $unfilled = $shifts->where('status', 'unfilled')->count();
        $completed = $shifts->where('status', 'completed')->count();

        $fillRate = $total > 0 ? round(($filled / $total) * 100) : 0;

        $weekly = $shifts->groupBy(function ($shift) {
            return Carbon::parse($shift->shift_date)
                ->startOfWeek()
                ->format('Y-m-d');
        });

        $weeklyData = [];

        foreach ($weekly as $weekStart => $weekShifts) {

            $weekTotal = $weekShifts->count();
            $weekFilled = $weekShifts->where('status', 'assigned')->count();
            $weekUnfilled = $weekShifts->where('status', 'unfilled')->count();
            $weekCompleted = $weekShifts->where('status', 'completed')->count();
            $weekFillRate = $weekTotal > 0 ? round(($weekFilled / $weekTotal) * 100) : 0;

            $weeklyData[] = [
                'week_start' => $weekStart,
                'week_end' => Carbon::parse($weekStart)->addDays(6)->format('Y-m-d'),
                'total' => $weekTotal,
                'filled' => $weekFilled,
                'unfilled' => $weekUnfilled,
                'completed' => $weekCompleted,
                'fill_rate' => $weekFillRate
            ];
        }

        return response()->json([
            'summary' => [
                'total' => $total,
                'filled' => $filled,
                'unfilled' => $unfilled,
                'completed' => $completed,
                'fill_rate' => $fillRate
            ],
            'weekly' => $weeklyData
        ]);
    }

    public function getMonthlyShifts(Request $request)
    {

        $shifts = ScheduledShift::with(['recurrence', 'staff', 'documents', 'assessments'])->where('home_id', Auth::user()->home_id)
            ->whereBetween('start_date', [$request->start, $request->end])
            ->get();

        $events = [];

        foreach ($shifts as $shift) {

            // Status color mapping (same as your legend)
            switch (strtolower($shift->status)) {
                case 'completed':
                    $color = '#22c55e'; // green
                    break;
                case 'in progress':
                    $color = '#eab308'; // yellow
                    break;
                case 'assigned':
                case 'scheduled':
                    $color = '#a855f7'; // purple
                    break;
                case 'published':
                    $color = '#3b82f6'; // blue
                    break;
                case 'unfilled':
                    $color = '#f97316'; // orange
                    break;
                case 'cancelled':
                    $color = '#ef4444'; // red
                    break;
                default:
                    $color = '#9ca3af'; // draft gray
            }

            $startTime = \Carbon\Carbon::parse($shift->start_time);
            $endTime = \Carbon\Carbon::parse($shift->end_time);

            $events[] = [
                'title' => date('H:i', strtotime($shift->start_time)),
                'start' => $shift->start_date,
                'backgroundColor' => $color,
                'borderColor' => $color,
                'status' => strtolower($shift->status),
                'shift_id' => $shift->id,
                'shift_type_raw' => $shift->shift_type,
                'shift_category_id' => $shift->shift_category_id,
                'start_time_raw' => $startTime->format('H:i'),
                'end_time_raw' => $endTime->format('H:i'),
                'staff_id' => $shift->staff_id,
                'staff_name' => $shift->staff ? $shift->staff->first_name . ' ' . $shift->staff->last_name : '',
                'client_id' => $shift->service_user_id,
                'home_area_id' => $shift->home_area_id,
                'property_id' => $shift->property_id,
                'location_name' => $shift->location_name,
                'location_address' => $shift->location_address,
                'start_date' => $shift->start_date,
                'care_type' => $shift->care_type_id,
                'assignment' => $shift->assignment,
                'notes' => $shift->notes,
                'tasks' => $shift->tasks,
                'is_recurring' => $shift->is_recurring,
                'recurrence' => $shift->recurrence,
                'documents' => $shift->documents,
                'assessments' => $shift->assessments,
            ];
        }

        return response()->json($events);
    }

    public function deleteShift(Request $request, $id)
    {
        $shift = ScheduledShift::where('home_id', Auth::user()->home_id)->find($id);

        if (!$shift) {
            return response()->json(['success' => false, 'message' => 'Shift not found'], 404);
        }

        $shift->delete();

        return response()->json(['success' => true, 'message' => 'Shift deleted successfully']);
    }

    public function schedule_shift_webview_form($schedule_shift_id)
    {
        $shift = ShiftDocument::where('id', $schedule_shift_id)->first();
        if (!$shift || empty($shift->form_id)) {
            return response(view('frontEnd.error_404'), 404);
        }
        $data['singleData'] = $shift;
        $data['formTemplate'] = DynamicFormBuilder::where('id', $shift->form_id)->first();
        return view('frontEnd.roster.schedule.schedule_shift_form', $data);
    }

    public function scheduleShiftFormSave(Request $req)
    {
        try {
            $data = ShiftDocument::scheduleShiftFormSave($req);
            if ($data) {
                return response()->json(['status' => true, 'message' => 'Form Saved Successfully']);
            }
            return response()->json(['status' => false, 'message' => 'Form not Saved']);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage() . ' on line ' . $e->getLine() . ' in ' . $e->getFile()]);
        }
    }

    public function scheduleShiftFormFetch(Request $req)
    {
        return  ShiftDocument::scheduleShiftFormFetch($req);
    }

    public function assignShift(Request $request)
    {
        $request->validate([
            'shift_id' => 'required|exists:scheduled_shifts,id',
            'staff_id' => 'required'
        ]);

        $shift = ScheduledShift::find($request->shift_id);

        // Update shift times if provided (from drag-and-drop)
        $startDate = $request->start_date ?? $shift->start_date;
        $startTime = $request->start_time ?? $shift->start_time;
        $endTime   = $request->end_time   ?? $shift->end_time;

        // 1. Double Booking Check for drag-and-drop (against other shifts)
        if ($request->staff_id != 'open') {
            $formattedStart = date('H:i:s', strtotime($startTime));
            $formattedEnd = date('H:i:s', strtotime($endTime));

            $overlap = ScheduledShift::where('staff_id', $request->staff_id)
                ->where('start_date', $startDate)
                ->where('id', '!=', $shift->id)
                ->where(function ($q) use ($formattedStart, $formattedEnd) {
                    $q->whereTime('start_time', '<', $formattedEnd)
                        ->whereTime('end_time', '>', $formattedStart);
                })
                ->exists();

            if ($overlap) {
                return response()->json([
                    'success' => false,
                    'message' => 'This carer is already assigned to another shift at this time.'
                ]);
            }
        }

        // 2. Complete Availability Check (against unavailability table)
        if ($request->staff_id != 'open') {
            $carerUnavail = \App\Models\ClientCareUnavailableDate::where('carer_id', $request->staff_id)
                ->where('start_date', '<=', $startDate)
                ->where('end_date', '>=', $startDate)
                ->exists();
            if ($carerUnavail) {
                return response()->json([
                    'success' => false,
                    'message' => 'The selected carer is marked as unavailable on this date.'
                ]);
            }

            // --- ADDED: Regular Availability Check ---
            $availCheck = $this->checkStaffWorkingHours($request->staff_id, $startDate, $startTime, $endTime);
            if ($availCheck !== true) {
                return response()->json([
                    'success' => false,
                    'message' => $availCheck
                ]);
            }
        }

        // Save new values
        $shift->staff_id = $request->staff_id == 'open' ? null : $request->staff_id;
        $shift->start_date = $startDate;
        $shift->start_time = $startTime;
        $shift->end_time = $endTime;
        $shift->status = 'assigned';
        $shift->save();

        return response()->json([
            'success' => true,
            'message' => 'Shift assigned successfully'
        ]);
    }

    public function update_acknowledge(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'shift_id' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }
            $isArray = is_array($req->shift_id);
            if ($isArray) {
                foreach ($req->shift_id as $id) {
                    $d = ScheduledShift::find($id);
                    $d->acknowledge = 1;
                    $d->save();
                }
            } else {
                $d = ScheduledShift::find($req->shift_id);
                $d->acknowledge = 1;
                $d->save();
            }
            return response()->json([
                'status' => true,
                'message' => 'Success'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage() . ' on line ' . $e->getLine() . ' in ' . $e->getFile()
            ], 500);
        }
    }
    public function load_past_shift()
    {
        $now = Carbon::now();
        $alertScheduledShift = ScheduledShift::homeId()
            ->with(['client:id,name', 'staff:id,name'])
            ->where(function ($q) use ($now) {
                $q->whereDate('start_date', '<', $now->toDateString())
                    ->orWhere(function ($q2) use ($now) {
                        $q2->whereDate('start_date', $now->toDateString()) // today
                            ->where('start_time', '<', $now->toTimeString()); // time bhi nikal gaya
                    });
            })
            ->whereIn('status', ['unfilled', 'assigned', 'in_progress'])
            ->latest()
            ->paginate(5);
        return response()->json([
            'status' => true,
            'data' => $alertScheduledShift->items(),
            'pagination' => [
                'next_page_url' => $alertScheduledShift->nextPageUrl(),
                'prev_page_url' => $alertScheduledShift->previousPageUrl()
            ],
        ]);
    }

    /**
     * Helper to check if a staff member is available according to their regular schedule or specific dates.
     */
    private function checkStaffWorkingHours($staffId, $startDate, $startTime, $endTime)
    {
        $shiftStart = Carbon::parse($startDate . ' ' . $startTime);
        $shiftEnd = Carbon::parse($startDate . ' ' . $endTime);
        $dayOfWeek = $shiftStart->format('l');

        // 1. Check Specific Dates (Overrides/Supplements)
        $specific = \App\Models\ClientCareScheduleDate::where('carer_id', $staffId)
            ->whereDate('start_date', $startDate)
            ->first();

        if ($specific) {
            if ($specific->is_working == 0) {
                return "The carer is marked as not working on this specific date ($startDate).";
            }
            $specStart = Carbon::parse($specific->start_date);
            $specEnd = Carbon::parse($specific->end_date);
            if ($shiftStart < $specStart || $shiftEnd > $specEnd) {
                return "Shift is outside the carer's defined working hours for this specific date (" . $specStart->format('H:i') . " - " . $specEnd->format('H:i') . ").";
            }
            return true;
        }

        // 2. Check Weekly Schedule
        $schedules = \App\Models\ClientCareScheduleDay::where('carer_id', $staffId)
            ->where('day', $dayOfWeek)
            ->where('is_working', 1)
            ->get();

        if ($schedules->isEmpty()) {
            return "The carer has no regular schedule defined for $dayOfWeek.";
        }

        $fits = false;
        foreach ($schedules as $sched) {
            $schedStart = Carbon::parse($startDate . ' ' . $sched->start_time);
            $schedEnd = Carbon::parse($startDate . ' ' . $sched->end_time);
            if ($shiftStart >= $schedStart && $shiftEnd <= $schedEnd) {
                $fits = true;
                break;
            }
        }

        if (!$fits) {
            $ranges = $schedules->map(fn($s) => substr($s->start_time, 0, 5) . "-" . substr($s->end_time, 0, 5))->implode(', ');
            return "Shift is outside the carer's regular $dayOfWeek schedule ($ranges).";
        }

        return true;
    }
}
