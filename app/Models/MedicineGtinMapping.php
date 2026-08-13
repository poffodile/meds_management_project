<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicineGtinMapping extends Model
{
    protected $table = 'medicine_gtin_mappings';
    protected $guarded = [];
    protected $casts = ['active' => 'boolean', 'source_updated_at' => 'datetime'];

    public function medicine()
    {
        return $this->belongsTo(MedicineCatalogue::class, 'medicine_id');
    }
}
