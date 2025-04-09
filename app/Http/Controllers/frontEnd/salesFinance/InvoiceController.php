<?php

namespace App\Http\Controllers\frontEnd\salesFinance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use PDF;
use App\Models\Construction_account_code;
use App\Models\Construction_tax_rate;
use App\Models\Country;
use App\Models\Customer_type;
use App\Models\Job_title;
use App\Models\Currency;
use App\Models\Region;
use App\Models\Product_category;
use App\Models\Invoice\Invoice;
use App\Models\Invoice\InvoiceProduct;
use App\Admin;
use App\Models\Project;
use App\Models\Constructor_additional_contact;
use App\Models\Constructor_customer_site;
use App\Models\Product;
use App\Models\Payment_type;
use App\Models\Task_type;
use App\Models\Invoice\InvoiceAttachment;
use App\Models\Invoice\InvoiceReminder;
use App\Models\Invoice\InvoiceNewTask;
use App\User;

use App\Http\Controllers\frontEnd\salesFinance\CustomerController;

class InvoiceController extends Controller
{

    public function dashboard(){
        return view('frontEnd.salesAndFinance.invoice.dashboard');
    }
    public function account_codes(Request $request){
        $home_id = Auth::user()->home_id;
        $data['account_codes']=Construction_account_code::getAllAccount_Codes($home_id);
        $data['home_id']=$home_id;
        $data['page']='setting';
        return view('frontEnd.salesAndFinance.jobs.account_code',$data);
    }

