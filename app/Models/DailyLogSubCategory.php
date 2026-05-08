<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DailyLogSubCategory extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable=['id','home_id','daily_cat_id','sub_cat','icon','color','background_color','status'];

    public function dailyLogCategory(){
        return $this->belongsTo(DailyLogCategory::class, 'daily_cat_id','id');
    }
}
