<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuBehavior extends Model
{
    use HasFactory;

    
    protected $table = 'su_behavior'; // your table name

    protected $fillable = [
        'user_id',
        'service_user_id',
        'rate',
        'description',
        'is_deleted',
    ];
}
