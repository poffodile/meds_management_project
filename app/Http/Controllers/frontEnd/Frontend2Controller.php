<?php

namespace App\Http\Controllers\frontEnd;

use App\Http\Controllers\Controller;
use App\Models\MARSheet;
use App\ServiceUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

/**
 * Frontend2 — a second app shell (its own "CLINIK"-style sidebar) sitting alongside
 * the medication frontend. Renders React/Inertia pages under resources/js/Pages/Frontend2,
 * reading the same live resident + MAR data as the rest of the app.
 */
class Frontend2Controller extends Controller
{
    use \App\Http\Controllers\frontEnd\Concerns\ResolvesCurrentHome;

    /** The home currently in view — the manager's selected home, not blindly the first. */
    private function getHomeId(): int
    {
        return $this->currentHomeId();
    }

    /**
     * Switch which of the manager's homes is in view. Validates the target against the
     * user's OWN home list (via the resolver's session write path re-checking against
     * that list), so a manager can never select a home they don't have access to.
     */
    public function switchHome(Request $request)
    {
        $request->validate(['home_id' => 'required|integer']);
        $target = (int) $request->input('home_id');

        if (! in_array($target, $this->allowedHomeIds(), true)) {
            return back()->with('error', 'You do not have access to that home.');
        }

        session(['active_home_id' => $target]);
        $name = \App\Home::where('id', $target)->value('name') ?: ('Home #'.$target);

        return back()->with('success', 'Now viewing '.$name.'.');
    }

    private function genderLabel($g): ?string
    {
        return $g ? (['M' => 'Male', 'F' => 'Female'][$g] ?? $g) : null;
    }

    private function photoUrl($su): ?string
    {
        return ($su && $su->image) ? url('public/images/serviceUserProfileImages/'.$su->image) : null;
    }

    /** Dashboard landing — headline counts + entry points. */
    public function index(Request $request)
    {
        $homeId = $this->getHomeId();

        $residentCount = ServiceUser::where('home_id', $homeId)->where('status', 1)->count();
        $sheets = MARSheet::forHome($homeId)->active()->currentlyActive()->get(['client_id', 'as_required', 'is_controlled']);

        return Inertia::render('Frontend2/Home', [
            'stats' => [
                'residents' => $residentCount,
                'medications' => $sheets->count(),
                'prn' => $sheets->where('as_required', true)->count(),
                'controlled' => $sheets->where('is_controlled', true)->count(),
            ],
        ]);
    }

    /** Residents list (the "Patients" screen). */
    public function residents(Request $request)
    {
        $homeId = $this->getHomeId();

        $residents = ServiceUser::where('home_id', $homeId)->where('status', 1)->orderBy('name')->get();

        $medCounts = MARSheet::forHome($homeId)->active()->currentlyActive()
            ->get(['client_id'])->groupBy('client_id')->map->count();

        $data = $residents->map(fn ($su) => [
            'id' => $su->id,
            'name' => $su->name,
            'photo' => $this->photoUrl($su),
            'dob' => $su->date_of_birth,
            'gender' => $this->genderLabel($su->gender),
            'room' => $su->room_number ?: null,
            'nhs' => $su->nhs_number ?: null,
            'med_count' => $medCounts[$su->id] ?? 0,
        ])->values();

        return Inertia::render('Frontend2/Residents', ['residents' => $data]);
    }

    /** A single resident's profile — modelled on the clinic "Patient profile" screen. */
    public function resident(Request $request, $id)
    {
        $homeId = $this->getHomeId();

        $su = ServiceUser::where('home_id', $homeId)->where('id', $id)->firstOrFail();

        $sheets = MARSheet::forHome($homeId)->active()->currentlyActive()
            ->where('client_id', $id)->orderBy('medication_name')->get();

        $meds = $sheets->map(fn ($s) => [
            'id' => $s->id,
            'name' => $s->medication_name,
            'strength' => $s->dosage,
            'dose' => $s->dose,
            'route' => $s->route,
            'times' => $s->time_slots ?: [],
            'prn' => (bool) $s->as_required,
            'controlled' => (bool) $s->is_controlled,
            'stock' => $s->stock_level,
        ])->values();

        $allergies = $su->allergies
            ? array_values(array_filter(array_map('trim', preg_split('/[,;]+/', $su->allergies))))
            : [];

        $address = trim(implode(', ', array_filter([$su->address ?? null, $su->city ?? null, $su->postcode ?? null])));

        $resident = [
            'id' => $su->id,
            'name' => $su->name,
            'photo' => $this->photoUrl($su),
            'dob' => $su->date_of_birth,
            'gender' => $this->genderLabel($su->gender),
            'room' => $su->room_number ?: null,
            'nhs' => $su->nhs_number ?: null,
            'phone' => $su->contact_number ?? $su->mobile ?? $su->phone_number ?? $su->em_phone ?? null,
            'email' => $su->email ?? null,
            'address' => $address ?: null,
            'registered' => $su->created_at ? Carbon::parse($su->created_at)->format('l, M j Y') : null,
            'weight' => $su->weight ?: null,
            'weight_unit' => $su->weight_unit ?: null,
            'diet' => $su->diet ?: null,
            'mobility' => $su->suMobility ?: null,
            'allergies' => $allergies,
        ];

        return Inertia::render('Frontend2/ResidentProfile', [
            'resident' => $resident,
            'meds' => $meds,
        ]);
    }

