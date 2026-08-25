<?php

namespace App\Http\Middleware\Record7;

use App\Services\Record7\AuditRecorder;
use App\Services\Record7\SessionManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Record7's full gate: signed in, verified, scoped to a house, not locked.
 *
 * Re-checked on EVERY request rather than trusted from the session, because a
 * manager may suspend an account or end a placement while that person is
 * mid-round. The next thing they tap must stop.
 */
class Authenticate
{
    public function __construct(
        private readonly SessionManager $sessions,
        private readonly AuditRecorder $audit
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('record7');

        if (! $guard->check()) {
            return $this->toLogin($request);
        }

        $user = $this->sessions->user();

        if (! $user) {
            return $this->toLogin($request);
        }

        // The account may have changed since sign-in.
        $user->refresh();

        if ($refusal = $user->accessRefusalReason()) {
            $this->audit->record(
                'session_revoked', AuditRecorder::DENIED, $user,
                $this->sessions->organisationId($request), $this->sessions->serviceId($request),
                'Account is now '.$refusal, 'high', [], $request
            );
            $this->sessions->end($request, 'revoked');

            return redirect()->route('record7.login')
                ->with('error', 'Your access has changed. Please sign in again.');
        }

        // Gone for too long: the session is over, not merely locked.
        if ($this->sessions->shouldEnd($request)) {
            $this->sessions->end($request, 'expired');

            return redirect()->route('record7.login')
                ->with('status', 'You were signed out because the session was idle.');
        }

        // Idle for a few minutes on a shared device: lock, keep the house.
        if ($this->sessions->shouldAutoLock($request) && ! $this->sessions->isLocked($request)) {
            $this->sessions->lock($request, 'No activity for '.SessionManager::IDLE_LOCK_MINUTES.' minutes.');
        }

        if ($this->sessions->isLocked($request)) {
            if ($request->isMethod('get') && ! $request->expectsJson()) {
                $request->session()->put(SessionManager::INTENDED, $request->fullUrl());
            }

            return $request->expectsJson()
                ? abort(423, 'This screen is locked.')
                : redirect()->route('record7.lock');
        }

        // A house must be chosen before any clinical screen.
        if (! $this->sessions->serviceId($request)) {
            if ($request->isMethod('get') && ! $request->expectsJson()) {
                $request->session()->put(SessionManager::INTENDED, $request->fullUrl());
            }

            return redirect()->route('record7.houses');
        }

        $this->sessions->touch($request);

        return $next($request);
    }

    private function toLogin(Request $request): Response
    {
        if ($request->expectsJson()) {
            abort(401, 'Sign in to continue.');
        }

        if ($request->isMethod('get')) {
            $request->session()->put(SessionManager::INTENDED, $request->fullUrl());
        }

        return redirect()->route('record7.login');
    }
}
