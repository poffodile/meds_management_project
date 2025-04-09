<?php
namespace App\Http\Controllers\backEnd\superAdmin;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session; 
use App\Home, App\CompanyCharges, App\CompanyPayment, App\Admin, App\CompanyPaymentInformation,App\AdminCardDetail, App\States;  
use DB; 
use Hash;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index(Request $request, $system_admin_id) { 
        // $scits_home_id = Session::get('scits_home_id');

        $states = States::get()->toArray();
        $company_charges    = CompanyCharges::select('company_charges.*')
                                            ->get()->toArray();
        /*$company_package    = CompanyPayment::select('company_payment.admin_id','company_payment.homes_added','company_payment.expiry_date','company_charges.home_range','company_charges.package_type','status')*/
        // echo "<pre>"; print_r($company_charges); //die;
        $company_package    = CompanyPayment::select('company_payment.admin_id','company_payment.homes_added','company_payment.expiry_date','company_charges.home_range','company_charges.package_type','company_payment.free_trial_done','company_payment.status')
                                            ->join('company_charges','company_charges.id','company_payment.company_charges_id')
                                            ->where('company_payment.admin_id',$system_admin_id)
                                            ->first();
        // echo "<pre>"; print_r($company_package); die;
        $current_date   = Carbon::now();
        // echo $current_date; die;
        if($company_package != ''){
            $company_package    = json_decode(json_encode($company_package));
            $disable_btn        = array(); // package with, out of home range
            foreach ($company_charges as $company_charge) {


                $package_range      = $company_charge['home_range'];
                $package_range      = explode('-', $package_range);
                $package_range_end  = $package_range['1'];

                if($package_range_end <= $company_package->homes_added){
                    array_push($disable_btn, $company_charge['package_type']);
                }
                
            }
            $cur_date = date('Y-m-d',strtotime($current_date));
            $expiry_date = date('Y-m-d',strtotime('+1 day',strtotime($company_package->expiry_date)));
            if($cur_date == $expiry_date ){
                array_push($disable_btn, $company_package->package_type);
            }
        }else{
            $company_package    = '';
            $disable_btn        = '';
        }
        // echo "<pre>"; print_r($disable_btn); die;
        $current_date   = Carbon::now();
        $sa_home_query  = DB::table('home')->where('admin_id',$system_admin_id)->select('id','admin_id','title','image')->where('is_deleted','0');
        $search = '';
        
        if(isset($request->limit)) {
            $limit = $request->limit;
            Session::put('page_record_limit',$limit);
        } 
        else{
            if(Session::has('page_record_limit')) {
                $limit = Session::get('page_record_limit');
            } else{
                $limit = 20;
            }
        }

        if(isset($request->search)) {
            $search         = trim($request->search);
            $sa_home_query  = $sa_home_query->where('title','like','%'.$search.'%');
        }

        /*if($limit == 'all') {
            $users = $users_query->get();
        } else{
            $users = $users_query->paginate($limit);
        }*/

        $system_admins = $sa_home_query->paginate($limit);
        $admin_card_detail = AdminCardDetail::where('admin_id',$system_admin_id)->first();
        //$users = DB::table('user')->select('id','name','user_name', 'email', 'access_level')->paginate(25);
        $page = 'system-admins';
       	return view('backEnd/superAdmin/home/homes', compact('page','limit','system_admins','system_admin_id','search','company_charges','company_package','current_date','disable_btn','admin_card_detail','states')); //users.blade.php
    }

    public function add(Request $request, $system_admin_id) { 
      	
        if($request->isMethod('post')) {
            $admin  = Session::get('scitsAdminSession');
            $data   = $request->input();
    	    $system_admin_home                              = new Home;
            $system_admin_home->admin_id                    = $system_admin_id;
            $system_admin_home->title                       = $data['title'];
            $system_admin_home->address                     = $data['address'];
            $system_admin_home->location_history_duration   = $data['location_history_duration'];
            $system_admin_home->is_registered               = $data['is_registered'];
            $system_admin_home->rota_time_format            = $data['rota_time_format'];
            
            if(!empty($_FILES['image']['name'])) {
                $tmp_image  =   $_FILES['image']['tmp_name'];
                $image_info =   pathinfo($_FILES['image']['name']);
                $ext        =   strtolower($image_info['extension']);
                $new_name   =   time().'.'.$ext; 
               
                if($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png')
                {
                    $destination = base_path().homebasePath; 
                    if(move_uploaded_file($tmp_image, $destination.'/'.$new_name)){
                        $system_admin_home->image = $new_name;
                    }
                }
            }

            
            if(!isset($system_admin_home->image)) {
                $system_admin_home->image = '';
            }

    		if($system_admin_home->save()) {
                $update_company_payment = CompanyPayment::where('admin_id',$system_admin_id)
                                                        ->increment('homes_added');
                if($update_company_payment){
                    return redirect('admin/system-admin/homes/'.$system_admin_id)->with('success', 'home added successfully.');
                }else{
                    return redirect()->back()->with('error', 'Some error occurred. Please try after sometime.');
                }
      		}  
            else {
    			return redirect()->back()->with('error', 'Some error occurred. Please try after sometime.');
    		}
        }

        $page = 'system-admins';
        return view('backEnd/superAdmin/home/home_form', compact('page', 'system_admin_id'));
    }
   			
   	public function edit(Request $request, $id) { 

        if(!Session::has('scitsAdminSession'))
        {   
            return redirect('admin/login');
        }

        $system_admin_home = Home::find($id);
        if(!empty($system_admin_home)) {
            $system_admin_id = $system_admin_home->admin_id;
            if($request->isMethod('post'))
            {
                $home_old_image                               = $system_admin_home->image;
                //$home_old_policy                              = $system_admin_home->security_policy;
                $system_admin_home->title                     = $request->title;
                $system_admin_home->address                   = $request->address;
                $system_admin_home->location_history_duration = $request->location_history_duration;
                $system_admin_home->rota_time_format          = $request->rota_time_format;
                //$system_admin_home->image            = $request->image;
                
                /*if(!empty($request->password))
                {
                    $user->password   = Hash::make($request->password);
                }*/

                if(!empty($_FILES['image']['name']))
                {
                    $tmp_image  =   $_FILES['image']['tmp_name'];
                    $image_info =   pathinfo($_FILES['image']['name']);
                    $ext        =   strtolower($image_info['extension']);
                    $new_name   =   time().'.'.$ext; 
                    if($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png')
                    {
                        $destination = base_path().homebasePath; 
                        if(move_uploaded_file($tmp_image, $destination.'/'.$new_name))
                        {
                            if(!empty($home_old_image))
                            { 
                                if(file_exists($destination.'/'.$home_old_image))
                                {
                                    unlink($destination.'/'.$home_old_image);
                                }
                            }
                            $system_admin_home->image = $new_name;
                        }
                    }
                }
                
               if($system_admin_home->save())
               {
                   return redirect('admin/system-admin/homes/'.$system_admin_id)->with('success','Home Updated successfully.'); 
               } 
               else
               {
                   return redirect()->back()->with('error','Home could not be Updated.'); 
               }  
            }
        }

       	$system_admin_home = DB::table('home')
                    ->where('id', $id)
                    ->where('is_deleted','0')
                    ->first();
        $page = 'system-admins';
        return view('backEnd/superAdmin/home/home_form', compact('system_admin_home','page', 'system_admin_id'));
    }

    public function delete($home_id) {
       if(!empty($home_id)) {
            $updated = DB::table('home')->where('id', $home_id)->update(['is_deleted' => '1']);

            if(!empty($updated)) {
                return redirect()->back()->with('undo','<a href="'.url('/admin/system-admin/home/undo-delete/'.$home_id).' " class="undo"><strong>Undo</strong></a> Home Deleted Successfully.'); 
                // return redirect('admin/system_admins/homes/'.$system_admin_id)->with('success','Home deleted Successfully.'); 
            } else{
                return redirect('admin/homelist')->with('error', 'Some error occurred. Please try after sometime.'); 
            }
        } else {
            return redirect('admin/')->with('error','Home does not exists.');
        }
    }

    public function undo_delete($home_id) {
        if(!empty($home_id)) {
            $undo_home = DB::table('home')->where('id', $home_id)->update(['is_deleted' => '0']);

            if(!empty($undo_home)) {
                return redirect('admin/homelist')->with('success','Deleted home undo Successfully.'); 
            } else {
                return redirect('admin/homelist')->with('error', 'Some error occurred. Please try after sometime.'); 
            }
       
        } else {
            return redirect('admin/')->with('error','Home does not exists.');
        }
    }

    /*public function check_user_email_exists(Request $request)
    {

        $count = DB::table('user')->where('email',$request->email)->count();
        if($count > 0)
        {
            echo '{"valid":false}';die;
        }    
        else
        {
            echo '{"valid":true}';die;
        }    
    }
   
    public function check_user_edit_email_exists(Request $request)
    {
       
        $count = DB::table('user')->where('email',$request->email)->count();
        if($count > 1)
        {
            echo '{"valid":false}';die;
        }    
        else{
            echo '{"valid":true}';die;
        }    
    }

    public function send_user_set_pass_link_mail(Request $request, $user_id = NULL)
    {
        $response = User::sendCredentials($user_id);
        echo $response; die;
    }*/
    
    public function company_package_type(Request $request){

        // echo "<pre>"; print_r($request->input()); die;
        $data = $request->input();
        if(!empty($data['card_holder_name'])){
            $admin_card_detail                   = new AdminCardDetail;
            $admin_card_detail->admin_id         = $data['system_admin_id'];
            $admin_card_detail->admin_type       = 'S';
            $admin_card_detail->card_holder_name = $data['card_holder_name'];
            $admin_card_detail->card_number      = $data['card_number'];
            $admin_card_detail->mm_yy            = $data['mm_yy'];
            $admin_card_detail->cvv              = $data['cvv'];
            $admin_card_detail->save();
        }

        $package_duration   = $request->package_duration;
        $company_charges_id = $request->company_charges_id;
        $system_admin_id    = $request->system_admin_id;
        $cmpny_email        = Admin::where('id',$system_admin_id)
                                    ->where('is_deleted','0')
                                    ->value('email');
        
        if($package_duration == 'M' && $company_charges_id != '1'){ // Monthly/yearly package
            $expiry_date = Carbon::now()->addMonths(1);
        }elseif($package_duration == 'Y' && $company_charges_id != '1'){
            $expiry_date = Carbon::now()->addMonths(12);
        }

        if($company_charges_id == '1'){ // Free Trial days
            $free_days = CompanyCharges::where('id','1')
                                        ->value('days');
            $expiry_date = Carbon::now()->addDays($free_days);
        }

        if($request->company_charges_id != '1'){
            $package_info = CompanyCharges::where('id',$request->company_charges_id)
                                        ->first();
            if(!empty($package_info)){
                if($request->package_duration == 'M'){
                    $amount = $package_info->price_monthly;
                }else{
                    $amount = $package_info->price_yearly;
                }
            }


            $payment_resp = app(\App\Http\Controllers\backEnd\superAdmin\PaymentController::class)->index($system_admin_id,$amount,$company_charges_id,$expiry_date,$package_duration);
            if($payment_resp == 'true'){
                return redirect('admin/dashboard')->with('success','Payment paid successfully. Next payment will be deducted automatically.');
            }else{
                return redirect('admin/dashboard')->with('error',COMMON_ERROR);
            }
            
        }else{
            $company_payment_id = CompanyPayment::where('admin_id',$system_admin_id)
                                                ->value('id');

            if($company_payment_id != ''){
                $update_company_payment = CompanyPayment::where('id',$company_payment_id)
                                                        ->update([
                                                                'company_charges_id'=>$company_charges_id,
                                                                'package_duration'=>$package_duration,
                                                                'expiry_date'=>$expiry_date,
                                                                'pay_key'=>'',
                                                                'status'=>'1',
                                                                'free_trial_done'=>'1'
                                                            ]);
                if($update_company_payment){

                    $save_package_dtl                           = new CompanyPaymentInformation;
                    $save_package_dtl->company_charges_id       = $company_charges_id;
                    $save_package_dtl->admin_id                 = $system_admin_id;
                    $save_package_dtl->paid_amount              = '0';
                    $save_package_dtl->sender_transaction_id    = '';
                    $save_package_dtl->expiry_date              = $expiry_date;
                    if($save_package_dtl->save()){
                        echo "1"; 
                    }else{
                        echo "2"; 
                        
                    }
                }else{
                    echo "2"; 
                    
                }
            }else{
                $company_payment                        = new CompanyPayment;
                $company_payment->admin_id              = $system_admin_id;
                $company_payment->company_charges_id    = $company_charges_id;
                $company_payment->package_duration      = $package_duration;
                $company_payment->expiry_date           = $expiry_date;
                $company_payment->status                = '1';
                $company_payment->pay_key               = '';
                $company_payment->free_trial_done       = '1';
                if($company_payment->save()){

                    $save_package_dtl                           = new CompanyPaymentInformation;
                    $save_package_dtl->company_charges_id       = $company_charges_id;
                    $save_package_dtl->admin_id                 = $system_admin_id;
                    $save_package_dtl->paid_amount              = '0';
                    $save_package_dtl->sender_transaction_id    = '';
                    $save_package_dtl->expiry_date              = $expiry_date;
                    if($save_package_dtl->save()){
                        echo "1"; 
                    }else{
                        echo "2"; 
                        
                    }
                }else{
                    echo "2"; 
                    
                }
            }

           
        }         
    }

    /*public function company_package_type(Request $request){

        $data = $request->input();
        return view('backEnd.superAdmin.home.user_detail', compact('data','page','system_admin_id'));
    }*/ 
    //Not Used
    function success($system_admin_id = null){
        
        $package_info   = CompanyPayment::where('admin_id',$system_admin_id)->first();
        $str_req        = "payKey=".$package_info->pay_key."&requestEnvelope.errorLanguage=en_US";
     
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://svcs.sandbox.paypal.com/AdaptivePayments/PaymentDetails");
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getInvoiceAPIHeader());   

        curl_setopt($ch, CURLOPT_HEADER, 1); // tells curl to include headers in response, use for testing 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, TRUE);

        // setting the NVP $my_api_str as POST FIELD to curl
        $log_req = $aryRresponse = explode("&", $str_req);
       
        curl_setopt($ch, CURLOPT_POSTFIELDS, $str_req);

        // getting response from server
        $httpResponse = curl_exec($ch);
        
        // var_dump($httpResponse); die;
        $test = array_values(explode("&",$httpResponse));
        // echo "<pre>"; print_r($test); die;
        if(!empty($test)){
            
            $test_re                            = explode("responseEnvelope.ack=", $test[1]);
            $paid_amount                        = explode("=",@$test[6]);
            $receiver_email                     = explode("=",@$test[7]);
            $sender_transaction_id              = explode("=",@$test[12]);
            $sender_email                       = explode("=",@$test[16]);
            $final_pay_key                      = explode("=",@$test[18]);
            $sender_account_id                  = explode("=",@$test[23]);
            
            $data['paid_amount']                = @$paid_amount[1];
            $data['receiver_email']             = @$receiver_email[1];
            $data['sender_transaction_id']      = @$sender_transaction_id[1];
            $data['sender_email']               = @$sender_email[1];
            $data['final_pay_key']              = @$final_pay_key[1];
            $data['sender_account_id']          = @$sender_account_id[1];

            if($test_re[1]== "Success"){
                
                $paymentInfo                             = new CompanyPaymentInformation;
                $paymentInfo->company_payment_id         = $package_info->id;
                $paymentInfo->paid_amount                = $data['paid_amount'];
                $paymentInfo->receiver_email             = $data['receiver_email'];
                $paymentInfo->sender_transaction_id      = $data['sender_transaction_id'];
                $paymentInfo->sender_email               = $data['sender_email'];
                $paymentInfo->final_pay_key              = $data['final_pay_key'];
                $paymentInfo->sender_account_id          = $data['sender_account_id'];
                
                if($paymentInfo->save()){
                    //update status
                    $update_company_payment = CompanyPayment::where('admin_id',$system_admin_id)
                                                            ->update(['status'=>'1']);
                    if($update_company_payment){
                        return redirect('admin/system-admin/homes/add/'.$system_admin_id)->with('success','Your payment paid successfully.');
                    }else{
                        return redirect()->back()->with('error',COMMON_ERROR);
                    }
                }else{
                    return redirect()->back()->with('error',COMMON_ERROR);
                }
            }else{
                return redirect()->back()->with('error',COMMON_ERROR);
            }
        }
    }
    //Not Used
    function getInvoiceAPIHeader(){

        global $API_Username, $API_Password, $Signature;
        $API_Username    = 'promatics.hashishgarg-facilitator_api1.gmail.com';
        $API_Password    = '4DMJ26SNXMD6RVCL';
        $Signature       = 'Ao68DNqlX5gVaPZlGVYk.BmBnaqJAKRQa8wtH5yrtwKwLKkCuupfUpHQ';
        //$API_Username  = 'gagandeep.sethi-facilitator_api1.promaticsindia.com';
        //$API_Password  = 'LYK3VM9ZFU2Q6F77';
        //$Signature     = 'AFcWxV21C7fd0v3bYYYRCpSSRl31A.-Iup1vRL4IeShIeQPfsT09PTFo';
        $headers[0]     = "Content-Type: text/namevalue";              // either text/namevalue or text/xml
        $headers[1]     = "X-PAYPAL-SECURITY-USERID: $API_Username";   // API user
        $headers[2]     = "X-PAYPAL-SECURITY-PASSWORD: $API_Password"; // API PWD
        $headers[3]     = "X-PAYPAL-SECURITY-SIGNATURE: $Signature";   // API Sig
        $headers[4]     = "X-PAYPAL-APPLICATION-ID: APP-80W284485P519543T"; //APP ID        
        $headers[6]     = "X-PAYPAL-REQUEST-DATA-FORMAT: NV";   // Set Name Value Request Format
        $headers[7]     = "X-PAYPAL-RESPONSE-DATA-FORMAT: NV"; // Set Name Value Response Format
        // Debugger::dump($headers);
        return $headers;
    }

    public function card_detail_save(Request $request){
        // echo "<pre>"; print_r($request->input()); die;
        $data = $request->input();
        if(!empty($data)){
            $admin_card_detail                   = new AdminCardDetail;
            $admin_card_detail->admin_id         = $data['system_admin_id'];
            $admin_card_detail->admin_type       = 'S';
            $admin_card_detail->card_holder_name = $data['card_holder_name'];
            $admin_card_detail->card_number      = $data['card_number'];
            $admin_card_detail->mm_yy            = $data['card_expiry_date'];
            $admin_card_detail->cvv              = $data['cvv'];
            if($admin_card_detail->save()){
                echo "1"; 
            }else{
                echo "2"; 
            }
        }
    }

    public function getHomeList(Request $request){
        $data = Home::getHomeFromConpany($request->companyId);
        if ($data) {
            return response()->json(['success' => true, 'data' => $data]);
        } else {
            return response()->json(['success' => false, 'Data' => 'No Data']);
        }
    }

}
