<?php

namespace App\Http\Controllers\Record7;

use App\Models\Record7\Organisation;
use App\Models\Record7\User;
use App\Services\Record7\AuditRecorder;
use App\Services\Record7\AuthenticationService;
use App\Services\Record7\OrganisationDirectory;
use App\Services\Record7\SessionManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

/**
 * Record7 sign-in: Sections 0.1, 0.2, 0.3, 0.6, 0.7 and the refusal half of 0.8.
 *
 * THE JOURNEY
 *   1. Organisation      one field, forgiving match, no list
 *   2. Username/password checked inside that organisation only
 *   3. Verification      second factor before the session begins
 *   then house selection, which lives in HouseController.
 *
 * Nothing about the person is revealed before all three steps pass — not
 * whether the username exists, not which houses they work in, not why a
 * suspended account was refused.
 */
class SignInController extends R7Controller
{
    private const PENDING_ORG_SECONDS = 600;

    public function __construct(
        private readonly OrganisationDirectory $directory,
        private readonly AuthenticationService $auth,
        private readonly SessionManager $sessions,
        private readonly AuditRecorder $audit
    ) {
    }

    /* ── 0.1 Organisation ────────────────────────────────────────────────── */

    public function show(Request $request)
    {
        $this->useR7Layout($request);

        if (Auth::guard('record7')->check()) {
            return redirect()->route('record7.houses');
        }

        if ($request->boolean('change_organisation')) {
            $this->forgetPendingOrganisation($request);
        }

        $organisation = $this->pendingOrganisation($request);

        return Inertia::render('Auth/SignIn', [
            'step' => $organisation ? 'credentials' : 'organisation',
            'organisationName' => $organisation?->display_name,
            'urls' => [
                'organisation' => route('record7.login.organisation'),
                'credentials' => route('record7.login.credentials'),
                'change' => route('record7.login', ['change_organisation' => 1]),
                'forgot' => route('record7.password.request'),
            ],
            'status' => session('status'),
            'error' => session('error'),
        ]);
    }

    public function chooseOrganisation(Request $request)
    {
        $data = $request->validate(['organisation' => ['required', 'string', 'max:255']]);

        $organisation = $this->directory->match($data['organisation']);

        if (! $organisation) {
            $this->audit->record(
                'organisation_not_recognised', AuditRecorder::FAILURE, null, null, null,
                'An unrecognised organisation name was entered.', 'low',
                // The typed name is not stored; only its length, which is enough
                // to tell a typo from an attempt to enumerate.
                ['typed_length' => mb_strlen($data['organisation'])], $request
            );

            return back()->withInput()->with('error', $this->auth->failureMessage());
        }

        $request->session()->put([
            SessionManager::PENDING_ORG => $organisation->id,
            SessionManager::PENDING_AT => time(),
        ]);

        return redirect()->route('record7.login');
    }

    /* ── 0.2 Credentials ─────────────────────────────────────────────────── */

