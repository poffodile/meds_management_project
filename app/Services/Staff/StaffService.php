<?php

namespace App\Services\Staff;

use App\User, App\UserQualification, App\Models\UserEmergencyContact;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;


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

        return User::select(
            'user.*',
            'pay_rates.pay_rate',
            'user_emergency_contacts.name as emergency_contact_name',
            'user_emergency_contacts.phone_no as emergency_contact_phone',
            'user_emergency_contacts.relationship as emergency_contact_relationship',
        )
            ->leftJoin('pay_rates', function ($join) use ($payRateTypeId) {
                $join->on('user.access_level', '=', 'pay_rates.access_level_id');

                if ($payRateTypeId) {
                    $join->where('pay_rates.rate_type_id', $payRateTypeId);
                }
            })
            ->leftJoin('user_emergency_contacts', function ($join) {
                $join->on('user.id', '=', 'user_emergency_contacts.user_id');
            })
            ->where('user.home_id', $homeId)
            ->where('user.is_deleted', 0)
            // ->orderBy('user.created_at', 'desc')
            ->get();
    }

    /**
     * Active staff
     */
    public function activeStaff($homeId)
    {
        $payRateTypeId = $this->getPayRateTypeId();

        return User::with('emergencyContacts')->select(
            'user.*',
            'pay_rates.pay_rate',
            'user_emergency_contacts.name as emergency_contact_name',
            'user_emergency_contacts.phone_no as emergency_contact_phone',
            'user_emergency_contacts.relationship as emergency_contact_relationship',
        )
            ->leftJoin('pay_rates', function ($join) use ($payRateTypeId) {
                $join->on('user.access_level', '=', 'pay_rates.access_level_id');

                // Apply pay_rate_id condition only if it exists
                if ($payRateTypeId) {
                    $join->where('pay_rates.rate_type_id', $payRateTypeId);
                }
            })
            ->leftJoin('user_emergency_contacts', function ($join) {
                $join->on('user.id', '=', 'user_emergency_contacts.user_id');
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

        return User::select(
            'user.*',
            'pay_rates.pay_rate',
            'user_emergency_contacts.name as emergency_contact_name',
            'user_emergency_contacts.phone_no as emergency_contact_phone',
            'user_emergency_contacts.relationship as emergency_contact_relationship',
        )
            ->leftJoin('pay_rates', function ($join) use ($payRateTypeId) {
                $join->on('user.access_level', '=', 'pay_rates.access_level_id');

                // Apply pay_rate_id condition only if it exists
                if ($payRateTypeId) {
                    $join->where('pay_rates.rate_type_id', $payRateTypeId);
                }
            })
            ->leftJoin('user_emergency_contacts', function ($join) {
                $join->on('user.id', '=', 'user_emergency_contacts.user_id');
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
        $payRateTypeId = $this->getPayRateTypeId();

        $user = User::select('user.*', 'pay_rates.pay_rate')
            ->leftJoin('pay_rates', function ($join) use ($payRateTypeId) {
                $join->on('user.access_level', '=', 'pay_rates.access_level_id');

                // Apply rate type condition only if exists
                if (!empty($payRateTypeId)) {
                    $join->where('pay_rates.rate_type_id', $payRateTypeId);
                }
            })
            ->where('user.id', $userId)
            ->where('user.is_deleted', 0)
            ->first();

        if (!$user) {
            return null;
        }

        // Attach related data
        $user->emergencyContact = UserEmergencyContact::where('user_id', $userId)->first();
        $user->qualifications   = UserQualification::where('user_id', $userId)->get();

        return $user;
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

    public function attachQualifications(Collection $staff)
    {
        if ($staff->isEmpty()) {
            return $staff;
        }

        $userIds = $staff->pluck('id')->toArray();

        $qualifications = UserQualification::whereIn('user_id', $userIds)
            ->get()
            ->groupBy('user_id');

        foreach ($staff as $user) {
            $user->qualifications = $qualifications[$user->id] ?? collect();
        }

        return $staff;
    }

    /**
     * Update a staff record from a request. Handles field mapping, dates,
     * profile image upload/removal, qualifications and emergency contact.
     * Returns the updated User model.
     */
    public function updateFromRequest(User $staff, Request $request)
    {
        // Basic mapping of simple fields
        $map = [
            'staff_name' => 'name',
            'staff_user_name' => 'user_name',
            'staff_phone_no' => 'phone_no',
            'staff_email' => 'email',
            'job_title' => 'job_title',
            'department' => 'department_id',
            'employment_type' => 'employment_type',
            'status' => 'status',
            'description' => 'description',
            'payroll' => 'payroll',
            'holiday_entitlement' => 'holiday_entitlement',
            'dbs_certificate_number' => 'dbs_certificate_number',
            'max_extra_hours' => 'max_extra_hours',
        ];

        foreach ($map as $input => $column) {
            if ($request->has($input) && Schema::hasColumn('user', $column)) {
                $staff->{$column} = $request->input($input);
            }
        }

        // Dates
        $dateFields = ['date_of_joining', 'date_of_leaving', 'dbs_expiry_date'];
        foreach ($dateFields as $df) {
            if ($request->filled($df) && Schema::hasColumn('user', $df)) {
                try {
                    $d = Carbon::createFromFormat('d-m-Y', $request->input($df));
                    $staff->{$df} = $d->format('Y-m-d');
                } catch (\Exception $e) {
                    // ignore invalid date formats
                }
            }
        }

        // Overtime
        if (Schema::hasColumn('users', 'available_for_overtime')) {
            $staff->available_for_overtime = $request->has('available_for_overtime') ? 1 : 0;
        }
        if (Schema::hasColumn('users', 'max_extra_hours')) {
            $staff->max_extra_hours = $request->input('max_extra_hours');
        }

        // Profile image handling
        $removeImage = $request->input('remove_image') == '1';
        if ($removeImage && Schema::hasColumn('user', 'image')) {
            if ($staff->image) {
                $path = public_path('images/userProfileImages/' . $staff->image);
                if (file_exists($path)) @unlink($path);
            }
            $staff->image = '';
        }

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $file = $request->file('image');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $dest = public_path('images/userProfileImages');
            if (!is_dir($dest)) @mkdir($dest, 0755, true);
            $file->move($dest, $filename);
            if (Schema::hasColumn('user', 'image')) {
                if ($staff->image) {
                    $old = public_path('images/userProfileImages/' . $staff->image);
                    if (file_exists($old)) @unlink($old);
                }
                $staff->image = $filename;
            }
        }

        $staff->save();

        // Qualifications
        $qualsInput = $request->input('qualifications', []);
        $selectedCourseIds = [];
        if (is_array($qualsInput)) {
            foreach ($qualsInput as $courseId => $q) {
                if (empty($q['course_id'])) continue;
                $selectedCourseIds[] = $courseId;

                $file = $request->file("qualifications.$courseId.cert");
                $certFilename = null;
                if ($file && $file->isValid()) {
                    $certFilename = time() . '_qual_' . $courseId . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                    $dest = public_path('images/userQualification');
                    if (!is_dir($dest)) @mkdir($dest, 0755, true);
                    $file->move($dest, $certFilename);
                }

                $data = ['user_id' => $staff->id, 'course_id' => $courseId];
                $exists = DB::table('user_qualification')->where($data)->exists();
                if ($exists) {
                    $update = ['name' => $q['name'] ?? ''];
                    if ($certFilename) $update['image'] = $certFilename;
                    DB::table('user_qualification')->where($data)->update($update);
                } else {
                    $insert = array_merge($data, ['name' => $q['name'] ?? '', 'image' => $certFilename ?: '']);
                    DB::table('user_qualification')->insert($insert);
                }
            }
        }

        // sync relation if available
        if (method_exists($staff, 'qualifications')) {
            try {
                $staff->qualifications()->sync($selectedCourseIds);
            } catch (\Exception $e) {
                // ignore
            }
        }

        // Emergency contact
        $ec = $request->input('emergency_contact', []);
        if (is_array($ec) && (!empty($ec['name']) || !empty($ec['phone_no']) || !empty($ec['relationship']))) {
            DB::table('user_emergency_contacts')->updateOrInsert(
                ['user_id' => $staff->id],
                ['name' => $ec['name'] ?? null, 'phone_no' => $ec['phone_no'] ?? null, 'relationship' => $ec['relationship'] ?? null]
            );
        }

        return $staff;
    }
}
