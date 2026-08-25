<?php

namespace App\Models\Record7;

class Role extends Record7Model
{
    protected $table = 'record7_roles';

    protected $casts = ['privilege_level' => 'integer'];

    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class, 'record7_role_permissions', 'role_id', 'permission_id'
        );
    }
}
