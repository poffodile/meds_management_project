<?php

namespace App\Models\HomeManagement;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\AccessLevel;

class PayRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'home_id',
        'access_level_id',
        'pay_rate',
        'rate_type_id',
        'status',
        'is_deleted'
    ];

    /**
     * Get all payrates with access level details
     */
    public static function getAllPayRates($home_id)
    {
        return self::select(
            'pay_rates.id',
            'pay_rates.home_id',
            'pay_rates.status',
            'pay_rates.access_level_id',
            'pay_rates.rate_type_id',
            'pay_rates.pay_rate',
            'pay_rates.created_at',
            'pay_rates.updated_at',
            'access_level.name as access_level_name',
            'pay_rate_types.type_name as rate_type_name'
        )
        ->join('access_level', 'pay_rates.access_level_id', '=', 'access_level.id')
        ->join('pay_rate_types', 'pay_rates.rate_type_id', '=', 'pay_rate_types.id')
        ->where('pay_rates.home_id', $home_id)
        ->get();
    }

    /**
     * Define relationship with AccessLevel
     */
    public function accessLevel()
    {
        return $this->belongsTo(AccessLevel::class);
    }
}
