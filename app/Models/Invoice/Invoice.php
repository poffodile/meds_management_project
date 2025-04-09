<?php

namespace App\Models\Invoice;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;
use App\Models\Constructor_customer_site;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'home_id',
        'customer_id',
        'project_id',
        'site_delivery_add_id',
        'invoice_ref',
        'invoice_type',
        'customer_ref',
        'customer_job_ref',
        'purchase_order_ref',
        'invoice_date',
        'payment_terms',
        'deposit_percentage',
        'due_date',
        'sub_total',
        'VAT_id',
        'VAT_amount',
        'Total',
        'outstanding',
        'status',
        'tags',
        'is_printed',
        'is_emailed',
        'customer_notes', 
        'terms',
        'internal_notes',
        'site_telephone_code',
        'site_mobile',
        'site_mobile_code',
        'site_telephone',
        'site_county',
        'site_postcode',
        'site_city',
        'site_address',
        'company_name',
        'site_name',
        'region',
        'email',
        'mobile',
        'invoice_mobile_code',
        'telephone',
        'telephone_code',
        'postcode',
        'county',
        'city',
        'address',
        'name',
        'contact_id',
        'deleted_at'

    ];
    public static function saveInvoice($data){
        return self::updateOrCreate(['id' => $data['id'] ?? null],$data);
    }
    public static function getAllInvoices($home_id){
        return self::where('home_id',$home_id)->whereNull('deleted_at');
    }
    public function customers(){
        return $this->belongsTo(Customer::class, 'customer_id','id');
    }
    public function sites(){
        return $this->belongsTo(Constructor_customer_site::class, 'site_delivery_add_id','id');
    }
    public function invoiceAttachments(){
        return $this->hasMany(InvoiceAttachment::class, 'invoice_id', 'id')->whereNull('deleted_at');
    }
    public function invoiceProducts(){
        return $this->hasMany(InvoiceProduct::class, 'invoice_id', 'id')->whereNull('deleted_at');
    }

}
