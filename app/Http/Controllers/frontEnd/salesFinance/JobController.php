<?php

namespace App\Http\Controllers\frontEnd\salesFinance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\User;
use App\Models\Customer;
use App\ServiceUser;
use App\Models\Job;
use App\Models\Quote;
use App\Models\Product;
use App\Models\Project;
use App\Models\Country;
use App\Models\Job_type;
use App\Models\Work_flow;
use App\Models\QuoteType;
use App\Models\Job_title;
use App\Models\Job_recurring;
use App\Models\Customer_type;
use App\Models\Product_category;
// use App\Models\Constructor_region;
use App\Models\Quote_product_detail;
use App\Models\Workflow_notification;
use App\Models\Construction_tax_rate;
use App\Models\Recurrence_pattern_rule;
use App\Models\Recurring_product_detail;
use App\Models\Constructor_customer_site;
use App\Models\Construction_account_code;
use App\Models\Construction_job_appointment;
use App\Models\Construction_jobassign_product;
use App\Models\Constructor_additional_contact;
use App\Models\Construction_job_appointment_type;
use App\Models\Construction_product_supplier_list;
use App\Models\construction_appointment_rejection_category;
use App\Models\Region;
// use App\Models\LogHistory;
use App\Models\Tag;
use DB,Session,Validator;
use Illuminate\Support\Facades\Auth;

class JobController extends Controller
{
    public function index(){
        $data['header_title']="Active Jobs";
        $data['job_type']=Job_type::where('status',1)->get();
        $data['ServiceUser']=ServiceUser::where('status',1)->get();
        $data['job']=Job::where('status',1)->get();
        $data['work_flow']=Work_flow::where('status',1)->get();
        $data['category']=Product_category::where('status',1)->get();
        $data['all_product']=Product::where('status',1)->get();
        $data['quote']=Quote::where('status',1)->get();
        $data['project']=Project::where('status',1)->get();
        $data['product_details1']=DB::table('products as pr')->select('pr.*','cat.id as cat_id','cat.name')->join('product_categories as cat','cat.id','=','pr.cat_id')->get();
        $data['job_recurring']=Job_recurring::where('status',1)->get();
        $data['quote_type']=QuoteType::where('status',1)->get();
        // echo "<pre>";print_r($data['product_details']);die;
        // for listing
        $data['job_type_list']=Job_type::whereNot('status',2)->get();
        $data['work_flow_list']=Work_flow::whereNot('status',2)->get();
        $data['category_list']=Product_category::whereNot('status',2)->get();
        $data['product_list']=Product::whereNot('status',2)->get();
        $data['quote_list']=Quote::whereNot('status',2)->get();
        $data['project_list']=Project::whereNot('status',2)->get();
        $data['job_recurring_list']=Job_recurring::whereNot('status',2)->get();
        $data['job_list']=Job::whereNot('status',2)->get();
        $data['quote_type_list']=QuoteType::whereNot('status',2)->get();
        $data['page']="job_index";
        return view('frontEnd.salesAndFinance.jobs.index',$data);
    }
    public function job_list(Request $request){
        $lastSegment = request()->segment(request()->segments() ? count(request()->segments()) : 1);
        // echo $lastSegment;die;
        $home_id = Auth::user()->home_id;
        $job=Job::getAllJob($home_id)->where('user_id',Auth::user()->id)->get();
        // echo "<pre>";print_r($job);die;
        $data['access_rights']=$this->access_rights();
        $data_arr=array();
        foreach($job as $val){
            $customer_name=Customer::where('id',$val->customer_id)->first();
            $job_type_detail=Job_type::where('id',$val->job_type)->first(); 
            // $product_details=Product::where('id',$val->product_id)->first(); 
            if($val->site_id == 'default'){
                $site=Customer::where('id',$val->customer_id)->first();
            }else{
                $site=Constructor_customer_site::where('id',$val->site_id)->first();
            }
            $customers = Customer::with('sites','additional_contact','customer_project')->where('id', $val->customer_id)->first();
            $data_arr[]=[
                'id'=>$val->id,
                'job_ref'=>$val->job_ref,
                'customer_name'=>$customer_name->name,
                'job_type'=>$job_type_detail->name,
                'site'=>$site->site_name ?? $site->address,
                'short_decinc'=>$val->short_decinc,
                'complete_by'=>$val->complete_by
            ];
        }
        // echo "<pre>";print_r($data_arr);die;
        $data['lastSegment']=$lastSegment;
        $data['job']=$data_arr;
        $data['page']="jobs";
        // echo "<pre>";print_r($data['job']);die;
        return view('frontEnd.salesAndFinance.jobs.job',$data);
    }
    public function job_type(Request $request){
        $home_id = Auth::user()->home_id;
        $data['job_type']=Job_type::whereNull('deleted_at')->where('home_id',$home_id)->get();
        $data['access_rights']=$this->access_rights();
        $data['appointment_type']=Construction_job_appointment_type::where('home_id',$home_id)->get();
        $data['home_id']=$home_id;
        $data['customers']=Customer::get_customer_list_Attribute($home_id,'ACTIVE');
        $data['page']='setting';

        // echo "<pre>";print_r($data['workflow']);die;
        return view('frontEnd.salesAndFinance.jobs.job_type',$data);
    }
    public function job_titles(){
        $home_id = Auth::user()->home_id;
        $data['job_title']=Job_title::whereNull('deleted_at')->get();
        $data['home_id']=$home_id;
        $data['page']='setting';
        return view('frontEnd.salesAndFinance.jobs.job_titles',$data);
    }
    public function job_type_save(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $home_id = Auth::user()->home_id;
       $data= Job_type::job_type_save_data($request->all());
       if($data){
        if(isset($request->key) && $request->key != ''){
            $result=Job_type::find($data);
            $html='<option value="'.$result->id.'">'.$result->name.'</option>';
            return $html;
        }else{
            return response()->json(['success'=>'true','message' => "Successfully  Done"], 200);
        }
       } else {
        return response()->json(['success'=>'true','message' => "Successfully  Done",'data'=>$html], 200);
       }
    }
    public function job_type_edit_form(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $data=Job_type::find($request->id);
        return response()->json($data);
    }
    public function workflow_save_data(Request $request){
        // echo "<pre>";print_r($request->all());
        $ex=explode(',',$request->delete_ids_array);
        // print_r(count($ex));die;
        if($request->delete_ids_array !='' && count($ex) > 0 && count($request->appointment_id) > 0){
            echo 111;die;
        } else if(count($ex) > 0) {
            echo 222;die;
        } else {
            echo 333;die;
        }
        $data=Work_flow::work_flow_save($request->all());
        return $data;

    }
    public function workflow_list_job(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $home_id = Auth::user()->home_id;
        $data=Work_flow::where(['home_id'=>$home_id, 'job_type_id'=>$request->id,'status'=>1])->get();
        // echo "<pre>";print_r($data);die;
        
        $appointment_type=Construction_job_appointment_type::where('home_id',$home_id)->get();
        $html='';
        foreach($data as $val){
            $html .='<tr>
                <input type="hidden" value="'.$val->id.'" name="row_count" id="row_count">
                
                <td></td>
                <td>
                    <select class="form-select" name="appointment_id[]" multiselect-search="false" multiselect-select-all="true" multiselect-max-items="4" multiple="multiple">';
                    foreach($appointment_type as $type){
                        if($type->id == $val->appointment_id){
                            $selected='selected';
                        }else {
                            $selected='';
                        }
                        $html .='<option value="'.$type->id.'" '.$selected.'>'.$type->name.'</option>';
                    }
                    $html .='</select>
                </td>
                <td>
                    <a href="javascript:void(0)" class="text-primary" onclick="set_rules('.$request->id.','.$val->id.')">Set Rule</a>
                </td>
                <td>
                    <a href="javascript:void(0)" class="text-danger delete-row">X</a>
                </td>
                </tr>';
        }
        return response()->json($html);
    }
    public function workflow_list_add(Request $request){
        $home_id = Auth::user()->home_id;
        $data=Work_flow::where(['home_id'=>$home_id, 'job_type_id'=>$request->job_type_id,'status'=>1])->count();
        
        $appointment_type=Construction_job_appointment_type::where('home_id',$home_id)->get();
        if($data == 0){
            $row_count=1;
        }else {
            $row_count=$data+1;
        }
        $html='';
            $html .='<tr>
                <input type="hidden" value="'.$row_count.'" name="row_count">
                <td></td>
                <td>
                    <select class="form-select" name="appointment_id[]" multiselect-search="false" multiselect-select-all="true" multiselect-max-items="4" multiple="multiple">';
                    foreach($appointment_type as $type){
                       
                        $html .='<option value="'.$type->id.'">'.$type->name.'</option>';
                    }
                    $html .='</select>
                </td>
                <td>
                    <a href="javascript:" class="text-primary" onclick="set_rules('.$request->id.','.$row_count.')">Set Rule</a>
                </td>
                <td>
                    <a href="javascript:" class="text-danger delete-row">X</a>
                </td>
                </tr>';
                return response()->json($html);
    }
    public function Workflow_notification_save(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $data=Workflow_notification::work_flow_notification_save($request->all());
        return $data;
    }
    
