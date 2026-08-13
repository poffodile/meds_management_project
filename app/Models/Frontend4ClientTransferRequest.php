<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Frontend4ClientTransferRequest extends Model
{
    protected $table = 'frontend4_client_transfer_requests';
    protected $guarded = [];

    protected $casts = [
        'requested_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];
}
