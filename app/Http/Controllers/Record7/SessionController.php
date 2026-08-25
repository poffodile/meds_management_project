<?php

namespace App\Http\Controllers\Record7;

use App\Services\Record7\AuditRecorder;
use App\Services\Record7\AuthenticationService;
use App\Services\Record7\SessionManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

/**
 * Locking, unlocking and signing out.
 *
 * Section 0.9.
 *
 * WHY LOCKING IS NOT SIGNING OUT
 * On a medicines round a tablet is put down and picked up constantly, often by
 * the same person, sometimes by a colleague. Signing out every time would mean
 * re-entering the organisation, the username, the password and the house — so
 * in practice nobody would lock anything, and the device would sit unlocked on
 * a trolley. Locking keeps the identity and the house and asks only for the
 * password, which is quick enough that people actually do it.
 *
 * Signing out is the deliberate end: the session closes and the house clears.
 */
class SessionController extends R7Controller
{
    public function __construct(
        private readonly SessionManager $sessions,
        private readonly AuthenticationService $auth,
        private readonly AuditRecorder $audit
    ) {
    }

    /** The lock screen. */
    public function showLock(Request $request)
    {
        // Deliberately WITHOUT the request, so the shared context — which
        // carries the permission list — is not published to a locked screen.
        // A locked device should say who it belongs to and which house it is
        // open on, and nothing else about what that person may do.
        $this->useR7Layout();

        $user = $this->user();

        if (! $user) {
            return redirect()->route('record7.login');
        }

        // Reaching the lock screen while unlocked means the person chose to
        // lock; honour it rather than bouncing them back.
        if (! $this->sessions->isLocked($request)) {
            $this->sessions->lock($request);
        }

        $context = $this->contextProps($request);

        return Inertia::render('Lock', [
            'name' => $user->displayName(),
            'fullName' => $user->full_name,
            'house' => $context['house'] ?? null,
            'organisationName' => $context['organisation'] ?? null,
            'unlockUrl' => route('record7.unlock'),
            'signOutUrl' => route('record7.signout'),
            'error' => session('error'),
        ]);
    }

    /** Lock deliberately, from a button. */
    public function lock(Request $request)
    {
        $this->sessions->lock($request);

        return redirect()->route('record7.lock');
    }

    /**
     * Unlock with the password.
     *
     * The second factor is not asked for again: the person never left the
     * session, and the device is the one already verified.
     */
    public function unlock(Request $request)
    {
        $data = $request->validate(['password' => ['required', 'string', 'max:1024']]);

        $user = $this->user();

        if (! $user) {
            return redirect()->route('record7.login');
        }

        if (! $this->auth->passwordMatches($user, $data['password'])) {
            $this->auth->registerFailure($user);

            $this->audit->record(
                'unlock', AuditRecorder::FAILURE, $user,
                $this->sessions->organisationId($request), $this->sessions->serviceId($request),
                'Incorrect password at the lock screen.', 'medium',
                ['failed_attempts' => $user->failed_attempts], $request,
                $this->sessions->current($request)
            );

            // Enough wrong attempts at a lock screen ends the session rather
            // than leaving a locked device someone is guessing at.
            if ($user->isLockedOut()) {
                $this->sessions->end($request, 'revoked');

                return redirect()->route('record7.login')
                    ->with('error', 'Too many attempts. Please sign in again.');
            }

            return back()->with('error', 'That password was not correct.');
        }

        $this->sessions->unlock($request);

        $intended = $request->session()->pull(SessionManager::INTENDED);

        return is_string($intended) && str_starts_with($intended, url('/record7'))
            ? redirect()->to($intended)
            : redirect()->route('record7.today');
    }

    public function signOut(Request $request)
    {
        $user = $this->user();

        if ($user) {
            $this->audit->record(
                'sign_out', AuditRecorder::SUCCESS, $user,
                $this->sessions->organisationId($request), $this->sessions->serviceId($request),
                null, 'none', [], $request, $this->sessions->current($request)
            );
        }

        $this->sessions->end($request);

        return redirect()->route('record7.login')
            ->with('status', 'You have signed out.');
    }
}
