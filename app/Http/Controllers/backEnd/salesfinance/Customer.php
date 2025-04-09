<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Project;
// use App\Models\Quote;
use App\Models\Constructor_customer_site;
use App\Models\Constructor_additional_contact;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'customers';

    protected $fillable = [
        'home_id',
        'name',
        'customer_type_id',
        'contact_name',
        'job_title',
        'email',
        'telephone_country_code',
        'telephone',
        'mobile_country_code',
        'mobile',
        'fax',
        'website',
        'catalogue_id',
        'region',
        'address',
        'city',
        'country',
        'postal_code',
        'country_code',
        'site_notes',
        'currency',
        'credit_limit',
        'discount',
        'discount_type',
        'saga_ref',
        'company_reg',
        'vat_tax_no',
        'payment_terms',
        'assigned_product',
        'notes',
        'product_tax',
        'service_tax',
        'is_converted',
        'show_msg',
        'msg',
        'section_id',
        'status',
    ];


    public static function getConvertedCustomers($home_id){
        return Customer::where(['is_converted' => '1', 'status' => 1])->select('id','contact_name')->where('home_id', $home_id)->get();
    }

    public static function converToCustomer($id){
        return Customer::where('id', $id)->update(['is_converted' => 1]);
    }
    public static function saveCustomer(array $data)
    {
        if (isset($data['section_id'])) {
            $data['section_id'] = implode(',',$data['section_id']);
        }
        // echo "<pre>";print_r($data);die;
            $insert=self::updateOrCreate(
                ['id' => $data['id'] ?? null],
                $data
            );
        $data=['id'=>$insert->id,'name'=>$insert->name];
        return $data;
    }
    public static function getCustomerWithLeads($lastSegment, $home_id){

        $query = DB::table('customers')
        ->join('leads', 'customers.id', '=', 'leads.customer_id')
        ->join('lead_statuses', 'lead_statuses.id', 'leads.status')
        ->select('customers.*', 'leads.*', 'lead_statuses.title as status', 'lead_statuses.id as status_id', 'customers.email')
        ->orderBy('leads.created_at', 'desc')
        ->where('leads.home_id', $home_id)
        ->whereNull('leads.deleted_at');

        // dd($query->get());
   
        if ($lastSegment ===  "leads") {
            return $query->whereNotIn('assign_to', [0])->whereNotIn('leads.status', ['6','7'])->where('leads.converted_to', null)->get();
        } else if ($lastSegment === "unassigned"){
            return $query->where('assign_to', 0)->where('leads.converted_to', null)->get();
        } else if ($lastSegment === "rejected"){
            return $query->where('leads.status', '6')->where('leads.converted_to', null)->get();
        } else if ($lastSegment === "converted"){
            return $query->whereIn('leads.converted_to', ['quote', 'customer'])->where('customers.status', 1)->get();
        } else if ($lastSegment === "myLeads"){
            return $query->where('leads.assign_to', Auth::user()->id)->whereNotIn('leads.status', [7])->where('leads.converted_to', null)->get();
        } else if ($lastSegment === "authorization"){
            return $query->where('leads.status', 7)->where('leads.converted_to', null)->get();
        } else if ($lastSegment === "actioned") {
            return DB::table('customers')
                ->join('leads', 'leads.customer_id', '=','customers.id')
                ->join('lead_tasks', 'lead_tasks.lead_ref', '=', 'leads.lead_ref')
                ->join('lead_statuses', 'lead_statuses.id', 'leads.status')
                ->select('leads.lead_ref', 'customers.contact_name', 'customers.name', 'customers.email', 'customers.telephone', 'customers.mobile', 'leads.assign_to', 'lead_statuses.title as status', 'lead_statuses.id as status_id','customers.website','customers.address', 'customers.city', 'customers.country', 'customers.postal_code', 'leads.id as id', 'customers.id as customer_id', 'leads.authorization_status')
                ->orderBy('leads.created_at', 'desc')
                ->where('leads.converted_to', null)
                ->where('leads.home_id', $home_id)
                ->distinct()
                ->get();    
        }
    }

    public static function getCustomerLeads($id){
        return   DB::table('customers')
        ->join('leads', 'leads.customer_id', '=',   'customers.id')
        ->select('customers.*', 'leads.*', 'customers.email as email')
        ->where('leads.id', $id)
        ->first();
        // dd($return);
    }
    public static function get_customer_list_Attribute($home_id, $list_mode){
        $status = ($list_mode == 'ACTIVE') ? 1 : 0;
        return Customer::where(['is_converted' => '1', 'status' => $status,'home_id'=>$home_id,'deleted_at'=>null])->get();
    }

    public function sites()
    {
        return $this->hasMany(Constructor_customer_site::class, 'customer_id');
    }
    public function additional_contact()
    {
        return $this->hasMany(Constructor_additional_contact::class, 'customer_id');
    }
    public function customer_project()
    {
        return $this->hasMany(Project::class, 'customer_name');
    }

    public function quotes()
    {
        return $this->hasMany(Quote::class);
    }

    // public function customer_profession(){
    //     return $this->hasOne(Job_title::class, 'id');
    // }

    public static function saveCustomerData(array $data, $customerId = null)
    {
        $data['home_id'] = Auth::user()->home_id;
        $data['is_converted'] = 1;
        return self::updateOrCreate(['id' => $customerId], $data);
    }

    public static function getCustomerList(){
        return self::where('is_converted', 1)->where('status', 1)->get();
    }

    public static function getCustomerDetails($id){
        return self::where('id', $id)->get();
    }

    // public static function getCustomerSiteData($id){
    //     return self::where('id', $id)->get();
    // }

    public static function getConvertedCustomersCount($home_id){
        return self::where('is_converted', 1)->where('status', 1)->where('deleted_at', null)->where('home_id', $home_id)->count();
    }
  
}
