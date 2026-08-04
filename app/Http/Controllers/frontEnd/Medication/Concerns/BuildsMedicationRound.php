<?php

namespace App\Http\Controllers\frontEnd\Medication\Concerns;

use App\Models\MARAdministration;
use App\Models\MARSheet;
use App\Models\MedicationRoundClosure;
use App\Models\MedicationStockTransaction;
use App\Services\Medication\DoseOutcome;
use App\Services\Staff\MARSheetService;
use App\ServiceUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

/**
 * The shared Medication Round domain logic: building a round's props, and
 * recording a single dose against it.
 *
 * WHY A TRAIT AND NOT A BASE CLASS (owner decision, 2026-07-16).
 *
 * MedicationRoundController exposes 18 page variants and 18 record endpoints,
 * all of which call buildRoundProps()/applyRecord(). If a new controller
 * `extends` it to reuse that logic, it also inherits all 62 methods and the
 * whole routed surface, and any edit made to serve the new page silently
 * reshapes all 18 existing pages at once.
 *
 * Composing this trait instead keeps the reuse (one definition of the clinical
 * rules — duplication would drift, and drift in dose-recording rules is a
 * safety risk) while keeping each controller's public surface its own.
 *
 * The private helpers below stay private: each composing class gets its own
 * copy, so there is no fragile base-class coupling between them.
 */
trait BuildsMedicationRound
{
    /**
     * How close together two identical PRN submissions must be to count as one event.
     *
     * A stopgap for the absence of a client idempotency key — see the guard in
     * applyRecordLocked(). Long enough to absorb a double-tap or a retry after a network
     * timeout; far shorter than any plausible genuine re-dose.
     */
    private const PRN_DUPLICATE_WINDOW_SECONDS = 90;

    /**
     * Time-of-day rounds. A medication belongs to a round when one of its
     * scheduled time slots falls within [start, end). Anything before 06:00
     * or from 18:00 onwards counts as Night.
     */
    private const ROUNDS = [
        'morning' => ['label' => 'Morning',   'start' => 6,  'end' => 12, 'icon' => 'fa-sun-o',     'window' => '06:00–12:00'],
        'lunchtime' => ['label' => 'Lunchtime', 'start' => 12, 'end' => 14, 'icon' => 'fa-coffee',    'window' => '12:00–14:00'],
        'evening' => ['label' => 'Evening',   'start' => 14, 'end' => 18, 'icon' => 'fa-cloud',     'window' => '14:00–18:00'],
        'night' => ['label' => 'Night',     'start' => 18, 'end' => 24, 'icon' => 'fa-moon-o',    'window' => '18:00–06:00'],
    ];

    use \App\Http\Controllers\frontEnd\Concerns\ResolvesCurrentHome;

    /** The home currently in view — the manager's selected home, not blindly the first. */
    private function getHomeId(): int
    {
        return $this->currentHomeId();
    }

    /** Which round does an "HH:MM" time fall into? */
    private function roundForTime(?string $time): string
    {
        if (! $time) {
            return 'night';
        }
        $hour = (int) substr($time, 0, 2);
        foreach (self::ROUNDS as $key => $cfg) {
            if ($key === 'night') {
                continue;
            }
            if ($hour >= $cfg['start'] && $hour < $cfg['end']) {
                return $key;
            }
        }

        return 'night';
    }

