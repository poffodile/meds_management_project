<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\RotaShift;

class RotaAssignEmployee extends Model
{
    //
    protected $table = 'rota_assign_employees'; 

    public function shift()
    {
        return $this->belongsTo(RotaShift::class, 'shift_id', 'id');
    }
}