    public function credentials(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:1024'],
        ]);

        $organisation = $this->pendingOrganisation($request);

        if (! $organisation) {
            return redirect()->route('record7.login')
                ->with('error', 'Please enter your organisation again.');
        }

        $throttle = 'record7-signin:'.hash('sha256',
            $organisation->id.'|'.Str::lower(trim($data['username'])).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttle, AuthenticationService::MAX_ATTEMPTS)) {
            return back()->withInput($request->except('password'))
                ->with('error', $this->auth->failureMessage());
        }

        $user = $this->auth->findUser($organisation, $data['username']);

        if (! $user) {
            RateLimiter::hit($throttle, AuthenticationService::LOCK_MINUTES * 60);
            $this->audit->record(
                'sign_in', AuditRecorder::FAILURE, null, $organisation->id, null,
                'No such account in this organisation.', 'low',
                ['username_hash' => hash('sha256', Str::lower(trim($data['username'])))], $request
            );

            return back()->withInput($request->except('password'))
                ->with('error', $this->auth->failureMessage());
        }

        // A lock that has served its time is released before it is checked, so
        // a lock is a delay rather than a permanent state needing an admin.
        $this->auth->releaseExpiredLock($user);

        // 0.8 — every restricted state is refused here, before anything about
        // the person's houses is looked up, let alone shown.
        if ($refusal = $user->accessRefusalReason()) {
            RateLimiter::hit($throttle, AuthenticationService::LOCK_MINUTES * 60);
            $this->audit->record(
                'sign_in', AuditRecorder::DENIED, $user, $organisation->id, null,
                $this->refusalReason($refusal),
                in_array($refusal, ['suspended', 'security_locked'], true) ? 'high' : 'medium',
                ['account_status' => $user->account_status, 'refusal' => $refusal], $request
            );

            return back()->withInput($request->except('password'))
                ->with('error', $this->auth->failureMessage());
        }

        if (! $this->auth->passwordMatches($user, $data['password'])) {
            RateLimiter::hit($throttle, AuthenticationService::LOCK_MINUTES * 60);
            $this->auth->registerFailure($user);
            $this->audit->record(
                'sign_in', AuditRecorder::FAILURE, $user, $organisation->id, null,
                'Incorrect password.', 'low',
                ['failed_attempts' => $user->failed_attempts], $request
            );

            return back()->withInput($request->except('password'))
                ->with('error', $this->auth->failureMessage());
        }

        RateLimiter::clear($throttle);

        // Password proven. Hold the person in partial authentication — NOT
        // signed in — until the second factor is given.
        $request->session()->put([
            SessionManager::PENDING_USER => $user->id,
            SessionManager::PENDING_ORG => $organisation->id,
            SessionManager::PENDING_AT => time(),
        ]);

        $this->audit->record(
            'password_verified', AuditRecorder::SUCCESS, $user, $organisation->id, null,
            null, 'none', [], $request
        );

        if (! $this->auth->requiresVerification($user)) {
            return $this->completeSignIn($request, $user, $organisation, false);
        }

        return redirect()->route('record7.verify');
    }

    /* ── 0.3 Security verification ───────────────────────────────────────── */

    public function showVerification(Request $request)
    {
        $this->useR7Layout($request);

        $user = User::find($request->session()->get(SessionManager::PENDING_USER));
        $method = $user ? $this->auth->primaryMethod($user) : null;

        return Inertia::render('Auth/Verify', [
            'prompt' => $method?->prompt() ?? 'Verify your identity',
            'methodLabel' => $method?->label,
            'verifyUrl' => route('record7.verify.check'),
            'cancelUrl' => route('record7.login', ['change_organisation' => 1]),
            // Shown on screen only when a prototype code is configured, so a
            // real deployment never advertises one.
            'prototypeCode' => $this->auth->prototypeCode(),
            'error' => session('error'),
        ]);
    }

    public function verify(Request $request)
    {
        $data = $request->validate(['code' => ['required', 'string', 'max:32']]);

        $user = User::find($request->session()->get(SessionManager::PENDING_USER));
        $organisation = Organisation::find($request->session()->get(SessionManager::PENDING_ORG));

        if (! $user || ! $organisation) {
            return redirect()->route('record7.login');
        }

        $throttle = 'record7-verify:'.$user->id.'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttle, AuthenticationService::MAX_ATTEMPTS)) {
            $this->audit->record(
                'verification', AuditRecorder::DENIED, $user, $organisation->id, null,
                'Too many verification attempts.', 'high', [], $request
            );
            $request->session()->forget([SessionManager::PENDING_USER, SessionManager::PENDING_AT]);

            return redirect()->route('record7.login')->with('error', $this->auth->failureMessage());
        }

        if (! $this->auth->verifyCode($user, $data['code'])) {
            RateLimiter::hit($throttle, 300);
            $this->audit->record(
                'verification', AuditRecorder::FAILURE, $user, $organisation->id, null,
                'Incorrect verification code.', 'medium', [], $request
            );

            return back()->with('error', 'That code was not correct.');
        }

        RateLimiter::clear($throttle);

        return $this->completeSignIn($request, $user, $organisation, true);
    }

    /* ── 0.6 First-time activation ───────────────────────────────────────── */

    public function showActivation(Request $request, string $token)
    {
        $this->useR7Layout($request);

        $invitation = $this->auth->findInvitation($token);

        return Inertia::render('Auth/Activate', [
            'valid' => $invitation !== null,
            'name' => $invitation?->user?->displayName(),
            'organisationName' => $invitation?->user?->organisation?->display_name,
            'activateUrl' => route('record7.activate.store', ['token' => $token]),
            'signInUrl' => route('record7.login'),
            'error' => session('error'),
        ]);
    }

    public function activate(Request $request, string $token)
    {
        $invitation = $this->auth->findInvitation($token);

        if (! $invitation) {
            $this->audit->record(
                'activation', AuditRecorder::FAILURE, null, null, null,
                'An expired or unknown activation link was used.', 'medium', [], $request
            );

            return redirect()->route('record7.activate.show', ['token' => $token])
                ->with('error', 'That activation link is no longer valid. Ask your manager to send a new one.');
        }

        $request->validate([
            'password' => ['required', 'confirmed', Password::min(12)->letters()->numbers()],
        ]);

        $user = $this->auth->activate($invitation, $request->input('password'));

        $this->audit->record(
            'activation', AuditRecorder::SUCCESS, $user, $user->organisation_id, null,
            'Account activated and first password set.', 'none', [], $request
        );

        return redirect()->route('record7.login')
            ->with('status', 'Your account is ready. Please sign in.');
    }

    /* ── 0.7 Password recovery ───────────────────────────────────────────── */

    public function showForgotPassword(Request $request)
    {
        $this->useR7Layout($request);

        return Inertia::render('Auth/ForgotPassword', [
            'submitUrl' => route('record7.password.email'),
            'signInUrl' => route('record7.login'),
            'status' => session('status'),
            // Local prototype only: the reset link is shown on screen because
            // there is no mail transport in a development environment.
            'localLink' => session('record7_local_reset_link'),
        ]);
    }

    public function sendResetLink(Request $request)
    {
        $data = $request->validate([
            'organisation' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255'],
        ]);

        $organisation = $this->directory->match($data['organisation']);
        $user = $organisation ? $this->auth->findUser($organisation, $data['username']) : null;

        // The same answer either way. Confirming that an account exists would
        // turn this form into an account-enumeration tool.
        $response = redirect()->route('record7.password.request')->with(
            'status',
            'If those details match an account, a reset link has been sent to its work email address.'
        );

        if (! $user || $user->account_status === 'suspended') {
            $this->audit->record(
                'password_reset_requested', AuditRecorder::FAILURE, $user, $organisation?->id, null,
                'Reset requested for an unknown or suspended account.', 'low', [], $request
            );

            return $response;
        }

        $token = $this->auth->issueReset($user);

        $this->audit->record(
            'password_reset_requested', AuditRecorder::SUCCESS, $user, $organisation->id, null,
            null, 'none', [], $request
        );

        if (! app()->environment('production')) {
            $response->with('record7_local_reset_link', route('record7.password.reset', ['token' => $token]));
        }

        return $response;
    }

    public function showResetPassword(Request $request, string $token)
    {
        $this->useR7Layout($request);

        return Inertia::render('Auth/ResetPassword', [
            'valid' => $this->auth->findReset($token) !== null,
            'resetUrl' => route('record7.password.update', ['token' => $token]),
            'requestUrl' => route('record7.password.request'),
            'signInUrl' => route('record7.login'),
            'error' => session('error'),
        ]);
    }

    public function resetPassword(Request $request, string $token)
    {
        $reset = $this->auth->findReset($token);

        if (! $reset) {
            return redirect()->route('record7.password.reset', ['token' => $token])
                ->with('error', 'That reset link has expired. Please request a new one.');
        }

        $request->validate([
            'password' => ['required', 'confirmed', Password::min(12)->letters()->numbers()],
        ]);

        $user = $this->auth->completeReset($reset, $request->input('password'));

        $this->audit->record(
            'password_reset_completed', AuditRecorder::SUCCESS, $user, $user->organisation_id, null,
            'Password reset completed, and any security lock cleared.', 'none', [], $request
        );

        return redirect()->route('record7.login')
            ->with('status', 'Your password has been changed. Please sign in.');
    }

    /* ── Shared ──────────────────────────────────────────────────────────── */

    private function completeSignIn(Request $request, User $user, Organisation $organisation, bool $verified)
    {
        $this->auth->registerSuccess($user);

        $request->session()->forget([
            SessionManager::PENDING_USER,
            SessionManager::PENDING_ORG,
            SessionManager::PENDING_AT,
        ]);

        $session = $this->sessions->start($request, $user, $organisation->id);

        $this->audit->record(
            'sign_in', AuditRecorder::SUCCESS, $user, $organisation->id, null,
            null, 'none', ['verified' => $verified], $request, $session
        );

        return redirect()->route('record7.houses');
    }

    private function pendingOrganisation(Request $request): ?Organisation
    {
        $startedAt = (int) $request->session()->get(SessionManager::PENDING_AT, 0);

        if ($startedAt <= 0 || (time() - $startedAt) > self::PENDING_ORG_SECONDS) {
            $this->forgetPendingOrganisation($request);

            return null;
        }

        $organisation = Organisation::find($request->session()->get(SessionManager::PENDING_ORG));

        if (! $organisation || ! $organisation->isActive()) {
            $this->forgetPendingOrganisation($request);

            return null;
        }

        return $organisation;
    }

    private function forgetPendingOrganisation(Request $request): void
    {
        $request->session()->forget([
            SessionManager::PENDING_ORG,
            SessionManager::PENDING_AT,
            SessionManager::PENDING_USER,
        ]);
    }

    /** Plain wording for the audit trail. Never shown to the person. */
    private function refusalReason(string $refusal): string
    {
        return match ($refusal) {
            'invited' => 'Account has not been activated yet.',
            'suspended' => 'Account is suspended.',
            'inactive' => 'Account is closed.',
            'access_expired' => 'Access period has ended.',
            'security_locked' => 'Account is locked after failed sign-in attempts.',
            default => 'Account cannot sign in.',
        };
    }
}
