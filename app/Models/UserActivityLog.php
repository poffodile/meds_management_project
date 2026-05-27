<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserActivityLog extends Model
{
    use HasFactory;
    protected $table="user_activity_logs";
    protected $fillable=['user_id','login_time','logout_time','ip_address','description','user_agent','user_type'];
}
