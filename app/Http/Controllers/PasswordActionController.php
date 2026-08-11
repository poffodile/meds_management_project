<?php

namespace App\Http\Controllers;

use App\Services\AuthenticationSecurityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordActionController extends Controller
{
    public function show(string $token)
    {
        $security = app(AuthenticationSecurityService::class);
        $passwordToken = $security->validPasswordToken($token);
        $account = $passwordToken ? $security->accountModel($passwordToken) : null;

        if (! $account || ! in_array($passwordToken->authenticatable_type, ['user', 'service_user'], true)) {
            return redirect('/login')->with('error', 'This password link is invalid or has expired.');
        }

        return view('frontEnd.forget_set_password', [
            'token' => $token,
            'user_name' => $account->user_name,
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
        $account = $passwordToken ? $security->accountModel($passwordToken) : null;

        if (! $account || ! in_array($passwordToken->authenticatable_type, ['user', 'service_user'], true)) {
            return redirect('/login')->with('error', 'This password link is invalid or has expired.');
        }

        $security->consumePasswordToken($passwordToken, $account, $request, Hash::make($data['password']));

        return redirect('/login')->with('success', 'Your password has been set successfully.');
    }
}
