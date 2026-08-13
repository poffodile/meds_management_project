<?php

namespace App\Http\Controllers\Frontend4;

use App\Http\Controllers\Controller;
use App\Services\Frontend4\Permissions;
use App\Services\Frontend4\RoleResolver;
use App\Services\Frontend4\AccessContext;
use App\Models\Frontend4User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

/**
 * Base for every frontend4 controller.
 *
 * Two jobs: put every response through frontend4's own root view, and give
 * every action the same role and permission checks.
 */
abstract class F4Controller extends Controller
{
    /** Resolved once per request — the resolver reads the database. */
    private ?string $resolvedRole = null;

    /**
     * Point Inertia at frontend4's own root view (resources/views/f4.blade.php).
     *
     * MUST be called from inside an action, NOT from a constructor.
     *
     * Laravel instantiates a controller while *gathering* route middleware —
     * before the middleware pipeline runs. So a constructor call happens first,
     * and then HandleInertiaRequests::handle() overwrites the root view back to
     * 'app'. The page then boots resources/js/app.jsx, cannot find the frontend4
     * page under ./Pages/, and renders blank with
     * "Cannot read properties of undefined (reading 'default')".
     *
     * Calling it inside the action runs after that middleware. Every frontend4
     * action must call it as its first line.
     */
    protected function useF4Layout(): void
    {
        Inertia::setRootView('f4');
    }

    /** The signed-in user's frontend4 role: carer, lead, manager, admin or none. */
    protected function role(): string
    {
        if ($this->resolvedRole === null) {
            $this->resolvedRole = app(RoleResolver::class)->resolve(Auth::user());
        }

        return $this->resolvedRole;
    }

    /** May the signed-in user perform this action? */
    protected function can(string $permission): bool
    {
        return app(Permissions::class)->allows($this->role(), $permission);
    }

    /**
     * Refuse the request unless the user holds this permission.
     *
     * THIS is the access control. The React side hides controls a user has no
     * permission for, but that is a courtesy — a hidden button is not a check.
     * Every frontend4 action that writes anything calls this first.
     */
    protected function requirePermission(string $permission): void
    {
        if (! $this->can($permission)) {
            abort(403, 'You do not have permission to do that.');
        }
    }

    /**
     * Refuse anyone with no medication access at all.
     *
     * Called at the top of every frontend4 action, including read-only ones. It
     * replaces the previous check, which admitted every user type there is —
     * meaning anyone who could log in could reach medication management.
     *
     * Finance accounts ("Account Manager") land here even when their account
     * type says admin, because an access level mapped to NONE beats the account
     * type. See RoleResolver::resolve().
     */
    protected function requireMedicationAccess(): void
    {
        if ($this->role() === RoleResolver::NONE) {
            abort(403, 'You do not have access to medication management.');
        }
    }

    /**
     * Role and permissions for the React side.
     *
     * Merged into every frontend4 Inertia response so a page can hide what it
     * should hide without asking the server a second time. Display only.
     */
    protected function roleProps(): array
    {
        $role = $this->role();

        return [
            'role' => $role,
            'roleLabel' => app(RoleResolver::class)->label($role),
            'can' => app(Permissions::class)->forRole($role),
            'accessContext' => Auth::guard('frontend4')->user() instanceof Frontend4User
                ? app(AccessContext::class)->props(Auth::guard('frontend4')->user())
                : null,
        ];
    }

    /** Apply Frontend 4's service/location client boundary to an Eloquent query. */
    protected function scopeFrontend4Clients($query)
    {
        $user = Auth::guard('frontend4')->user();
        abort_unless($user instanceof Frontend4User, 403);

        return app(AccessContext::class)->scopeClients($query, $user);
    }

    /** Hook used only when the shared round builder is composed by Frontend 4. */
    protected function scopeMedicationRoundSheetsForAccess($query, int $homeId, ?string $date = null)
    {
        $user = Auth::guard('frontend4')->user();
        abort_unless($user instanceof Frontend4User, 403);

        $query->whereIn('client_id', app(AccessContext::class)->allowedClientIds($user))
            ->currentlyActive();

        return $date ? $query->effectiveOn($date) : $query;
    }
}
