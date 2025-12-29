<?php

namespace App\Http\Controllers\frontEnd\Roster\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Services\Staff\StaffService, App\Models\CompanyDepartment;

class CarerController extends Controller
{
    protected StaffService $staffService;

    public function __construct(StaffService $staffService)
    {
        $this->staffService = $staffService;
    }

    public function index()
    {
        $homeIds = explode(',', Auth::user()->home_id);
        $homeId  = $homeIds[0] ?? null;

        if (!$homeId) {
            abort(403, 'Home ID not found.');
        }
        $data['department'] = CompanyDepartment::getActiveCompanyDepartment();
        $data['activeStaff'] = $this->staffService->activeStaff($homeId);
        $data['inactiveStaff'] = $this->staffService->inactiveStaff($homeId);
        $data['onLeaveStaff'] = $this->staffService->onLeaveStaff($homeId);
        $data['allStaff'] = $this->staffService->allStaff($homeId);
        $data['counts'] = $this->staffService->staffCounts($homeId);

        $data['courses'] = $this->staffService->courses();

        // dd($data['allStaff']);

        return view('frontEnd.roster.staff.carer', $data);
    }

    public function carer_details($carer_id)
    {

        if (!$carer_id) {
            abort(400, 'User ID is required.');
        }

        $data['staffDetails'] = $this->staffService->getStaffDetails($carer_id);
        // dd($data['staffDetails']);
        return view('frontEnd.roster.staff.carer_details', $data);
    }

}
