<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;
    protected $table="suppliers";
    protected $fillable=['home_id', 'user_id', 'name', 'code', 'contact_name', 'email', 'telephone_code_id', 'telephone', 'mobile_code_id', 'mobile', 'fax', 'website', 'address', 'city', 'county', 'postcode', 'country_id', 'currency_id', 'creadit_limit', 'vat_tax_no', 'account_ref', 'purchase_terms', 'notes', 'status'];

    public static function supplierSave($data){
        // echo "<pre>";print_r($data);die;
        return self::updateOrCreate(
            ['id' => $data['id'] ?? null],
            $data
        );
    }
    public static function allGetSupplier($home_id,$user_id){
        return self::where(['home_id'=>$home_id, 'user_id'=>$user_id,'deleted_at'=>null]);
    }

    public static function getActiveSuppliers( $home_id, $user_id){

        if($user_id == null){
            return self::where('home_id', $home_id)->where('status', 1)->where('deleted_at', null)->get();
        }
        return self::where('home_id', $home_id)->where('user_id', $user_id)->where('status', 1)->where('deleted_at', null)->get();
    }

    public function contacts(){
        return $this->hasMany(Constructor_additional_contact::class,'customer_id');
    }
    public function purchaseOrders(){
        return $this->hasMany(PurchaseOrder::class, 'supplier_id');
    }
    
    
}
