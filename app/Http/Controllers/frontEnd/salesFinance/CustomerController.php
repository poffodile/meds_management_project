<?php

namespace App\Http\Controllers\frontEnd\salesFinance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session, DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\User;
use App\Models\Customer;
use App\Models\Week;
use App\Models\Region;
use App\Models\Country;
use App\Models\Job_title;
use App\Models\Task_type;
use App\Models\Customer_type;
use App\Models\CRMSectionType;
use App\Models\Crm_customer_call;
use App\Models\Crm_customer_task;
use App\Models\Crm_customer_note;
use App\Models\Crm_customer_email;
use App\Models\CrmCustomerComplaint;
use App\Models\Construction_currency;
use App\Models\Construction_tax_rate;
use App\Models\Constructor_customer_site;
use App\Models\Construction_customer_login;
use App\Models\Constructor_additional_contact;
use App\Models\CustomerBillingAddress;
use App\Models\LogHistory;
use App\Models\Supplier;

class CustomerController extends Controller
{
    public function customer_add_edit(Request $request)
    {
        // echo "<pre>";print_r(Auth::user()->home_id);die;
        // $key = base64_decode($request->key);
        $key = $request->key;
        // echo $key;die;
        if ($key) {
            $task = 'Edited';
        } else {
            $task = 'Added';
        }
        $data['customer'] = Customer::find($key);
        if($key){
            $contact=Constructor_additional_contact::whereNull('deleted_at')->where('customer_id', $key)->get();
            $login=Construction_customer_login::whereNull('deleted_at')->where('customer_id', $key)->get();
        }else{
            $contact=array();
            $login=array();
        }
        $data['task'] = $task;
        $data['page'] = 'customers';
        $data['del_status'] = 0;
        $data['customer_type'] = Customer_type::whereNull('deleted_at')->where('status', 1)->get();
        $data['job_title'] = Job_title::whereNull('deleted_at')->where('status', 1)->get();
        $data['contact'] = $contact;
        $data['login'] = $login;
        $data['country'] = Country::all_country_list();
        $data['country_code']=Country::getCountriesNameCode();
        $home_id=Auth::user()->home_id;
        $data['home_id'] =$home_id; 
        $data['key']=$key;
        $data['region']=Region::where(['home_id'=>$home_id,'status'=>1,'deleted_at'=>null])->get();
        $data['tax']=Construction_tax_rate::getAllTax_rate($home_id,'Active');
        // echo "<pre>";print_r($data['country_code']);die;
        return view('frontEnd.salesAndFinance.jobs.add_customer', $data);
    }
    public function customer_add_edit_save(Request $request)
    {
        // echo "<pre>";print_r($request->all());die;
        if($request->email !=''){
            $validator = Validator::make($request->all(), [
                'email' => [Rule::unique('customers')->ignore($request->id)],
            ]);
            
        }else{
            $validator = Validator::make($request->all(), [
                'name'=>'required',
                'contact_name'=>'required',
                'address'=>'required',
            ]);
             
        }
        if ($validator->fails()) {
            return response()->json(['vali_error' => $validator->errors()->first()]);
        }
        try {
            $customer = Customer::saveCustomer($request->all());
            return response()->json($customer);
        } catch (\Exception $e) {
            Log::error('Error saving Tag: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function default_address(Request $request)
    {
        // echo "<pre>";print_r($request->all());die;
        // return $customer_id=$request->customer_id;

        $country = Country::all_country_list();
        $userType=$request->userType;
        if($userType == 1){
            $address_details = Customer::find($request->customer_id);
            $country_code=$address_details->country_code ?? 0;
        }else if($userType == 2){
            $address_details = Supplier::find($request->customer_id);
            $country_code=$address_details->country_id ?? 0;
        }
        
        if(!$address_details){
            return response()->json(['error'=>"something went wrong"]);
        }
        // echo $country_code;die;
        $result = '';
        if ($request->check == 1) {
            $result .= '<option value="" selected disabled>None</option>';
            foreach ($country as $country_codev) {
                $select = ($country_codev->id == $country_code) ? "selected" : "";
                $result .= '<option value="' . $country_codev->code . '" ' . $select . '>' . $country_codev->name . ' (' . $country_codev->code . ')</option>';
            }
        } 
        // else {
        //     $result .= '<option value="" selected disabled>None</option>';
        //     foreach ($country as $country_codev) {
        //         $result .= '<option value="' . $country_codev->code . '">' . $country_codev->name . ' (' . $country_codev->code . ')</option>';
        //     }
        // }
        $data['reslut'] = $result;
        $data['details'] = $address_details;
        return response()->json($data);
    }
    public function save_contact(Request $request)
    {
        // echo "<pre>";print_r($request->all());die;
        $vali_data = [];

        if ($request->filled('telephone')) {
            $vali_data['telephone'] = [
                'required',
                'regex:/^\d{10}$/',
            ];
        }

        if ($request->filled('mobile')) {
            $vali_data['mobile'] = [
                'required',
                'regex:/^\d{10}$/',
            ];
        }

        $vali_data = array_merge($vali_data, [
            'contact_name' => 'required',
            'address' => 'required',
            'userType' => 'required',
        ]);

        $validator = Validator::make($request->all(),$vali_data);
        if ($validator->fails()) {
            return response()->json(['vali_error' => $validator->errors()->first()]);
        }
        // die;
        $data=$request->all();
        $data['home_id']=Auth::user()->home_id;
        try {
            $customer = Constructor_additional_contact::saveCustomerAdditional($data);
            if($request->id == ''){
                return response()->json(['success'=>true,'message'=>'Contact Added Succuessfully Done','data'=>$customer]);
            }else{
                return response()->json(['success'=>true,'message'=>'Contact Updated Succuessfully Done','data'=>$customer]);
            }
        } catch (\Exception $e) {
            Log::error('Error saving Tag: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function delete_contact(Request $request ){
        // echo "<pre>";print_r($request->all());die;
        $delete= Constructor_additional_contact::where('id', $request->id)->update(['deleted_at' => Carbon::now()]);
    }
    public function save_site(Request $request)
    {
        // echo "<pre>";print_r($request->all());die;
        try {
            $customer = Constructor_customer_site::saveCustomerAdditional($request->all());
            if($request->id ==''){
                return response()->json(['success'=>true,'message'=>'Site has been saved','data'=>$customer]);
            }else{
                return response()->json(['success'=>true,'message'=>'Site has been Updated','data'=>$customer]);
            }
        } catch (\Exception $e) {
            Log::error('Error saving Tag: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
            // echo "error";
        }
    }
    public function getAllSite(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $getAllSite=Constructor_customer_site::whereNull('deleted_at')->where('customer_id', $request->customer_id)->orderBy('id', 'desc')->paginate(10);
        $data=array();
        foreach($getAllSite as $sitev){
            // $job_title_detail=Job_title::find($sitev->title_id);
            $site_regionName=Region::find($sitev->region);
            $data[]=[
                'id'=>$sitev->id,
                'customer_id'=>$sitev->customer_id,
                'site_name'=>$sitev->site_name,
                'contact_name'=>$sitev->contact_name,
                'title_id'=>$sitev->title_id,
                'company_name'=>$sitev->company_name,
                'email'=>$sitev->email,
                'telephone_country_code'=>$sitev->telephone_country_code,
                'telephone'=>$sitev->telephone,
                'mobile_country_code'=>$sitev->mobile_country_code,
                'mobile'=>$sitev->mobile,
                'fax'=>$sitev->fax,
                'region'=>$sitev->region,
                'address'=>$sitev->address,
                'city'=>$sitev->city,
                'country'=>$sitev->country,
                'country_id'=>$sitev->country_id,
                'catalogue'=>$sitev->catalogue,
                'notes'=>$sitev->notes,
                'post_code'=>$sitev->post_code,
                'status'=>$sitev->status,
                'site_regionName'=>$site_regionName->title ?? "",
            ];
        }
        // return response()->json(['success' =>true,'data'=>$data]);
        return response()->json([
            'success' => true, 'data' => $data, 
            'pagination' => [
                    'total' => $getAllSite->total(),
                    'current_page' => $getAllSite->currentPage(),
                    'last_page' => $getAllSite->lastPage(),
                    'per_page' => $getAllSite->perPage(),
                    'next_page_url' => $getAllSite->nextPageUrl(),
                    'prev_page_url' => $getAllSite->previousPageUrl(),
                ]
        ]);
    }
    public function delete_site(Request $request){
        $delete= Constructor_customer_site::where('id', $request->id)->update(['deleted_at' => Carbon::now()]);
        echo "done";
    }
    public function save_login(Request $request)
    {
        // echo "<pre>";print_r($request->all());die;
        try {
            $customer = Construction_customer_login::saveCustomerAdditional($request->all());
            if($request->id == ''){
                return response()->json(['success'=>true,'message'=>'Contact has been saved successfully','data'=>$customer]);
            }else{
                return response()->json(['success'=>true,'message'=>'Contact has been updated successfully','data'=>$customer]);
            }
        } catch (\Exception $e) {
            Log::error('Error saving Tag: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
            // echo "error";
        }
    }
    public function getAllLogin(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $getAllContact=Construction_customer_login::whereNull('deleted_at')->where('customer_id', $request->customer_id)->orderBy('id', 'desc')->paginate(10);
        // return response()->json(['success' =>true,'data'=>$getAllContact]);
        return response()->json([
            'success' => true, 'data' => $getAllContact, 
            'pagination' => [
                    'total' => $getAllContact->total(),
                    'current_page' => $getAllContact->currentPage(),
                    'last_page' => $getAllContact->lastPage(),
                    'per_page' => $getAllContact->perPage(),
                    'next_page_url' => $getAllContact->nextPageUrl(),
                    'prev_page_url' => $getAllContact->previousPageUrl(),
                ]
        ]);
    }
    public function delete_login(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $delete= Construction_customer_login::where('id', $request->id)->update(['deleted_at' => Carbon::now()]);
    }
    public function active_customer(Request $request)
    {
        $home_id = Auth::user()->home_id;
        $data['customer'] = Customer::get_customer_list_Attribute($home_id, $request->list_mode);
        $data['list_mode'] = $request->list_mode;
        $data['active_customer'] = Customer::getConvertedCustomersCount($home_id);
        $data['inactive_customer'] = Customer::where(['is_converted' => 1, 'status' => 0, 'home_id' => $home_id,'deleted_at'=>null])->count();
        $data['users'] = User::getHomeUsers($home_id);
        $data['customer_email_to']=$this->customer_email_to($home_id);
        // echo "<pre>";print_r($data['customer_email_to']);die;
        $data['rate']=Construction_tax_rate::getAllTax_rate($home_id,'Active');
        $data['weeks'] = Week::getWeeklist();
        $data['task_type']=Task_type::getAllTask_type($home_id);
        $data['job_title'] = Job_title::whereNull('deleted_at')->where('status', 1)->get();
        $data['country'] = Country::all_country_list();
        $data['country_code']=Country::getCountriesNameCode();
        // echo "<pre>";print_r($data['country_code']);die;
        $data['page']="customers";
        return view('frontEnd.salesAndFinance.jobs.active_customer', $data);
    }
    private function customer_email_to($home_id){
        $user=User::where(['home_id'=>$home_id,'is_deleted'=>0])->get();
        $data=array();
        foreach($user as $val){
            $data[]=[
                'id'=>$val->id,
                'name'=>$val->name,
                'email'=>$val->email,
                'type'=>'user',
            ];
        }
        // Ram here code for supplier leave for now
        return $data;
    }
    public function customer_type()
    {
        $home_id = Auth::user()->home_id;
        $data['customer_type'] = Customer_type::whereNUll('deleted_at')->get();
        $data['home_id'] = $home_id;
        $data['page']='setting';
        // echo "<pre>";print_r($customer_type);die;
        return view('frontEnd.salesAndFinance.jobs.customer_type', $data);
    }
    public function customer_type_edit_form(Request $request)
    {
        $data = Customer_type::find($request->id);
        return $data;
    }
    public function save_customer_type(Request $request)
    {
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(),[
            'title'=>'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['vali_error' => $validator->errors()->first()]);
        }
        $home_id = Auth::user()->home_id;

        $insert = '';
        $table = new Customer_type;
        try {
            $insert = $table::updateOrCreate(
                ['id' => $request->id ?? null],
                $request->all(),
            );
        } catch (\Exception $e) {
            return response()->json(['success' => 'false', 'message' => $e->getMessage()], 500);
        }
        // echo "<pre>";print_r($insert);die;
        if ($insert) {
            if ($insert->status == 1) {
                echo '<option value="' . $insert->id . '">' . $insert->title . '</option>';
            }
        } else {
            echo "error";
        }
    }
    public function add_currency(Request $request)
    {
        $all_country = Country::all();
        foreach ($all_country as $val) {
            $table = new Construction_currency;
            $table->country_id = $val->id;
            $table->currency_code = $val->currency_code;
            $table->save();
        }
        $currecny = Construction_currency::all();
        echo "<pre>";
        print_r($currecny);
        die;
    }

    public function SaveCustomerData(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'contact_name' => 'required',
            'address' => 'required',
            'email' => 'required',
            'telephone' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Call the model's method to save customer data
        $saveData = Customer::saveCustomerData($request->all(), $request->customer_id);

        // Return the appropriate response
        return response()->json([
            'success' => (bool) $saveData,
            'message' => $saveData ? 'Customer added successfully.' : 'Customer could not be created.'
        ]);
    }

    public function getCustomerList()
    {
        $data =  Customer::getCustomerList();

        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? $data : 'No data.'
        ]);
    }

    public function getCustomerDetails(Request $request){

        $data =  Customer::getCustomerDetails($request->id);

        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? $data : 'No data.'
        ]);
    }

    public function SaveCustomerContactData(Request $request) {
        $validator = Validator::make($request->all(), [
            'contact_name' => 'required',
            'address' => 'required',
            'email' => 'required',
            'telephone' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $sameAsDefault =  ['same_as_default' => $request->has('same_as_default') ? '1' : '0'];  
        $data =  CustomerBillingAddress::saveCustomerContactDetails(array_merge($sameAsDefault, $request->all()));

        return response()->json([
            'success' => (bool) $data,
            'message' => $data ? "Customer Contact added successfully" : 'Customer contact could not be added.',
            'lastid' => $data->id
        ]);
    }

    public function getCustomerJobTitle(){
        $data =  Job_title::getCustomerJobTitle();

        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? $data : 'No data.'
        ]);
    }

    public function saveJobTitle(Request $request){
        $data = Job_title::saveJobTitle($request->all(),  Auth::user()->home_id);

        if($request->job_title_id == null){ 
            return response()->json([
                'success' => (bool) $data,
                'message' => $data ? "Job Title added successfully" : 'Job Title could not be added.'
            ]); 
        } else {
            return response()->json([
                'success' => (bool) $data,
                'message' => $data ? "Job Title edited successfully" : 'Job Title could not be edited.'
            ]);  
        }
    }

    public function saveCustomerSiteAddress(Request $request){

        $validator = Validator::make($request->all(), [
            'site_name' => 'required',
            'contact_name' => 'required',
            'company_name' => 'required',
            'telephone' => 'required',
            'email' => 'required',
            'address' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }


        $data = Constructor_customer_site::saveCustomerAdditional($request->all());
        return response()->json([
            'success' => (bool) $data,
            'message' => $data ? "Site Address added successfully" : 'Site Address could not be added.',
            'id' => $data
        ]);
    }

    public function getCustomerBillingAddress(Request $request){
        // dd($request);
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = CustomerBillingAddress::getCustomerBillingAddress($request->id);

        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? $data : 'No data.'
        ]);

    }

    public function getCustomerBillingAddressData(Request $request){
        
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = CustomerBillingAddress::getCustomerBillingAddressData($request->id);

        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? $data : 'No data.'
        ]);
    }

