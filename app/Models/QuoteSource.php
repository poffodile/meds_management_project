<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteSource extends Model
{
    use HasFactory;

    protected $fillable = ['title' ,'status', 'home_id'];

    public static function getAllQuoteSources(){
        return self::where('deleted_at', null)->get();
    }

    public static function getAllQuoteSourcesHome($home_id){
        return self::where('deleted_at', null)->where('status', 1)->where('deleted_at', null)->where('home_id', $home_id)->get();
    }

}
