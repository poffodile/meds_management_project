<?php

namespace App\Http\Controllers\frontEnd\Roster;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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

    public function index(){
        $data['company_department'] = CompanyDepartment::getActiveCompanyDepartment();
        $data['service_users'] = $this->serviceUserService->getAllserviceUser();
        $data['dynamic_form_builder'] = DynamicFormBuilder::getFormList();
        return view('frontEnd.roster.schedule.schedule_shift', $data);   
    }

    public function store(Request $request)
    {
        // Basic validation
        $request->validate([
            'client_id' => 'required',
            'start_date'=> 'required',
            'carer_id'  => 'required', 
        ]);

        try {
            // Using generic table 'scheduled_shifts'. Please update if table name is different.
            \Illuminate\Support\Facades\DB::table('scheduled_shifts')->insert([
                'care_type_id'      => $request->care_type,
                'client_id'         => $request->client_id,
                'property_id'       => $request->property_id,
                'location_name'     => $request->location_name,
                'location_address'  => $request->location_address,
                'user_id'           => $request->carer_id, // Assigned carer
                'start_date'        => $request->start_date,
                'start_time'        => $request->start_time,
                'end_time'          => $request->end_time,
                'shift_type'        => $request->shift_type,
                'tasks'             => $request->tasks,
                'notes'             => $request->notes,
                'is_recurring'      => $request->has('is_recurring') ? 1 : 0,
                'frequency'         => $request->frequency,
                'week_days'         => $request->week_days,
                'end_recurring_date'=> $request->end_date,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            return redirect()->back()->with('success', 'Shift saved successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error saving shift: '.$e->getMessage());
        }
    }
}
