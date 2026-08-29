<?php

namespace App\Http\Controllers\Record7;

use App\Http\Controllers\Controller;
use App\Models\Record7\Organisation;
use App\Models\Record7\Service;
use App\Models\Record7\User;
use App\Services\Record7\AccessPolicy;
use App\Services\Record7\AuthenticationService;
use App\Services\Record7\SessionManager;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Base for every Record7 controller.
 */
abstract class R7Controller extends Controller
{
    /**
     * Point Inertia at Record7's own root view, and replace the shared props
     * that were written for the legacy front ends.
     *
     * MUST be called inside an action, never a constructor: Laravel builds the
     * controller while gathering route middleware, so a constructor call would
     * run before HandleInertiaRequests resets the root view to 'app' and the
     * page would boot the wrong bundle and render blank.
     */
    protected function useR7Layout(?Request $request = null): void
    {
        Inertia::setRootView('r7');

        Inertia::share('product', [
            'name' => config('record7.product_name'),
            'strapline' => config('record7.product_strapline'),
            'seventhRight' => config('record7.seventh_right'),
        ]);

        // How many steps the sign-in journey actually has. With verification
        // switched off it is organisation, credentials, house — three. With it
        // on there are four. The screens must not disagree with each other:
        // "step 1 of 3" followed by "step 4 of 4" is the kind of small
        // incoherence that makes a product feel unfinished.
        Inertia::share('journeySteps', app(AuthenticationService::class)->verificationStepEnabled() ? 4 : 3);

        // The global shared props read the legacy web guard and run the legacy
        // home switcher, which queries a column Record7's schema does not have.
        // Overriding them here is scoped to this request and leaves
        // HandleInertiaRequests untouched for the other front ends.
        $user = $this->user();

        // Record7's own flash keys. The shared middleware exposes only
        // 'success' and 'error', which belong to the legacy front ends; these
        // are namespaced so the two can never be confused, and overriding here
        // leaves HandleInertiaRequests untouched for everybody else.
        Inertia::share('flash', [
            'r7.recorded' => fn () => session('r7.recorded'),
            'r7.error' => fn () => session('r7.error'),
        ]);

        Inertia::share('homes', null);
        Inertia::share('witnessPending', 0);
        Inertia::share('auth', [
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->displayName(),
                'fullName' => $user->full_name,
                'username' => $user->username,
            ] : null,
        ]);

        Inertia::share('context', $request ? $this->contextProps($request) : null);
    }

    protected function user(): ?User
    {
        return app(SessionManager::class)->user();
    }

    /** Organisation and house, for the persistent header. */
    protected function contextProps(Request $request): ?array
    {
        $sessions = app(SessionManager::class);
        $user = $this->user();

        if (! $user) {
            return null;
        }

        $service = $sessions->serviceId($request) ? Service::find($sessions->serviceId($request)) : null;
        $organisation = Organisation::find($sessions->organisationId($request));
        $policy = app(AccessPolicy::class);

        return [
            'organisation' => $organisation?->display_name,
            'house' => $service?->name,
            'houseId' => $service?->id,
            'role' => $user->primaryRole()?->name,
            'accessType' => $service
                ? $policy->usableAccess($user, $service->id)?->access_type
                : null,
            'can' => $policy->grantedPermissions($user, $service?->id),
            'houseCount' => count($policy->availableServices($user)),
        ];
    }

    /** Refuse unless the person holds this permission in the current house. */
    protected function requirePermission(Request $request, string $permission): void
    {
        $user = $this->user();
        $serviceId = app(SessionManager::class)->serviceId($request);

        abort_unless($user !== null, 403);

        $decision = app(AccessPolicy::class)->decide($user, $permission, $serviceId);

        abort_if($decision->denied(), 403, $decision->message ?? 'You do not have permission to do that.');
    }
}
