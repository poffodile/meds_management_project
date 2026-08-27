<?php

namespace App\Models\Record7;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prescription extends Record7Model
{
    protected $table = 'record7_prescriptions';

    protected $casts = [
        'is_time_critical' => 'boolean',
        'starts_on' => 'date',
        'ends_on' => 'date',
        'changed_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }

    public function doses(): HasMany
    {
        return $this->hasMany(ScheduledDose::class, 'prescription_id');
    }

    public function administrations(): HasMany
    {
        return $this->hasMany(Administration::class, 'prescription_id');
    }

    public function isPrn(): bool
    {
        return $this->kind === 'prn';
    }

    /**
     * Changed recently enough that whoever is starting a shift may not know.
     *
     * Seven days is a shift pattern, not a clinical constant: somebody working
     * two on and four off can miss a change made the day after they left.
     */
    public function changedRecently(): bool
    {
        return $this->changed_at !== null
            && $this->changed_at->greaterThan(now()->subDays(7));
    }
}
