<?php

namespace App\Http\Controllers\Frontend4;

use App\Admin;
use App\Home;
use App\Http\Controllers\Controller;
use App\Models\Frontend4User;
use App\Services\Frontend4\AuthenticationSecurityService;
use App\Services\Frontend4\AccessContext;
use App\Services\Frontend4\RoleResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class AuthenticationController extends Controller
{
    public function showLogin(Request $request)
    {
        $this->useF4Layout();

        if (Auth::guard('frontend4')->check()) {
            return app(AccessContext::class)->serviceId() > 0
                ? redirect()->route('frontend4.today')
                : redirect()->route('frontend4.service-selection.show');
        }

        if ($request->boolean('change_organisation')) {
            $this->forgetPendingOrganisation($request);
        }

        $pendingOrganisationId = $this->pendingOrganisationId($request);
        $pendingOrganisation = $pendingOrganisationId
            ? Admin::whereKey($pendingOrganisationId)->where('is_deleted', 0)->first()
            : null;
        if (! $pendingOrganisation) {
            $this->forgetPendingOrganisation($request);
        }

        return Inertia::render('Auth/Login', [
            'step' => $pendingOrganisation ? 'credentials' : 'organisation',
            'organisationName' => $pendingOrganisation?->company,
            'organisationUrl' => route('frontend4.login.organisation'),
            'loginUrl' => route('frontend4.login.store'),
            'changeOrganisationUrl' => route('frontend4.login', ['change_organisation' => 1]),
            'forgotUrl' => route('frontend4.password.request'),
            'status' => session('status'),
            'error' => session('error'),
        ]);
    }

    public function chooseOrganisation(Request $request)
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
        ]);
        $adminId = $this->organisationId($data['company_name']);

        if (! $adminId) {
            return back()->withInput()->with('error', 'We could not continue with those details.');
        }

        $organisation = Admin::findOrFail($adminId);
        $request->session()->put([
            'frontend4.pending_organisation_id' => $adminId,
            'frontend4.pending_organisation_name' => $organisation->company,
            'frontend4.pending_organisation_at' => time(),
        ]);

        return redirect()->route('frontend4.login');
    }

    public function login(Request $request, AuthenticationSecurityService $security, AccessContext $context)
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:1024'],
        ]);
        $identifier = trim($data['username']);
        $adminId = $this->pendingOrganisationId($request);
        $organisation = Admin::whereKey($adminId)->where('is_deleted', 0)->first();
        if (! $organisation) {
            $this->forgetPendingOrganisation($request);

            return redirect()->route('frontend4.login')
                ->with('error', 'Please choose your organisation again.');
        }

        $throttleKey = 'frontend4-login:'.hash('sha256', $adminId.'|'.Str::lower($identifier).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, config('frontend4_auth.max_attempts'))) {
            return back()->withInput($request->except('password'))->with('error', $this->failureMessage());
        }

        $user = Frontend4User::where('user_name', $identifier)
                ->where('status', 1)
                ->where('is_deleted', 0)
                ->get()
                ->first(fn (Frontend4User $candidate) => $context->belongsToOrganisation($candidate, $adminId));

        if (! $user) {
            RateLimiter::hit($throttleKey, config('frontend4_auth.decay_seconds'));
            $security->record($request, 'login_failed', false, null, $identifier, [
                'organisation_id' => $adminId,
            ]);

            return back()->withInput($request->except('password'))->with('error', $this->failureMessage());
        }

        $mayUseMedication = app(RoleResolver::class)->hasAccess($user);

        if (
            ! $mayUseMedication
            || $security->isLocked($user)
            || ! $security->passwordMatches($user, $data['password'])
        ) {
            RateLimiter::hit($throttleKey, config('frontend4_auth.decay_seconds'));
            $security->registerFailure($user, $request, $identifier, [
                'organisation_id' => $adminId,
            ]);

            return back()->withInput($request->except('password'))->with('error', $this->failureMessage());
        }

        RateLimiter::clear($throttleKey);
        $security->registerSuccess($user, $request, $identifier, [
            'organisation_id' => $adminId,
        ]);
        Auth::guard('frontend4')->login($user);
        $request->session()->regenerate();
        $context->putOrganisationSession($user, $adminId);
        $this->forgetPendingOrganisation($request);
        $request->session()->put('frontend4.last_activity', time());

        $serviceIds = $context->allowedServiceIds($user, $adminId);
        if ($serviceIds === []) {
            $security->record($request, 'no_active_service', false, $user, $identifier, [
                'organisation_id' => $adminId,
            ]);
            $this->endFrontend4Session($request, $context);

            return redirect()->route('frontend4.login')->with(
                'error',
                'Your account has no active service in this organisation. Please contact your manager.'
            );
        }

        if (count($serviceIds) === 1) {
            return $this->completeServiceSelection(
                $request,
                $context,
                $security,
                $user,
                $adminId,
                $serviceIds[0],
                true
            );
        }

        return redirect()->route('frontend4.service-selection.show');
    }

    public function showServiceSelection(Request $request, AccessContext $context, AuthenticationSecurityService $security)
    {
        $this->useF4Layout();
        $user = Auth::guard('frontend4')->user();
        abort_unless($user instanceof Frontend4User, 403);

        if ($context->serviceId() > 0 && $context->validContext(
            $user,
            $context->organisationId(),
            $context->serviceId(),
            $context->locationId()
        )) {
            return redirect()->route('frontend4.today');
        }

        $organisationId = $context->organisationId();
        $serviceIds = $context->allowedServiceIds($user, $organisationId);
        if ($serviceIds === []) {
            $security->record($request, 'no_active_service', false, $user, $user->user_name, [
                'organisation_id' => $organisationId,
            ]);
            $this->endFrontend4Session($request, $context);

            return redirect()->route('frontend4.login')->with(
                'error',
                'Your account has no active service in this organisation. Please contact your manager.'
            );
        }

        if (count($serviceIds) === 1) {
            return $this->completeServiceSelection(
                $request,
                $context,
                $security,
                $user,
                $organisationId,
                $serviceIds[0],
                true
            );
        }

        return Inertia::render('Auth/SelectService', [
            'organisationName' => Admin::whereKey($organisationId)->value('company'),
            'services' => Home::whereIn('id', $serviceIds)
                ->where('admin_id', $organisationId)
                ->where('is_deleted', 0)
                ->orderBy('title')
                ->get(['id', 'title'])
                ->map(fn ($service) => ['id' => (int) $service->id, 'name' => $service->title])
                ->values(),
            'selectUrl' => route('frontend4.service-selection.store'),
            'logoutUrl' => route('frontend4.logout'),
        ]);
    }

    public function selectService(Request $request, AccessContext $context, AuthenticationSecurityService $security)
    {
        $data = $request->validate(['service_id' => ['required', 'integer']]);
        $user = Auth::guard('frontend4')->user();
        abort_unless($user instanceof Frontend4User, 403);

        $organisationId = $context->organisationId();
        $serviceId = (int) $data['service_id'];
        if (! $context->validContext($user, $organisationId, $serviceId)) {
            $security->record($request, 'service_selection_denied', false, $user, $user->user_name, [
                'organisation_id' => $organisationId,
                'requested_service_id' => $serviceId,
            ]);

            abort(403, 'You do not have access to that service.');
        }

        return $this->completeServiceSelection(
            $request,
            $context,
            $security,
            $user,
            $organisationId,
            $serviceId,
            false
        );
    }

    public function logout(Request $request, AuthenticationSecurityService $security, AccessContext $context)
    {
        $user = Auth::guard('frontend4')->user();
        if ($user) {
            $security->record($request, 'logout', true, $user, $user->user_name, [
                'organisation_id' => $context->organisationId(),
                'service_id' => $context->serviceId(),
                'location_id' => $context->locationId(),
            ]);
        }

        Auth::guard('frontend4')->logout();
        $context->forgetSession();
        $request->session()->forget([
            'frontend4.last_activity',
            'frontend4.intended',
            'frontend4.pending_organisation_id',
            'frontend4.pending_organisation_name',
            'frontend4.pending_organisation_at',
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

    public function sendResetLink(Request $request, AuthenticationSecurityService $security, AccessContext $context)
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255'],
        ]);
        $adminId = $this->organisationId($data['company_name']);
        $user = $adminId
            ? Frontend4User::where('email', $data['email'])
                ->where('status', 1)
                ->where('is_deleted', 0)
                ->get()
                ->first(fn (Frontend4User $candidate) => $context->allowedServiceIds($candidate, $adminId) !== [])
            : null;

        if ($user && app(RoleResolver::class)->hasAccess($user)) {
            try {
                $token = $security->issuePasswordToken($user, $request, [
                    'organisation_id' => $adminId,
                ]);
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
            $security->record($request, 'password_reset_requested', true, null, $data['email'], [
                'organisation_id' => $adminId,
            ]);
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

    private function completeServiceSelection(
        Request $request,
        AccessContext $context,
        AuthenticationSecurityService $security,
        Frontend4User $user,
        int $organisationId,
        int $serviceId,
        bool $automatic
    ) {
        abort_unless($context->validContext($user, $organisationId, $serviceId), 403);

        $context->putSession($user, $organisationId, $serviceId);
        $security->record($request, 'service_selected', true, $user, $user->user_name, [
            'organisation_id' => $organisationId,
            'service_id' => $serviceId,
            'automatic' => $automatic,
        ]);

        $intended = $request->session()->pull('frontend4.intended');
        $frontend4Base = url('/frontend4');

        return is_string($intended) && Str::startsWith($intended, $frontend4Base)
            ? redirect()->to($intended)
            : redirect()->route('frontend4.today');
    }

    private function endFrontend4Session(Request $request, AccessContext $context): void
    {
        Auth::guard('frontend4')->logout();
        $context->forgetSession();
        $request->session()->forget([
            'frontend4.last_activity',
            'frontend4.intended',
            'frontend4.pending_organisation_id',
            'frontend4.pending_organisation_name',
            'frontend4.pending_organisation_at',
        ]);
        $request->session()->regenerateToken();
    }

    private function useF4Layout(): void
    {
        Inertia::setRootView('f4');
    }

    private function pendingOrganisationId(Request $request): int
    {
        $selectedAt = (int) $request->session()->get('frontend4.pending_organisation_at', 0);
        if ($selectedAt <= 0 || (time() - $selectedAt) > 600) {
            $this->forgetPendingOrganisation($request);

            return 0;
        }

        return (int) $request->session()->get('frontend4.pending_organisation_id', 0);
    }

    private function forgetPendingOrganisation(Request $request): void
    {
        $request->session()->forget([
            'frontend4.pending_organisation_id',
            'frontend4.pending_organisation_name',
            'frontend4.pending_organisation_at',
        ]);
    }

    private function organisationId(string $companyName): ?int
    {
        $identifier = Str::lower(trim($companyName));
        $query = Admin::where('is_deleted', 0)
            ->where(function ($query) use ($identifier) {
                $query->whereRaw('LOWER(company) = ?', [$identifier]);
                if (Schema::hasColumn('admin', 'frontend4_slug')) {
                    $query->orWhereRaw('LOWER(frontend4_slug) = ?', [$identifier]);
                }
            });
        $ids = $query->limit(2)->pluck('id');

        // A display name is usable only when it identifies exactly one
        // organisation. Duplicate names must use the unique Frontend 4 slug.
        return $ids->count() === 1 ? (int) $ids->first() : null;
    }

    private function failureMessage(): string
    {
        return 'We could not sign you in with those details.';
    }
}
