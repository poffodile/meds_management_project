<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AIChatSession extends Model
{
    protected $table = 'ai_chat_sessions';

    protected $fillable = [
        'home_id',
        'user_id',
        'session_title',
        'context_type',
        'context_id',
        'message_count',
        'total_tokens',
        'is_active',
        'is_deleted',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_deleted' => 'boolean',
        'message_count' => 'integer',
        'total_tokens' => 'integer',
    ];

    public function scopeForHome($query, $homeId)
    {
        return $query->where('home_id', $homeId);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopeNotDeleted($query)
    {
        return $query->where('is_deleted', 0);
    }

    public function messages()
    {
        return $this->hasMany(AIChatMessage::class, 'session_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }
}