    /** Builds the shared props for the medication round React pages. */
    protected function buildRoundProps(Request $request): array
    {
        $request->validate(['date' => 'nullable|date']);

        $homeId = $this->getHomeId();
        $date = $request->input('date', now()->toDateString());

        $sheets = MARSheet::forHome($homeId)
            ->active()
            ->currentlyActive()
            ->with(['administrations' => function ($q) use ($date) {
                $q->where('date', $date)->with('administeredByUser:id,name');
            }])
            ->orderBy('medication_name')
            ->get();

        $clientIds = $sheets->pluck('client_id')->unique()->values();
        $residents = ServiceUser::whereIn('id', $clientIds)->get()->keyBy('id');

        // Current weight from the dated series (append-only) — derived, never the stale
        // single-value column. One query for the whole round (no N+1). Carries the
        // measurement AGE so the round can flag a weight too old to trust for a
        // weight-based dose (REQ-MED-112 / audit CR-09).
        $weights = \App\Models\ServiceUserWeight::currentFor($clientIds->all());

        // Per-resident regular / PRN medication counts (across all active sheets).
        $counts = [];
        foreach ($sheets as $s) {
            $cid = $s->client_id;
            if (! isset($counts[$cid])) {
                $counts[$cid] = ['regular' => 0, 'prn' => 0];
            }
            $s->as_required ? $counts[$cid]['prn']++ : $counts[$cid]['regular']++;
        }

        // Per-resident active risks (generic — surfaced with their impact level).
        $risksByClient = \DB::table('care_plan_risks')
            ->whereIn('client_id', $clientIds)
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->get(['client_id', 'description', 'impact'])
            ->groupBy('client_id');

        // Time context for the due / overdue / upcoming derivation (relative to "now").
        $today = now()->toDateString();
        $isToday = ($date === $today);
        $isPast = ($date < $today);
        $nowMin = (int) now()->format('H') * 60 + (int) now()->format('i');

        $grid = [];
        foreach (array_keys(self::ROUNDS) as $roundKey) {
            $grid[$roundKey] = [];
        }

        foreach ($sheets as $sheet) {
            $slots = ! empty($sheet->time_slots) ? $sheet->time_slots : [null];
            $adminsBySlot = $sheet->administrations->keyBy('time_slot');

            // PRN ("as needed") state: how many given today, last given, next allowed.
            $prn = null;
            if ($sheet->as_required) {
                // 'A' only — an asleep resident consumed no dose, so it must not count
                // against the PRN daily maximum or restart the interval clock.
                $given = $sheet->administrations->filter(fn ($a) => DoseOutcome::isGiven($a->code));
                $count = $given->count();
                // administered_at, not updated_at: the interval must run from when the
                // dose was GIVEN, not from the last time anyone edited the row. Editing
                // a note used to push this clock forward and block the next dose.
                $last = $given->sortByDesc('administered_at')->first()?->administered_at;
                $intervalH = (float) ($sheet->prn_min_interval_hours ?? 0);
                $next = ($last && $intervalH > 0) ? $last->copy()->addMinutes((int) round($intervalH * 60)) : null;
                $maxDaily = $sheet->prn_max_daily;
                $hitMax = $maxDaily !== null && $count >= (int) $maxDaily;
                $tooSoon = $isToday && $next && now()->lt($next);
                $prn = [
                    'max_daily' => $maxDaily !== null ? (int) $maxDaily : null,
                    'interval_hours' => $sheet->prn_min_interval_hours !== null ? (float) $sheet->prn_min_interval_hours : null,
                    'given_today' => $count,
                    'last_given' => $last?->format('H:i'),
                    'next_available' => $next?->format('H:i'),
                    'blocked' => $isToday && ($hitMax || $tooSoon),
                    'block_reason' => $hitMax ? 'Daily maximum reached' : ($tooSoon ? ('Not due until '.$next?->format('H:i')) : null),
                ];
            }

            foreach ($slots as $slot) {
                $targetRounds = $slot !== null ? [$this->roundForTime($slot)] : array_keys(self::ROUNDS);

                foreach ($targetRounds as $roundKey) {
                    $clientId = $sheet->client_id;
                    if (! isset($grid[$roundKey][$clientId])) {
                        $su = $residents->get($clientId);
                        $allergies = ($su && $su->allergies)
                            ? array_values(array_filter(array_map('trim', preg_split('/[,;]+/', $su->allergies))))
                            : [];
                        $riskFlags = ($risksByClient[$clientId] ?? collect())->map(function ($r) {
                            return ['label' => $r->description, 'level' => strtolower($r->impact ?: 'medium')];
                        })->values()->all();
                        $gender = ($su && $su->gender) ? (['M' => 'Male', 'F' => 'Female'][$su->gender] ?? $su->gender) : null;
                        $grid[$roundKey][$clientId] = [
                            'client_id' => $clientId,
                            'name' => $su->name ?? ('Resident #'.$clientId),
                            'photo' => ($su && $su->image) ? url('public/images/serviceUserProfileImages/'.$su->image) : null,
                            'dob' => $su->date_of_birth ?? null,
                            'gender' => $gender,
                            // Legacy single value kept for now (other screens still read it);
                            // the round should use `weight_current` below.
                            'weight' => ($su && $su->weight) ? $su->weight : null,
                            'weight_unit' => $su->weight_unit ?? null,
                            // Derived current weight: kg, when it was measured, how old that is,
                            // and whether it is stale. Null when the resident has no reading.
                            'weight_current' => $weights[$clientId] ?? null,
                            'room' => ($su && $su->room_number) ? $su->room_number : null,
                            'nhs' => ($su && $su->nhs_number) ? $su->nhs_number : null,
                            'mobility' => ($su && $su->suMobility) ? $su->suMobility : null,
                            'diet' => ($su && $su->diet) ? $su->diet : null,
                            'allergies' => $allergies,
                            'risk_flags' => $riskFlags,
                            'regular_count' => $counts[$clientId]['regular'] ?? 0,
                            'prn_count' => $counts[$clientId]['prn'] ?? 0,
                            'rows' => [],
                        ];
                    }
                    $admin = $slot !== null ? $adminsBySlot->get($slot) : null;
                    $grid[$roundKey][$clientId]['rows'][] = [
                        'mar_sheet_id' => $sheet->id,
                        'medication_name' => $sheet->medication_name,
                        'strength' => $sheet->dosage,
                        'dose' => $sheet->dose,
                        'route' => $sheet->route,
                        // Three DIFFERENT things, kept separate (they were once jammed into one
                        // field, which let the round show an indication styled as a directive —
                        // review C1/HAZ-22). `instruction` is the genuine "how to give it"
                        // directive (issue #29 / C1) — the dedicated field, falling back to PRN
                        // details; `indication` is WHY the medicine is prescribed.
                        'instruction' => $sheet->administration_instructions ?: ($sheet->prn_details ?: null),
                        'indication' => $sheet->reason_for_medication ?: null,
                        'slot' => $slot,
                        'stock' => $sheet->stock_level,
                        'low_stock' => ! is_null($sheet->stock_level) && ! is_null($sheet->reorder_level) && $sheet->stock_level <= $sheet->reorder_level,
                        'unit' => $sheet->unit,
                        'is_controlled' => (bool) $sheet->is_controlled,
                        // C2: a CD with no numeric dose can't be given (nothing to register).
                        'cd_needs_quantity' => (bool) $sheet->is_controlled && $this->deductionQuantity($sheet) === null,
                        'cd_schedule' => $sheet->cd_schedule,
                        'as_required' => (bool) $sheet->as_required,
                        'prn' => $prn,
                        'status' => $this->doseBucket($slot, $admin->code ?? null, $isToday, $isPast, $nowMin),
                        'code' => $admin->code ?? null,
                        'reason' => $admin?->reason,
                        'notes' => $admin?->notes,
                        'witnessed_by' => $admin?->witnessed_by,
                        'recorded_at' => $admin?->updated_at?->format('H:i'),
                        'dose_given' => $admin->dose_given ?? null,
                        'recorded_by' => ($admin && $admin->administeredByUser) ? $admin->administeredByUser->name : null,
                    ];
                }
            }
        }

        foreach ($grid as $roundKey => $residents) {
            $grid[$roundKey] = collect($residents)->sortBy('name')->values()->all();
        }

        $rounds = [];
        foreach (self::ROUNDS as $key => $cfg) {
            $rounds[] = ['key' => $key, 'label' => $cfg['label'], 'window' => $cfg['window']];
        }

        // Which rounds (for this home + date) have been ended, with who/when.
        $closures = MedicationRoundClosure::where('home_id', $homeId)->where('date', $date)
            ->with('closedByUser:id,name')->get()
            ->mapWithKeys(fn ($c) => [$c->round => [
                'by' => $c->closedByUser->name ?? null,
                'at' => $c->updated_at?->format('H:i'),
            ]])->all();

        return [
            'rounds' => $rounds,
            'grid' => $grid,
            'date' => $date,
            'currentRound' => $this->roundForTime(now()->format('H:i')),
            'closures' => $closures,
            'home' => \DB::table('home')->where('id', $homeId)->value('title'),
            // Staff who can be named as a CD witness (issue #14 / A2) — excludes the current user.
            'staff' => $this->homeStaffOptions(),
        ];
    }

