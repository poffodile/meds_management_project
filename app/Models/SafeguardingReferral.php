<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SafeguardingReferral extends Model
{
    protected $table = 'safeguarding_referrals';

    protected $fillable = [
        'client_id',
        'reference_number',
        'reported_by',
        'date_of_concern',
        'location_of_incident',
        'details_of_concern',
        'immediate_action_taken',
        'safeguarding_type',
        'risk_level',
        'status',
        'ongoing_risk',
        'alleged_perpetrator',
        'witnesses',
        'capacity_to_make_decisions',
        'client_wishes',
        'police_notified',
        'police_reference',
        'police_notification_date',
        'local_authority_notified',
        'local_authority_reference',
        'local_authority_notification_date',
        'cqc_notified',
        'cqc_notification_date',
        'family_notified',
        'family_notification_details',
        'advocate_involved',
        'advocate_details',
        'strategy_meeting',
        'safeguarding_plan',
        'outcome',
        'outcome_details',
        'lessons_learned',
        'closed_date',
    ];

    protected $casts = [
        'safeguarding_type' => 'array',
        'alleged_perpetrator' => 'array',
        'witnesses' => 'array',
        'strategy_meeting' => 'array',
        'safeguarding_plan' => 'array',
        'ongoing_risk' => 'boolean',
        'capacity_to_make_decisions' => 'boolean',
        'police_notified' => 'boolean',
        'local_authority_notified' => 'boolean',
        'cqc_notified' => 'boolean',
        'family_notified' => 'boolean',
        'advocate_involved' => 'boolean',
        'is_deleted' => 'boolean',
        'date_of_concern' => 'datetime',
        'police_notification_date' => 'datetime',
        'local_authority_notification_date' => 'datetime',
        'cqc_notification_date' => 'datetime',
        'closed_date' => 'datetime',
    ];

    public function scopeForHome($query, $homeId)
    {
        return $query->where('home_id', $homeId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_deleted', 0);
    }

    public function reportedByUser()
    {
        return $this->belongsTo(\App\User::class, 'reported_by');
    }

    public function createdByUser()
    {
        return $this->belongsTo(\App\User::class, 'created_by');
    }

    public static function generateReferenceNumber($homeId)
    {
        $year = date('Y');
        $month = date('m');
        $prefix = "SAFE-{$year}-{$month}-";

        $lastRef = static::where('reference_number', 'like', $prefix . '%')
            ->where('home_id', $homeId)
            ->orderBy('id', 'desc')
            ->value('reference_number');

        if ($lastRef) {
            $lastSeq = (int) substr($lastRef, strlen($prefix));
            $nextSeq = $lastSeq + 1;
        } else {
            $nextSeq = 1;
        }

        return $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
    }
}
