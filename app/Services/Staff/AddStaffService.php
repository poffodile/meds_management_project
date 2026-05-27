<?php

namespace App\Services\Staff;

use App\User;
use Illuminate\Support\Facades\Hash;

class AddStaffService
{
    /**
     * Create a new staff user
     */
    public function create(array $data)
    {
        // ---------------------------------------
        // SECURITY CODE DECODING
        // ---------------------------------------
        $security_code = null;
        if (!empty($data['security_code'])) {
            $security_code = convert_uudecode(base64_decode($data['security_code']));
        }

        // ---------------------------------------
        // HASH PASSWORD
        // ---------------------------------------
        $password = Hash::make($data['password']);

        // ---------------------------------------
        // CREATE USER
        // ---------------------------------------
        $user = new User();
        $user->name                = $data['name'];
        $user->user_name           = $data['user_name'];
        $user->email               = $data['email'];
        $user->job_title           = $data['job_title'] ?? null;
        $user->access_level        = $data['access_level'] ?? null;
        $user->department          = $data['department'];
        $user->description         = $data['description'] ?? '';
        $user->payroll             = $data['payroll'] ?? '';
        $user->holiday_entitlement = $data['holiday_entitlement'] ?? 0;
        $user->access_rights       = $data['access_rights'] ?? null;
        $user->status              = 1;
        $user->security_code       = $security_code;
        $user->is_deleted          = 0;
        $user->phone_no            = $data['phone_no'];
        $user->current_location    = $data['current_location'] ?? null;
        $user->date_of_joining     = date('Y-m-d', strtotime($data['date_of_joining']));
        $user->date_of_leaving     = !empty($data['date_of_leaving']) ? date('Y-m-d', strtotime($data['date_of_leaving'])) : null;
        $user->personal_info       = $data['personal_info'] ?? null;
        $user->banking_info        = $data['banking_info'] ?? null;
        $user->qualification_info  = $data['qualification_info'] ?? null;
        $user->last_activity_time  = null;
        $user->logged_in           = 0;
        $user->login_ip            = null;
        $user->design_layout       = 0;
        $user->company_id          = null;
        $user->user_type           = 'N';
        $user->login_home_id       = null;
        $user->password            = $password;
        $user->home_id             = $data['home_id'];

        // ---------------------------------------
        // IMAGE UPLOAD
        // ---------------------------------------
        $user->image = $this->uploadImage();

        // ---------------------------------------
        // SAVE
        // ---------------------------------------
        if (!$user->save()) {
            return false;
        }

        // ---------------------------------------
        // OPTIONAL SERVICES
        // ---------------------------------------
        User::saveQualification($data, $user->id);

        if (!empty($data['send_credentials'])) {
            User::sendCredentials($user->id);
        }

        return $user;
    }

    /**
     * Handle image upload
     */
    private function uploadImage()
    {
        if (!empty($_FILES['image']['name'])) {
            $tmp_image  = $_FILES['image']['tmp_name'];
            $image_info = pathinfo($_FILES['image']['name']);
            $ext        = strtolower($image_info['extension']);
            $new_name   = time() . '.' . $ext;

            if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                $destination = base_path() . userProfileImageBasePath;

                if (move_uploaded_file($tmp_image, $destination . '/' . $new_name)) {
                    return $new_name;
                }
            }
        }

        return '';
    }
}
