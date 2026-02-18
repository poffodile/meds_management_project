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

class ScheduleShiftController extends Controller
{
    protected $serviceUserService;

    public function __construct(ServiceUserServices $serviceUserService)
    {
        $this->serviceUserService = $serviceUserService;
    }

    public function index()
    {
        $data['company_department'] = CompanyDepartment::getActiveCompanyDepartment();
        $data['service_users'] = $this->serviceUserService->getAllserviceUser();
        $data['dynamic_form_builder'] = DynamicFormBuilder::getFormList();

        $data['scheduled_shifts'] = ScheduledShift::with(['client', 'staff'])
            ->where('home_id', Auth::user()->home_id)
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(function ($shift) {
                $shift->client_name = $shift->client->name ?? 'Unknown Client';
                $shift->staff_name = $shift->staff->name ?? 'Unassigned';
                return $shift;
            });

        return view('frontEnd.roster.schedule.schedule_shift', $data);
    }

    public function store(Request $request)
    {
        // Basic validation
        $request->validate([
            'client_id'  => 'required',
            'start_date' => 'required',
            'carer_id'   => 'nullable',
            'start_time' => 'required',
            'end_time'   => 'required|after:start_time',
        ]);

        try {
            // 1. Create the main shift record using Eloquent
            $shift = ScheduledShift::create([
                'home_id'           => Auth::user()->home_id,
                'care_type_id'      => $request->care_type,
                'assignment'        => $request->assignment ?? 'Client',
                'service_user_id'   => $request->client_id,
                'property_id'       => $request->property_id,
                'location_name'     => $request->location_name,
                'location_address'  => $request->location_address,
                'staff_id'          => $request->carer_id,
                'start_date'        => $request->start_date,
                'start_time'        => $request->start_time,
                'end_time'          => $request->end_time,
                'shift_type'        => $request->shift_type,
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
                    ShiftDocument::create([
                        'shift_id'   => $shiftId,
                        'form_id'    => $formId,
                        'doc_name'   => $request->form_names[$key] ?? 'System Form',
                    ]);
                }
            } elseif ($request->form_id) {
                ShiftDocument::create([
                    'shift_id'   => $shiftId,
                    'form_id'    => $request->form_id,
                    'doc_name'   => $request->form_name ?? 'System Form',
                ]);
            }

            return redirect()->back()->with('success', 'Shift saved successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error saving shift: ' . $e->getMessage());
        }
    }
}
