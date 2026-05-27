<?php

namespace App\Models\Staff;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documents extends Model
{
    use HasFactory;

    protected $table="user_documents";

    protected $fillable = [
        'user_id',
        'title',
        'file_path'
    ];
}
