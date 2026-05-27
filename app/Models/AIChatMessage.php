<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AIChatMessage extends Model
{
    public $timestamps = false;

    protected $table = 'ai_chat_messages';

    protected $fillable = [
        'session_id',
        'home_id',
        'role',
        'content',
        'model_used',
        'tokens_input',
        'tokens_output',
    ];

    protected $casts = [
        'tokens_input' => 'integer',
        'tokens_output' => 'integer',
    ];

    public function session()
    {
        return $this->belongsTo(AIChatSession::class, 'session_id');
    }
}
