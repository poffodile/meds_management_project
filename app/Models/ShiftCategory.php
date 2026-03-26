<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftCategory extends Model
{
    use HasFactory;

    protected $fillable = ['home_id', 'name', 'color', 'status', 'is_deleted'];

    public static function getAllCategories($home_id)
    {
        return self::where('is_deleted', '0')->where('home_id', $home_id)->orderBy('id', 'desc')->get();
    }
}
