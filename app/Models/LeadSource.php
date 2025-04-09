<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LeadSource extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'status', 'home_id'];


    public static function getAllLeadSources(){
        return LeadSource::where('deleted_at', null)->get();
    } 
    // public static function getLeadSources($home_id){
    //     return LeadSource::where('deleted_at', null)->where('home_id', $home_id)->where('status', 1)->orderBy('created_at', 'desc')->get();
    // }
    public static function getLeadSources(){
        return LeadSource::where('deleted_at', null)->where('status', 1)->orderBy('created_at', 'desc')->get();
    }
    public static function deleteLeadSources($id){
        return LeadSource::where('id', $id)->update(['deleted_at' => Carbon::now()]);
    }
}
