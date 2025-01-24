<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LeadStatus extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'status','home_id'];

    public static function getAllLeadStatus(){
        return LeadStatus::where('deleted_at', null)->get();
    }

    public static function getLeadStatus(){
        return LeadStatus::where('deleted_at', null)->where('status', 1)->orderBy('created_at', 'desc')->get();
    }

    public static function deleteLeadStatus($id){
        return LeadStatus::where('id', $id)->update(['deleted_at' => Carbon::now()]);;
    }
}
