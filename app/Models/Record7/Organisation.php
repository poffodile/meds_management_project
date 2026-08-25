<?php

namespace App\Models\Record7;

class Organisation extends Record7Model
{
    protected $table = 'record7_organisations';

    public function services()
    {
        return $this->hasMany(Service::class, 'organisation_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
