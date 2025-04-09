<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepreciationType extends Model
{
    use HasFactory;
    protected $table="depreciation_types";
    protected $fillable=['name','percentage','status','deleted_at'];

    public static function saveDepreciationType($data){
        return self::updateOrCreate(['id' => $data['id'] ?? null],$data);
    }
    public static function getDepreciationType(){
        return self::whereNull('deleted_at');
    }
}
