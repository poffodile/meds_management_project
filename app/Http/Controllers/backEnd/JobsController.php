<?php

namespace App\Http\Controllers\backEnd;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session,DB,Validator;
use Carbon\Carbon;
// use App\Customer;
use App\Models\Customer;
use App\Models\Job;
use App\Models\Product;
use App\Models\Project;
use App\Models\Country;
use App\Models\Job_type;
use App\Models\Work_flow;
use App\Models\Job_title;
use App\Models\ProductGroup;
use App\Models\Job_recurring;
use App\Models\Product_category;
use App\Models\ProductCatalogue;
use App\Models\ProductGroupProduct;
use App\Models\Construction_tax_rate;
use App\Models\ProductCataloguePrice;
use App\Models\Workflow_notification;
use App\Models\Construction_account_code;
use App\Models\Construction_jobassign_product;
use App\Models\Construction_job_appointment_type;
use App\Models\Construction_product_supplier_list;
use App\Models\Construction_job_rejection_category;

class JobsController extends Controller
{
    public function jobs_list(Request $request){
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        if($home_id){
            $jobs_query=DB::table('jobs as job')
            ->select('job.*','service.id as service_id','service.home_id','service.name','service.user_name','service.phone_no','service.section','service.email','type.id as job_type_id','type.name as type_name','type.default_days')
            ->join('service_user as service','job.customer_id','service.id')
            ->join('job_types as type','job.job_type','type.id')
            ->whereNull('job.deleted_at');

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
                $jobs_query = $jobs_query->where('type.name','like','%'.$search.'%');
            }
            $jobs = $jobs_query->paginate($limit);
            $data['jobs']=$jobs;
            $data['limit']=$limit;
            $data['search']=$search;
            $data['page']='jobs_list';
            return view('backEnd.jobs_management.list',$data);
        }else {
            return redirect('admin/')->with('error',NO_HOME_ERR);
        }
        
    }
    public function job_status_change(Request $request){
        $id=base64_decode($request->id);
        $status=$request->status;
        $table=Job::find($id);
        $table->status=$status;
        $table->save();
        Session::flash('success','Status Change Successfully Done');
        echo "done";
    }
    public function job_delete(Request $request){
        $id=base64_decode($request->id);
        $delete= Job::where('id', $id)->update(['deleted_at' => Carbon::now()]);
        // $table=Job::find($id);
        // $table->status=2;
        // $table->save();
        Session::flash('success','Job Deleted Successfully Done');
        echo "done";
    }
    public function job_add(Request $request){
        $key=base64_decode($request->key);
        if($key){
            $task='Edit';
        }else{
            $task='Add';
        }
        $data['task']=$task;
        $data['page']='jobs_list';
        $data['del_status']=0;
        $data['projects']=Project::where('status',1)->get();
        $data['last_job_id']=Job::orderBy('id','DESC')->first();
        $data['job_details']=Job::find($key);
        $data['jobassign_products']=Construction_jobassign_product::where(['job_id'=>$key,'status'=>1])->get();
        $data['job_type']=Job_type::where('status',1)->get();
        $data['country']=Country::all_country_list();
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        $data['product_details1']=DB::table('products as pr')->select('pr.*','cat.id as cat_id','cat.name')->join('product_categories as cat','cat.id','=','pr.cat_id')->get();
        $data['customers']=Customer::get_customer_list_Attribute($home_id,'ACTIVE');
        // echo "<pre>";print_r($data['customers']);die;
        return view('backEnd.jobs_management.job_form',$data);
    }
    public function search_value(Request $request){
        $search_value = $request->search_value;
        $data = Product::where('product_name', 'like', "%{$search_value}%")->get();  
        foreach($data as $val){
            $cat_name=Product_category::find($val->cat_id);
            echo '<tr onclick="selectProduct(this)">
                            <td>'.$val->id.'</td>
                            <td>'.$cat_name->name.'</td>
                            <td>'.$val->product_name.'</td>
                            <td>'.$val->description.'</td>
                        </tr>';
        }
    }
    public function job_save_data(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        if ($request->hasFile('img_upload')) {
            $imageName = time().'.'.$request->img_upload->extension();      
            $request->img_upload->move(public_path('images/jobs'), $imageName);
        } else {
            $imageName=$request->old_image; 
        }
        if($request->last_job_id == ''){
            $job_ref="JOB-1";    
        }else {
            $job_ref="JOB-".$request->last_job_id+1;
        }
        if($request->id == ''){
            $table=new Job;
            $table->home_id=$home_id;
            $table->name=$request->name;
            $table->customer_id=$request->customer_id;
            $table->job_ref=$job_ref;
            $table->project_id=$request->project_id;
            $table->contact=$request->mobile;
            $table->telephone=$request->telephone;
            $table->email=$request->email;
            $table->short_decinc=$request->short_decinc;
            $table->description=$request->description;
            $table->address=$request->address;
            $table->city=$request->city;
            $table->country=$request->country;
            $table->pincode=$request->pincode;
            $table->site_id=$request->site_id;
            $table->region=$request->region;
            $table->company=$request->company_id;
            $table->conatact_name=$request->conatact_name;
            $table->site_email=$request->site_email;
            $table->site_telephone=$request->site_telephone;
            $table->site_mobile=$request->site_mobile;
            $table->site_address=$request->site_address;
            $table->site_city=$request->site_city;
            $table->site_country=$request->site_country;
            $table->site_pincode=$request->site_pincode;
            $table->notes=$request->notes;
            $table->customer_ref=$request->cust_ref;
            $table->purchase_order_ref=$request->order_ref;
            $table->cust_job_ref=$request->cust_job_ref;
            $table->job_type=$request->job_type;
            $table->priorty=$request->priority;
            $table->alert_customer=$request->alert_cust;
            $table->on_route_sms=$request->sms;
            $table->start_date=$request->start_date;
            $table->complete_by=$request->end_date;
            $table->tags=$request->tags;
            $table->customer_notes=$request->customer_notes;
            $table->internal_notes=$request->internal_notes;
            $table->attachments=$imageName;
            $table->site_country_id=$request->site_country_code;
            $table->country_id=$request->country_code;
            // echo "<pre>";print_r($table);die;
            $table->save();
            $product_detail_id=$request->product_detail_id;
            for($i=0;$i<count($product_detail_id);$i++){
                $table1=new Construction_jobassign_product;
                $table1->job_id=$table->id;
                $table1->product_id=$product_detail_id[$i];
                $table1->qty=$request->quantity[$i];
                $table1->save();
            }
            echo "done";
        }else {
            $table=Job::find($request->id);
            $table->home_id=$home_id;
            $table->name=$request->name;
            $table->customer_id=$request->customer_id;
            $table->job_ref=$job_ref;
            $table->project_id=$request->project_id;
            $table->contact=$request->mobile;
            $table->telephone=$request->telephone;
            $table->email=$request->email;
            $table->short_decinc=$request->short_decinc;
            $table->description=$request->description;
            $table->address=$request->address;
            $table->city=$request->city;
            $table->country=$request->country;
            $table->pincode=$request->pincode;
            $table->site_id=$request->site_id;
            $table->region=$request->region;
            $table->company=$request->company_id;
            $table->conatact_name=$request->conatact_name;
            $table->site_email=$request->site_email;
            $table->site_telephone=$request->site_telephone;
            $table->site_mobile=$request->site_mobile;
            $table->site_address=$request->site_address;
            $table->site_city=$request->site_city;
            $table->site_country=$request->site_country;
            $table->site_pincode=$request->site_pincode;
            $table->notes=$request->notes;
            $table->customer_ref=$request->cust_ref;
            $table->purchase_order_ref=$request->order_ref;
            $table->cust_job_ref=$request->cust_job_ref;
            $table->job_type=$request->job_type;
            $table->priorty=$request->priority;
            $table->alert_customer=$request->alert_cust;
            $table->on_route_sms=$request->sms;
            $table->start_date=$request->start_date;
            $table->complete_by=$request->end_date;
            $table->tags=$request->tags;
            $table->customer_notes=$request->customer_notes;
            $table->internal_notes=$request->internal_notes;
            $table->attachments=$imageName;
            $table->site_country_id=$request->site_country_id;
            $table->country_id=$request->country_code;
            $table->save();
            // echo "<pre>";print_r($table);die;
            $product_detail_id=$request->product_detail_id;
            if(isset($product_detail_id) && $product_detail_id !=''){
                for($i=0;$i<count($product_detail_id);$i++){
                    $table1=new Construction_jobassign_product;
                    $table1->job_id=$table->id;
                    $table1->product_id=$product_detail_id[$i];
                    $table1->qty=$request->quantity[$i];
                    $table1->save();
                }
            }
            echo "done";
        }

    }
    public function get_delete_jobproduct(Request $request){
        $id=$request->id;
        $delete= Construction_jobassign_product::where('id', $id)->update(['deleted_at' => Carbon::now()]);
        // $table=Construction_jobassign_product::find($id);
        // $table->status=2;
        // $table->save();
        // Session::flash('success','Deleted Successfully Done');
        echo "done";
    }
    public function jobs_type_list(Request $request){
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        if($home_id){
            $query=Job_type::whereNull('deleted_at');

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
            $jobs_type = $query->paginate($limit);
            $data['jobs_type']=$jobs_type;
            $data['limit']=$limit;
            $data['search']=$search;
            $data['page']='jobs_type_list';
            return view('backEnd.jobs_management.job_type_list',$data);
        }else {
            return redirect('admin/')->with('error',NO_HOME_ERR);
        }
        
    }
    public function job_type_status_change(Request $request){
        $id=base64_decode($request->id);
        $status=$request->status;
        $table=Job_type::find($id);
        $table->status=$status;
        $table->save();
        Session::flash('success','Status Change Successfully Done');
        echo "done";
    }
    public function job_type_delete(Request $request){
        $id=base64_decode($request->id);
        $delete= Job_type::where('id', $id)->update(['deleted_at' => Carbon::now()]);
        // $table=Job_type::find($id);
        // $table->status=2;
        // $table->save();
        Session::flash('success','Deleted Successfully Done');
        echo "done";
    }
    public function job_type_add(Request $request){
        $key=base64_decode($request->key);
        if($key){
            $task='Edit';
        }else{
            $task='Add';
        }
        $data['job_type']=Job_type::find($key);
        $data['task']=$task;
        $data['page']='jobs_type_list';
        $data['del_status']=0;
        $data['appointement_type']=Construction_job_appointment_type::whereNull('deleted_at')->where('status',1)->get();
        return view('backEnd.jobs_management.job_type_form',$data);
    }
    public function job_type_save_data(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        $name=$request->name;
        $days=$request->days;
        if($request->visible == ''){
            $visible=0;
        }else {
            $visible=1;
        }
        $appointment=$request->appointment;
        if($request->id == ''){
            $table=new Job_type;
            $table->home_id=$home_id;
            $table->name=$name;
            $table->default_days=$days;
            $table->customer_visible=$visible;
            $table->appointment_id=$appointment;
            $table->save();
            Session::flash('success','Added Successfully Done');
            return redirect('admin/jobs_type_list');
        }else {
            $table=Job_type::find($request->id);
            $table->home_id=$home_id;
            $table->name=$name;
            $table->default_days=$days;
            $table->customer_visible=$visible;
            $table->appointment_id=$appointment;
            $table->save();
            Session::flash('success','Updated Successfully Done');
            return redirect('admin/jobs_type_list');
        }
        
    }
    public function work_flow_list(Request $request){
        // echo 1;
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        if($home_id){
            $query=Work_flow::whereNull('deleted_at');

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
                $query = $query->where('flow_name','like','%'.$search.'%');
            }
            $flow = $query->paginate($limit);
            $data['flow']=$flow;
            $data['limit']=$limit;
            $data['search']=$search;
            $data['page']='work_flow_list';
            return view('backEnd.jobs_management.work_flow_list',$data);
        }else {
            return redirect('admin/')->with('error',NO_HOME_ERR);
        }
        
    }
    public function wrok_flow_status_change(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $id=base64_decode($request->id);
        $status=$request->status;
        $table=Work_flow::find($id);
        $table->status=$status;
        $table->save();
        Session::flash('success','Status Change Successfully Done');
        echo "done";
    }
    public function wrok_flow_delete(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $id=base64_decode($request->id);
        $delete= Work_flow::where('id', $id)->update(['deleted_at' => Carbon::now()]);
        // $table=Work_flow::find($id);
        // $table->status=2;
        // $table->save();
        Session::flash('success','Deleted Change Successfully Done');
        echo "done";
    }
    public function work_flow_add(Request $request){
        $key=base64_decode($request->key);
        if($key){
            $task='Edit';
        }else{
            $task='Add';
        }
        $data['flow']=Work_flow::find($key);
        $data['task']=$task;
        $data['page']='work_flow_list';
        $data['del_status']=0;
        return view('backEnd.jobs_management.work_flow_form',$data);
    }
    public function workflow_save_data(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        $job_type_id=$request->job_type_id;
        $appointment_id=$request->appointment_id;
        // echo "<pre>";print_r($appointment_id);die;
        $row_count=$request->row_count;
        // $name=$request->name;
        // if($request->id == ''){
        //     $table=new Work_flow;
        //     $table->flow_name=$namw;
        //     $table->save();
        //     Session::flash('success','Added Change Successfully Done');
        //     return redirect('admin/work_flow_list');
        // }else {
        //     $table=Work_flow::find($request->id);
        //     $table->flow_name=$namw;
        //     $table->save();
        //     Session::flash('success','Updated Change Successfully Done');
        //     return redirect('admin/work_flow_list');
        // }
        for($i=0;$i<count($appointment_id);$i++){
            $table=new Work_flow;
            $table->home_id=$home_id;
            $table->job_type_id=$job_type_id;
            $table->appointment_id=$appointment_id[$i];
            $table->save();
            
        }
        Session::flash('success','Added Successfully Done');
        return redirect('admin/jobs_type_list');
    }
    // Workflow_notification
    public function Workflow_notification_save(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $job_type_id_noti=$request->job_type_id_noti;
        $row_id_noti=$request->row_id_noti;
        $emailsend=$request->emailsend;
        $om_complete=$request->om_complete;
        $om_change=$request->om_change;
        $sms=$request->sms;
        $om_complete_noti=$request->om_complete_noti;
        $who_noti=$request->who_noti[0];
        // echo "<pre>";print_r(count($who_noti));die;
        $count=0;
        for($i=0;$i < count($who_noti);$i++){
            $table=new Workflow_notification;
            $table->job_type_id=$job_type_id_noti;
            $table->row_id=$row_id_noti;
            $table->notify_when_on_complete=$om_complete;
            $table->notify_when_on_change=$om_change;
            $table->notify_who=$who_noti[$i];
            $table->notify_customer_on_complete=$om_complete_noti;
            $table->sendas=$emailsend;
            $table->sms=$sms;
            $table->save();
            $count++;
        }
        // echo $count;
        if($count == count($who_noti)){
            echo "done";
        }else {
            echo "error";
        }
        
    }
    public function product_category(Request $request){
        // echo 1 ;die;
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        if($home_id){
            $query = Product_category::with('parent', 'children')->whereNull('deleted_at')->orderBy('id', 'ASC');
            $search = '';
    
            if (isset($request->limit)) {
                $limit = $request->limit;
                Session::put('page_record_limit', $limit);
            } else {
                if (Session::has('page_record_limit')) {
                    $limit = Session::get('page_record_limit');
                } else {
                    $limit = 20;
                }
            }
    
            if (isset($request->search)) {
                $search = trim($request->search);
                $query = $query->where('name', 'like', '%' . $search . '%');
            }
    
            // $cat = $query->paginate($limit);
            $cat = $query->paginate($limit);
            $data['cat']=$cat;
            // echo "<pre>";print_r($data['cat']);die;
            // $data['category']=Product_category::where('status',1)->get();
            // $product_category=Product_category::all();
            // echo "<pre>";print_r($product_category);die;
            $data['limit']=$limit;
            $data['search']=$search;
            $data['page']='product_category';
            return view('backEnd.jobs_management.product_category_list',$data);
        }else {
            return redirect('admin/')->with('error',NO_HOME_ERR);
        }
        
    }
    public function product_cat_status_change(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $id=base64_decode($request->id);
        $status=$request->status;
        $table=Product_category::find($id);
        $table->status=$status;
        $table->save();
        Session::flash('success','Status Change Successfully Done');
        echo "done";
    }
    public function product_cat_delete(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $id=base64_decode($request->id);
        $delete= Product_category::where('id', $id)->update(['deleted_at' => Carbon::now()]);
        // $table=Product_category::find($id);
        // $table->status=2;
        // $table->save();
        Session::flash('success','Deleted Successfully Done');
        echo "done";
    }
    public function product_category_add(Request $request){
        $key=base64_decode($request->key);
        if($key){
            $task='Edit';
        }else{
            $task='Add';
        }
        $data['cat']=Product_category::find($key);
        $data['category']=Product_category::with('parent', 'children')->where('status',1)->whereNull('deleted_at')->get();
        $data['task']=$task;
        $data['page']='product_category';
        $data['del_status']=0;
        return view('backEnd.jobs_management.product_category_form',$data);
    }
    public function product_cat_save_data(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->first()]);
        }
        if(Product_category::checkproductcategoryname($request->name,$request->productCategoryID)==0){
            try{
                Product_category::saveProductCategoryData($request->all(), $request->productCategoryID);
                $Data=Product_category::with('parent', 'children')->where('status',1)->whereNull('deleted_at')->get();
                if($request->productCategoryID){
                    return response()->json(['success'=>true,'message'=>'Product Category Updated Successfully Done.','data'=>$Data]);
                }else{
                    return response()->json(['success'=>true,'message'=>'Product Category Added Successfully Done.','data'=>$Data]);
                }
                
            }catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
        }else{
            return response()->json([
                'success' => false,
                'errors' => 'This Product category already exist.',
                'lastid' => 0
            ]);
        }
    }
    public function product_list(Request $request){
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        if($home_id){
            $query=Product::whereNull('deleted_at');

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
                $query = $query->where('product_name','like','%'.$search.'%');
            }
            $product = $query->paginate($limit);
            $data['product']=$product;
            $data['limit']=$limit;
            $data['search']=$search;
            $data['page']='product_list';
            return view('backEnd.jobs_management.product_list',$data);
        }else {
            return redirect('admin/')->with('error',NO_HOME_ERR);
        }
        
    }
    public function product_status_change(Request $request){
        $id=base64_decode($request->id);
        $status=$request->status;
        $table=Product::find($id);
        $table->status=$status;
        $table->save();
        Session::flash('success','Status Change Successfully Done');
        echo "done";
    }
    public function product_delete(Request $request){
        $id=base64_decode($request->id);
        $delete= Product::where('id', $id)->update(['deleted_at' => Carbon::now()]);
        Session::flash('success','Deleted Successfully Done');
        echo "done";  
    }
    public function account_codes(Request $request){
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        if($home_id){
            $query=Construction_account_code::whereNull('deleted_at');

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
            $acc_code = $query->paginate($limit);
            $data['acc_code']=$acc_code;
            $data['limit']=$limit;
            $data['search']=$search;
            $data['page']='account_codes';
            return view('backEnd.jobs_management.account_list',$data);
        }else {
            return redirect('admin/')->with('error',NO_HOME_ERR);
        }
        
    }
    public function account_code_add(Request $request){
        $key=base64_decode($request->key);
        if($key){
            $task='Edit';
        }else{
            $task='Add';
        }
        $data['account']=Construction_account_code::find($key);
        $data['task']=$task;
        $data['page']='account_codes';
        $data['del_status']=0;
        return view('backEnd.jobs_management.account_form',$data);
    }
    public function account_save_data(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        $name=$request->name;
        $code=$request->code;
        if($request->id == ''){
            $table=new Construction_account_code;
            $table->home_id=$home_id;
            $table->name=$name;
            $table->departmental_code=$code;
            $table->save();
            Session::flash('success','Added Successfully Done');
            echo "done";
        }else {
            $table=Construction_account_code::find($request->id);
            $table->home_id=$home_id;
            $table->name=$name;
            $table->departmental_code=$code;
            $table->save();
            Session::flash('success','Updated Successfully Done');
            echo "done";
        }
    }
    public function account_status_change(Request $request){
        $id=base64_decode($request->id);
        $status=$request->status;
        $table=Construction_account_code::find($id);
        $table->status=$status;
        $table->save();
        Session::flash('success','Status Change Successfully Done');
        echo "done";
    }
    public function account_delete(Request $request){
        $id=base64_decode($request->id);
        $delete= Construction_account_code::where('id', $id)->update(['deleted_at' => Carbon::now()]);
        // $table=Construction_account_code::find($id);
        // $table->status=2;
        // $table->save();
        Session::flash('success','Deleted Successfully Done');
        echo "done";
    }
    public function tax_rate(Request $request){
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        if($home_id){
            $query=Construction_tax_rate::whereNull('deleted_at');

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
            $tax = $query->paginate($limit);
            $data['tax']=$tax;
            $data['limit']=$limit;
            $data['search']=$search;
            $data['page']='tax_rate';
            return view('backEnd.jobs_management.tax_list',$data);
        }else {
            return redirect('admin/')->with('error',NO_HOME_ERR);
        }
        
    }
    public function tax_add(Request $request){
        $key=base64_decode($request->key);
        if($key){
            $task='Edit';
        }else{
            $task='Add';
        }
        $data['tax']=Construction_tax_rate::find($key);
        $data['task']=$task;
        $data['page']='tax_rate';
        $data['del_status']=0;
        return view('backEnd.jobs_management.tax_form',$data);
    }
    public function tax_save_data(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'tax_rate' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->first()]);
        }
        if(Construction_tax_rate::checkTaxRatename($request->name,$request->taxrateID)==0){
            try{
                $saveData = Construction_tax_rate::saveTaxRateData($request->all(), $request->taxrateID);
              
                if($request->taxrateID){
                    return response()->json(['success'=>true,'message'=>'Tax rate Updated Successfully Done.','data'=>$saveData]);
                }else{
                    return response()->json(['success'=>true,'message'=>'Tax Rate Added Successfully Done.','data'=>$saveData]);
                }
                
            }catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
        }else{
            return response()->json([
                'success' => false,
                'errors' => 'This Tax Rate already exist.',
                'lastid' => 0
            ]);
        }
    }
    public function tax_status_change(Request $request){
        $id=base64_decode($request->id);
        $status=$request->status;
        $table=Construction_tax_rate::find($id);
        $table->status=$status;
        $table->save();
        Session::flash('success','Status Change Successfully Done');
        echo "done";
    }
    public function tax_delete(Request $request){
        $id=base64_decode($request->id);
        $delete= Construction_tax_rate::where('id', $id)->update(['deleted_at' => Carbon::now()]);
        // $table=Construction_tax_rate::find($id);
        // $table->status=2;
        // $table->save();
        Session::flash('success','Deleted Successfully Done');
        echo "done";
    }
    public function product_add(Request $request){
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        $key=base64_decode($request->key);
        if($key){
            $task='Edit';
        }else{
            $task='Add';
        }
        $data['product']=Product::find($key);
        // $data['product_category']=Product_category::where('status',1)->get();
        $data['product_category']=Product_category::with('parent', 'children')->where('status',1)->whereNull('deleted_at')->get();
        // echo "<pre>";print_r($data['product_category']);die;
        $data['tax']=Construction_tax_rate::where(['deleted_at'=>null,'status'=>1])->get();
        $data['acc_code']=Construction_account_code::where(['deleted_at'=>null,'status'=>1])->get();
        $data['supplier']=Construction_product_supplier_list::where('product_id',$key)->get();
        // echo "<pre>";print_r($data['supplier']);die;
        $data['task']=$task;
        $data['page']='product_list';
        $data['del_status']=0;
        $data['product_count']=Product::count();
        $data['data']=['Ram','Deena','Harsh'];
        $data['customer']=Customer::get_customer_list_Attribute($home_id, 'ACTIVE');
        // echo "<pre>";print_r($data['customer']);die;
        return view('backEnd.jobs_management.product_form',$data);
    }
    public function product_save_data(Request $request){
        // echo "<pre>";print_r($request->all());die;
        
        $validator = Validator::make($request->all(), [
            'product_name' => 'required',
            'price' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->first()]);
        }
        if ($request->hasFile('attachment')) {
            $imageName = time().'.'.$request->attachment->extension();      
            $request->attachment->move(public_path('images/jobs'), $imageName);
        } else {
            $imageName=$request->old_image; 
        }
        $requestData=$request->all();
        $requestData['attachment']=$imageName ?? '';
        $requestData['cat_id']=$request->product_category ?? '';
        $requestData['show_temp'] = $request->show_temp === 'on' ? 1 : 0;
        $requestData['customer_only'] = $request->Customer_id;
        $requestData['margin'] = $request->markup;
        $supplier_id=$request->supplier_id;
        $part_number=$request->part_number;
        $cost_price_supplier=$request->cost_price_supplierl;
        try{
            $saveData = Product::saveProductdata($requestData, $request->productID);
            for($i=0;$i<count($supplier_id);$i++){
                $data=[
                    'id'=>$request->product_supplier_list_id[$i] ?? null,
                    'product_id'=>$saveData->id,
                    'supplier_id'=>$supplier_id[$i],
                    'part_number'=>$request->part_number[$i],
                    'cost_price_supplier'=>$request->cost_price_supplier[$i]
                ];
                $productsupplier_table=Construction_product_supplier_list::saveProductSupplierList($data);
            }
          
            if($request->productID){
                return response()->json(['success'=>true,'message'=>'Product Updated Successfully Done.','data'=>$saveData]);
            }else{
                return response()->json(['success'=>true,'message'=>'Product Added Successfully Done.','data'=>$saveData]);
            }
            
        }catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    public function supplier_result(Request $request){
        $data=['Ram','Deena','Harsh'];
        $res= '<tr>
                <td>
                    <select id="supplier_id" name="supplier_id[]" class="form-control">
                        <option selected disabled>Select Supplier</option>';
                    $inc=1;
                    for($i=0;$i<count($data);$i++){
                       $res.='<option value="'.$inc.'">'.$data[$i].'</option>'; 
                       $inc++;
                    }
                   $res.='</select>
                </td>
                <td><input type="text" id="part_number" name="part_number[]"></td>
                <td><span class="currency">$</span><input type="text" id="cost_price_supplier" name="cost_price_supplier[]"></td>
                <td class="delete_row">X</td>
            </tr>';
        echo $res;
    }
    public function catalogue(Request $request){
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        if($home_id){
            $query = ProductCatalogue::withCount('productCataloguePrices')->where(['home_id' => $home_id,'deleted_at'=>null])->orderBy('id','DESC');

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
                $query = $query->where('project_name','like','%'.$search.'%');
            }
            $catalogue = $query->paginate($limit);
            $data['catalogue']=$catalogue;
            $data['limit']=$limit;
            $data['search']=$search;
            $data['page']='catalogue';
            return view('backEnd.jobs_management.catalogue',$data);
        }else {
            return redirect('admin/')->with('error',NO_HOME_ERR);
        }
    }
    public function save_catalogue(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->first()]);
        }
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        if($request->TabId == 0){
            $catlogueTable=['id'=>$request->id ?? null,'home_id'=>$home_id,'user_id'=>$admin->id,'name'=>$request->name,'description'=>$request->description,'catalogue_type'=>$request->catalogue_type,'status'=>$request->status];
            // echo "<pre>";print_r($catlogueTable);die;
            try {
                $catlogueSave=ProductCatalogue::CatalogueSave($catlogueTable);
                // $catlogueSave=['id'=>1];
                if($request->id){
                    return response()->json(['success' => true,'message'=>'Catalogue Updated Successfully Done', 'data' => $catlogueSave]);
                }else{
                    return response()->json(['success' => true,'message'=>'Catalogue Added Successfully Done', 'data' => $catlogueSave]);
                }
                
            }catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }
        }else{
            // echo count($request->tableData);die;
            $cataloguePriceResults = [];
            foreach($request->tableData as $val){
                 $cataloguePriceTable=[
                        'id'=>$val['id'] ?? null,
                        'product_catalogue_id'=>$request->catalogue_id,
                        'product_id'=>$val['product_id'],
                        'product_code'=>$val['product_code'],
                        'product_name'=>$val['product_name'],
                        'default_price'=>$val['price'],
                        'catalogue_price'=>$val['custom_price'],
                        'product_type'=>$val['product_type'],
                        'status'=>1
                    ];
                try {
                        $cataloguePriceSave=ProductCataloguePrice::productCatalogueSave($cataloguePriceTable);
                        $cataloguePriceResults[] = $cataloguePriceSave;
                }catch (\Exception $e) {
                    return response()->json(['success' => false, 'message' => $e->getMessage()]);
                }
                
            }
            return response()->json(['success' => true, 'data' => $cataloguePriceResults]);
            
        }
    }
    public function getCategoryList(Request $request){
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        $data = Product_category::getProductCategory($home_id);
        
        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? $data : 'No data.'
        ]);
    }
    public function getProduct_List(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        if($request->type == 4){
            $data = ProductGroup::getProductGroupData($home_id);   
        }else{
            $data = Product::getProductList($request->type);
        }

        return response()->json([
            'success' => (bool) $data,
            'type'=>$request->type ?? null,
            'data' => $data ? $data : 'No data.'
        ]);
    }
    public function getProductListCounts(Request $request){
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        $data['product'] = Product::getProductListCountType(1);
        $data['service'] = Product::getProductListCountType(2);
        $data['consumable'] = Product::getProductListCountType(3);
        $product_group= ProductGroup::getProductGroupData($home_id); 
         $data['product_group']=count($product_group);
        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? $data : 'No data.'
        ]);
    }
    public function getProductSelectId(Request $request){
        $data =  Product::getProductFromId($request->id);
        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? $data : 'No data.'
        ]);
    }
    public function ProductCataloguePriceList(Request $request){
        $catalogues = ProductCataloguePrice::where(['product_catalogue_id'=>$request->cat_id,'status'=>1,'deleted_at'=>null])->get();
        return response()->json(['data'=>$catalogues]);
    }
    public function ProductGroupProductsdetail(Request $request){
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        $ProductGroupProduct=ProductGroup::with('productGroupProduct')->where('id',$request->id)->get();
        return response()->json(['data'=>$ProductGroupProduct]);
    }
    public function product_group(Request $request){
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        if($home_id){
            $query = ProductGroup::where('home_id',$home_id)->whereNull('deleted_at')->orderBy('id','DESC');

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
                $query = $query->where('project_name','like','%'.$search.'%');
            }
            $product_group = $query->paginate($limit);
            $data['product_group']=$product_group;
            $data['limit']=$limit;
            $data['search']=$search;
            $data['page']='product_group';
            return view('backEnd.jobs_management.productGroup',$data);
        }else {
            return redirect('admin/')->with('error',NO_HOME_ERR);
        }
    }
    public function save_productGroup(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->first()]);
        }
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        $productsData = json_decode($request->input('products'), true);
        $formData=[
            'id'=>$request->id ?? null,
            'name'=>$request->name,
            'description'=>$request->description,
            'code'=>$request->code,
            'status'=>$request->status,
            'cost'=>$request->cost,
            'price'=>$request->price
        ];
        try {
            $saveData = ProductGroup::saveProductGroup($formData, $home_id, 0);
            // $catlogueSave=['id'=>1];
            if (!empty($productsData['products']) && is_array($productsData['products'])) {
                $saveProduct = ProductGroupProduct::saveProductGroupData($saveData->id, $productsData);
                if($request->id){
                    return response()->json([
                        'success' => true,
                        'message' =>'Product group updated successfully.',
                    ]);
                }else{
                    return response()->json([
                        'success' => (bool) $saveProduct,
                        'message' => $saveProduct ? 'Product group and products added successfully.' : 'Product Group products could not be added.',
                    ]);
                }
                
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Product group added successfully (no products provided).',
                ]);
            } 
        }catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    public function ProductGroupProductsList(Request $request){
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        $ProductGroupProduct=ProductGroupProduct::getProductGroupProductData($home_id)->where('product_group_id',$request->id)->get();
        return response()->json(['data'=>$ProductGroupProduct]);
    }
    public function customer_list(Request $request){
        echo 1;die;
    }
    public function project_list(Request $request){
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        if($home_id){
            $query=Project::whereNull('deleted_at')->orderBy('id','DESC');

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
                $query = $query->where('project_name','like','%'.$search.'%');
            }
            $project = $query->paginate($limit);
            $data['project']=$project;
            $data['limit']=$limit;
            $data['search']=$search;
            $data['page']='project_list';
            return view('backEnd.jobs_management.project_list',$data);
        }else {
            return redirect('admin/')->with('error',NO_HOME_ERR);
        }
        
    }
    public function project_add(Request $request){
        $key=base64_decode($request->key);
        if($key){
            $task='Edit';
        }else{
            $task='Add';
        }
        $data['project']=Project::find($key);
        $data['project_count']=Project::count();
        $data['task']=$task;
        $data['page']='project_list';
        $data['del_status']=0;
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        $data['customers']=Customer::get_customer_list_Attribute($home_id,'ACTIVE');
        return view('backEnd.jobs_management.project_form',$data);
    }
    public function project_save_data(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        if($request->id == ''){
            $project_ref="PRO-000";
            if($request->project_count > 9){
                $project_ref="PRO-00";
            }else if($request->project_count > 99){
                $project_ref="PRO-0";
            }else if($request->project_count > 999){
                $project_ref="PRO-";
            }
            $table=new Project;
            $table->home_id=$home_id;
            $table->project_name=$request->name;
            $table->project_ref=$project_ref.$request->project_count;
            $table->customer_name=$request->Customer_id;
            $table->start_date=$request->start_date;
            $table->end_date=$request->end_date;
            $table->project_value=$request->project_value;
            $table->description=$request->description;
            $table->catalogue_id=$request->catalogue_id;
            $table->save();
            Session::flash('success','Added Successfully Done');
            echo "done";
        }else {
            $table=Project::find($request->id);
            $table->home_id=$home_id;
            $table->project_name=$request->name;
            $table->project_ref=$request->project_count;
            $table->customer_name=$request->Customer_id;
            $table->start_date=$request->start_date;
            $table->end_date=$request->end_date;
            $table->project_value=$request->project_value;
            $table->description=$request->description;
            $table->catalogue_id=$request->catalogue_id;
            $table->save();
            Session::flash('success','Updated Successfully Done');
            echo "done";
        }
    }
    public function project_status_change(Request $request){
        $id=base64_decode($request->id);
        $status=$request->status;
        $table=Project::find($id);
        $table->status=$status;
        $table->save();
        Session::flash('success','Status Change Successfully Done');
        echo "done";
    }
    public function project_delete(Request $request){
        $id=base64_decode($request->id);
        $delete= Project::where('id', $id)->update(['deleted_at' => Carbon::now()]);
        // $table=Project::find($id);
        // $table->status=2;
        // $table->save();
        Session::flash('success','Deleted Successfully Done');
        echo "done";
    }
    public function job_recurring_list(Request $request){
        // echo 1;die;
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        if($home_id){
            $query=Job_recurring::whereNull('deleted_at')->orderBy('id','DESC');

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
                $query = $query->where('project_name','like','%'.$search.'%');
            }
            $recurring = $query->paginate($limit);
            $data['recurring']=$recurring;
            $data['limit']=$limit;
            $data['search']=$search;
            $data['page']='project_list';
            return view('backEnd.jobs_management.recurring_job_list',$data);
        }else {
            return redirect('admin/')->with('error',NO_HOME_ERR);
        }
        
    }
    public function job_appointment_type(Request $request){
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        if($home_id){
            $query=Construction_job_appointment_type::whereNull('deleted_at');

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
            $appointtype = $query->paginate($limit);
            
            $data['appointtype']=$appointtype;
            $data['limit']=$limit;
            $data['search']=$search;
            $data['page']='job_appointment_type';
            return view('backEnd.jobs_management.job_appointment_type',$data);
        }else {
            return redirect('admin/')->with('error',NO_HOME_ERR);
        }
    }
    public function job_appointment_type_add(Request $request){
        $key=base64_decode($request->key);
        if($key){
            $task='Edit';
        }else{
            $task='Add';
        }
        $data['appointmenttype']=Construction_job_appointment_type::find($key);
        $data['task']=$task;
        $data['page']='job_appointment_type';
        $data['del_status']=0;
        return view('backEnd.jobs_management.job_appointment_type_form',$data);
    }
    public function job_appointment_type_save(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        if($request->id == ''){
            $table=new Construction_job_appointment_type;
            $table->home_id=$home_id;
            $table->name=$request->name;
            $table->hours=$request->hours;
            $table->minute=$request->minutes;
            $table->auth=$request->auth;
            $table->save();
            Session::flash('success','Added Successfully Done');
        }else {
            $table=Construction_job_appointment_type::find($request->id);
            $table->home_id=$home_id;
            $table->name=$request->name;
            $table->hours=$request->hours;
            $table->minute=$request->minutes;
            $table->auth=$request->auth;
            $table->save();
            Session::flash('success','Updated Successfully Done');
        }
        echo "done";
    }
    public function job_appointment_type_status_change(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $id=base64_decode($request->id);
        $status=$request->status;
        $table=Construction_job_appointment_type::find($id);
        $table->status=$status;
        $table->save();
        Session::flash('success','Status Change Successfully Done');
        echo "done";
    }
    public function job_appointment_type_delete(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $id=base64_decode($request->id);
        $delete= Construction_job_appointment_type::where('id', $id)->update(['deleted_at' => Carbon::now()]);
        // $table=Construction_job_appointment_type::find($id);
        // $table->status=2;
        // $table->save();
        Session::flash('success','Deleted Successfully Done');
        echo "done";
    }
    public function job_rejection_categories(Request $request){
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        if($home_id){
            $query=Construction_job_rejection_category::whereNull('deleted_at')->orderBy('id','DESC');

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
            $rejectionCat = $query->paginate($limit);
            
            $data['rejectionCat']=$rejectionCat;
            $data['limit']=$limit;
            $data['search']=$search;
            $data['page']='job_rejection_categories';
            return view('backEnd.jobs_management.job_rejection_categories',$data);
        }else {
            return redirect('admin/')->with('error',NO_HOME_ERR);
        }
    }
    public function job_rejection_category_add(Request $request){
        $key=base64_decode($request->key);
        if($key){
            $task='Edit';
        }else{
            $task='Add';
        }
        $data['rejection']=Construction_job_rejection_category::find($key);
        $data['task']=$task;
        $data['page']='job_appointment_type';
        $data['del_status']=0;
        return view('backEnd.jobs_management.job_rejection_categories_form',$data);
    }
    public function job_rejection_category_save(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        if($request->id == ''){
            $table=new Construction_job_rejection_category;
            $table->home_id=$home_id;
            $table->appointment_id=$request->status;
            $table->name=$request->name;
            $table->save();
            Session::flash('success','Added Successfully Done');
        }else {
            $table=Construction_job_rejection_category::find($request->id);
            $table->home_id=$home_id;
            $table->appointment_id=$request->status;
            $table->name=$request->name;
            $table->save();
            Session::flash('success','Updated Successfully Done');
        }
        echo "done";
    }
    public function job_rejection_category_status_change(Request $request){
        $id=base64_decode($request->id);
        $status=$request->status;
        $table=Construction_job_rejection_category::find($id);
        $table->status=$status;
        $table->save();
        Session::flash('success','Status Change Successfully Done');
        echo "done";
    }
    public function job_rejection_category_delete(Request $request){
        $id=base64_decode($request->id);
        $delete= Construction_job_rejection_category::where('id', $id)->update(['deleted_at' => Carbon::now()]);
        // $table=Construction_job_rejection_category::find($id);
        // $table->status=2;
        // $table->save();
        Session::flash('success','Deleted Successfully Done');
        echo "done";
    }
    
    public function job_title(Request $request){
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        if($home_id){
            $query=Job_title::whereNull('deleted_at')->orderBy('id','DESC');

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
            $job_title = $query->paginate($limit);
            $data['job_title']=$job_title;
            $data['limit']=$limit;
            $data['search']=$search;
            $data['page']='job_title';
            return view('backEnd.jobs_management.job_title',$data);
        }else {
            return redirect('admin/')->with('error',NO_HOME_ERR);
        }
    }
    public function job_title_add(Request $request){
        $key=base64_decode($request->key);
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        if($key){
            $task='Edit';
        }else{
            $task='Add';
        }
        $data['type']=Job_title::find($key);
        $data['task']=$task;
        $data['page']='job_title';
        $data['del_status']=0;
        $data['home_id']=$home_id;
        return view('backEnd.jobs_management.Job_title_form',$data);
    }
    public function job_title_save(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $insert=Job_title::updateOrCreate(['id' => $request->id], $request->all());
        if(isset($request->id)){
            Session::flash('success','Updated Successfully Done');
        } else {
            Session::flash('success','Added Successfully Done');
        }
        
        if($insert->status ==1){
            echo '<option value="'.$insert->id.'">'.$insert->name.'</option>';
        }else{
            echo "error";
        }
    }
    public function job_title_status_change(Request $request){
        $id=base64_decode($request->id);
        $status=$request->status;
        $table=Job_title::find($id);
        $table->status=$status;
        $table->save();
        Session::flash('success','Status Change Successfully Done');
        echo "done";
    }
    public function job_title_delete(Request $request){
        $id=base64_decode($request->id);
        $delete= Job_title::where('id', $id)->update(['deleted_at' => Carbon::now()]);
        // $table=Job_title::find($id);
        // $table->status=2;
        // $table->save();
        Session::flash('success','Deleted Successfully Done');
        echo "done";
    }
    public function get_customer_details(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $customer_id=$request->customer_id;
        $customers = Customer::with('sites','additional_contact','customer_project')->where('id', $customer_id)->get();
        // echo "<pre>";print_r($customers);die;
        return response()->json($customers);
    }
    
}
