<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Frontend4HandoverItem extends Model
{
    protected $table = 'frontend4_handover_items';
    protected $guarded = [];
    protected $casts = ['occurred_at' => 'datetime', 'requires_action' => 'boolean'];
    public function handover() { return $this->belongsTo(Frontend4Handover::class, 'handover_id'); }
    public function tasks() { return $this->hasMany(Frontend4FollowUpTask::class, 'handover_item_id'); }
}
