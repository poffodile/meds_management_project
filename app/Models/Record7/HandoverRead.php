<?php

namespace App\Models\Record7;

/**
 * One person's confirmation that they have read one handover.
 *
 * Personal rather than house-level: two people starting the same shift each
 * confirm for themselves, because responsibility does not transfer in bulk.
 */
class HandoverRead extends Record7Model
{
    protected $table = 'record7_handover_reads';

    protected $casts = [
        'read_at' => 'datetime',
    ];
}
