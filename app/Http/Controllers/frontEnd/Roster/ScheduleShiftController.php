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
}
