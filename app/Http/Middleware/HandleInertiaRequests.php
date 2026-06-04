<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        // Manager-level roles (can edit/approve/override). Everyone else = carer view.
        // Mirrors App\Models\ShiftHandover::MANAGER_TYPES.
        $managerTypes = ['M', 'CM', 'A', 'O'];

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id'        => $user->id,
                    'name'      => $user->name,
                    'user_type' => $user->user_type,
                    'role'      => in_array($user->user_type, $managerTypes, true) ? 'manager' : 'carer',
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
