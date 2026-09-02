<?php

namespace App\Http\Controllers\Record7;

use App\Models\Record7\Administration;
use App\Models\Record7\ReviewItem;
use App\Models\Record7\Service;
use App\Services\Record7\AccessPolicy;
use App\Services\Record7\AuditRecorder;
use App\Services\Record7\RoundPersonView;
use App\Services\Record7\SessionManager;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use RuntimeException;

/**
 * Asking for a recorded administration to be corrected.
 *
 * WHY THIS EXISTS. Section 1.2 built the approval half of an administration
 * correction and Section 2.7 extended it, and both were reachable only from a
 * seeded fixture: the one place in the whole application that raised a review
 * item was the stock screen. A worker who signed for the wrong outcome — the
 * commonest recording error there is — had no way to say so, and the manager's
 * approve path could never receive a real request.
 *
 * WHAT THIS IS NOT. It does not correct anything. It writes a request and
 * stops. The original administration is not touched, no corrected record is
 * written here, and the person asking gains no authority to approve it: that
 * belongs to `correction_approval`, is re-checked at the moment of the
 * decision, and is deliberately held by different people from the ones who
 * record doses.
 *
 * The request names an EXISTING administration and says what the record should
 * say instead, because that is the contract ManagerActions::correct() already
 * enforces — a manager approves the requester's outcome and may not substitute
 * their own. Asking for "a correction" without saying to what would produce a
 * request nobody could approve.
 */
class CorrectionController extends R7Controller
{
    public function __construct(
        private readonly AccessPolicy $policy,
        private readonly SessionManager $sessions,
        private readonly RoundPersonView $personView,
        private readonly AuditRecorder $audit
    ) {
    }

    /**
     * THE OUTCOMES A CORRECTION MAY ASK FOR.
     *
     * Taken to match ManagerActions::correct() exactly. A request naming
     * anything outside this list is refused there with "there is nothing to
     * approve", so it is refused here instead, where the person can still fix
     * it.
     *
     * `withheld` is included because the approval path accepts it as a
     * correction TARGET — Section 2.7 §8.3 is explicit that correcting to
     * withheld is a correction and not a withheld recording, which remains
     * unbuilt.
     */
    private const OUTCOMES = [
        'given' => 'It was given',
        'self_administered' => 'They took it themselves',
        'refused' => 'They refused it',
        'withheld' => 'It was withheld',
        'not_available' => 'It was not available',
        'missed' => 'It was missed',
    ];

    /** The house from the session, the administration from the house. */
    private function context(Request $request, int $administrationId): array
    {
        $user = $this->user();
        $serviceId = $this->sessions->serviceId($request);

        abort_unless($user !== null && $serviceId !== null, 403);

        // NOTHING FROM THE BROWSER IS TRUSTED. The house comes from the
        // session and the record must belong to it, so an id from another
        // house is a 404 rather than a record somebody may act on.
        $administration = Administration::with(['client', 'prescription.medicine', 'recordedBy'])
            ->where('service_id', $serviceId)
            ->findOrFail($administrationId);

        // The organisation is checked as well as the house, exactly as the
        // approval path checks it before writing anything.
        abort_unless(
            (int) $administration->client?->organisation_id
                === (int) Service::find($serviceId)?->organisation_id,
            403
        );

        return [$user, $serviceId, $administration];
    }

    /** Why this record cannot be asked about, if it cannot. */
    private function refusal(Administration $administration, int $serviceId): ?string
    {
        if ($administration->corrects_administration_id !== null) {
            return 'That record is itself a correction. Ask about the original instead.';
        }

        if (Administration::where('service_id', $serviceId)
            ->where('corrects_administration_id', $administration->id)->exists()) {
            return 'That record has already been corrected.';
        }

        if ($this->pendingRequestFor($administration, $serviceId)) {
            return 'Somebody has already asked for that record to be corrected.';
        }

        return null;
    }

    private function pendingRequestFor(Administration $administration, int $serviceId): bool
    {
        return ReviewItem::where('service_id', $serviceId)
            ->where('kind', 'correction_request')
            ->where('subject_type', 'administration')
            ->where('subject_id', $administration->id)
            ->where('status', 'open')
            ->exists();
    }

