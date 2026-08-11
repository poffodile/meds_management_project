<?php

namespace App\Http\Controllers\backEnd;

use App\Admin;
use App\Http\Controllers\Controller;
use App\Services\AuthenticationSecurityService;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AdminProfilePasswordController extends Controller
{
    public function update(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => [
                'required',
                'same:confirm_password',
                Password::min(12)->mixedCase()->numbers()->symbols(),
            ],
            'confirm_password' => ['required', 'string'],
        ]);

        $account = $this->account();

        if (! $account || ! $this->matchesCurrentPassword($account, $data['current_password'])) {
            return redirect()->back()->with('error', 'The current password is incorrect.');
        }

        $account->forceFill([
            'password' => Hash::make($data['new_password']),
            'password_changed_at' => now(),
            'force_password_reset' => false,
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ])->save();

        app(AuthenticationSecurityService::class)->record(
            $request,
            'password_changed',
            true,
            $account,
            $account->user_name
        );

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('admin/login')->with(
            'flash_message_success',
            'Password changed. Please sign in again.'
        );
    }

    private function account()
    {
        if (Session::has('scitsAgentSession')) {
            return User::query()
                ->whereKey(Session::get('scitsAgentSession')->id)
                ->where('status', 1)
                ->where('is_deleted', 0)
                ->first();
        }

        return Admin::query()
            ->whereKey(Session::get('scitsAdminSession')->id)
            ->where('is_deleted', 0)
            ->first();
    }

    private function matchesCurrentPassword($account, string $plainPassword): bool
    {
        $hash = (string) $account->password;

        if (Str::startsWith($hash, ['$2y$', '$argon2'])) {
            return Hash::check($plainPassword, $hash);
        }

        return $account instanceof Admin
            && preg_match('/^[a-f0-9]{32}$/i', $hash)
            && hash_equals(Str::lower($hash), md5($plainPassword));
    }
}
