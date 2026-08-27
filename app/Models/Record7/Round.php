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

    public function isInProgress(): bool
    {
        return $this->completed_at === null && $this->closed_at === null;
    }

    public function isClosed(): bool
    {
        return $this->closed_at !== null;
    }

    /**
     * Status derived from the timestamps, never stored.
     *
     * A stored status is a second copy of something the timestamps already say,
     * and the two drift apart the first time anything is written outside the one
     * method that maintains both.
     */
    public function status(): string
    {
        return match (true) {
            $this->closed_at !== null => 'closed',
            $this->completed_at !== null => 'completed',
            default => 'in_progress',
        };
    }
}
