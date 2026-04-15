<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyHomeArea extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'company_home_areas';

    protected $fillable = [
        'company_id',
        'area_name',
    ];
}
