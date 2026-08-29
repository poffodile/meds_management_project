<?php

namespace App\Services\Record7;

use App\Models\Record7\Administration;
use App\Models\Record7\Client;
use App\Models\Record7\IssueState;
use App\Models\Record7\Round;
use App\Models\Record7\ScheduledDose;
use App\Models\Record7\Service;
use App\Models\Record7\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Section 2.2 — recording that a planned medicine was actually given.
 *
 * ONE OUTCOME, AND ONLY THE SUCCESSFUL ONE.
 * Refusal, omission, unavailability and re-offer are Section 2.3. Nothing here
 * can express "not given", and a screen offering a half-built version of that
 * would collect worse records than no record at all.
 *
 * THE PLANNED DOSE IS THE ANCHOR, NOT A DETAIL.
 * An administration is always ABOUT a specific planned obligation. It never
 * floats free of one, the planned row is never edited or removed to mark it
 * done, and the scheduled time is never overwritten with the time somebody
 * happened to press the button. Those are two different facts, and a medicines
 * record that conflates them cannot answer "was it late?" a month later.
 *
 * THE DATABASE SETTLES DUPLICATES, NOT THIS CLASS.
 * Two phones, a double tap, a retry after a timeout, two tabs — all of them put
 * two requests in flight, and every "has this been recorded already?" check has
 * a gap between the question and the answer. So the insert is simply attempted,
 * and a unique-constraint violation is read as "somebody else got there first"
 * and answered with THEIR record. Administrations cannot be deleted, so a
 * duplicate is not something anybody can tidy up afterwards.
 *
 * WHAT IT REFUSES, AND WHY EACH REFUSAL EXISTS
 *   already recorded    a permanent record already answers this obligation
 *   not in this round   the id came from a browser and proves nothing
 *   person away         nobody hands a tablet to somebody in hospital
 *   controlled drug     needs a witness, and that is Section 2.5
 *   as-required         PRN has its own reasoning, and that is Section 2.4
 *   prompted            staff did not administer it, so "given" would be false
 *   self-administered   the person is authorised to take it themselves
 *   prescription state  suspended or stopped is not something to hand over
 */
class AdministrationRecorder
{
    /**
     * Arrangements this section can honestly record as "given".
     *
     * STAFF ADMINISTERED is the ordinary case: the worker hands it over.
     *
     * ASSISTED is included because the worker is physically part of the
     * administration — steadying a hand, holding the cup — which is what
     * "given" means and what a paper MAR chart has always been signed for.
     *
     * PROMPTED is NOT included. There the worker reminds and watches; the
     * person takes it themselves. Recording "given by Noah" would put a false
     * statement about who administered a medicine into a record that can never
     * be deleted. It needs an outcome this enum cannot currently express, so it
     * waits rather than being approximated.
     */
    public const CAN_BE_GIVEN = ['staff_administered', 'assisted'];

    /** What the worker is actually confirming, in their own words. */
    public const CONFIRMATION = [
        'staff_administered' => 'You are recording that you gave this medicine.',
        'assisted' => 'You are recording that you helped them to take this medicine.',
    ];

    public const REFUSAL_REASONS = [
        'client_declined',
        'disliked_form_or_taste',
        'felt_unwell',
        'no_reason_given',
    ];

    public const PERSON_UNAVAILABLE_REASONS = [
        'in_hospital',
        'away_on_leave',
        'at_appointment',
        'not_found_in_service',
    ];

    public const MEDICINE_UNAVAILABLE_REASONS = [
        'stock_unavailable',
        'awaiting_delivery',
        'damaged_or_expired',
        'wrong_item_supplied',
    ];

    public const MISSED_REASONS = [
        'round_not_completed',
        'overlooked',
        'staffing_shortfall',
        'discovered_later',
    ];

    public const MISSED_ACTIONS = [
        'manager_notified',
        'medication_lead_notified',
        'pharmacist_contacted',
        'prescriber_contacted',
        'nhs_111_contacted',
        'emergency_services_contacted',
        'no_escalation_required_under_policy',
    ];

    public function __construct(private readonly AuditRecorder $audit)
    {
    }

    /**
     * Find the dose, or refuse to admit it exists.
     *
     * Every filter is applied in the query rather than checked afterwards, so a
     * dose belonging to another organisation, another house, another person,
     * another date or another slot is not found rather than merely rejected —
     * and the reply cannot be used to discover that it exists at all.
     */
    public function resolve(Round $round, Client $client, int $doseId): ?ScheduledDose
    {
        return ScheduledDose::with(['prescription.medicine', 'administration'])
            ->where('id', $doseId)
            ->where('client_id', $client->id)
            ->where('service_id', $round->service_id)
            ->whereDate('due_at', $round->round_date->toDateString())
            ->where('slot', $round->slot)
            ->whereHas('client', fn ($q) => $q->where('organisation_id', $round->organisation_id))
            ->first();
    }

