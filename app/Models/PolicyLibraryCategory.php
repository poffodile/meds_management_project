<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PolicyLibraryCategory extends Model
{
    use HasFactory;
    protected $fillable = ['id', 'home_id', 'type', 'status'];
    public function policy_library()
    {
        return $this->hasMany(PolicyLibrary::class, 'category_id', 'id');
    }
}
