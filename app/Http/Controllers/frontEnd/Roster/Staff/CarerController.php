<?php

namespace App\Http\Controllers\frontEnd\Roster\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Services\Staff\StaffService, App\Models\CompanyDepartment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;

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
        $data['allStaff'] = $this->staffService->allStaff($homeId);
        $data['activeStaff'] = $this->staffService->activeStaff($homeId);
        $data['inactiveStaff'] = $this->staffService->inactiveStaff($homeId);
        $data['onLeaveStaff'] = $this->staffService->onLeaveStaff($homeId);

        $data['allStaff'] = $this->staffService
            ->attachQualifications(
                $this->staffService->allStaff($homeId)
            );
        // dd($data['allStaff']);
        $data['activeStaff'] = $this->staffService
            ->attachQualifications(
                $this->staffService->activeStaff($homeId)
            );
        $data['inactiveStaff'] = $this->staffService
            ->attachQualifications(
                $this->staffService->inactiveStaff($homeId)
            );

        // $data['onLeaveStaff'] = $this->staffService
        //     ->attachQualifications(
        //         $this->staffService->onLeaveStaff($homeId)
        //     );


       
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
        return view('frontEnd.roster.staff.carer_details', $data);
    }

    public function update(Request $request, $carer_id)
    {
        $staff = User::findOrFail($carer_id);

        // Basic validation (extend as needed)
        $request->validate([
            'staff_name' => 'required|string|max:255',
            'staff_email' => 'nullable|email|max:255',
        ]);

        // delegate to service to perform the update
        $this->staffService->updateFromRequest($staff, $request);

        return back()->with('success', 'Staff updated');
    }
}
