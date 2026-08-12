<?php

namespace App\Http\Middleware;

use App\Home;
use App\Models\Frontend4User;
use App\Services\Frontend4\AccessContext;
use App\Services\Frontend4\AuthenticationSecurityService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Frontend4Authenticate
{
    public function __construct(
        private readonly AccessContext $context,
        private readonly AuthenticationSecurityService $security
    ) {
    }

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

        if (! $user instanceof Frontend4User) {
            $guard->logout();
            $this->forgetFrontend4Session();
            abort(403, 'This account cannot use Care One OS.');
        }

        $serviceId = $this->context->serviceId();
        $organisationId = $this->context->organisationId();
        if ($organisationId <= 0 && $serviceId > 0) {
            // Upgrade sessions created before Requirement 4. The relationship is
            // still checked against the user's current service access below.
            $organisationId = (int) Home::whereKey($serviceId)->where('is_deleted', 0)->value('admin_id');
        }
        $locationId = $this->context->locationId();

        if (! $this->context->validContext($user, $organisationId, $serviceId, $locationId)) {
            $this->security->record($request, 'access_scope_denied', false, $user, null, [
                'organisation_id' => $organisationId ?: null,
                'service_id' => $serviceId ?: null,
                'location_id' => $locationId,
                'route' => $request->route()?->getName(),
            ]);
            $guard->logout();
            $this->forgetFrontend4Session();

            if ($request->expectsJson()) {
                abort(403, 'Your organisation or service access is no longer available.');
            }

            return redirect()->route('frontend4.login')
                ->with('error', 'Your organisation or service access is no longer available. Please sign in again.');
        }

        // Recalculate the allow-lists on every request. A removed assignment,
        // deleted service or moved service therefore takes effect immediately.
        $this->context->putSession($user, $organisationId, $serviceId, $locationId);

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
            'active_home_id' => $this->context->serviceId(),
            'allowed_home_ids' => session('frontend4.allowed_service_ids', []),
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
        $this->context->forgetSession();
        session()->forget([
            'frontend4.last_activity',
            'frontend4.intended',
        ]);
    }
}