    /**
     * May this dose be recorded as given, and if not, what is the worker told?
     *
     * Order matters. "Already recorded" comes first because it is the answer to
     * a retry rather than a refusal, and a worker who has just pressed the
     * button twice should be told what happened, not lectured about controlled
     * drugs.
     *
     * @return array{allowed:bool, code:?string, reason:?string, nextSection:?string}
     */
    public function eligibility(ScheduledDose $dose, Client $client): array
    {
        $prescription = $dose->prescription;
        $medicine = $prescription?->medicine;

        if ($dose->administration !== null) {
            return $this->no(
                'already_recorded',
                'This dose already has a recorded outcome. It cannot be recorded twice.'
            );
        }

        if (! $prescription) {
            return $this->no('no_prescription', 'This dose has no prescription attached to it.');
        }

        if ($prescription->status !== 'active') {
            return $this->no(
                'prescription_'.$prescription->status,
                'This prescription is '.$prescription->status.'. Do not give it without asking.'
            );
        }

        // PRN reasoning is a different question entirely — was it needed, has
        // enough time passed, did it work afterwards. Section 2.4.
        if ($prescription->kind === 'prn') {
            return $this->no(
                'as_required',
                'This is an as-required medicine. Recording it needs the as-required '
                .'workflow, which is not built yet.',
                '2.4'
            );
        }

        if ($prescription->support_type === 'self_administered'
            && $prescription->self_administration_monitoring === 'none') {
            return $this->no(
                'self_managed',
                'This medicine is fully self-managed. It does not need individual staff dose recording.',
                null
            );
        }

        // A controlled drug needs a second person to witness it. The general
        // "given" button existing is not a reason to skip that.
        if ($medicine?->is_controlled) {
            return $this->no(
                'witness_required',
                'This is a controlled drug and needs a witness. Witnessed administration '
                .'is not built yet, so it cannot be recorded here.',
                '2.5'
            );
        }

        if (! in_array($prescription->support_type, self::CAN_BE_GIVEN, true)) {
            return $this->no(
                'support_type_'.$prescription->support_type,
                $prescription->support_type === 'self_administered'
                    ? 'They are authorised to take this themselves. Recording it as given by '
                        .'staff would say somebody handed it to them.'
                    : 'They take this themselves after a reminder. Recording it as given by '
                        .'staff would say somebody handed it to them.',
                '2.3'
            );
        }

        // Callum is in hospital. His dose stays planned and still needs an
        // answer — but the answer is not "given".
        if (! $client->isAvailable()) {
            return $this->no(
                'person_away',
                $client->statusWord().'. This cannot be recorded as given while they are away; '
                .'it needs an outcome saying why it was not given.',
                '2.3'
            );
        }

        return ['allowed' => true, 'code' => null, 'reason' => null, 'nextSection' => null];
    }

    /**
     * Write it, or hand back the record that already exists.
     *
     * @return array{administration:Administration, created:bool}
     */
    public function recordGiven(
        User $user,
        Round $round,
        Client $client,
        ScheduledDose $dose,
        ?string $notes,
        Request $request
    ): array {
        try {
            $administration = Administration::create([
                'reference' => 'R7A-'.strtoupper(Str::random(12)),
                'scheduled_dose_id' => $dose->id,
                'prescription_id' => $dose->prescription_id,
                'client_id' => $client->id,
                'service_id' => $round->service_id,

                // THE AUTHENTICATED USER. Never an id from the request — a
                // worker must not be able to sign a medicine in somebody
                // else's name, and joining a colleague's round must not make
                // your actions look like theirs.
                'recorded_by_user_id' => $user->id,

                'outcome' => 'given',

                // No reason code. A medicine given as prescribed does not need
                // one, and forcing a choice would fill the record with filler.
                'reason_code' => null,
                'notes' => $this->cleanNotes($notes),

                // The server's clock. A browser can say anything, and the time
                // a medicine was given is a clinical fact. The dose's own
                // due_at is left exactly as it was.
                'administered_at' => now(),
            ]);
        } catch (UniqueConstraintViolationException $clash) {
            // Somebody — or another request from this same worker — got there
            // first. The obligation is answered, so this is a safe outcome
            // rather than an error. Hand back THEIR record.
            $existing = Administration::where('scheduled_dose_id', $dose->id)
                ->whereNull('corrects_administration_id')
                ->first();

            if (! $existing) {
                throw $clash;
            }

            $this->audit->record(
                eventType: 'medication_administration_duplicate_blocked',
                result: AuditRecorder::WARNING,
                user: $user,
                serviceId: $round->service_id,
                reason: 'A second administration was attempted for a dose already recorded.',
                riskLevel: 'medium',
                metadata: $this->trail($round, $client, $dose, $existing),
                request: $request
            );

            return ['administration' => $existing, 'created' => false];
        }

        $this->audit->record(
            eventType: 'medication_administered',
            result: AuditRecorder::SUCCESS,
            user: $user,
            serviceId: $round->service_id,
            reason: null,
            riskLevel: 'medium',
            metadata: $this->trail($round, $client, $dose, $administration),
            request: $request
        );

        return ['administration' => $administration, 'created' => true];
    }

