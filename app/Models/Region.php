<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $fillable=[
        'home_id',
        'title',
        'status'
    ];

    public static function getRegions($home_id){
        return Region::where('home_id', $home_id)->where('status', 1)->whereNull('deleted_at')->orderBy('created_at', 'desc')->get();
    }

    public static function getAllRegion($home_id){
        return self::whereNull('deleted_at')->where('home_id',$home_id)->get();
    }
}
