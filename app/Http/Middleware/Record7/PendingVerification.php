<?php

namespace App\Http\Middleware\Record7;

use App\Models\Record7\User;
use App\Services\Record7\SessionManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Partial authentication: the password was right, but the second factor has
 * not been given yet.
 *
 * The person is NOT signed in at this point — nothing is on the record7 guard,
 * only a pending user id in the session, and it expires. That is deliberate:
 * a half-finished sign-in must not be able to reach anything.
 */
class PendingVerification
{
    /** How long the verification step stays open. */
    public const WINDOW_SECONDS = 300;

    public function handle(Request $request, Closure $next): Response
    {
        $userId = (int) $request->session()->get(SessionManager::PENDING_USER, 0);
        $startedAt = (int) $request->session()->get(SessionManager::PENDING_AT, 0);

        if ($userId <= 0 || $startedAt <= 0 || (time() - $startedAt) > self::WINDOW_SECONDS) {
            $request->session()->forget([
                SessionManager::PENDING_USER,
                SessionManager::PENDING_ORG,
                SessionManager::PENDING_AT,
            ]);

            return redirect()->route('record7.login')
                ->with('error', 'That took too long. Please sign in again.');
        }

        if (! User::find($userId)) {
            $request->session()->forget([SessionManager::PENDING_USER, SessionManager::PENDING_AT]);

            return redirect()->route('record7.login');
        }

        return $next($request);
    }
}
