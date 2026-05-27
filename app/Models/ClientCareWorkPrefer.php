<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\User;

class ClientCareWorkPrefer extends Model
{
    use HasFactory;

    protected $fillable = [
        'home_id',
        'client_id',
        'carer_id',
        'max_per_week',
    ];

    public function carer()
    {
        return $this->belongsTo(User::class, 'carer_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}
