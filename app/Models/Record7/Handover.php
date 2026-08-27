<?php

namespace App\Models\Record7;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Handover extends Record7Model
{
    protected $table = 'record7_handovers';

    protected $casts = [
        'covers_from' => 'datetime',
        'covers_to' => 'datetime',
    ];

    public function notes(): HasMany
    {
        return $this->hasMany(HandoverNote::class, 'handover_id');
    }

    public function writtenBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'written_by_user_id');
    }
}
