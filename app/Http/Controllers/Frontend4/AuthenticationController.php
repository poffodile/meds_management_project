<?php

namespace App\Http\Controllers\Frontend4;

use App\Admin;
use App\Home;
use App\Http\Controllers\Controller;
use App\Models\Frontend4User;
use App\Services\Frontend4\AuthenticationSecurityService;
use App\Services\Frontend4\RoleResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class AuthenticationController extends Controller
{
    public function showLogin()
    {
        $this->useF4Layout();

        if (Auth::guard('frontend4')->check()) {
            return redirect()->route('frontend4.today');
        }

        return Inertia::render('Auth/Login', [
            'servicesUrl' => route('frontend4.services'),
            'loginUrl' => route('frontend4.login.store'),
            'forgotUrl' => route('frontend4.password.request'),
            'status' => session('status'),
            'error' => session('error'),
        ]);
    }

    public function services(Request $request)
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
        ]);
        $adminId = $this->organisationId($data['company_name']);

        if (! $adminId) {
            return response()->json(['services' => []]);
        }

        return response()->json([
            'services' => Home::where('admin_id', $adminId)
                ->where('is_deleted', 0)
                ->orderBy('title')
                ->get(['id', 'title'])
                ->map(fn ($home) => ['id' => (int) $home->id, 'name' => $home->title])
                ->values(),
        ]);
    }

    public function login(Request $request, AuthenticationSecurityService $security)
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'home' => ['required', 'integer'],
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:1024'],
        ]);
        $identifier = trim($data['username']);
        $throttleKey = 'frontend4-login:'.hash('sha256', Str::lower($identifier).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, config('frontend4_auth.max_attempts'))) {
            return back()->withInput($request->except('password'))->with('error', $this->failureMessage());
        }

        $user = Frontend4User::where('user_name', $identifier)
            ->where('status', 1)
            ->where('is_deleted', 0)
            ->first();

        if (! $user) {
            RateLimiter::hit($throttleKey, config('frontend4_auth.decay_seconds'));
            $security->record($request, 'login_failed', false, null, $identifier);

            return back()->withInput($request->except('password'))->with('error', $this->failureMessage());
        }

        $adminId = $this->organisationId($data['company_name']);
        $home = $adminId
            ? Home::whereKey($data['home'])->where('admin_id', $adminId)->where('is_deleted', 0)->first()
            : null;
        $allowedHomeIds = $this->allowedHomeIds($user);
        $mayUseMedication = app(RoleResolver::class)->hasAccess($user);

        if (
            ! $home
            || ! in_array((int) $home->id, $allowedHomeIds, true)
            || ! $mayUseMedication
            || $security->isLocked($user)
            || ! $security->passwordMatches($user, $data['password'])
        ) {
            RateLimiter::hit($throttleKey, config('frontend4_auth.decay_seconds'));
            $security->registerFailure($user, $request, $identifier);

            return back()->withInput($request->except('password'))->with('error', $this->failureMessage());
        }

        RateLimiter::clear($throttleKey);
        $security->registerSuccess($user, $request, $identifier);
        Auth::guard('frontend4')->login($user);
        $request->session()->regenerate();
        $request->session()->put([
            'frontend4.active_home_id' => (int) $home->id,
            'frontend4.allowed_home_ids' => $allowedHomeIds,
            'frontend4.last_activity' => time(),
        ]);

        $intended = $request->session()->pull('frontend4.intended');

        return $intended ? redirect()->to($intended) : redirect()->route('frontend4.today');
    }

    public function logout(Request $request, AuthenticationSecurityService $security)
    {
        $user = Auth::guard('frontend4')->user();
        if ($user) {
            $security->record($request, 'logout', true, $user, $user->user_name);
        }

        Auth::guard('frontend4')->logout();
        $request->session()->forget([
            'frontend4.active_home_id',
            'frontend4.allowed_home_ids',
            'frontend4.last_activity',
            'frontend4.intended',
        ]);
        $request->session()->regenerateToken();

        return redirect()->route('frontend4.login')->with('status', 'You have signed out of Care One OS.');
    }

    public function showForgotPassword()
    {
        $this->useF4Layout();

        return Inertia::render('Auth/ForgotPassword', [
            'submitUrl' => route('frontend4.password.email'),
            'loginUrl' => route('frontend4.login'),
            'status' => session('status'),
        ]);
    }

    public function sendResetLink(Request $request, AuthenticationSecurityService $security)
    {
        $data = $request->validate(['email' => ['required', 'email:rfc', 'max:255']]);
        $user = Frontend4User::where('email', $data['email'])
            ->where('status', 1)
            ->where('is_deleted', 0)
            ->first();

        if ($user && app(RoleResolver::class)->hasAccess($user)) {
            try {
                $token = $security->issuePasswordToken($user, $request);
                $url = route('frontend4.password.reset', ['token' => $token]);
                $company = config('app.name', 'Care One OS');
                Mail::send('emails.user_set_password_mail', [
                    'name' => $user->name,
                    'user_name' => $user->user_name,
                    'set_password_url' => $url,
                    'home_security_policy' => '',
                ], function ($message) use ($user, $company) {
                    $message->to($user->email, $user->name)
                        ->subject($company.' password reset');
                });
            } catch (\Throwable $exception) {
                Log::error('Frontend 4 password reset email failed.', [
                    'user_id' => $user->id,
                    'exception' => $exception,
                ]);
            }
        } else {
            $security->record($request, 'password_reset_requested', true, null, $data['email']);
        }

        return back()->with('status', 'If the account exists, a Care One OS password link has been sent.');
    }

    public function showResetPassword(string $token, AuthenticationSecurityService $security)
    {
        $this->useF4Layout();
        $passwordToken = $security->validPasswordToken($token);

        return Inertia::render('Auth/ResetPassword', [
            'token' => $passwordToken ? $token : null,
            'submitUrl' => route('frontend4.password.update'),
            'loginUrl' => route('frontend4.login'),
            'invalid' => ! $passwordToken,
        ]);
    }

    public function resetPassword(Request $request, AuthenticationSecurityService $security)
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'size:64'],
            'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()],
        ]);
        $token = $security->validPasswordToken($data['token']);
        $user = $token ? Frontend4User::whereKey($token->user_id)->where('status', 1)->where('is_deleted', 0)->first() : null;

        if (! $token || ! $user) {
            return back()->withErrors(['token' => 'This Care One OS password link is invalid or has expired.']);
        }

        $security->consumePasswordToken($token, $user, $request, $data['password']);

        return redirect()->route('frontend4.login')->with('status', 'Your Care One OS password has been updated.');
    }

    private function useF4Layout(): void
    {
        Inertia::setRootView('f4');
    }

    private function organisationId(string $companyName): ?int
    {
        $id = Admin::whereRaw('LOWER(company) = ?', [Str::lower(trim($companyName))])
            ->where('is_deleted', 0)
            ->value('id');

        return $id ? (int) $id : null;
    }

    private function allowedHomeIds(Frontend4User $user): array
    {
        return collect(explode(',', (string) $user->real_home_id))
            ->map(fn ($id) => (int) trim($id))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function failureMessage(): string
    {
        return 'We could not sign you in with those details.';
    }
}
