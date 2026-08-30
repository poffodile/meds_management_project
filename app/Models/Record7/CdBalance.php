<?php

namespace App\Models\Record7;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The current figure for one person's one preparation.
 *
 * DERIVED, NOT HISTORY. Everything here can be rebuilt from the register, and a
 * test asserts it agrees with it. This row exists for one reason: a lock needs
 * something to hold on to, and the question "is there enough" has to be settled
 * while holding one or it is worthless — two workers who both read "one tablet"
 * before either writes will both conclude there is enough.
 *
 * So this is the row that gets locked, and everything downstream of the lock
 * reads state that has already settled.
 */
class CdBalance extends Record7Model
{
    protected $table = 'record7_cd_balances';

    protected $fillable = [
        'organisation_id', 'service_id', 'client_id', 'medicine_id',
        'preparation_key', 'unit',
        'current_balance', 'last_sequence_no', 'last_register_id',
    ];

    protected $casts = [
        'current_balance' => 'decimal:3',
        'last_sequence_no' => 'integer',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }

    public function lastEntry(): BelongsTo
    {
        return $this->belongsTo(CdRegister::class, 'last_register_id');
    }
}
