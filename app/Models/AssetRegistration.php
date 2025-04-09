<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetRegistration extends Model
{
    use HasFactory;
    protected $table="asset_registrations";

    protected $fillable=['company_id','asset_name','asset_type','date','cost_bfwd','cost_disposal','cost_addition','cost_fwd','depreciation_bfwd','depreciation_type','charge','depreciation','depreciation_cfwd','nbv_cfwd','nq','nbv_bfwd','status','deleted_at'];

    public static function saveAssetRegistration($data){
        return self::updateOrCreate(['id' => $data['id'] ?? null],$data);
    }
    public static function getAllAssetRegistration(){
        return self::whereNull('deleted_at');
    }
}
