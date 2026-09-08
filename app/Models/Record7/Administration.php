<?php

namespace App\Models\Record7;

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
