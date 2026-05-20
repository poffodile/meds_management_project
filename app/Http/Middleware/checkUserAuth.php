<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
//use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route;
use App\AccessRight, App\User;
use Session;
use Carbon\Carbon;

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
        if (Auth::check()) {
            $current_token = csrf_token();
            // print_r($current_token); 
            // echo "<br>";
            $saved_token = Auth::user()->session_token;
            // print_r($current_token); die;
            if ($current_token != $saved_token) {
                // echo 'session_expired';
                Auth::logout();
                // return redirect('/')->with('success','Your sesion has been expired');
                return redirect('/login')->with('success', 'Your sesion has been expired');
            }

            // Populate manager session if missing
            if (in_array(Auth::user()->user_type, ['M', 'CM']) && !Session::has('allowed_home_ids')) {
                $raw_home_id = \App\User::where('id', Auth::user()->id)->value('home_id');
                Session::put('allowed_home_ids', explode(',', $raw_home_id));
                if (!Session::has('active_home_id')) {
                    Session::put('active_home_id', explode(',', $raw_home_id)[0]);
                }
            }
        }

        if (!Auth::check()) {
            if ($request->ajax()) {
                echo 'logged_out';
                die;
            }

            // if this is bug report case then do not redirect to login
            if (strpos('bug-report', $path) !== false) {
                return true;
            } else {
                return redirect('/login');
            }
        } else {

            // if user is logged in 

            //check lockscreen button is not pressed
            if (Session::has('LOCKED')) {
                if ($request->ajax()) {
                    echo json_encode('locked');
                    die;
                } else {
                    return redirect('/lockscreen');
                }
            }
            // print_r(Session::has('LAST_ACTIVITY')); die;

            //check user last activity time and redirect to lockscreen if it is delayed more than 30 sec.
            if (Session::has('LAST_ACTIVITY')) {
                $time_diff = time() - Session::get('LAST_ACTIVITY');
                //echo LOCK_TIME; die;

                //checks is ideal time more than the automatically set locked time.
                if ($time_diff > LOCK_TIME) { //in seconds
                    if ($request->ajax()) {
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
            Session::put('LAST_ACTIVITY', time());
            User::updateUserLastActivityTime();

            // ================= USER ACTIVE / EXPIRY CHECK =================
            // $user = Auth::user();

            // // Auto deactivate if end date reached or passed
            // if (!empty($user->date_of_joining) && !empty($user->date_of_leaving)) {
            //     $endDate = Carbon::parse($user->date_of_leaving)->startOfDay();
            //     $today = Carbon::today();

            //     if ($today->gte($endDate)) {
            //         if ($user->status != 0) {
            //             User::where('id', $user->id)->update(['status' => 0]);
            //             $user->status = 0;
            //         }
            //     }
            // }

            // // Block inactive users
            // if ($user->status == 0) {
            //     Auth::logout();

            //     if ($request->ajax()) {
            //         return response()->json([
            //             'status' => false,
            //             'message' => 'Your account is inactive. Please contact admin.'
            //         ], 403);
            //     }

            //     return redirect('/login')
            //         ->with('error', 'Your account is inactive. Please contact admin.');
            // }
            // ================= END USER ACTIVE / EXPIRY CHECK =================

            //check if user has permission to access this page.
            if ($path != '/') {
                if (in_array(Auth::user()->user_type, ['M', 'CM'])) {
                    return $next($request);
                }

                // $path = preg_replace('/\d/', '', $path);
                // print_r($path); die;
                //paths that does not need permssions
                $allowed_path = array('send-modify-request', 'bug-report', 'bug-report/add', 'notif/response', 'ajax.getCountriesList', 'bulk_delete', 'getAllSupplierPurchaseOrder', 'purchase/getSupplierData', 'purchase/purchase-daybook/data', 'getTaxRate', 'purchase/reclaimPercantage', 'purchase/purchase-day-book-reclaim-per', 'purchase/getPurchaseExpense', 'sales/get-sales-day-book/data', 'customers/getCustomerList', 'sales-finance/assets/asset-register-search', 'petty-cash/getAllExpendCash', 'petty-cash/cash_filter', 'find_project', 'expense_image_delete', 'find_job', 'find_appointment', 'searchExpenses', 'searchCustomerName', 'get_supplier_details', 'lead/getCountriesList', 'get_customer_details_front', 'getCustomerSiteDetails', 'result_product_calculation', 'vat_tax_details', 'item/searchProduct', 'getAllAttachmens', 'getAllNewTaskList', 'delete_po_attachment', 'searchPurchase_qoute_ref', 'searchPurchase_job_ref', 'getAllPurchaseInvoices', 'getAllPaymentPaids', 'paymentPaidDelete', 'savePurchaseOrderRecordPayment', 'item/get_product_categories', 'item/getProductCounts', 'item/getProductList', 'purchase-orders-search', 'purchase-order-invoices', 'purchase-order-statements', 'customers/getCustomerSiteDetails', 'getTags', 'invoices/getAllInvoiceNewTaskList', 'invoices/customer_visibleUpdate', '/invoices/delete_invoice_reminder', 'invoices/mobile_user_visibleUpdate', 'invoices/invoice_attachmentSave', 'invoices/getInvoiceAllAttachmens', 'invoices/new_task_save', 'item/ProductGroupProductsList', 'item/ProductCataloguePriceList', 'item/getProductFromId', 'item/ProductCataloguePriceDelete', 'lead/getUserList', 'my-profile/time-sheet', 'quote/getRegions', 'service/dynamic-form/view/pattern', 'service/patterndataformio', 'service/patterndataformiovaule', 'service/weekly-logs', 'service/monthly-logs', 'petty-cash/expand_card_filter', 'searchPurchaseOrders', 'searchDepartment', 'searchTag', 'searchSupplier', 'searchCreatedBy', 'searchProject', 'searchCreditNotes', 'add-leave', 'pending-request', 'get_all_rota_data', 'roster/child-courses/', 'roster/client-search', '/roster/carer/get-hourly-rate', 'roster/daily-log-loadData', 'roster/supervision-management/fetch_supervision_list', '/roster/incident-report-loadData', 'roster/carer/get-shift-carer/', 'roster/client/medication-log-list', 'roster/client/care-task-list', 'roster/care-task-save', 'roster/carer-availability/loadData', 'roster/carer-availability/details', 'roster/carer-availability/overview', 'roster/carer-availability/unavailability/loadData', 'roster/client/alert-type', 'roster/get-carer-shifts', 'roster/client/alert-increase-acknowledge', 'roster/client/alert-resolve', 'roster/client/alert-archived', 'roster/client/dols-list', 'roster/client/save-dols', 'roster/carer-availability/load-working-hours', 'roster/schedule-shift/assign-shift', 'roster/client/care-plan-get-list', 'roster/client/care-plan-save', 'roster/client/care-plan-details', 'roster/client/care-plan-objective-delete', 'roster/client/care-plan-task-delete', 'roster/client/care-plan-medical-delete', 'roster/client/care-plan-risk-delete', 'roster/client/care-plan-delete', 'roster/onboarding-configuration/workflow/loadData', 'roster/onboarding-configuration/stages/loadData', 'roster/onboarding-configuration/stages/details', 'roster/messaging-center/load-shift-request', 'roster/messaging-center/load-messages', 'roster/messaging-center/load-recipients', 'roster/policylibrary/details', 'roster/policylibrary/loadData', 'roster/clientcommunicationhub/load_clients', 'roster/carer-list', 'roster/onboarding-configuration/activate_status', 'roster/clientonboarding/loadData', 'roster/clientonboarding/loadUserDetails', 'roster/staffonboarding/loadforms', 'roster/staffonboarding/loadforms/save', 'roster/client_active_status', 'roster/staffonboarding/loadData', 'roster/staffonboarding/loadUserDetails', 'roster/carer_active_status', 'roster/child-educations/loadData', 'roster/child-educations/create', 'roster/child-educations/details', 'roster/get-document-ai-response', 'roster/save-document-ai-response', 'roster/get-risk-assessment', 'roster/risk-assessment-delete', 'roster/risk-assessment-details', 'roster/get-behaviour-list', 'roster/get-behaviour-details', 'roster/behaviour-delete', 'roster/get-peep-list', 'roster/get-peep-details', 'roster/peep-delete', 'roster/get-mental-capacity-list', 'roster/get-mental-capacity-details', 'roster/mental-capacity-delete', 'roster/client/dna-cpr-list', 'roster/client/dna-cpr-save', 'roster/client/dna-cpr-details', 'roster/onboarding-configuration/loadUsers', 'roster/client/emergency-contact-list', 'roster/client/emergency-contact/delete', 'roster/client/emergency-contact-save', 'roster/client/consent-list', 'roster/client/consent-save', 'roster/client/consent-status-change', 'roster/client/medication-log-save', 'roster/client/document-manage-list', 'roster/client/document-manage-save', 'roster/client/document-manage/delete','roster/care-document-get');
                //,'/general/petty_cash/check-balance'
                //if requested path is not one of them that don't need permission. then check it for permission 
                // Ram 10/06/2025 new array create to temprary for Rota Management by Abhishek sir when testing task done then we have to remove it.
                array_push($allowed_path, 'rota/staff', 'rota/staff-add', '/rota/staff-delete/{id}', 'get_leave_record_for_1_week', 'get_leave_record_for__week', 'staff/logs', 'staff/log/view', 'satff/log/view/filter', 'satff/log/view/is_valid', 'staff/timesheet', 'staff/timesheet_filter', 'pending-request-data', 'approve_leave', 'date_validation_for_user', 'rota-planner', 'publish_unpublish_rota', 'unpublish_rota_employee', 'publish_rota_employee', 'delete_rota_employee', 'edit_rota/', 'get-all-users', 'get-all-users-search', 'assign_rota_users', 'update_rota_name', 'get_all_shift', 'get_rota_employee', 'edit_shift_data_get', 'check_users_add_in_shift', 'update-shift-data', 'service/missing-care-form-records/', 'rota-absence', 'get-dynamic-form-daily-log/', 'service/dynamic-form/view/pattern_log','roster/schedule-shift/resolve');
                // echo "<pre>";print_r($allowed_path);die;
                // end here

                 // Whitelist AI Document Import and AI New Client Import routes
                array_push($allowed_path, 
                    'roster/ai-document-import/upload',
                    'roster/ai-document-import/extract',
                    'roster/ai-document-import/confirm',
                    'roster/ai-document-import/list',
                    'roster/ai-document-import/documents',
                    'roster/ai-document-import/delete',
                    'roster/ai-document-import/download',
                    'roster/ai-new-client-import/upload',
                    'roster/ai-new-client-import/extract',
                    'roster/ai-new-client-import/confirm',
                    'roster/sos-alert/trigger',
                    'roster/sos-alert/list',
                    'roster/sos-alert/acknowledge',
                    'roster/sos-alert/resolve'
                );

                $is_allowed = false;
                foreach ($allowed_path as $ap) {
                    $ap_clean = trim($ap, '/');
                    if ($path === $ap_clean || 
                        str_starts_with($path, $ap_clean . '/') || 
                        (str_contains($ap_clean, '{') && preg_match('#^' . preg_replace('/\{[^}]+\}/', '[^\/]+', $ap_clean) . '$#', $path))
                    ) {
                        $is_allowed = true;
                        break;
                    }
                }

                if (!$is_allowed) {
                    // echo $path; die;
                    $res = $this->checkPermission($path);
                    // echo json_encode($res); die;
                    // echo "<pre>";print_r($res); die;
                    if (!$res) {
                        // return response()->json(['error' => 'unauthorize','path'=>$path], 403);
                        if ($request->ajax()) {
                            // echo json_encode($path);die;
                            echo json_encode('unauthorize');
                            die;
                        }
                        if ($path === 'roster') {
                            Auth::logout();
                            return redirect('/login')->with("error", UNAUTHORIZE_ERR);
                        }
                        return redirect()->back()->with("error", UNAUTHORIZE_ERR);
                    } else {
                    }
                }
            }
        }

        return $next($request);
    }

    function checkPermission($path)
    {
        //return true; //by passing route check 
        // return $path;
        // $user_rights = Auth::user()->access_rights;
        // $user_rights = explode(',',$user_rights);
        // $rights      = AccessRight::select('id','route')->whereIn('id',$user_rights)->get()->toArray();
        // foreach ($rights as $key => $right) {
        //     if(strpos($right['route'], $path) !== false) { 
        //         return true;    
        //     }
        // }
        $user_rights = explode(',', Auth::user()->access_rights);
        $routes = AccessRight::whereIn('id', $user_rights)
            ->pluck('route')
            ->toArray();

        $reqPath = trim($path, '/');
        if (in_array($reqPath, array_map(fn($r) => trim($r, '/'), $routes), true)) {
            return true;
        }
        foreach ($routes as $route) {
            $dbRoute = trim($route, '/');

            if (!str_contains($dbRoute, '{')) {
                continue;
            }

            $pattern = preg_replace('/\{[^}]+\}/', '[^\/]+', $dbRoute);

            if (preg_match("#^{$pattern}$#", $reqPath)) {
                return true;
            }
        }

        return false;
    }
}
