<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicationDoseReview extends Model
{
    protected $table = 'medication_dose_reviews';

    protected $fillable = [
        'home_id',
        'mar_sheet_id',
        'client_id',
        'client_name',
        'medication_name',
        'review_date',
        'time_slot',
        'dose_kind',
        'code',
        'clinical_action',
        'notes',
        'status',
        'reviewed_by_user_id',
    ];

    protected $casts = [
        'home_id'             => 'integer',
        'mar_sheet_id'        => 'integer',
        'client_id'           => 'integer',
        'reviewed_by_user_id' => 'integer',
        'review_date'         => 'date',
    ];

    public function reviewedByUser()
    {
        return $this->belongsTo(\App\User::class, 'reviewed_by_user_id');
    }

    public function scopeForHome($query, int $homeId)
    {
        return $query->where('medication_dose_reviews.home_id', $homeId);
    }
}
