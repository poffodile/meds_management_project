<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DailyLogCategory extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable=['id','home_id','category','status'];

    public function subCategorys(){
        return $this->hasMany(DailyLogSubCategory::class, 'daily_cat_id' , 'id');
    }
}
