<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OnboardingConfigStage extends Model
{
    use HasFactory, SoftDeletes;

    public function entitydata()
    {
        return $this->belongsTo(EntityType::class, 'entity_type_id');
    }
    public function workflow()
    {
        return $this->belongsTo(OnboardingConfig::class, 'onboarding_config_id');
    }
}
