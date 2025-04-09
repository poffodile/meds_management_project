<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    use HasFactory;

    protected $fillable = [
        'home_id',
        'user_id',
        'quote_ref',
        'customer_id',
        'billing_add_id',
        'site_add_id',
        'site_delivery_add_id',
        'project_id',
        'quota_type',
        'quota_date',
        'expiry_date',
        'customer_ref',
        'customer_job_ref',
        'purchase_order_ref',
        'source',
        'performed_job_date',
        'period',
        'tags',
        'sub_total',
        'vat_amount',
        'total',
        'deposit',
        'outstanding',
        'profit',
        'extra_information',
        'customer_notes',
        'tearms',
        'internal_notes',
        'converted_to',
        'status'
    ];

    public function products()
    {
        return $this->hasMany(QuoteProduct::class);
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function billingAddress()
    {
        return $this->belongsTo(CustomerBillingAddress::class, 'billing_add_id');
    }

    public function siteAddress()
    {
        return $this->belongsTo(Constructor_customer_site::class, 'billing_add_id');
    }

    public function attachments()
    {
        return $this->hasMany(QuoteAttachment::class);
    }

    public function callBack()
    {
        return $this->hasOne(QuoteCallBack::class, 'quote_id');
    }

    public static function getDraftCount($home_id){
        return self::where('status', 'Draft')->where('home_id', $home_id)->count();
    }
 
    public static function getCallBackCount($home_id){
        return self::where('status', 'Call Back')->where('home_id', $home_id)->count();
    }

    public static function getAcceptedCount($home_id){
        return self::where('status', 'Accepted')->where('home_id', $home_id)->count();
    }

    public static function getActionedCount($home_id){
        return self::where('status', 'Processed')->where('home_id', $home_id)->count();
    }
        
    public static function getRejectedCount($home_id){
        return self::where('status', 'Rejected')->where('home_id', $home_id)->count();
    }
    

}
