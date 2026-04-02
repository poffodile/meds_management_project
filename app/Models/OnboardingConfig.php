<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OnboardingConfig extends Model
{
    use HasFactory, SoftDeletes;

    public function getstages()
    {
        return $this->hasMany(OnboardingConfigStage::class);
    }
    public function departments()
    {
        return $this->belongsTo(CompanyDepartment::class, 'care_type', 'id');
    }
}
