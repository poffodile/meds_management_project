<?php

namespace App\Models\Record7;

class Permission extends Record7Model
{
    protected $table = 'record7_permissions';

    protected $casts = ['is_sensitive' => 'boolean'];
}