    public function getCustomerSiteAddress(Request $request) {

        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = Constructor_customer_site::getCustomerSiteAddress($request->id);

        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? $data : 'No data.'
        ]);
    }

    // public function getCustomerSiteData(Request $request){
    //     $validator = Validator::make($request->all(), [
    //         'id' => 'required'
    //     ]);
    //     if ($validator->fails()) {
    //         return response()->json(['errors' => $validator->errors()], 422);
    //     }

    //     $data = Constructor_customer_site::getCustomerSiteData($request->id);

    //     return response()->json([
    //         'success' => (bool) $data,
    //         'data' => $data ? $data : 'No data.'
    //     ]);
    // }

    public function getCustomerSiteDetails(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = Constructor_customer_site::getCustomerSiteDetails($request->id);

        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? $data : 'No data.'
        ]);
    }
    public function save_crm_customer_call(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'crm_type_id' => 'required',
            'content' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['vali_error' => $validator->errors()->first()]);
        }
        if ($request->notify_radio == 1) {
            $validator = Validator::make($request->all(), [
                'notify_user' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json(['vali_error' => $validator->errors()->first()]);
            }
            $notification = $request->has('notification') ? 1 : 0;
            $sms = $request->has('sms') ? 1 : 0;
            $email = $request->has('email') ? 1 : 0;
        }
        if (!isset($notification) || !isset($sms) || !isset($email)) {
            $notification = $sms = $email = null;
        }

        if ($request->telephone) {
            $phone = "+" . $request->country_code . "-" . $request->telephone;
        } else {
            $phone = $request->telephone;
        }
        $values = [
            'home_id' => Auth::user()->home_id,
            'customer_id' => $request->call_customer_id,
            'contact_id' => $request->call_contact,
            'direction' => $request->direction,
            'telephone' => $phone,
            'crm_type_id' => $request->crm_type_id,
            'notes' => $request->content,
            'notify' => $request->notify_radio,
            'user_id' => $request->notify_user,
            'notification' => $notification,
            'sms' => $sms,
            'email' => $email,
            'customer_visibility' => $request->customer_visible
        ];
        try{
            $customer_call= Crm_customer_call::save_customer_call($values);
            $type=CRMSectionType::find($customer_call->crm_type_id);
            
            $notes = $customer_call->notes; // Default notes value
            if ($customer_call->notify == 1) {
                $notification = ($customer_call->notification == 1) ? "Notification" : "";
                $sms = ($customer_call->sms == 1) ? "SMS" : "";
                $email = ($customer_call->email == 1) ? "Email" : "";
                $sendAsTypes = array_filter([$notification, $email, $sms]);
                
                $notes=['message'=>$customer_call->notes,'notify'=>Auth::user()->name,'send_as'=>$sendAsTypes];
            }
            $log_history = [
                'home_id' => Auth::user()->home_id,
                'taskId' => $customer_call->id,
                'userId' => $request->call_customer_id,
                'userType' => 1,
                'type' => $type->title,
                'notes' => json_encode($notes),
                'status' => '',
                'modelName' => 'Crm_customer_call',
            ];
            // echo "<pre>";print_r($log_history);die;
            $LogHistory=LogHistory::saveLogHistory($log_history);
            $data=['type'=>$type->title,$customer_call,'LogHistory'=>$LogHistory];
            return response()->json(['success' =>true,'data'=>$data]);
        }catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    public function get_all_crm_customer_call(Request $request){
        $getAllcrmlist_call=Crm_customer_call::getAllcrmlist($request->id)->orderBy('id', 'desc')->paginate(10);
        $data=array();
        foreach($getAllcrmlist_call as $val){
            $type=CRMSectionType::find($val->crm_type_id);
            $data[]=[
                'customer_visibility'=>$val->customer_visibility,
                'telephone'=>$val->telephone,
                'notes'=>$val->notes,
                'type'=>$type->title
            ];
        }
        // return response()->json(['success' =>true,'data'=>$data]);
        return response()->json([
            'success' => true, 'data' => $data, 
            'pagination' => [
                    'total' => $getAllcrmlist_call->total(),
                    'current_page' => $getAllcrmlist_call->currentPage(),
                    'last_page' => $getAllcrmlist_call->lastPage(),
                    'per_page' => $getAllcrmlist_call->perPage(),
                    'next_page_url' => $getAllcrmlist_call->nextPageUrl(),
                    'prev_page_url' => $getAllcrmlist_call->previousPageUrl(),
                ]
        ]);
    }
    public function save_crm_customer_email(Request $request){
        // echo "<pre>";print_r($request->all());die;
        // echo $request->message;die;
        $validator = Validator::make($request->all(), [
            'to' => 'required',
            'cc' => 'required',
            'subject' => 'required',
            'message' => 'required',
        ]);
        // Ram when this is comming multiple in future then uncomment below codes
        // $validator = Validator::make($request->all(), [
        //     'to' => 'required|array|min:1',
        //     'to.*' => 'required|string',
        //     'cc' => 'required|array|min:1',
        //     'cc.*' => 'required|string',
        //     'subject' => 'required|string',
        //     'message' => 'required|string',
        // ], [
        //     'to.required' => 'The recipient field is required.',
        //     'to.array' => 'The recipient field must be an array.',
        //     'to.min' => 'At least one recipient is required.',
        //     'to.*.required' => 'The recipient field must be a valid email address.',
            
        //     'cc.required' => 'The CC field is required.',
        //     'cc.array' => 'The CC field must be an array.',
        //     'cc.min' => 'At least one CC email address is required.',
        //     'cc.*.required' => 'The CC field is required.',
        
        //     'subject.required' => 'The subject field is required.',
        //     'message.required' => 'The message field is required.',
        // ]);

        if ($validator->fails()) {
            return response()->json(['vali_error' => $validator->errors()->first()]);
        }
        if ($request->notify == 1) {
            $validator = Validator::make($request->all(), [
                'notify_user' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json(['vali_error' => $validator->errors()->first()]);
            }
            $notification = $request->has('notification') ? 1 : 0;
            $sms = $request->has('sms') ? 1 : 0;
            $email = $request->has('email') ? 1 : 0;
        }
        if (!isset($notification) || !isset($sms) || !isset($email)) {
            $notification = $sms = $email = null;
        }
        if ($request->hasFile('image_attachment')) {
            $imageName = time().'.'.$request->image_attachment->extension();      
            $request->image_attachment->move(public_path('images/customer_crm'), $imageName);
        }else{
            $imageName='';
        }
        $values = [
            'home_id' => Auth::user()->home_id,
            'customer_id' => $request->email_customer_id,
            'to' => $request->to,
            'cc' => $request->cc,
            'subject' => $request->subject,
            'message' => $request->message,
            'notify' => $request->notify,
            'user_id' => $request->notify_user,
            'notification' => $notification,
            'sms' => $sms,
            'email' => $email,
            'attachments'=>$imageName
        ];
        try{
            $customer_email= Crm_customer_email::save_customer_email($values);
            $customer_crm_email=Crm_customer_email::find($customer_email->id);
            $customer_detail=Customer::find($customer_email->customer_id);
            $user_detail=User::find($customer_email->to);
            $data=$customer_crm_email;
            $data['customer_email'] = $customer_detail->email ?? "";
            $data['name']=$customer_detail->name ?? "";
            $data['send_email']=$user_detail->email ?? "";
            return response()->json(['success' =>true,'data'=>$data]);
        }catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    public function get_all_crm_customer_email(Request $request){
        // echo "<pre>";print_r($request->id);die;
        $getAllcrmlistEmail=Crm_customer_email::getAllcrmEmail($request->id)->orderBy('id', 'desc')->paginate(10);
        $data=array();
        foreach($getAllcrmlistEmail as $val){
            $customer_detail=Customer::find($val->customer_id);
            $user_detail=User::find($val->to);
            $data[]=[
                'id'=>$val->id,
                'customer_visibility'=>$val->customer_visibility,
                'telephone'=>$val->telephone,
                'message'=>$val->message,
                'type'=>'System',
                'customer_email'=>$customer_detail->email ?? "",
                'name'=>$customer_detail->name ?? "",
                'send_email'=>$user_detail->email ?? "",
            ];
        }
        return response()->json([
            'success' => true, 'data' => $data, 
            'pagination' => [
                    'total' => $getAllcrmlistEmail->total(),
                    'current_page' => $getAllcrmlistEmail->currentPage(),
                    'last_page' => $getAllcrmlistEmail->lastPage(),
                    'per_page' => $getAllcrmlistEmail->perPage(),
                    'next_page_url' => $getAllcrmlistEmail->nextPageUrl(),
                    'prev_page_url' => $getAllcrmlistEmail->previousPageUrl(),
                ]
        ]);
        // return response()->json(['success' =>true,'data'=>$data]);
    }
    public function visibility_change(Request $request){
        try{
            $customer_crm_email=Crm_customer_email::find($request->id);
            if($customer_crm_email->customer_visibility == 0){
                $status=1;
            }else{
                $status=0;
            }
            $customer_crm_email->customer_visibility=$status;
            $customer_crm_email->save();
            return response()->json(['success' =>true,'data'=>$customer_crm_email]); 
        }catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    public function save_crm_customer_task(Request $request){
        // echo "<pre>";print_r($request->all());die;
        if($request->form_type == 'task_form'){
            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'task_type_id' => 'required',
                'start_date' => 'required',
                'start_time' => 'required',
                'end_date' => 'required',
                'end_time' => 'required',
            ]);
        }else{
            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'task_type_id' => 'required',
            ]);
        }
        if ($validator->fails()) {
            return response()->json(['vali_error' => $validator->errors()->first()]);
        }
        if ($request->notify == 1) {
            $notification = $request->has('notification') ? 1 : 0;
            $sms = $request->has('sms') ? 1 : 0;
            $email = $request->has('email') ? 1 : 0;
        }

        if (!isset($notification) || !isset($sms) || !isset($email)) {
            $notification = $sms = $email = null;
        }
        $values = [
            'id'=>$request->task_id,
            'home_id' => Auth::user()->home_id,
            'customer_id' => $request->task_customer_id,
            'user_id' => $request->user_id ?? $request->user_id_timer,
            'title' => $request->title ?? $request->title_timer,
            'task_type_id' => $request->task_type_id ?? $request->task_type_id_time,
            'start_date' => $request->start_date ?? Carbon::now()->toDateString(),
            'start_time' => $request->start_time ?? Carbon::now()->toTimeString(),
            'end_date' => $request->end_date,
            'end_time' => $request->end_time,
            'is_recurring' => $request->is_recurring ?? false,
            'notify' => $request->notify,
            'notification' => $notification,
            'sms' => $sms,
            'email' => $email,
            'notify_date' => $request->task_date,
            'notify_time' => $request->task_time,
            'notes' => $request->notes
        ];
        // echo "<pre>";print_r($values);die;
        try{
            $crm_customer_task = Crm_customer_task::save_customer_task($values);
            $task_type=Task_type::find($crm_customer_task->task_type_id);
            $customer=Customer::find($crm_customer_task->customer_id);
            $data=$crm_customer_task;
            $data['type']=$task_type->title;
            $data['customer_name']=$customer->name;
            return response()->json(['success' =>true,'data'=>$data]);
        }catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    public function get_customer_details(Request $request){
        $data['contact']=Constructor_additional_contact::where(['customer_id'=>$request->id,'deleted_at'=>null])->get();
        $data['customer']=Customer::find($request->id);
        return $data;
    }
    public function get_all_crm_customer_task(Request $request){
        $getAllcrmlistTask=Crm_customer_task::getAllcrmTask($request->id)->orderBy('id', 'desc')->paginate(10);
        // echo "<pre>";print_r($getAllcrmlistTask);die;
        $data=array();
        foreach($getAllcrmlistTask as $val){
            $task_type=Task_type::find($val->task_type_id);
            $customer=Customer::find($val->customer_id);
            $data[]=[
                'id'=>$val->id,
                'home_id'=>$val->home_id,
                'customer_id'=>$val->customer_id,
                'user_id'=>$val->user_id,
                'title'=>$val->title,
                'task_type_id'=>$val->task_type_id,
                'start_date'=>$val->start_date,
                'start_time'=>$val->start_time,
                'end_date'=>$val->end_date,
                'end_time'=>$val->end_time,
                'is_recurring'=>$val->is_recurring,
                'notify'=>$val->notify,
                'notification'=>$val->notification,
                'email'=>$val->email,
                'sms'=>$val->sms,
                'notify_date'=>$val->notify_date,
                'notify_time'=>$val->notify_time,
                'notes'=>$val->notes,
                'type'=>$task_type->title,
                'customer_name'=>$customer->name
            ];
        }
        return response()->json([
            'success' => true, 'data' => $data, 
            'pagination' => [
                    'total' => $getAllcrmlistTask->total(),
                    'current_page' => $getAllcrmlistTask->currentPage(),
                    'last_page' => $getAllcrmlistTask->lastPage(),
                    'per_page' => $getAllcrmlistTask->perPage(),
                    'next_page_url' => $getAllcrmlistTask->nextPageUrl(),
                    'prev_page_url' => $getAllcrmlistTask->previousPageUrl(),
                ]
        ]);
        // return response()->json(['success' =>true,'data'=>$data]);
    }
    public function save_crm_customer_notes(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'notes_customer_id' => 'required',
            'crm_section_type_id' => 'required',
            'notes' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['vali_error' => $validator->errors()->first()]);
        }
        if ($request->notify == 1) {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json(['vali_error' => $validator->errors()->first()]);
            }
            $notification = $request->has('notification') ? 1 : 0;
            $sms = $request->has('sms') ? 1 : 0;
            $email = $request->has('email') ? 1 : 0;
        }

        if (!isset($notification) || !isset($sms) || !isset($email)) {
            $notification = $sms = $email = null;
        }
        $values = [
            'home_id' => Auth::user()->home_id,
            'customer_id' => $request->notes_customer_id,
            'contact' => $request->notes_contact,
            'crm_section_type_id' => $request->crm_section_type_id,
            'notes' => $request->notes,
            'notify' => $request->notify,
            'user_id' => $request->user_id,
            'notification' => $notification,
            'sms' => $sms,
            'email' => $email,
            'customer_visibility' => $request->customer_visibility
        ];
        
        try{
            $crm_customer_note = Crm_customer_note::save_customer_note($values);
            $task_type=CRMSectionType::find($crm_customer_note->crm_section_type_id);
            $customer=Customer::find($crm_customer_note->customer_id);
            $contact=Constructor_additional_contact::find($crm_customer_note->contact);
            $data=$crm_customer_note;
            $data['type']=$task_type->title;
            $data['customer_name']=$customer->name;
            $data['contact']=$customer->contact_name ?? "";
            return response()->json(['success' =>true,'data'=>$data]);
        }catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    public function get_all_crm_customer_note(Request $request){
        $getAllcrmlistNotes=Crm_customer_note::getAllcrmNotes($request->id)->orderBy('id', 'desc')->paginate(10);
        // echo "<pre>";print_r($getAllcrmlistNotes);die;
        $data=array();
        foreach($getAllcrmlistNotes as $val){
            $task_type=CRMSectionType::find($val->crm_section_type_id);
            $customer=Customer::find($val->customer_id);
            $contact=Constructor_additional_contact::find($val->contact);
            $data[]=[
                'id'=>$val->id,
                'home_id'=>$val->home_id,
                'customer_id'=>$val->customer_id,
                'contact'=>$contact->contact_name ?? "",
                'crm_section_type_id'=>$val->crm_section_type_id,
                'notes'=>$val->notes,
                'customer_visibility'=>$val->customer_visibility,
                'user_id'=>$val->user_id,
                'type'=>$task_type->title,
                'customer_name'=>$customer->name
            ];
        }
        return response()->json([
            'success' => true, 'data' => $data, 
            'pagination' => [
                    'total' => $getAllcrmlistNotes->total(),
                    'current_page' => $getAllcrmlistNotes->currentPage(),
                    'last_page' => $getAllcrmlistNotes->lastPage(),
                    'per_page' => $getAllcrmlistNotes->perPage(),
                    'next_page_url' => $getAllcrmlistNotes->nextPageUrl(),
                    'prev_page_url' => $getAllcrmlistNotes->previousPageUrl(),
                ]
        ]);
        // return response()->json(['success' =>true,'data'=>$data]);
    }
    public function save_crm_customer_complaints(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'complaint_customer_id' => 'required',
            'crm_section_type_id' => 'required',
            'compliant' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['vali_error' => $validator->errors()->first()]);
        }
        if ($request->notify == 1) {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json(['vali_error' => $validator->errors()->first()]);
            }
            $notification = $request->has('notification') ? 1 : 0;
            $sms = $request->has('sms') ? 1 : 0;
            $email = $request->has('email') ? 1 : 0;
        }

        if (!isset($notification) || !isset($sms) || !isset($email)) {
            $notification = $sms = $email = null;
        }
        $values = [
            'home_id' => Auth::user()->home_id,
            'customer_id' => $request->complaint_customer_id,
            'contact' => $request->comaplint_contact,
            'crm_section_type_id' => $request->crm_section_type_id,
            'notes' => $request->compliant,
            'notify' => $request->notify,
            'user_id' => $request->user_id,
            'notification' => $notification,
            'sms' => $sms,
            'email' => $email,
            'customer_visibility'=>0,
        ];
        
        try{
            $crm_customer_complaint = CrmCustomerComplaint::save_customer_complaint($values);
            $task_type=CRMSectionType::find($crm_customer_complaint->crm_section_type_id);
            $customer=Customer::find($crm_customer_complaint->customer_id);
            $contact=Constructor_additional_contact::find($crm_customer_complaint->contact);
            $data=$crm_customer_complaint;
            $data['type']=$task_type->title;
            $data['customer_name']=$customer->name;
            $data['contact']=$customer->contact_name ?? "";
            return response()->json(['success' =>true,'data'=>$data]);
        }catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    public function get_all_crm_customer_complaint(Request $request){
        // echo "<pre>";print_r($request->all());die;
        // $getAllcrmlist=CrmCustomerComplaint::getAllcrmComplaint($request->id);
        // $data=array();
        // foreach($getAllcrmlist as $val){
        //     $task_type=CRMSectionType::find($val->crm_section_type_id);
        //     $customer=Customer::find($val->customer_id);
        //     $contact=Constructor_additional_contact::find($val->contact);
        //     $data[]=[
        //         'id'=>$val->id,
        //         'home_id'=>$val->home_id,
        //         'customer_id'=>$val->customer_id,
        //         'contact'=>$contact->contact_name ?? "",
        //         'crm_section_type_id'=>$val->crm_section_type_id,
        //         'notes'=>$val->notes,
        //         'customer_visibility'=>$val->customer_visibility,
        //         'user_id'=>$val->user_id,
        //         'type'=>$task_type->title,
        //         'customer_name'=>$customer->name,
        //         'notify'=>$val->notify,
        //         'sms'=>$val->sms,
        //         'notification'=>$val->notification,
        //         'email'=>$val->email
        //     ];
        // }
        // return response()->json(['success' =>true,'data'=>$data]);
    // $getAllcrmlist = CrmCustomerComplaint::where('customer_id', $request->id)->paginate(10);
    $getAllcrmlist = CrmCustomerComplaint::where('customer_id', $request->id)
                  ->orderBy('id', 'desc')
                  ->paginate(10);

    $data = [];
    foreach ($getAllcrmlist as $val) {
        $task_type = CRMSectionType::find($val->crm_section_type_id);
        $customer = Customer::find($val->customer_id);
        $contact = Constructor_additional_contact::find($val->contact);

        $data[] = [
            'id' => $val->id,
            'home_id' => $val->home_id,
            'customer_id' => $val->customer_id,
            'contact' => $contact->contact_name ?? "",
            'crm_section_type_id' => $val->crm_section_type_id,
            'notes' => $val->notes,
            'customer_visibility' => $val->customer_visibility,
            'user_id' => $val->user_id,
            'type' => $task_type->title,
            'customer_name' => $customer->name,
            'notify' => $val->notify,
            'sms' => $val->sms,
            'notification' => $val->notification,
            'email' => $val->email,
        ];
    }

    return response()->json([
        'success' => true, 'data' => $data, 
        'pagination' => [
                'total' => $getAllcrmlist->total(),
                'current_page' => $getAllcrmlist->currentPage(),
                'last_page' => $getAllcrmlist->lastPage(),
                'per_page' => $getAllcrmlist->perPage(),
                'next_page_url' => $getAllcrmlist->nextPageUrl(),
                'prev_page_url' => $getAllcrmlist->previousPageUrl(),
            ]
    ]);
    }
    public function get_all_crm_customer_contacts(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $userType=$request->userType ?? null;
        $contact_list=Constructor_additional_contact::getAllcrmContacts($request->id)->where('userType',$userType)->orderBy('id', 'desc')->paginate(10);
        $data=array();
        foreach($contact_list as $val){
            if($userType == 1){
                $customer=Customer::find($val->customer_id);
                $job_title_details=Job_title::find($val->job_title_id);
            }else if($userType == 2){
                $customer=Supplier::find($val->customer_id);
                $job_title_details='';
            }
            $country_name=Country::find($val->country_id);
            
            $data[]=[
                'id'=>$val->id,
                'customer_id'=>$request->id,
                'contact'=>$val->contact_name ?? "",
                'job_title_id'=>$val->job_title_id,
                'job_title'=>$job_title_details->name ?? "",
                'crm_section_type_id'=>'',
                'customer_name'=>$customer->name ?? "",
                'email'=>$val->email ?? "",
                'telephone_country_code'=>$val->telephone_country_code,
                'telephone'=>$val->telephone ?? "",
                'mobile_country_code'=>$val->mobile_country_code,
                'mobile'=>$val->mobile ?? "",
                'address'=>$val->address ?? "",
                'city'=>$val->city ?? "",
                'country'=>$country_name->name ?? "",
                'postcode'=>$val->postcode ?? "",
                'default_billing'=>$val->default_billing,
                'fax'=>$val->fax,
                'country_id'=>$val->country_id,
                'userType'=>$val->userType,
                'status'=>$val->status,
            ];
        }
        // return response()->json(['success' =>true,'data'=>$data]);
        return response()->json([
            'success' => true, 'data' => $data, 
            'pagination' => [
                    'total' => $contact_list->total(),
                    'current_page' => $contact_list->currentPage(),
                    'last_page' => $contact_list->lastPage(),
                    'per_page' => $contact_list->perPage(),
                    'next_page_url' => $contact_list->nextPageUrl(),
                    'prev_page_url' => $contact_list->previousPageUrl(),
                ]
        ]);
    }
    public function GetCustomerWithContact(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $home_id = Auth::user()->home_id;
        // if($customer_id->userType == 1){
        //     $data=Constructor_additional_contact::where('customer_id',$customer_id)->orderBy('id', 'desc')->paginate(10);
        // }else if($customer_id->userType == 1)
        $contact=Constructor_additional_contact::where('home_id',$home_id)->where('deleted_at',null)->orderBy('id', 'desc')->get();
        // echo "<pre>";print_r($contact);die;
        $arrya=array();
        foreach($contact as $val){
            if($val->userType == 1){
                $customer_id=Customer::find($val->customer_id);
            }else if($val->userType == 2){
                $customer_id=Supplier::find($val->customer_id);
            }else if($val->userType == 3){
                $customer_id=Customer::find($val->customer_id);
            }
            
            $user=User::find($val->customer_id);
            $data[]=[
                'customers'=>$customer_id,
                'user'=>$user,
                'id'=>$val->id,
                'home_id'=>$val->home_id,
                'customer_id'=>$val->customer_id,
                'contact_name'=>$val->contact_name,
                'email'=>$val->email,
                'telephone'=>$val->telephone,
                'mobile'=>$val->mobile,
                'userType'=>$val->userType
            ];
        }
        return response()->json(['success'=>true,'data'=>$data]);
        
    }
    public function getAllCustomerList(){
        $home_id = Auth::user()->home_id;
        $customer = Customer::get_customer_list_Attribute($home_id, 'ACTIVE');
        return response()->json(['success'=>true,'data'=>$customer]);
    }
    public function getAllUserList(Request $request){
        $home_id = Auth::user()->home_id;
        $users = User::getHomeUsers($home_id);
        return response()->json(['success'=>true,'data'=>$users]);
    }
    public function GetFullHistory(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $home_id = Auth::user()->home_id;
        $LogHistory=LogHistory::whereNull('deleted_at')->where('home_id',$home_id)->orderBy('id','DESC')->get();
        $callHistoryData=array();
        foreach($LogHistory as $val){
            if($val->modelName == 'Crm_customer_call'){
                $data=$this->call_history($val->taskId,$val->notes);
                array_push($callHistoryData,$data);
            }
        }
        return response()->json(['success'=>true,'callHistoryData'=>$callHistoryData]);
    }
    private function call_history($taskId,$notes){
        $call_history=Crm_customer_call::find($taskId);
        
        $type=CRMSectionType::find($call_history->crm_type_id);
        $contact=Constructor_additional_contact::find($call_history->contact_id);
        $data=[
            'date'=>$call_history->created_at,
            'by'=>Auth::user()->name.'<br>('.Auth::user()->email.')',
            'contact'=>$contact->contact_name,
            'customer_visibility'=>$call_history->customer_visibility,
            'type'=>$type->title,
            'notes'=>$notes,
            'status'=>$call_history->status ?? "",
            'customer_visible'=>$call_history->customer_visibility,
            'modelName' => 'Crm_customer_call',
        ];
        return $data;
    }
   
}
