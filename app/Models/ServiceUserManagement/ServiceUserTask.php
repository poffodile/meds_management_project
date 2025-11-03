<?php

namespace App\Models\ServiceUserManagement;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceUserTask extends Model
{
    use HasFactory;
     protected $fillable = [
        'home_id',
        'user_id',
        'service_user_id',
        'task',
        'date',
        'time',
        'status',
        'comments',
    ];
}