    /**
     * Record an explicit Section 2.3 outcome for a planned dose.
     *
     * This shares the same resolved round/client/dose context as Section 2.2.
     * The method still validates every clinical boundary again because the
     * posted outcome is only an assertion from a browser, not authority.
     *
     * @return array{administration:Administration, created:bool}
     */
    public function recordNonAdministration(
        User $user,
        Round $round,
        Client $client,
        ScheduledDose $dose,
        string $outcome,
        array $input,
        Request $request
    ): array {
        $prescription = $dose->prescription;
        $medicine = $prescription?->medicine;

        if (! in_array($outcome, ['refused', 'person_unavailable', 'not_available', 'missed'], true)) {
            throw new RuntimeException('That outcome is not available in Section 2.3.');
        }

        if (! $prescription) {
            throw new RuntimeException('This dose has no prescription attached to it.');
        }

        if ($prescription->kind === 'prn') {
            throw new RuntimeException('As-required medicines are Section 2.4.');
        }

        if ($outcome === 'withheld') {
            throw new RuntimeException('Withheld recording is not part of Section 2.3.');
        }

        if ($medicine?->is_controlled && ! (bool) ($input['controlled_drug_no_quantity_removed'] ?? false)) {
            throw new RuntimeException(
                'For a controlled drug, confirm no quantity was removed from secure storage. '
                .'If any quantity was removed, use the controlled-drug pathway.'
            );
        }

        $reason = $this->requireReason($outcome, $input['reason_code'] ?? null);
        $notes = $this->cleanMeaningful($input['notes'] ?? null, $outcome === 'missed');
        $actionTaken = $this->cleanMeaningful($input['action_taken'] ?? null, $outcome === 'missed');
        $immediateAction = $input['immediate_action_code'] ?? null;

        if ($outcome === 'missed' && ! in_array($immediateAction, self::MISSED_ACTIONS, true)) {
            throw new RuntimeException('Choose the immediate action taken for the missed dose.');
        }

        $target = $this->reofferTarget($round, $client, $dose, $outcome, $input['reoffer_of_administration_id'] ?? null);

        try {
            $administration = Administration::create([
                'reference' => 'R7N-'.strtoupper(Str::random(12)),
                'scheduled_dose_id' => $dose->id,
                'prescription_id' => $dose->prescription_id,
                'client_id' => $client->id,
                'service_id' => $round->service_id,
                'recorded_by_user_id' => $user->id,
                'outcome' => $outcome,
                'reason_code' => $reason,
                'notes' => $notes,
                'action_taken' => $actionTaken,
                'immediate_action_code' => $outcome === 'missed' ? $immediateAction : null,
                'controlled_drug_no_quantity_removed' => $medicine?->is_controlled ? true : null,
                'administered_at' => now(),
                'reoffer_of_administration_id' => $target?->id,
            ]);
        } catch (UniqueConstraintViolationException $clash) {
            $existing = Administration::where('scheduled_dose_id', $dose->id)
                ->whereNull('corrects_administration_id')
                ->where('reoffer_of_administration_id', $target?->id)
                ->first();

            if (! $existing) {
                throw $clash;
            }

            return ['administration' => $existing, 'created' => false];
        }

        if ($outcome === 'person_unavailable' && $reason === 'not_found_in_service') {
            $this->createWelfareAttention($round, $administration);
        }

        $this->audit->record(
            eventType: 'medication_non_administration_recorded',
            result: AuditRecorder::SUCCESS,
            user: $user,
            serviceId: $round->service_id,
            reason: null,
            riskLevel: $outcome === 'missed' ? 'high' : 'medium',
            metadata: $this->trail($round, $client, $dose, $administration) + [
                'reason_code' => $reason,
                'reoffer_of_administration_id' => $target?->id,
            ],
            request: $request
        );

        return ['administration' => $administration, 'created' => true];
    }

