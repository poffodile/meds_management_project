<?php

namespace App\Models\HomeManagement;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayRateType extends Model
{
    use HasFactory;
    
     protected $fillable = [
        'home_id',
        'type_name',
        'status',
        'is_deleted'
    ];

       public static function getActiveTypes($home_id)
    {
        return self::where('home_id', $home_id)
                    ->where('status', 1)
                    ->where('is_deleted', 0)
                    ->orderBy('id', 'DESC')
                    ->get();
    }

    /**
     * Fetch all types (except deleted).
     */
    public static function getAllTypes($home_id)
    {
        return self::where('home_id', $home_id)
                    ->where('is_deleted', 0)
                    ->orderBy('id', 'DESC')
                    ->get();
    }

    /**
     * Soft delete a record.
     */
    public static function softDeleteType($id)
    {
        return self::where('id', $id)->update(['is_deleted' => 1]);
    }

}
