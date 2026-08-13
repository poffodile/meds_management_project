<?php

namespace App\Http\Controllers\Frontend4;

use App\Http\Controllers\frontEnd\Concerns\ResolvesCurrentHome;
use App\Models\MARSheet;
use App\Services\Frontend4\Outcomes;
use App\ServiceUser;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

/**
 * frontend4 — Client profile (Page 2).
 *
 * SLICE A: the persistent identity header + the Overview tab, both on real data.
 * The other seven tabs (Medications, PRN protocols, Allergies, MAR history, Care
 * notes, Documents, Audit history) are present in the shell and filled in the
 * later slices (B–E) — see RECORD7-BUILD-PLAN.md / the Page 2 spec.
 *
 * Read-only for now; role-gated edits by addendum arrive in Slice E. Home-scoped:
 * a client outside the user's home is a 404, not a peek.
 *
 * Honest gaps: the schema has no GP, pharmacy or structured-diagnoses columns on
 * the client, so Overview shows what is actually recorded and does not invent
 * those. They arrive with the care-plan / GP Connect work, not here.
 */
class ClientProfileController extends F4Controller
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

    public function index(Request $request, int $client)
    {
        $this->useF4Layout();
        $this->requirePermission(\App\Services\Frontend4\Permissions::VIEW_CLIENTS);

        $homeId = $this->currentHomeId();

        $su = $this->scopeFrontend4Clients(ServiceUser::query())
            ->when(! $this->can(\App\Services\Frontend4\Permissions::MANAGE_CLIENTS), fn ($query) => $query->where('is_deleted', 0))
            ->where('id', $client)
            ->first();

        if (! $su) {
            abort(404);
        }

        $allergies = $su->allergies
            ? array_values(array_filter(array_map('trim', preg_split('/[,;]+/', $su->allergies))))
            : [];

        $gender = $su->gender
            ? (['M' => 'Male', 'F' => 'Female'][$su->gender] ?? $su->gender)
            : null;

        // Overview, grouped. Every value is filtered so an unrecorded field is
        // simply absent rather than shown as an empty row.
        $overview = [
            [
                'title' => 'Key details',
                'fields' => $this->fields([
                    ['NHS number', $su->nhs_number],
                    ['Date of birth', $this->dobLine($su->date_of_birth)],
                    ['Gender', $gender],
                    ['Room / location', $su->room_number],
                    ['Admission number', $su->admission_number],
                    ['Local authority', $su->local_authority],
                    ['Legal section', $su->section],
                ]),
            ],
            [
                // Key contacts. Holds next-of-kin today (the data we have). GP,
                // pharmacy and social worker are not yet columns on the client —
                // when those fields are added (see the onboarding form), add their
                // rows here and they appear automatically as another spec row.
                'title' => 'Key contacts',
                'fields' => $this->fields([
                    ['Next of kin', $su->em_name],
                    ['Relationship', $su->relationship],
                    ['Phone', $su->em_phone],
                ]),
            ],
            [
                'title' => 'Care & support',
                'fields' => $this->fields([
                    ['Care needs', $su->care_needs],
                    ['Medical notes', $su->medical_notes],
                    ['Diet', $su->diet],
                    ['Mobility', $su->suMobility],
                ]),
            ],
        ];

        // Keep only sections that actually have something to show.
        $overview = array_values(array_filter($overview, fn ($s) => count($s['fields']) > 0));

        // Medications tab — active + previous prescriptions, active first. Reuses
        // the same rows the round records against; nothing is duplicated.
        $sheets = MARSheet::forHome($homeId)->active()
            ->where('client_id', $su->id)
            ->orderByRaw("mar_status = 'active' desc")
            ->orderBy('medication_name')
            ->get();

        $lastAdminBySheet = \Illuminate\Support\Facades\DB::table('mar_administrations')
            ->whereIn('mar_sheet_id', $sheets->pluck('id'))
            ->where('is_current', 1)
            ->orderByDesc('date')
            ->orderByDesc('time_slot')
            ->get(['mar_sheet_id', 'date', 'time_slot'])
            ->groupBy('mar_sheet_id')
            ->map(fn ($rows) => $rows->first());

        $medications = $sheets->map(function ($s) use ($su, $lastAdminBySheet) {
            [$statusLabel, $statusTone] = $this->medStatus($s);
            $lastAdmin = $lastAdminBySheet->get($s->id);

            return [
                'id' => $s->id,
                'name' => $s->medication_name,
                'strength' => $s->dosage ?: null,
                'form' => $s->form ?: null,
                'dose' => $s->dose ?: null,
                'route' => $s->route ?: null,
                'doseRoute' => trim(implode(' · ', array_filter([$s->dose ?: null, $s->route ?: null]))) ?: null,
                'schedule' => $s->as_required
                    ? 'When required'
                    : (is_array($s->time_slots) && count($s->time_slots) ? implode(' · ', $s->time_slots) : ($s->frequency ?: '—')),
                'frequency' => $s->as_required ? 'When required (PRN)' : ($s->frequency ?: null),
                // Kept apart on purpose: how to give it vs why it is prescribed.
                'instruction' => $s->administration_instructions ?: null,
                'protocol' => $s->prn_details ?: null,
                'indication' => $s->reason_for_medication ?: null,
                'prescriber' => $s->prescriber ?: ($s->prescribed_by ?: null),
                'pharmacy' => $su->pharmacy_name ?: null,
                'lastAdministered' => $lastAdmin ? trim($this->fmtDate($lastAdmin->date).' '.$lastAdmin->time_slot) : null,
                'started' => $this->fmtDate($s->start_date),
                'ended' => $this->fmtDate($s->end_date),
                'stock' => $s->stock_level !== null ? $this->trimNumber($s->stock_level) : null,
                'unit' => $s->unit ?: null,
                'reorder' => $s->reorder_level !== null ? $this->trimNumber($s->reorder_level) : null,
                'lowStock' => ! is_null($s->stock_level) && ! is_null($s->reorder_level) && $s->stock_level <= $s->reorder_level,
                'asRequired' => (bool) $s->as_required,
                'maxDaily' => $s->prn_max_daily !== null ? (int) $s->prn_max_daily : null,
                'minIntervalHours' => $s->prn_min_interval_hours !== null ? (float) $s->prn_min_interval_hours : null,
                'isControlled' => (bool) $s->is_controlled,
                'statusLabel' => $statusLabel,
                'statusTone' => $statusTone,
            ];
        })->all();

        // PRN protocols tab — the when-required medicines and their limits.
        $prn = $sheets->filter(fn ($s) => $s->as_required)->map(fn ($s) => [
            'id' => $s->id,
            'name' => $s->medication_name,
            'strength' => $s->dosage ?: null,
            'form' => $s->form ?: null,
            'dose' => $s->dose ?: null,
            'route' => $s->route ?: null,
            'indication' => $s->reason_for_medication ?: null,
            'protocol' => $s->prn_details ?: null,
            'instruction' => $s->administration_instructions ?: null,
            'maxDaily' => $s->prn_max_daily !== null ? (int) $s->prn_max_daily : null,
            'minIntervalHours' => $s->prn_min_interval_hours !== null ? (float) $s->prn_min_interval_hours : null,
            'isControlled' => (bool) $s->is_controlled,
        ])->values()->all();

        // MAR history tab — this client's administrations, current records only,
        // most recent first. `is_current = 1` skips superseded (corrected) rows;
        // the correction chain itself belongs to the Audit history tab (Slice D).
        $outcomes = app(Outcomes::class);
        $marHistory = \Illuminate\Support\Facades\DB::table('mar_administrations as a')
            ->join('mar_sheets as m', 'm.id', '=', 'a.mar_sheet_id')
            ->leftJoin('user as u', 'u.id', '=', 'a.administered_by')
            ->leftJoin('user as w', 'w.id', '=', 'a.witnessed_by')
            ->where('m.client_id', $su->id)
            ->where('m.home_id', $homeId)
            ->where('a.is_current', 1)
            ->orderByDesc('a.date')->orderByDesc('a.time_slot')
            ->limit(60)
            ->get(['a.date', 'a.time_slot', 'a.code', 'a.reason', 'a.notes', 'a.is_late',
                'm.medication_name', 'u.name as staff', 'w.name as witness']);

        $mar = $marHistory->map(fn ($r) => [
            'date' => $this->fmtDate($r->date),
            'slot' => $r->time_slot,
            'medicine' => $r->medication_name,
            'outcomeLabel' => $outcomes->label($r->code),
            'outcomeStatus' => $outcomes->status($r->code),
            'isLate' => (bool) $r->is_late,
            'staff' => $r->staff,
            'witness' => $r->witness,
            'reason' => $r->reason,
            'notes' => $r->notes,
        ])->all();

        // Care notes — log-book entries linked to this service user.
        $careNotes = \Illuminate\Support\Facades\DB::table('su_log_book as sl')
            ->join('log_book as l', 'l.id', '=', 'sl.log_book_id')
            ->leftJoin('user as u', 'u.id', '=', 'l.user_id')
            ->where('sl.service_user_id', $su->id)
            ->where('l.is_deleted', 0)
            ->orderByDesc('l.date')->orderByDesc('l.id')
            ->limit(40)
            ->get(['l.title', 'l.details', 'l.date', 'l.category_name', 'u.name as staff'])
            ->map(fn ($r) => [
                'title' => $r->title ?: ($r->category_name ?: 'Note'),
                'body' => $this->snippet($r->details),
                'category' => $r->category_name ?: null,
                'date' => $this->fmtDate($r->date),
                'staff' => $r->staff,
            ])->all();

        // Documents — metadata only. Opening/downloading a document is a
        // permissioned action, added later; for now the profile lists what exists.
        $documents = \Illuminate\Support\Facades\DB::table('client_document_manages')
            ->where('client_id', $su->id)
            ->whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get(['doc_name', 'document_type', 'expiry_date', 'is_confidential', 'created_at'])
            ->map(fn ($r) => [
                'name' => $r->doc_name ?: 'Document',
                'type' => $r->document_type ?: null,
                'expiry' => $this->fmtDate($r->expiry_date),
                'confidential' => (bool) $r->is_confidential,
                'added' => $this->fmtDate($r->created_at),
            ])->all();

        // Audit history — the append-only correction chain on this client's
        // clinical records (a correction is a row that supersedes another). The
        // GENERAL audit log of config/permission changes is a separate future
        // feature (D4 / Page 10), not this.
        $auditRaw = [];

        // Administration corrections (the append-only supersede chain).
        foreach (\Illuminate\Support\Facades\DB::table('mar_administrations as a')
            ->join('mar_sheets as m', 'm.id', '=', 'a.mar_sheet_id')
            ->leftJoin('user as u', 'u.id', '=', 'a.administered_by')
            ->where('m.client_id', $su->id)->where('m.home_id', $homeId)
            ->whereNotNull('a.supersedes_id')
            ->orderByDesc('a.updated_at')->limit(50)
            ->get(['a.time_slot', 'a.date', 'a.code', 'a.amendment_reason', 'a.updated_at', 'm.medication_name', 'u.name as staff']) as $r) {
            $auditRaw[] = [
                'ts' => (string) $r->updated_at,
                'medicine' => $r->medication_name,
                'summary' => 'Administration corrected to '.$outcomes->label($r->code),
                'reason' => $r->amendment_reason,
                'staff' => $r->staff,
            ];
        }

        // Prescription changes (pause / resume / stop) from the change-log.
        foreach (\Illuminate\Support\Facades\DB::table('mar_sheet_changes as c')
            ->join('mar_sheets as m', 'm.id', '=', 'c.mar_sheet_id')
            ->leftJoin('user as u', 'u.id', '=', 'c.changed_by')
            ->where('c.client_id', $su->id)
            ->where('c.home_id', $homeId)
            ->where('m.home_id', $homeId)
            ->orderByDesc('c.id')->limit(50)
            ->get(['c.change_type', 'c.old_value', 'c.new_value', 'c.reason', 'c.created_at', 'm.medication_name', 'u.name as staff']) as $r) {
            $auditRaw[] = [
                'ts' => (string) $r->created_at,
                'medicine' => $r->medication_name,
                'summary' => 'Prescription '.$r->change_type.' ('.$r->old_value.' → '.$r->new_value.')',
                'reason' => $r->reason,
                'staff' => $r->staff,
            ];
        }

        usort($auditRaw, fn ($a, $b) => strcmp($b['ts'], $a['ts']));

        $audit = array_map(fn ($r) => [
            'medicine' => $r['medicine'],
            'summary' => $r['summary'],
            'when' => $this->fmtDateTime($r['ts']),
            'reason' => $r['reason'],
            'staff' => $r['staff'],
        ], array_slice($auditRaw, 0, 80));

        // ── Overview dashboard ─────────────────────────────────────────────
        // Next scheduled medication: the earliest upcoming slot among active
        // scheduled (non-PRN) medicines; if all today's slots have passed, the
        // first slot of the next day.
        $now = now()->format('H:i');
        $slotMap = [];
        foreach ($sheets as $s) {
            if ($s->as_required || ! in_array(strtolower((string) $s->mar_status), ['active', 'paused'], true)) {
                continue;
            }
            foreach ((is_array($s->time_slots) ? $s->time_slots : []) as $slot) {
                $slotMap[$slot][] = $s->medication_name;
            }
        }
        ksort($slotMap);
        $nextMed = null;
        foreach ($slotMap as $slot => $names) {
            if ($slot >= $now) {
                $nextMed = ['time' => $slot, 'count' => count($names), 'meds' => array_values(array_unique($names)), 'nextDay' => false];
                break;
            }
        }
        if ($nextMed === null && ! empty($slotMap)) {
            $first = array_key_first($slotMap);
            $nextMed = ['time' => $first, 'count' => count($slotMap[$first]), 'meds' => array_values(array_unique($slotMap[$first])), 'nextDay' => true];
        }

        $activeMeds = array_values(array_filter($medications, fn ($m) => $m['statusLabel'] === 'Active'));

        $contacts = [
            'gp' => trim((string) $su->gp_name) !== ''
                ? ['name' => $su->gp_name, 'sub' => $su->gp_practice ?: null]
                : null,
            'pharmacy' => trim((string) $su->pharmacy_name) !== ''
                ? ['name' => $su->pharmacy_name, 'sub' => $su->pharmacy_phone ?: null]
                : null,
            'nextOfKin' => trim((string) $su->em_name) !== ''
                ? ['name' => $su->em_name, 'sub' => trim(implode(' · ', array_filter([$su->relationship ?: null, $su->em_phone ?: null])))]
                : null,
        ];

        $careInstructions = [];
        foreach ([['Care needs', $su->care_needs], ['Medical notes', $su->medical_notes]] as [$t, $v]) {
            if (trim((string) $v) !== '') {
                $careInstructions[] = ['title' => $t, 'body' => trim((string) $v)];
            }
        }

        $recent = array_slice($mar, 0, 3);

        $infoStrip = [
            'allergy' => count($allergies) ? implode(', ', $allergies) : null,
            'allergyReaction' => $su->allergy_reaction ?: null,
            'medSupport' => $su->medication_support ?: null,
            'capacity' => $su->capacity_consent ?: null,
            'keyWorker' => $su->key_worker ?: null,
        ];

        $headerMeta = array_values(array_filter([
            $this->ageFromDob($su->date_of_birth) !== null ? $this->ageFromDob($su->date_of_birth).' years' : null,
            $this->fmtDate($su->date_of_birth),
            $gender,
            $su->room_number ? 'Room '.$su->room_number : null,
            $su->nhs_number ? 'NHS '.$su->nhs_number : null,
            $su->admission_number ? 'Adm '.$su->admission_number : null,
        ]));

        // The labelled stat block for the mobile header — laid out three across,
        // so up to six facts read as two tidy rows.
        $headerStats = array_values(array_filter([
            $this->ageFromDob($su->date_of_birth) !== null ? ['label' => 'Age', 'value' => $this->ageFromDob($su->date_of_birth).' yrs'] : null,
            $this->fmtDate($su->date_of_birth) ? ['label' => 'Born', 'value' => $this->fmtDate($su->date_of_birth)] : null,
            $gender ? ['label' => 'Sex', 'value' => $gender] : null,
            $su->room_number ? ['label' => 'Room', 'value' => (string) $su->room_number] : null,
            $su->nhs_number ? ['label' => 'NHS no.', 'value' => (string) $su->nhs_number] : null,
            $su->admission_number ? ['label' => 'Adm no.', 'value' => (string) $su->admission_number] : null,
        ]));

        // The Key details card at the top of the overview — the full identity
        // record. Unrecorded fields are filtered out by fields(), not shown blank.
        $keyDetails = $this->fields([
            ['Full name', $su->name],
            ['Date of birth', $this->dobLine($su->date_of_birth)],
            ['Gender', $gender],
            ['NHS number', $su->nhs_number],
            ['Room / location', $su->room_number],
            ['Admission number', $su->admission_number],
            ['Local authority', $su->local_authority],
            ['Legal section', $su->section],
            ['Diet', $su->diet],
            ['Mobility', $su->suMobility],
        ]);

        return Inertia::render('ClientProfile', $this->roleProps() + [
            'terms' => ['person' => 'client', 'people' => 'clients', 'place' => 'home'],
            'nextMed' => $nextMed,
            'keyDetails' => $keyDetails,
            'activeMeds' => $activeMeds,
            'contacts' => $contacts,
            'careInstructions' => $careInstructions,
            'recent' => $recent,
            'infoStrip' => $infoStrip,
            'headerMeta' => $headerMeta,
            'headerStats' => $headerStats,
            'roundUrl' => route('frontend4.round'),
            'place' => DB::table('home')->where('id', $homeId)->value('title') ?: 'Your home',
            'user' => Auth::user()->name ?? null,
            'client' => [
                'id' => $su->id,
                'name' => trim((string) $su->name) ?: ('Client #'.$su->id),
                'photo' => $su->image ? url('public/images/serviceUserProfileImages/'.$su->image) : null,
                'age' => $this->ageFromDob($su->date_of_birth),
                'weight' => trim((string) $su->weight) !== '' ? trim((string) $su->weight).' '.($su->weight_unit ?: 'kg') : null,
                'location' => $su->room_number ?: null,
                'nhs' => $su->nhs_number ?: null,
                'status' => ucfirst($su->lifecycle_status ?: ((int) $su->status === 1 ? 'active' : 'inactive')),
                'allergies' => $allergies,
            ],
            'overview' => $overview,
            'medications' => $medications,
            'prn' => $prn,
            'marHistory' => $mar,
            'marCapped' => count($mar) >= 60,
            'careNotes' => $careNotes,
            'documents' => $documents,
            'audit' => $audit,
        ]);
    }

    /** A prescription's status as a word and a tone. */
    private function medStatus($s): array
    {
        $st = strtolower((string) $s->mar_status);

        if ($s->discontinued || $st === 'discontinued' || $st === 'stopped') {
            return ['Stopped', 'muted'];
        }
        if ($st === 'paused') {
            return ['Paused', 'caution'];
        }
        if ($st === 'active') {
            return ['Active', 'good'];
        }

        return [$st !== '' ? ucfirst($st) : 'Unknown', 'muted'];
    }

    /** "54.000" → "54", "0.500" → "0.5" — a stock figure without trailing noise. */
    private function trimNumber($n): string
    {
        $s = (string) $n;

        return str_contains($s, '.') ? rtrim(rtrim($s, '0'), '.') : $s;
    }

    /** "j M Y" or null. */
    private function fmtDate($d): ?string
    {
        if (! $d || $d === '0000-00-00' || str_starts_with((string) $d, '0000-00-00')) {
            return null;
        }

        try {
            return Carbon::parse($d)->format('j M Y');
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** "j M Y, H:i" or null — for an audit timestamp. */
    private function fmtDateTime($d): ?string
    {
        if (! $d || str_starts_with((string) $d, '0000-00-00')) {
            return null;
        }

        try {
            return Carbon::parse($d)->format('j M Y, H:i');
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Plain-text snippet from possibly-HTML note details. */
    private function snippet($html, int $limit = 240): ?string
    {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags((string) $html)));
        if ($text === '') {
            return null;
        }

        return mb_strlen($text) > $limit ? mb_substr($text, 0, $limit).'…' : $text;
    }

    /** Drop pairs with no value; trim strings; keep order. */
    private function fields(array $pairs): array
    {
        $out = [];
        foreach ($pairs as [$label, $value]) {
            if (is_string($value)) {
                $value = trim($value);
            }
            if ($value !== null && $value !== '') {
                $out[] = ['label' => $label, 'value' => $value];
            }
        }

        return $out;
    }

    /** "3 Jan 2013 (12)" — the date with the age, or null when unusable. */
    private function dobLine($dob): ?string
    {
        if (! $dob || $dob === '0000-00-00') {
            return null;
        }

        try {
            $formatted = Carbon::parse($dob)->format('j M Y');
        } catch (\Throwable $e) {
            return null;
        }

        $age = $this->ageFromDob($dob);

        return $age !== null ? "{$formatted} ({$age})" : $formatted;
    }

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
}
