<?php

namespace App\Models\Record7;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One person's participation in one round.
 *
 * JOINING IS NOT BECOMING THE OPENER. The round keeps started_by_user_id for
 * whoever opened it, permanently; everybody else who works on it gets a row
 * here. Two people on a busy morning is ordinary, and the second must be
 * visible as themselves rather than disappearing into the first person's name.
 *
 * role_at_join and access_type_at_join are snapshots on purpose. They record
 * what was true when this person started, so that a competency lapsing later
 * cannot retrospectively make it look as though they were never entitled.
 */
class RoundParticipant extends Record7Model
{
    protected $table = 'record7_round_participants';

    protected $casts = [
        'opened_it' => 'boolean',
        'joined_at' => 'datetime',
        'last_acted_at' => 'datetime',
    ];

    public function round(): BelongsTo
    {
        return $this->belongsTo(Round::class, 'round_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
