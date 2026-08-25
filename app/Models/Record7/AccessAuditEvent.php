<?php

namespace App\Models\Record7;

use RuntimeException;

/**
 * One entry in Record7's access audit.
 *
 * Append-only, and enforced twice over: the database carries BEFORE UPDATE and
 * BEFORE DELETE triggers that raise, and this model refuses the operations in
 * PHP so the failure is a clear exception rather than a driver error.
 */
class AccessAuditEvent extends Record7Model
{
    protected $table = 'record7_access_audit_events';

    public $timestamps = false;

    protected $casts = [
        'event_time' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::updating(function () {
            throw new RuntimeException('Record7 access audit events are append-only.');
        });

        static::deleting(function () {
            throw new RuntimeException('Record7 access audit events are append-only.');
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}
