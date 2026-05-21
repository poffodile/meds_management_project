<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\ServiceUser;

class ServiceUserEmergencyContact extends Model
{
    protected $table = 'service_user_emergency_contacts';

    protected $fillable = [
        'service_user_id',
        'name',
        'phone_no',
        'relationship'
    ];

    /**
     * Get the service user that owns the emergency contact.
     */
    public function serviceUser()
    {
        return $this->belongsTo(ServiceUser::class, 'service_user_id');
    }
}