    /**
     * Derive a dose's timing bucket relative to "now" (only meaningful for today):
     * completed | overdue | due_now (<=60m) | upcoming (<=120m) | later | due (PRN/unscheduled).
     */
    private function doseBucket($slot, $code, $isToday, $isPast, $nowMin)
    {
        if ($code) {
            return 'completed';
        }
        if ($slot === null || strpos((string) $slot, ':') === false) {
            return 'due'; // PRN / unscheduled
        }
        [$h, $m] = explode(':', $slot);
        $slotMin = (int) $h * 60 + (int) $m;
        if ($isPast) {
            return 'overdue';
        }
        if (! $isToday) {
            return 'later';
        }
        $diff = $slotMin - $nowMin;
        if ($diff < 0) {
            return 'overdue';
        }
        if ($diff <= 60) {
            return 'due_now';
        }
        if ($diff <= 120) {
            return 'upcoming';
        }

        return 'later';
    }

    /**
     * Is a second signatory required to administer a controlled drug in this home?
     *
     * REQ-MED-23 (owner, 2026-07-16): compulsory for a registered care home,
     * OPTIONAL for supported living — CQC's position is that a supported-living
     * tenancy is the person's own home, so a care-home witness rule does not
     * automatically apply.
     *
     * This FAILS SAFE. A witness is required unless the home is explicitly and
     * positively identified as supported living. Everything else — a NULL setting,
     * an unrecognised value, a missing home row, or a children's home (whose
     * position is UNRESOLVED, STD-62) — requires the witness.
     *
     * The reasoning, recorded so it is not silently reversed later: the harm of a
     * missing witness in a registered care home outweighs the friction of an
     * unnecessary one in supported living. 12 of the active homes currently have
     * care_setting NULL; defaulting NULL to lenient would silently drop the witness
     * requirement across all of them at once.
     *
     * NOT a compliance determination. The regulatory position for children's homes
     * is unresolved and needs the owner plus a qualified regulatory reviewer.
     */
    protected function cdWitnessRequired(int $homeId): bool
    {
        $setting = \App\Home::where('id', $homeId)->value('care_setting');

        return $setting !== 'adult_supported_living';
    }

