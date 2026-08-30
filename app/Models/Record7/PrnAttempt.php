<?php

namespace App\Models\Record7;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

/**
 * One attempt to give an as-required medicine.
 *
 * Minted when the give screen opens, spent when the administration is written.
 * It is the difference between "this is the same attempt arriving twice" and
 * "this is a second dose the prescription allows" — a distinction no timestamp
 * heuristic can make, and the reason this table exists rather than a guess
 * about how quickly two doses could legitimately follow each other.
 */
class PrnAttempt extends Record7Model
{
    protected $table = 'record7_prn_attempts';

    protected $fillable = [
        'token',
        'organisation_id',
        'service_id',
        'client_id',
        'prescription_id',
        'issued_to_user_id',
        'issued_at',
        'consumed_at',
        'administration_id',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'consumed_at' => 'datetime',
    ];

    /**
     * What an attempt is FOR cannot change after it is minted.
     *
     * Everything here is the scope the token was issued against. Allowing any
     * of it to move would let a spent or pending attempt be pointed at another
     * person, another medicine or another worker — which is the whole attack
     * this table is meant to close.
     */
    private const FROZEN = [
        'token',
        'organisation_id',
        'service_id',
        'client_id',
        'prescription_id',
        'issued_to_user_id',

        // When it was minted is part of what makes the row evidence rather
        // than an assertion, so it is fixed from issue too.
        'issued_at',
    ];

    protected static function booted(): void
    {
        static::updating(function (self $attempt) {
            foreach (self::FROZEN as $field) {
                if ($attempt->isDirty($field)) {
                    throw new RuntimeException(
                        'An as-required attempt cannot be re-pointed at something else.'
                    );
                }
            }

            // Spending it is a one-way door. A second spend is the exact
            // duplicate this guards against.
            if ($attempt->getOriginal('consumed_at') !== null) {
                throw new RuntimeException(
                    'That as-required attempt has already been recorded.'
                );
            }
        });

        static::deleting(function () {
            throw new RuntimeException('An as-required attempt cannot be deleted.');
        });
    }

    public function isSpent(): bool
    {
        return $this->consumed_at !== null;
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class, 'prescription_id');
    }

    public function administration(): BelongsTo
    {
        return $this->belongsTo(Administration::class, 'administration_id');
    }

    public function issuedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_to_user_id');
    }
}
