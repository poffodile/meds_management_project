<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditNoteEmail extends Model
{
    use HasFactory;
    protected $table="credit_note_emails";
    protected $fillable=['home_id', 'loginUserId', 'credit_id', 'to', 'cc', 'subject', 'defaultSelect', 'body', 'deleted_at'];

    public static function saveCreditNoteEmail($data){
        return self::updateOrCreate(['id'=>$data['id'] ?? null],$data);
    }
}
