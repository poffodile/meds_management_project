<?php

namespace App\Models\Record7;

/**
 * Something this person must not be given.
 *
 * Severity is a word, never a colour alone. The row has to survive being read
 * in greyscale, by someone colour-blind, or aloud down a phone.
 */
class ClientAllergy extends Record7Model
{
    protected $table = 'record7_client_allergies';

    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    /** The ones that stop a round rather than inform it. */
    public function isCritical(): bool
    {
        return in_array($this->severity, ['severe', 'life_threatening'], true);
    }

    public function severityWord(): string
    {
        return $this->severity === 'life_threatening'
            ? 'Life threatening'
            : ucfirst($this->severity);
    }
}
