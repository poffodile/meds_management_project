<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Frontend4Handover extends Model
{
    protected $table = 'frontend4_handovers';
    protected $guarded = [];
    protected $casts = ['shift_start' => 'datetime', 'shift_end' => 'datetime', 'submitted_at' => 'datetime'];

    public function items() { return $this->hasMany(Frontend4HandoverItem::class, 'handover_id'); }
    public function acknowledgements() { return $this->hasMany(Frontend4HandoverAcknowledgement::class, 'handover_id'); }
    public function scopeForContext($query, int $organisationId, int $serviceId) { return $query->where('organisation_id', $organisationId)->where('service_id', $serviceId); }
}
