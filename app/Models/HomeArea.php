<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HomeArea extends Model
{
    use HasFactory;

    protected $table = 'home_areas';
    protected $guarded = [];

    public function home()
    {
        return $this->belongsTo(\App\Home::class, 'home_id');
    }
}
