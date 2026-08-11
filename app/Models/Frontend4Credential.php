<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Frontend4Credential extends Model
{
    protected $table = 'frontend4_credentials';

    protected $fillable = [
        'user_id', 'password_hash', 'failed_login_attempts', 'locked_until',
        'last_login_at', 'last_login_ip', 'password_changed_at',
        'force_password_reset',
    ];

    protected $hidden = ['password_hash'];

    protected $casts = [
        'locked_until' => 'datetime',
        'last_login_at' => 'datetime',
        'password_changed_at' => 'datetime',
        'force_password_reset' => 'boolean',
    ];
}
