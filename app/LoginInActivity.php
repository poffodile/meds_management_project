<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LoginInActivity extends Model
{
    protected $table = 'login_activities';

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
}
