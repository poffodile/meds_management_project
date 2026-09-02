<?php

namespace App\Services\Record7;

use App\Models\Record7\Administration;
use App\Models\Record7\Client;
use App\Models\Record7\Round;
use App\Models\Record7\ScheduledDose;
use Illuminate\Support\Carbon;

/**
 * One person, opened from a round, ready to be checked before anything is given.
 *
 * WHAT THIS SCREEN IS FOR
 * The last look before a medicine is handed over: is this the right person, is
 * there anything that must stop me, and what exactly am I about to give them.
 * It is not a care profile and not a medication history — only what belongs to
 * this person, in this house, in this round, on this date.
 *
 * NOTHING CAN BE RECORDED FROM HERE.
 * Section 2.1 reads. There is no method on this class that writes anything, no
 * outcome, no correction, no stock movement. Recording begins at 2.2, and a
 * control that looked as though it recorded something would be worse than no
 * control at all.
 *
 * AN ID FROM A BROWSER IS NOT PROOF OF ANYTHING.
 * A client id arrives as a number in a URL. It is never used to load anybody
 * until it has been resolved through the organisation, the house AND the round
 * — three filters, all applied before the record is fetched. A person from
 * another company, another house or another round is not "not shown", they are
 * not found.
 *
 * NOTHING IS INVENTED.
 * Where Record7 holds no photograph and no separate sensitivity record, this
 * says so in words rather than producing a placeholder that looks like data. A
 * fabricated blank is more dangerous than an honest gap, because a reader
 * cannot tell the difference between "no allergies" and "nobody has asked".
 */
class RoundPersonView
{
    public function __construct(private readonly AdministrationRecorder $recorder)
    {
    }

    /** The words staff use for each agreed support arrangement. */
    public const SUPPORT_WORDS = [
        'staff_administered' => 'Staff administered',
        'assisted' => 'Assisted',
        'prompted' => 'Prompted',
        'self_administered' => 'Self-administered',
    ];

    /** What each arrangement actually means for the person holding the pot. */
    public const SUPPORT_MEANING = [
        'staff_administered' => 'You give this and record it.',
        'assisted' => 'They take it themselves with your physical help.',
        'prompted' => 'They take it themselves. You remind and watch, you do not hand it over.',
        'self_administered' => 'Authorised to manage this themselves. Record it; do not hand it to them.',
    ];

    /**
     * Resolve a person through the round, or refuse.
     *
     * Three filters, in this order: the house owns the client, the round owns
     * the house, and the client has a dose planned in THIS round. The last one
     * is what stops somebody who genuinely lives here being opened as though
     * they were part of a round they are not in.
     */
    public function resolve(Round $round, int $clientId): ?Client
    {
        $client = Client::where('id', $clientId)
            ->where('service_id', $round->service_id)
            ->where('organisation_id', $round->organisation_id)
            ->first();

        if (! $client) {
            return null;
        }

        $inThisRound = ScheduledDose::where('client_id', $client->id)
            ->where('service_id', $round->service_id)
            ->whereDate('due_at', $round->round_date->toDateString())
            ->where('slot', $round->slot)
            ->exists();

        return $inThisRound ? $client : null;
    }

    /**
     * Where a worker should be put back after doing something for this person
     * on a screen outside the round.
     *
     * WHY THIS EXISTS. The as-required and controlled-drug workflows are
     * reached FROM a round, but they are not part of one — they have their own
     * routes and their own screens, and a worker who finished on one of them
     * was left there. On a shift that means going back to Today and starting
     * the round again to reach the next person, which is how somebody loses
     * their place in a list of six people and gives one of them nothing.
     *
     * The answer is only ever their round screen or Today, and it is decided
     * from the record rather than from where the browser says it came from:
     * a referrer can be forged, absent, or simply stale, and this has to be
     * right when somebody has had the page open through a shift change.
     *
     * Returns null when there is no open round holding this person, which the
     * caller reads as "send them to Today" — the honest answer, because their
     * round screen would refuse them anyway.
     */
    public function openRoundHolding(int $serviceId, Client $person, ?Carbon $now = null): ?Round
    {
        $round = app(RoundEntry::class)->openRoundFor($serviceId, $now);

        if (! $round) {
            return null;
        }

        return $this->resolve($round, $person->id) ? $round : null;
    }

