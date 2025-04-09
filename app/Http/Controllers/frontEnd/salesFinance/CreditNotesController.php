<?php

namespace App\Http\Controllers\frontEnd\salesFinance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\CreditNotes;
use Illuminate\Support\Facades\Validator;
use Auth,Log,PDF;
use App\Admin;
use App\Models\Job;
use App\Models\Country;
use App\Models\Product;
use App\Models\Currency;
use App\Models\Supplier;
use App\Models\Job_title;
use App\Models\CreditNote;
use App\Models\PurchaseOrder;
use App\Models\CreditNoteEmail;
use App\Models\Product_category;
use App\Models\CreditNoteProduct;
use App\Models\CreditNoteAllocate;
use App\Models\Construction_account_code;
use App\Models\PurchaseOrderRecordPayment;
use App\Models\Constructor_additional_contact;

class CreditNotesController extends Controller
{
    public function credit_notes(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $home_id=Auth::user()->home_id;
        $lastSegment = $request->list_mode;
        $segment_check=$this->check_segment_CreditNote($lastSegment);
        // echo "<pre>"; print_r($segment_check);die;
        $data['list']=CreditNote::with('suppliers','creditNoteProducts')->where(['loginUserId'=>Auth::user()->id,'deleted_at'=>null,'status'=>$segment_check['status']])->get();
        $data['status']=$segment_check;
        $data['approvedtCount']=CreditNote::where(['loginUserId'=>Auth::user()->id,'deleted_at'=>null,'status'=>1])->count();
        $data['paidCount']=CreditNote::where(['loginUserId'=>Auth::user()->id,'deleted_at'=>null,'status'=>2])->count();
        $data['cancelledCount']=CreditNote::where(['loginUserId'=>Auth::user()->id,'deleted_at'=>null,'status'=>0])->count();
       
        // $data['users'] = User::where('home_id', $home_id)->select('id', 'name','email','phone_no')->where('is_deleted', 0)->get();
        
        // echo "<pre>";print_r($data['list']);die;
        // $data['list']=array();
        $data['page']="finance";
        return view('frontEnd.salesAndFinance.credit_notes.credit_notes_list',$data);
    }
    private function check_segment_CreditNote($lastSegment=null){
        if($lastSegment === 'Approved'){
            return ['status'=>1,'list_status'=>'Approved'];
        }else if($lastSegment === 'Paid'){
            return ['status'=>2,'list_status'=>'Paid'];
        }else{
            return ['status'=>0,'list_status'=>'Cancelled'];
        }
    }
    public function new_credit_notes(Request $request){
        $home_id=Auth::user()->home_id;
        $user_id=Auth::user()->id;
        $data['home_id']=$home_id;
        $key=base64_decode($request->key);
        $credit_note=CreditNote::find($key);
        $data['credit_note']=$credit_note;
        $data['country']=Country::all_country_list();
        $data['currency']=Currency::where(['status'=>1,'deleted_at'=>null])->get();
        $data['job_title']=Job_title::where(['home_id'=>$home_id,'status'=>1])->get();
        $data['suppliers']=Supplier::allGetSupplier($home_id,$user_id)->where('status',1)->get();
        $data['product_categories'] = Product_category::with('parent', 'children')->where('home_id',Auth::user()->home_id)->where('status',1)->where('deleted_at',NULL)->get();
        $data['additional_contact'] = Constructor_additional_contact::where(['home_id'=> $home_id,'userType'=>2,'customer_id'=>$key,'deleted_at'=>null])->get();
        $data['page']="finance";
        return view('frontEnd.salesAndFinance.credit_notes.new_credit_notes',$data);
    }
    public function credit_notes_save(CreditNotes $request){
        $validated = $request->validated();
        // echo "<pre>";print_r($request->all());die;
        if(empty($request->product_id) && !isset($request->product_id)){
            return response()->json(['vali_error' => 'Please add at least one product for Credit Note.']);
        }
        try {
            $requestData=$request->all();
            if($request->id == ''){
                $credit_ref=$this->create_credit_ref();
                $requestData['credit_ref'] = $credit_ref;
            }
            // echo "<pre>";print_r($requestData);die;
            $requestData['home_id']=Auth::user()->home_id;
            $requestData['loginUserId']=Auth::user()->id;
            $credit_notes=CreditNote::saveCreditNotes($requestData);
            if(!empty($request->product_id) && count($request->product_id)>0){
                $requestData['credi_note_id'] = $credit_notes->id;
                $credit_notesProduct=$this->savecredit_notesProduct($requestData);
                $responseData = $credit_notesProduct->getData(true);
                if (empty($responseData['success']) || $responseData['success'] === false) {
                    return response()->json(['success' => false,'message' => 'Something went wrong.','data' => $responseData,]);
                }
            }
            if($request->id == ''){
                return response()->json(['success' => true,'message'=>'The Credit Note has been saved succesfully.', 'data' => $credit_notes]);
            }else{
                return response()->json(['success' => true,'message'=>'The Credit Note has been updated succesfully.', 'data' => $credit_notes]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    private function create_credit_ref(){
        $credit_count=CreditNote::count();
        if($credit_count == 0 || $credit_count <10){
           return $credit_ref='CN-00'.$credit_count+1;
        }else if($credit_count >=10 && $credit_count<100){
           return $credit_ref='CN-0'.$credit_count+1;
        }else{
            return $credit_ref='CN-'.$credit_count+1;
        }
    }
    public function savecredit_notesProduct($data){
        // echo "<pre>";print_r($data);die;
        try {
            $product_ids = $data['product_id'];
            $success = 0;
            $outstandignAmount=0;
            for ($i = 0; $i < count($product_ids); $i++) {
                $sub_total=$data['qty'][$i]*$data['price'][$i];
                $vatPercentage=$sub_total*$data['vat_ratePercentage'][$i]/100;
                $outstandignAmount=$outstandignAmount+$sub_total+$vatPercentage;
                $productData = [
                    'id'=>$data['creditProduct_id'][$i] ?? null,
                    'user_id'=>Auth::user()->id,
                    'userType'=>2,
                    'credi_note_id' => $data['credi_note_id'],
                    'product_id' => $product_ids[$i],
                    'description' => $data['description'][$i] ?? null,
                    'accountCode_id' => $data['accountCode_id'][$i] ?? null,
                    'qty' => $data['qty'][$i] ?? 0,
                    'price' => $data['price'][$i] ?? 0,
                    'vat_id' => $data['vat_id'][$i] ?? null,
                    'vat' => $data['vat_ratePercentage'][$i] ?? 0,
                ];
                // echo "<pre>";print_r($productData);die;
                
                $credit_notesProduct = CreditNoteProduct::saveCreditProduct($productData);
                if ($credit_notesProduct) {
                    $success++;
                }
            }
            $check=CreditNote::find($data['credi_note_id']);
            // if($check->balance_credit !=''){
            //     $outstandignAmount=$check->balance_credit+$outstandignAmount;
            // }
            $check->update(['balance_credit' => $outstandignAmount]);
            if ($success === count($product_ids)) {
                return response()->json(['success' => true, 'message' => 'All products saved successfully.']);
            } else {
                return response()->json(['success' => false, 'message' => 'Some products could not be saved.']);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function searchCreditNotes(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $credit_ref=$request->credit_ref;
        $supplier=$request->supplier;
        $startDate=$request->startDate;
        $endDate=$request->endDate;
        $created_by=$request->created_by;
        $po_posted=$request->po_posted;
        $keywords=$request->keywords;
        $status=$request->status;
        $list_status=$request->list_status;
        $selectedsupplierId=$request->selectedsupplierId;
        $selectedCustomerId=$request->selectedCustomerId;
        $selectedcreatedById=$request->selectedcreatedById;
        $home_id=Auth::user()->home_id;
        $query = CreditNote::with('suppliers','creditNoteProducts')->where(['loginUserId'=>Auth::user()->id,'deleted_at'=>null,'status'=>$status]);
        // echo "<pre>";print_r($query->get());die;
        if ($request->filled('credit_ref')) {
            $query->where('credit_ref', $credit_ref);
        }
        if ($request->filled('supplier')) {
            $query->where('supplier_id', $selectedsupplierId);
        }
        if ($request->filled('startDate') && $request->filled('endDate')) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }
        if ($request->filled('created_by')) {
            $query->where('loginUserId', $selectedcreatedById);
        }
        
        if ($request->filled('keywords')) {
            $query->where(function ($q) use ($keywords) {
                $q->where('credit_ref', 'LIKE', '%' . $keywords . '%')
                ->orWhere('name', 'LIKE', '%' . $keywords . '%')
                ->orWhere('county', 'LIKE', '%' . $keywords . '%')
                ->orWhere('supplier_ref', 'LIKE', '%' . $keywords . '%');
            });
        }
        // echo "<pre>";print_r($query->get());die;
        // echo $query->toSql();
        // print_r($query->getBindings());
        // die;
        $search_data = $query->where('loginUserId',Auth::user()->id)->get();
        // echo "<pre>";print_r($search_data);die;
        $array_data='';
        $all_subTotalAmount=0;
        $all_vatTotalAmount=0;
        $all_TotalAmount=0;
        $outstandingAmountTotal=0;
        foreach($search_data as $key=>$val){
            $sub_total_amount=0;
            $total_amount=0;
            $vat_amount=0;
            $creditProductId=0;
            $product_id=0;
            foreach($val->creditNoteProducts as $product){
                $creditProductId=$product->id;
                $product_id=$product->product_id;
                $qty=$product->qty*$product->price;
                $sub_total_amount=$sub_total_amount+$qty;
                $vat=$qty*$product->vat/100;
                $vat_amount=$vat_amount+$vat;
                $total_amount=$total_amount+$vat+$qty;
                
            }
            $all_subTotalAmount=$all_subTotalAmount+$sub_total_amount;
            $all_vatTotalAmount=$all_vatTotalAmount+$vat_amount;
            $all_TotalAmount=$all_TotalAmount+$total_amount;
            $outstandingAmountTotal=$outstandingAmountTotal+$val->balance_credit;
            $supplier_name=$val->suppliers->name;
            $supplier_email=$val->suppliers->email;
            $array_data .= '<tr>
                        <td><input type="checkbox" class="delete_checkbox" value="' . $val->id . '"></td>
                        <td>' . ++$key . '</td>
                        <td>' . $val->credit_ref . '</td>
                        <td>' . $val->suppliers->name . '</td>
                        <td>' . date('d/m/Y',strtotime($val->date)) . '</td>
                        <td>£' . $sub_total_amount . '</td>
                        <td>£' . $vat_amount . '</td>
                        <td>£' . $total_amount . '</td>
                        <td>£' . $val->balance_credit . '</td>
                        <td>' . $list_status . '</td>
                        <td>' . $val->telephone . '</td>
                        <td>' . $val->mobile . '</td>';
                        if($status == 1){
                            
                            $array_data.='
                            <td>
                                <div class="d-flex justify-content-end">
                                    <div class="nav-item dropdown">
                                        <a href="#!" class="nav-link dropdown-toggle profileDrop" data-bs-toggle="dropdown" aria-expanded="false">
                                            Action
                                        </a>
                                        <div class="dropdown-menu fade-up m-0">
                                            <a href="'.url('credit_note_edit?key=').''.base64_encode($val->id).'" class="dropdown-item">Edit</a>
                                            <hr class="dropdown-divider">
                                            <a href="'.url('credit_preview?key=').''.base64_encode($val->id).'" target="_blank" class="dropdown-item">Preview</a>
                                            <hr class="dropdown-divider">
                                            <a href="javascript:void(0)" onclick="openEmailModal('.$val->id.','."'$val->credit_ref'".','."'$supplier_email'".','."'$supplier_name'".')" class="dropdown-item">Email</a>
                                            <hr class="dropdown-divider">
                                            <a href="javascript:void(0)" onclick="openAllocateModal('.$val->id.','."'$val->credit_ref'".','.$val->supplier_id.','."'$supplier_name'".','.$val->balance_credit.','.$product_id.')" class="dropdown-item">Allocate</a>
                                            <hr class="dropdown-divider">
                                            <a href="javascript:void(0)" onclick="cancelCreditFunction('.$val->id.','."'$val->credit_ref'".')" class="dropdown-item">Cancel Credit Note</a>
                                            <hr class="dropdown-divider">
                                            <a href="#!" class="dropdown-item"CRM / History</a>
                                        </div>
                                    </div>
                                </div>
                            </td>';
                        }else{
                            $array_data .= '
                                        <td>
                                            <div class="d-flex justify-content-end">
                                                <div class="nav-item dropdown">
                                                    <a href="#!" class="nav-link dropdown-toggle profileDrop" data-bs-toggle="dropdown" aria-expanded="false">
                                                        Action
                                                    </a>
                                                    <div class="dropdown-menu fade-up m-0">
                                                        <a href="' . url('purchase_order_edit?key=') . base64_encode($val->id) . '" class="dropdown-item">Edit</a>
                                                        <hr class="dropdown-divider">
                                                        <a href="'.url('credit_preview?key=').''.base64_encode($val->id).'" target="_blank" class="dropdown-item">Preview</a>
                                                        <hr class="dropdown-divider">
                                                        <a href="'.url('credit_preview?key=').''.base64_encode($val->id).'" target="_blank" class="dropdown-item">Print</a>
                                                        <hr class="dropdown-divider">
                                                        <a href="#!" class="dropdown-item">Email</a>
                                                        <hr class="dropdown-divider">
                                                        <a href="#!" class="dropdown-item"CRM / History</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>';
                        }
                        
                    $array_data.='</tr>';
        }

        return response()->json(['data' => $array_data,'all_subTotalAmount'=>$all_subTotalAmount,'all_vatTotalAmount'=>$all_vatTotalAmount,'all_TotalAmount'=>$all_TotalAmount,'outstandingAmountTotal'=>$outstandingAmountTotal]);
    }
    public function getCreditProduct(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $home_id=Auth::user()->home_id;
        $creditproducts = CreditNote::with(['creditNoteProducts'])
        ->where(['id' => $request->id, 'deleted_at' => null])
        ->first();
        // return $creditproducts;
        if (!$creditproducts) {
            return response()->json(['success' => false, 'message' => 'Purchase Order not found.']);
        }
        
        $tax = Product::tax_detail($home_id);
        $all_job = Job::getAllJob($home_id)->where('status', 1)->get();
        $accountCode = Construction_account_code::getActiveAccountCode($home_id);
        
        if ($creditproducts->creditNoteProducts->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No products found for this purchase order.']);
        }
        
        $creditProduct_paginated = $creditproducts->creditNoteProducts()
            ->paginate(10);
        
        $data_array = [];
        foreach ($creditProduct_paginated as $val) {
            $crediProduct_detail = Product::product_detail($val->product_id);
        
            $data_array[] = [
                'product_details' => $creditproducts,
                'tax' => $tax,
                'all_job' => $all_job,
                'accountCode' => $accountCode,
                'crediProduct_detail' => $crediProduct_detail,
            ];
        }
        
        return response()->json([
            'success' => true,
            'data' => $data_array,
            'pagination' => [
                'total' => $creditProduct_paginated->total(),
                'current_page' => $creditProduct_paginated->currentPage(),
                'last_page' => $creditProduct_paginated->lastPage(),
                'per_page' => $creditProduct_paginated->perPage(),
                'next_page_url' => $creditProduct_paginated->nextPageUrl(),
                'prev_page_url' => $creditProduct_paginated->previousPageUrl(),
            ]
        ]);
    }
    public function cancelCreditNote(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $creditNote = CreditNote::find($request->id);
        try{
            if ($creditNote) {
                $creditNote->update(['status' => 0]);
            }
            return response()->json(['success'=>true,'message'=>'Credit Note Cancelled']);
        }catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
        
    }
    public function crediNoteEmailSave(Request $request){
        $validator = Validator::make($request->all(), [
            'selectedToEmail' => 'required',
            'subject' => 'required',
            'credit_id' => 'required',
        ],
        [
            'selectedToEmail.required' => 'To field is required.',
            'subject.required' => 'Subject field is required.',
            'credit_id.required' => 'Credit Note Id does not match.',
        ]);
        if ($validator->fails()) {
            return response()->json(['vali_error' => $validator->errors()->first()]);
        }
        try{
            $data=$request->all();
            $data['home_id']=Auth::user()->home_id;
            $data['loginUserId']=Auth::user()->home_id;
            $data['to'] = json_encode(explode(',', $request->selectedToEmail));
            $data['cc'] = $request->selectedToEmail1 ? json_encode(explode(',', $request->selectedToEmail1)) : json_encode([]);
            // echo "<pre>";print_r($data);die;
            $email=CreditNoteEmail::saveCreditNoteEmail($data);
            return response()->json(['success'=>true,'message'=>'The Email has been saved successfully.','data'=>$email]);
        }catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function credit_preview(Request $request){
        try{
            $credit_id=base64_decode($request->key);
            $credit_details=CreditNote::with('suppliers','creditNoteProducts')->where(['id' => $credit_id, 'deleted_at' => null])
            ->first();
            // echo "<pre>";print_r($credit_details);die;
            
            $data=[
                'email'=>Auth::user()->email,
                'phone_no'=>Auth::user()->phone_no,
                'job_title'=>Auth::user()->job_title,
                'current_location'=>Auth::user()->current_location,
                'company'=>Admin::find(Auth::user()->company_id)->company ?? "",
                'credit_details'=>$credit_details,
            ];
            // echo "<pre>";print_r($data);die;
            $pdf = PDF::loadView('frontEnd.salesAndFinance.credit_notes.creditNotePDF',$data)->setPaper('a4', 'portrait');
            return $pdf->stream('creditNotePDF.pdf');
        }catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function getAllSupplierPurchaseOrder(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $supplier_id=$request->supplier_id;
        $purchaseOrderList=PurchaseOrder::with('purchaseOrderProducts')->where('supplier_id',$supplier_id)->where('status',3)->get();
        // $purchaseOrderList=PurchaseOrder::with('purchaseOrderProducts')->where('supplier_id',$supplier_id)->whereNotIn('status', [1,5])->get();
        return response()->json(['success'=>true,'message'=>'Purchase Order List with Supplier','data'=>$purchaseOrderList]);
    }
    public function crediNoteAllocateSave(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $purchase_order_id=$request->purchase_order_id;
        $credit_id=$request->credit_id;
        $amount=$request->amount;
        if(count($amount)>0){
            $total_amount=0;
            for($i=0;$i<count($amount);$i++){
                $total_amount=$total_amount+$amount[$i];
                $data=[
                    'home_id'=>Auth::user()->home_id,
                    'loginUserId'=>Auth::user()->id,
                    'loginUserName'=>Auth::user()->name,
                    'po_id'=>$purchase_order_id[$i],
                    'credit_id'=>$credit_id,
                    'amount_paid'=>$amount[$i],
                    // 'product_id'=>$request->product_id,
                    'supplier_id'=>$request->supplier_id,
                    'date'=>$request->date,
                ];
                try{
                    // $saveData=CreditNoteAllocate::saveCreditAllocate($data);
                    $this->update_outstandingAmount($amount[$i],$purchase_order_id[$i],$credit_id);
                }catch (\Exception $e) {
                    return response()->json(['error' => $e->getMessage()], 500);
                }
            }
            return response()->json(['success'=>true,'message'=>'Credit note has been successfully allocated to selected purchase order']); 
        }
    }
    private function update_outstandingAmount($total_amount,$purchase_order_id,$credit_id){
        // echo "yha";die;
        $tableCreditNote=CreditNote::find($credit_id);
        $creditAmount=$tableCreditNote->balance_credit;
        $final_amountCredit=$creditAmount-$total_amount;
        $tableCreditNote->balance_credit=$final_amountCredit;
        if($final_amountCredit == 0){
            $tableCreditNote->status=2;
        }
        $tableCreditNote->save();
            $tablePurchaseOrder=PurchaseOrder::find($purchase_order_id);
            $orderAmount=$tablePurchaseOrder->outstanding_amount;
            $final_amountOrder=$orderAmount-$total_amount;
            $tablePurchaseOrder->outstanding_amount=$final_amountOrder;
            if($final_amountOrder == 0){
                $tablePurchaseOrder->status=5;
            }
            $tablePurchaseOrder->save();
    }
}
