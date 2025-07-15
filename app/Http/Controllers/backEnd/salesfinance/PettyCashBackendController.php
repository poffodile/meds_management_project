<?php

namespace App\Http\Controllers\backEnd\salesfinance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use Validator;
use Carbon\Carbon;
use App\Models\petty_cash\ExpendCard;
use App\Models\petty_cash\Cash;
use App\User;
class PettyCashBackendController extends Controller
{
    public function expend_card(){
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        if($home_id){
            $data['page']='petty_card_cash';
            $data['users'] = User::getHomeUsers(Session::get('scitsAdminSession')->home_id);
            $data['expendCardLastData'] = ExpendCard::getAllExpendCard()
            ->whereMonth('expend_date', now()->month)
            ->whereYear('expend_date', now()->year)
            ->orderBy('id','desc')->first();
            $data['years'] = range(date('Y'), 2000);
            return view('backEnd.salesFinance.petty_cash.expend_card',$data);
        }else{
            return redirect('admin/')->with('error',NO_HOME_ERR);
        }
    }
    public function getAllExpendCardData(){
        try{
            $data['previous_month_data']=$this->previous_month_data(Session::get('scitsAdminSession')->home_id);
            // echo "<pre>"; print_r($data['previous_month_data']);die;
            $data['expendCard'] = ExpendCard::getAllExpendCard()
            ->whereMonth('expend_date', now()->month)
            ->whereYear('expend_date', now()->year)
            ->get();
            // echo "<pre>";print_r($data['expendCard']);die;
            $data['cash']=Cash::getAllCash()
            ->whereMonth('cash_date', now()->month)
            ->whereYear('cash_date', now()->year)
            ->sum('petty_cashIn');
            return response()->json(['success'=>true,'message'=>'Expend card data','data'=>$data,'is_admin'=>1]);
        } catch (\Exception $e) {
            return response()->json(['success'=>false,'error' => $e->getMessage()], 500);
        }
    }
    private function previous_month_data($home_id=null,$month=null,$year=null){
        // $requestData=['home_id'=>$home_id,'month'=>$month,'year'=>$year];
        // return $requestData;die;
        // $current_month = Carbon::now()->month;
        // $currentYear = Carbon::now()->year;
        // if ($current_month == 1) {
        //     $previousMonth = 12;
        //     $previousYear = $currentYear - 1;
        // } else {
        //     $previousMonth = $current_month - 1;
        //     $previousYear = $currentYear;
        // }
        // if (!empty($month) && !empty($year)) {
        //     $previousMonth = $month-1;
        //     $previousYear = $year;
        // }
        $current_month = Carbon::now()->month;
        $current_year = Carbon::now()->year;
        if (!empty($month) && !empty($year)) {
            $current_month = $month;
            $current_year=$year;
        }
        // $data=['current_month'=>$current_month,'current_year'=>$current_year];
        // return $data;
        $ExpendCardquery = ExpendCard::getAllExpendCard();

        if (!empty($home_id)) {
            $ExpendCardquery->where(['home_id' => $home_id]);
        }
        $ExpendCardquery->where(function ($query) use ($current_month, $current_year) {
            $query->whereYear('expend_date', '<', $current_year)
                ->orWhere(function ($subQuery) use ($current_month, $current_year) {
                    $subQuery->whereYear('expend_date', $current_year)
                            ->whereMonth('expend_date', '<', $current_month);
                });
        });
        $previous_data = $ExpendCardquery->orderBy('id', 'desc')->get();

        // $previous_data = $ExpendCardquery->whereMonth('expend_date', $previousMonth)
        //     ->whereYear('expend_date', $previousYear)
        //     ->orderBy('id', 'desc')
        //     ->get();
        // echo "<pre>";print_r($previous_data);die;
        if(count($previous_data) == 0){
            $data=[
                'previousbalanceOnCard'=>0,
                'previousfundAmount'=>0,
                'previouspurchase_amount'=>0,
                'prvious_date'=>'',

            ];
            return $data;
        }
        $cashquery=Cash::getAllCash();
        if(!empty($home_id)){
            $cashquery->where(['home_id'=>$home_id]);
        }
        // $cash=$cashquery->whereMonth('cash_date',$previousMonth)
        // ->whereYear('cash_date', $previousYear)
        // ->sum('petty_cashIn');
        $cashquery->where(function ($query) use ($current_month, $current_year) {
            $query->whereYear('cash_date', '<', $current_year)
                ->orWhere(function ($subQuery) use ($current_month, $current_year) {
                    $subQuery->whereYear('cash_date', $current_year)
                            ->whereMonth('cash_date', '<', $current_month);
                });
        });

        $cash = $cashquery->sum('petty_cashIn');
        // echo "<pre>";print_r($cash);die;
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
        $sum=$totalBalanceFund;
        $calculation=$sum-$sumPurchaseCashIn;
        $balanceOnCard=$calculation-$cash;
        $data=[
            'previousbalanceOnCard'=>$balanceOnCard,
            'previousfundAmount'=>$fundAmount,
            'previouspurchase_amount'=>$sumPurchaseCashIn,
            'prvious_date'=>$prvious_date,

        ];
        return $data;
    }
    public function saveExpend(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $id=$request->id;
        // $loginUserId=$request->loginUserId;
        // $user_detail=User::find($loginUserId);
        $home_id=Session::get('scitsAdminSession')->home_id;
        $rules = [
            'expend_date'     => 'required',
            'card_details'     => 'required',
        ];
        
        
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return response()->json(['vali_error' => $validator->errors()->first()]);
        }
        try {
            if($id == ''){
                $month=Carbon::parse($request->expend_date)->format('m');
                $year=Carbon::parse($request->expend_date)->format('Y');
                $checkVal=$this->check_CardclosingAmount($home_id,$request->expend_date);
                $checkPurchaseAmount=$checkVal+$request->fund_added;
                if($checkVal == 0 && $request->fund_added == ''){
                    return response()->json(['success'=>false,'message'=>'Please fill fund added first','data'=>array()]);
                }else if($request->purchase_amount > $checkPurchaseAmount){
                    return response()->json(['success'=>false,'message'=>"Please enter purchases amount that does not exceed the closing balance or the added funds.",'data'=>array()]);
                }
                // echo "<pre>";print_r($checkVal);die;
            }
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
            // $requestData['loginUserId'] = $loginUserId;
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
    public function check_CardclosingAmount($home_id,$expend_date){
        $date = Carbon::parse($expend_date);
        $current_month=now()->month;
        $month = $date->format('m')+1;
        if($current_month == $date->format('m')){
            $month = $date->format('m');
        }
        $year = $date->format('Y');
        $startDate=Carbon::parse($expend_date)->format('m');
        $previous_month_data=$this->previous_month_data($home_id,$month,$year);
        $expendCard = ExpendCard::getAllExpendCard()
        ->where(['home_id'=>$home_id])
            ->whereMonth('expend_date', $startDate)
            ->whereYear('expend_date', now()->year)
            ->get();
        $cash=Cash::getAllCash()
            ->where(['home_id'=>$home_id])
            ->whereMonth('cash_date', $startDate)
            ->whereYear('cash_date', now()->year)
            ->sum('petty_cashIn');
        $enter=0;
        $balance_bfwd=0;
        $purchase_amount=0;
        $fund_added=0;
        foreach($expendCard as $val){
            if($enter == 0){
                $balance_bfwd=$val->balance_bfwd;
                $enter=1;
            }
            $purchase_amount=$purchase_amount+$val->purchase_amount;
            $fund_added=$fund_added+$val->fund_added;

        }
        $sum=$previous_month_data['previousbalanceOnCard']+$fund_added;
        return $calculate=$sum-$cash-$purchase_amount;
    }
    public function expend_delete(Request $request){
        $validator = Validator::make($request->all(), ['id'=>'required|integer|exists:expend_cards,id']);
        
        if ($validator->fails()) {
            return response()->json(['success'=>false,'message' => $validator->errors()->first(),'data'=>array()]);
        }
        $expendCard=ExpendCard::find($request->id);
        if($expendCard){
            $expendCard->update(['deleted_at' => Carbon::now()]);
            return response()->json(['success'=>true,'message'=>'Expend Card deleted successfully done','data'=>array()]);
        }else{
            return response()->json(['success'=>false,'message'=>'Invalid Id given','data'=>array()]);
        }
    }
    public function cash(){
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        if($home_id){
            $data['page']='petty_card_cash';
            // $data['cash']=Cash::getAllCash()->get();
            $data['cash']=Cash::getAllCash()
            ->whereMonth('cash_date', now()->month)
            ->whereYear('cash_date', now()->year)
            ->get();
            // echo "<pre>";print_r($data['cash']);die;
            $data['previous_Cash_month_data']=$this->previous_Cash_month_data();
            $data['cashLastId'] = Cash::getAllCash()
            ->whereMonth('cash_date', now()->month)
            ->whereYear('cash_date', now()->year)
            ->orderBy('id','desc')->first();
            $data['years'] = range(date('Y'), 2000);
            //  echo "<pre>";print_r($data['previous_Cash_month_data']);die;
            return view('backEnd.salesFinance.petty_cash.cash',$data);
        }else{
            return redirect('admin/')->with('error',NO_HOME_ERR);
        }
    }
    private function previous_Cash_month_data($home_id=null,$year=null,$month=null){
        $current_month = Carbon::now()->month;
        $current_year = Carbon::now()->year;
        $home_id=Session::get('scitsAdminSession')->home_id;
        if(!empty($month)){
            $current_month = $month;
            $current_year = $year;
        }
        // $data=['current_month'=>$current_month,'current_year'=>$current_year];
        // return $data;
        $cashQuery = Cash::getAllCash()->where('home_id', $home_id);
        $cashQuery->where(function ($query) use ($current_month, $current_year) {
            $query->whereYear('cash_date', '<', $current_year)
                ->orWhere(function ($subQuery) use ($current_month, $current_year) {
                    $subQuery->whereYear('cash_date', $current_year)
                            ->whereMonth('cash_date', '<', $current_month);
                });
        });

        $cash = $cashQuery->get();
        // return $cash;
        $total_balance=0;
        $cash_out=0;
        $balance_bfwd=0;
        $petty_cashIn=0;
        $prvious_date='';
        foreach($cash as $val){
            $total_balance=$total_balance+$val->petty_cashIn;
            $cash_out=$cash_out+$val->cash_out;
            $prvious_date=$val->cash_date;
            
        }
        $total_balanceInCash=$total_balance-$cash_out;
        $data=['total_balanceInCash'=>$total_balanceInCash,'prvious_date'=>$prvious_date];
        return $data;

    }
    public function saveCash(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $home_id=Session::get('scitsAdminSession')->home_id;
        // $user_id=Auth::user()->id;
        $id=$request->id;
        $array=[
            'cash_date'=>'required',
            'card_details'=>'required',
        ]; 
        
        $validator = Validator::make($request->all(), $array);
        
        if ($validator->fails()) {
            return response()->json(['vali_error' => $validator->errors()->first()]);
        }
        try {
            if($id == ''){
                $month=Carbon::parse($request->cash_date)->format('m');
                $year=Carbon::parse($request->cash_date)->format('Y');
                $checkVal=$this->check_CashclosingAmount($request->cash_date);
                $checkCashOut=$checkVal+$request->petty_cashIn;
                if($checkVal == 0 && $request->petty_cashIn == ''){
                    return response()->json(['success'=>false,'message'=>'Please fill petty cash in first','data'=>array()]);
                }else if($request->cash_out > $checkCashOut){
                    return response()->json(['success'=>false,'message'=>"Please enter cash out amount that does not exceed the closing balance or the petty cash in.",'data'=>array()]);
                }
                // echo "<pre>";print_r($checkVal);die;
            }
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
            // $requestData['loginUserId'] = $user_id;
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
    public function check_CashclosingAmount($cash_date){
        $home_id=Session::get('scitsAdminSession')->home_id;
        $date = Carbon::parse($cash_date);
        $current_month=now()->month;
        $month = $date->format('m')+1;
        if($current_month == $date->format('m')){
            $month = $date->format('m');
        }
        $year = $date->format('Y');
        $startDate=Carbon::parse($cash_date)->format('m');
        // return $startDate;
        $previous_Cash_month_data=$this->previous_Cash_month_data($home_id,$year,$month);
        $cash = Cash::getAllCash()
        ->where(['home_id'=>$home_id])
        ->whereMonth('cash_date', $month)
        ->whereYear('cash_date', $year)
        ->get();
        // echo "<pre>";print_r($cash);die;
        $entercash=0;
        $balance_bfwd=0;
        $petty_cashIn=0;
        $cash_out=0;
        foreach($cash as $val){
            if($entercash == 0){
                $balance_bfwd=$val->balance_bfwd;
                $entercash=1;
            }
            $petty_cashIn=$petty_cashIn+$val->petty_cashIn;
            $cash_out=$cash_out+$val->cash_out;
        }
        $sum=$previous_Cash_month_data['total_balanceInCash']+$petty_cashIn;
        return $calculate=$sum-$cash_out;
    }
    public function cash_delete(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), ['id'=>'required|integer|exists:cashes,id']);
        
        if ($validator->fails()) {
            return response()->json(['success'=>false,'message' => $validator->errors()->first(),'data'=>array()]);
        }
        $cash=Cash::find($request->id);
        if($cash){
            $cash->update(['deleted_at' => Carbon::now()]);
            return response()->json(['success'=>true,'message'=>'Cash deleted successfully done','data'=>array()]);
        }else{
            return response()->json(['success'=>false,'message'=>'Invalid Id given','data'=>array()]);
        }
    }
    public function expand_card_filter(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $startDate=Carbon::parse($request->startDate)->format('Y-m-d');
        $endDate=Carbon::parse($request->endDate)->format('Y-m-d');
        $year=$request->year;
        $month=$request->month;
        $home_id=Session::get('scitsAdminSession')->home_id;

        $cash=Cash::getAllCash()
        ->where(['home_id'=>$home_id])
        ->whereMonth('cash_date', $month)
        ->whereYear('cash_date', $year)
        ->sum('petty_cashIn');
        // echo "<pre>";print_r($cash);die;
        $query = ExpendCard::getAllExpendCard()
        ->where(['home_id'=>$home_id])
        ->whereMonth('expend_date', $month)
        ->whereYear('expend_date', $year);
        $search_data = $query->get();
        $previous_month_data=$this->previous_month_data($home_id,$month,$year);
        // return $previous_month_data;
        // echo "<pre>";print_r($previous_month_data);die;
        $html_data='';
        $enterInLoop=0;
        $index=0;
        if(!empty($previous_month_data) && $previous_month_data['previousbalanceOnCard'] !=0){ 
            $enterInLoop=1;
            $html_data.='<tr>
                            <td>'.++$index.'</td>
                            <td>'. $previous_month_data['prvious_date'] .'</td>
                            <td>£'. number_format($previous_month_data['previousbalanceOnCard'] ?? 0,2) .'</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
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
                <td class="white_space_nowrap">'. date("Y-m-d",strtotime($val->expend_date)) .'</td>';
                if($enterInLoop !=1 && $date == null){$date=$db_date;
                    $html_data.='<td>£'. number_format($val->balance_bfwd ?? 0, 2) .'</td>';
                 }else{
                    $html_data.='<td></td>';
                 }
                 $fund_added='';
                 if(isset($val->fund_added) && $val->fund_added !=''){$fund_added= '£'.$val->fund_added;}
                 $html_data.='<td>'. $fund_added .'</td>
                <td>£'. number_format($val->purchase_amount ?? 0,2) .'</td>
                <td>'. $val->card_details .'</td>';
                if($val->receipt){
                    $html_data.='<td><a href="'. url("public/images/finance_petty_cash/".$val->receipt) .'" target="_blank"><i class="fa fa-eye"></i></a></td>';
                }else{
                    $html_data.='<td></td>';
                }
                $html_data.='<td>'. $dext .'</td>
                <td>'. $invoice_la .'</td>
                <td>'. $val->initial .'</td>
                <td><a href="javascript:void(0)" class="openModalBtn" data-toggle="modal" data-target="#expend_card" data-action="edit" data-id="'.$val->id.'" data-expend_date="'.$val->expend_date.'" data-balance_bfwd="'.$val->balance_bfwd.'" data-fund_added="'.$val->fund_added.'" data-purchase_amount="'.$val->purchase_amount.'" data-card_details="'.$val->card_details.'" data-receipt="'.$val->receipt.'" data-dext="'.$val->dext.'" data-invoice_la="'.$val->invoice_la.'" data-initial="'.$val->initial.'" id=""><i class="fa fa-pencil" aria-hidden="true"></i></a> | <a href="javascript:void(0)" class="deleteBtn" data-id="'.$val->id.'"><i class="fa fa-trash radStar" aria-hidden="true"></i></a></td>
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
            $balanceOnCard=$previous_month_data['previousbalanceOnCard'];
        }
        // echo "<pre>";print_r($html_data);die;
        return response()->json(['success'=>true,'message'=>'Filtered Data','data'=>$search_data,'html_data'=>$html_data,'balanceOnCard'=>$balanceOnCard,'totalBalancebfwd'=>($totalBalancebfwd) ? $totalBalancebfwd: $previous_month_data['previousbalanceOnCard'],'totalBalanceFund'=>$totalBalanceFund,'sumPurchaseCashIn'=>$sumPurchaseCashIn]);
    }
    public function cash_filter(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $startDate=Carbon::parse($request->startDate)->format('Y-m-d');
        $endDate=Carbon::parse($request->endDate)->format('Y-m-d');
        $year=$request->year;
        $month=$request->month;
        // return $data=['year'=>$year,'month'=>$month];
        $home_id=Session::get('scitsAdminSession')->home_id;
        $query = Cash::getAllCash()
        ->where(['home_id'=>$home_id])
        ->whereMonth('cash_date', $month)
        ->whereYear('cash_date', $year);
        // if ($request->filled('startDate') && $request->filled('endDate')) {
        //     $query->whereBetween('cash_date', [$startDate, $endDate]);
        // }
        $search_data = $query->get();
        // echo "<pre>";print_r($search_data);die;
        $previous_Cash_month_data=$this->previous_Cash_month_data($home_id,$year,$month);
        // echo "<pre>";print_r($previous_Cash_month_data);die;
        $total_balance=0;
        $cash_out=0;
        $count=0;
        $balance_bfwd=0;
        $petty_cashIn=0;
        $html_data='';
        $date=null;
        $index=0;
        if (!empty($previous_Cash_month_data) && $previous_Cash_month_data['total_balanceInCash'] != 0) {
            $count = 1; 

            $html_data.='<tr>
                <td>'.++$index.'</td>
                <td>'.$previous_Cash_month_data['prvious_date'].'</td>
                <td>£'.$previous_Cash_month_data['total_balanceInCash'].'</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>';
        }
        foreach($search_data as $key=>$val){
            $cash_out=$cash_out+$val->cash_out;
            $petty_cashIn=$petty_cashIn+$val->petty_cashIn;
            if($count == 0){
                $count=1;
                $balance_bfwd=$val->balance_bfwd;
                $total_balance=$val->balance_bfwd;
            }
            $total_balance=$total_balance+$val->petty_cashIn;
            $dext='';
            $invoice_la='';
            if($val->dext == 1){ $dext= "Yes";}else{ $dext= "No"; }
            if($val->invoice_la == 1){ $invoice_la= "Yes"; }else{ $invoice_la= "No" ;}
            $db_date=date('m',strtotime($val->cash_date));
            $html_data.='<tr>
                            <td>'. ++$index .'</td>
                            <td class="white_space_nowrap">'. date("Y-m-d",strtotime($val->cash_date)) .'</td>';
                            if($date != $db_date || $date == null){$date=$db_date;
                                // $html_data.='<td>£'. ($val->balance_bfwd ?? 0) .'</td>';
                                $html_data.='<td></td>';
                            }else{
                                $html_data.='<td></td>';
                            }
                            $html_data.='<td>£'. ($val->petty_cashIn ?? 0) .'</td>
                            <td>£'. ($val->cash_out ?? 0) .'</td>
                            <td>'. $val->card_details .'</td>';
                            if($val->receipt){
                                $html_data.='<td><a href="'.url("public/images/finance_cash/".$val->receipt) .'" target="_blank"><i class="fa fa-eye"></i></a></td>';
                            }else{
                                $html_data.='<td></td>';
                            }
                            $html_data.='<td>'. $dext .'</td>
                            <td>'. $invoice_la .'</td>
                            <td>'. $val->initial .'</td>
                            <td><a href="javascript:void(0)" class="openModalBtn" data-toggle="modal" data-target="#petty_cash" data-action="edit" data-id="'.$val->id.'" data-cash_date="'.$val->cash_date.'" data-balance_bfwd="'.$val->balance_bfwd.'" data-petty_cashin="'.$val->petty_cashIn.'" data-cash_out="'.$val->cash_out.'" data-card_details="'.$val->card_details.'" data-receipt="'.$val->receipt.'" data-dext="'.$val->dext.'" data-invoice_la="'.$val->invoice_la.'" data-initial="'.$val->initial.'" id=""><i class="fa fa-pencil" aria-hidden="true"></i></a> | <a href="javascript:void(0)" class="deleteBtn" data-id="'.$val->id.'"><i class="fa fa-trash radStar" aria-hidden="true"></i></a></td>
                        </tr>';

        }
        $calculate=$previous_Cash_month_data['total_balanceInCash']+$total_balance;
        $total_balanceInCash=$calculate-$cash_out;
        return response()->json(['success'=>true,'message'=>'Filtered Data','data'=>$search_data,'html_data'=>$html_data,'total_balance'=>$balance_bfwd,'cash_out'=>$cash_out,'balance_bfwd'=>$balance_bfwd,'petty_cashIn'=>$petty_cashIn,'total_balanceInCash'=>$total_balanceInCash]);
    }
}