    /**
     * Everything the screen shows, in the order it should be read.
     *
     * Identity first, then anything that could stop the round, then the
     * medicines. That order is the safety check itself: knowing what to give
     * before knowing who you are giving it to is how the wrong person gets the
     * right medicine.
     */
    public function forPerson(Round $round, Client $client, ?Carbon $now = null): array
    {
        $now ??= now();

        return [
            'person' => $this->identity($client),
            'safety' => $this->safety($client),
            'medicines' => $this->medicines($round, $client, $now),
        ];
    }

    /* ── Identity ───────────────────────────────────────────────────────── */

    /**
     * Who this is. Public because Section 2.4 needs the same identity block
     * without a round — a PRN happens at three in the morning, not on a
     * timetable, and the person must be checked exactly as carefully.
     */
    public function identityFor(Client $client): array
    {
        return $this->identity($client);
    }

    /** Allergies and what is not recorded, likewise reusable without a round. */
    public function safetyFor(Client $client): array
    {
        return $this->safety($client);
    }

    private function identity(Client $client): array
    {
        return [
            'id' => $client->id,
            'fullName' => $client->full_name,
            // Only when it tells the reader something. "Terence Boyle, known
            // as Terry" is worth a line; "Callum Fraser, known as Callum" is
            // the same name twice, and four of the six people in the fixture
            // were getting that line. A preferred name that is simply the
            // first name is not a preferred name.
            'preferredName' => $this->preferredName($client),
            'room' => $client->room_name,

            // The second identity check every medicines policy asks for.
            'bornOn' => $client->date_of_birth?->format('j F Y'),
            'reference' => $client->reference,

            // RECORD7 HOLDS NO PHOTOGRAPHS. Not a nullable column that happens
            // to be empty — the concept does not exist in the schema at all.
            // Saying so is the honest answer; rendering a grey silhouette would
            // suggest a photo had been looked for and not found.
            'photo' => null,
            'photoState' => 'not_held',

            'status' => $client->status,
            'statusWord' => $client->statusWord(),
            'available' => $client->isAvailable(),

            // A real field, written by staff about how this person prefers to
            // be supported. Not a clinical warning, and not presented as one.
            'supportNote' => $client->support_note,
        ];
    }

    /** Null unless the person is actually called something else. */
    private function preferredName(Client $client): ?string
    {
        $preferred = trim((string) $client->preferred_name);

        if ($preferred === '') {
            return null;
        }

        $firstName = explode(' ', trim((string) $client->full_name))[0] ?? '';

        return strcasecmp($preferred, $firstName) === 0
            || strcasecmp($preferred, (string) $client->full_name) === 0
                ? null
                : $preferred;
    }

    /* ── Critical safety ────────────────────────────────────────────────── */

