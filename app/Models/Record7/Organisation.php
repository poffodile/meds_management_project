<?php

namespace App\Models\Record7;

class Organisation extends Record7Model
{
    protected $table = 'record7_organisations';

    protected $casts = [
        'device_trust_enabled' => 'boolean',
        'trusted_device_days' => 'integer',
    ];

    public function services()
    {
        return $this->hasMany(Service::class, 'organisation_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
