<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\ServiceUser;
use App\Services\AuthenticationSecurityService;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class SecureLoginController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'user_name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:1024'],
        ]);

        $identifier = trim($data['user_name']);
        $key = 'api-login:'.hash('sha256', Str::lower($identifier).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($key, config('auth_security.max_attempts'))) {
            return $this->failedResponse();
        }

        $account = ServiceUser::query()
            ->where('user_name', $identifier)
            ->where('status', 1)
            ->where('is_deleted', 0)
            ->first()
            ?? User::query()
                ->where('user_name', $identifier)
                ->where('status', 1)
                ->where('is_deleted', 0)
                ->first();
        $security = app(AuthenticationSecurityService::class);

        if (! $account || $security->isLocked($account)) {
            RateLimiter::hit($key, config('auth_security.decay_seconds'));
            $security->record($request, 'api_login_failed', false, $account, $identifier);

            return $this->failedResponse();
        }

        if (! Hash::check($data['password'], (string) $account->password)) {
            RateLimiter::hit($key, config('auth_security.decay_seconds'));
            $security->registerFailure($account, $request, $identifier);

            return $this->failedResponse();
        }

        RateLimiter::clear($key);
        $security->registerSuccess($account, $request, $identifier);
        $token = $account->createToken(
            'mobile',
            ['mobile'],
            now()->addHours(12)
        )->plainTextToken;

        return $account instanceof ServiceUser
            ? $this->serviceUserResponse($account, $token)
            : $this->staffResponse($account, $token);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    private function serviceUserResponse(ServiceUser $user, string $token)
    {
        $dateOfBirth = $user->date_of_birth
            ? Carbon::parse($user->date_of_birth)
            : null;

        return response()->json([
            'success' => true,
            'message' => 'User login successfully.',
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 43200,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'date_of_birth' => $dateOfBirth?->format('d M Y'),
                'age' => $dateOfBirth?->diffForHumans(['parts' => 3, 'syntax' => Carbon::DIFF_ABSOLUTE]),
                'user_age' => $dateOfBirth?->age ?? 0,
                'wish' => $this->greeting(),
                'image' => $user->image ?? '',
                'su_image_ur' => serviceUserProfileImagePath,
                'user_type' => 'Child',
            ],
        ]);
    }

    private function staffResponse(User $user, string $token)
    {
        $user->loadMissing('access_level');

        return response()->json([
            'result' => [
                'response' => true,
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => 43200,
                'data' => [
                    'id' => (string) $user->id,
                    'name' => $user->name,
                    'access_level_id' => (string) ($user->access_level?->id ?? ''),
                    'access_level_name' => $user->access_level?->name ?? '',
                    'image' => $user->image,
                    'wish' => $this->greeting(),
                    'user_image_url' => userProfileImagePath,
                    'user_type' => 'Staff',
                ],
                'message' => 'User login successfully.',
            ],
        ]);
    }

    private function failedResponse()
    {
        return response()->json([
            'success' => false,
            'result' => [
                'response' => false,
                'message' => 'We could not sign you in with those details.',
            ],
            'message' => 'We could not sign you in with those details.',
        ], 401);
    }

    private function greeting(): string
    {
        return match (true) {
            now()->hour < 12 => 'Good morning',
            now()->hour < 17 => 'Good afternoon',
            now()->hour < 19 => 'Good evening',
            default => 'Good night',
        };
    }
}
