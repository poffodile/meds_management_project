<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormTemplate extends Model
{
    protected $table = 'form_templates';

    protected $fillable = [
        'home_id',
        'title',
        'description',
        'source_filename',
        'form_json',
        'status',
        'ai_generated',
        'created_by',
        'is_deleted',
    ];

    protected $casts = [
        'ai_generated' => 'boolean',
        'is_deleted' => 'boolean',
    ];

    public function scopeForHome($query, $homeId)
    {
        return $query->where('home_id', $homeId);
    }

    public function scopeNotDeleted($query)
    {
        return $query->where('is_deleted', 0);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function submissions()
    {
        return $this->hasMany(FormSubmission::class, 'form_template_id');
    }

    public function getFormJsonDecodedAttribute()
    {
        return json_decode($this->form_json, true);
    }
}
