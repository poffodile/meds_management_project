<?php

namespace App\Http\Controllers\Record7;

use App\Services\Record7\AccessPolicy;
use App\Services\Record7\SessionManager;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * The house's Today screen.
 *
 * Section 0 ends here: the handoff asks that a successful sign-in opens the
 * selected house's Today placeholder. There is no clinical content yet — that
 * arrives with Section 1 — so this screen reports the access decision plainly:
 * who you are, which house you are in, what your access type is, and exactly
 * what you may and may not do here.
 *
 * That is genuinely useful during Section 0 review, and it is honest: it does
 * not pretend to hold a medicines round it cannot yet run.
 */
class TodayController extends R7Controller
{
    public function __construct(
        private readonly AccessPolicy $policy,
        private readonly SessionManager $sessions
    ) {
    }

    public function index(Request $request)
    {
        $this->useR7Layout($request);

        $user = $this->user();
        abort_unless($user !== null, 403);

        $serviceId = $this->sessions->serviceId($request);
        $granted = $this->policy->grantedPermissions($user, $serviceId);

        // Every permission that exists, with the decision and — where refused —
        // the reason. Showing the refusals is the point of a Section 0 screen.
        $decisions = \App\Models\Record7\Permission::orderBy('code')->get()
            ->map(function ($permission) use ($user, $serviceId) {
                $decision = $this->policy->decide($user, $permission->code, $serviceId);

                return [
                    'code' => $permission->code,
                    'name' => $permission->name,
                    'sensitive' => (bool) $permission->is_sensitive,
                    'allowed' => $decision->allowed,
                    'reason' => $decision->message,
                ];
            })->values()->all();

        return Inertia::render('Today', [
            'name' => $user->displayName(),
            'fullName' => $user->full_name,
            'employmentType' => $user->employment_type,
            'accessEndsAt' => $user->access_ends_at?->format('j F Y'),
            'granted' => $granted,
            'decisions' => $decisions,
            'competencies' => $user->competencies()->with('competencyType')->get()
                ->map(fn ($c) => [
                    'name' => $c->competencyType?->name,
                    'status' => $c->status,
                    'reviewDue' => $c->review_due_at?->format('j F Y'),
                ])->values()->all(),
            'urls' => [
                'houses' => route('record7.houses'),
                'lock' => route('record7.lock.now'),
                'signOut' => route('record7.signout'),
                'audit' => route('record7.audit'),
            ],
        ]);
    }
}
