<?php

namespace App\Http\Middleware;

use App\Models\Frontend4User;
use App\Services\Frontend4\AuthenticationSecurityService;
use App\Services\Frontend4\Permissions;
use App\Services\Frontend4\RoleResolver;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/** Enforces a Frontend 4 permission before its controller is reached. */
class Frontend4Authorize
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = Auth::guard('frontend4')->user();
        $role = app(RoleResolver::class)->resolve($user);

        if (app(Permissions::class)->allows($role, $permission)) {
            return $next($request);
        }

        if ($user instanceof Frontend4User) {
            app(AuthenticationSecurityService::class)->record(
                $request,
                'permission_denied',
                false,
                $user,
                null,
                [
                    'permission' => $permission,
                    'route' => $request->route()?->getName(),
                ]
            );
        }

        abort(403, 'You do not have permission to access this area.');
    }
}
