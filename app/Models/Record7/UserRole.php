<?php

namespace App\Models\Record7;

class UserRole extends Record7Model
{
    protected $table = 'record7_user_roles';

    protected $casts = ['starts_at' => 'datetime', 'ends_at' => 'datetime'];

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}
