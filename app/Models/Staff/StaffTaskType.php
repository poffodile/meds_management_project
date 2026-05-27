<?php

namespace App\Models\Staff;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaffTaskType extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['id', 'home_id', 'type', 'status'];
}
