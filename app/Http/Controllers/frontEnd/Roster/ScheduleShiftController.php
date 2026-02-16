<?php

namespace App\Http\Controllers\frontEnd\Roster;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CompanyDepartment;
use App\Services\ServiceUser\ServiceUserServices;
use App\DynamicFormBuilder;

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
        return view('frontEnd.roster.schedule.schedule_shift', $data);
    }

    public function store(Request $request)
    {
        // Basic validation
        $request->validate([
            'client_id'  => 'required',
            'start_date' => 'required',
            'carer_id'   => 'required',
            'start_time' => 'required',
            'end_time'   => 'required|after:start_time',
        ]);

        try {
            // 1. Insert the main shift record
            $shiftId = \Illuminate\Support\Facades\DB::table('scheduled_shifts')->insertGetId([
                'home_id'           => Auth::user()->home_id,
                'care_type_id'      => $request->care_type,
                'assignment'        => $request->assignment ?? 'Client',
                'client_id'         => $request->client_id,
                'property_id'       => $request->property_id,
                'location_name'     => $request->location_name,
                'location_address'  => $request->location_address,
                'carer_id'          => $request->carer_id,
                'form_id'           => $request->form_id,
                'start_date'        => $request->start_date,
                'start_time'        => $request->start_time,
                'end_time'          => $request->end_time,
                'shift_type'        => $request->shift_type,
                'tasks'             => $request->tasks,
                'notes'             => $request->notes,
                'is_recurring'      => $request->has('is_recurring') ? 1 : 0,
                'frequency'         => $request->frequency,
                'week_days'         => $request->week_days,
                'end_recurring_date' => $request->end_date,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            // 1.1 Insert into shift_recurrences if recurring
            if ($request->has('is_recurring')) {
                \Illuminate\Support\Facades\DB::table('shift_recurrences')->insert([
                    'shift_id'           => $shiftId,
                    'frequency'          => $request->frequency,
                    'week_days'          => $request->week_days,
                    'end_recurring_date' => $request->end_date,
                    'created_at'         => now(),
                    'updated_at'         => now(),
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

                    \Illuminate\Support\Facades\DB::table('shift_assessments')->insert([
                        'shift_id'        => $shiftId,
                        'assessment_doc'  => $path,
                        'assessment_type' => $type,
                        'created_at'      => now(),
                        'updated_at'      => now(),
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

                \Illuminate\Support\Facades\DB::table('shift_documents')->insert([
                    'shift_id'     => $shiftId,
                    'doc_name'     => $request->doc_name,
                    'doc_type'     => $request->doc_type,
                    'doc_file'     => $doc_file_path,
                    'doc_required' => $request->has('doc_required') ? 1 : 0,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }

            // 4. Handle System Forms if selected
            if ($request->has('form_ids') && is_array($request->form_ids)) {
                foreach ($request->form_ids as $key => $formId) {
                    \Illuminate\Support\Facades\DB::table('shift_documents')->insert([
                        'shift_id'   => $shiftId,
                        'form_id'    => $formId,
                        'doc_name'   => $request->form_names[$key] ?? 'System Form',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            } elseif ($request->form_id) {
                \Illuminate\Support\Facades\DB::table('shift_documents')->insert([
                    'shift_id'   => $shiftId,
                    'form_id'    => $request->form_id,
                    'doc_name'   => $request->form_name ?? 'System Form',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return redirect()->back()->with('success', 'Shift saved successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error saving shift: ' . $e->getMessage());
        }
    }
}