    public function planner_day(){
        echo "<h1 style='color:red'>It's under working</h1>";die;
    }
    private function access_rights(){
        $rights = User::where('id', Auth::user()->id)->where('is_deleted', 0)->first()->access_rights;
        $access_rights=explode(',',$rights);
        return $access_rights;

    }
    public function jobs_create(Request $request){
        // echo 1;die;
        // echo "<pre>";print_r(Auth::user());die;
        $home_id = Auth::user()->home_id;
        $user_id=Auth::user()->id;
        $key=base64_decode($request->key);
        // echo $key;die;
        $data['key']=$key;
        $data['projects']=Project::where(['status'=>1,'home_id'=>$home_id])->get();
        $job_details=Job::find($key);
        $data['job_details']=$job_details;
        $data['additional_contact'] = Constructor_additional_contact::where('home_id', $home_id)->get();
        $data['tag'] = Tag::getAllTag($home_id);
        // $product_details=Product::product_detail($request->id);
        // $tax=Product::tax_detail($home_id);
        $data['jobassign_products']=Construction_jobassign_product::where(['job_id'=>$key,'status'=>1,'deleted_at'=>null])->get();
        $data['job_appointment']=Construction_job_appointment::where(['job_id'=>$key,'status'=>1,'deleted_at'=>null])->get();
        $data['job_type']=Job_type::where('status',1)->get();
        $data['country']=Country::all_country_list();
        // echo "<pre>";print_r($data['country']);die;
        $data['product_details1']=DB::table('products as pr')->select('pr.*','cat.id as cat_id','cat.name')
        ->join('product_categories as cat','cat.id','=','pr.cat_id')
        ->where(['pr.home_id'=>$home_id,'pr.adder_id'=>$user_id])->get();
        $data['customers']=Customer::get_customer_list_Attribute($home_id,'ACTIVE');
        $data['home_id']=$home_id;
        $data['users']=User::where('is_deleted',0)->get();
        $data['appointment_type']=Construction_job_appointment_type::where('home_id',$home_id)->get();
        $data['customer_types']=Customer_type::where(['home_id'=>$home_id,'status'=>1])->get();
        $data['job_title']=Job_title::where(['home_id'=>$home_id,'status'=>1])->get();
        $data['region']=Region::where(['home_id'=>$home_id,'status'=>1,'deleted_at'=>null])->get();
        $data['product_count']=Product::count();
        $data['category']=Product_category::with('parent', 'children')->where('status',1)->get();
        $data['account_code']=Construction_account_code::where(['home_id'=>$home_id,'status'=>1])->get();
        $data['sales_tax']=Construction_tax_rate::where(['home_id'=>$home_id,'status'=>1])->get();
        $customerId = optional($job_details)->customer_id;
        $customer_details=optional(Customer::find($customerId));
        $data['site']=Constructor_customer_site::where('customer_id',$customerId)->get();
        $data['customer_profession']=Job_title::find($customer_details->job_title);
        $data['contact_name']=$customer_details->contact_name;
        // $customer_details=Customer::with('sites','additional_contact','customer_project')->where('id', $customerId)->first();
        // $data['site']=$customer_details->sites;
        // $data['customer_project']=$customer_details->customer_project;
        // echo "<pre>";print_r($customer_profession);die;
        // echo "<pre>";print_r($data['sales_tax']);die;
        $data['page']="jobs";
        return view('frontEnd.salesAndFinance.jobs.add_job',$data);
    }
    public function job_add_edit_save(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $home_id = Auth::user()->home_id;
        $user_id=Auth::user()->id;
        
        if ($request->hasFile('attachments')) {
            $imageName = time().'.'.$request->attachments->extension();      
            $request->attachments->move(public_path('images/jobs'), $imageName);
            $requestData = $request->all();
            $requestData['attachments'] = $imageName;
        } else {
            $requestData = $request->all();
        }
        $last_job_id=optional(Job::orderBy('id','DESC')->first());
        $requestData['last_job_id'] = $last_job_id->id;
        $requestData['user_id'] = $user_id;
        $requestData['home_id'] = $home_id;
        // echo "<pre>";print_r($requestData);die;
        try {
            $job_id=Job::job_save($requestData);
        } catch (\Exception $e) {
            return response()->json(['success'=>'false','message' => $e->getMessage()], 500);
        }
        
        // echo "<pre>";print_r($job_id);die;
        if(isset($request->quantity) && $request->quantity !=''){
            $job_product= $this->save_job_product($job_id,$request->all());
        }
        
        if(isset($request->Appointmentuser_id) && $request->Appointmentuser_id !=''){
            $appointment=$this->get_save_appointment($job_id,$request->all());
        }
        // echo "<pre>";print_r($job_product);die;
        
        return response()->json(['success'=>true, 'data'=>$job_id]);

    }
    public function get_customer_details_front(Request $request){
        $customer_id=$request->customer_id;
        $title_id=Customer::find($customer_id);
        $customers = Customer::with('sites','additional_contact','customer_project')->where('id', $customer_id)->get();
        $customer_profession=Job_title::find($title_id->job_title);
        // echo "<pre>";print_r($customer_profession);die;
        $data=[
            'customers'=>$customers,
            'customer_profession'=>$customer_profession,
        ];
        return response()->json($data);
    }
    // public function search_value_front(Request $request){
    //     $search_value = $request->search_value;
    //     $data = Product::where('product_name', 'like', "%{$search_value}%")->get();  
    //     foreach($data as $val){
    //         $cat_name=Product_category::find($val->cat_id);
    //         echo '<tr onclick="selectProduct(this)">
    //                         <td>'.$val->id.'</td>
    //                         <td>'.$cat_name->name.'</td>
    //                         <td>'.$val->product_name.'</td>
    //                         <td>'.$val->description.'</td>
    //                     </tr>';
    //     }
    // }
    public function result_product_calculation(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $html='';
        $home_id = Auth::user()->home_id;
        $user_id=Auth::user()->id;
        $previous_ids=$request->previous_id;
        // $calculation=$this->calculation($previous_ids);
        // echo "<pre>";print_r($calculation);die;
        $product_details=Product::product_detail($request->id);
        $tax=Product::tax_detail($home_id);
        if($request->key ==''){
            $html.= '<tr>
                <td>'.$product_details->product_code.'<input type="hidden" id="product_codejob" name="product_codejob[]" value="'.$product_details->product_code.'"></td>
                <td>'.$product_details->product_name.'<input type="hidden" id="product_namejob" name="product_namejob[]" value="'.$product_details->product_name.'"></td>
                <td>'.$product_details->description.'<input type="hidden" id="descriptionjob" name="descriptionjob[]" value="'.$product_details->description.'"></td>
                <td><input type="text" class="quantity" value="'.$product_details->qty.'" name="quantity[]" id="quantity"></td>
                <td>'.$product_details->cost_price.'<input type="hidden" id="cost_pricejob" class="cost_pricejob" name="cost_pricejob[]" value="'.$product_details->cost_price.'"></td>
                <td>'.$product_details->price.'<input type="hidden" id="pricejob" class="pricejob" name="pricejob[]" value="'.$product_details->price.'"></td>
                <td><input type="text" class="" value="0" name="discount[]"></td>';

            $html.= '<td><select id="vatjob" name="vatjob[]">';
            foreach($tax as $taxv){
            $html.= '<option value="'.$taxv->id.'">'.$taxv->name.'</option>';
            }
            $html.= '</select></td>
                    <td id="pre_total_amount" class="pre_total_amount">'.$product_details->price.'</td>
                    <td><button type="button" class="btn btn-danger" onclick="removeRow(this)">Delete<input type="hidden" value="'.$product_details->id.'" name="product_detail_id[]" id="product_detail_id"></button></td>
                </tr>';
            return $data=['html'=>$html];
        }else{
            $all_job=Job::getAllJob($home_id)->where('status',1)->get();
            $accountCode=Construction_account_code::getActiveAccountCode($home_id);
            return response()->json(['success'=>true,'product_detail'=>$product_details,'tax'=>$tax,'job'=>$all_job,'accountCode'=>$accountCode]);
        }
        
       
    }
    // private function calculation($data){
    //     $cost_price=0;
    //     $total_amount_assign=0;
    //     for($i=0;$i<count($data);$i++){
    //         $product_details=Product::find($data[$i]);
    //         $cost_price=$cost_price+$product_details->cost_price;
    //         $total_amount_assign=$total_amount_assign+$product_details->price;
    //     }
    //    return $data=['cost_price'=>$cost_price,'total_amount_assign'=>$total_amount_assign];
        
