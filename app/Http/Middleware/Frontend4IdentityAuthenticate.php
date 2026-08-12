<?php

namespace App\Http\Middleware;

use App\Admin;
use App\Models\Frontend4User;
use App\Services\Frontend4\AccessContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/** Allows password-verified users to select a service without clinical access. */
class Frontend4IdentityAuthenticate
{
    public function __construct(private readonly AccessContext $context)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('frontend4');
        $user = $guard->user();

        if (! $guard->check() || ! $user instanceof Frontend4User) {
            return $this->unauthenticated($request);
        }

        $organisationId = $this->context->organisationId();
        $organisationIsActive = Admin::whereKey($organisationId)->where('is_deleted', 0)->exists();
        if (
            (int) $user->status !== 1
            || (int) $user->is_deleted !== 0
            || ! $organisationIsActive
            || ! $this->context->belongsToOrganisation($user, $organisationId)
        ) {
            $this->endSession($request);

            return redirect()->route('frontend4.login')
                ->with('error', 'Your organisation access is no longer available. Please sign in again.');
        }

        $lastActivity = (int) $request->session()->get('frontend4.last_activity', 0);
        $idleSeconds = max(1, (int) config('frontend4_auth.idle_minutes')) * 60;
        if ($lastActivity && (time() - $lastActivity) > $idleSeconds) {
            $this->endSession($request);

            return redirect()->route('frontend4.login')
                ->with('status', 'Your Care One OS session expired. Please sign in again.');
        }

        $request->session()->put('frontend4.last_activity', time());
        Auth::shouldUse('frontend4');

        return $next($request);
    }

    private function unauthenticated(Request $request): Response
    {
        if ($request->expectsJson()) {
            abort(401, 'Authentication required.');
        }

        return redirect()->route('frontend4.login');
    }

    private function endSession(Request $request): void
    {
        Auth::guard('frontend4')->logout();
        $this->context->forgetSession();
        $request->session()->forget([
            'frontend4.last_activity',
            'frontend4.intended',
            'frontend4.pending_organisation_id',
            'frontend4.pending_organisation_name',
            'frontend4.pending_organisation_at',
        ]);
        $request->session()->regenerateToken();
    }
}
