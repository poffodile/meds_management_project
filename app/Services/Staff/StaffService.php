<?php

namespace App\Services\Staff;

use App\User, App\UserQualification, App\Models\UserEmergencyContact, App\Models\HomeManagement\PayRate;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use App\ServiceUser;


class StaffService
{
    /**
     * Get Pay Rate Type ID by name
     */
    public function getPayRateTypeId(): ?int
    {
        return DB::table('pay_rate_types')
            ->where('type_name', 'Hourly Rate')
            ->where('home_id', Auth::user()->home_id)
            ->value('id');
    }

    /**
     * Get pay rate (hourly) for a given access level id
     */
    public function getPayRateForAccessLevel($access_level_id)
    {
        if (empty($access_level_id)) {
            return null;
        }

        $hourly_rate_id = $this->getPayRateTypeId();
        if (empty($hourly_rate_id)) {
            return null;
        }

        return PayRate::where('access_level_id', $access_level_id)
            ->where('rate_type_id', trim($hourly_rate_id))
            ->where('status', 1)
            ->value('pay_rate');
    }

    /**
     * Get all staff list
     */
    public function allStaff($homeId)
    {
        return User::with('emergencyContacts')
            ->where('home_id', $homeId)
            ->where('is_deleted', 0);
    }

    /**
     * Active staff
     */
    public function activeStaff($homeId)
    {
        return User::with('emergencyContacts')->select('user.*')
            ->where('home_id', $homeId)
            ->where('is_deleted', 0)
            ->where('status', 1);
    }

    /**
     * Inactive staff
     */
    public function inactiveStaff($homeId)
    {
        // dd($homeId);
        return  User::with('emergencyContacts')
            ->where('home_id', $homeId)
            ->where('is_deleted', 0)
            ->where('status', 0);
    }

    /**
     * On Leave Staff
     */
    public function onLeaveStaff($homeId)
    {
        return  User::with('emergencyContacts')
            ->where('home_id', $homeId)
            ->where('is_deleted', 0)
            ->where('status', 2);
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

        $user = User::select('user.*')
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
            'street' => 'street',
            'city' => 'city',
            'postcode' => 'postcode',
            'department' => 'department',
            'employment_type' => 'employment_type',
            'status' => 'status',
            'description' => 'description',
            // 'payroll' => 'payroll',
            'hourly_rate' => 'hourly_rate',
            'holiday_entitlement' => 'holiday_entitlement',
            'dbs_certificate_number' => 'dbs_certificate_number',
            'max_extra_hours' => 'max_extra_hours',
        ];

        foreach ($map as $input => $column) {
            if ($request->has($input) && Schema::hasColumn('user', $column)) {
                $staff->{$column} = $request->input($input);
            }
        }

        $address = $request->input('street') . ' ' . $request->input('city') . ' ' . $request->input('postcode');
        $staffLatLong = $this->getLatLongFromAddress($address);

        // Save latitude and longitude if available
        if ($staffLatLong) {
            if (Schema::hasColumn('user', 'latitude')) {
                $staff->latitude = $staffLatLong['latitude'];
            }
            if (Schema::hasColumn('user', 'longitude')) {
                $staff->longitude = $staffLatLong['longitude'];
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

        // Overtime (FIXED)
        if (Schema::hasColumn('users', 'available_for_overtime')) {
            $isOvertime = (int) $request->input('available_for_overtime', 0);
            $staff->available_for_overtime = $isOvertime;

            if ($isOvertime === 1) {
                // Save only when checked
                $staff->max_extra_hours = (int) $request->input('max_extra_hours', 0);
            } else {
                // Clear when unchecked
                $staff->max_extra_hours = null;
            }
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

    public function getShiftUser($id)
    {
        $client = ServiceUser::find($id);

        // Check if service user has latitude and longitude
        if (!$client || !$client->latitude || !$client->longitude) {
            return collect(); // Return empty collection if no location data
        }

        $clientLatitude = $client->latitude;
        $clientLongitude = $client->longitude;

        $users = User::select('id', 'name', 'postcode', 'latitude', 'longitude')
            ->where('home_id', Auth::user()->home_id)
            ->where('status', 1)
            ->get();

        // dd($users);

        // Map staff to process distance logic for everyone
        $nearbyStaff = $users->map(function ($staff) use ($clientLatitude, $clientLongitude) {

            $distance = null;

            // Check if staff has location data
            if ($staff->latitude && $staff->longitude) {
                // Calculate distance
                $distance = $this->calculateDistance($clientLatitude, $clientLongitude, $staff->latitude, $staff->longitude);
                $staff->distance = $distance;
            } else {
                // No location data -> Treat as very far / mismatch
                $staff->distance = 999999;
            }

            // Assign Card Color & Tag based on distance
            if ($distance !== null && $distance < 20) {
                // $staff->card_color = 'greenCarerCard';
                // $staff->tag = 'Best Match';

            } elseif ($distance !== null && $distance == 20) {
                $staff->card_color = 'muteCarerCard';
                $staff->tag = 'Standard Match';
            } else { // Greater than 20 or no location
                $staff->card_color = 'red';
                $staff->tag = 'Geographic Mismatch';
                $staff->warning = 'Very far from client';
            }

            return $staff;
        })->sortBy('distance'); // Sort by closest distance first

        return $nearbyStaff->values(); // Reset collection keys
    }

    public function getLatLongFromAddress($address)
    {
        if (empty($address)) {
            return null;
        }

        $apiKey = env('GOOGLE_MAPS_KEY');

        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address='
            . urlencode($address)
            . '&key=' . $apiKey;

        $response = @file_get_contents($url);

        if (!$response) {
            return null;
        }

        $geo = json_decode($response, true);

        if (
            isset($geo['status']) &&
            $geo['status'] === 'OK' &&
            isset($geo['results'][0]['geometry']['location'])
        ) {
            return [
                'latitude'  => $geo['results'][0]['geometry']['location']['lat'],
                'longitude' => $geo['results'][0]['geometry']['location']['lng']
            ];
        }

        return null;
    }

    /**
     * Calculate distance between two coordinates using Haversine formula
     * Returns distance in kilometers
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Earth radius in KM

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $distance = $earthRadius * $c;

        return round($distance, 2); // Distance in KM (2 decimal points)
    }
}
