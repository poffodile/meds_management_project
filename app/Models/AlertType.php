<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AlertType extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = ['home_id','title','status','deleted_at'];
}
