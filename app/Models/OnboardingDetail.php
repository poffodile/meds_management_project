<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OnboardingDetail extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['home_id', 'client_id', 'name', 'type', 'vat', 'deleted_at'];

    public static function saveOnboardingDetail($data)
    {
        self::updateOrCreate(['id' => $data['id'] ?? null], $data);
        return $data;
    }
    public static function onboardingDetailList($filters)
    {
        $query = self::query();
        if (!empty($filters['home_id'])) {
            $query->where('home_id', $filters['home_id']);
        }
        if (!empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }
        $list = $query->orderBy('id', 'desc')->get();
        return $list;
    }
    public static function onboardingDetailDelete($id)
    {
        $table = self::find($id);
        $table->delete();
    }
}