    /**
     * Allergies, worst first, and an honest account of what is not recorded.
     *
     * RECORD7 HAS NO SEPARATE "SENSITIVITY" RECORD. It has allergies with a
     * severity, and mild or moderate ones are the nearest thing the data holds
     * to a sensitivity. They are shown for what they are rather than relabelled
     * — inventing a clinical distinction the source does not make would be
     * worse than the gap it papers over.
     */
    private function safety(Client $client): array
    {
        $allergies = $client->allergies()->get()
            ->sortByDesc(fn ($allergy) => match ($allergy->severity) {
                'life_threatening' => 3,
                'severe' => 2,
                'moderate' => 1,
                default => 0,
            })
            ->values()
            ->map(fn ($allergy) => [
                'id' => $allergy->id,
                'substance' => $allergy->substance,
                'reaction' => $allergy->reaction,
                'severity' => $allergy->severity,
                'severityWord' => $allergy->severityWord(),
                'critical' => $allergy->isCritical(),
                'source' => $allergy->source,
                'recordedOn' => $allergy->recorded_at?->format('j F Y'),
            ])->all();

        return [
            'allergies' => $allergies,
            'criticalCount' => count(array_filter($allergies, fn ($a) => $a['critical'])),

            // "None recorded" and "nobody has ever asked" are different facts,
            // and a reader has to be able to tell them apart before giving a
            // medicine. Record7 cannot currently distinguish them, and says so.
            'allergiesState' => $allergies === [] ? 'none_recorded' : 'recorded',

            // Stated rather than implied by an empty section.
            'sensitivitiesState' => 'not_separately_held',
        ];
    }

    /* ── Medicines due in this round ────────────────────────────────────── */

