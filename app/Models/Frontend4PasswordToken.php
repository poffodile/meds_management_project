<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Frontend4PasswordToken extends Model
{
    protected $table = 'frontend4_password_tokens';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id', 'user_id', 'token_hash', 'expires_at', 'used_at',
        'requested_ip', 'created_at',
    ];

    protected $hidden = ['token_hash'];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'created_at' => 'datetime',
    ];
}
