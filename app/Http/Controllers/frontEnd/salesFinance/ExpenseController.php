<?php

namespace App\Http\Controllers\frontEnd\salesFinance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth,Validator,File;
use Illuminate\Support\Facades\Log;
use App\Models\Construction_tax_rate;
use App\User;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Project;
use App\Models\Job;
use App\Models\Construction_job_appointment;
use App\Models\Constructor_customer_site;

class ExpenseController extends Controller
{
    public function expenses(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $key=$request->key;
        $value=$request->value;
        $home_id=Auth::user()->home_id;
        $data['user_id']=Auth::user()->id;
        $data['users'] = User::getHomeUsers($home_id);
        $data['rate']=Construction_tax_rate::getAllTax_rate($home_id,'Active');
        $data['customer']=Customer::get_customer_list_Attribute($home_id,'ACTIVE');
        $data['home_id']=$home_id;
        if(isset($key) && isset($value)){
            if($key === 'reject' && $value == 1){
                $expense=Expense::getAllExpense($home_id)->where(["$key"=>$value,'login_user_id'=>Auth::user()->id])->get();
            }else if($key === 'authorised'){
                $expense=Expense::getAllExpense($home_id)->where(["$key"=>$value,'reject'=>0,'paid'=>0,'login_user_id'=>Auth::user()->id])->get();
            }else{
                $expense=Expense::getAllExpense($home_id)->where(["$key"=>$value,'reject'=>0,'login_user_id'=>Auth::user()->id])->get();
            }
            
        }else{
            $expense=Expense::getAllExpense($home_id)->where('login_user_id',Auth::user()->id)->get();
        }
        $data['expense']=$expense;
        // echo "<pre>";print_r($data['expense']);die;
        $data['authorisedCount']=Expense::getAllExpense($home_id)->where(['authorised'=>1,'reject'=>0,'paid'=>0,'login_user_id'=>Auth::user()->id])->count();
        $data['unauthorisedCount']=Expense::getAllExpense($home_id)->where(['authorised'=>0,'reject'=>0,'paid'=>0,'login_user_id'=>Auth::user()->id])->count();
        $data['rejectCount']=Expense::getAllExpense($home_id)->where(['reject'=>1,'login_user_id'=>Auth::user()->id])->count();
        $data['paidCount']=Expense::getAllExpense($home_id)->where(['paid'=>1,'reject'=>0,'login_user_id'=>Auth::user()->id])->count();
        $data['expenseCount']=Expense::getAllExpense($home_id)->where('login_user_id',Auth::user()->id)->count();
        $data['page'] = "expenses";
        // echo "<pre>";print_r($data['paidWithAuthCount']);die;
        return view('frontEnd.salesAndFinance.expenses.expense',$data);
    }
    public function find_project(Request $request){
        $customer_id=$request->customer_id;
        $project=Project::getAllProject($customer_id);
        return response()->json(['project'=>$project]);
    }
    public function find_job(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $job_input=$request->job_input;
        $job_appoint = Job::where('job_ref', 'like', '%' . $job_input . '%')->get();
        return response()->json(['job_appoint'=>$job_appoint]);
    }
    public function find_appointment(Request $request){
        // echo "<pre>";print_r($request->all());die;
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
    public function expense_save(Request $request){
        // echo "<pre>";print_r($request->all());die;
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
        $requestData['login_user_id']=Auth::user()->id;
        // echo "<pre>";print_r($requestData);die;
        try {
            $expense=Expense::expense_save($requestData);
            if($request->id == ''){
                return response()->json(['success' => true,'message'=>'Expense has been saved', 'expense' => $expense]);
            }else{
                return response()->json(['success' => true,'message'=>'Expense has been updated', 'expense' => $expense]);
            }
            
        } catch (\Exception $e) {
            Log::error('Error saving Tag: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to save Tag. Please try again.'], 500);
        }
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
    public function reject_expense(Request $request){
        $id=base64_decode($request->key);
        try {
            $expense_reject=Expense::find($id);    
            $expense_reject->reject=1;
            $expense_reject->save();
            return redirect()->back()->with('message','Expense Rejected');
        }
        catch (\Exception $e) {
            return response()->json(['success'=>'false','message' => $e->getMessage()], 500);
        }
    }
    public function searchCustomerName(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $query = $request->input('search_query');  
        $home_id = Auth::user()->home_id;

        $CustomerSearchData = Customer::where('name', 'LIKE', "%$query%")
            ->where('home_id', $home_id)
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->take(10)
            ->get();

        return response()->json(['data' => $CustomerSearchData]);
    }
    public function searchExpenses(Request $request){
        // echo "<pre>";print_r($request->all());die;
        
        $expenseBy=$request->expenseBy;
        $customer_name=$request->customer_name;
        $selectedId=$request->selectedId;
        $billable=$request->billable;
        $start_date=$request->start_date;
        $end_date=$request->end_date;
        $keywords=$request->keywords;
        $key=$request->key;
        $value=$request->value;
        $home_id=Auth::user()->home_id;
        $query = Expense::getAllExpense($home_id);

        if ($request->filled('key') && $request->filled('value')) {
            if ($key === 'reject' && $value == 1) {
                $query->where('reject', 1);
            } elseif ($key === 'authorised') {
                $query->where([
                    'authorised' => $value,
                    'reject' => 0,
                    'paid' => 0,
                ]);
            } else {
                $query->where($key, $value)->where('reject', 0);
            }
        }

        if ($request->filled('expenseBy')) {
            $query->where('user_id', $expenseBy);
        }

        if ($request->filled('selectedId')) {
            $query->where('customer_id', $selectedId);
        }

        if ($request->filled('billable')) {
            $query->where('billable', $billable);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('expense_date', [$start_date, $end_date]);
        }

        if ($request->filled('keywords')) {
            $query->where(function ($q) use ($keywords) {
                $q->where('notes', 'LIKE', '%' . $keywords . '%')
                ->orWhere('title', 'LIKE', '%' . $keywords . '%')
                ->orWhere('reference', 'LIKE', '%' . $keywords . '%');
            });
        }
        $expenses = $query->where('user_id',Auth::user()->id)->get();
        // echo "<pre>";print_r($expenses);die;
        $array_data='';
        $net_amount=0;
        $vat_amount=0;
        $gross_amount=0;
        foreach($expenses as $key=>$val){
            $net_amount=$net_amount+$val->amount;
            $vat_amount=$vat_amount+$val->vat_amount;
            $gross_amount=$gross_amount+$val->gross_amount;
            $user = User::find($val->user_id)->name;
            $job = Job::find($val->job_id);
            if(isset($job)){
                if($job->site_id == 'default' || $job->site_id == ''){
                    $site = Constructor_customer_site::where('customer_id',$job->customer_id)->orderBy('id','DESC')->first(); 
                }else{
                    $site = Constructor_customer_site::find($job->site_id);
                }
            }
            $array_data .= '<tr>
                        <td><input type="checkbox" class="delete_checkbox" value="' . $val->id . '"></td>
                        <td>' . ++$key . '</td>
                        <td>' . date('d-m-Y',strtotime($val->expense_date)) . '</td>
                        <td>' . $user . '</td>
                        <td>' . $val->title . '</td>
                        <td>' . ($val->reference ?? "") . '</td>
                        <td>' . ($val->job ?? "-") . '</td>
                        <td>' . ($site->site_name ?? "") . '</td>
                        <td>' . ($val->notes ?? "") . '</td>
                        <td>£ ' . number_format($val->amount, 2) . '</td>
                        <td>£ ' . number_format($val->vat_amount, 2) . '</td>
                        <td>£ ' . number_format($val->gross_amount, 2) . '</td>
                        <td>' . ($val->billable == 1 ? 'Yes' : 'No') . '</td>
                        <td>' . ($val->authorised == 1 ? 'Yes' : 'No') . '</td>
                        <td>' . ($val->reject == 1 ? 'Yes' : 'No') . '</td>
                        <td>' . ($val->paid == 1 ? 'Yes' : 'No') . '</td>
                        <td>';
                        
                    if (!empty($val->attachments)) {
                        $array_data .= '<a href="' . url('public/images/expense/' . htmlspecialchars($val->attachments)) . '" target="_blank" style="text-decoration:none">
                            View
                        </a>';
                    }

                    $array_data .= '</td>
                        <td>' . htmlspecialchars($val->created_at) . '</td>
                        <td>
                            <div class="pageTitleBtn p-0">
                                <div class="nav-item dropdown">
                                    <a href="#" class="nav-link dropdown-toggle profileDrop" data-bs-toggle="dropdown" aria-expanded="false">
                                        Action
                                    </a>
                                    <div class="dropdown-menu fade-up m-0">
                                        <a href="#" class="dropdown-item col-form-label fetch_data" data-bs-toggle="modal" 
                                        data-bs-target="#customerPop" 
                                        data-id="' . $val->id . '"
                                        data-title="' . $val->title . '"
                                        data-amount="' . $val->amount . '"
                                        data-vat="' . $val->vat . '"
                                        data-vat_amount="' . $val->vat_amount . '"
                                        data-gross_amount="' . $val->gross_amount . '"
                                        data-expense_date="' . $val->expense_date . '"
                                        data-user_id="' . $val->user_id . '"
                                        data-reference="' . $val->reference . '"
                                        data-customer_id="' . $val->customer_id . '"
                                        data-job="' . $val->job . '"
                                        data-project_id="' . $val->project_id . '"
                                        data-job_appointment_id="' . $val->job_appointment_id . '"
                                        data-authorised="' . $val->authorised . '"
                                        data-billable="' . $val->billable . '"
                                        data-paid="' . $val->paid . '"
                                        data-notes="' . $val->notes . '"
                                        data-attachments="' . $val->attachments . '">
                                        Edit</a>
                                        <hr class="dropdown-divider">
                                        <a onclick="return confirm(\'Are you sure to reject it?\')" href="' . url('/reject_expense?key=' . base64_encode($val->id)) . '" class="dropdown-item col-form-label">Reject</a>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>';
        }

        return response()->json(['data' => $array_data,'net_amount'=>$net_amount,'vat_amount'=>$vat_amount,'gross_amount'=>$gross_amount]);
    
    }
}
