<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class MedicationRoundClosure extends Model
{
    protected $table = 'medication_round_closures';

    protected $fillable = [
        'home_id',
        'date',
        'round',
        'closed_by',
    ];

    protected $casts = [
        'date' => 'date',
        'home_id' => 'integer',
        'closed_by' => 'integer',
    ];

    public function closedByUser()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }
}
