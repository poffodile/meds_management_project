<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetCategory extends Model
{
    use HasFactory;
    protected $table="asset_categories";
    protected $fillable=['name','status','deleted_at'];

    public static function saveAssetCategory($data){
        return self::updateOrCreate(['id' => $data['id'] ?? null],$data);
    }
    public static function getAllAssetCategory(){
        return self::whereNull('deleted_at');
    }
}
