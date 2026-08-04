<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MARAdministration extends Model
{
    protected $table = 'mar_administrations';

    protected $fillable = [
        'mar_sheet_id',
        'date',
        'time_slot',
        'administered_at',
        'given',
        'is_late',            // recorded as GIVEN LATE via missed-doses resolve (B4)
        'dose_given',
        'quantity_given',
        'administered_by',
        'witnessed_by',
        'code',
        'reason',
        'notes',
        'is_current',
        'supersedes_id',
        'superseded_at',
        'amendment_reason',
    ];

    /**
     * Every query sees only the CURRENT version of a record by default (append-only —
     * see the 2026_07_23 migration). Superseded prior versions are still in the table
     * for audit but are hidden unless explicitly asked for with
     * withoutGlobalScope('current') or ->withHistory().
     *
     * A global scope is used deliberately rather than adding ->where('is_current',1)
     * to each reader: this codebase has been bitten repeatedly by a reader that was
     * missed. Scoping at the model means no Eloquent reader can forget. (Raw
     * DB::table('mar_administrations') queries are NOT covered — those are patched
     * individually; there are two, both aggregate counts.)
     */
    protected static function booted(): void
    {
        static::addGlobalScope('current', function (Builder $query) {
            $query->where($query->getModel()->getTable().'.is_current', 1);
        });
    }

    /** Include superseded history in a query. */
    public function scopeWithHistory(Builder $query): Builder
    {
        return $query->withoutGlobalScope('current');
    }

    /** The record this one replaced (if it is an amendment). */
    public function supersedes()
    {
        return $this->belongsTo(self::class, 'supersedes_id')->withoutGlobalScope('current');
    }

    protected $casts = [
        'date' => 'date:Y-m-d',
        // When the dose was actually given. Distinct from `time_slot` (the SCHEDULED
        // slot) and from `updated_at` (the last edit) — conflating them is what broke
        // the PRN interval check.
        'administered_at' => 'datetime',
        'quantity_given' => 'float',
        'given' => 'boolean',
        'is_current' => 'boolean',
        'superseded_at' => 'datetime',
        'mar_sheet_id' => 'integer',
        'home_id' => 'integer',
        'administered_by' => 'integer',
    ];

    public function scopeForHome($query, $homeId)
    {
        return $query->where('home_id', $homeId);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('date', $date);
    }

    public function marSheet()
    {
        return $this->belongsTo(MARSheet::class, 'mar_sheet_id');
    }

    public function administeredByUser()
    {
        return $this->belongsTo(User::class, 'administered_by');
    }
}