    /** The form: what was recorded, and what it should have said. */
    public function show(Request $request, int $administration)
    {
        $this->useR7Layout($request);

        [$user, $serviceId, $record] = $this->context($request, $administration);

        $house = Service::find($serviceId);
        $person = $record->client;

        // Their round screen while a round holds them, Today otherwise —
        // decided from the record, not from where the browser says it came.
        $inRound = $person
            ? $this->personView->openRoundHolding($serviceId, $person)
            : null;

        return Inertia::render('CorrectionRequest', [
            'house' => ['id' => $house?->id, 'name' => $house?->name],
            'person' => $person ? $this->personView->identityFor($person) : null,

            'record' => [
                'id' => $record->id,
                'reference' => $record->reference,
                'medicine' => $record->prescription?->medicine?->name,
                'strength' => $record->prescription?->medicine?->strength,
                'dose' => $record->prescription?->dose,
                'outcome' => $record->outcome,
                'outcomeWord' => $record->outcomeWord(),
                'recordedAt' => $record->administered_at?->format('j F, H:i'),
                'recordedBy' => $record->recordedBy?->displayName(),
                'notes' => $record->notes,
            ],

            // Never the outcome already recorded: asking for the record to say
            // what it already says is not a correction.
            'outcomes' => collect(self::OUTCOMES)
                ->reject(fn ($word, $code) => $code === $record->outcome)
                ->map(fn ($word, $code) => ['code' => $code, 'word' => $word])
                ->values()->all(),

            'blockedReason' => $this->refusal($record, $serviceId),

            'authority' => [
                // Asking is not approving, and the screen says so rather than
                // letting somebody assume their request settles it.
                'mayApprove' => $this->policy->allows($user, 'correction_approval', $serviceId),
            ],

            'stage' => 'Asking for a correction. The original record is never changed.',

            'urls' => [
                'record' => route('record7.correction.store', ['administration' => $record->id]),
                'back' => $inRound && $person
                    ? route('record7.round.person', ['client' => $person->id])
                    : route('record7.today'),
                'backLabel' => $inRound && $person ? $person->displayName().'’s round' : 'Today',
                'today' => route('record7.today'),
                'round' => route('record7.round'),
                'houses' => route('record7.houses'),
                'lock' => route('record7.lock.now'),
                'signOut' => route('record7.signout'),
            ],
        ]);
    }

    /**
     * Write the request. Nothing else.
     *
     * No administration is created, none is edited, and none is deleted — the
     * append-only triggers on record7_administrations would refuse the last two
     * anyway, which is the point of them.
     */
    public function store(Request $request, int $administration)
    {
        [$user, $serviceId, $record] = $this->context($request, $administration);

        $blocked = $this->refusal($record, $serviceId);

        if ($blocked !== null) {
            return back()->with('r7.error', $blocked);
        }

        $validated = $request->validate([
            'requested_outcome' => ['required', 'string', 'in:'.implode(',', array_keys(self::OUTCOMES))],
            // A CORRECTION WITHOUT A REASON IS NOT REVIEWABLE. Somebody has to
            // decide this, and "please correct it" tells them nothing.
            'detail' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        if ($validated['requested_outcome'] === $record->outcome) {
            return back()->with(
                'r7.error',
                'That is what the record already says. Choose what it should say instead.'
            );
        }

        $item = ReviewItem::create([
            'reference' => 'R7AC-'.strtoupper(Str::random(10)),
            'organisation_id' => $record->client->organisation_id,
            'service_id' => $serviceId,
            'kind' => 'correction_request',
            'title' => 'Correction: '.($record->prescription?->medicine?->name ?? 'a medicine')
                .' for '.$record->client->displayName(),
            'detail' => trim($validated['detail']),
            'subject_type' => 'administration',
            'subject_id' => $record->id,

            // The Section 2.7 discriminator, and the outcome the approval path
            // requires. Both are the existing contract, not a new one.
            'correction_shape' => 'administration_outcome',
            'requested_outcome' => $validated['requested_outcome'],

            'raised_by_user_id' => $user->id,
            'raised_at' => now(),
            'severity' => 'high',
            'status' => 'open',
        ]);

        $this->audit->record(
            eventType: 'administration_correction_requested',
            result: AuditRecorder::SUCCESS,
            user: $user,
            serviceId: $serviceId,
            reason: 'Correction asked for on administration '.$record->reference,
            riskLevel: 'medium',
            metadata: [
                'administration_id' => $record->id,
                'review_item_id' => $item->id,
                'recorded_outcome' => $record->outcome,
                'requested_outcome' => $validated['requested_outcome'],
            ],
            request: $request
        );

        $person = $record->client;
        $inRound = $this->personView->openRoundHolding($serviceId, $person);

        /* ITS OWN FLASH, NOT THE ADMINISTRATION ONE.
           The round screen's banner is written for a dose that was just
           signed for — "Signed by X. It stays on this page below." Neither
           half of that is true here: nothing was signed and nothing was added
           to the medicines list. Borrowing it produced a success message
           about a clinical record that does not exist. */
        return redirect()
            ->route($inRound ? 'record7.round.person' : 'record7.today',
                $inRound ? ['client' => $person->id] : [])
            ->with('r7.requested', [
                'reference' => $item->reference,
                'medicine' => $record->prescription?->medicine?->name,
                'at' => now()->format('H:i'),
                'by' => $user->displayName(),
            ]);
    }
}
