<?php

namespace App\Models\Record7;

class UserServiceAccess extends Record7Model
{
    protected $table = 'record7_user_service_access';

    protected $casts = ['starts_at' => 'datetime', 'ends_at' => 'datetime'];

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Usable right now?
     *
     * Status and date window together. A grant that has not started, or has
     * ended, is not access however active the row claims to be.
     */
    public function isUsable(): bool
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

    /** Access types that never permit a write to a clinical record. */
    public function isReadOnly(): bool
    {
        return $this->access_type === 'read_only';
    }
}
