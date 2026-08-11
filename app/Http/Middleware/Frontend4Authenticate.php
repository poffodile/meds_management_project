<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Frontend4Authenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('frontend4');

        if (! $guard->check()) {
            if ($request->expectsJson()) {
                abort(401, 'Authentication required.');
            }

            if ($request->isMethod('get')) {
                session(['frontend4.intended' => $request->fullUrl()]);
            }

            return redirect()->route('frontend4.login');
        }

        $user = $guard->user();
        if ((int) $user->status !== 1 || (int) $user->is_deleted !== 0) {
            $guard->logout();
            $this->forgetFrontend4Session();

            return redirect()->route('frontend4.login')->with('error', 'This account is not available.');
        }

        $lastActivity = (int) session('frontend4.last_activity', 0);
        $idleSeconds = max(1, (int) config('frontend4_auth.idle_minutes')) * 60;
        if ($lastActivity && (time() - $lastActivity) > $idleSeconds) {
            $guard->logout();
            $this->forgetFrontend4Session();

            return redirect()->route('frontend4.login')->with('status', 'Your Care One OS session expired. Please sign in again.');
        }

        session(['frontend4.last_activity' => time()]);
        Auth::shouldUse('frontend4');

        // The medication services currently read the legacy session key. Make
        // Frontend 4's selected service visible only while this request runs,
        // then restore whatever the legacy frontend had stored.
        $legacy = [];
        foreach (['active_home_id', 'allowed_home_ids'] as $key) {
            $legacy[$key] = ['exists' => session()->has($key), 'value' => session($key)];
        }
        session([
            'active_home_id' => session('frontend4.active_home_id'),
            'allowed_home_ids' => session('frontend4.allowed_home_ids', []),
        ]);

        try {
            return $next($request);
        } finally {
            foreach ($legacy as $key => $state) {
                $state['exists'] ? session([$key => $state['value']]) : session()->forget($key);
            }
        }
    }

    private function forgetFrontend4Session(): void
    {
        session()->forget([
            'frontend4.active_home_id',
            'frontend4.allowed_home_ids',
            'frontend4.last_activity',
            'frontend4.intended',
        ]);
    }
}
