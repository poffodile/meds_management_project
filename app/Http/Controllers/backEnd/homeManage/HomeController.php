<?php

namespace App\Http\Controllers\backEnd\homeManage;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use App\Home, App\CompanyCharges, App\CompanyPayment, App\Admin;
use DB;
use Hash;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index(Request $request)
    {

        $admin_id           = Session::get('scitsAdminSession')->id;
        $access_type        = Session::get('scitsAdminSession')->access_type;
        $company_charges    = CompanyCharges::select('company_charges.*')
            ->get()->toArray();
        $company_package    = CompanyPayment::select('company_payment.admin_id', 'company_payment.homes_added', 'company_payment.expiry_date', 'company_charges.home_range', 'company_payment.status', 'company_payment.free_trial_done', 'company_charges.package_type')
            ->join('company_charges', 'company_charges.id', 'company_payment.company_charges_id')
            ->where('company_payment.admin_id', $admin_id)
            /*->where('status','!=','0')*/ // payment not pending
            ->first();
        $current_date = Carbon::now();
        if ($company_package != '') {
            $company_package    = json_decode(json_encode($company_package));
            $disable_btn        = array(); // package with, out of home range
            foreach ($company_charges as $company_charge) {
                $package_range      = $company_charge['home_range'];
                $package_range      = explode('-', $package_range);
                $package_range_end  = $package_range['1'];

                if ($package_range_end <= $company_package->homes_added) {
                    array_push($disable_btn, $company_charge['package_type']);
                }
            }
            $cur_date = date('Y-m-d', strtotime($current_date));
            $expiry_date = date('Y-m-d', strtotime('+1 day', strtotime($company_package->expiry_date)));
            if ($cur_date == $expiry_date) {
                array_push($disable_btn, $company_package->package_type);
            }
        } else {
            $company_package    = '';
            $disable_btn        = '';
        }


        // ECHO "<pre>"; print_r($company_package); die;
        //if($super_admin == 1){
        if ($access_type != 'O') {
            return redirect()->back();
        }

        $homelist_query = DB::table('home')->select('id', 'title', 'admin_id', 'image')->where('is_deleted', '0')->where('admin_id', $admin_id);
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
            $search         = trim($request->search);
            $homelist_query = $homelist_query->where('title', 'like', '%' . $search . '%');
        }

        /*if($limit == 'all') {
            $users = $users_query->get();
        } else{
            $users = $users_query->paginate($limit);
        }*/
        $homelist = $homelist_query->paginate($limit);
        //$users = DB::table('user')->select('id','name','user_name', 'email', 'access_level')->paginate(25);
        $page = 'homelist';
        return view('backEnd/homeManage/home/homelist', compact('page', 'limit', 'homelist', 'search', 'company_charges', 'company_package', 'current_date', 'disable_btn', 'admin_id')); //users.blade.php
    }

    public function add(Request $request)
    {
        $admin_id = Session::get('scitsAdminSession')->id;
        $access_type = Session::get('scitsAdminSession')->access_type;
        if ($access_type != 'O') {
            return redirect()->back();
        }

        if ($request->isMethod('post')) {
            // echo "<pre>"; print_r($_FILES); die;

            $admin = Session::get('scitsAdminSession');

            $homelist                   = new Home;
            $homelist->admin_id         = $admin->id;
            $homelist->title            = $request->title;
            $homelist->address          = $request->address;
            $homelist->location_history_duration = $request->location_history_duration;
            $homelist->rota_time_format          = $request->rota_time_format;

            if (!empty($_FILES['image']['name'])) {
                $tmp_image  =   $_FILES['image']['tmp_name'];
                $image_info =   pathinfo($_FILES['image']['name']);
                $ext        =   strtolower($image_info['extension']);
                $new_name   =   time() . '.' . $ext;

                if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png') {
                    $destination = base_path() . homebasePath;
                    if (move_uploaded_file($tmp_image, $destination . '/' . $new_name))
                        $homelist->image = $new_name;
                }
            }

            if ($homelist->save()) {
                // Update home_id in user table for the Owner/System Admin
                $owner_user = \App\User::where('admn_id', $admin_id)->where('user_type', 'O')->first();
                if (!empty($owner_user)) {
                    $raw_home_ids = $owner_user->getAttributes()['home_id'] ?? '';
                    $existing_homes = array_filter(explode(',', $raw_home_ids));
                    if (!in_array($homelist->id, $existing_homes)) {
                        $existing_homes[] = $homelist->id;
                        $owner_user->home_id = implode(',', $existing_homes);
                        $owner_user->save();
                    }
                }
                $update_company_payment = CompanyPayment::where('admin_id', $admin_id)
                    ->increment('homes_added');
                if ($update_company_payment) {
                    return redirect('admin/homelist')->with('success', 'Home added successfully.');
                } else {
                    return redirect()->back()->with('error', 'Some error occurred. Please try after sometime.');
                }
            } else {
                return redirect()->back()->with('error', 'Some error occurred. Please try after sometime.');
            }
        }
        $page = 'homelist';
        return view('backEnd/homeManage/home/homelist_form', compact('page'));
    }

    public function edit(Request $request, $home_id)
    {

        $admin_id = Session::get('scitsAdminSession')->id;
        $access_type = Session::get('scitsAdminSession')->access_type;
        if ($access_type != 'O') {
            return redirect()->back();
        }

        if ($request->isMethod('post')) {
            $homelist = Home::find($home_id);
            if (!empty($homelist)) {
                $home_old_image                         = $homelist->image;
                //$home_old_policy                        = $homelist->security_policy;
                $homelist->title                        = $request->title;
                $homelist->address                      = $request->address;
                $homelist->location_history_duration    = $request->location_history_duration;
                $homelist->rota_time_format             = $request->rota_time_format;

                if (!empty($_FILES['image']['name'])) {
                    $tmp_image  =   $_FILES['image']['tmp_name'];
                    $image_info =   pathinfo($_FILES['image']['name']);
                    $ext        =   strtolower($image_info['extension']);
                    $new_name   =   time() . '.' . $ext;

                    if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png') {
                        $destination = base_path() . homebasePath;
                        if (move_uploaded_file($tmp_image, $destination . '/' . $new_name)) {
                            if (!empty($home_old_image)) {   //echo $destination.'/'.$home_old_image; die;
                                if (file_exists($destination . '/' . $home_old_image)) {
                                    unlink($destination . '/' . $home_old_image);
                                }
                            }
                            $homelist->image = $new_name;
                        }
                    }
                }

                if ($homelist->save()) {
                    $home_id = $homelist->id;
                    return redirect('admin/homelist')->with('success', 'Home Updated successfully.');
                } else {
                    return redirect()->back()->with('error', 'Home could not be Updated.');
                }
            }
        }

        $homelist = DB::table('home')
            ->where('id', $home_id)
            ->first();
        $page = 'homelist';
        return view('backEnd/homeManage/home/homelist_form', compact('homelist', 'page'));
    }

    /*    public function delete($home_id)
    {
       if(!empty($home_id))
       {
        DB::table('home')->where('id',$home_id)->delete();

        // return redirect('admin/daily-record')->with('success','Record deleted Successfully.'); 
        return redirect()->back()->with('success','Home deleted Successfully.'); 
       }
    }*/

    public function delete($home_id)
    {
        $admin_id = Session::get('scitsAdminSession')->id;
        $access_type = Session::get('scitsAdminSession')->access_type;
        if ($access_type != 'O') {
            return redirect()->back();
        }

        if (!empty($home_id)) {
            $updated = DB::table('home')->where('id', $home_id)->update(['is_deleted' => '1']);

            if ($updated) {
                return redirect()->back()->with('undo', '<a href="' . url('/admin/homelist/undo-delete/' . $home_id) . ' " class="undo"><strong>Undo</strong></a> Home Deleted Successfully.');
            } else {
                return redirect('admin/homelist')->with('error', 'Some error occurred. Please try after sometime.');
            }
        }
    }
    // public function check_user_email_exists(Request $request)
    // {

    //     $count = DB::table('user')->where('email',$request->email)->count();
    //     if($count > 0)
    //     {
    //         echo '{"valid":false}';die;
    //     }    
    //     else
    //     {
    //         echo '{"valid":true}';die;
    //     }    
    // }

    public function undo_delete($home_id)
    {
        $admin_id       = Session::get('scitsAdminSession')->id;
        $access_type    = Session::get('scitsAdminSession')->access_type;
        if ($access_type != 'O') {
            return redirect()->back();
        }

        if (!empty($home_id)) {
            $undo_home = DB::table('home')->where('id', $home_id)->update(['is_deleted' => '0']);

            if ($undo_home) {
                return redirect('admin/homelist')->with('success', 'Deleted home undo Successfully.');
            } else {
                return redirect('admin/homelist')->with('error', 'Some error occurred. Please try after sometime.');
            }
        }
    }

    public function company_package_type(Request $request)
    {
        echo "<pre>";
        print_r($request->input());
        die;
        $package_duration   = $request->package_duration;
        $company_charges_id = $request->company_charges_id;
        $admin_id           = Session::get('scitsAdminSession')->id;
        $cmpny_email        = Admin::where('id', $admin_id)
            ->where('is_deleted', '0')
            ->value('email');

        // Initially make the payment status pending
        $update_company_payment = CompanyPayment::where('admin_id', $admin_id)
            ->update(['status' => '0']);

        if ($package_duration == 'M' && $company_charges_id != '1') { // Monthly/yearly package
            $expiry_date = Carbon::now()->addMonths(1);
        } elseif ($package_duration == 'Y' && $company_charges_id != '1') {
            $expiry_date = Carbon::now()->addMonths(12);
        }

        if ($company_charges_id == '1') { // Free Trial days
            $free_days = CompanyCharges::where('id', '1')
                ->value('days');
            $expiry_date = Carbon::now()->addDays($free_days);
        }

        if ($request->company_charges_id != '1') {

            //redirect to payment gateway Start
            $package_info = CompanyCharges::where('id', $request->company_charges_id)
                ->first();
            if (!empty($package_info)) {

                if ($request->package_duration == 'M') {
                    $amount = $package_info->price_monthly;
                } else {
                    $amount = $package_info->price_yearly;
                }
            }

            $payment_resp = app(\App\Http\Controllers\backEnd\superAdmin\PaymentController::class)->index($admin_id, $amount, $company_charges_id, $expiry_date, $package_duration);
            if ($payment_resp == 'true') {
                return redirect('admin/dashboard')->with('success', 'Payment paid successfully. Next payment will be deducted automatically.');
            } else {
                return redirect('admin/dashboard')->with('error', COMMON_ERROR);
            }
            // $return_url = url('admin/homelist/add');
            // $cancel_url = url('admin/homelist');

            // $str_req = "actionType=PAY&currencyCode=USD&receiverList.receiver.amount=$amount&receiverList.receiver.email=$cmpny_email&returnUrl=$return_url&cancelUrl=$cancel_url&requestEnvelope.errorLanguage=en_US&requestEnvelope.detailLevel=ReturnAll";

            // $ch = curl_init();
            // curl_setopt($ch, CURLOPT_URL, "https://svcs.sandbox.paypal.com/AdaptivePayments/Pay");
            // curl_setopt($ch, CURLOPT_VERBOSE, 1);
            // curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            // curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getInvoiceAPIHeader());
            // curl_setopt($ch, CURLOPT_HEADER, 1); // tells curl to include headers in response, use for testing
            // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            // curl_setopt($ch, CURLOPT_POST, TRUE);

            // // setting the NVP $my_api_str as POST FIELD to curl
            // if(!empty($str_req)){
            //     $log_req = $aryRresponse = explode("&", $str_req);

            //     curl_setopt($ch, CURLOPT_POSTFIELDS, $str_req);

            //     // getting response from server
            //     $httpResponse   = curl_exec($ch);
            //     //var_dump($httpResponse);
            //     $aryRresponse   = explode("&", $httpResponse);
            //     $response       = explode("&",$httpResponse);
            //     // echo "<pre>"; print_r($response); die;
            //     if(!empty($response)){
            //         $re = explode("responseEnvelope.ack=", $response[1]);
            //         $re4 = explode('payKey=', $response[4]);

            //         if($re[1]=="Success"){
            //             // echo "111"; die;
            //             $company_payment_id = CompanyPayment::where('admin_id',$admin_id)
            //                                                 ->value('id');
            //             if($company_payment_id != ''){
            //                 $update_company_payment = CompanyPayment::where('id',$company_payment_id)
            //                                                         ->update([
            //                                                                 'company_charges_id'=>$company_charges_id,
            //                                                                 'package_duration'=>$package_duration,
            //                                                                 'expiry_date'=>$expiry_date,
            //                                                                 'pay_key'=>$re4[1]
            //                                                             ]);
            //                 if($update_company_payment){
            //                     // return redirect('admin/system-admin/homes/add/'.$admin_id)->with('success','Payment received successfully.');
            //                     return redirect('https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_ap-payment&paykey='.$re4[1]);
            //                 }else{
            //                     return redirect()->back()->with('error',COMMON_ERROR);
            //                 }
            //             }else{

            //                 $company_payment                        = new CompanyPayment;
            //                 $company_payment->admin_id              = $admin_id;
            //                 $company_payment->company_charges_id    = $company_charges_id;
            //                 $company_payment->package_duration      = $package_duration;
            //                 $company_payment->expiry_date           = $expiry_date;
            //                 $company_payment->pay_key               = $re4[1];

            //                 if($company_payment->save()){
            //                     return redirect('https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_ap-payment&paykey='.$re4[1]);
            //                 }else{
            //                     return redirect()->back()->with('error',COMMON_ERROR);
            //                 } 
            //             }
            //         }else{
            //             return redirect()->back()->with('error',COMMON_ERROR);
            //         }
            //     }else{
            //         return redirect()->back()->with('error',COMMON_ERROR);
            //     }
            //} //redirect to payment gateway End
        } else {
            $company_payment                        = new CompanyPayment;
            $company_payment->admin_id              = $admin_id;
            $company_payment->company_charges_id    = $company_charges_id;
            $company_payment->package_duration      = $package_duration;
            $company_payment->expiry_date           = $expiry_date;
            $company_payment->status                = '1';
            $company_payment->pay_key               = '';
            $company_payment->free_trial_done       = '1';

            if ($company_payment->save()) {

                $save_package_dtl                           = new CompanyPaymentInformation;
                $save_package_dtl->company_charges_id       = $company_charges_id;
                $save_package_dtl->admin_id                 = $admin_id;
                $save_package_dtl->paid_amount              = '0';
                $save_package_dtl->sender_transaction_id    = '';
                $save_package_dtl->expiry_date              = $expiry_date;
                if ($save_package_dtl->save()) {
                    return redirect('admin/homelist/add')->with('success', 'Free trial period started successfully.');
                } else {
                    return redirect()->back()->with('error', COMMON_ERROR);
                }
            } else {
                return redirect()->back()->with('error', COMMON_ERROR);
            }
        }
    }
    function getInvoiceAPIHeader()
    {

        global $API_Username, $API_Password, $Signature;
        $API_Username    = 'promatics.hashishgarg-facilitator_api1.gmail.com';
        $API_Password    = '4DMJ26SNXMD6RVCL';
        $Signature       = 'Ao68DNqlX5gVaPZlGVYk.BmBnaqJAKRQa8wtH5yrtwKwLKkCuupfUpHQ';
        // $API_Username   = 'gagandeep.sethi-facilitator_api1.promaticsindia.com';
        // $API_Password   = 'LYK3VM9ZFU2Q6F77';
        // $Signature      = 'AFcWxV21C7fd0v3bYYYRCpSSRl31A.-Iup1vRL4IeShIeQPfsT09PTFo';
        $headers[0]     = "Content-Type: text/namevalue";              // either text/namevalue or text/xml
        $headers[1]     = "X-PAYPAL-SECURITY-USERID: $API_Username";   //API user
        $headers[2]     = "X-PAYPAL-SECURITY-PASSWORD: $API_Password"; //API PWD
        $headers[3]     = "X-PAYPAL-SECURITY-SIGNATURE: $Signature";   //API Sig
        $headers[4]     = "X-PAYPAL-APPLICATION-ID: APP-80W284485P519543T"; //APP ID        
        $headers[6]     = "X-PAYPAL-REQUEST-DATA-FORMAT: NV";   //Set Name Value Request Format
        $headers[7]     = "X-PAYPAL-RESPONSE-DATA-FORMAT: NV"; //Set Name Value Response Format
        // Debugger::dump($headers);
        return $headers;
    }
}
