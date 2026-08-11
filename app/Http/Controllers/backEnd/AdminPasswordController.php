<?php

namespace App\Http\Controllers\backEnd;

use App\Admin;
use App\Http\Controllers\Controller;
use App\Services\AuthenticationSecurityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminPasswordController extends Controller
{
    public function requestReset(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
        ]);

        $admin = Admin::query()
            ->where('email', $data['email'])
            ->where('is_deleted', 0)
            ->first();

        if ($admin) {
            try {
                Admin::sendCredentials($admin->id, 'password_reset');
            } catch (\Throwable $exception) {
                \Illuminate\Support\Facades\Log::error('Password reset email failed.', [
                    'account_type' => 'admin',
                    'account_id' => $admin->id,
                    'exception' => $exception,
                ]);
            }
        }

        return redirect('admin/login')->with(
            'success',
            'If the account exists, a password link has been sent.'
        );
    }

    public function show(Request $request, string $token)
    {
        $security = app(AuthenticationSecurityService::class);
        $passwordToken = $security->validPasswordToken($token);
        $admin = $passwordToken && $passwordToken->authenticatable_type === 'admin'
            ? $security->accountModel($passwordToken)
            : null;

        if (! $admin || $admin->is_deleted) {
            return redirect('admin/login')->with('error', 'This password link is invalid or has expired.');
        }

        return view('backEnd.admin_set_password', [
            'token' => $token,
            'user_name' => $admin->user_name,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'size:64'],
            'password' => [
                'required',
                'confirmed',
                Password::min(12)->mixedCase()->numbers()->symbols(),
            ],
        ]);

        $security = app(AuthenticationSecurityService::class);
        $passwordToken = $security->validPasswordToken($data['token']);
        $admin = $passwordToken && $passwordToken->authenticatable_type === 'admin'
            ? $security->accountModel($passwordToken)
            : null;

        if (! $admin || $admin->is_deleted) {
            return redirect('admin/login')->with('error', 'This password link is invalid or has expired.');
        }

        $security->consumePasswordToken($passwordToken, $admin, $request, Hash::make($data['password']));

        return redirect('admin/login')->with('success', 'Your password has been set successfully.');
    }

    public function checkEmail()
    {
        return response()->json(true);
    }
}