    // }
    public function jobassign_productsDelete(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $id=$request->id;
        try{
            Construction_jobassign_product::find($id)->update(['deleted_at' => now()]);
            return response()->json(['success'=>true,'message'=>'Deleted Successfully done']);
        }catch (\Exception $e) {
            Log::error('Error saving Tag: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
        
    }
    public function save_job_product($job_detail,$data){
        // echo "<pre>";print_r($data);die;
        $quantity=$data['quantity'];
        for($i=0;$i<count($quantity);$i++){
            if(array_key_exists('idjobasign',$data) && $data['idjobasign'] !=''){
                $table=Construction_jobassign_product::find($data['idjobasign'][$i]);
            }else{
                $table=new Construction_jobassign_product;
            }
            $table->job_id=$job_detail['id'];
            $table->product_id=$data['product_detail_id'][$i];
            $table->qty=$data['quantity'][$i];
            $table->code=$data['product_codejob'][$i];
            $table->description=$data['descriptionjob'][$i];
            $table->cost_price=$data['cost_pricejob'][$i];
            $table->price=$data['pricejob'][$i];
            $table->discount=$data['discount'][$i];
            $table->vat=$data['vatjob'][$i];
            $table->product_name=$data['product_namejob'][$i];
            $table->save();
        }
        return true;
    }
    public function get_save_appointment($job_detail,$data){
        // echo "<pre>";print_r($data);die;
        $array_data=[
            'id'=>$data['appointment_id'] ?? null,
            'user_id'=>$data['Appointmentuser_id'],
            'home_id'=>$data['home_id'],
            'job_id'=>$job_detail['id'],
            'appointment_type_id'=>$data['appointment_type_id'] ?? null,
            'start_date'=>$data['appointment_start_date'],
            'start_time'=>$data['start_time'],
            'end_date'=>$data['end_date'],
            'end_time'=>$data['end_time'],
            'floating_appointment'=>$data['floating_appointment'],
            'single_appointment'=>$data['single_appointment'],
            'travel_time'=>$data['firstinput_time'],
            'appointment_time'=>$data['secondinput_time'],
            'appointment_status'=>$data['appointment_status'],
            'priority'=>$data['priority'],
            'email'=>$data['alert_email_appointment'],
            'sms'=>$data['alert_sms_appointment'],
            'notes'=>$data['appointment_notes'],
            'status'=>1
        ];
        // echo "<pre>";print_r($array_data);die;
        try {
            $result= Construction_job_appointment::save_appointement($array_data);
            return true;
        } catch (\Exception $e) {
            Log::error('Error saving Tag: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to save Tag. Please try again.'], 500);
        }
        
    }
    public function new_appointment_add_section(Request $request){
        $home_id = Auth::user()->home_id;
        $count_number = $request->count_number;
        $users = User::where('is_deleted', 0)->get();
        $appointment_type = Construction_job_appointment_type::where('home_id', $home_id)->get();
    
        $html = '<tr>
                    <td>
                        <div class="d-flex">
                            <p class="leftNum">'.$count_number.'</p>
                            <select class="form-control editInput selectOptions" id="Appointmentuser_id" name="Appointmentuser_id[]">
                                <option selected disabled>Select user</option>';
                                foreach($users as $user){
                                    $html .= '<option value="'.$user->id.'">'.$user->name.'</option>';
                                }
        $html .=         '</select>
                            <a href="#!" class="callIcon"><i class="fa-solid fa-square-phone"></i></a>
                        </div>
                        <div class="alertBy">
                            <label><strong>Alert By:</strong></label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="alert_sms_appointment'.$count_number.'" value="0" name="alert_sms_appointment[]">
                                <label class="form-check-label" for="inlineCheckbox1">SMS</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="alert_email_appointment'.$count_number.'" value="0" name="alert_email_appointment[]">
                                <label class="form-check-label" for="inlineCheckbox2">Email</label>
                            </div>
                        </div>
                    </td>
                    <td class="col-2">
                        <div class="appoinment_type">
                            <select class="form-control editInput selectOptions" id="appointment_type_id" name="appointment_type_id[]">
                                <option selected disabled>Select Appointment Type</option>';
                                foreach($appointment_type as $appointmentv){
                                    $html .= '<option value="'.$appointmentv->id.'">'.$appointmentv->name.'</option>';
                                }
        $html .=         '</select>
                        </div>
                        <div class="Priority">
                            <label>Priority :</label>
                            <select class="form-control editInput selectOptions" id="priority" name="priority[]">
                                <option selected disabled>Select Priority</option>
                                <option>Default</option>
                            </select>
                        </div>
                    </td>
                    <td>
                        <div class="addDateAndTime">
                            <div class="startDate">
                                <input type="date" name="appointment_start_date[]" class="editInput">
                                <input type="time" name="start_time[]" class="editInput">
                            </div>
                            <span class="p-2">To</span>
                            <div class="endDate">
                                <input type="date" name="end_date[]" class="editInput">
                                <input type="time" name="end_time[]" class="editInput">
                            </div>
                        </div>
                        <div class="pt-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="single_appointment'.$count_number.'" value="0" name="single_appointment[]">
                                <label class="form-check-label" for="singleAppointment">Single Appointment</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="floating_appointment'.$count_number.'" value="0" name="floating_appointment[]">
                                <label class="form-check-label" for="floatingAppointment">Floating Appointment</label>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="addTextarea">
                            <textarea cols="40" rows="5" id="appointment_notes" name="appointment_notes[]"></textarea>
                        </div>
                    </td>
                    <td>
                        <div class="statuswating">
                            <select class="form-control editInput selectOptions" id="appointment_status" name="appointment_status[]">
                                <option selected disabled>Select Status</option>
                                <option value="1">Awaiting</option>
                                <option value="2">Received</option>
                                <option value="3">Accepted</option>
                                <option value="4">Declined</option>
                                <option value="5">on Route</option>
                                <option value="6">On Site</option>
                                <option value="7">Completed</option>
                                <option value="8">Follow On</option>
                                <option value="9">Abandoned</option>
                                <option value="10">No Access</option>
                                <option value="11">Cancelled</option>
                                <option value="12">On Hold</option>
                            </select>
                            <a href="javascript:void(0)" onclick="deleteRow(this)"><i class="fa-solid fa-circle-xmark"></i></a>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="Priority">
                            <label><strong>Travel Time
                                    -</strong></label>
                            <input type="text"
                                class="form-control editInput"
                                id="firstinput_time'.$count_number.'" name="firstinput_time[]"
                                placeholder="" onkeyup="get_time('.$count_number.')"><label>
                                Mins</label>
                        </div>
                    </td>
                    <td></td>
                    <td>
                        <div class="Priority">
                            <label><strong>Appointment Time
                                    -</strong></label>
                            <input type="text"
                                class="form-control editInput"
                                id="secondinput_time'.$count_number.'" name="secondinput_time[]"
                                placeholder="" onkeyup="get_time('.$count_number.')"><label> Mins
                                <strong>Total Time -</strong><font id="time_show'.$count_number.'">0h
                                0mins</font> </label>
                        </div>
                        <input type="hidden" id="appointment_time'.$count_number.'" class="appointment_time" name="appointment_time[]">
                    </td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>
                        <div class="Priority p-0">
                            <label class="p-0"><strong>Assigned
                                    Products: </strong><a
                                    href="#!">All</a> None</label>
                        </div>
                    </td>
                    <td>
                        <div class="pageTitleBtn p-0">
                            <a href="#" class="profileDrop">Asign
                                Product</a>
                        </div>
                    </td>
                    <td></td>
                    <td colspan="2">
                        <div class="pageTitleBtn p-0">
                            <a href="#" class="profileDrop">Add
                                Title</a>
                            <a href="#" class="profileDrop">Show
                                Variations</a>
                            <a href="#"
                                class="profileDrop bg-secondary">Export</a>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="5" class="padingtableBottom"></td>
                </tr>';
        
        return $html;
    }
    public function job_appointment_type_list(Request $request){
        $home_id = Auth::user()->home_id;
        $data['appointment_type']=Construction_job_appointment_type::where(['home_id'=>$home_id,'deleted_at'=>null])->get();
        // echo "<pre>";print_r($data['appointment_type']);die;
        $data['home_id']=$home_id;
        $data['users']=User::all();
        $data['page']='setting';
        // echo "<pre>";print_r($data['users']);die;
        return view('frontEnd.salesAndFinance.jobs.job_appointment_type', $data);
    }
    public function job_type_appointment_save(Request $request){
        // echo "<pre>";print_r($request->all());die;
        if(isset($request->notify) && $request->notify == 1){
            $data=[
                'id'=>$request->id,
                'document'=>$request->document,
                'notify'=>$request->notify,
                'on_complete'=>$request->on_complete,
                'on_change'=>$request->on_change,
                'notify_who'=>implode(',',$request->notify_who),
                'notification'=>$request->notification,
                'sms'=>$request->sms,
                'email'=>$request->email,
                'notify_customer'=>$request->notify_customer
            ];
            // echo "<pre>";print_r($data);die;
            echo $result=Construction_job_appointment_type::SaveJobAppointmentType($data);
        }else {
            echo $result=Construction_job_appointment_type::SaveJobAppointmentType($request->all());
        }
        
    }
    public function job_appointment_type_edit_form(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $data=Construction_job_appointment_type::find($request->id);
        return response()->json($data);
    }
    public function appointment_rejection_cat_list(){
        $data['rejection']=construction_appointment_rejection_category::where(['deleted_at'=>null])->get();
        $home_id = Auth::user()->home_id;
        $data['home_id']=$home_id;
        $data['page']='setting';
        // echo "<pre>";print_r($data['rejection']);die;
        return view('frontEnd.salesAndFinance.jobs.appointment_rejection_cat',$data);
        
    }
    public function appointment_rejection_cat_save(Request $request){
        // echo "<pre>";print_r($request->all());die;
        try {
            $reject_cat=construction_appointment_rejection_category::SaveAppointmentRejectionCategory($request->all());
            return response()->json(['success' => true, 'reject_cat' => $reject_cat]);
        } catch (\Exception $e) {
            Log::error('Error saving Tag: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to save Tag. Please try again.'], 500);
        }
    }
    public function job_appointment_rejection_edit_form(Request $request){
        $data=construction_appointment_rejection_category::find($request->id);
        return response()->json($data);
    }
    public function job_title_edit_form(Request $request){
        $data=job_title::find($request->id);
        return $data;
    }
    public function save_job_title(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $insert='';
        $table=new job_title;
        try {
            $insert=$table::updateOrCreate(
                ['id' => $request->id ?? null],
                $request->all(),
            );
            if($insert->status ==1){
                echo '<option value="'.$insert->id.'">'.$insert->name.'</option>';
            }
        } catch (\Exception $e) {
            return response()->json(['success'=>'false','message' => $e->getMessage()], 500);
        }
    }
    public function save_region(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $insert='';
        $table=new Region;
        try {
            $insert=$table::updateOrCreate(
                ['id' => $request->id ?? null],
                $request->all(),
            );
        } catch (\Exception $e) {
            // return response()->json(['success'=>'false','message' => $e->getMessage()], 500);
            $error=$e->getMessage();
        }
        if($insert){
            if($insert->status ==1){
                echo '<option value="'.$insert->id.'">'.$insert->title.'</option>';
            }
        }else{
            echo "error";
        }
    }

