<?php

namespace App\Models\Record7;

class Service extends Record7Model
{
    protected $table = 'record7_services';

    public function organisation()
    {
        return $this->belongsTo(Organisation::class, 'organisation_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
