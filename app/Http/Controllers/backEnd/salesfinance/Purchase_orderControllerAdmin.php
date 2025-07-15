<?php

namespace App\Http\Controllers\backEnd\salesfinance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth,Validator,File,Session,DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\Construction_tax_rate;
use App\User;
// use App\Customer;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Project;
use App\Models\Job;
use App\Models\Construction_job_appointment;

class Purchase_orderControllerAdmin extends Controller
{
    public function purchase_orders(Request $request){
        $page = 'Expense';
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        // echo "<pre>";print_r($request->all());die;
        $key=$request->key;
        $value=$request->value;
        if($home_id){
            if(isset($key) && isset($value)){
                if($key === 'reject' && $value == 1){
                    $expense_query=Expense::getAllExpense($home_id)->where("$key",$value);
                }else if($key === 'authorised'){
                    $expense_query=Expense::getAllExpense($home_id)->where(["$key"=>$value,'reject'=>0,'paid'=>0]);
                }else{
                    $expense_query=Expense::getAllExpense($home_id)->where(["$key"=>$value,'reject'=>0]);
                }
                
            }else{
                $expense_query=Expense::getAllExpense($home_id);
            }
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
                $expense_query = $expense_query->where('title','like','%'.$search.'%');
            }
            $expense = $expense_query->paginate($limit);
            $data['authorisedCount']=Expense::getAllExpense($home_id)->where(['authorised'=>1,'reject'=>0,'paid'=>0])->count();
            $data['unauthorisedCount']=Expense::getAllExpense($home_id)->where(['authorised'=>0,'reject'=>0,'paid'=>0])->count();
            $data['rejectCount']=Expense::getAllExpense($home_id)->where('reject',1)->count();
            $data['paidCount']=Expense::getAllExpense($home_id)->where(['paid'=>1,'reject'=>0])->count();
            $data['expenseCount']=Expense::getAllExpense($home_id)->count();
            $data['expense']=$expense;
            $data['limit']=$limit;
            $data['search']=$search;
            $data['page']='Expense';
            $data['home_id']=$home_id;
            $data['user_id']=$admin->id;
            $data['users'] = User::getHomeUsers($home_id);
            $data['rate']=Construction_tax_rate::getAllTax_rate($home_id,'Active');
            $data['customer']=Customer::get_customer_list_Attribute($home_id,'ACTIVE');
        }else{
            return redirect('admin/')->with('error',NO_HOME_ERR);
        }
        return view('backEnd.salesFinance.purchase_order.purchase_order',$data);
    }
    public function purchase_order_add(){
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        if($home_id){
            $data['task']="Add";
            $data['page']="Purchase Order";
            return view('backEnd.salesFinance.purchase_order.purchase_order_form',$data);
        }else{
            return redirect('admin/')->with('error',NO_HOME_ERR);
        }
    }
}
