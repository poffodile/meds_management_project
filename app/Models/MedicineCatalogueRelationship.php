<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicineCatalogueRelationship extends Model
{
    protected $table = 'medicine_catalogue_relationships';
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['source_updated_at' => 'datetime'];

    public function child()
    {
        return $this->belongsTo(MedicineCatalogue::class, 'child_medicine_id');
    }

    public function parent()
    {
        return $this->belongsTo(MedicineCatalogue::class, 'parent_medicine_id');
    }
}