    public function project_save(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'customer_name'=>'required',
            'project_name'=>'required',
            'start_date'=>'required',
            'end_date'=>'required',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['vali_error' => $validator->errors()->first()]);
        }
        $insert=Project::saveProject($request->all());
        if($insert){
            if($insert->status ==1){
                echo '<option value="'.$insert->id.'">'.$insert->project_name.'</option>';
            }
        }else{
            echo "error";
        }
    }
    public function contact_save(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $data=$request->all();
        $data['home_id']=Auth::user()->home_id;
        // echo "<pre>";print_r($data);die;
        $insert=Constructor_additional_contact::saveCustomerAdditional($data);
        $data=Constructor_additional_contact::find($insert->id);
        if($data){
            if($data->status ==1){
                echo '<option value="'.$data->id.'">'.$data->contact_name.'</option>';
            }
        }else{
            echo "error";
        }
    }
    public function site_save(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'site_name'=>'required',
            'contact_name'=>'required',
            'address'=>'required',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['vali_error' => $validator->errors()->first()]);
        }
        $result=Constructor_customer_site::saveCustomerAdditional($request->all());
        $data=Constructor_customer_site::find($result);
        if($data){
            if($data->status ==1){
                echo '<option value="'.$data->id.'">'.$data->site_name.'</option>';
            }
        }else{
            echo "error";
        }
    }
    public function supplier_result(){
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
                <td><span class="currency">£</span><input type="text" id="cost_price_supplier" name="cost_price_supplier[]" value="0.0000"></td>
                <td class="delete_row">X</td>
            </tr>';
        echo $res;
    }
    public function product_save(Request $request){
        // echo "<pre>";print_r($request->all());die;
        if ($request->hasFile('product_attachments')) {
            $imageName = time().'.'.$request->product_attachments->extension();      
            $request->product_attachments->move(public_path('images/jobs'), $imageName);
        } else {
            $imageName=$request->old_image; 
        }
        $product_data=[
            'home_id'=>Auth::user()->home_id,
            'adder_id'=>Auth::user()->id,
            'customer_only'=>$request->product_yes,
            'cat_id'=>$request->product_category_id,
            'product_name'=>$request->product_name,
            'cost_price'=>$request->product_cost_price,
            'margin'=>$request->product_markup,
            'price'=>$request->product_price,
            'tax_rate'=>$request->purchase_tax_rate,
            'description'=>$request->product_description,
            'product_code'=>$request->product_postal_code,
            'show_temp'=>$request->showontemplate,
            'bar_code'=>$request->bar_code,
            'tax_id'=>$request->sales_tax_rate,
            'nominal_code'=>$request->nominal_code,
            'sales_acc_code'=>$request->sales_account_code,
            'purchase_acc_code'=>$request->purchase_account_code,
            'expense_acc_code'=>$request->expense_account_code,
            'location'=>$request->product_location,
            'attachment'=>$imageName,
            'status'=>$request->product_status
        ];

        $insert='';
        $table=new Product;
        try {
            $insert=$table::updateOrCreate(
                ['id' => $request->id ?? null],
                $product_data,
            );
        } catch (\Exception $e) {
           return $e->getMessage();
        }
        if(isset($request->supplier_id) && count($request->supplier_id) >0){
            for($i=0;$i<count($request->supplier_id);$i++){
                $table_insert=new Construction_product_supplier_list;
                $table_insert->product_id=$insert->id;
                $table_insert->supplier_id=$request->supplier_id[$i];
                $table_insert->part_number=$request->part_number[$i];
                $table_insert->cost_price_supplier=$request->cost_price_supplier[$i];
                $table_insert->save();
            }
        }
        echo "done";
       
    }

    public function save_product_category(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $insert='';
        $table=new Product_category;
        try {
            $insert=$table::updateOrCreate(
                ['id' => $request->id ?? null],
                $request->all(),
            );
        } catch (\Exception $e) {
            // return response()->json(['success'=>'false','message' => $e->getMessage()], 500);
            $error=$e->getMessage();
        }
        if($insert){
            $category=Product_category::with('parent', 'children')->where('status',1)->get();
            foreach($category as $val){
                echo '<option value="'.$val->id.'">'.$val->full_category.'</option>';
            }
        }else{
            echo "error";
        }
    }
    public function save_tax_rate(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $insert='';
        $table=new Construction_tax_rate;
        try {
            $insert=$table::updateOrCreate(
                ['id' => $request->id ?? null],
                $request->all(),
            );
        } catch (\Exception $e) {
            // return response()->json(['success'=>'false','message' => $e->getMessage()], 500);
            $error=$e->getMessage();
        }
        if($insert){
            
            if($insert->status == 1){
                echo '<option value="'.$insert->id.'">'.$insert->name.'</option>';
            }
        }else{
            echo "error";
        }
    }
    
    public function job_save_all(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $form_id=$request->form_id;
        
        if($form_id == 1){
            if ($request->hasFile('attach')) {
                $imageName = time().'.'.$request->attach->extension();      
                $request->attach->move(public_path('images/jobs'), $imageName);
            } else {
                $imageName=$request->old_image; 
            }
            if($request->job_id == ''){
                $validator=Validator::make($request->all(),
            [
                'customer'=>'required',
                'project' => 'required',
                'contact'=>'required',
                'country'=>'required',
                'customer_ref'=>'required',
                'amount'=>'required',
                'purchase_order'=>'required',
                'job_type'=>'required',
                'priority'=>'required',
                'sms'=>'required',
                'start_date'=>'required',
                'complete_by'=>'required',
                'tags'=>'required',
                'product_id'=>'required',
                'attach'=>'required',

            ]);
            if($validator->fails())
            {
                return response()->json(['error'=>$validator->errors()->first()]);
            }
                $table_job= new Job;
            } else {
                $table_job=Job::find($request->job_id);
            }
            $table_job->customer_id=$request->customer;
            $table_job->project_id=$request->project;
            $table_job->contact=$request->contact;
            $table_job->country=$request->country;
            $table_job->customer_ref=$request->customer_ref;
            $table_job->pay_amount=$request->amount;
            $table_job->purchase_order_ref=$request->purchase_order;
            $table_job->job_type=$request->job_type;
            $table_job->priorty=$request->priority;
            $table_job->alert_customer=$request->alert_customer;
            $table_job->on_route_sms=$request->sms;
            $table_job->start_date=$request->start_date;
            $table_job->complete_by=$request->complete_by;
            $table_job->tags=$request->tags;
            $table_job->product_id=$request->product_id;
            $table_job->attachments=$imageName;
            $table_job->save();
            if($request->job_id == ''){
                $job_ref="#_".$table_job->id;
                $table_update= Job::find($table_job->id);
                $table_update->job_ref=$job_ref;
                $table_update->save();
            }
            echo "done";
        } else if($form_id == 2){
            $name=$request->name;
            $days=$request->days;
            if($request->job_type_id == ''){
            $validator=Validator::make($request->all(),
            [
                'name'=>'required',
                'days' => 'required',
            ]);
            if($validator->fails())
            {
                return response()->json(['error'=>$validator->errors()->first()]);
            }
                $table_job_type= new Job_type;
            } else {
                $table_job_type= Job_type::find($request->job_type_id);
            }
            $table_job_type->name=$name;
            $table_job_type->default_days=$days;
            $table_job_type->save();
            echo "done";

        } else if($form_id == 3){
            $job_type=$request->job_type;
            $work_flow=$request->work_flow;
            if($request->work_flow_id == ''){
            $validator=Validator::make($request->all(),
            [
                'job_type'=>'required',
                'work_flow' => 'required',
            ]);
            if($validator->fails())
            {
                return response()->json(['error'=>$validator->errors()->first()]);
            }
                $table_work_flow=new Work_flow;
            } else {
                $table_work_flow= Work_flow::find($request->work_flow_id);
            }
            // $table_work_flow->job_type_id=$job_type;
            $table_work_flow->flow_name=$work_flow;
            $table_work_flow->save();
            echo "done";

        } else if($form_id == 5){
            $name=$request->name;
            if($request->category_id == ''){
            $validator=Validator::make($request->all(),
            [
                'name'=>'required',
            ]);
            if($validator->fails())
            {
                return response()->json(['error'=>$validator->errors()->first()]);
            }
                $table_category= new Product_category;
            } else {
                $table_category= Product_category::find($request->category_id);
            }
            // echo "<per>";print_r($table_category);die;
            $table_category->name=$name;
            $table_category->save();
            echo "done";
        } else if($form_id == 6){
            $cat_id=$request->cat_id;
            $name=$request->name;
            if($request->product_id == ''){
            $validator=Validator::make($request->all(),
            [
                'cat_id'=>'required',
                'name'=>'required',
            ]);
            if($validator->fails())
            {
                return response()->json(['error'=>$validator->errors()->first()]);
            }
                $table_product = new Product;
            } else {
                $table_product= Product::find($request->product_id);
            }
            $table_product->cat_id=$cat_id;
            $table_product->product_name=$name;
            $table_product->save();
            echo "done";
        } else if($form_id == 7){
            // echo "<pre>";print_r($request->all());die;
            $quote_ref=$request->quote_ref;
            $quote_customer=$request->quote_customer;
            $quote_project=$request->quote_project;
            $quotes_date=$request->quotes_date;
            $quotes_expiry=$request->quotes_expiry;
            $customer_ref_quote=$request->customer_ref_quote;
            $customer_jobref_quote=$request->customer_jobref_quote;
            $purchase_ref_quote=$request->purchase_ref_quote;
            $source=$request->source;
            $status=$request->status;
            $extra_information=$request->extra_information;
            $customer_notes=$request->customer_notes;
            $tearms=$request->tearms;
            $internal_notes=$request->internal_notes;
            
            if ($request->hasFile('attachment3')) {
                $imageName3 = time().'.'.$request->attachment3->extension();      
                $request->attachment3->move(public_path('images/jobs'), $imageName3);
            } else {
                $imageName3=$request->old_image3; 
            }

            $quantity=$request->quantity;
            $product_detail_id=$request->product_detail_id;
            if($request->quotes_id == ''){
            // $validator=Validator::make($request->all(),
            // [
            //     'job_ref_id'=>'required',
            //     'quotes_name'=>'required',
            // ]);
            // if($validator->fails())
            // {
            //     return response()->json(['error'=>$validator->errors()->first()]);
            // }
                $table_quotes= new Quote;
                $table_quotes->quote_ref=$quote_ref;
                $table_quotes->customer_id=$quote_customer;
                $table_quotes->project_id=$quote_project;
                $table_quotes->site_id=1;
                $table_quotes->quota_type=1;
                $table_quotes->quota_date=$quotes_date;
                $table_quotes->expiry_date=$quotes_expiry;
                $table_quotes->customer_ref=$customer_ref_quote;
                $table_quotes->customer_job_ref=$customer_jobref_quote;
                $table_quotes->purchase_order_ref=$purchase_ref_quote;
                $table_quotes->source=$source;
                $table_quotes->performed_job_date=date('Y-m-d');
                $table_quotes->period='';
                $table_quotes->tags='#tag';
                $table_quotes->title='';
                $table_quotes->title_description='';
                $table_quotes->price=100;
                $table_quotes->vat='No Vat';
                $table_quotes->extra_information=$extra_information;
                $table_quotes->customer_notes=$customer_notes;
                $table_quotes->tearms=$tearms;
                $table_quotes->internal_notes=$internal_notes;
                $table_quotes->attachments=$imageName3;
                $table_quotes->status=$status;
                $table_quotes->save();
                for($i=0;$i<count($product_detail_id);$i++){
                    $table_quote_product_detail=new Quote_product_detail;
                    $table_quote_product_detail->quotes_id=$table_quotes->id;
                    $table_quote_product_detail->product_id=$product_detail_id[$i];
                    $table_quote_product_detail->description="testing";
                    $table_quote_product_detail->save();
                }
            } else {
                $table_quotes= Quote::find($request->quotes_id);
                $table_quotes->customer_id=$quote_customer;
                $table_quotes->project_id=$quote_project;
                $table_quotes->site_id=1;
                $table_quotes->quota_type=1;
                $table_quotes->quota_date=$quotes_date;
                $table_quotes->expiry_date=$quotes_expiry;
                $table_quotes->customer_ref=$customer_ref_quote;
                $table_quotes->customer_job_ref=$customer_jobref_quote;
                $table_quotes->purchase_order_ref=$purchase_ref_quote;
                $table_quotes->source=$source;
                $table_quotes->performed_job_date=$table_quotes->performed_job_date;
                $table_quotes->period='';
                $table_quotes->tags='#tag';
                $table_quotes->title='';
                $table_quotes->title_description='';
                $table_quotes->price=100;
                $table_quotes->vat='No Vat';
                $table_quotes->extra_information=$extra_information;
                $table_quotes->customer_notes=$customer_notes;
                $table_quotes->tearms=$tearms;
                $table_quotes->internal_notes=$internal_notes;
                $table_quotes->attachments=$imageName3;
                $table_quotes->status=$status;
                $table_quotes->save();
                for($i=0;$i<count($product_detail_id);$i++){
                    $table_quote_product_detail=Quote_product_detail::find($product_detail_id[$i]);
                    $table_quote_product_detail->quotes_id=$table_quotes->id;
                    $table_quote_product_detail->product_id=$product_detail_id[$i];
                    $table_quote_product_detail->description="testing";
                    $table_quote_product_detail->save();
                }
            }
            
            echo "done";
        } else if($form_id == 8){
            $customer_name=$request->customer_name;
            $project_name=$request->project_name;
            if($request->project_id == ''){
                $count_project=Project::count();
                // echo $count_project;die;
                $ref_pattern="Pro_".$count_project+1;
                $validator=Validator::make($request->all(),
                [
                    'customer_name'=>'required',
                    'project_name' => 'required',
                ]);
                if($validator->fails())
                {
                    return response()->json(['error'=>$validator->errors()->first()]);
                }
                $table_project= new Project;
            } else {
                $ref_pattern=$request->project_ref;
                $table_project= Project::find($request->project_id);
            }
            $table_project->project_name=$project_name;
            $table_project->customer_name=$customer_name;
            $table_project->project_ref=$ref_pattern;
            // echo "<pre>";print_r($table_project);die;
            $table_project->save();
            echo "done";
        } else if($form_id == 9){
            // echo "<pre>";print_r($request->all());die;
            $users=$request->users;
            $site=$request->site;
            $mobile=$request->mobile;
            $sms_number=$request->sms_number;
            $customer_ref=$request->customer_ref;
            $amount=$request->amount;
            $purchase_order=$request->purchase_order;
            $job_types=$request->job_types;
            $priority=$request->priority;
            $alert_customer=$request->alert_customer;
            $sms1=$request->sms1;
            $tags1=$request->tags1;
            $short_des=$request->short_des;
            $instruction=$request->instruction;

            $inc=$request->inc;
            $user_id=$request->user_id;
            $recurringjob_start_date=$request->recurringjob_start_date;
            $count_repetition=$request->count_repetition;
            $job_frequency_name=$request->job_frequency_name;
            $day1=$request->day1;
            $month1=$request->month1;
            $every1=$request->every1;
            $day2=$request->day2;
            $every2=$request->every2;
            $quantity=$request->quantity;
            $create_before=7;
            $end=1;

            $product_detail_id=$request->product_detail_id;

            $job_recurring_id=$request->job_recurring_id;
            if ($request->hasFile('attach1')) {
                $imageName1 = time().'.'.$request->attach1->extension();      
                $request->attach1->move(public_path('images/job_ercurring'), $imageName1);
            } else {
                $imageName1=$request->old_image1; 
            }
            if($job_recurring_id == ''){
                $table_job_recurring= new Job_recurring;
                $table_recurrence_pattern_rule= new Recurrence_pattern_rule;
                $table_recurring_product_detail= new Recurring_product_detail;

                $table_job_recurring->job_type=$job_types;
                $table_job_recurring->customer_id=$users;
                $table_job_recurring->mobile=$mobile;
                $table_job_recurring->site_id=$site;
                $table_job_recurring->mobile_sms=$sms_number;
                $table_job_recurring->sms_alert=$sms1;
                $table_job_recurring->customer_ref=$customer_ref;
                $table_job_recurring->amount=$amount;
                $table_job_recurring->purchase_orderref=$purchase_order;
                $table_job_recurring->priority=$priority;
                $table_job_recurring->customer_alert=$request->alert_customer1;
                $table_job_recurring->tags=$tags1;
                $table_job_recurring->short_des=$short_des;
                $table_job_recurring->instruction=$instruction;
                $table_job_recurring->attachments=$imageName1;
                $table_job_recurring->save();
                // echo "<pre>";print_r($table_job_recurring);die;
    
                for($i=0;$i<count($inc);$i++){
                    $table_recurrence_pattern_rule->job_recurring_id=$table_job_recurring->id;
                    $table_recurrence_pattern_rule->user_id=$user_id[$i];
                    $table_recurrence_pattern_rule->job_create=$create_before;
                    $table_recurrence_pattern_rule->start_date=$recurringjob_start_date[$i];
                    $table_recurrence_pattern_rule->end_radio=$end;
                    $table_recurrence_pattern_rule->repetition=$count_repetition[$i];
                    $table_recurrence_pattern_rule->range_day_first=$day1[$i];
                    $table_recurrence_pattern_rule->range_every_first=$month1[$i];
                    $table_recurrence_pattern_rule->range_every_sec=$every1[$i];
                    $table_recurrence_pattern_rule->range_day_sec=$day2[$i];
                    $table_recurrence_pattern_rule->range_every_third=$every2[$i];
                    $table_recurrence_pattern_rule->job_frequency=$job_frequency_name[$i];
                    // echo "<pre>";print_r($table_recurrence_pattern_rule);die;
                    $table_recurrence_pattern_rule->save();
                }
    
                for($ii=0;$ii<count($product_detail_id);$ii++){
                    $table_recurring_product_detail->job_recurring_id=$table_job_recurring->id;
                    $table_recurring_product_detail->product_id=$product_detail_id[$ii];
                    $table_recurring_product_detail->qty=$quantity[$ii];
                    // echo "<pre>";print_r($table_recurring_product_detail);die;
                    $table_recurring_product_detail->save();
                }
            } else {
                $table_job_recurring= Job_recurring::find($job_recurring_id);

                $table_job_recurring->job_type=$job_types;
                $table_job_recurring->customer_id=$users;
                $table_job_recurring->mobile=$mobile;
                $table_job_recurring->site_id=$site;
                $table_job_recurring->mobile_sms=$sms_number;
                $table_job_recurring->sms_alert=$sms1;
                $table_job_recurring->customer_ref=$customer_ref;
                $table_job_recurring->amount=$amount;
                $table_job_recurring->purchase_orderref=$purchase_order;
                $table_job_recurring->priority=$priority;
                $table_job_recurring->customer_alert=$request->alert_customer1;
                $table_job_recurring->tags=$tags1;
                $table_job_recurring->short_des=$short_des;
                $table_job_recurring->instruction=$instruction;
                $table_job_recurring->attachments=$imageName1;
                $table_job_recurring->save();
                // echo "<pre>";print_r($table_job_recurring);die;
    
                for($i=0;$i<count($inc);$i++){
                    // echo "<pre>";print_r($request->all());die;
                    $table_recurrence_pattern_rule= Recurrence_pattern_rule::find($request->rules_id[$i]);
                    // echo "<pre>";print_r($table_recurrence_pattern_rule);die;
                    $table_recurrence_pattern_rule->job_recurring_id=$request->job_recurring_id;
                    $table_recurrence_pattern_rule->user_id=$user_id[$i];
                    $table_recurrence_pattern_rule->job_create=$create_before;
                    $table_recurrence_pattern_rule->start_date=$recurringjob_start_date[$i];
                    $table_recurrence_pattern_rule->end_radio=$end;
                    $table_recurrence_pattern_rule->repetition=$count_repetition[$i];
                    $table_recurrence_pattern_rule->range_day_first=$day1[$i];
                    $table_recurrence_pattern_rule->range_every_first=$month1[$i];
                    $table_recurrence_pattern_rule->range_every_sec=$every1[$i];
                    $table_recurrence_pattern_rule->range_day_sec=$day2[$i];
                    $table_recurrence_pattern_rule->range_every_third=$every2[$i];
                    $table_recurrence_pattern_rule->job_frequency=$job_frequency_name[$i];
                    // echo "<pre>";print_r($table_recurrence_pattern_rule);die;
                    $table_recurrence_pattern_rule->save();
                }
    
                for($ii=0;$ii<count($product_detail_id);$ii++){
                    $table_recurring_product_detail= Recurring_product_detail::find($request->product_detail_id[$ii]);
                    $table_recurring_product_detail->job_recurring_id=$request->job_recurring_id;
                    $table_recurring_product_detail->product_id=$product_detail_id[$ii];
                    $table_recurring_product_detail->qty=$quantity[$ii];
                    // echo "<pre>";print_r($table_recurring_product_detail);die;
                    $table_recurring_product_detail->save();
                }
            }
           
            echo "done";

        } else if($form_id == 10){
            $type_name=$request->type_name;
            if($request->type_id == ''){
                $table_quote_type = new QuoteType;
            } else {
                $table_quote_type = QuoteType::find($request->type_id);
            }
            $table_quote_type->name=$type_name;
            $table_quote_type->save();
            echo "done";

        }
    }
    public function status_change(Request $request){
        echo "<pre>";print_r($request->all());die;
        $form_id=$request->form_id;
        if($form_id == 1){
            $table = Job::find($request->id);
        } else if($form_id == 2){
            $table = Job_type::find($request->id);
        } else if($form_id == 3){
            $table = Work_flow::find($request->id);
        } else if($form_id == 5){
            $table= Product_category::find($request->id);
        } else if($form_id == 6){
            $table=Product::find($request->id);
        } else if($form_id == 7){
            $table=Quote::find($request->id);
        } else if($form_id == 8){
            $table=Project::find($request->id);
        } else if($form_id == 9){
            $table=Job_recurring::find($request->id);
        } else if($form_id == 10){
            $table=QuoteType::find($request->id);
        }
        // echo "<pre>";print_r($table);die;
        $table->status=$request->status;
        $table->save();
        echo "done";
    }
    public function delete_function(Request $request){
        echo "<pre>";print_r($request->all());die;
        $form_id=$request->form_id;
        if($form_id == 1){
            $table = Job::find($request->id);
        } else if($form_id == 2){
            $table = Job_type::find($request->id);
        } else if($form_id == 3){
            $table = Work_flow::find($request->id);
        } else if($form_id == 5){
            $table= Product_category::find($request->id);
        } else if($form_id == 6){
            $table=Product::find($request->id);
        } else if($form_id == 7){
            $table=Quote::find($request->id);
        } else if($form_id == 8){
            $table=Project::find($request->id);
        } else if($form_id == 9){
            $table=Job_recurring::find($request->id);
        } else if($form_id == 10){
            $table=QuoteType::find($request->id);
        }
        // echo "<pre>";print_r($table);die;
        $table->status=2;
        $table->save();
        echo "done";
    }
    public function edit_job(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $form_id=$request->form_id;
        if($form_id == 1){
            $table = Job::find($request->id);
            $table['form_id']=$form_id;
        } else if($form_id == 2){
            $table = Job_type::find($request->id);
            $table['form_id']=$form_id;
        } else if($form_id == 3){
            $table = Work_flow::find($request->id);
            $table['form_id']=$form_id;
        } else if($form_id == 5){
            $table= Product_category::find($request->id);
            $table['form_id']=$form_id;
        } else if($form_id == 6){
            $table=Product::find($request->id);
            $table['form_id']=$form_id;
        } else if($form_id == 7){
            // echo "<pre>";print_r($request->all());die;
            $table=Quote::find($request->id);
            $table['form_id']=$form_id;
            $quote_product_detail=Quote_product_detail::where('quotes_id',$request->id)->get();
            // echo "<pre>";print_r($quote_product_detail);die;
            $data_product='';
            foreach($quote_product_detail as $k=>$details){
                $data_product.='<tr>
                            <td>'.++$k.'</td>
                            <td>QU-'.$table->quote_ref.'</td>
                            <td>job_ref_123</td>
                            <td></td>
                            <td></td>
                            <td>100</td>
                            <td>vat</td>
                            <td>100</td>
                            <td>-</td>
                            <td>-</td>
                            <td>In-active</td>
                            <input type="hidden" value="'.$details->product_id.'" name="product_detail_id[]">
                            <input type="hidden" value="'.$details->qty.'" name="quantity[]">
                            </tr>';
            }
            $table['product_data']=$data_product;
        } else if($form_id == 8){
            $table=Project::find($request->id);
            $table['form_id']=$form_id;
        } else if($form_id == 9){
            $table=Job_recurring::find($request->id);
            $Recurrence_pattern_rule=Recurrence_pattern_rule::where('job_recurring_id',$request->id)->get();
            $Recurring_product_detail=Recurring_product_detail::where('job_recurring_id',$request->id)->get();
            $data='';
            $inc=1;
            foreach($Recurrence_pattern_rule as $value){
                $user_details=ServiceUser::where('id',$value->user_id)->first();
                if($value->job_frequency == 1){
                    $frequency="Yearly";
                } else if($value->job_frequency == 2){
                    $frequency="Monthly";
                } else {
                    $frequency="Daily";
                }
                $data.='<tr>
                            <td>'.$inc.'<input type="hidden" value="'.$inc.'" id="inc" name="inc[]"></td>
                            <td>'.$user_details->name.'<input type="hidden" value="'.$user_details->id.'" name="user_id[]"></td>
                            <td>'.$value->start_date.'<input type="hidden" value="'.$value->start_date.'" name="recurringjob_start_date[]"></td>
                            <td>'.$value->repetition.'<input type="hidden" value="'.$value->repetition.'" name="count_repetition[]"></td>
                            <td>2024/08/20</td><td>'.$frequency.'<input type="hidden" value="'.$value->job_frequency.'" name="job_frequency_name[]"></td>
                            <input type="hidden" value="'.$value->range_day_first.'" name="day1[]">
                            <input type="hidden" value="'.$value->range_every_first.'" name="month1[]">
                            <input type="hidden" name="every1[]" value="'.$value->range_every_sec.'">
                            <input type="hidden" value="'.$value->range_day_sec.'" name="day2[]">
                            <input type="hidden" value="'.$value->range_every_third.'" name="every2[]">
                            <input type="hidden" value="'.$value->id.'" name="rules_id[]"
                        </tr>';
                $inc++;
            }
            $i=1;
            $data1='';
            foreach($Recurring_product_detail as $val){
                $product_details=Product::find($val->product_id);
                $data1.='<tr>
                            <td>'.$product_details->product_name.'</td>
                            <td>'.$product_details->description.'</td>
                            <td><input type="number" class="form-control" value="'.$val->qty.'" name="quantity[]"></td>
                            <td><button class="btn btn-danger" onclick="removeRow(this)">Delete<input type="hidden" value="'.$val->id.'" name="product_detail_id[]"></button></td>
                        </tr>';
            }
            // echo "<pre>";print_r($data1);die;
            $table['form_id']=$form_id;
            $val_data['table']=$table;
            $val_data['rule']=$data;
            $val_data['product']=$data1;
            return response()->json($val_data);

        } else if($form_id == 10){
            $table=QuoteType::find($request->id);
            $table['form_id']=$form_id;
        }
        return response()->json($table);
    }
    public function search_value(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $search_value = $request->search_value;
        $data = Product::where('product_name', 'like', "%{$search_value}%")->get();  
        foreach($data as $val){
            $cat_name=Product_category::find($val->cat_id);
            echo '<tr onclick="selectProduct('.$val->id.')">
                            <td>'.$val->id.'</td>
                            <td>'.$cat_name->name.'</td>
                            <td>'.$val->product_name.'</td>
                            <td>'.$val->description.'</td>
                        </tr>';
        }
    }
    public function product_modal_list(Request $request){
        $home_id=Auth::user()->home_id;
        $data = Product::where(['home_id'=>$home_id,'status'=>1])->get();
        // echo "<pre>";print_r($data);die;
        foreach($data as $val){
            $cat_name=Product_category::find($val->cat_id);
            echo '<tr onclick="selectProduct('.$val->id.')">
                            <td>'.$val->id.'</td>
                            <td>'.$cat_name->name.'</td>
                            <td>'.$val->product_name.'</td>
                            <td>'.$val->description.'</td>
                        </tr>';
        }
    }
    public function save_get_ajax(Request $request){
        // echo "<pre>";print_r($request->all());die;
        // $productDetailIds=$request->productDetailIds;
        // $all_product=Product::whereIn('id',$productDetailIds)->get();
        // echo "<pre>";print_r(count($all_product));die;
        $quote_ref=rand(100,9999999);
        if($request->status == 0){
            $status = "In-active";
        } else if($request->status == 1){
            $status = "Active";
        } else if($request->status == 3){
            $status = "Draft";
        }
            $data='<tr>
            <td>1</td>
            <td>QU-'.$quote_ref.'</td>
            <td>job_ref_123</td>
            <td>'.$request->start_date.'</td>
            <td>'.$request->end_date.'</td>
            <td>100</td>
            <td>vat</td>
            <td>100</td>
            <td>-</td>
            <td>-</td>
            <td>'.$status.'</td>
            </tr>
            <input type="hidden" value="'.$quote_ref.'"  name="quote_ref">
            <input type="hidden" value="100" name="quote_price">
            ';
        // return $data;
        echo $data;
    }

    public function getActiveJobAppointment(){
        $data = Construction_job_appointment_type::where('home_id', Auth::user()->home_id)->select('id', 'name')->where('status', 1)->where('deleted_at', null)->get();    
        
        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? $data : 'No Data'
        ]);

    }
}