    /** A single medication's detail — schedule, stock, controlled-drug info + recent administrations. */
    public function medication(Request $request, $id)
    {
        $homeId = $this->getHomeId();

        $s = MARSheet::forHome($homeId)->active()->where('id', $id)->firstOrFail();
        $su = ServiceUser::where('id', $s->client_id)->first();

        $admins = $s->administrations()
            ->orderByDesc('date')->orderByDesc('id')
            ->with('administeredByUser:id,name')
            ->limit(20)->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'date' => $a->date ? Carbon::parse($a->date)->format('d M Y') : null,
                'slot' => $a->time_slot,
                'code' => $a->code,
                'by' => $a->administeredByUser->name ?? null,
            ])->values();

        $low = ! is_null($s->stock_level) && ! is_null($s->reorder_level) && $s->stock_level <= $s->reorder_level;

        $med = [
            'id' => $s->id,
            'name' => $s->medication_name,
            'strength' => $s->dosage,
            'dose' => $s->dose,
            'route' => $s->route,
            'times' => $s->time_slots ?: [],
            'prn' => (bool) $s->as_required,
            'controlled' => (bool) $s->is_controlled,
            'cd_schedule' => $s->cd_schedule,
            'stock' => $s->stock_level,
            'reorder_level' => $s->reorder_level,
            'low' => $low,
            'unit' => $s->unit,
            'expiry_date' => $s->expiry_date ? Carbon::parse($s->expiry_date)->format('d M Y') : null,
            'instruction' => $s->prn_details ?: $s->reason_for_medication,
            'resident' => $su ? [
                'id' => $su->id,
                'name' => $su->name,
                'photo' => $this->photoUrl($su),
                'room' => $su->room_number ?: null,
            ] : null,
        ];

        return Inertia::render('Frontend2/MedicationDetail', [
            'med' => $med,
            'administrations' => $admins,
        ]);
    }

    /** "Medication 2" — placeholder pages (a second meds area to iterate on later). */
    public function medication2(Request $request, $page)
    {
        // These four are now served by real routes registered BEFORE the {page} catch-all
        // (frontend2.medication2.*). Only genuinely-unbuilt sub-pages fall through here.
        $map = [];

        abort_unless(isset($map[$page]), 404);

        return Inertia::render('Frontend2/Medication2/'.$map[$page]);
    }

    /** Medication 2 → Medications: every active prescription across the current home. */
    public function medications2(Request $request)
    {
        $homeId = $this->getHomeId();

        $sheets = MARSheet::forHome($homeId)->active()->currentlyActive()->orderBy('medication_name')->get();
        $names = ServiceUser::whereIn('id', $sheets->pluck('client_id')->unique())->pluck('name', 'id');

        $meds = $sheets->map(fn ($s) => [
            'id' => $s->id,
            'name' => $s->medication_name,
            'resident' => $names[$s->client_id] ?? null,
            'client_id' => $s->client_id,
            'strength' => $s->dosage,
            'dose' => $s->dose,
            'form' => $s->form,
            'unit' => $s->unit,
            'route' => $s->route,
            'prn' => (bool) $s->as_required,
            'controlled' => (bool) $s->is_controlled,
            'cd_schedule' => $s->cd_schedule,
            'stock' => $s->stock_level,
            'low_stock' => ! is_null($s->stock_level) && ! is_null($s->reorder_level) && $s->stock_level <= $s->reorder_level,
        ])->values();

        return Inertia::render('Frontend2/Medication2/Medications', [
            'meds' => $meds,
            'home' => \DB::table('home')->where('id', $homeId)->value('title'),
        ]);
    }

    /** Medications section — every active prescription across the home. */
    public function medications(Request $request)
    {
        $homeId = $this->getHomeId();

        $sheets = MARSheet::forHome($homeId)->active()->currentlyActive()->orderBy('medication_name')->get();
        $names = ServiceUser::whereIn('id', $sheets->pluck('client_id')->unique())->pluck('name', 'id');

        $meds = $sheets->map(fn ($s) => [
            'id' => $s->id,
            'name' => $s->medication_name,
            'resident' => $names[$s->client_id] ?? null,
            'client_id' => $s->client_id,
            'strength' => $s->dosage,
            'dose' => $s->dose,
            'route' => $s->route,
            'prn' => (bool) $s->as_required,
            'controlled' => (bool) $s->is_controlled,
            'stock' => $s->stock_level,
        ])->values();

        return Inertia::render('Frontend2/Medications', ['meds' => $meds]);
    }
}
