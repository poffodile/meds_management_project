<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordActionToken extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'authenticatable_type',
        'authenticatable_id',
        'token_hash',
        'purpose',
        'expires_at',
        'used_at',
        'requested_ip',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];
}
