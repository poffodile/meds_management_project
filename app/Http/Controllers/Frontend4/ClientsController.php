<?php

namespace App\Http\Controllers\Frontend4;

use App\Http\Controllers\frontEnd\Concerns\ResolvesCurrentHome;
use App\ServiceUser;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

/**
 * frontend4 — Clients (the service users).
 *
 * Page 1 of the build: the way IN. A searchable list of the people this user is
 * responsible for; tapping one opens their profile (Page 2). Deliberately
 * read-only — no client is created or edited here. Creating and deactivating a
 * client is an Administration function, not this page.
 *
 * SCOPING
 * Always the signed-in user's own home, resolved server-side by
 * ResolvesCurrentHome — the request can never ask for another home's clients.
 * "Client" is the canonical concept (client_id in 37 tables); the display label
 * stays configurable via `terms`.
 *
 * This controller is read-only. It records nothing.
 */
class ClientsController extends F4Controller
{
    use ResolvesCurrentHome;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (! Auth::check()) {
                abort(403, 'You do not have access to medication management.');
            }

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $this->useF4Layout();
        $this->requireMedicationAccess();

        $homeId = $this->currentHomeId();

        // Clients of this home. Not deleted; every status is loaded so the page can
        // offer a status filter (Active / Inactive). The React side defaults the
        // filter to Active, so the list reads the same as before unless the user
        // asks for inactive.
        $clients = ServiceUser::where('home_id', $homeId)
            ->where('is_deleted', 0)
            ->orderBy('name')
            ->get(['id', 'name', 'image', 'date_of_birth', 'room_number', 'nhs_number', 'allergies', 'status']);

        $rows = $clients->map(function ($su) {
            // Free-text allergy string → a list. It can be displayed but not yet
            // checked (that is D1 / issue I3), so on this page it is a flag only.
            $allergies = $su->allergies
                ? array_values(array_filter(array_map('trim', preg_split('/[,;]+/', $su->allergies))))
                : [];

            $name = trim((string) $su->name) ?: ('Client #'.$su->id);

            return [
                'id' => $su->id,
                'name' => $name,
                'photo' => $su->image
                    ? url('public/images/serviceUserProfileImages/'.$su->image)
                    : null,
                'age' => $this->ageFromDob($su->date_of_birth),
                // Only when the service records one — nothing assumes a room.
                'location' => $su->room_number ?: null,
                'allergies' => $allergies,
                'hasAllergy' => count($allergies) > 0,
                'letter' => $this->groupLetter($name),
                'active' => (int) $su->status === 1,
                'statusLabel' => (int) $su->status === 1 ? 'Active' : 'Inactive',
            ];
        })->values()->all();

        return Inertia::render('Clients', $this->roleProps() + [
            'terms' => ['person' => 'client', 'people' => 'clients', 'place' => 'home'],
            'place' => DB::table('home')->where('id', $homeId)->value('title') ?: 'Your home',
            'user' => Auth::user()->name ?? null,
            'clients' => $rows,
            'total' => count($rows),
        ]);
    }

    /** Whole years from a date of birth, or null when there is no usable date. */
    private function ageFromDob($dob): ?int
    {
        if (! $dob || $dob === '0000-00-00') {
            return null;
        }

        try {
            $age = Carbon::parse($dob)->age;
        } catch (\Throwable $e) {
            return null;
        }

        return ($age >= 0 && $age < 130) ? $age : null;
    }

    /** The A–Z bucket a name sorts into; anything non-alphabetic groups under "#". */
    private function groupLetter(string $name): string
    {
        $c = strtoupper(substr(ltrim($name), 0, 1));

        return ctype_alpha($c) ? $c : '#';
    }
}
