<?php

namespace App\Models\Record7;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The ask-back after an as-required medicine.
 *
 * Giving someone paracetamol for pain and never asking whether it worked is how
 * a person stays in pain all afternoon and nobody notices. It is the part of a
 * PRN that is easiest to drop and the most worth carrying across a shift.
 */
class PrnFollowUp extends Record7Model
{
    protected $table = 'record7_prn_follow_ups';

    protected $casts = [
        'due_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function administration(): BelongsTo
    {
        return $this->belongsTo(Administration::class, 'administration_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function isOutstanding(): bool
    {
        return $this->outcome === 'pending';
    }
}