    public function save_account_code(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['vali_error' => $validator->errors()->first()]);
        }
        try {
            $data=Construction_account_code::saveAccount_Codes(array_merge(['home_id' => Auth::user()->home_id], $request->all()));
            return response()->json(['data' => $data,  'message' => $data ? "Account Code save succcessfully" : 'Account Code could not be added.']);
        } catch (\Exception $e) {
            Log::error('Error saving Tag: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to save Tag. Please try again.'], 500);
        }
    }

    public function tax_rate(Request $request){
        $mode=$request->mode;
        $home_id = Auth::user()->home_id;
        $data['tax_rate'] = Construction_tax_rate::getAllTax_rate($home_id,$mode);
        $data['home_id'] = $home_id;
        $data['page']='setting';
        return view('frontEnd.salesAndFinance.jobs.tax_rate',$data);
    }

    public function save_tax_rate(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:construction_tax_rates,name|string',
            'tax_rate' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['vali_error' => $validator->errors()->first()]);
        }
        try{
            $data=Construction_tax_rate::saveTax_rate($request->all());
            if($request->id == ''){
                return response()->json(['success'=>true,'message'=>'Tax Rate added successfully done','data' => $data]);
            }else{
                return response()->json(['success'=>true,'message'=>'Tax Rate updated successfully done','data' => $data]);
            }
        }catch (\Exception $e) {
            Log::error('Error saving Tag: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
        
    }

    public function getAccountCode(){
        $data =  Construction_account_code::getAllAccount_Codes(Auth::user()->home_id);

        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? $data : 'No data.'
        ]);
    }

    public function getActiveAccountCode(){
        $data =  Construction_account_code::getActiveAccountCode(Auth::user()->home_id);

        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? $data : 'No data.'
        ]);
    }

    public function getActiveTaxRate(){
        $data = Construction_tax_rate::getAllTax_rate(Auth::user()->home_id, "Active");

        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? $data : 'No data.'
        ]);
    }

    public function getTaxRateOnTaxId(Request $request){

        $data = Construction_tax_rate::getTaxRateOnId($request->id);

        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? $data : 'No data.'
        ]);
    }

    public function create(Request $request,CustomerController $customer){
        // echo "<pre>";print_r($request->all());die;
        $id=base64_decode($request->key);
        $data['page'] = "invoice";
        $home_id = Auth::user()->home_id;
        $invoice=Invoice::find($id);
        $projects=array();
        $additional_contact=array();
        $site=array();
        $reminder_data=array();
        if($invoice){
            $projects=Project::where(['status'=>1,'home_id'=>$home_id])->get();
            $additional_contact = Constructor_additional_contact::where(['home_id'=> $home_id,'userType'=>1,'customer_id'=>$invoice->customer_id,'deleted_at'=>null])->get();
            $site=Constructor_customer_site::where('customer_id',$invoice->customer_id)->get();
            $reminder_data=$this->reminder_check($id);
        }
        $data['projects']=$projects;
        $data['additional_contact']=$additional_contact;
        $data['site']=$site;
        $data['customers'] =  $customer->getAllCustomerList()->getData()->data;
        $data['countries'] = Country::getCountriesNameCode();
        $data['customer_types']=Customer_type::where(['home_id'=>$home_id,'status'=>1])->get();
        $data['job_title']=Job_title::where(['home_id'=>$home_id,'status'=>1])->get();
        $data['country']=Country::all_country_list();
        $data['currency']=Currency::where(['status'=>1,'deleted_at'=>null])->get();
        $data['region']=Region::where(['home_id'=>$home_id,'status'=>1,'deleted_at'=>null])->get();
        $data['product_categories'] = Product_category::with('parent', 'children')->where('home_id',Auth::user()->home_id)->where('status',1)->where('deleted_at',NULL)->get();
        $data['invoice']=$invoice;
        $data['reminder_data']=$reminder_data;
        // dd($data);
        return view('frontEnd.salesAndFinance.invoice.invoice_form', $data);
    }
    public function invoice_save(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $home_id=Auth::user()->home_id;
        $user_id=Auth::user()->id;
        
        $validator = Validator::make($request->all(), [
            'customer_id'=>'required',
            'name'=>'required',
            'address'=>'required',
            'invoice_date'=>'required',
            'due_date'=>'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['vali_error' => $validator->errors()->first()]);
        }
        if(empty($request->product_id) && !isset($request->product_id)){
            return response()->json(['vali_error' => 'Please add at least one product for Invoice.']);
        }
        try {
            if(!empty($request->purchaseattachment_id)){
                $purchaseattachment_id=$request->purchaseattachment_id;
                for($i=0;$i<count($purchaseattachment_id);$i++){
                    $poTable=InvoiceAttachment::find($purchaseattachment_id[$i]);
                    $poTable->title=$request->purchaseattachment_title[$i];
                    $poTable->save();
                }
            }
            
            $requestData = $request->all();
            if($request->id == ''){
                $inv_ref=$this->create_invoice_ref();
                $requestData['invoice_ref'] = $inv_ref;
            }
            $requestData['home_id'] = $home_id;
            $requestData['sub_total'] = 0;
            $requestData['deposit_percentage'] = 0;
            $requestData['VAT_id'] = 0;
            $requestData['VAT_amount'] = 0;
            $requestData['Total'] = 0;
            $requestData['outstanding'] = 0;
            // $requestData['invoice_date'] = Carbon::createFromFormat('d/m/Y', $request->invoice_date)->format('Y-m-d');
            // $requestData['due_date'] = Carbon::createFromFormat('d/m/Y', $request->due_date)->format('Y-m-d');
            
            // echo "<pre>";print_r($requestData);die;
            $invoice=Invoice::saveInvoice($requestData);
            if(!empty($request->product_id) && count($request->product_id)>0){
                $requestData['invoice_id'] = $invoice->id;
                $InvoiceProduct=$this->saveInvoiceProduct($requestData);
                $responseData = $InvoiceProduct->getData(true);
                if (empty($responseData['success']) || $responseData['success'] === false) {
                    return response()->json(['success' => false,'message' => 'Something went wrong.','data' => [],]);
                }
            }
            if($request->id == ''){
                return response()->json(['success' => true,'message'=>'The Invoice has been saved succesfully.', 'data' => $invoice]);
            }else{
                return response()->json(['success' => true,'message'=>'The Invoice has been updated succesfully.', 'data' => $invoice]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    
    }
    private function create_invoice_ref(){
        $invoice_count=Invoice::count();
        if($invoice_count == 0 || $invoice_count <10){
           return $invoice_ref='Inv-00'.$invoice_count+1;
        }else if($invoice_count >=10 && $invoice_count<100){
           return $invoice_ref='Inv-0'.$invoice_count+1;
        }else{
            return $invoice_ref='Inv-'.$invoice_count+1;
        }
    }
    public function saveInvoiceProduct($data){
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
                    'id'=>$data['invoice_product_id'][$i] ?? null,
                    'home_id'=>Auth::user()->home_id,
                    'customer_id'=>$data['customer_id'],
                    'invoice_id' => $data['invoice_id'],
                    'product_id' => $product_ids[$i],
                    'description' => $data['description'][$i] ?? null,
                    'code' => $data['code'][$i] ?? null,
                    'accountCode_id' => $data['accountCode_id'][$i] ?? null,
                    'qty' => $data['qty'][$i] ?? 0,
                    'price' => $data['price'][$i] ?? 0,
                    'vat_id' => $data['vat_id'][$i] ?? null,
                    'vat' => $data['vat_ratePercentage'][$i] ?? 0,
                    'discount' => $data['discount'][$i] ?? 0,
                    'discount_type' => $data['discount_type'][$i] ?? 0,
                ];
                Invoice::find($data['invoice_id'])->update(['outstanding' => $outstandignAmount]);
                // echo "<pre>";print_r($productData);die;
                $InvoiceProduct = InvoiceProduct::saveInvoiceProduct($productData);
                if ($InvoiceProduct) {
                    $success++;
                }
            }
            if ($success === count($product_ids)) {
                return response()->json(['success' => true, 'message' => 'All products saved successfully.']);
            } else {
                return response()->json(['success' => false, 'message' => 'Some products could not be saved.']);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function invoice(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $data['key_mode']=$request->key;
        $data['invoice']=Invoice::with(['customers','invoiceProducts','sites'])->where('status',$request->key)->whereNull('deleted_at')->get();
        $data['draft_invoice']=Invoice::getAllInvoices(Auth::user()->home_id)->where('status','Draft')->count();
        $data['outstanding_invoice']=Invoice::getAllInvoices(Auth::user()->home_id)->where('status','Outstanding')->count();
        $data['overdue_invoice']=Invoice::getAllInvoices(Auth::user()->home_id)->where('status','Overdue')->count();
        $data['paid_invoice']=Invoice::getAllInvoices(Auth::user()->home_id)->where('status','Paid')->count();
        // echo "<pre>";print_r($data['invoice']);die;
        return view('frontEnd.salesAndFinance.invoice.Invoice_list', $data);
    }
    public function preview(Request $request){
        // echo "<pre>";print_r(Auth::user());die;
        // echo "<pre>";print_r($request->all());die;
        try{
            $invoice_id=base64_decode($request->key);
            if($request->url === 'print'){
                $invoice_table=Invoice::find($invoice_id);
                $invoice_table->is_printed=1;
                $invoice_table->save();
            }
            die;
            $invoice_details=Invoice::with(['customers','invoiceProducts'])->where(['id' => $invoice_id, 'deleted_at' => null])
            ->first();
            // $site_detail=Customer::find($invoice_details->customer_id);
			// echo "<pre>";print_r($site_detail);die;
            // echo "<pre>";print_r($invoice_details);die;
            
            $data=[
                'email'=>Auth::user()->email,
                'phone_no'=>Auth::user()->phone_no,
                'job_title'=>Auth::user()->job_title,
                'current_location'=>Auth::user()->current_location,
                'company'=>Admin::find(Auth::user()->company_id)->company ?? "",
                'invoice_details'=>$invoice_details,
            ];
            // echo "<pre>";print_r($data);die;
            $pdf = PDF::loadView('frontEnd.salesAndFinance.purchase_order.purchaseOrderPDF',$data);
            return $pdf->stream('frontEnd.salesAndFinance.purchase_order.purchaseOrderPDF');
            // return $pdf->download('purchaseOrderPDF.pdf');
        }catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function getInvoiceProductDetail(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $home_id=Auth::user()->home_id;
        $invoice = Invoice::with(['customers','invoiceProducts','sites'])
        ->where(['id' => $request->id, 'deleted_at' => null])
        ->first();
        // return $invoice;
        if (!$invoice) {
            return response()->json(['success' => false, 'message' => 'Invoice not found.']);
        }
        // return $invoice;
        $tax = Product::tax_detail($home_id);
        $accountCode = Construction_account_code::getActiveAccountCode($home_id);
        $payment_type=Payment_type::getActivePaymentType($home_id);
        if ($invoice->invoiceProducts->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No products found for this Invoice.']);
        }
        $invoice_products_paginated = $invoice->invoiceProducts()
            ->paginate(10);
        
        $data_array = [];
        foreach ($invoice_products_paginated as $val) {
            $invoice_products_detail = Product::product_detail($val->product_id);
        
            $data_array[] = [
                'product_details' => $invoice,
                'tax' => $tax,
                'accountCode' => $accountCode,
                'invoice_products_detail' => $invoice_products_detail,
                'payment_type'=>$payment_type,
            ];
        }
        // $paid_all_amount=$this->getAllPaymentPaid($request->id);
        $paid_all_amount=0;
        return response()->json([
            'success' => true,
            'data' => $data_array,
            'paid_amount'=>$paid_all_amount,
            'pagination' => [
                'total' => $invoice_products_paginated->total(),
                'current_page' => $invoice_products_paginated->currentPage(),
                'last_page' => $invoice_products_paginated->lastPage(),
                'per_page' => $invoice_products_paginated->perPage(),
                'next_page_url' => $invoice_products_paginated->nextPageUrl(),
                'prev_page_url' => $invoice_products_paginated->previousPageUrl(),
            ]
        ]);
    }
    private function getAllPaymentPaid($po_id){
        $purchase_order = DB::table('purchase_order_record_payments')->where(['po_id'=>$po_id,'deleted_at'=>null])->sum('record_amount_paid');
        
        // return $purchase_order;
        $credit_allocate = DB::table('credit_note_allocates')->where(['po_id'=>$po_id,'deleted_at'=>null])->sum('amount_paid');
        
        // $mergedData = $purchase_order->merge($credit_allocate);
        // $sortedData = $mergedData->sortBy('date');

        // $sortedArray = $sortedData->values()->all();
        return $purchase_order+$credit_allocate;
    }
    public function invoice_productsDelete(Request $request){
        // echo "<pre>";print_r($request->all());die;
        try{
            InvoiceProduct::find($request->id)->update(['deleted_at' => now()]);
            return response()->json(['success'=>true,'message'=>'Deleted Successfully done']);
        }catch (\Exception $e) {
            Log::error('Error deleting Invoice Product: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function invoice_attachmentSave(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'file' => 'required|file',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['vali_error' => $validator->errors()->first()]);
        }
        try{
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $imageName = time() . '.' . $file->extension();
                $original_name = $file->getClientOriginalName();
                $mime_type = $file->getMimeType();
                $file_size_bytes = $file->getSize();
                $file->move(public_path('images/invoice_attachment'), $imageName);

                if ($file_size_bytes >= 1073741824) {
                    $file_size = round($file_size_bytes / 1073741824, 2) . ' GB';
                } elseif ($file_size_bytes >= 1048576) { 
                    $file_size = round($file_size_bytes / 1048576, 2) . ' MB';
                } elseif ($file_size_bytes >= 1024) { 
                    $file_size = round($file_size_bytes / 1024, 2) . ' KB';
                } else {
                    $file_size = $file_size_bytes . ' Bytes';
                }

                $requestData = $request->all();
                $requestData['file'] = $imageName;
                $requestData['original_file_name'] = $original_name;
                $requestData['mime_type'] = $mime_type;
                $requestData['size'] = $file_size;
            } else {
                $requestData = $request->all();
            }
            // echo "<pre>";print_r($requestData);die;
            $attachment=InvoiceAttachment::saveInvoiceAttachments($requestData);
            if($request->id == ''){
                return response()->json(['success'=>true,'message'=>"Attachment Added Successfully Done",'data'=>$attachment]);
            }else{
                return response()->json(['success'=>true,'message'=>"Attachment Updated Successfully Done",'data'=>$attachment]);
            }
        }catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function getInvoiceAllAttachmens(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $invioceAttachments = InvoiceAttachment::with(['attachmentType'])->where(['invoice_id'=> $request->id,'deleted_at'=>null])->orderBy('id', 'asc')->paginate(10);

        return response()->json([
            'success' => true, 'data' => $invioceAttachments, 
            'pagination' => [
                    'total' => $invioceAttachments->total(),
                    'current_page' => $invioceAttachments->currentPage(),
                    'last_page' => $invioceAttachments->lastPage(),
                    'per_page' => $invioceAttachments->perPage(),
                    'next_page_url' => $invioceAttachments->nextPageUrl(),
                    'prev_page_url' => $invioceAttachments->previousPageUrl(),
                ]
        ]);
    }
    public function customer_visibleUpdate(Request $request){
        // echo "<pre>";print_r($request->all());die;
        try{
            
            $attachment=InvoiceAttachment::find($request->id);
            $attachment->customer_visible=$request->customer_visibleData;
            $attachment->save();
            if($attachment){
                return response()->json(['success'=>true,'message'=>"Changes succesfully Done",'data'=>$attachment]);
            }
        }catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function mobile_user_visibleUpdate(Request $request){
        // echo "<pre>";print_r($request->all());die;
        try{
            
            $attachment=InvoiceAttachment::find($request->id);
            $attachment->mobile_user_visible=$request->mobile_user_visibleData;
            $attachment->save();
            if($attachment){
                return response()->json(['success'=>true,'message'=>"Changes succesfully Done",'data'=>$attachment]);
            }
        }catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function delete_invoice_attachment(Request $request){
        // echo "<pre>";print_r($request->all());die;
        try{
            InvoiceAttachment::find($request->id)->update(['deleted_at' => now()]);
            return response()->json(['success'=>true,'message'=>'Deleted Successfully done']);
        }catch (\Exception $e) {
            Log::error('Error deleting Invoice Attachments: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function save_reminder(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'reminder_date' => 'required',
            'user_id' => 'required',
            'title' => 'required',
        ],
        [
            'reminder_date.required' => 'Reminder Date field is required.',
            'user_id.required' => 'Reminder email field is required.',
            'title.required' => 'Title field is required.',
        ]);
        if ($validator->fails()) {
            return response()->json(['vali_error' => $validator->errors()->first()]);
        }
        $notification = $request->has('notification') ? 1 : 0;
        $sms = $request->has('sms') ? 1 : 0;
        $email = $request->has('email') ? 1 : 0;

        if ($notification==0 && $sms==0 && $email==0) {
            return response()->json(['vali_error' => 'Send as field is requird']);
        }
        $data=$request->all();
        $data['notification']=$notification;
        $data['sms']=$sms;
        $data['email']=$email;
        $data['home_id']=Auth::user()->home_id;
        $data['loginUserId']=Auth::user()->id;
        $data['invoice_id']=$request->po_id;
        $data['user_id']=implode(',',$request->user_id);
        // echo "<pre>";print_r($data);die;
        try{
            $reminder=InvoiceReminder::saveReminder($data);
            if($request->id){
                return response()->json(['success'=>true,'message'=>'The Reminder has been updated successfully.','data'=>$reminder]);
            }else{
                return response()->json(['success'=>true,'message'=>'The Reminder has been saved successfully.','data'=>$reminder]);
            }
        }catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    
    }
    public static function reminder_check($invoice_id){
        $home_id=Auth::user()->home_id;
        InvoiceReminder::allInvoiceReminderData($home_id)
        ->whereNull('invoice_id')
        ->forceDelete();
        $current_date=Date('Y-m-d');
        InvoiceReminder::allInvoiceReminderData($home_id)
        ->where('invoice_id', $invoice_id)
        ->whereDate('reminder_date', '<', $current_date)
        ->update(['status' => 1]);
        return InvoiceReminder::allInvoiceReminderData($home_id)->where('invoice_id',$invoice_id)->get();
    }
    public function delete_invoice_reminder(Request $request){
        // echo "<pre>";print_r($request->all());die;
        try{
            InvoiceReminder::find($request->id)->update(['deleted_at' => now()]);
            return response()->json(['success'=>true,'message'=>'Deleted Successfully done']);
        }catch (\Exception $e) {
            Log::error('Error deleting Invoice Reminder: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function new_task_save(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'title' => 'required',
            'task_type_id' => 'required',
            'start_date' => 'required',
            'start_time' => 'required',
            'end_date' => 'required',
            'end_time' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['vali_error' => $validator->errors()->first()]);
        }
        try{
            if ($request->notify == 1) {
                $notification = $request->has('notification') ? 1 : 0;
                $sms = $request->has('sms') ? 1 : 0;
                $email = $request->has('email') ? 1 : 0;
                if($notification == 0 && $sms == 0 && $email == 0){
                    return response()->json(['vali_error' => "The send as field is required."]);
                }
            }
    
            if (!isset($notification) || !isset($sms) || !isset($email)) {
                $notification = $sms = $email = null;
            }
            $values = [
                'id'=>$request->id,
                'invoice_id'=>$request->task_invoice_id,
                'home_id' => Auth::user()->home_id,
                'customer_id' => $request->task_customer_id,
                'user_id' => $request->user_id ?? $request->user_id_timer,
                'title' => $request->title ?? $request->title_timer,
                'task_type_id' => $request->task_type_id ?? $request->task_type_timer_id,
                'start_date' => $request->start_date ?? Carbon::now()->toDateString(),
                'start_time' => $request->start_time ?? Carbon::now()->toTimeString(),
                'end_date' => $request->end_date,
                'end_time' => $request->end_time,
                'is_recurring' => $request->is_recurring ?? false,
                'notify' => $request->notify,
                'notification' => $notification,
                'sms' => $sms,
                'email' => $email,
                'notify_date' => $request->notify_date,
                'notify_time' => $request->notify_time,
                'notes' => $request->notes
            ];
            $data=InvoiceNewTask::saveReminderNewTask($values);
            if($request->id == ''){
                return response()->json(['success'=>true,'message'=>'New Task Successfully Added','data'=>$data]);
            }else{
                return response()->json(['success'=>true,'message'=>'New Task Successfully Updated','data'=>$data]);
            }
            
        }catch (\Exception $e) {
            Log::error('Error saving Invoice New Task: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function getAllInvoiceNewTaskList(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $data_newTask=InvoiceNewTask::where(['invoice_id'=> $request->id,'deleted_at'=>null])->orderBy('id', 'desc')->paginate(10);
        $data_array=[];
        foreach($data_newTask as $val){
            $ref=Invoice::find($val->invoice_id);
            $user=User::find($val->user_id);
            $type=Task_type::find($val->task_type_id);
            $data_array[]=[
                'date'=>$val->start_date,
                'ref'=>$ref->invoice_ref,
                'user'=>$user->name,
                'type'=>$type->title,
                'title'=>$val->title,
                'notes'=>$val->notes,
                'created_at'=>$val->created_at,
                'executed'=>$val->status,
                'id'=>$val->id,
                'invoice_id'=>$val->invoice_id,
                'customer_id'=>$val->customer_id,
                'user_id'=>$val->user_id,
                'task_type_id'=>$val->task_type_id,
                'start_time'=>$val->start_time,
                'end_date'=>$val->end_date,
                'end_time'=>$val->end_time,
                'is_recurring'=>$val->is_recurring,
                'notify'=>$val->notify,
                'notify_date'=>$val->notify_date,
                'notify_time'=>$val->notify_time,
                'notification'=>$val->notification,
                'email'=>$val->email,
                'sms'=>$val->sms,

            ];
        }
        return response()->json([
            'success' => true, 'data' => $data_array, 
            'pagination' => [
                    'total' => $data_newTask->total(),
                    'current_page' => $data_newTask->currentPage(),
                    'last_page' => $data_newTask->lastPage(),
                    'per_page' => $data_newTask->perPage(),
                    'next_page_url' => $data_newTask->nextPageUrl(),
                    'prev_page_url' => $data_newTask->previousPageUrl(),
                ]
        ]);
    }
    public function completeNewTaskUrl(Request $request){
        // echo "<pre>";print_r($request->all());die;
        try{
            $table=InvoiceNewTask::find($request->id);
            $table->status=1;
            $table->save();
            return response()->json(['success'=>true,'message'=>'Task Complete','data'=>array()]); 
        }catch (\Exception $e) {
            Log::error('Error Complete Invoice New Task: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }

    
}
