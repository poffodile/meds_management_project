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

    /**
     * The latest actual clinical event attached to this planned dose.
     *
     * Corrections are not another attempt at the medicine. They describe what
     * one earlier event should have said. Excluding them here preserves the
     * simple question most callers ask — has this planned obligation received
     * any clinical answer? — without allowing a later correction to an older
     * refusal to jump ahead of a newer re-offer in the dose chain.
     */
    public function administration(): HasOne
    {
        return $this->hasOne(Administration::class, 'scheduled_dose_id')
            ->whereNull('corrects_administration_id')
            ->latestOfMany();
    }

    /** The latest actual event in the refusal/re-offer chain. */
    public function latestAdministration(): HasOne
    {
        return $this->hasOne(Administration::class, 'scheduled_dose_id')
            ->whereNull('corrects_administration_id')
            ->latestOfMany();
    }

    /**
     * What the latest clinical event says now.
     *
     * First choose the latest real event — original administration or re-offer.
     * Then, and only then, apply the one append-only correction that names that
     * event. This is deliberately different from "latest row wins": a manager
     * may correct an older refusal after a newer re-offer exists, and that older
     * correction must not become the current state of the whole scheduled dose.
     */
    public function effectiveAdministration(): ?Administration
    {
        $event = $this->relationLoaded('latestAdministration')
            ? $this->latestAdministration
            : $this->latestAdministration()->first();

        if ($event === null) {
            return null;
        }

        return Administration::where('service_id', $this->service_id)
            ->where('scheduled_dose_id', $this->id)
            ->where('corrects_administration_id', $event->id)
            ->first() ?? $event;
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
