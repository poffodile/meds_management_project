<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Frontend4FollowUpTask extends Model
{
    protected $table = 'frontend4_follow_up_tasks';
    protected $guarded = [];
    protected $casts = ['due_at' => 'datetime', 'escalate_at' => 'datetime', 'completed_at' => 'datetime'];
    public function item() { return $this->belongsTo(Frontend4HandoverItem::class, 'handover_item_id'); }
    public function scopeForContext($query, int $organisationId, int $serviceId) { return $query->where('organisation_id', $organisationId)->where('service_id', $serviceId); }
}
