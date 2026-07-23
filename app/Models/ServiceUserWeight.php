<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * One dated weight reading for a resident (append-only series — see the
 * 2026_07_23 migration). Weight is stored ONLY as integer grams; there is no unit
 * column, because a unit choice is how a unit error happens (two residents were
 * recorded in lbs). Display in kg; capture in kg; reject lb/st at input.
 *
 * "Current weight" is DERIVED as the latest non-voided reading — never cached back
 * onto service_user.
 */
class ServiceUserWeight extends Model
{
    protected $table = 'service_user_weights';

    protected $fillable = [
        'home_id', 'service_user_id', 'weight_grams', 'measured_at', 'recorded_at',
        'recorded_by', 'method', 'is_estimated', 'notes', 'supersedes_id',
        'voided_at', 'void_reason',
    ];

    protected $casts = [
        'weight_grams' => 'integer',
        'measured_at' => 'datetime',
        'recorded_at' => 'datetime',
        'voided_at' => 'datetime',
        'is_estimated' => 'boolean',
        'service_user_id' => 'integer',
        'home_id' => 'integer',
    ];

    /**
     * Staleness threshold in days. ⚠️ PLACEHOLDER pending a qualified clinical reviewer
     * (REQ-MED-112). It should almost certainly be age-dependent — an infant outgrows a
     * weight far faster than a 16-year-old — and whether a stale weight should BLOCK or
     * only WARN a weight-based dose is a clinical decision, not an engineering one. The
     * round always shows the weight's AGE regardless; this only drives an extra flag.
     */
    public const STALE_AFTER_DAYS = 90;

    /** Non-voided readings only. */
    public function scopeLive(Builder $query): Builder
    {
        return $query->whereNull('voided_at');
    }

    /**
     * The current weight for a set of residents, as a map keyed by service_user_id.
     * Each value: ['grams', 'kg', 'measured_at', 'age_days', 'is_stale', 'is_estimated'].
     * Derived from the latest non-voided reading per resident — one query, no N+1.
     */
    public static function currentFor(array $serviceUserIds): array
    {
        if (empty($serviceUserIds)) {
            return [];
        }

        // Latest measured_at per resident, then the row at that instant.
        $rows = static::live()
            ->whereIn('service_user_id', $serviceUserIds)
            ->orderBy('service_user_id')
            ->orderByDesc('measured_at')
            ->orderByDesc('id')
            ->get();

        $out = [];
        foreach ($rows as $row) {
            if (isset($out[$row->service_user_id])) {
                continue; // already have the latest for this resident
            }
            $ageDays = $row->measured_at ? (int) $row->measured_at->startOfDay()->diffInDays(now()->startOfDay()) : null;
            $out[$row->service_user_id] = [
                'grams' => $row->weight_grams,
                'kg' => round($row->weight_grams / 1000, 1),
                'measured_at' => $row->measured_at?->toDateString(),
                'age_days' => $ageDays,
                'is_stale' => $ageDays !== null && $ageDays > self::STALE_AFTER_DAYS,
                'is_estimated' => (bool) $row->is_estimated,
            ];
        }

        return $out;
    }
}
