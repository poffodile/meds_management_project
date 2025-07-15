<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyDepartment extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public static function getActiveCompanyDepartment(){
        return CompanyDepartment::where('status', true)->whereNull('deleted_at')->get();
    }
}