    /** Record an administration + auto-deduct stock on a newly-given dose. Returns false if not found. */
    protected function applyRecord(Request $request, MARSheetService $marSheetService): bool
    {
        $request->validate([
            'mar_sheet_id' => 'required|integer',
            'date' => 'required|date',
            'time_slot' => 'required|string|max:10',
            // A,S,R,W,N,O are the original six, offered by frontends 1 and 2.
            // AW,OP,VO,NR are the wider outcome set frontend4 records, from the
            // Care One OS UX Specification (away, omitted-operational,
            // vomited/spat out, not required). Widening an allow-list changes
            // nothing for the existing codes or the front ends that send them.
            //
            // Keep in step with CODE_LABELS in frontend/lib/medicationCodes.js,
            // which is what lets every screen NAME a code it cannot offer.
            //
            // 'PA' (part administered) is deliberately absent — see the note on
            // the reason check below.
            'code' => 'required|in:A,S,R,W,N,O,AW,OP,VO,NR',
            'dose_given' => 'nullable|string|max:100',
            'witnessed_by' => 'nullable|string|max:255',
            'witness_user_id' => 'nullable|integer',
            'reason' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
        ]);

        // Any non-administration must carry a reason (auditable record, REQ-MED-06).
        // 'W' (Withheld) included: withholding a prescribed dose is a deliberate clinical
        // decision and the record must say why. 'S' (asleep) is excluded — the code already
        // states the reason. Keep this list in step with REASON_REQUIRED_CODES on the client.
        /*
         * 'OP' and 'VO' join the list (2026-08-04): an operational omission is a
         * failure of the service and needs a reason and an owner; a vomited dose
         * needs timing and observed amount before anyone can advise. 'AW' (away)
         * and 'NR' (not required) do not — the code already states the reason,
         * the same rule that keeps 'S' off this list.
         *
         * WHY 'PA' (PART ADMINISTERED) IS NOT SUPPORTED YET
         * Part administered means medicine DID go in, so stock must move and a
         * controlled drug still needs a witness. But seven places in this file
         * decide that by comparing `code === 'A'` directly, so a part
         * administration would silently skip every one of those gates — the
         * same class of bug as the sleeping-child one described in
         * frontend/lib/medicationCodes.js. Those seven comparisons must be
         * replaced with one shared "did the resident get this?" helper before
         * 'PA' can be accepted.
         */
        if (in_array($request->input('code'), ['R', 'W', 'N', 'O', 'OP', 'VO'], true) && trim((string) $request->input('reason')) === '') {
            throw ValidationException::withMessages([
                'reason' => 'Please give a reason when a dose is refused, withheld or not given.',
            ]);
        }

        $homeId = $this->getHomeId();
        $userId = (int) Auth::id();

        // EVERYTHING below runs in ONE transaction with the prescription row LOCKED.
        //
        // Why: every check here (PRN daily maximum, minimum interval, already-given) is a
        // READ, and the write that follows depends on it. Without a lock those are two
        // separate reads in two separate transactions — a textbook check-then-act race.
        // Two carers tapping "Given" at the same moment, or one carer double-tapping,
        // both read "not yet at the maximum" and both proceed.
        //
        // The lock is taken on the mar_sheets row, not on mar_administrations, because
        // that is the one row every concurrent write for this prescription has in common.
        // A lock on the administrations table cannot work: for a PRN dose the row does
        // not exist yet, and SELECT ... FOR UPDATE matching zero rows blocks nobody.
        // (An earlier attempt put lockForUpdate() on the administration lookup and was
        // useless for exactly that reason — and worse, it sat on the scheduled branch
        // while the PRN branch, which had just been changed to insert blindly, took no
        // lock at all.)
        return DB::transaction(function () use ($request, $marSheetService, $homeId, $userId) {
            $sheet = MARSheet::forHome($homeId)->active()
                ->lockForUpdate()
                ->find($request->input('mar_sheet_id'));

            if (! $sheet) {
                // THROW, do not return false (audit CR-07). Every record* wrapper turned a
                // false return into `redirect()->with('error', ...)` — a 302, which Inertia
                // resolves as SUCCESS, so the modal cleared and closed as though the dose had
                // been saved. A ValidationException 302s back with the error in the error bag,
                // which the page renders and the one-tap path now handles. A missing sheet is
                // not a normal outcome anyway: it means a deleted/cross-home id reached here.
                throw ValidationException::withMessages([
                    'mar_sheet_id' => 'This prescription could not be found for your home. Nothing was recorded.',
                ]);
            }

            return $this->applyRecordLocked($request, $marSheetService, $sheet, $homeId, $userId);
        });
    }

