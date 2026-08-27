<?php

namespace App\Models\Record7;

/**
 * A medicine, as a thing in its own right.
 *
 * dmd_code is nullable and unused in Section 1 — there is no catalogue
 * synchronisation yet. The column exists so that adding one later is a backfill
 * rather than a migration of live prescriptions.
 */
class Medicine extends Record7Model
{
    protected $table = 'record7_medicines';

    protected $casts = [
        'is_controlled' => 'boolean',
    ];

    /** "Levodopa 100mg tablet" — one string a person can read at a glance. */
    public function label(): string
    {
        return trim($this->name.' '.($this->strength ?? '').' '.($this->form ?? ''));
    }
}
