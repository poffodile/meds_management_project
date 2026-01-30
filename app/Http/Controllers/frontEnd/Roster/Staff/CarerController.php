<?php

namespace App\Http\Controllers\frontEnd\Roster\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Services\Staff\StaffService, App\Models\CompanyDepartment, App\AccessLevel, App\Models\Staff\UserNote;


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
        $data['access_levels'] = AccessLevel::select('id', 'name')->where('home_id', $homeId)->get()->toArray();
        $data['counts'] = $this->staffService->staffCounts($homeId);
        $data['courses'] = $this->staffService->courses();

        return view('frontEnd.roster.staff.carer', $data);
    }

    public function getStaffByStatus(Request $request)
    {
        $homeIds = explode(',', auth()->user()->home_id);
        $homeId  = $homeIds[0] ?? null;

        if (!$homeId) {
            return response()->json([
                'status' => false,
                'message' => 'Home ID not found'
            ]);
        }

        $type   = $request->type ?? 'allCarerActibity';     // all | active | inactive | leave
        $search = trim($request->search ?? ''); // username / name search

        /**
         * ----------------------------------
         * Base query (COMMON CONDITIONS)
         * ----------------------------------
         */
        switch ($type) {
            case 'activeCarer':
                $staff = $this->staffService->activeStaff($homeId);
                break;

            case 'inactiveCarer':
                $staff = $this->staffService->inactiveStaff($homeId);
                break;

            case 'onLeaveCarer':
                $staff = $this->staffService->onLeaveStaff($homeId); // if exists
                break;

            case 'allCarerActibity':
            default:
                $staff = $this->staffService->allStaff($homeId);
                break;
        }

        /**
         * ----------------------------------
         * SEARCH (username / name)
         * ----------------------------------
         */
        // if ($search !== '') {
        //     $staff = $staff->filter(function ($user) use ($search) {
        //         return str_contains(strtolower($user->name), strtolower($search))
        //             || str_contains(strtolower($user->user_name ?? ''), strtolower($search));
        //     })->values();
        // }



        /**
         * ----------------------------------
         * Apply TAB FILTER
         * ----------------------------------
         */
        // $query = clone $baseQuery;

        // if ($type === 'active') {
        //     $query->where('status', 1);
        // } elseif ($type === 'inactive') {
        //     $query->where('status', 0);
        // } elseif ($type === 'leave') {
        //     // $query->where('on_leave', 1);
        // }

        /**
         * ----------------------------------
         * Apply SEARCH FILTER
         * ----------------------------------
         */
        if (!empty($search)) {
            $staff->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('user_name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        /**
         * ----------------------------------
         * Fetch staff
         * ----------------------------------
         */
        $staff = $staff->get();

        // dd($staff);

        // Attach qualifications
        $staff = $this->staffService->attachQualifications($staff);
        // dd($staff);
        /**
         * ----------------------------------
         * Response
         * ----------------------------------
         */
        return response()->json([
            'status' => true,
            'data'   => $staff
        ]);
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

    public function getHourlyRate(Request $request)
    {
        $access_level_id = $request->input('access_level_id');

        if (!$access_level_id) {
            return response()->json(['error' => 'Access level is required.'], 400);
        }

        $pay_rate = $this->staffService->getPayRateForAccessLevel($access_level_id);

        if (!$pay_rate) {
            return response()->json(['error' => 'Pay rate not found.'], 404);
        }

        return response()->json(['hourly_rate' => $pay_rate]);
    }

    public function deleteCarer(Request $request)
    {
        try {
            $request->validate([
                'carer_id' => 'required|integer|exists:user,id'
            ]);

            $carer = User::find($request->carer_id);

            if (!$carer) {
                return response()->json([
                    'status' => false,
                    'message' => 'Carer not found'
                ]);
            }

            // Soft delete (recommended)
            $carer->update(['is_deleted' => 1]);

            // OR Hard delete
            // $carer->delete();

            return response()->json([
                'status' => true,
                'message' => 'Carer deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong'
            ], 500);
        }
    }

    public function shift_resources()
    {
        $staff = User::where('home_id', Auth::user()->home_id)->select('id', 'name')->where('is_deleted', 0)->where('status', 1)->get();

        $resources = [];

        // Open Shifts FIRST
        $resources[] = [
            'id'    => 'open',
            'title' => '🟡 Open Shifts',
            'order' => 0
        ];

        foreach ($staff as $index => $member) {
            $resources[] = [
                'id'    => (string) $member->id,
                'title' => $member->name,
                'order' => $index + 1
            ];
        }

        return response()->json($resources);
    }
}
