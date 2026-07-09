<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicationDoseReviewLog extends Model
{
    protected $table = 'medication_dose_review_logs';

    protected $fillable = [
        'home_id',
        'mar_sheet_id',
        'review_date',
        'time_slot',
        'action',
        'clinical_action',
        'notes',
        'changed_by_user_id',
    ];

    protected $casts = [
        'home_id'            => 'integer',
        'mar_sheet_id'       => 'integer',
        'changed_by_user_id' => 'integer',
        'review_date'        => 'date',
    ];

    public function changedByUser()
    {
        return $this->belongsTo(\App\User::class, 'changed_by_user_id');
    }

    public function scopeForHome($query, int $homeId)
    {
        return $query->where('medication_dose_review_logs.home_id', $homeId);
    }
}
