<?php

namespace App\Services\Staff;

use App\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class StaffService
{

    /**
     * Get Pay Rate Type ID by name
     */
    protected function getPayRateTypeId(): ?int
    {
        return DB::table('pay_rate_types')
            ->where('type_name', 'Hourly Rate')
            ->where('home_id', Auth::user()->home_id)
            ->value('id');
    }

    /**
     * Get all staff list
     */
    public function allStaff($homeId)
    {
        $payRateTypeId = $this->getPayRateTypeId();

        return User::select('user.*', 'pay_rates.pay_rate')
            ->leftJoin('pay_rates', function ($join) use ($payRateTypeId) {
                $join->on('user.access_level', '=', 'pay_rates.access_level_id');

                if ($payRateTypeId) {
                    $join->where('pay_rates.rate_type_id', $payRateTypeId);
                }
            })
            ->where('user.home_id', $homeId)
            ->where('user.is_deleted', 0)
            ->get();
    }

    /**
     * Active staff
     */
    public function activeStaff($homeId)
    {
        $payRateTypeId = $this->getPayRateTypeId();

        return User::select('user.*', 'pay_rates.pay_rate')
            ->leftJoin('pay_rates', function ($join) use ($payRateTypeId) {
                $join->on('user.access_level', '=', 'pay_rates.access_level_id');

                // Apply pay_rate_id condition only if it exists
                if ($payRateTypeId) {
                    $join->where('pay_rates.rate_type_id', $payRateTypeId);
                }
            })
            ->where('user.home_id', $homeId)
            ->where('user.status', 1)
            ->where('user.is_deleted', 0)
            ->get();
    }

    /**
     * Inactive staff
     */
    public function inactiveStaff($homeId)
    {
        $payRateTypeId = $this->getPayRateTypeId();

        return User::select('user.*', 'pay_rates.pay_rate')
            ->leftJoin('pay_rates', function ($join) use ($payRateTypeId) {
                $join->on('user.access_level', '=', 'pay_rates.access_level_id');

                // Apply pay_rate_id condition only if it exists
                if ($payRateTypeId) {
                    $join->where('pay_rates.rate_type_id', $payRateTypeId);
                }
            })
            ->where('user.home_id', $homeId)
            ->where('user.status', 0)
            ->where('user.is_deleted', 0)
            ->get();
    }

    /**
     * On Leave Staff
     */
    public function onLeaveStaff($homeId)
    {
        $today = Carbon::today()->toDateString();

        return User::join('staff_leaves', 'staff_leaves.user_id', '=', 'user.id')
            ->where('user.home_id', $homeId)
            ->where('user.is_deleted', 0)
            ->where('staff_leaves.leave_status', 1)
            ->whereDate('staff_leaves.start_date', '<=', $today)
            ->whereDate('staff_leaves.end_date', '>=', $today)
            ->select('user.*')
            ->distinct()
            ->get();
    }

    /**
     * Staff counts
     */
    public function staffCounts($homeId): array
    {
        return [
            'all'       => $this->allStaff($homeId)->count(),
            'active'    => $this->activeStaff($homeId)->count(),
            'inactive'  => $this->inactiveStaff($homeId)->count(),
            'on_leave'  => $this->onLeaveStaff($homeId)->count(),
        ];
    }

    public function getStaffDetails($userId)
    {
        return User::where('id', $userId)->first();
    }

    public function courses()
    {
        // $response = Http::get('http://66.116.198.68:8055/api/all-courses-list/');
        $response = Http::get('http://thunderingslap.com/api/all-courses-list/');
        

        if ($response->successful()) {
            $data = $response->json();
            $courses = $data['all_course_list'] ?? [];
        } else {
            $courses = [];
        }
        return $courses;
    }
}