    /**
     * The body of applyRecord(), running with the prescription row already locked.
     *
     * Split out only so the lock scope is impossible to misread: everything in here is
     * serialised per prescription. Do not call this directly.
     */
    private function applyRecordLocked(Request $request, MARSheetService $marSheetService, MARSheet $sheet, int $homeId, int $userId): bool
    {
        // A round that has been ended is locked — no further recording.
        $round = $this->roundForTime($request->input('time_slot'));
        if (MedicationRoundClosure::where('home_id', $homeId)->where('date', $request->input('date'))->where('round', $round)->exists()) {
            throw ValidationException::withMessages([
                'code' => 'This round has been ended and is locked.',
            ]);
        }

        // Controlled drugs: a second signatory at administration. Whether this is
        // required depends on the care setting (REQ-MED-23) — see cdWitnessRequired().
        if ($sheet->is_controlled
            && DoseOutcome::isGiven($request->input('code'))
            && $this->cdWitnessRequired($homeId)
            && trim((string) $request->input('witnessed_by')) === '') {
            throw ValidationException::withMessages([
                'witnessed_by' => 'A witness is required to administer a controlled drug.',
            ]);
        }

        // Owner C2 (2026-07-28): a controlled drug can't be given until its dose is a proper
        // NUMBER (the unit comes from the medicine, never typed). Without a numeric quantity
        // there is nothing to move in the register — and a CD given with no register movement
        // is exactly the gap the review flagged (Round I3 / CD C-2 / HAZ-24). So block it and
        // point to fixing the prescription's dose, rather than silently recording it off the
        // register. The pharmacist assigns the correct number per medicine.
        if ($sheet->is_controlled
            && DoseOutcome::isGiven($request->input('code'))
            && $this->deductionQuantity($sheet) === null) {
            throw ValidationException::withMessages([
                'code' => 'This controlled drug’s dose needs to be recorded as a quantity before it can be given, so it can be entered in the controlled-drug register. Ask a manager to set the dose amount on the prescription.',
            ]);
        }

        // Owner B2 (2026-07-28): a medicine that isn't in stock must not be recorded as
        // given. When the stock figure is tracked and shows nothing left, refuse the
        // "given" and steer the carer to record it as Not available — a manager can
        // correct the count if it's wrong (carers can't change stock, see decision A1).
        // Untracked stock (null) is left alone: we can't assert it's absent.
        if (DoseOutcome::isGiven($request->input('code'))
            && ! is_null($sheet->stock_level)
            && (float) $sheet->stock_level <= 0) {
            throw ValidationException::withMessages([
                'code' => 'There is no stock recorded for this medicine, so it cannot be marked as given. Record it as “Not available”, or ask a manager to correct the stock count.',
            ]);
        }

        // PRN double-submission guard.
        //
        // A PRN dose has no slot to identify it — every dose of the day is submitted
        // against the same fixed nominal slot, so two taps produce byte-identical
        // payloads. Nothing in the request distinguishes "the carer tapped twice" from
        // "a genuine second dose". The only thing that does is elapsed time.
        //
        // This matters because PRN doses are now discrete INSERTs. Without this, a
        // double-tap records two administrations and deducts stock twice — and it is not
        // hypothetical: the PRN limits below are the only other thing standing in the way,
        // and until now they could not even be set on a real prescription.
        //
        // A genuine second PRN dose seconds after the first is not a real clinical event;
        // a double-tap, or a client retry after a timeout, very much is. So a repeat
        // inside the window is treated as the SAME event: idempotent success, not an
        // error — the dose IS recorded, by the first tap.
        //
        // This is a guard, not the right answer. The right answer is a client-supplied
        // idempotency key so the server can identify the event rather than infer it from
        // the clock. Recorded as such; do not mistake this for a solved problem.
        if ($sheet->as_required) {
            $duplicate = MARAdministration::where('mar_sheet_id', $sheet->id)
                ->where('date', $request->input('date'))
                ->where('code', $request->input('code'))
                ->where('administered_by', $userId)
                ->where('administered_at', '>=', now()->subSeconds(self::PRN_DUPLICATE_WINDOW_SECONDS))
                ->exists();

            if ($duplicate) {
                return true;
            }
        }

        // PRN limits: enforce daily maximum + minimum interval between doses.
        //
        // Counted by (sheet, date) — NOT filtered by time_slot. The old query excluded
        // `time_slot != <this one>` meaning to skip "the dose being re-recorded", but a
        // PRN sheet has ONE fixed nominal slot that every dose of the day is filed
        // under, so that filter excluded every previous dose and the query always came
        // back empty. Both limits were therefore unenforceable through the real UI
        // (audit CR-01). PRN doses are now discrete rows, so "every other dose today"
        // is simply every row for the sheet and date.
        // 'A' only, on both sides: only a dose the resident actually received counts
        // toward the daily maximum or starts the interval clock. Recording "asleep"
        // (S) must never consume a PRN allowance — a child who received nothing and
        // then wakes in pain must not be locked out.
        if ($sheet->as_required && DoseOutcome::isGiven($request->input('code'))) {
            $given = MARAdministration::where('mar_sheet_id', $sheet->id)
                ->where('date', $request->input('date'))
                ->where('code', 'A')
                ->get();
            if ($sheet->prn_max_daily !== null && $given->count() >= (int) $sheet->prn_max_daily) {
                throw ValidationException::withMessages([
                    'code' => 'PRN daily maximum ('.(int) $sheet->prn_max_daily.') already reached for this medication.',
                ]);
            }
            $intervalH = (float) ($sheet->prn_min_interval_hours ?? 0);
            if ($intervalH > 0) {
                // administered_at, not updated_at: the interval must run from when the
                // dose was GIVEN, not from the last time anyone edited the row. Editing
                // a note used to push this clock forward and block the next dose.
                $last = $given->sortByDesc('administered_at')->first()?->administered_at;
                if ($last) {
                    $next = $last->copy()->addMinutes((int) round($intervalH * 60));
                    if (now()->lt($next)) {
                        throw ValidationException::withMessages([
                            'code' => 'Too soon — the next PRN dose is not due until '.$next->format('H:i').'.',
                        ]);
                    }
                }
            }
        }

        // Was this slot already recorded as Given? (so we don't deduct twice on edit)
        //
        // Only meaningful for a SCHEDULED dose, where the slot identifies one dose and
        // re-submitting it is an amendment. A PRN dose is a discrete event that gets its
        // own row (see MARSheetService::administer), so it is never an amendment of an
        // earlier one and must always deduct.
        //
        // Getting this wrong is not theoretical: because every PRN dose shares one fixed
        // nominal slot, this lookup found the FIRST dose of the day and marked doses 2..n
        // as "already given" — so a child could receive four doses and have stock
        // decrement only once.
        if ($sheet->as_required) {
            $wasGiven = false;
        } else {
            $existing = MARAdministration::where('mar_sheet_id', $request->input('mar_sheet_id'))
                ->where('date', $request->input('date'))
                ->where('time_slot', $request->input('time_slot'))
                ->first();
            $wasGiven = $existing && DoseOutcome::isGiven($existing->code);
        }

        $admin = $marSheetService->administer(
            (int) $request->input('mar_sheet_id'),
            $request->only(['date', 'time_slot', 'code', 'dose_given', 'witnessed_by', 'reason', 'notes']),
            $homeId,
            $userId
        );

        if (! $admin) {
            return false;
        }

        // Auto-deduct stock only on a newly-given dose.
        $nowGiven = DoseOutcome::isGiven($request->input('code'));
        if ($nowGiven && ! $wasGiven && ! is_null($sheet->stock_level)) {
            $qty = $this->deductionQuantity($sheet);

            if ($qty !== null) {
                $resident = ServiceUser::where('id', $sheet->client_id)->first();

                // Record the CD register movement FIRST, while $sheet->stock_level still
                // holds the pre-administration balance — the register's opening balance for
                // the very first entry is that stock figure, and deducting first would make
                // it a dose short. A controlled-drug administration is a witnessed movement
                // in the register (append-only running-balance ledger); the witness captured
                // at administration is the second signatory, the balance is computed
                // server-side. Without this the register stays empty even as CDs are given
                // (the exact gap the compliance review flagged).
                if ($sheet->is_controlled) {
                    $cdEntry = \App\Models\ControlledDrugRegister::record(
                        $sheet, 'administered', $qty, $userId,
                        $request->input('witnessed_by'),
                        [
                            'client_name' => $resident->name ?? null,
                            'notes' => 'Administered on the medication round.',
                        ]
                    );

                    // Open a PENDING witness co-signature for the witness to confirm on their
                    // own account (issue #14 / A2). No-op if there was no witness.
                    \App\Models\CdWitnessConfirmation::open(
                        $cdEntry, $userId,
                        $request->input('witness_user_id') ? (int) $request->input('witness_user_id') : null,
                        $request->input('witnessed_by')
                    );
                }

                MedicationStockTransaction::apply($sheet, 'administered', $qty, $userId, [
                    'client_name' => $resident->name ?? null,
                    'notes' => 'Auto-deducted on administration (Medication Round)',
                ]);

                if (Schema::hasColumn('mar_administrations', 'quantity_given')) {
                    $admin->quantity_given = $qty;
                    $admin->save();
                }
            }
            // $qty === null: this prescription has no structured dose quantity, so
            // there is nothing safe to deduct. The dose is still recorded — refusing
            // to record a dose that was physically given would be the worse error.
            // Stock simply does not move, and the gap is visible rather than wrong.
            // (A CD with no structured quantity also cannot get a correct register
            // balance, so it is left out of the register rather than entered wrong.)
        }

        return true;
    }

