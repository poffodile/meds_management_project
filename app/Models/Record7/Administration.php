<?php

namespace App\Models\Record7;

use App\Exceptions\Record7RoundClosed;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

/**
 * What actually happened to a dose. Permanent.
 *
 * The database refuses to rewrite the clinical facts on these rows, or to
 * delete them at all. These guards make the same refusal in the application, so
 * the mistake is caught where it is made rather than surfacing as a driver
 * exception three layers away.
 */
class Administration extends Record7Model
{
    protected $table = 'record7_administrations';

    protected $casts = [
        'administered_at' => 'datetime',
    ];

    /** Outcomes that mean the person did not get their medicine. */
    public const NOT_TAKEN = ['refused', 'withheld', 'not_available', 'missed', 'person_unavailable'];

    /** Facts that a correction replaces rather than edits. */
    private const FROZEN = [
        'outcome',
        'client_id',
        'prescription_id',
        'recorded_by_user_id',
        'administered_at',
        'corrects_administration_id',
        'reoffer_of_administration_id',

        // Section 2.5. These were protected by neither this list nor the
        // trigger, which nothing exploited only because nothing ever wrote a
        // witness. Section 2.5 writes one, so they are closed first.
        'witnessed_by_user_id',
        'dose_amount',
        'dose_unit',
        'controlled_drug_no_quantity_removed',
        'cd_register_id',

        // Section 2.7. What a dose did to the cupboard is as permanent as the
        // outcome it belongs to. Both are frozen in the database trigger too,
        // and both belong here so the model refuses before MySQL has to.
        'stock_movement_id',
        'stock_no_quantity_removed',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $administration) {
            self::anchorScheduledControlledDrug($administration);
            self::assertScheduledRoundWritable($administration);

            if ($administration->corrects_administration_id === null) {
                return;
            }

            $original = self::with('prescription.medicine')
                ->find($administration->corrects_administration_id);

            // PRN corrections need more than a replacement outcome. A PRN dose
            // also carries an actual amount, spends interval/count/amount
            // allowance, may move stock, and may create an effectiveness
            // follow-up. The generic correction workflow does not yet capture
            // those consequences, so it must not append a clinically partial
            // PRN correction while pretending the record is complete.
            if ($original?->prescription?->kind === 'prn') {
                throw new RuntimeException(
                    'As-required medicine corrections need the PRN-specific correction pathway '
                    .'so dose amount, limits, stock and follow-up stay together.'
                );
            }

            // Controlled-drug corrections have an independent append-only
            // register and balance. The ordinary correction workflow only knows
            // the ordinary stock ledger, so allowing it to change the clinical
            // outcome would let the MAR and controlled-drug register disagree.
            if ($original?->prescription?->medicine?->is_controlled) {
                throw new RuntimeException(
                    'Controlled-drug corrections need the controlled-drug correction pathway '
                    .'so the clinical record and register stay together.'
                );
            }
        });

        static::updating(function (self $administration) {
            foreach (self::FROZEN as $field) {
                if ($administration->isDirty($field)) {
                    throw new RuntimeException(
                        'A Record7 administration is a permanent record. Record a '
                        .'correction that refers to it instead of changing it.'
                    );
                }
            }
        });

        static::deleting(function () {
            throw new RuntimeException('A Record7 administration cannot be deleted.');
        });
    }

    /**
     * Keep a scheduled controlled-drug administration attached to its planned
     * obligation even though the controlled-drug workspace is person-scoped.
     *
     * Section 2.5 deliberately has to work outside a round for PRN medicines,
     * so its route does not carry a round id. That became unsafe for a scheduled
     * controlled medicine: the clinical record could be written with no
     * scheduled_dose_id, leaving the real round dose looking unanswered.
     *
     * For a scheduled controlled prescription we therefore resolve the one
     * matching dose from the person's CURRENT open round. Zero matches means
     * there is no scheduled round context to write against; more than one means
     * Record7 cannot know which obligation is being answered. Both fail closed
     * rather than guessing. PRN controlled medicines remain unplanned and are
     * untouched by this rule.
     */
    private static function anchorScheduledControlledDrug(self $administration): void
    {
        if ($administration->scheduled_dose_id !== null
            || $administration->corrects_administration_id !== null
            || $administration->cd_register_id === null
            || $administration->prescription_id === null) {
            return;
        }

        $prescription = Prescription::with('medicine')->find($administration->prescription_id);

        if ($prescription?->kind !== 'scheduled' || ! $prescription->medicine?->is_controlled) {
            return;
        }

        $client = Client::where('service_id', $administration->service_id)
            ->find($administration->client_id);

        if ($client === null) {
            throw new RuntimeException('That controlled-drug record does not belong to this house.');
        }

        $round = app(\App\Services\Record7\RoundPersonView::class)
            ->openRoundHolding((int) $administration->service_id, $client);

        if ($round === null) {
            throw new RuntimeException(
                'This is a scheduled controlled medicine. Open or reopen its medication round before recording it.'
            );
        }

        $matches = ScheduledDose::where('service_id', $round->service_id)
            ->where('client_id', $client->id)
            ->where('prescription_id', $prescription->id)
            ->whereDate('due_at', $round->round_date->toDateString())
            ->where('slot', $round->slot)
            ->get();

        if ($matches->count() !== 1) {
            throw new RuntimeException(
                'Record7 cannot identify one scheduled dose for this controlled medicine in the open round. '
                .'Do not guess which dose this administration belongs to.'
            );
        }

        $administration->scheduled_dose_id = $matches->first()->id;
    }

    /**
     * A scheduled clinical act and a round closure must settle in one order.
     *
     * The ordinary and controlled-drug recorders both create their clinical row
     * inside a transaction. Locking the matching round here makes that row share
     * the same serialization point as RoundLifecycle::close(): if the medicine
     * gets the lock first, the manager waits and their close snapshot includes
     * it; if the manager closes first, this sees the closed lifecycle and the
     * surrounding medicine/stock transaction rolls back.
     *
     * Corrections are deliberately different. They are retrospective append-only
     * evidence about a record that already existed when the manager signed. A
     * correction may therefore be appended after closure without reopening the
     * round or altering the immutable close-time snapshot.
     */
    private static function assertScheduledRoundWritable(self $administration): void
    {
        if ($administration->scheduled_dose_id === null
            || $administration->corrects_administration_id !== null) {
            return;
        }

        $dose = ScheduledDose::find($administration->scheduled_dose_id);

        if ($dose === null) {
            return;
        }

        $round = Round::where('service_id', $dose->service_id)
            ->whereDate('round_date', $dose->due_at->toDateString())
            ->where('slot', $dose->slot)
            ->lockForUpdate()
            ->first();

        if ($round?->isClosed()) {
            throw new Record7RoundClosed(
                'That round has been closed by a manager. Reopen it before recording another scheduled outcome.'
            );
        }
    }

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class, 'prescription_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }

    public function wasTaken(): bool
    {
        return in_array($this->outcome, ['given', 'self_administered'], true);
    }

    /** The words staff use, not the values stored. */
    public function outcomeWord(): string
    {
        return [
            'given' => 'Given',
            'self_administered' => 'Self-administered',
            'refused' => 'Refused',
            'withheld' => 'Withheld',
            'not_available' => 'Not available',
            'missed' => 'Missed',
            'person_unavailable' => 'Person unavailable',
        ][$this->outcome] ?? $this->outcome;
    }
}