    /**
     * The structured trail, and nothing beyond it.
     *
     * Identifiers, both times and the outcome — enough to reconstruct exactly
     * what was recorded and by whom. Deliberately no clinical free text: the
     * notes belong on the clinical record, and copying them into the access
     * audit would spread the same sensitive sentence across two tables with two
     * different retention rules.
     */
    private function trail(Round $round, Client $client, ScheduledDose $dose, Administration $administration): array
    {
        return [
            'round_id' => $round->id,
            'slot' => $round->slot,
            'client_id' => $client->id,
            'prescription_id' => $dose->prescription_id,
            'medicine_id' => $dose->prescription?->medicine_id,
            'scheduled_dose_id' => $dose->id,
            'support_type' => $dose->prescription?->support_type,

            // BOTH times. Either one alone makes lateness unanswerable later.
            'due_at' => $dose->due_at->toIso8601String(),
            'administered_at' => $administration->administered_at->toIso8601String(),
            'minutes_late' => $dose->minutesLate($administration->administered_at),

            'outcome' => $administration->outcome,
            'administration_id' => $administration->id,
            'administration_reference' => $administration->reference,
        ];
    }

    /** Empty is null, not an empty string pretending to be a note. */
    private function cleanNotes(?string $notes): ?string
    {
        $notes = trim((string) $notes);

        return $notes === '' ? null : Str::limit($notes, 495, '');
    }

    private function requireReason(string $outcome, ?string $reason): string
    {
        $allowed = match ($outcome) {
            'refused' => self::REFUSAL_REASONS,
            'person_unavailable' => self::PERSON_UNAVAILABLE_REASONS,
            'not_available' => self::MEDICINE_UNAVAILABLE_REASONS,
            'missed' => self::MISSED_REASONS,
            default => [],
        };

        if (! in_array($reason, $allowed, true)) {
            throw new RuntimeException('Choose the structured reason for this outcome.');
        }

        return $reason;
    }

    private function cleanMeaningful(?string $value, bool $required): ?string
    {
        $clean = trim(preg_replace('/\s+/', ' ', (string) $value));

        if ($clean === '') {
            if ($required) {
                throw new RuntimeException('Write a meaningful explanation.');
            }

            return null;
        }

        $normalised = strtolower(str_replace(['.', '/', '\\', '_'], '', $clean));

        if (in_array($normalised, ['na', 'none', 'nil', 'no', 'notapplicable', '-'], true)
            || preg_match('/^-+$/', $clean)) {
            throw new RuntimeException('Write a meaningful explanation, not filler.');
        }

        return Str::limit($clean, 495, '');
    }

    private function reofferTarget(
        Round $round,
        Client $client,
        ScheduledDose $dose,
        string $outcome,
        mixed $targetId
    ): ?Administration {
        if ($targetId === null || $targetId === '') {
            return null;
        }

        if (! in_array($outcome, ['given', 'self_administered'], true)) {
            throw new RuntimeException('Only an accepted same-dose re-offer can link to a refusal.');
        }

        $target = Administration::where('service_id', $round->service_id)
            ->where('client_id', $client->id)
            ->where('scheduled_dose_id', $dose->id)
            ->where('prescription_id', $dose->prescription_id)
            ->where('outcome', 'refused')
            ->whereNull('reoffer_of_administration_id')
            ->find((int) $targetId);

        if (! $target) {
            throw new RuntimeException('A re-offer must refer to the original refusal for this same dose.');
        }

        return $target;
    }

    private function createWelfareAttention(Round $round, Administration $administration): void
    {
        $service = Service::findOrFail($round->service_id);

        IssueState::firstOrCreate([
            'organisation_id' => $service->organisation_id,
            'service_id' => $round->service_id,
            'issue_type' => 'welfare_check',
            'source_id' => $administration->id,
        ], [
            'issue_key' => 'welfare_check:'.$administration->id,
            'acknowledged_at' => null,
        ]);
    }

    private function no(string $code, string $reason, ?string $nextSection = null): array
    {
        return [
            'allowed' => false,
            'code' => $code,
            'reason' => $reason,
            'nextSection' => $nextSection,
        ];
    }
}
