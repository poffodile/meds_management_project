<?php

namespace App\Http\Controllers\backEnd;

use App\Admin;
use App\Home;
use App\Http\Controllers\Controller;
use App\Services\AuthenticationSecurityService;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class SecureAdminAuthenticationController extends Controller
{
    public function login(Request $request)
    {
        if ($request->isMethod('get')) {
            return Session::has('scitsAdminSession')
                ? redirect('/admin')
                : view('backEnd.login');
        }

        $data = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:1024'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'home' => ['nullable', 'string', 'max:255'],
        ]);

        $identifier = trim($data['username']);
        $key = $this->throttleKey($request, $identifier);

        if (RateLimiter::tooManyAttempts($key, config('auth_security.max_attempts'))) {
            return $this->failure();
        }

        [$admin, $agent, $passwordAccount] = $this->resolveAccount($data);
        $security = app(AuthenticationSecurityService::class);

        if (! $passwordAccount || $security->isLocked($passwordAccount)) {
            RateLimiter::hit($key, config('auth_security.decay_seconds'));
            $security->record($request, 'admin_login_failed', false, $passwordAccount, $identifier);

            return $this->failure();
        }

        if (! $this->passwordMatches($passwordAccount, $data['password'], $request)) {
            RateLimiter::hit($key, config('auth_security.decay_seconds'));
            $security->registerFailure($passwordAccount, $request, $identifier);

            return $this->failure();
        }

        if (! $admin || ! $this->mayAccessSelectedContext($admin, $agent, $data)) {
            RateLimiter::hit($key, config('auth_security.decay_seconds'));
            $security->record($request, 'admin_login_wrong_context', false, $passwordAccount, $identifier);

            return $this->failure();
        }

        if ($admin->access_type === 'S') {
            $admin->home_id = 0;
        }

        $request->session()->regenerate();
        RateLimiter::clear($key);
        $security->registerSuccess($passwordAccount, $request, $identifier);

        Session::put('scitsAdminSession', $this->safeAdminSession($admin));
        if ($agent) {
            Session::put('scitsAgentSession', (object) $agent->only([
                'id', 'name', 'image', 'home_id', 'user_type', 'admn_id',
            ]));
        }

        return in_array($admin->access_type, ['S', 'O'], true)
            ? redirect('admin/welcome')
            : redirect('admin/dashboard');
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('admin/login')->with('flash_message_success', 'Logged out successfully.');
    }

    private function resolveAccount(array $data): array
    {
        $isSuperAdmin = Str::lower(trim($data['company_name'] ?? '')) === 'scits super admin';

        if ($isSuperAdmin) {
            $admin = Admin::query()
                ->where('user_name', $data['username'])
                ->where('access_type', 'S')
                ->where('is_deleted', 0)
                ->first();

            return [$admin, null, $admin];
        }

        $agent = User::query()
            ->where('user_name', $data['username'])
            ->where('user_type', 'A')
            ->where('status', 1)
            ->where('is_deleted', 0)
            ->first();

        if ($agent) {
            $admin = Admin::query()
                ->whereKey($agent->admn_id)
                ->where('is_deleted', 0)
                ->first();

            return [$admin, $agent, $agent];
        }

        $admin = Admin::query()
            ->where('user_name', $data['username'])
            ->where('is_deleted', 0)
            ->first();

        return [$admin, null, $admin];
    }

    private function passwordMatches($account, string $plainPassword, Request $request): bool
    {
        $hash = (string) $account->password;

        if (Str::startsWith($hash, ['$2y$', '$argon2'])) {
            return Hash::check($plainPassword, $hash);
        }

        if ($account instanceof Admin && preg_match('/^[a-f0-9]{32}$/i', $hash)) {
            if (! hash_equals(Str::lower($hash), md5($plainPassword))) {
                return false;
            }

            $account->forceFill([
                'password' => Hash::make($plainPassword),
                'password_changed_at' => now(),
                'force_password_reset' => true,
            ])->save();

            app(AuthenticationSecurityService::class)->record(
                $request,
                'legacy_password_hash_upgraded',
                true,
                $account,
                $account->user_name
            );

            return true;
        }

        return false;
    }

    private function mayAccessSelectedContext(Admin $admin, ?User $agent, array $data): bool
    {
        if ($agent) {
            $allowedHomeIds = array_values(array_filter(array_map(
                'intval',
                explode(',', (string) $agent->home_id)
            )));
            $home = Home::query()
                ->where('admin_id', $admin->id)
                ->where('title', $data['home'] ?? '')
                ->where('is_deleted', 0)
                ->first();

            if (! $home || ! in_array((int) $home->id, $allowedHomeIds, true)) {
                return false;
            }

            $admin->home_id = $home->id;
            return true;
        }

        if ($admin->access_type === 'S') {
            return true;
        }

        if ($admin->access_type === 'O') {
            if (! hash_equals((string) $admin->company, (string) ($data['company_name'] ?? ''))) {
                return false;
            }

            if (! empty($data['home'])) {
                return Home::query()
                    ->where('admin_id', $admin->id)
                    ->where('title', $data['home'])
                    ->where('is_deleted', 0)
                    ->exists();
            }

            return true;
        }

        if ($admin->access_type === 'A') {
            return Home::query()
                ->whereKey($admin->home_id)
                ->where('title', $data['home'] ?? '')
                ->where('is_deleted', 0)
                ->exists();
        }

        return false;
    }

    private function safeAdminSession(Admin $admin): object
    {
        return (object) $admin->only([
            'id',
            'name',
            'user_name',
            'email',
            'company',
            'access_type',
            'home_id',
            'image',
        ]);
    }

    private function throttleKey(Request $request, string $identifier): string
    {
        return 'admin-login:'.hash('sha256', Str::lower($identifier).'|'.$request->ip());
    }

    private function failure()
    {
        return redirect()->back()->with(
            'error',
            'We could not sign you in with those details. Please check them or try again later.'
        );
    }
}