    /**
     * Only what is planned for this person, in this round, on this date.
     *
     * Filtered by service, date and slot as well as by person — so a dose from
     * this morning cannot appear in tonight's round, and nothing from the
     * person's history appears merely because it belongs to them.
     */
    private function medicines(Round $round, Client $client, Carbon $now): array
    {
        return ScheduledDose::with(['prescription.medicine', 'latestAdministration.recordedBy'])
            ->where('client_id', $client->id)
            ->where('service_id', $round->service_id)
            ->whereDate('due_at', $round->round_date->toDateString())
            ->where('slot', $round->slot)
            ->orderBy('due_at')
            ->get()
            ->map(function ($dose) use ($client, $now) {
                $prescription = $dose->prescription;
                $medicine = $prescription?->medicine;
                $support = $prescription?->support_type ?? 'staff_administered';
                $eligibility = $this->recorder->eligibility($dose, $client);
                $openRefusal = $this->recorder->openRefusalFor($dose);

                // The answer that stands, not whichever row came back first.
                $answer = $dose->latestAdministration;

                return [
                    'doseId' => $dose->id,

                    // The prescription this dose belongs to. Carried so the
                    // round can hand the worker to the screen that CAN record
                    // it when this one cannot: a controlled drug and an
                    // as-required medicine are both refused here and answered
                    // elsewhere, and a hand-off needs the prescription.
                    'prescriptionId' => $prescription?->id,

                    // Medicine identity, from the medicine record.
                    'name' => $medicine?->name,
                    'strength' => $medicine?->strength,
                    'form' => $medicine?->form,
                    'controlled' => (bool) $medicine?->is_controlled,
                    // Held for later interoperability, empty until a catalogue
                    // is synchronised. Shown as absent rather than as blank.
                    'dmdCode' => $medicine?->dmd_code,

                    // What is actually to be given, from the prescription.
                    'dose' => $prescription?->dose,
                    'route' => $prescription?->route,
                    'frequency' => $prescription?->frequency_text,
                    'directions' => $prescription?->instructions,

                    'dueAt' => $dose->due_at->format('H:i'),
                    'timeSensitive' => (bool) $prescription?->is_time_critical,
                    'graceMinutes' => $dose->grace_minutes,
                    'late' => $dose->isLate($now),
                    'minutesLate' => $dose->minutesLate($now),
                    // "355 min late" makes a support worker do arithmetic to
                    // find out it is nearly six hours. Today already writes
                    // elapsed time as hours and minutes; the round says it the
                    // same way rather than inventing a second dialect.
                    'latePhrase' => $this->lateness($dose->minutesLate($now)),

                    // PER MEDICINE, never rolled up. A person can be handed one
                    // tablet and watched taking another, and a single label
                    // across both would be wrong about one of them.
                    'support' => $support,
                    'supportWord' => self::SUPPORT_WORDS[$support] ?? $support,
                    'supportMeaning' => self::SUPPORT_MEANING[$support] ?? null,
                    'selfAdministered' => $support === 'self_administered',

                    // A change nobody was told about is the classic cause of a
                    // wrong dose after a few days off.
                    'changed' => $prescription?->changedRecently()
                        ? [
                            'on' => $prescription->changed_at->format('j F'),
                            'note' => $prescription->change_note,
                        ]
                        : null,

                    // Read-only. Section 2.1 shows whether something has been
                    // recorded; it provides no way to record it.
                    'recorded' => $answer !== null,
                    'recordedOutcome' => $answer?->outcomeWord(),

                    // The CODE as well as the word. "Refused" and "Given" are
                    // both recorded outcomes, and a screen that knows only that
                    // something was recorded has to paint them the same — which
                    // is how a stock failure ends up looking like a completed
                    // administration.
                    'recordedCode' => $answer?->outcome,

                    // Kept visible after the event. A worker who has just
                    // signed for something has to be able to look at it and see
                    // the time and the name, or the only way to check is to
                    // record it again.
                    'recordedAt' => $answer?->administered_at->format('H:i'),
                    'recordedBy' => $answer?->recordedBy?->displayName(),

                    // The record itself, so somebody who sees that it is wrong
                    // can ask about THAT row rather than about the dose. A dose
                    // may carry a refusal and a re-offer; a correction has to
                    // name which of them it means.
                    'administrationId' => $answer?->id,

                    // A correction is asked about a record that has not already
                    // been corrected and is not itself a correction. Worked out
                    // here so the round does not offer a door the correction
                    // screen would immediately close.
                    'correctable' => $answer !== null
                        && $answer->corrects_administration_id === null
                        && ! Administration::where('service_id', $dose->service_id)
                            ->where('corrects_administration_id', $answer->id)
                            ->exists(),

                    // ONE WORD FOR ONE OUTCOME, RESOLVED HERE.
                    // The pill and the line beneath it were being worded
                    // separately, so the same fact appeared twice in two
                    // different words — "Taken themselves" above
                    // "Self-administered at 08:10". Deciding it once, on the
                    // server, is the only way they cannot drift apart.
                    'recordedWord' => $answer ? $this->recordedWord($answer, $support) : null,

                    // LATENESS DOES NOT STOP BEING TRUE WHEN IT IS RECORDED.
                    // A dose is "late" only while it is unanswered, so the
                    // moment it was signed for the late marker vanished — and a
                    // medicine given eight hours late then read exactly like
                    // one given on time. The delay is measured against the
                    // moment it was actually given and kept.
                    // WHY, not just WHAT. A refusal that does not say why is a
                    // gap in the record, and the worker who wrote it a minute
                    // ago is the only person who can still fill it in.
                    'recordedReason' => $answer?->reason_code
                        ? (AdministrationRecorder::REASON_WORDS[$answer->reason_code]
                            ?? $answer->reason_code)
                        : null,
                    'recordedNotes' => $answer?->notes,

                    // What was actually done about a missed dose, and who was
                    // told. Recorded at the time; useless if never shown.
                    'recordedAction' => $answer?->action_taken,
                    'recordedEscalation' => $answer?->immediate_action_code
                        ? (AdministrationRecorder::MISSED_ACTION_WORDS[
                                $answer->immediate_action_code
                            ] ?? $answer->immediate_action_code)
                        : null,

                    // A refusal on this dose that nobody has offered again yet.
                    // Present means the screen may offer a second attempt; the
                    // recorder and the database both check it again.
                    'reofferOf' => $openRefusal?->id,
                    'reofferedFrom' => $answer?->reoffer_of_administration_id
                        ? $this->earlierAnswer($answer)
                        : null,

                    'recordedLatePhrase' => $answer
                        && $dose->minutesLate($answer->administered_at) > 0
                            ? $this->duration($dose->minutesLate($answer->administered_at))
                            : null,

                    // Whether Section 2.2 can honestly record this as given,
                    // and if not, the reason in the worker's own language.
                    // Asked of the same class that will refuse the write, so
                    // the screen can never offer an action the server declines.
                    'canBeGiven' => $eligibility['allowed'],

                    // Fully self-managed: the person handles this one entirely
                    // themselves by agreement, so there is nothing for staff to
                    // record and nothing for the round to wait on.
                    'selfManaged' => (bool) $prescription?->isFullySelfManaged(),
                    'blockedReason' => $eligibility['reason'],
                    'blockedCode' => $eligibility['code'],
                    'nextSection' => $eligibility['nextSection'],

                    // Truthful about what the record does not say, rather than
                    // rendering an empty line that looks like "no directions".
                    'missing' => array_values(array_filter([
                        $medicine?->strength ? null : 'strength',
                        $medicine?->form ? null : 'form',
                        $prescription?->route ? null : 'route',
                        $prescription?->dose ? null : 'dose',
                    ])),
                ];
            })->values()->all();
    }

