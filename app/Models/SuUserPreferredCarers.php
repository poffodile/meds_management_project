<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SuUserPreferredCarers extends Model
{
    use HasFactory;
    use SoftDeletes;

    public function carers_data()
    {
        return $this->belongsTo(\App\User::class, 'carer_id', 'id');
    }
}
