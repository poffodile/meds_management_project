<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteType extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'number_of_days' ,'status', 'home_id'];

    public static function getAllQuoteType($home_id){
        return QuoteType::where('deleted_at', null)->where('home_id', $home_id)->get();
    }

    public static function getActiveQuoteType($home_id){
        return QuoteType::where('deleted_at', null)->where('status', 1)->where('home_id', $home_id)->get();
    }
}
