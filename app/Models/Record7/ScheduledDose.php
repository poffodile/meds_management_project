<?php

namespace App\Models\Record7;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * One dose, planned for one moment.
 *
 * The plan and what happened are separate records on purpose. A dose that was
 * never given still has to exist, or nobody can tell the difference between a
 * missed dose and a dose that was never due.
 */
class ScheduledDose extends Record7Model
{
    protected $table = 'record7_scheduled_doses';

    protected $casts = [
        'due_at' => 'datetime',
    ];

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class, 'prescription_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function administration(): HasOne
    {
        return $this->hasOne(Administration::class, 'scheduled_dose_id');
    }

    public function isRecorded(): bool
    {
        return $this->administration !== null;
    }

    /** The moment it stops being on time. */
    public function lateFrom(): Carbon
    {
        return $this->due_at->copy()->addMinutes($this->grace_minutes);
    }

    public function isLate(?Carbon $now = null): bool
    {
        return ! $this->isRecorded() && ($now ?? now())->greaterThan($this->lateFrom());
    }

    public function minutesLate(?Carbon $now = null): int
    {
        return (int) max(0, $this->lateFrom()->diffInMinutes($now ?? now(), false));
    }
}