    /**
     * What to CALL an outcome on this screen.
     *
     * The stored code is the record; this is the sentence a worker reads, and
     * two of them need saying more carefully than the shared vocabulary does.
     *
     * "Not available" sits beside a person whose own availability is a
     * first-class fact here, so it says which one was missing.
     *
     * "Given" beside an assisted arrangement reads as the worker having handed
     * it over on their own. They did not — they steadied a hand. The stored
     * outcome is still `given`, because staff were physically part of the
     * administration, but the words on the screen say what actually happened.
     */
    public function recordedWord(Administration $administration, string $support): string
    {
        if ($administration->outcome === 'not_available') {
            return 'Medicine not available';
        }

        if ($administration->outcome === 'self_administered') {
            return 'Taken themselves';
        }

        if ($administration->outcome === 'given' && $support === 'assisted') {
            return 'Given with help';
        }

        return $administration->outcomeWord();
    }

    /**
     * The answer this one follows, said plainly.
     *
     * A second attempt that shows no sign of the first reads as though the
     * refusal never happened — which is exactly what an append-only record
     * exists to prevent.
     */
    private function earlierAnswer(Administration $administration): ?array
    {
        $earlier = Administration::find($administration->reoffer_of_administration_id);

        if (! $earlier) {
            return null;
        }

        return [
            'word' => $earlier->outcomeWord(),
            'at' => $earlier->administered_at->format('H:i'),
            'by' => $earlier->recordedBy?->displayName(),
            'reason' => $earlier->reason_code
                ? (AdministrationRecorder::REASON_WORDS[$earlier->reason_code] ?? $earlier->reason_code)
                : null,
        ];
    }

    /** Under an hour stays in minutes; past that, hours and minutes. */
    private function lateness(int $minutes): string
    {
        return $this->duration($minutes).' late';
    }

    /**
     * The same span of time without the word "late" on the end.
     *
     * The recorded line already says "after it was due", and "7h 12m late after
     * it was due" is the kind of sentence nobody writes on purpose.
     */
    private function duration(int $minutes): string
    {
        if ($minutes < 60) {
            return $minutes.' min';
        }

        $rest = $minutes % 60;

        return intdiv($minutes, 60).'h'.($rest ? ' '.$rest.'m' : '');
    }

    /**
     * Where this person sits in the round, so next and previous can only ever
     * move within it.
     *
     * Derived from the authorised queue rather than from an id in a request, so
     * neither control can walk out of the round, the house or the organisation.
     */
    public function neighbours(array $queue, int $clientId): array
    {
        $ids = array_column($queue, 'clientId');
        $at = array_search($clientId, $ids, true);

        if ($at === false) {
            return ['previous' => null, 'next' => null, 'position' => null, 'total' => count($ids)];
        }

        return [
            'previous' => $queue[$at - 1] ?? null,
            'next' => $queue[$at + 1] ?? null,
            'position' => $at + 1,
            'total' => count($ids),
        ];
    }
}
