<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A witness co-signature confirmation for a controlled-drug register movement
 * (issue #14, owner decision A2). See the migration for the full rationale.
 *
 * Lifecycle: PENDING -> CONFIRMED (the witness confirmed on their own account)
 *                     -> MANAGER_OVERRIDDEN (a manager confirmed on the witness's behalf).
 *
 * This record is deliberately NOT on the append-only controlled_drug_register row: the
 * register never changes, but a confirmation does (that is its whole job).
 */
class CdWitnessConfirmation extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_OVERRIDDEN = 'manager_overridden';

    protected $table = 'cd_witness_confirmations';

    protected $fillable = [
        'home_id',
        'register_id',
        'recorded_by_user_id',
        'witness_user_id',
        'witness_name',
        'status',
        'confirmed_by_user_id',
        'confirmed_at',
        'override_reason',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
    ];

    /**
     * Open a PENDING confirmation for a controlled-drug register movement (issue #14 / A2).
     * No-op (returns null) when the movement genuinely has no witness — e.g. supported
     * living, recorded as "Not witnessed" — because there is nothing to co-sign.
     *
     * @param  ControlledDrugRegister $entry          the register movement just written
     * @param  int                    $recordedBy      who recorded/administered it (Adam)
     * @param  int|null               $witnessUserId   the witness's staff account (Eve), if known
     * @param  string|null            $witnessName     the witness name as entered
     */
    public static function open(ControlledDrugRegister $entry, int $recordedBy, ?int $witnessUserId, ?string $witnessName): ?self
    {
        $name = trim((string) $witnessName);
        if ($name === '' || strcasecmp($name, 'Not witnessed') === 0) {
            return null;
        }

        return static::create([
            'home_id' => $entry->home_id,
            'register_id' => $entry->id,
            'recorded_by_user_id' => $recordedBy,
            'witness_user_id' => $witnessUserId ?: null,
            'witness_name' => $name,
            'status' => self::STATUS_PENDING,
        ]);
    }

    /* ---- scopes ---- */

    public function scopeForHome($query, int $homeId)
    {
        return $query->where('home_id', $homeId);
    }

    /** Confirmations still awaiting a specific witness's sign-off. */
    public function scopePendingForUser($query, int $userId)
    {
        return $query->where('witness_user_id', $userId)->where('status', self::STATUS_PENDING);
    }

    /* ---- state ---- */

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /** Human-readable status for display. */
    public function statusLabel(): string
    {
        return [
            self::STATUS_PENDING => 'Awaiting witness confirmation',
            self::STATUS_CONFIRMED => 'Witness confirmed',
            self::STATUS_OVERRIDDEN => 'Manager-overridden',
        ][$this->status] ?? $this->status;
    }

    /* ---- relationships ---- */

    public function registerEntry()
    {
        return $this->belongsTo(ControlledDrugRegister::class, 'register_id');
    }

    public function witness()
    {
        return $this->belongsTo(\App\User::class, 'witness_user_id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(\App\User::class, 'recorded_by_user_id');
    }

    public function confirmedBy()
    {
        return $this->belongsTo(\App\User::class, 'confirmed_by_user_id');
    }
}
