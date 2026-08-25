<?php

namespace App\Http\Middleware\Record7;

use App\Services\Record7\SessionManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Signed in and verified, but no house chosen yet.
 *
 * The gate for choosing a house, locking, and signing out — the few things a
 * person can do before they are scoped to somewhere.
 */
class Identity
{
    public function __construct(private readonly SessionManager $sessions)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('record7')->check() || ! ($user = $this->sessions->user())) {
            return $request->expectsJson()
                ? abort(401, 'Sign in to continue.')
                : redirect()->route('record7.login');
        }

        $user->refresh();

        if ($user->accessRefusalReason()) {
            $this->sessions->end($request, 'revoked');

            return redirect()->route('record7.login')
                ->with('error', 'Your access has changed. Please sign in again.');
        }

        if ($this->sessions->shouldEnd($request)) {
            $this->sessions->end($request, 'expired');

            return redirect()->route('record7.login')
                ->with('status', 'You were signed out because the session was idle.');
        }

        return $next($request);
    }
}
