<?php

namespace App\Services\ServiceUser;

use App\Models\User;
use App\ServiceUser;
use Illuminate\Support\Facades\Auth;

class ServiceUserServices
{    
    public function getAllserviceUser()
    {
        return ServiceUser::where('status', true)->where('is_deleted', 0)->where('home_id', Auth::user()->home_id)->select('id', 'name')->get();
    }
}
