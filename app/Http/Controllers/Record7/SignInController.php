<?php

namespace App\Http\Controllers\Record7;

use App\Models\Record7\Organisation;
use App\Models\Record7\User;
use App\Services\Record7\AuditRecorder;
use App\Services\Record7\AuthenticationService;
use App\Services\Record7\OrganisationDirectory;
use App\Services\Record7\SessionManager;
use App\Services\Record7\VerificationPolicy;
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
        private readonly AuditRecorder $audit,
        private readonly VerificationPolicy $verification
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

        // Shown once, then cleared: the credentials were right but the account
        // cannot be used. Nothing identifying travels with it.
        $unavailable = (bool) $request->session()->pull('record7.unavailable', false);

        $organisation = $this->pendingOrganisation($request);

        return Inertia::render('Auth/SignIn', [
            'step' => $unavailable ? 'unavailable' : ($organisation ? 'credentials' : 'organisation'),
            'organisationName' => $unavailable ? null : $organisation?->display_name,
            'supportUrl' => config('record7.support_url'),
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
        $data = $request->validate(
            ['organisation' => ['required', 'string', 'max:255']],
            [
                'organisation.required' => 'Enter your organisation name to continue.',
                'organisation.max' => 'That is too long to be an organisation name.',
            ]
        );

        // Step one is rate limited on its own, so wrong organisation names do
        // not eat the sign-in allowance the person needs a moment later.
        $organisationThrottle = 'record7-organisation:'.$request->ip();

        if (RateLimiter::tooManyAttempts($organisationThrottle, 12)) {
            return back()->withInput()->with(
                'error',
                'That is several organisation names in a row. Please wait a minute and try again.'
            );
        }

        $organisation = $this->directory->match($data['organisation']);

        if (! $organisation) {
            RateLimiter::hit($organisationThrottle, 60);
            $this->audit->record(
                'organisation_not_recognised', AuditRecorder::FAILURE, null, null, null,
                'An unrecognised organisation name was entered.', 'low',
                // The typed name is not stored; only its length, which is enough
                // to tell a typo from an attempt to enumerate.
                ['typed_length' => mb_strlen($data['organisation'])], $request
            );

            /*
             * AN HONEST MESSAGE HERE, A DELIBERATELY VAGUE ONE LATER.
             *
             * At this point nobody has offered a credential, only an
             * organisation name, which is a business name rather than a secret.
             * Telling someone their sign-in details are wrong before they have
             * given any is confusing, and sends them off to check a password
             * that was never the problem.
             *
             * The vagueness starts at the next step, where saying WHICH of the
             * username and password was wrong would let somebody discover who
             * works for an organisation. That is the enumeration that matters.
             */
            return back()->withInput()->with(
                'error',
                'We do not recognise that organisation name. Check the spelling with your '
                .'manager. Capital letters and extra spaces do not matter, but the name itself '
                .'must match.'
            );
        }

        RateLimiter::clear($organisationThrottle);

        $request->session()->put([
            SessionManager::PENDING_ORG => $organisation->id,
            SessionManager::PENDING_AT => time(),
        ]);

        return redirect()->route('record7.login');
    }

    /* ── 0.2 Credentials ─────────────────────────────────────────────────── */

    public function credentials(Request $request)
    {
        $data = $request->validate(
            [
                'username' => ['required', 'string', 'max:255'],
                'password' => ['required', 'string', 'max:1024'],
            ],
            [
                'username.required' => 'Enter your username.',
                'password.required' => 'Enter your password.',
            ]
        );

        $organisation = $this->pendingOrganisation($request);

        if (! $organisation) {
            return redirect()->route('record7.login')
                ->with('error', 'Please enter your organisation again.');
        }

        $throttle = 'record7-signin:'.hash('sha256',
            $organisation->id.'|'.Str::lower(trim($data['username'])).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttle, AuthenticationService::MAX_ATTEMPTS)) {
            // Telling somebody who is rate limited that their details are wrong
            // is simply untrue, and they will keep trying. Tell them to wait.
            return back()->withInput($request->except('password'))->with(
                'error',
                'Too many sign-in attempts for that username. Please wait '
                .AuthenticationService::LOCK_MINUTES
                .' minutes and try again, or reset your password.'
            );
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

        // THE PASSWORD IS CHECKED FIRST, AND THAT ORDER MATTERS.
        //
        // A wrong password gets the same answer whatever state the account is
        // in, so nobody can type a username with junk and learn whether that
        // account exists, is suspended or is locked. Only once the password is
        // proven correct is anything said about the account — and at that point
        // the person already holds valid credentials, so telling them their
        // access is unavailable reveals nothing they could not already reach.
        if (! $this->auth->passwordMatches($user, $data['password'])) {
            RateLimiter::hit($throttle, AuthenticationService::LOCK_MINUTES * 60);
            $this->auth->registerFailure($user);
            $this->audit->record(
                'sign_in', AuditRecorder::FAILURE, $user, $organisation->id, null,
                'Incorrect password.', 'low',
                ['failed_attempts' => $user->failed_attempts, 'account_status' => $user->account_status],
                $request
            );

            return back()->withInput($request->except('password'))
                ->with('error', $this->auth->failureMessage());
        }

        // 0.8 — the credentials are right, but the account cannot be used.
        //
        // Telling this person their password was wrong would be a lie, and it
        // sends them round a password-reset loop that cannot fix anything. They
        // are told plainly that access is unavailable and who can help.
        //
        // What they are NOT told: which house they were assigned to, what role
        // they held, what they could do, or that the word is "suspended". The
        // precise reason goes to the access audit, where a manager can read it.
        if ($refusal = $user->accessRefusalReason()) {
            RateLimiter::hit($throttle, AuthenticationService::LOCK_MINUTES * 60);
            $this->audit->record(
                'sign_in', AuditRecorder::DENIED, $user, $organisation->id, null,
                $this->refusalReason($refusal),
                in_array($refusal, ['suspended', 'security_locked'], true) ? 'high' : 'medium',
                [
                    'account_status' => $user->account_status,
                    'refusal' => $refusal,
                    'credentials_were_correct' => true,
                ],
                $request
            );

            $this->forgetPendingOrganisation($request);
            $request->session()->put('record7.unavailable', true);

            return redirect()->route('record7.login');
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

        // WHETHER to verify is a policy decision, not "does this account have
        // a method configured". Asking on every sign-in trains people to type
        // the code without reading it; asking at the moments that matter keeps
        // it meaningful. See VerificationPolicy.
        $sharedDevice = $request->boolean('shared_device');
        $reason = $this->verification->reasonToVerify($user, $request, $sharedDevice);

        if ($reason === null) {
            $this->verification->record(
                $user,
                $this->auth->verificationStepEnabled() ? 'trusted_device' : 'step_disabled',
                'skipped',
                null,
                $request
            );

            return $this->completeSignIn($request, $user, $organisation, false);
        }

        $this->verification->record($user, $reason, 'required', null, $request);

        $request->session()->put([
            'record7.verify_reason' => $reason,
            'record7.shared_device' => $sharedDevice,
        ]);

        return redirect()->route('record7.verify');
    }

    /* ── 0.3 Security verification ───────────────────────────────────────── */

    public function showVerification(Request $request)
    {
        $this->useR7Layout($request);

        $user = User::find($request->session()->get(SessionManager::PENDING_USER));
        $method = $user ? $this->auth->primaryMethod($user) : null;

        $reason = (string) $request->session()->get('record7.verify_reason', 'new_device');

        return Inertia::render('Auth/Verify', [
            'prompt' => $method?->prompt() ?? 'Verify your identity',
            'methodLabel' => $method?->label,
            // Why this is being asked for. People comply with a control they
            // understand and work around one that seems arbitrary.
            'reason' => $this->verification->explain($reason),
            'canTrustDevice' => $reason !== 'shared_device'
                && ! $request->session()->get('record7.shared_device', false),
            'recoveryCodesLeft' => $user ? $this->verification->unusedRecoveryCodeCount($user) : 0,
            'verifyUrl' => route('record7.verify.check'),
            'cancelUrl' => route('record7.login', ['change_organisation' => 1]),
            // Shown on screen ONLY when the fictional code is actually enabled,
            // so a real deployment never advertises one.
            'prototypeCode' => $this->auth->prototypeCode(),
            'error' => session('error'),
        ]);
    }

    public function verify(Request $request)
    {
        $data = $request->validate(
            ['code' => ['required', 'string', 'max:32']],
            ['code.required' => 'Enter the six-digit code, or one of your recovery codes.']
        );

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

            return redirect()->route('record7.login')->with(
                'error',
                'Too many verification attempts. Please sign in again, and use a recovery code '
                .'if you cannot reach your usual method.'
            );
        }

        if (! $this->auth->verifyCode($user, $data['code'])) {
            RateLimiter::hit($throttle, 300);
            $this->audit->record(
                'verification', AuditRecorder::FAILURE, $user, $organisation->id, null,
                'Incorrect verification code.', 'medium', [], $request
            );
            $this->verification->record($user, (string) $request->session()->get('record7.verify_reason', 'new_device'), 'failed', 'code', $request);

            return back()->with(
                'error',
                'That code was not correct. Check your authenticator, or enter one of your '
                .'recovery codes instead.'
            );
        }

        RateLimiter::clear($throttle);

        $reason = (string) $request->session()->get('record7.verify_reason', 'new_device');
        $shared = (bool) $request->session()->get('record7.shared_device', false)
            || $request->boolean('shared_device');

        $this->verification->record($user, $reason, 'passed', 'code', $request);

        // A shared trolley tablet is recognised but NEVER trusted, so the next
        // person to pick it up is asked for their own code rather than
        // inheriting this one.
        $this->verification->rememberDevice($user, $request, $shared);

        $request->session()->forget(['record7.verify_reason', 'record7.shared_device']);

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

        $request->validate(
            ['password' => ['required', 'confirmed', Password::min(12)->letters()->numbers()]],
            $this->passwordMessages()
        );

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
        $data = $request->validate(
            [
                'organisation' => ['required', 'string', 'max:255'],
                'username' => ['required', 'string', 'max:255'],
            ],
            [
                'organisation.required' => 'Enter your organisation name.',
                'username.required' => 'Enter your username.',
            ]
        );

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

        $request->validate(
            ['password' => ['required', 'confirmed', Password::min(12)->letters()->numbers()]],
            $this->passwordMessages()
        );

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

    /** Password rules, written for a person rather than for a validator. */
    private function passwordMessages(): array
    {
        return [
            'password.required' => 'Choose a password.',
            'password.confirmed' => 'The two passwords do not match. Enter the same one twice.',
            'password.min' => 'Use at least twelve characters.',
            'password.letters' => 'Include at least one letter.',
            'password.numbers' => 'Include at least one number.',
        ];
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
