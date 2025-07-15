<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
//use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route;
use App\AccessRight, App\User;
use Session;
//use Carbon\Carbon;

class checkUserAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        //get entered url        
        $path = $request->path();
        // print_r($path); die;
        //checking current session for one user logged in at one time
        if(Auth::check()){
            $current_token = csrf_token();
            // print_r($current_token); 
            // echo "<br>";
            $saved_token = Auth::user()->session_token;
            // print_r($current_token); die;
            if($current_token != $saved_token){
                echo 'session_expired';
                Auth::logout();
                return redirect('/')->with('success','Your sesion has been expired');
            }
        }

        if (!Auth::check()) {
            if($request->ajax()){
                echo 'logged_out'; die;
            }
            
            // if this is bug report case then do not redirect to login
            if(strpos('bug-report', $path) !== false) {
                return true;    
            } else{      
                return redirect('/login');
            }
        } else{

            // if user is logged in 

            //check lockscreen button is not pressed
            if(Session::has('LOCKED')) {
                if($request->ajax()){
                    echo json_encode('locked');
                    die;    
                } else{
                    return redirect('/lockscreen');
                }
            }
            // print_r(Session::has('LAST_ACTIVITY')); die;

            //check user last activity time and redirect to lockscreen if it is delayed more than 30 sec.
            if(Session::has('LAST_ACTIVITY')) { 
                $time_diff = time() - Session::get('LAST_ACTIVITY');
                //echo LOCK_TIME; die;

                //checks is ideal time more than the automatically set locked time.
                if($time_diff > LOCK_TIME){ //in seconds
                    if($request->ajax()){
                        echo json_encode('locked');
                        die;    
                    }
                    //if it is <a href> case then save the current path for future use
                    $pre_path = $request->path();
                    // Session::set('PREVIOUS_PATH',$pre_path);
                    Session::put('PREVIOUS_PATH', $pre_path);
                    return redirect('/lockscreen');
                } 
            }
            Session::put('LAST_ACTIVITY',time());
            User::updateUserLastActivityTime();
            
            //check if user has permission to access this page.
            if($path != '/'){
                
                $path = preg_replace('/\d/', '', $path);
                // print_r($path); die;
                //paths that does not need permssions
                $allowed_path = array('send-modify-request','bug-report','bug-report/add','notif/response','ajax.getCountriesList','bulk_delete','getAllSupplierPurchaseOrder','purchase/getSupplierData','purchase/purchase-daybook/data','getTaxRate','purchase/reclaimPercantage','purchase/purchase-day-book-reclaim-per','purchase/getPurchaseExpense','sales/get-sales-day-book/data','customers/getCustomerList','sales-finance/assets/asset-register-search','petty-cash/getAllExpendCash','petty-cash/cash_filter','find_project','expense_image_delete','find_job','find_appointment','searchExpenses','searchCustomerName','get_supplier_details','lead/getCountriesList','get_customer_details_front','getCustomerSiteDetails','result_product_calculation','vat_tax_details','item/searchProduct','getAllAttachmens','getAllNewTaskList','delete_po_attachment','searchPurchase_qoute_ref','searchPurchase_job_ref','getAllPurchaseInvoices','getAllPaymentPaids','paymentPaidDelete','savePurchaseOrderRecordPayment','item/get_product_categories','item/getProductCounts','item/getProductList','purchase-orders-search','purchase-order-invoices','purchase-order-statements','customers/getCustomerSiteDetails','getTags','invoices/getAllInvoiceNewTaskList','invoices/customer_visibleUpdate','/invoices/delete_invoice_reminder','invoices/mobile_user_visibleUpdate','invoices/invoice_attachmentSave','invoices/getInvoiceAllAttachmens','invoices/new_task_save','item/ProductGroupProductsList','item/ProductCataloguePriceList','item/getProductFromId','item/ProductCataloguePriceDelete','lead/getUserList','my-profile/time-sheet','quote/getRegions','service/dynamic-form/view/pattern','service/patterndataformio','service/patterndataformiovaule','service/weekly-logs','service/monthly-logs','petty-cash/expand_card_filter','searchPurchaseOrders','searchDepartment','searchTag','searchSupplier','searchCreatedBy','searchProject','searchCreditNotes');
                //,'/general/petty_cash/check-balance'
                //if requested path is not one of them that don't need permission. then check it for permission 
                // Ram 10/06/2025 new array create to temprary for Rota Management by Abhishek sir when testing task done then we have to remove it.
                array_push($allowed_path,'rota-management','rota/staff','/rota/staff-add','/rota/staff-delete/{id}');
                // echo "<pre>";print_r($allowed_path);die;
                // end here
                if(!in_array($path, $allowed_path)) {
                    // echo $path; die;
                    $res = $this->checkPermission($path);
                    // echo json_encode($res); die;
                    // if(!$res){
                    //     if($request->ajax()){
                    //         echo json_encode('unauthorize'); die;    
                    //     }
                    //     return redirect()->back()->with("error", UNAUTHORIZE_ERR);
                    // } else {
                       
                    // }
                }            
            } 
        } 
 
        return $next($request);
    }

    function checkPermission($path){
        //return true; //by passing route check 
        // return $path;
        $user_rights = Auth::user()->access_rights;
        $user_rights = explode(',',$user_rights);
        $rights      = AccessRight::select('id','route')->whereIn('id',$user_rights)->get()->toArray();
       // echo '<pre>'; print_r( $user_rights ); die;
        foreach ($rights as $key => $right) {
            if(strpos($right['route'], $path) !== false) { 
                return true;    
            }
        }
        return false;
    }

}
