<?php

namespace App;

use App\Services\AuthenticationSecurityService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

class Admin extends Model
{
    protected $table = 'admin';

    protected $hidden = [
        'password',
        'security_code',
    ];

    protected $casts = [
        'locked_until' => 'datetime',
        'last_login_at' => 'datetime',
        'password_changed_at' => 'datetime',
        'force_password_reset' => 'boolean',
    ];

    public static function sendCredentials($system_admin_id = null, string $purpose = 'password_setup')
    {
        $admin = self::query()
            ->whereKey($system_admin_id)
            ->where('is_deleted', 0)
            ->first();

        if (! $admin || ! filter_var($admin->email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $homeSecurityPolicy = $admin->home_id
            ? Home::whereKey($admin->home_id)->value('security_policy')
            : '';
        $token = app(AuthenticationSecurityService::class)
            ->issuePasswordToken($admin, request(), $purpose);
        $setPasswordUrl = url('/admin/set-password/'.$token);
        $companyName = defined('PROJECT_NAME') ? PROJECT_NAME : config('app.name');

        Mail::send('emails.user_set_password_mail', [
            'name' => $admin->name,
            'user_name' => $admin->user_name,
            'set_password_url' => $setPasswordUrl,
            'home_security_policy' => $homeSecurityPolicy,
        ], function ($message) use ($admin, $companyName) {
            $message->to($admin->email, $admin->name)
                ->subject($companyName.' password setup');
        });

        return true;
    }
}
