<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChildSection extends Model
{
    use HasFactory;
    protected $fillable = ['section','home_id','status'];
}
