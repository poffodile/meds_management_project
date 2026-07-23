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
            // The homes this user may switch between, and which one is active. Drives the
            // header home-switcher (CR-06). A single home ⇒ no switcher shown. Re-validates
            // the active id against the user's own list, never trusting the session alone.
            'homes' => fn () => $this->homesForSwitcher($request),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],
        ];
    }

    /**
     * Build the home-switcher payload: the list this user may access and the active id.
     * Returns null (no switcher) when the user has one home or none.
     */
    private function homesForSwitcher(Request $request): ?array
    {
        $user = $request->user();
        if (! $user) {
            return null;
        }

        // real_home_id — NOT home_id. App\User's home_id accessor returns the already-
        // resolved single active home; real_home_id is the actual list the user may access.
        $allowed = collect(explode(',', (string) $user->real_home_id))
            ->map(fn ($h) => (int) trim($h))->filter()->unique()->values();

        if ($allowed->count() < 2) {
            return null; // nothing to switch between
        }

        $active = (int) $request->session()->get('active_home_id');
        if (! $allowed->contains($active)) {
            $active = (int) $allowed->first();
        }

        $names = \App\Home::whereIn('id', $allowed)->pluck('name', 'id');

        return [
            'active' => $active,
            'list' => $allowed->map(fn ($id) => [
                'id' => $id,
                'name' => $names[$id] ?? ('Home #'.$id),
            ])->values()->all(),
        ];
    }
}
