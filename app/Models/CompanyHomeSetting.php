<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyHomeSetting extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'company_home_settings';

    protected $fillable = [
        'company_id',
        'address',
        'is_home_area',
        'weekly_allowance_service_users',
        'monthly_allowance_service_users',
        'clock_in_range',
        'staff_term',
        'service_user_term',
    ];
}
