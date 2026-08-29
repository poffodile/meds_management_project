<?php

namespace App\Models\Record7;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

/**
 * Somebody went and looked, and this is what they found.
 *
 * The only thing that answers "we could not find them". Not an acknowledgement,
 * not a note, not somebody closing the alert — a person recording that they
 * located the person, or established where they are.
 *
 * Permanent, like every other clinical record in Record7. The database refuses
 * to rewrite or delete it, and so does this model.
 */
class WelfareCheck extends Record7Model
{
    protected $table = 'record7_welfare_checks';

    protected $casts = [
        'occurred_at' => 'datetime',
    ];

    /** What was actually established, in the words staff read. */
    public const RESOLUTION_WORDS = [
        'located_and_well' => 'Found them, and they are well',
        'located_needs_follow_up' => 'Found them, but something needs following up',
        'whereabouts_confirmed_elsewhere' => 'Confirmed where they are, elsewhere',
    ];

    private const FROZEN = [
        'administration_id', 'client_id', 'service_id', 'organisation_id',
        'resolution_type', 'recorded_by_user_id', 'occurred_at',
    ];

    protected static function booted(): void
    {
        static::updating(function (self $check) {
            foreach (self::FROZEN as $field) {
                if ($check->isDirty($field)) {
                    throw new RuntimeException(
                        'A Record7 welfare check is a permanent record and cannot be changed.'
                    );
                }
            }
        });

        static::deleting(function () {
            throw new RuntimeException('A Record7 welfare check cannot be deleted.');
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }

    public function administration(): BelongsTo
    {
        return $this->belongsTo(Administration::class, 'administration_id');
    }

    public function resolutionWord(): string
    {
        return self::RESOLUTION_WORDS[$this->resolution_type] ?? $this->resolution_type;
    }
}
