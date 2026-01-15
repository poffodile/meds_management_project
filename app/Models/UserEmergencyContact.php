<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\User;

class UserEmergencyContact extends Model
{
    use HasFactory;

    use SoftDeletes;
    protected $table = 'user_emergency_contacts';

    protected $fillable = [
        'user_id', 'name', 'relationship', 'phone_no', 'email'
    ];

    protected $dates = ['deleted_at']; // optional, SoftDeletes handles this

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
}
