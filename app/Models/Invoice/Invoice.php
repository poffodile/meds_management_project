<?php

namespace App\Models\Invoice;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'home_id',
        'customer_id',
        'invoice_ref',
        'invoice_type',
        'invoice_date',
        'payment_terms',
        'due_date',
        'sub_total',
        'deposit_percentage',
        'VAT_id',
        'VAT_amount',
        'Total',
        'outstanding',
        'status',
        'is_printed',
        'is_emailed',
        'customer_notes',
        'terms',
        'internal_notes',
        'name',
        'contact_id',
        'deleted_at'

    ];
    public static function saveInvoice($data)
    {
        return self::updateOrCreate(['id' => $data['id'] ?? null], $data);
    }
    public static function getAllInvoices($home_id)
    {
        return self::where('home_id', $home_id)->whereNull('deleted_at');
    }
    public function serviceUser()
    {
        return $this->belongsTo(\App\ServiceUser::class, 'customer_id', 'id');
    }
    public function invoiceAttachments()
    {
        return $this->hasMany(InvoiceAttachment::class, 'invoice_id', 'id')->whereNull('deleted_at');
    }
    public function invoiceProducts()
    {
        return $this->hasMany(InvoiceProduct::class, 'invoice_id', 'id')->whereNull('deleted_at');
    }
}
