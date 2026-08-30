<?php

namespace App\Models\Record7;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

/**
 * One transition in a round's life.
 *
 * THIS CHAIN IS THE TRUTH. The round row keeps `closed_at`, `reopened_at` and a
 * pointer to the newest event, but those are projections for display and for
 * cheap queries. What actually happened to a round is the ordered list of these,
 * and nothing here is ever updated or removed.
 *
 * The old behaviour set `closed_at = null` on reopen, which erased the closure
 * it was undoing. A round could go close, reopen, close, reopen and end up
 * claiming almost none of it happened. That is what this replaces.
 *
 * `completed` is not an event here. Completeness is a fact about the doses,
 * true or false whenever it is asked, and it can go back to false if a dose is
 * added — so recording it as a transition would freeze a claim the records
 * could later contradict.
 */
class RoundLifecycleEvent extends Record7Model
{
    protected $table = 'record7_round_lifecycle_events';

    protected $fillable = [
        'reference',
        'organisation_id', 'service_id', 'round_id',
        'event', 'sequence_no', 'occurred_at',
        'actor_user_id', 'actor_name_at_time', 'actor_role_at_time',
        'review_item_id', 'reason',
        'planned_doses', 'accounted_doses', 'unrecorded_doses', 'unresolved_categories',
        'imported', 'import_note',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'unresolved_categories' => 'array',
        'imported' => 'boolean',
        'planned_doses' => 'integer',
        'accounted_doses' => 'integer',
        'unrecorded_doses' => 'integer',
        'sequence_no' => 'integer',
    ];

    /** What a transition is called, for somebody reading the history. */
    public const EVENT_WORDS = [
        'closed' => 'Closed',
        'reopened' => 'Reopened',
    ];

    protected static function booted(): void
    {
        static::updating(function () {
            throw new RuntimeException(
                'A round lifecycle event is permanent. Append another instead of changing it.'
            );
        });

        static::deleting(function () {
            throw new RuntimeException('A round lifecycle event cannot be deleted.');
        });
    }

    public function word(): string
    {
        return self::EVENT_WORDS[$this->event] ?? $this->event;
    }

    public function round(): BelongsTo
    {
        return $this->belongsTo(Round::class, 'round_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function reviewItem(): BelongsTo
    {
        return $this->belongsTo(ReviewItem::class, 'review_item_id');
    }
}
