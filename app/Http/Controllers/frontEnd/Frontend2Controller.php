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
    /** Resolve the carer's primary home (matches the medication screens). */
    private function getHomeId(): int
    {
        return (int) explode(',', Auth::user()->home_id)[0];
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
        $map = [
            'round' => 'MedicationRound',
            'medications' => 'Medications',
            'missed-doses' => 'MissedDoses',
            'controlled-drugs' => 'ControlledDrugs',
            'stock' => 'Stock',
        ];

        abort_unless(isset($map[$page]), 404);

        return Inertia::render('Frontend2/Medication2/'.$map[$page]);
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
