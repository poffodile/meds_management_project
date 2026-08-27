<?php

namespace App\Models\Record7;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A person receiving support.
 *
 * CLIENT, not resident or service user: it is the owner's company wording and
 * already the legacy schema's. This product is not only for care homes.
 */
class Client extends Record7Model
{
    protected $table = 'record7_clients';

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function allergies(): HasMany
    {
        return $this->hasMany(ClientAllergy::class, 'client_id');
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class, 'client_id');
    }

    /** What staff actually call them. */
    public function displayName(): string
    {
        return $this->preferred_name ?: $this->full_name;
    }

    public function isAvailable(): bool
    {
        return $this->status === 'active';
    }

    /** "On leave", "In hospital" — why they are not here. */
    public function statusWord(): string
    {
        return [
            'active' => 'At home',
            'on_leave' => 'Away',
            'in_hospital' => 'In hospital',
            'moved_out' => 'Moved out',
        ][$this->status] ?? $this->status;
    }
}
