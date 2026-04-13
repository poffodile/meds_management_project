<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClientAlert extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'client_alerts';

    protected $guarded = [];

    /**
     * Get the client that owns the alert.
     */
    public function client()
    {
        return $this->belongsTo(\App\ServiceUser::class, 'client_id');
    }

    /**
     * Get the staff member who created the alert.
     */
    public function staff()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }

    /**
     * Get the type of the alert.
     */
    public function alert_types()
    {
        return $this->belongsTo(AlertType::class, 'alert_type_id');
    }
}
