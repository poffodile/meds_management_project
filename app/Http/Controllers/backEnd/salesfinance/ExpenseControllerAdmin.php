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

class ExpenseControllerAdmin extends Controller
{
    public function index(Request $request){
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
        }else {
            return redirect('admin/')->with('error',NO_HOME_ERR);
        }
        return view('backEnd.salesFinance.expense.expense',$data);
    }
    public function find_project(Request $request){
        $customer_id=$request->customer_id;
        $project=Project::getAllProject($customer_id);
        return response()->json(['project'=>$project]);
    }
    public function find_job(Request $request){
        $job_input=$request->job_input;
        $job_appoint = Job::where('job_ref', 'like', '%' . $job_input . '%')->get();
        return response()->json(['job_appoint'=>$job_appoint]);
    }
    public function find_appointment(Request $request){
        $job_id=$request->job_id;
        $appointment_list=Construction_job_appointment::where(['job_id'=>$job_id,'status'=>1,'deleted_at'=>null])->get();
        $data=array();
        $appointment_status=[
            1=>'Awaiting',
            2=>'Received',
            3=>'Accepted',
            4=>'Declined',
            5=>'on Route',
            6=>'On Site',
            7=>'Completed',
            8=>'Follow On',
            9=>'Abandoned',
            10=>'No Access',
            11=>'Cancelled',
            12=>'On Hold'
    ];
    // echo "<pre>";print_r($appointment_status);die;
        foreach($appointment_list as $val){
            $user_name=User::find($val->user_id)->name;
            $status='';
            if(array_key_exists($val->appointment_status,$appointment_status)){
                $status=$appointment_status[$val->appointment_status];
            }
            $data[]=[
                'appointment_id'=>$val->id,
                'appointment_type_id'=>$val->appointment_type_id,
                'start_date'=>$val->start_date,
                'start_time'=>$val->start_time,
                'end_date'=>$val->end_date,
                'end_time'=>$val->end_time,
                'user_name'=>$user_name,
                'user_id'=>$val->user_id,
                'status'=>$status
            ];
        }
        return $data;
    }
    public function expense_image_delete(Request $request){
        // echo "<pre>";print_r($request->all());die;
        try {
            $expense_record=Expense::find($request->id);    
            File::delete(public_path('images/expense/' . $expense_record->attachments));
            $expense_record->attachments='';
            $expense_record->save();
            return true;
        }
        catch (\Exception $e) {
            return response()->json(['success'=>'false','message' => $e->getMessage()], 500);
        }
    }
    public function expense_delete(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $id=base64_decode($request->id);
        $delete= Expense::where('id', $id)->update(['deleted_at' => Carbon::now()]);
        Session::flash('success','Expense Deleted Successfully Done');
        echo "done";
    }
    public function expense_reject(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $id=base64_decode($request->id);
        $reject=$request->reject;
        $table=Expense::find($id);
        $table->reject=$reject;
        $table->save();
        Session::flash('success','Changes Successfully Done');
        echo "done";
    }
    public function expense_save(Request $request){
        $validator = Validator::make($request->all(), [
                'home_id' => 'required',
                'title' => 'required',
                'amount' => 'required',
                'vat' => 'required',
                'gross_amount' => 'required',
                'expense_date' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['vali_error' => $validator->errors()->first()]);
            }
        

        if ($request->hasFile('attachments')) {
            $imageName = time().'.'.$request->attachments->extension();      
            $request->attachments->move(public_path('images/expense'), $imageName);
            $requestData = $request->all();
            $requestData['attachments'] = $imageName;
        } else {
            $requestData = $request->all();
        }
        try {
            $expense=Expense::expense_save($requestData);
            return response()->json(['success' => true, 'expense' => $expense]);
        } catch (\Exception $e) {
            Log::error('Error saving Tag: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to save Tag. Please try again.'], 500);
        }
    }
}
