<?php

namespace App\Http\Controllers\frontEnd;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator,Auth;
use Carbon\Carbon;
use App\Models\petty_cash\ExpendCard;
use App\Models\petty_cash\Cash;

class PettyCashController extends Controller
{
    public function index(){
        return view('frontEnd.petty_cash.index');
    }
    public function expend_card(){
        $home_id=Auth::user()->home_id;
        $user_id=Auth::user()->id;
        // $data['expendCard']=ExpendCard::getAllExpendCard($home_id,$user_id)->get();
        $data['previous_month_data']=$this->previous_month_data($home_id,$user_id);
        // $data['expendCard'] = ExpendCard::getAllExpendCard($home_id, $user_id)
        // ->whereMonth('expend_date', now()->month)
        // ->whereYear('expend_date', now()->year)
        // ->orderBy('id','desc')->first();
        $data['expendCard'] = ExpendCard::getAllExpendCard($home_id, $user_id)
        ->whereMonth('expend_date', now()->month)
        ->whereYear('expend_date', now()->year)
        ->get();

        $data['cash']=Cash::getAllCash($home_id,$user_id)
        ->whereMonth('cash_date', now()->month)
        ->whereYear('cash_date', now()->year)
        ->sum('petty_cashIn');
        
        // echo "<pre>";print_r($data['previous_month_data']);die;
        return view('frontEnd.petty_cash.expend_card',$data);
    }
    public function petty_cash(){
        $home_id=Auth::user()->home_id;
        $user_id=Auth::user()->id;
        $data['cash']=Cash::getAllCash($home_id,$user_id)->get();
        return view('frontEnd.petty_cash.petty_cash',$data);
    }
    public function child_register(){
        return view('frontEnd.petty_cash.child_register');
    }
    public function expend_card_add(){
        $home_id=Auth::user()->home_id;
        $user_id=Auth::user()->id;
        $data['expendCard'] = ExpendCard::getAllExpendCard($home_id, $user_id)
        ->whereMonth('expend_date', now()->month)
        ->whereYear('expend_date', now()->year)
        ->orderBy('id','desc')->first();
        $data['previous_month_data']=$this->previous_month_data($home_id,$user_id);
        // echo "<pre>";print_r($data['previous_month_data']);die;
        return view('frontEnd.petty_cash.expend_card_form',$data);
    }
    public function petty_cash_add(){
        $home_id=Auth::user()->home_id;
        $user_id=Auth::user()->id;
        $data['previous_Cash_month_data']=$this->previous_Cash_month_data($home_id,$user_id);
        $data['cash'] = Cash::getAllCash($home_id, $user_id)
        ->whereMonth('cash_date', now()->month)
        ->whereYear('cash_date', now()->year)
        ->orderBy('id','desc')->first();
        // echo "<pre>";print_r($data['previous_Cash_month_data']);die;
        return view('frontEnd.petty_cash.petty_cash_form',$data);
    }
    public function child_register_add(){
        return view('frontEnd.petty_cash.child_register_form');
    }
    public function saveExpend(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $home_id=Auth::user()->home_id;
        $user_id=Auth::user()->id;
        $rules = [
            'expend_date'     => 'required',
            // 'fund_added'      => 'required',
            'purchase_amount' => 'required',
            'card_details'    => 'required',
            'dext'            => 'required',
            'invoice_la'      => 'required',
            'initial'         => 'required',
            'receipt'         => 'required',
        ];
        
        if ($request->last_id == '') {
            $rules['balance_bfwd'] = 'required';
        }
        
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return response()->json(['vali_error' => $validator->errors()->first()]);
        }
        try {
            if ($request->hasFile('receipt')) {
                $imageName = time().'.'.$request->receipt->extension();      
                $request->receipt->move(public_path('images/finance_petty_cash'), $imageName);
                $original_name=$request->receipt->getClientOriginalName();
                $requestData = $request->all();
                $requestData['receipt'] = $imageName;
                $requestData['fileName'] = $original_name;
            } else {
                $requestData = $request->all();
            }
            
            $requestData['home_id'] = $home_id;
            $requestData['loginUserId'] = $user_id;
            // echo "<pre>";print_r($requestData);die;
            $expendCard=ExpendCard::saveExpenseCard($requestData);
            
            if($request->id == ''){
                return response()->json(['success' => true,'message'=>'The Expend Card has been saved succesfully.', 'data' => $expendCard]);
            }else{
                return response()->json(['success' => true,'message'=>'The Expend Card has been updated succesfully.', 'data' => $expendCard]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function saveCash(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $home_id=Auth::user()->home_id;
        $user_id=Auth::user()->id;

        $validator = Validator::make($request->all(), [
            'cash_date'=>'required',
            'balance_bfwd'=>'required',
            'petty_cashIn'=>'required',
            'cash_out'=>'required',
            'card_details'=>'required',
            'dext'=>'required',
            'invoice_la'=>'required',
            'initial'=>'required',
            'receipt'=>'required',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['vali_error' => $validator->errors()->first()]);
        }
        try {
            if ($request->hasFile('receipt')) {
                $imageName = time().'.'.$request->receipt->extension();      
                $request->receipt->move(public_path('images/finance_cash'), $imageName);
                $original_name=$request->receipt->getClientOriginalName();
                $requestData = $request->all();
                $requestData['receipt'] = $imageName;
                $requestData['fileName'] = $original_name;
            } else {
                $requestData = $request->all();
            }
            
            $requestData['home_id'] = $home_id;
            $requestData['loginUserId'] = $user_id;
            // echo "<pre>";print_r($requestData);die;
            $cash=Cash::saveCash($requestData);
            
            if($request->id == ''){
                return response()->json(['success' => true,'message'=>'The Cash has been saved succesfully.', 'data' => $cash]);
            }else{
                return response()->json(['success' => true,'message'=>'The Cash has been updated succesfully.', 'data' => $cash]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    private function previous_month_data($home_id,$user_id){
        $previousMonth = Carbon::now()->subMonth();
        $previous_data = ExpendCard::getAllExpendCard($home_id, $user_id)
                ->whereMonth('expend_date', $previousMonth->month)
                ->whereYear('expend_date', now()->year)
                ->orderBy('id', 'desc')
                ->get();
        $cash=Cash::getAllCash($home_id,$user_id)
                ->whereMonth('cash_date',$previousMonth->month)
                ->whereYear('cash_date', now()->year)
                ->sum('petty_cashIn');
        $sumPurchaseCashIn=0;  
        $totalBalanceFund=0; 
        $fundAmount=0; 
        $count=0;
        $date=null;
        $prvious_date=null;
        foreach($previous_data as $val){
            $sumPurchaseCashIn=$sumPurchaseCashIn+$val->purchase_amount;
            $totalBalanceFund=$totalBalanceFund+$val->fund_added;
            if($count == 0){
                $fundAmount=$val->fund_added;
                $prvious_date=$val->expend_date;
                $count=1;
            }
            $db_date=date('m',strtotime($val->expend_date));
            if($date != $db_date || $date == null){$date=$db_date;}
        }   
        $sum=$totalBalanceFund+$totalBalanceFund;
        $calculation=$sum-$sumPurchaseCashIn;
        $balanceOnCard=$calculation-$cash;
        $data=[
            'previousbalanceOnCard'=>$balanceOnCard,
            'previousfundAmount'=>$fundAmount,
            'prvious_date'=>$prvious_date,

        ];
        return $data;
    }
    private function previous_Cash_month_data($home_id,$user_id){
        $previousMonth = Carbon::now()->subMonth();
        $cash=Cash::getAllCash($home_id,$user_id)
                ->whereMonth('cash_date',$previousMonth->month)
                ->whereYear('cash_date', now()->year)
                ->get();
        $total_balance=0;
        $cash_out=0;
        $balance_bfwd=0;
        $petty_cashIn=0;
        foreach($cash as $val){
            $total_balance=$total_balance+$val->balance_bfwd+$val->petty_cashIn;
            $cash_out=$cash_out+$val->cash_out;
            
        }
        $total_balanceInCash=$total_balance-$cash_out;
        $data=['total_balanceInCash'=>$total_balanceInCash];
        return $data;

    }
    public function cash_filter(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $startDate=$request->startDate;
        $endDate=$request->endDate;
        $home_id=Auth::user()->home_id;
        $user_id=Auth::user()->id;
        $query = Cash::getAllCash($home_id,$user_id);
        if ($request->filled('startDate') && $request->filled('endDate')) {
            $query->whereBetween('cash_date', [$startDate, $endDate]);
        }
        $search_data = $query->get();
        // echo "<pre>";print_r($search_data);die;
        $total_balance=0;
        $cash_out=0;
        $count=0;
        $balance_bfwd=0;
        $petty_cashIn=0;
        $html_data='';
        foreach($search_data as $key=>$val){
            $total_balance=$total_balance+$val->balance_bfwd+$val->petty_cashIn;
            $cash_out=$cash_out+$val->cash_out;
            $petty_cashIn=$petty_cashIn+$val->petty_cashIn;
            if($count == 0){
                $count=1;
                $balance_bfwd=$val->balance_bfwd;
            }
            $dext='';
            $invoice_la='';
            if($val->dext == 1){ $dext= "Yes";}else{ $dext= "No"; }
            if($val->invoice_la == 1){ $invoice_la= "Yes"; }else{ $invoice_la= "No" ;}
            $html_data.='<tr>
                            <td>'. ++$key .'</td>
                            <td>'. date("Y-m-d",strtotime($val->cash_date)) .'</td>
                            <td>£'. $val->balance_bfwd .'</td>
                            <td>£'. $val->petty_cashIn .'</td>
                            <td>£'. $val->cash_out .'</td>
                            <td>'. $val->card_details .'</td>
                            <td><a href="'.url("public/images/finance_cash/".$val->receipt) .'" target="_blank"><i class="fa-solid fa-eye"></i></a></td>
                            <td>'. $dext .'</td>
                            <td>'. $invoice_la .'</td>
                            <td>'. $val->initial .'</td>
                        </tr>';

        }
        $total_balanceInCash=$total_balance-$cash_out;
        return response()->json(['success'=>true,'message'=>'Filtered Data','data'=>$search_data,'html_data'=>$html_data,'total_balance'=>$total_balance,'cash_out'=>$cash_out,'balance_bfwd'=>$balance_bfwd,'petty_cashIn'=>$petty_cashIn,'total_balanceInCash'=>$total_balanceInCash]);
    }
    public function expand_card_filter(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $startDate=$request->startDate;
        $endDate=$request->endDate;
        $home_id=Auth::user()->home_id;
        $user_id=Auth::user()->id;

        $cash=Cash::getAllCash($home_id,$user_id)
        ->whereMonth('cash_date', now()->month)
        ->whereYear('cash_date', now()->year)
        ->sum('petty_cashIn');
        $query = ExpendCard::getAllExpendCard($home_id, $user_id)
        ->whereMonth('expend_date', now()->month)
        ->whereYear('expend_date', now()->year);
        if ($request->filled('startDate') && $request->filled('endDate')) {
            $query->whereBetween('expend_date', [$startDate, $endDate]);
        }
        $search_data = $query->get();
        $previous_month_data=$this->previous_month_data($home_id,$user_id);
        // return $previous_month_data['previousbalanceOnCard'];
        // echo "<pre>";print_r($search_data);die;
        $html_data='';
        $enterInLoop=0;
        $index=0;
        if(!empty($previous_month_data) && $previous_month_data['previousbalanceOnCard'] !=0){ 
            $enterInLoop=1;

            $html_data.='<tr>
                            <td>{{++$index}}</td>
                            <td>'. $previous_month_data['prvious_date'] .'</td>
                            <td>£'. $previous_month_data['previousbalanceOnCard'] .'</td>
                            <td>£'. $previous_month_data['previousfundAmount'] .'</td>
                            <td colspan="6"></td>
                        </tr>';
        }
        $sumBalanceFund=0;
        $sumPurchaseCashIn=0;
        $totalBalancebfwd=0;   
        $totalBalanceFund=0; 
        $date=null;
        foreach($search_data as $val){
            $sumPurchaseCashIn=$sumPurchaseCashIn+$val->purchase_amount;
            $totalBalanceFund=$totalBalanceFund+$val->fund_added;
            if($enterInLoop == 0){
                $totalBalancebfwd=$val->balance_bfwd;
                $enterInLoop=1;
            }
            $db_date=date('m',strtotime($val->expend_date));
            if($val->dext == 1){ $dext= "Yes";}else{ $dext= "No"; }
            if($val->invoice_la == 1){ $invoice_la= "Yes"; }else{ $invoice_la= "No" ;}
            $html_data.='<tr>
                <td>'. ++$index .'</td>
                <td>'. date("Y-m-d",strtotime($val->expend_date)) .'</td>';
                if($previous_month_data['previousbalanceOnCard'] == 0){if($date != $db_date || $date == null){$date=$db_date;
                    $html_data.='<td>£'. $val->balance_bfwd .'</td>';
                 }else{
                    $html_data.='<td></td>';
                 }}
                 $fund_added='';
                 if(isset($val->fund_added) && $val->fund_added !=''){$fund_added= '£'.$val->fund_added;}
                 $html_data.='<td>'. $fund_added .'</td>
                <td>£'. $val->purchase_amount .'</td>
                <td>'. $val->card_details .'</td>
                <td><a href="'. url("public/images/finance_petty_cash/".$val->receipt) .'" target="_blank"><i class="fa-solid fa-eye"></i></a></td>
                <td>'. $dext .'</td>
                <td>'. $invoice_la .'</td>
                <td>'. $val->initial .'</td>
            </tr>';
        }
        if($totalBalancebfwd == 0){
            $sum=$totalBalanceFund+$previous_month_data['previousbalanceOnCard'];
        }else{
            $sum=$totalBalanceFund+$totalBalancebfwd;
        }
        $calculation=$sum-$sumPurchaseCashIn;
        if(count($search_data) > 0){
            $balanceOnCard=$calculation-$cash;
        }else{
            $balanceOnCard=0;
        }
        // echo "<pre>";print_r($html_data);die;
        return response()->json(['success'=>true,'message'=>'Filtered Data','data'=>$search_data,'html_data'=>$html_data,'balanceOnCard'=>$balanceOnCard,'totalBalancebfwd'=>($totalBalancebfwd) ? $totalBalancebfwd: $previous_month_data['previousbalanceOnCard'],'totalBalanceFund'=>$totalBalanceFund,'sumPurchaseCashIn'=>$sumPurchaseCashIn]);
    }
}
