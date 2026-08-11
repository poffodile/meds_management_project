<?php

namespace App\Http\Controllers\Frontend4;

use App\Http\Controllers\frontEnd\Concerns\ResolvesCurrentHome;
use App\Models\MARSheet;
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
            if (! Auth::guard('frontend4')->check()) {
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
            ->get([
                'id', 'name', 'image', 'date_of_birth', 'room_number', 'nhs_number',
                'allergies', 'allergy_reaction', 'status', 'medication_support',
                'key_worker', 'gp_name', 'gp_practice', 'pharmacy_name', 'pharmacy_phone',
                'em_name', 'relationship', 'em_phone',
            ]);

        $medsByClient = MARSheet::forHome($homeId)->active()
            ->whereIn('client_id', $clients->pluck('id'))
            ->orderBy('medication_name')
            ->get([
                'client_id', 'medication_name', 'dosage', 'time_slots', 'as_required',
                'stock_level', 'reorder_level', 'unit',
            ])
            ->groupBy('client_id');

        $now = now()->format('H:i');

        $rows = $clients->map(function ($su) use ($medsByClient, $now) {
            $allergies = $su->allergies
                ? array_values(array_filter(array_map('trim', preg_split('/[,;]+/', $su->allergies))))
                : [];

            $name = trim((string) $su->name) ?: ('Client #'.$su->id);
            $clientMeds = $medsByClient->get($su->id, collect());
            $slotMap = [];
            foreach ($clientMeds as $med) {
                if ($med->as_required) {
                    continue;
                }
                foreach ((is_array($med->time_slots) ? $med->time_slots : []) as $slot) {
                    $slotMap[$slot][] = $med->medication_name;
                }
            }
            ksort($slotMap);

            $nextMed = null;
            $due = 0;
            foreach ($slotMap as $slot => $names) {
                if ($slot <= $now) {
                    $due += count(array_unique($names));
                }
                if ($nextMed === null && $slot >= $now) {
                    $nextMed = $slot;
                }
            }
            if ($nextMed === null && ! empty($slotMap)) {
                $nextMed = array_key_first($slotMap);
            }

            $lowStock = $clientMeds->first(fn ($m) => ! is_null($m->stock_level) && ! is_null($m->reorder_level) && $m->stock_level <= $m->reorder_level);
            $medicineNames = $clientMeds->pluck('medication_name')->filter()->unique()->values()->all();

            return [
                'id' => $su->id,
                'name' => $name,
                'preferred' => explode(' ', $name)[0] ?? $name,
                'photo' => $su->image
                    ? url('public/images/serviceUserProfileImages/'.$su->image)
                    : null,
                'age' => $this->ageFromDob($su->date_of_birth),
                'dob' => $this->fmtDate($su->date_of_birth),
                'location' => $su->room_number ?: null,
                'nhs' => $su->nhs_number ?: null,
                'allergies' => $allergies,
                'allergyText' => count($allergies) ? implode(', ', $allergies) : 'No known allergies',
                'reaction' => $su->allergy_reaction ?: null,
                'hasAllergy' => count($allergies) > 0,
                'letter' => $this->groupLetter($name),
                'active' => (int) $su->status === 1,
                'statusLabel' => (int) $su->status === 1 ? 'Active' : 'Inactive',
                'support' => $su->medication_support ?: null,
                'keyWorker' => $su->key_worker ?: null,
                'gp' => trim((string) $su->gp_name) !== ''
                    ? ['name' => $su->gp_name, 'sub' => $su->gp_practice ?: null]
                    : null,
                'pharmacy' => trim((string) $su->pharmacy_name) !== ''
                    ? ['name' => $su->pharmacy_name, 'sub' => $su->pharmacy_phone ?: null]
                    : null,
                'nextOfKin' => trim((string) $su->em_name) !== ''
                    ? ['name' => $su->em_name, 'sub' => trim(implode(' - ', array_filter([$su->relationship ?: null, $su->em_phone ?: null])))]
                    : null,
                'medicines' => $medicineNames,
                'medicineCount' => count($medicineNames),
                'nextMed' => $nextMed,
                'due' => $due,
                'attention' => (bool) $lowStock,
                'concern' => $lowStock ? 'Low stock: '.$lowStock->medication_name : null,
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
    private function fmtDate($date): ?string
    {
        if (! $date || $date === '0000-00-00' || str_starts_with((string) $date, '0000-00-00')) {
            return null;
        }

        try {
            return Carbon::parse($date)->format('j M Y');
        } catch (\Throwable $e) {
            return null;
        }
    }
    private function groupLetter(string $name): string
    {
        $c = strtoupper(substr(ltrim($name), 0, 1));

        return ctype_alpha($c) ? $c : '#';
    }
}

