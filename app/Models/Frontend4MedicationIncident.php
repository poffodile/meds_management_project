<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Frontend4MedicationIncident extends Model
{
    protected $table = 'frontend4_medication_incidents';
    protected $guarded = [];
    protected $casts = ['reported_at' => 'datetime', 'closed_at' => 'datetime'];
    public function scopeForContext($query, int $organisationId, int $serviceId) { return $query->where('organisation_id', $organisationId)->where('service_id', $serviceId); }
}
