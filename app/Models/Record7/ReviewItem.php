<?php

namespace App\Models\Record7;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

/**
 * Something waiting for a manager to decide.
 *
 * The status legitimately changes — that is what a queue is. What was asked,
 * who asked and when do not: rewriting those would let somebody quietly change
 * what a manager actually approved after the fact. The database refuses it and
 * so does this model.
 */
class ReviewItem extends Record7Model
{
    protected $table = 'record7_review_items';

    protected $casts = [
        'raised_at' => 'datetime',
        'decided_at' => 'datetime',
    ];

    private const FROZEN = ['kind', 'service_id', 'raised_by_user_id', 'raised_at', 'subject_type', 'subject_id'];

    protected static function booted(): void
    {
        static::updating(function (self $item) {
            foreach (self::FROZEN as $field) {
                if ($item->isDirty($field)) {
                    throw new RuntimeException(
                        'What a Record7 review item asks, and who asked, cannot be changed.'
                    );
                }
            }
        });
    }

    public function raisedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'raised_by_user_id');
    }

    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by_user_id');
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /** The words a manager uses, not the stored values. */
    public function kindWord(): string
    {
        return [
            'correction_request' => 'Correction request',
            'incident' => 'Incident',
            'round_reopen_request' => 'Request to reopen a round',
            'handover_escalation' => 'Handover escalation',
        ][$this->kind] ?? $this->kind;
    }
}
