<?php

namespace App\Models\Record7;

/**
 * A medicines round, in progress or finished.
 *
 * One per house per slot per day, enforced by a unique key: two people starting
 * the morning round within a minute of each other is an ordinary event on a
 * busy shift, and the second must join the first rather than open a rival one.
 */
class Round extends Record7Model
{
    protected $table = 'record7_rounds';

    protected $casts = [
        'round_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'closed_at' => 'datetime',
        'reopened_at' => 'datetime',
    ];

    public function participants()
    {
        return $this->hasMany(RoundParticipant::class, 'round_id');
    }

    public function openedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by_user_id');
    }

    /* ── Lifecycle ──────────────────────────────────────────────────────── */

    /**
     * Every transition this round has been through, oldest first.
     *
     * THIS IS THE SOURCE OF TRUTH. `closed_at`, `reopened_at` and
     * `last_lifecycle_event_id` are projections of it kept for display and for
     * cheap queries, and none of them decides anything.
     */
    public function lifecycleEvents()
    {
        return $this->hasMany(RoundLifecycleEvent::class, 'round_id')->orderBy('sequence_no');
    }

    public function lastLifecycleEvent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(RoundLifecycleEvent::class, 'last_lifecycle_event_id');
    }

    /**
     * Where this round has got to, read from the chain.
     *
     * Deliberately NOT from `closed_at`. That column is a projection, and a
     * projection that has drifted — or been written by hand — must not be able
     * to tell the application a round is closed when its history says otherwise.
     * The head pointer is used when it agrees with the chain and the chain is
     * consulted when it does not, so a stale pointer costs a query rather than
     * a wrong answer.
     */
    public function lifecycleState(): string
    {
        // reorder(), not orderByDesc(). The relation already sorts ascending
        // for the history list, and adding a second clause leaves
        // "ORDER BY sequence_no ASC, sequence_no DESC" — where the first wins
        // and this quietly returns the OLDEST event, so a reopened round would
        // read as closed forever.
        $latest = $this->lifecycleEvents()->reorder('sequence_no', 'desc')->first();

        return match ($latest?->event) {
            'closed' => 'closed',
            'reopened' => 'reopened',
            default => 'open',
        };
    }

    public function isClosed(): bool
    {
        return $this->lifecycleState() === 'closed';
    }

    public function isInProgress(): bool
    {
        return ! $this->isClosed();
    }

    /**
     * Status, derived every time it is asked.
     *
     * `completed_at` is NOT consulted. Nothing writes it — it is a leftover from
     * before this section and is documented as deprecated — and completeness is
     * a fact about the doses that can go from true back to false if a dose is
     * added. Asking the doses cannot disagree with the doses.
     */
    public function status(): string
    {
        if ($this->isClosed()) {
            return 'closed';
        }

        return app(\App\Services\Record7\RoundLifecycle::class)->isComplete($this)
            ? 'complete'
            : 'in_progress';
    }
}