    /**
     * How much stock does one dose of this prescription consume?
     *
     * Returns null when the answer is not known with certainty — the caller must
     * then skip the deduction rather than guess.
     *
     * This replaces (audit CR-02):
     *
     *     $qty = (float) preg_replace('/[^0-9.]/', '', $doseGiven ?: $sheet->dose);
     *
     * which stripped the non-digits out of a free-text dose and used whatever fell
     * out. For '10 ml (500mg)' that produced 10500 — the volume and the strength
     * concatenated — and one tap on "Given" floored a real 56-unit balance to zero
     * with no error. The default one-tap path sent the prescription's own dose
     * string, so no carer mistake was needed to trigger it, and 'X ml (Ymg)' is the
     * ordinary way a liquid medicine is written.
     *
     * A quantity must never be inferred from text. It comes from the structured
     * dose_quantity column, or it does not come at all.
     *
     * NOTE (known limitation, deliberately not fixed here): mar_sheets.stock_level
     * is `int unsigned`, and MedicationStockTransaction::apply() rounds the new
     * balance. So a fractional dose (7.5 ml) leaves a sub-unit rounding drift each
     * time. That is a real but much smaller problem than the wipe, and converting
     * stock_level to a decimal touches Stock2, the CD register, the stock
     * controller and four validation rules — a separate, staged change.
     */
    private function deductionQuantity(MARSheet $sheet): ?float
    {
        $qty = $sheet->dose_quantity;

        if ($qty === null || (float) $qty <= 0) {
            return null;
        }

        return (float) $qty;
    }
}
