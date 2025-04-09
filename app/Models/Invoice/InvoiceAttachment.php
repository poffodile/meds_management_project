<?php

namespace App\Models\Invoice;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AttachmentType;

class InvoiceAttachment extends Model
{
    use HasFactory;
    protected $fillable = [
        'invoice_id',
        'Invoice_ref',
        'attachment_type',
        'file',
        'original_file_name',
        'mime_type',
        'size',
        'title',
        'description',
        'customer_visible',
        'mobile_user_visible',
        'deleted_at'

    ];
    public static function saveInvoiceAttachments($data){
        return self::updateOrCreate(['id' => $data['id'] ?? null],$data);
    }
    public function Invoices(){
        return $this->belongsTo(Invoice::class, 'po_id','id');
    }
    public function attachmentType(){
        return $this->belongsTo(AttachmentType::class, 'attachment_type','id')->whereNull('deleted_at');
    }
}
