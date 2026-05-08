<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientCarePlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'client_care_plans';

    protected $fillable = [
        'home_id',
        'name',
        'status'
    ];
}
