<?php

namespace App\Models\Record7;

class UserPermission extends Record7Model
{
    protected $table = 'record7_user_permissions';

    protected $casts = ['starts_at' => 'datetime', 'ends_at' => 'datetime'];

    public function permission()
    {
        return $this->belongsTo(Permission::class, 'permission_id');
    }

    public function isInForce(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $now = now();

        if ($this->starts_at && $now->lessThan($this->starts_at)) {
            return false;
        }

        return ! ($this->ends_at && $now->greaterThan($this->ends_at));
    }
}
