<?php

namespace App\Http\Controllers\backEnd;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session,DB,Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
// use App\Customer;
use App\Models\Customer;
use App\Models\Region;
use App\Models\Country;
use App\Models\Job_title;
use App\Models\Customer_type;
use App\Models\Construction_tax_rate;
use App\Models\Constructor_customer_site;
use App\Models\Construction_customer_login;
use App\Models\Constructor_additional_contact;

class CustomerController extends Controller
{
    public function customers(Request $request){
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        if($home_id){
            $query=Customer::whereNull('deleted_at')->where('is_converted', 1)->orderBy('id','DESC');

            $search = '';

            if(isset($request->limit)) {
                $limit = $request->limit;
                Session::put('page_record_limit',$limit);
            } else {

                if(Session::has('page_record_limit')){
                    $limit = Session::get('page_record_limit');
                } else{
                    $limit = 20;
                }
            }
            if(isset($request->search))
            {
                $search      = trim($request->search);
                $query = $query->where('name','like','%'.$search.'%');
            }
            $customers = $query->paginate($limit);
            // echo "<pre>";print_r($customers);die;
            $data['customers']=$customers;
            $data['limit']=$limit;
            $data['search']=$search;
            $data['page']='customers';
            return view('backEnd.jobs_management.customers',$data);
        }else {
            return redirect('admin/')->with('error',NO_HOME_ERR);
        }
    }
    public function customer_add(Request $request){
        $key=$request->key;
        if($key){
            $task='Edit';
        }else{
            $task='Add';
        }
        $admin   = Session::get('scitsAdminSession');
        $home_id=$admin->home_id;
        $data['home_id'] = $home_id;
        $data['customer']=Customer::find($key);
        $data['task']=$task;
        $data['page']='customers';
        $data['del_status']=0;
        $data['customer_type']=Customer_type::whereNull('deleted_at')->where('status', 1)->get();
        $data['job_title']=Job_title::whereNull('deleted_at')->where('status', 1)->get();
        $data['contact']=Constructor_additional_contact::whereNull('deleted_at')->where('customer_id', $key)->get();
        $data['site']=Constructor_customer_site::whereNull('deleted_at')->where('customer_id', $key)->get();
        $data['login']=Construction_customer_login::whereNull('deleted_at')->where('customer_id', $key)->get();
        $data['country']=Country::all_country_list();
        $data['region']=Region::where(['home_id'=>$home_id,'status'=>1,'deleted_at'=>null])->get();
        $data['tax']=Construction_tax_rate::getAllTax_rate($home_id,'Active');
        $data['country_code']=Country::getCountriesNameCode();
        // echo "<pre>";print_r($data['region']);die;
        return view('backEnd.jobs_management.customers_form',$data);
    }
    public function customer_type(Request $request){
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        if($home_id){
            $query=Customer_type::whereNull('deleted_at')->orderBy('id','DESC');

            $search = '';

            if(isset($request->limit)) {
                $limit = $request->limit;
                Session::put('page_record_limit',$limit);
            } else {

                if(Session::has('page_record_limit')){
                    $limit = Session::get('page_record_limit');
                } else{
                    $limit = 20;
                }
            }
            if(isset($request->search))
            {
                $search      = trim($request->search);
                $query = $query->where('name','like','%'.$search.'%');
            }
            $customer_type = $query->paginate($limit);
            
            $data['customer_type']=$customer_type;
            $data['limit']=$limit;
            $data['search']=$search;
            $data['page']='customer_type';
            return view('backEnd.jobs_management.customer_type',$data);
        }else {
            return redirect('admin/')->with('error',NO_HOME_ERR);
        }
    }
    public function customer_type_add(Request $request){
        $key=base64_decode($request->key);
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        if($key){
            $task='Edit';
        }else{
            $task='Add';
        }
        $data['type']=Customer_type::find($key);
        $data['task']=$task;
        $data['page']='customer_type';
        $data['del_status']=0;
        $data['home_id']=$home_id;
        return view('backEnd.jobs_management.customer_type_form',$data);
    }
    public function customer_type_save(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'title' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['vali_error' => $validator->errors()->first()]);
        }

        try {
            $insert=Customer_type::updateOrCreate(['id' => $request->id], $request->all());
        }catch (\Exception $e) {
            Log::error('Error saving Payment Type: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to save Payment Type. Please try again.']);
        }
        
        if(isset($request->id)){
            Session::flash('success','Updated Successfully Done');
        } else {
            Session::flash('success','Added Successfully Done');
        }
        
        if ($insert) {
            if ($insert->status == 1) {
                echo '<option value="' . $insert->id . '">' . $insert->title . '</option>';
            }
        } else {
            echo "error";
        }
    }
    public function customer_type_status_change(Request $request){
        $id=base64_decode($request->id);
        $status=$request->status;
        $table=Customer_type::find($id);
        $table->status=$status;
        $table->save();
        Session::flash('success','Status Change Successfully Done');
        echo "done";
    }
    public function customer_type_delete(Request $request){
        $id=base64_decode($request->id);
        $delete= Customer_type::where('id', $id)->update(['deleted_at' => Carbon::now()]);
        // $table=Customer_type::find($id);
        // $table->status=2;
        // $table->save();
        Session::flash('success','Deleted Successfully Done');
        echo "done";
    }
    public function customer_save(Request $request){
        // echo "<pre>";print_r($request->all());die;
        if($request->email !=''){
            $validator = Validator::make($request->all(), [
                'email' => [Rule::unique('customers')->ignore($request->id)],
            ]);
            if ($validator->fails()) {
                return response()->json(['vali_error' => $validator->errors()->first()]);
            }
        }
        $customer = Customer::saveCustomer($request->all());
        return response()->json($customer);
    }
    public function default_address(Request $request){
        // echo 1;die;
        $country = Country::all_country_list();
        $address_details = Customer::find($request->customer_id);
        $result = '';
        if ($request->check == 1) {
            $result .= '<option value="" selected disabled>None</option>';
            foreach ($country as $country_codev) {
                $select = ($country_codev->id == $address_details->country_code) ? "selected" : "";
                $result .= '<option value="' . $country_codev->code . '" ' . $select . '>' . $country_codev->name . ' (' . $country_codev->code . ')</option>';
            }
        } else {
            $result .= '<option value="" selected disabled>None</option>';
            foreach ($country as $country_codev) {
                $result .= '<option value="' . $country_codev->code . '">' . $country_codev->name . ' (' . $country_codev->code . ')</option>';
            }
        }
        $data['reslut'] = $result;
        $data['details'] = $address_details;
        return response()->json($data);
    }
    public function customer_contact_save(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $customer=Constructor_additional_contact::saveCustomerAdditional($request->all());
        // echo "<pre>";print_r($customer);die;
        if($customer){
            echo "done";
        }else{
            echo "error";
        }
    }
    public function delete_contact(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $id=$request->id;
        $delete= Constructor_additional_contact::where('id', $id)->update(['deleted_at' => Carbon::now()]);
        if($delete){
            Session::flash('success','Deleted Successfully Done');
            echo "done";
        }else{
            echo "error";
        }
        
    }
    public function customer_site_save(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $customer=Constructor_customer_site::saveCustomerAdditional($request->all());
        if($customer){
            echo "done";
        }else{
            echo "error";
        }
    }
    public function delete_site(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $id=$request->id;
        $delete= Constructor_customer_site::where('id', $id)->update(['deleted_at' => Carbon::now()]);
        if($delete){
            Session::flash('success','Deleted Successfully Done');
            echo "done";
        }else{
            echo "error";
        }
    }
    public function customer_login_save(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $customer=Construction_customer_login::saveCustomerAdditional($request->all());
        if($customer){
            echo "done";
        }else{
            echo "error";
        }
    }
    public function delete_login(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $id=$request->id;
        $delete= Construction_customer_login::where('id', $id)->update(['deleted_at' => Carbon::now()]);
        if($delete){
            Session::flash('success','Deleted Successfully Done');
            echo "done";
        }else{
            echo "error";
        }
    }
    public function customer_status_change(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $id=base64_decode($request->id);
        $status=$request->status;
        $table=Customer::find($id);
        $table->status=$status;
        $table->save();
        Session::flash('success','Status Change Successfully Done');
        echo "done";
    }
    public function customer_delete(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $id=base64_decode($request->id);
        $delete= Customer::where('id', $id)->update(['deleted_at' => Carbon::now()]);
        // $table=Customer::find($id);
        // $table->status=2;
        // $table->save();
        Session::flash('success','Deleted Successfully Done');
        echo "done";
    }
}
