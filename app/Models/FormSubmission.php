<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormSubmission extends Model
{
    protected $table = 'form_submissions';

    protected $fillable = [
        'home_id',
        'form_template_id',
        'client_id',
        'form_title',
        'values_json',
        'submitted_by',
        'submitted_by_name',
        'ai_filled',
        'is_deleted',
    ];

    protected $casts = [
        'ai_filled' => 'boolean',
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

    public function template()
    {
        return $this->belongsTo(FormTemplate::class, 'form_template_id');
    }

    public function client()
    {
        return $this->belongsTo(\App\ServiceUser::class, 'client_id');
    }

    public function getValuesDecodedAttribute()
    {
        return json_decode($this->values_json, true);
    }
}
