<?php

namespace App\Http\Controllers\frontEnd\salesFinance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\NewTaskRequest;
use Auth,Log,DB;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Project;
use App\Models\Country;
use App\Models\Tag;
use App\Models\Customer_type;
use App\Models\Constructor_additional_contact;
use App\Models\Job_title;
use App\Models\Region;
use App\Models\Product_category;
use App\Models\Construction_tax_rate;
use App\Models\Construction_account_code;
use App\Models\Construction_job_appointment_type;
use App\Models\Constructor_customer_site;
use App\Models\Job;
use App\Models\Job_type;
use App\Models\Product;
use App\Models\Currency;
use App\Models\Supplier;
use App\Home;
use App\Admin;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderProduct;
use App\Models\AttachmentType;
use App\Models\PoAttachment;
use App\Models\PrucahseOrderNewTask;
use App\Models\PurchaseOrderApproveNotification;
use App\Models\Task_type;
use App\Models\Quote;
use App\Models\Payment_type;
use App\Models\PurchaseOrderRecordPayment;
use App\Models\PurchaseOrderInvoiceReceives;
use App\Models\PurchaseOrderReject;
use App\Models\PurchaseReminder;
use App\Models\PurchaseOrderEmail;
use App\Models\CreditNoteAllocate;
use App\Models\CreditNote;
use App\Models\CreditNoteProduct;
use App\User;
use PDF;
use Carbon\Carbon;

class Purchase_orderController extends Controller
{
    public function departments(Request $request){
        $home_id = Auth::user()->home_id;
        $data['department']=Department::getAllDepartment($home_id);
        $data['home_id']=$home_id;
        $data['page']='setting';
        return view('frontEnd.salesAndFinance.jobs.department',$data);
    }

    public function save_department(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'title' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['vali_error' => $validator->errors()->first()]);
        }
        try {
            $home_id = Auth::user()->home_id;
            $requestData=$request->all();
            $requestData['home_id']=$home_id;
            $data=Department::save_Department($requestData);
            if($request->id == ''){
                return response()->json(['success'=>true,'message'=>'Department Added Successfully Done','data' => $data]);
            }else{
                return response()->json(['success'=>true,'message'=>'Department Updated Successfully Done','data' => $data]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
        
    }
    public function purchase_order(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $home_id = Auth::user()->home_id;
        $user_id=Auth::user()->id;
        $home_table=Home::find($home_id);
        $data['company_name']=Admin::find($home_table->admin_id)->company;
        $key=base64_decode($request->key) ?: base64_decode($request->duplicate);
        $data['duplicate']=base64_decode($request->duplicate);
        $data['key']=$key;
        $purchase_orders=PurchaseOrder::find($key);
        $site=array();
        $contact_name=array();
        $attachments=array();
        $reminder_data=array();
        $additional_contact=array();
        if($key){
            $site=Constructor_customer_site::where('customer_id',$purchase_orders->customer_id)->get();
            $contact_name=Customer::find($purchase_orders->customer_id);
            $reminder_data=$this->reminder_check($key);
            $additional_contact = Constructor_additional_contact::where(['home_id'=> $home_id,'userType'=>2,'customer_id'=>$key,'deleted_at'=>null])->get();
        }
        // echo "<pre>";print_r($reminder_data);die;
        $data['purchase_orders']=$purchase_orders;
        $data['attachments']=$attachments;
        $data['site']=$site;
        $data['projects']=Project::where(['status'=>1,'home_id'=>$home_id])->get();
        $data['customers']=Customer::get_customer_list_Attribute($home_id,'ACTIVE');
        $data['additional_contact'] = $additional_contact;
        $data['country']=Country::all_country_list();
        $data['tag'] = Tag::getAllTag($home_id);
        $data['currency']=Currency::where(['status'=>1,'deleted_at'=>null])->get();
        $data['suppliers']=Supplier::allGetSupplier($home_id,$user_id)->where('status',1)->get();
        $data['department']=Department::getAllDepartment($home_id);
        $data['job_title']=Job_title::where(['home_id'=>$home_id,'status'=>1])->get();
        $data['customer_types']=Customer_type::where(['home_id'=>$home_id,'status'=>1])->get();
        $data['region']=Region::where(['home_id'=>$home_id,'status'=>1,'deleted_at'=>null])->get();
        $data['contact_name']=$contact_name;
        $data['product_categories'] = Product_category::with('parent', 'children')->where('home_id',Auth::user()->home_id)->where('status',1)->where('deleted_at',NULL)->get();
        $data['reminder_data']=$reminder_data;
        $data['paymentTypeList']=Payment_type::getActivePaymentType($home_id);
        $data['page']='finance';
        // echo "<pre>";print_r($data['country']);die;
        return view('frontEnd.salesAndFinance.purchase_order.new_purchase_order',$data);
    }
    public function purchase_order_save(Request $request){
        // echo "<pre>";print_r($request->all());die;
        //   
        $home_id=Auth::user()->home_id;
        $user_id=Auth::user()->id;
        
        $validator = Validator::make($request->all(), [
            'supplier_id'=>'required',
            'name'=>'required',
            'address'=>'required',
            'user_name'=>'required',
            'user_address'=>'required',
            'purchase_date'=>'required',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['vali_error' => $validator->errors()->first()]);
        }
        if(empty($request->product_id) && !isset($request->product_id)){
            return response()->json(['vali_error' => 'Please add at least one product for purchase order.']);
        }
        try {
            if(!empty($request->purchaseattachment_id)){
                $purchaseattachment_id=$request->purchaseattachment_id;
                for($i=0;$i<count($purchaseattachment_id);$i++){
                    $poTable=PoAttachment::find($purchaseattachment_id[$i]);
                    $poTable->title=$request->purchaseattachment_title[$i];
                    $poTable->save();
                }
            }
            if ($request->hasFile('attachment')) {
                $imageName = time().'.'.$request->attachment->extension();      
                $request->attachment->move(public_path('images/purchase_order'), $imageName);
                $original_name=$request->attachment->getClientOriginalName();
                $requestData = $request->all();
                $requestData['attachment'] = $imageName;
                $requestData['file_original_name'] = $original_name;
            } else {
                $requestData = $request->all();
            }
            if($request->id == ''){
                $order_ref=$this->create_purchase_order_ref();
                $requestData['purchase_order_ref'] = $order_ref;
            }
            $requestData['home_id'] = $home_id;
            $requestData['user_id'] = $user_id;
            $requestData['delivery_status'] = 0;
            $requestData['purchase_date'] = Carbon::createFromFormat('d/m/Y', $request->purchase_date)->format('Y-m-d');
            $requestData['payment_due_date'] = Carbon::createFromFormat('d/m/Y', $request->payment_due_date)->format('Y-m-d');
            
            // echo "<pre>";print_r($requestData);die;
            $purchaseOrder=PurchaseOrder::savePurchaseOrder($requestData);
            if(!empty($request->product_id) && count($request->product_id)>0){
                $requestData['purchase_order_id'] = $purchaseOrder->id;
                $PurchaseOrderProduct=$this->savePurchaseOrderProduct($requestData);
                $responseData = $PurchaseOrderProduct->getData(true);
                if (empty($responseData['success']) || $responseData['success'] === false) {
                    return response()->json(['success' => false,'message' => 'Something went wrong.','data' => [],]);
                }
            }
            if($request->id == ''){
                return response()->json(['success' => true,'message'=>'The Purchase Order has been saved succesfully.', 'data' => $purchaseOrder]);
            }else{
                return response()->json(['success' => true,'message'=>'The Purchase Order has been updated succesfully.', 'data' => $purchaseOrder]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    private function create_purchase_order_ref(){
        $order_count=PurchaseOrder::count();
        if($order_count == 0 || $order_count <10){
           return $order_ref='PO-00'.$order_count+1;
        }else if($order_count >=10 && $order_count<100){
           return $order_ref='PO-0'.$order_count+1;
        }else{
            return $order_ref='PO-'.$order_count+1;
        }
    }
    public function getPurchaesOrderProductDetail(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $home_id=Auth::user()->home_id;
        $purchase_order_products = PurchaseOrder::with(['purchaseOrderProducts','suppliers'])
        ->where(['id' => $request->id, 'deleted_at' => null])
        ->first();
        // return $purchase_order_products;
        if (!$purchase_order_products) {
            return response()->json(['success' => false, 'message' => 'Purchase Order not found.']);
        }
        
        $tax = Product::tax_detail($home_id);
        $all_job = Job::getAllJob($home_id)->where('status', 1)->get();
        $accountCode = Construction_account_code::getActiveAccountCode($home_id);
        $payment_type=Payment_type::getActivePaymentType($home_id);
        
        if ($purchase_order_products->purchaseOrderProducts->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No products found for this purchase order.']);
        }
        
        $purchase_order_products_paginated = $purchase_order_products->purchaseOrderProducts()
            ->paginate(10);
        
        $data_array = [];
        foreach ($purchase_order_products_paginated as $val) {
            $purchase_order_products_detail = Product::product_detail($val->product_id);
        
            $data_array[] = [
                'product_details' => $purchase_order_products,
                'tax' => $tax,
                'all_job' => $all_job,
                'accountCode' => $accountCode,
                'purchase_order_products_detail' => $purchase_order_products_detail,
                'payment_type'=>$payment_type,
            ];
        }
        $paid_all_amount=$this->getAllPaymentPaid($request->id);
        return response()->json([
            'success' => true,
            'data' => $data_array,
            'paid_amount'=>$paid_all_amount,
            'pagination' => [
                'total' => $purchase_order_products_paginated->total(),
                'current_page' => $purchase_order_products_paginated->currentPage(),
                'last_page' => $purchase_order_products_paginated->lastPage(),
                'per_page' => $purchase_order_products_paginated->perPage(),
                'next_page_url' => $purchase_order_products_paginated->nextPageUrl(),
                'prev_page_url' => $purchase_order_products_paginated->previousPageUrl(),
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
    public function vat_tax_details(Request $request){
        // echo "<pre>";print_r($request->all());
        $dataVat=Construction_tax_rate::getTaxRateOnId($request->vat_id);
        return response()->json(['success'=>true,'data'=>$dataVat]);

    }
    public function savePurchaseOrderProduct($data){
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
                    'id'=>$data['purchase_product_id'][$i] ?? null,
                    'user_id'=>Auth::user()->id,
                    'userType'=>2,
                    'purchase_order_id' => $data['purchase_order_id'],
                    'product_id' => $product_ids[$i],
                    'description' => $data['description'][$i] ?? null,
                    'accountCode_id' => $data['accountCode_id'][$i] ?? null,
                    'qty' => $data['qty'][$i] ?? 0,
                    'price' => $data['price'][$i] ?? 0,
                    'vat_id' => $data['vat_id'][$i] ?? null,
                    'vat' => $data['vat_ratePercentage'][$i] ?? 0,
                ];
                PurchaseOrder::find($data['purchase_order_id'])->update(['outstanding_amount' => $outstandignAmount]);
                // echo "<pre>";print_r($productData);die;
                $PurchaseOrderProduct = PurchaseOrderProduct::savePurchaseOrderProduct($productData);
                if ($PurchaseOrderProduct) {
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
    public function purchase_order_attachment_save(Request $request){
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
                $file->move(public_path('images/purchase_order'), $imageName);

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
            $attachment=PoAttachment::savePoAttachment($requestData);
            if($request->id == ''){
                return response()->json(['success'=>true,'message'=>"Attachment Added Successfully Done",'data'=>$attachment]);
            }else{
                return response()->json(['success'=>true,'message'=>"Attachment Updated Successfully Done",'data'=>$attachment]);
            }
        }catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function getAllAttachmens(Request $request){
        // echo "<pre>";print_r($request->all());die;
        // $purchase_orders = PurchaseOrder::with(['poAttachments.attachmentType'])->where(['id'=> $request->id,'deleted_at'=>null])->orderBy('id', 'desc')->paginate(10);
        $purchase_orders = PoAttachment::with(['attachmentType'])->where(['po_id'=> $request->id,'deleted_at'=>null])->orderBy('id', 'desc')->paginate(10);

        return response()->json([
            'success' => true, 'data' => $purchase_orders, 
            'pagination' => [
                    'total' => $purchase_orders->total(),
                    'current_page' => $purchase_orders->currentPage(),
                    'last_page' => $purchase_orders->lastPage(),
                    'per_page' => $purchase_orders->perPage(),
                    'next_page_url' => $purchase_orders->nextPageUrl(),
                    'prev_page_url' => $purchase_orders->previousPageUrl(),
                ]
        ]);

    }
    public function delete_po_attachment(Request $request){
        $id=$request->id;
        try{
            PoAttachment::find($id)->update(['deleted_at' => now()]);
            return response()->json(['success'=>true,'message'=>'Deleted Successfully done']);
        }catch (\Exception $e) {
            Log::error('Error saving Tag: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function purchase_productsDelete(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $id=$request->id;
        try{
            PurchaseOrderProduct::find($id)->update(['deleted_at' => now()]);
            return response()->json(['success'=>true,'message'=>'Deleted Successfully done']);
        }catch (\Exception $e) {
            Log::error('Error saving Tag: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function purchase_order_new_task_save(NewTaskRequest $request){
        // echo "<pre>";print_r($request->all());die;
        $validatedData = $request->validated();
        try{
            if ($request->notify == 1) {
                $notification = $request->has('notification') ? 1 : 0;
                $sms = $request->has('sms') ? 1 : 0;
                $email = $request->has('email') ? 1 : 0;
            }
    
            if (!isset($notification) || !isset($sms) || !isset($email)) {
                $notification = $sms = $email = null;
            }
            $values = [
                'id'=>$request->id,
                'po_id'=>$request->task_po_id,
                'home_id' => Auth::user()->home_id,
                'supplier_id' => $request->task_supplier_id,
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
                'notes' => $request->notes ?? $request->notes_timer
            ];
            $data=PrucahseOrderNewTask::savePurchaseOrderNewTask($values);
            if($request->id == ''){
                return response()->json(['success'=>true,'message'=>'New Task Successfully Added','data'=>$data]);
            }else{
                return response()->json(['success'=>true,'message'=>'New Task Successfully Updated','data'=>$data]);
            }
            
        }catch (\Exception $e) {
            Log::error('Error saving Tag: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function getAllNewTaskList(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $data_newTask=PrucahseOrderNewTask::where(['po_id'=> $request->id,'deleted_at'=>null])->orderBy('id', 'desc')->paginate(10);
        $data_array=[];
        foreach($data_newTask as $val){
            $ref=PurchaseOrder::find($val->po_id);
            $user=User::find($val->user_id);
            $type=Task_type::find($val->task_type_id);
            $data_array[]=[
                'date'=>$val->start_date,
                'ref'=>$ref->purchase_order_ref,
                'user'=>$user->name,
                'type'=>$type->title,
                'title'=>$val->title,
                'notes'=>$val->notes,
                'created_at'=>$val->created_at,
                'executed'=>$val->start_date,
                'id'=>$val->id,
                'po_id'=>$val->po_id,
                'supplier_id'=>$val->supplier_id,
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
    public function draft_purchase_order(Request $request){
        $home_id=Auth::user()->home_id;
        $lastSegment = $request->list_mode;
        $segment_check=$this->check_segment_purchaseOrder($lastSegment);
        // echo "<pre>"; print_r($segment_check);die;
        $data['list']=PurchaseOrder::with('suppliers','purchaseOrderProducts')->where(['user_id'=>Auth::user()->id,'deleted_at'=>null,'status'=>$segment_check['status']])->get();
        $data['status']=$segment_check;
        $data['draftCount']=PurchaseOrder::where(['user_id'=>Auth::user()->id,'deleted_at'=>null,'status'=>1])->count();
        $data['awaitingApprovalCount']=PurchaseOrder::where(['user_id'=>Auth::user()->id,'deleted_at'=>null,'status'=>2])->count();
        $data['approvedCount']=PurchaseOrder::where(['user_id'=>Auth::user()->id,'deleted_at'=>null])->whereIn('status',[3,9])->count();
        $data['rejectedCount']=PurchaseOrder::where(['user_id'=>Auth::user()->id,'deleted_at'=>null,'status'=>8])->count();
        $data['actionedCount']=PurchaseOrder::where(['user_id'=>Auth::user()->id,'deleted_at'=>null,'status'=>4])->count();
        $data['paidCount']=PurchaseOrder::where(['user_id'=>Auth::user()->id,'deleted_at'=>null,'status'=>5])->count();
        $data['customer_data'] = Customer::get_customer_list_Attribute($home_id, 'ACTIVE');
        $data['users'] = User::where('home_id', $home_id)->select('id', 'name','email','phone_no')->where('is_deleted', 0)->get();
        $data['paymentTypeList']=Payment_type::getActivePaymentType($home_id);
        $data['page']='finance';
        // echo "<pre>";print_r($data['status']);die;
        return view('frontEnd.salesAndFinance.purchase_order.purchase_order_list',$data);
    }
    private function check_segment_purchaseOrder($lastSegment=null){
        if($lastSegment === 'AwaitingApprivalPurchaseOrders'){
            return ['status'=>2,'list_status'=>'Awaiting Approval','page_heading'=>'Awaiting Authorisation Purchase Orders'];
        }else if($lastSegment === 'Approved'){
            return ['status'=>3,'list_status'=>'Approved','page_heading'=>'Authorised Purchase Orders'];
        }else if($lastSegment === 'Rejected'){
            return ['status'=>8,'list_status'=>'Rejected','page_heading'=>'Rejected Purchase Orders'];
        }else if($lastSegment === 'Actioned'){
            return ['status'=>4,'list_status'=>'Actioned','page_heading'=>'Actioned Purchase Orders'];
        }else if($lastSegment === 'Paid'){
            return ['status'=>5,'list_status'=>'Paid','page_heading'=>'Paid Purchase Orders'];
        }else{
            return ['status'=>1,'list_status'=>'Draft','page_heading'=>'Draft Purchase Orders'];
        }
    }
    public function searchPurchaseOrders(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $po_ref=$request->po_ref;
        $department=$request->department;
        $tag=$request->tag;
        $supplier=$request->supplier;
        $edd_startDate=$request->edd_startDate;
        $edd_endDate=$request->edd_endDate;
        $po_startDate=$request->po_startDate;
        $po_endDate=$request->po_endDate;
        $customer=$request->customer;
        $created_by=$request->created_by;
        $po_posted=$request->po_posted;
        $project=$request->project;
        $keywords=$request->keywords;
        $delivery_status=$request->delivery_status;
        $key=$request->key;
        $value=$request->value;
        $status=$request->status;
        $list_status=$request->list_status;
        $selectedDeptId=$request->selectedDeptId;
        $selectedTagtId=$request->selectedTagtId;
        $selectedsupplierId=$request->selectedsupplierId;
        $selectedCustomerId=$request->selectedCustomerId;
        $selectedcreatedById=$request->selectedcreatedById;
        $selectedProjectId=$request->selectedProjectId;
        $home_id=Auth::user()->home_id;
        $purchaseSearchstatus=$request->purchaseSearchstatus;
        $fields = $request->except('_token');
        $hasValue = collect($fields)->some(fn($value) => !empty($value));
        if(!$hasValue){
            return response()->json(['success'=>false,'message'=>'Please fill in at least one field before searching.','data'=>array()]);
        }
        if($status == '' && $purchaseSearchstatus==''){
            $query = PurchaseOrder::with('suppliers','purchaseOrderProducts')->where(['deleted_at'=>null]);
        }else if(!empty($request->purchaseSearchstatus)){
            $query = PurchaseOrder::with('suppliers','purchaseOrderProducts')->whereIn('status',$request->purchaseSearchstatus)->whereNull('deleted_at');
        }else{
            $query = PurchaseOrder::with('suppliers','purchaseOrderProducts')->where(['deleted_at'=>null,'status'=>$status]);
        }
        // echo "<pre>";print_r($query->get());die;
        if ($request->filled('po_ref')) {
            $query->where('purchase_order_ref', $po_ref);
        }
        
        if ($request->filled('department')) {
            $query->where('department_id', $selectedDeptId);
        }

        if ($request->filled('tag')) {
            $query->where('tag_id', $selectedTagtId);
        }
        if ($request->filled('supplier')) {
            $query->where('supplier_id', $selectedsupplierId);
        }
        if ($request->filled('edd_startDate') && $request->filled('edd_endDate')) {
            $query->whereBetween('expected_deleveryDate', [$edd_startDate, $edd_endDate]);
        }
        if ($request->filled('po_startDate') && $request->filled('po_endDate')) {
            $query->whereBetween('purchase_date', [$po_startDate, $po_endDate]);
        }
        if ($request->filled('customer')) {
            $query->where('customer_id', $selectedCustomerId);
        }
        if ($request->filled('created_by')) {
            $query->where('user_id', $selectedcreatedById);
        }
        if ($request->filled('project')) {
            $query->where('project_id', $selectedProjectId);
        }
        if ($request->filled('delivery_status')) {
            $query->where('delivery_status', $delivery_status);
        }
        if ($request->filled('keywords')) {
            $query->where(function ($q) use ($keywords) {
                $q->where('purchase_order_ref', 'LIKE', '%' . $keywords . '%')
                ->orWhere('name', 'LIKE', '%' . $keywords . '%')
                ->orWhere('qoute_ref', 'LIKE', '%' . $keywords . '%')
                ->orWhere('job_ref', 'LIKE', '%' . $keywords . '%')
                ->orWhere('invoice_ref', 'LIKE', '%' . $keywords . '%')
                ->orWhere('reference', 'LIKE', '%' . $keywords . '%');
            });
        }
        // echo $query->toSql();
        // print_r($query->getBindings());
        // die;
        $search_data = $query->where('user_id',Auth::user()->id)->get();
        // echo "<pre>";print_r($search_data);die;
        $array_data='';
        $all_subTotalAmount=0;
        $all_vatTotalAmount=0;
        $all_TotalAmount=0;
        $outstandingAmountTotal=0;
        foreach($search_data as $key=>$val){
            $customer=Customer::find($val->customer_id);
            $sub_total_amount=0;
            $total_amount=0;
            $vat_amount=0;
            $purchaseProductId=0;
            foreach($val->purchaseOrderProducts as $product){
                $purchaseProductId=$product->id;
                $qty=$product->qty*$product->price;
                $sub_total_amount=$sub_total_amount+$qty;
                $vat=$qty*$product->vat/100;
                $vat_amount=$vat_amount+$vat;
                $total_amount=$total_amount+$vat+$qty;
            }
            $all_subTotalAmount=$all_subTotalAmount+$sub_total_amount;
            $all_vatTotalAmount=$all_vatTotalAmount+$vat_amount;
            $all_TotalAmount=$all_TotalAmount+$total_amount;
            $outstandingAmountTotal=$outstandingAmountTotal+$val->outstanding_amount;

            if($list_status == ''){
                $status = $val->status;
                switch ($status) {
                case 1:
                    $list_status= "Draft";
                    break;
                case 2:
                    $list_status= "Awaiting Approval";
                    break;
                case 3:
                    $list_status= "Approved";
                    break;
                case 4:
                    $list_status= "Actioned";
                    break;
                case 5:
                    $list_status= "Paid";
                    break;
                case 6:
                    $list_status= "Cancelled";
                    break;
                case 7:
                    $list_status= "Invoice Received";
                    break;
                case 8:
                    $list_status= "Rejected";
                    break;

                default:
                $list_status= "";
                }
            }
            $array_data .= '<tr>
                        <td><div class="text-center"><input type="checkbox" class="delete_checkbox" value="' . $val->id . '"></div></td>
                        <td>' . ++$key . '</td>
                        <td>' . $val->purchase_order_ref . '</td>
                        <td>' . date('d/m/Y',strtotime($val->purchase_date)) . '</td>
                        <td>' . date('d/m/Y',strtotime($val->payment_due_date)) . '</td>
                        <td>' . $val->suppliers->name . '</td>
                        <td>' . ($customer->name ?? '') . '</td>
                        <td>' . $val->city . '</td>
                        <td>£' . $sub_total_amount . '</td>
                        <td>£' . $vat_amount . '</td>
                        <td>£' . $total_amount . '</td>
                        <td>£' . $val->outstanding_amount . '</td>
                        <td>' . $list_status . '</td>
                        <td>';
                            if($status == 1){
                                $array_data .= '-';
                            }else{
                                if($val->delivery_status == 1){
                                    $array_data.='<span class="grencheck"><i class="fa-solid fa-check"></i></span>';
                                }else if($val->delivery_status == 2){
                                    $array_data.='<a href="javascript:void(0)" class="tutor-student-tooltip-col" style="color:red"><span class="" style="color:#FFCC66"><i class="fa-solid fa-check"></i></span><span class="tutor-student-tooltiptext3">Part Delivered</span></a>';
                                }else{
                                    $array_data.='<a href="javascript:void(0)" class="tutor-student-tooltip-col" style="color:red">X<span class="tutor-student-tooltiptext3">Not Delivered</span></a>';
                                }
                            }
                        $array_data .= '</td><td>
                                    <div class="d-flex justify-content-end">
                                        <div class="nav-item dropdown">
                                            <a href="#!" class="nav-link dropdown-toggle profileDrop" data-bs-toggle="dropdown" aria-expanded="false">
                                                Action
                                            </a>
                                            <div class="dropdown-menu fade-up m-0" style="z-index:9999">';
                                                if( $status != 1 && $status != 2){
                                                    $array_data .= '<a href="javascript:void(0)" onclick="openRecordDeliveryModal(' . $val->id . ',\'' . $val->purchase_order_ref . '\')" class="dropdown-item">Record Delivery</a>
                                                    <hr class="dropdown-divider">';
                                                }
                                                $array_data .= '<a href="' . url('purchase_order_edit?key=') . base64_encode($val->id) . '" class="dropdown-item">Edit</a>
                                                <hr class="dropdown-divider">
                                                <a href="'.url('preview?key=').''.base64_encode($val->id).'" target="_blank" class="dropdown-item">Preview</a>
                                                <hr class="dropdown-divider">';
                                                if( $status != 1 && $status != 2){
                                                    $array_data .= '<a href="'.url('preview?key=').''.base64_encode($val->id).'" target="_blank" class="dropdown-item">Print</a>
                                                    <hr class="dropdown-divider">
                                                    <a href="javascript:void(0)" onclick="openEmailModal('.$val->id.',\'' . $val->purchase_order_ref . '\',\'' . $val->suppliers->email . '\',\'' . $val->suppliers->name . '\')" class="dropdown-item">Email</a>
                                                    <hr class="dropdown-divider">';
                                                }
                                                $array_data .= '<a href="' . url('purchase_order?duplicate=') . base64_encode($val->id) . '" target="_blank" class="dropdown-item">Duplicate</a>
                                                <hr class="dropdown-divider">';
                                                if($status != 8 && $status != 1 ){
                                                    $array_data .= '<a href="javascript:void(0)" onclick="openRejectModal(' . $val->id . ',\'' . $val->purchase_order_ref . '\')" class="dropdown-item">Reject</a>
                                                    <hr class="dropdown-divider">';
                                                }
                                                if($status == 1 || $status == 2){
                                                    $array_data .= '<a href="javascript:void(0)" onclick="openApproveModal('.$val->id.',\'' . $val->purchase_order_ref . '\')" class="dropdown-item">Approve</a>
                                                    <hr class="dropdown-divider">';
                                                }
                                                if($status != 5 && $status != 1 && $status != 2){
                                                    $array_data .= '<a href="javascript:void(0)" onclick="openRecordPaymentModal(' . $val->id . ',\'' . $val->purchase_order_ref . '\',\'' . $val->suppliers->name . '\',' . $total_amount . ',\'' . date('d/m/Y', strtotime($val->purchase_date)) . '\',' . $purchaseProductId . ',' . $val->outstanding_amount . ')" class="dropdown-item">Record Payment</a>
                                                    <hr class="dropdown-divider">
                                                    <a href="javascript:void(0)" onclick="openInvoiceRecieveModal(' . $val->id . ',\'' . $val->purchase_order_ref . '\',\'' . $val->suppliers->name . '\',' . $val->suppliers->id . ',' . $sub_total_amount . ',\'' . date('d/m/Y', strtotime($val->purchase_date)) . '\',' . $vat . ',' . $val->outstanding_amount . ')" class="dropdown-item">Invoice Received</a>
                                                    <hr class="dropdown-divider">';
                                                }
                                                if($status == 8 || $status == 4 || $status == 5 || $status == 3){
                                                    if($val->delivery_status !=1){
                                                        $array_data .= '<a href="#!" class="dropdown-item">Cancel Purchase Order</a>
                                                        <hr class="dropdown-divider">';
                                                    }
                                                }
                                                $array_data .= '<a href="#!" class="dropdown-item">CRM / History</a>
                                                <hr class="dropdown-divider">
                                                <a href="#!" class="dropdown-item">Start Timer</a>
                                                
                                            </div>
                                        </div>
                                    </div>
                    </tr>';
                    $list_status='';
        }

        return response()->json(['data' => $array_data,'all_subTotalAmount'=>$all_subTotalAmount,'all_vatTotalAmount'=>$all_vatTotalAmount,'all_TotalAmount'=>$all_TotalAmount,'outstandingAmountTotal'=>$outstandingAmountTotal]);
    }
    public function searchDepartment(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $query = $request->input('search_deptquery');  
        $home_id = Auth::user()->home_id;

        $DeptSearchData = Department::where('title', 'LIKE', "%$query%")
            ->where('home_id', $home_id)
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->take(10)
            ->get();

        return response()->json(['data' => $DeptSearchData]);
    }
    public function searchTag(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $query = $request->input('search_tagquery');  
        $home_id = Auth::user()->home_id;

        $TagSearchData = Tag::where('title', 'LIKE', "%$query%")
            ->where('home_id', $home_id)
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->take(10)
            ->get();

        return response()->json(['data' => $TagSearchData]);
    }
    public function searchSupplier(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $query = $request->input('search_supplierquery');  
        $home_id = Auth::user()->home_id;

        $SupplierSearchData = Supplier::where('name', 'LIKE', "%$query%")
            ->where('home_id', $home_id)
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->take(10)
            ->get();

        return response()->json(['data' => $SupplierSearchData]);
    }
    public function searchCreatedBy(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $query = $request->input('search_createdbyquery');  
        $home_id = Auth::user()->home_id;

        $CreatedBySearchData = User::where('name', 'LIKE', "%$query%")
            ->where('home_id', $home_id)
            ->where('status', 1)
            ->where('is_deleted',0)
            ->take(10)
            ->get();

        return response()->json(['data' => $CreatedBySearchData]);
    }
    public function searchProject(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $query = $request->input('search_projectquery');  
        $home_id = Auth::user()->home_id;

        $ProjectSearchData = Project::where('project_name', 'LIKE', "%$query%")
            ->where('home_id', $home_id)
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->take(10)
            ->get();

        return response()->json(['data' => $ProjectSearchData]);
    }
    public function searchPurchase_qoute_ref(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $query = $request->input('search_projectquery');  
        $home_id = Auth::user()->home_id;

        $QuoteSearchData = Quote::where('quote_ref', 'LIKE', "%$query%")
            ->where('home_id', $home_id)
            ->where('customer_id',$request->purchase_customer_id)
            // ->where('status', 1)
            // ->whereNull('deleted_at')
            ->take(10)
            ->get();

        return response()->json(['data' => $QuoteSearchData]);
    }
    public function searchPurchase_job_ref(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $query = $request->input('search_projectquery');  
        $home_id = Auth::user()->home_id;

        $QuoteSearchData = Job::where('job_ref', 'LIKE', "%$query%")
            ->where('home_id', $home_id)
            ->where('customer_id',$request->purchase_customer_id)
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->take(10)
            ->get();

        return response()->json(['data' => $QuoteSearchData]);
    }
    public function purchase_order_approve(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $po_id=$request->po_id;
        if ($request->notify_radio == 1) {
            $validator = Validator::make($request->all(), [
                'notify_user_id' => 'required'
            ],
            [
                'notify_user_id.required' => 'The Notify Who field is required.',
            ]);
        
            if ($validator->fails()) {
                return response()->json(['vali_error' => $validator->errors()->first()]);
            }
            $notification = $request->has('notification') ? 1 : 0;
            $sms = $request->has('sms') ? 1 : 0;
            $email = $request->has('email') ? 1 : 0;
        }
        if (!isset($notification) || !isset($sms) || !isset($email)) {
            $notification = $sms = $email = null;
        }
        try{
            if ($request->notify_radio == 1) {
                PurchaseOrderApproveNotification::purchaseOrderApproveSave($request->all());
            }
            PurchaseOrder::find($po_id)->update(['status' => 3]);
            return response()->json(['success'=>true,'message'=>'Purchase Order Apporoved']);
        }catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function purchase_order_record_delivered(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $po_id=$request->po_id;
        $purchase_product_id=$request->purchase_product_id;
        if(count($purchase_product_id)>0){
            for($i=0;$i<count($purchase_product_id);$i++){
                $data=[
                    'id'=>$purchase_product_id[$i],
                    'deliverd_qty'=>$request->already_deliver[$i],
                    'receive_more'=>$request->receive_more[$i],
                ];
                // echo "<pre>";print_r($data);die;
                try{
                    PurchaseOrderProduct::savePurchaseOrderProduct($data);
                    $checkAlreadyDelivered=$this->checkAlreadyDelivered($purchase_product_id[$i],$request->qty[$i],$po_id);
                }catch (\Exception $e) {
                    Log::error('Error: ' . $e->getMessage());
                    return response()->json(['error' => $e->getMessage()], 500);
                }
                
            }
            return response()->json(['success'=>true,'message'=>'The Purchase Order Delivered has been saved successfully.']);
        }
    }
    private function checkAlreadyDelivered($id,$qty,$po_id){
        // return $qty;
        $check=PurchaseOrderProduct::find($id);
        if($check->deliverd_qty == $qty){
            PurchaseOrder::find($po_id)->update(['delivery_status' => 1]);
        }else{
            PurchaseOrder::find($po_id)->update(['delivery_status' => 2]);
        }
    }
    // public function record_payment_details(Request $request){
    //     // echo "<pre>";print_r($request->all());die;
    //     $data=PurchaseOrderProduct::with('purchaseOrders')->where('purchase_order_id',$request->id)->get();
    //     return $data;
    // }
    public function savePurchaseOrderRecordPayment(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'po_id' => 'required',
            'record_amount_paid' => 'required',
            'record_payment_date' => 'required',
            'record_payment_type' => 'required',
        ],
        [
            'po_id.required' => 'The Purchase Order Id not found.',
            'record_amount_paid.required' => 'Amount Paid field required.',
            'record_payment_date.required' => 'Payment Date field required.',
            'record_payment_type.required' => 'Payment Type field required.',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['vali_error' => $validator->errors()->first()]);
        }
        $data=$request->all();
        $data['home_id']=Auth::user()->home_id;
        $data['loginUserId']=Auth::user()->id;
        $data['loginUserName']=Auth::user()->name;
        try{
            $orderRecord=PurchaseOrderRecordPayment::savePurchaseOrderRecordPayment($data);
            $calculation_amount=$request->total_amount-$request->record_amount_paid;
            $tablePurchaseOrder=PurchaseOrder::find($request->po_id);
            $tablePurchaseOrder->outstanding_amount=$calculation_amount;
            if($calculation_amount == 0){
                $tablePurchaseOrder->status=5;
            }
            $tablePurchaseOrder->save();
            // PurchaseOrder::find($request->po_id)->update(['outstanding_amount' => $request->outstanding_amount]);
            // $itt=PurchaseOrderProduct::find($recordPayment_ppurchaseProduct)->update(['outstanding_amount' => $request->record_amount_paid]);
            return response()->json(['success'=>true,'message'=>'The Record Payment has been saved successfully.','data'=>$orderRecord]);
        }catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function purchaseOrderInviceRecieve(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'po_id' => 'required',
            'supplier_id' => 'required',
            'inv_ref' => 'required',
            'net_amount' => 'required',
            'vat_id' => 'required',
            'gross_amount' => 'required',
            'invoice_date' => 'required',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['vali_error' => $validator->errors()->first()]);
        }
        if ($request->hasFile('file')) {
            $imageName = time().'.'.$request->file->extension();
            $original_name=$request->file->getClientOriginalName();      
            $request->file->move(public_path('images/purchase_invoice'), $imageName);
            $requestData = $request->all();
            $requestData['file'] = $imageName;
            $requestData['original_file_name'] = $original_name;
        } else {
            $requestData = $request->all();
        }
        $requestData['loginUserId']=Auth::user()->id;
        $requestData['home_id']=Auth::user()->home_id;
        $requestData['oustanding_amount']=$request->gross_amount;
        // echo "<pre>";print_r($requestData);die;
        try {
            $invoice=PurchaseOrderInvoiceReceives::purchaseOrderInvoiceReceives_save($requestData);
            if($request->id == ''){
                return response()->json(['success' => true,'message'=>'Invoice has been saved', 'expense' => $invoice]);
            }else{
                return response()->json(['success' => true,'message'=>'Invoice has been updated', 'expense' => $invoice]);
            }
            
        } catch (\Exception $e) {
            Log::error('Error saving Tag: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function purchaseOrderreject(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'po_id' => 'required',
            'message' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['vali_error' => $validator->errors()->first()]);
        }
        if ($request->notify_radio == 1) {
            $validator = Validator::make($request->all(), [
                'notification' => 'required',
                'sms' => 'required',
                'email' => 'required'
            ]);
        
            if ($validator->fails()) {
                return response()->json(['vali_error' => $validator->errors()->first()]);
            }
            $notification = $request->has('notification') ? 1 : 0;
            $sms = $request->has('sms') ? 1 : 0;
            $email = $request->has('email') ? 1 : 0;
        }
        if (!isset($notification) || !isset($sms) || !isset($email)) {
            $notification = $sms = $email = null;
        }
        $data=$request->all();
        $data['notification']=$notification;
        $data['sms']=$sms;
        $data['email']=$email;
        $data['home_id']=Auth::user()->home_id;
        $data['loginUserId']=Auth::user()->id;
        // echo "<pre>";print_r($data);die;
        try{
            $reject=PurchaseOrderReject::savePurchaseOrderReject($data);
            PurchaseOrder::find($request->po_id)->update(['status' => 8]);
            return response()->json(['success'=>true,'message'=>'The Purchase Order has been rejected successfully.','data'=>$reject]);
        }catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());
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
        $data['user_id']=implode(',',$request->user_id);
        // echo "<pre>";print_r($data);die;
        try{
            $reminder=PurchaseReminder::saveReminder($data);
            return response()->json(['success'=>true,'message'=>'The Reminder has been saved successfully.','data'=>$reminder]);
        }catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    static function reminder_check($po_id){
        $home_id=Auth::user()->home_id;
        PurchaseReminder::allReminderData($home_id)
        ->whereNull('po_id')
        ->forceDelete();
        $current_date=Date('Y-m-d');
        PurchaseReminder::allReminderData($home_id)
        ->where('po_id', $po_id)
        ->whereDate('reminder_date', '<', $current_date)
        ->update(['status' => 1]);
        return PurchaseReminder::allReminderData($home_id)->where('po_id',$po_id)->get();
    }
    public function purchaseOrderEmailSave(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'selectedToEmail' => 'required',
            'subject' => 'required',
            'po_id' => 'required',
        ],
        [
            'selectedToEmail.required' => 'To field is required.',
            'subject.required' => 'Subject field is required.',
            'po_id.required' => 'Purchase Order Id does not match.',
        ]);
        if ($validator->fails()) {
            return response()->json(['vali_error' => $validator->errors()->first()]);
        }
        try{
            $po_id=explode(',',$request->po_id);
            // echo"<pre>"; print_r($po_id);die;
            $data=$request->all();
            $data['home_id']=Auth::user()->home_id;
            $data['loginUserId']=Auth::user()->home_id;
            $data['to'] = json_encode(explode(',', $request->selectedToEmail));
            $data['cc'] = $request->selectedToEmail1 ? json_encode(explode(',', $request->selectedToEmail1)) : json_encode([]);
            if(count($po_id)>1){
                for($i=0;$i<count($po_id);$i++){
                    $data['po_id']=$po_id[$i];
                    $email=PurchaseOrderEmail::saveEmail($data);
                }
            }else{
                $email=PurchaseOrderEmail::saveEmail($data);
            }
            // echo "<pre>";print_r($data);die;
            
            return response()->json(['success'=>true,'message'=>'The Email has been saved successfully.','data'=>$email]);
        }catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function preview(Request $request){
        // echo "<pre>";print_r(Auth::user());die;
        try{
            // $pdf = PDF::loadView('frontEnd.salesAndFinance.purchase_order.purchaseOrderPDF')->setPaper('a4', 'landscape');
            $po_id=base64_decode($request->key);
            $po_details=PurchaseOrder::with('suppliers','purchaseOrderProducts')->where(['id' => $po_id, 'deleted_at' => null])
            ->first();
            // $site_detail=Customer::find($po_details->customer_id);
			// echo "<pre>";print_r($site_detail);die;
            // echo "<pre>";print_r($po_details);die;
            
            $data=[
                'email'=>Auth::user()->email,
                'phone_no'=>Auth::user()->phone_no,
                'job_title'=>Auth::user()->job_title,
                'current_location'=>Auth::user()->current_location,
                'company'=>Admin::find(Auth::user()->company_id)->company ?? "",
                'po_details'=>$po_details,
            ];
            // echo "<pre>";print_r($data);die;
            $pdf = PDF::loadView('frontEnd.salesAndFinance.purchase_order.purchaseOrderPDF',$data);
            return $pdf->stream('frontEnd.salesAndFinance.purchase_order.purchaseOrderPDF');
            // return $pdf->download('purchaseOrderPDF.pdf');
        }catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function purchase_orders_search(Request $request){
        $home_id=Auth::user()->home_id;
        $data['list']=array();
        $data['draftCount']=PurchaseOrder::where(['user_id'=>Auth::user()->id,'deleted_at'=>null,'status'=>1])->count();
        $data['awaitingApprovalCount']=PurchaseOrder::where(['user_id'=>Auth::user()->id,'deleted_at'=>null,'status'=>2])->count();
        $data['approvedCount']=PurchaseOrder::where(['user_id'=>Auth::user()->id,'deleted_at'=>null])->whereIn('status',[3,9])->count();
        $data['rejectedCount']=PurchaseOrder::where(['user_id'=>Auth::user()->id,'deleted_at'=>null,'status'=>8])->count();
        $data['actionedCount']=PurchaseOrder::where(['user_id'=>Auth::user()->id,'deleted_at'=>null,'status'=>4])->count();
        $data['paidCount']=PurchaseOrder::where(['user_id'=>Auth::user()->id,'deleted_at'=>null,'status'=>5])->count();
        $data['customer_data'] = Customer::get_customer_list_Attribute($home_id, 'ACTIVE');
        $data['users'] = User::where('home_id', $home_id)->select('id', 'name','email','phone_no')->where('is_deleted', 0)->get();
        $data['paymentTypeList']=Payment_type::getActivePaymentType($home_id);
        // echo "<pre>";print_r($data['list']);die;
        return view('frontEnd.salesAndFinance.purchase_order.search_purchaseOrder',$data);
    }
    public function purchase_order_statements(Request $request){
        $home_id=Auth::user()->home_id;
        $data['list']=array();
        $data['draftCount']=PurchaseOrder::where(['user_id'=>Auth::user()->id,'deleted_at'=>null,'status'=>1])->count();
        $data['awaitingApprovalCount']=PurchaseOrder::where(['user_id'=>Auth::user()->id,'deleted_at'=>null,'status'=>2])->count();
        $data['approvedCount']=PurchaseOrder::where(['user_id'=>Auth::user()->id,'deleted_at'=>null])->whereIn('status',[3,9])->count();
        $data['rejectedCount']=PurchaseOrder::where(['user_id'=>Auth::user()->id,'deleted_at'=>null,'status'=>8])->count();
        $data['actionedCount']=PurchaseOrder::where(['user_id'=>Auth::user()->id,'deleted_at'=>null,'status'=>4])->count();
        $data['paidCount']=PurchaseOrder::where(['user_id'=>Auth::user()->id,'deleted_at'=>null,'status'=>5])->count();
        // echo "<pre>";print_r($data['list']);die;
        return view('frontEnd.salesAndFinance.purchase_order.purchaseOrderStatement',$data);
    }
    public function searchPurchaseOrdersStatements(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $po_startDate = date('Y-m-d', strtotime(str_replace('/', '-', $request->po_startDate)));
        $po_endDate=date('Y-m-d', strtotime(str_replace('/', '-', $request->po_endDate)));
        
        $purchaseOrderquery=PurchaseOrder::with('suppliers','purchaseOrderProducts')->where('supplier_id',$request->selectedsupplierId)->whereNull('deleted_at');
        if ($request->filled('po_startDate') && $request->filled('po_endDate')) {
            $purchaseOrderquery->whereBetween('purchase_date', [$po_startDate, $po_endDate]);
        }
        $purchase_orders=$purchaseOrderquery->get();
        // return $purchase_orders;
        $allRecords = collect();
        foreach($purchase_orders as $val){
            $orderGross_amount=0;
            foreach($val->purchaseOrderProducts as $orderProduct){
                $order_calculateAmount=$orderProduct->qty*$orderProduct->price;
                $order_calculatepercentage=$order_calculateAmount*$orderProduct->vat/100;
                $orderGross_amount=$orderGross_amount+$order_calculatepercentage+$order_calculateAmount;
                $allRecords->push([
                    'date' => $val->purchase_date,
                    'ref'=>$val->purchase_order_ref,
                    'address'=>$val->address,
                    'supplier_ref'=>'',
                    'net_amount'=>$orderProduct->qty*$orderProduct->price,
                    'vat'=>$order_calculatepercentage,
                    'paid_amount'=>'',
                    'total_amount'=>$orderGross_amount,
                    'type'=>'purchase',
                ]);
            }
            $allocateData=CreditNoteAllocate::where('po_id',$val->id)->whereNull('deleted_at')->get();
            foreach($allocateData as $allocate){
                $ref=CreditNote::find($allocate->credit_id);
                $allRecords->push([
                    'date' => $allocate->date,
                    'ref'=>$ref->credit_ref,
                    'address'=>$ref->address,
                    'supplier_ref'=>'',
                    'net_amount'=>'',
                    'vat'=>'',
                    'paid_amount'=>$allocate->amount_paid,
                    'total_amount'=>'',
                    'type'=>'allocate',
                ]);
            }
            $recordPaymentData=PurchaseOrderRecordPayment::where('po_id',$val->id)->whereNull('deleted_at')->get();
            foreach($recordPaymentData as $record){
                $allRecords->push([
                    'date' => $record->record_payment_date,
                    'ref'=>$val->purchase_order_ref,
                    'address'=>$val->address,
                    'supplier_ref'=>'',
                    'net_amount'=>'',
                    'vat'=>'',
                    'paid_amount'=>$record->record_amount_paid,
                    'total_amount'=>'',
                    'type'=>'record_payment',
                ]);
            }
            
        }
        $sortedData = $allRecords->sortBy('date');
        $all_data= $sortedData->values(); 
        // return $all_data;
        $data_array='';
        $gross_amount=0;
        $grandNetAmount=0;
        $grandVatAmount=0;
        $grandTotalAmount=0;
        $grandPaidAmount=0;
        foreach($all_data as $dataVal){
            $netAmount = isset($dataVal['net_amount']) && $dataVal['net_amount'] !== "" ? '£' . $dataVal['net_amount'] : '';
            $vatAmount = isset($dataVal['vat']) && $dataVal['vat'] !== "" ? '£' . $dataVal['vat'] : '';
            $totalAmount = isset($dataVal['total_amount']) && $dataVal['total_amount'] !== "" ? '£' . $dataVal['total_amount'] : '';
            $paidAmount = isset($dataVal['paid_amount']) && $dataVal['paid_amount'] !== "" ? '£' . $dataVal['paid_amount'] : '';
            $minPaidAmount=$gross_amount-(float)$dataVal['paid_amount'];
            $addAllAmount=(float)$dataVal['total_amount']+$minPaidAmount;
            $gross_amount=$addAllAmount;

            $grandNetAmount=$grandNetAmount+(float)$dataVal['net_amount'];
            $grandVatAmount=$grandVatAmount+(float)$dataVal['vat'];
            $grandTotalAmount=$grandTotalAmount+(float)$dataVal['total_amount'];
            $grandPaidAmount=$grandPaidAmount+(float)$dataVal['paid_amount'];
            $data_array .= '<tr>
                    <td>' . date('d/m/Y', strtotime($dataVal['date'])) . '</td>
                    <td>' . $dataVal['ref'] . '</td>
                    <td></td>
                    <td>' . (strip_tags($dataVal['address']) ?? "") . '</td>
                    <td>' . ($netAmount ?? "") . '</td>
                    <td>' . ($vatAmount ?? "") . '</td>
                    <td>' . ($totalAmount ?? "") . '</td>
                    <td>' . ($paidAmount ?? "") . '</td>
                    <td>£'.$gross_amount.'</td>
                </tr>';
        }
        // return $sortedData->values(); 
        return response()->json(['data' => $data_array,'grandNetAmount'=>$grandNetAmount,'all_vatTotalAmount'=>$grandVatAmount,'all_TotalAmount'=>$grandTotalAmount,'outstandingAmountTotal'=>$grandPaidAmount,'grandGrossAmount'=>$gross_amount]);
        
    }
    public function searchPurchaseOrdersStatementsOutstanding(Request $request){
        // echo "<pre>";print_r($request->all());die;
        // getAllPaymentPaid($po_id)
        $po_startDate = date('Y-m-d', strtotime(str_replace('/', '-', $request->po_startDate)));
        $po_endDate=date('Y-m-d', strtotime(str_replace('/', '-', $request->po_endDate)));
        
        $purchaseOrderquery=PurchaseOrder::with('suppliers','purchaseOrderProducts')->where('supplier_id',$request->selectedsupplierId)->whereNull('deleted_at')->whereNot('outstanding_amount',0);
        if ($request->filled('po_startDate') && $request->filled('po_endDate')) {
            $purchaseOrderquery->whereBetween('purchase_date', [$po_startDate, $po_endDate]);
        }
        $purchase_orders=$purchaseOrderquery->get();
        // return $purchase_orders;
        $data_array='';
        $final_netAmount=0;
        $final_vatAmount=0;
        $final_totalAmount=0;
        $final_paidAmount=0;
        foreach($purchase_orders as $val){
            // return $valOrders->purchaseOrderProducts;
            $paid_amount=$this->getAllPaymentPaid($val->id);
            $totalAmount=0;
            $gross_amount=0;
            foreach($val->purchaseOrderProducts as $product){
                $calculation=$product->qty*$product->price;
                $percentageValue=$calculation*$product->vat/100;
                $subTotal=$calculation+$percentageValue;
                $minPaidAmount=$gross_amount-$paid_amount;
                $addAllAmount=$subTotal+$minPaidAmount;
                $gross_amount=$addAllAmount;

                $final_netAmount=$final_netAmount+$calculation;
                $final_vatAmount=$final_vatAmount+$percentageValue;
                $final_totalAmount=$final_totalAmount+$subTotal;
                $final_paidAmount=$final_paidAmount+$paid_amount;
            }
            $data_array .= '<tr>
                    <td>' . date('d/m/Y', strtotime($val->purchase_date)) . '</td>
                    <td>' . $val->purchase_order_ref . '</td>
                    <td></td>
                    <td>' . (strip_tags($val->address) ?? "") . '</td>
                    <td>' . ($calculation ?? "") . '</td>
                    <td>' . ($percentageValue ?? "") . '</td>
                    <td>' . ($subTotal ?? "") . '</td>
                    <td>' . ($paid_amount ?? "") . '</td>
                    <td>£'.$gross_amount.'</td>
                </tr>';
            
        }
        // return $data_array; 
        return response()->json(['data' => $data_array,'grandNetAmount'=>$final_netAmount,'all_vatTotalAmount'=>$final_vatAmount,'all_TotalAmount'=>$final_totalAmount,'outstandingAmountTotal'=>$final_paidAmount,'grandGrossAmount'=>$gross_amount]);
        return $purchase_orders;
    }
    public function purchase_order_invoices(Request $request){
        $home_id=Auth::user()->home_id;
        $data['list']=PurchaseOrderInvoiceReceives::with('suppliers','purchaseOrders')->where(['loginUserId'=>Auth::user()->id,'deleted_at'=>null])->get();
        $data['draftCount']=PurchaseOrder::where(['user_id'=>Auth::user()->id,'deleted_at'=>null,'status'=>1])->count();
        $data['awaitingApprovalCount']=PurchaseOrder::where(['user_id'=>Auth::user()->id,'deleted_at'=>null,'status'=>2])->count();
        $data['approvedCount']=PurchaseOrder::where(['user_id'=>Auth::user()->id,'deleted_at'=>null])->whereIn('status',[3,9])->count();
        $data['rejectedCount']=PurchaseOrder::where(['user_id'=>Auth::user()->id,'deleted_at'=>null,'status'=>8])->count();
        $data['actionedCount']=PurchaseOrder::where(['user_id'=>Auth::user()->id,'deleted_at'=>null,'status'=>4])->count();
        $data['paidCount']=PurchaseOrder::where(['user_id'=>Auth::user()->id,'deleted_at'=>null,'status'=>5])->count();
        // echo "<pre>";print_r($data['list']);die;
        return view('frontEnd.salesAndFinance.purchase_order.purchase_order_invoicelist',$data);
    }
    public function searchPurchaseOrdersInvoice(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $query = PurchaseOrderInvoiceReceives::with('suppliers','purchaseOrders')->where(['deleted_at'=>null]);
    
        // echo "<pre>";print_r($query->get());die;
        $selectedsupplierId=$request->selectedsupplierId;
        if ($request->filled('po_ref')) {
            $query->where('purchase_order_ref', $po_ref);
        }
        if ($request->filled('supplier')) {
            $query->where('supplier_id', $selectedsupplierId);
        }
        if ($request->filled('id_startDate') && $request->filled('id_endDate')) {
            $query->whereBetween('expected_deleveryDate', [$edd_startDate, $edd_endDate]);
        }
        if ($request->filled('created_startDate') && $request->filled('created_endDate')) {
            $query->whereBetween('purchase_date', [$po_startDate, $po_endDate]);
        }
        
        if ($request->filled('paid_status')) {
            $query->where('delivery_status', $delivery_status);
        }
        $search_data = $query->where('loginUserId',Auth::user()->id)->get();
        return $search_data;
    }
    public function getAllPurchaseInvoices(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $po_id=$request->po_id;
        // $data = PurchaseOrderInvoiceReceives::with('suppliers','purchaseOrders')->where(['po_id'=>$po_id,'deleted_at'=>null])->orderBy('id', 'desc')->paginate(10);
        $data = PurchaseOrderInvoiceReceives::with('suppliers','purchaseOrders')->where(['po_id'=>$po_id,'deleted_at'=>null])->orderBy('id', 'desc')->get();

        return response()->json([
            'success' => true, 'list_data' => $data, 
            // 'pagination' => [
            //         'total' => $data->total(),
            //         'current_page' => $data->currentPage(),
            //         'last_page' => $data->lastPage(),
            //         'per_page' => $data->perPage(),
            //         'next_page_url' => $data->nextPageUrl(),
            //         'prev_page_url' => $data->previousPageUrl(),
            //     ]
        ]);
    }
    public function getAllPaymentPaids(Request $request){
        // echo "<pre>";print_r($request->all());die;
        // $data=PurchaseOrderRecordPayment::where('po_id',$request->po_id)->get();
        $purchaseOrderquery = DB::table('purchase_order_record_payments')->select(DB::raw('id,home_id,po_id,supplier_id,product_id,record_amount_paid,record_payment_date as date,record_payment_type,created_at,loginUserName,record_type'))->where(['po_id'=>$request->po_id,'deleted_at'=>null]);
        
        $purchase_order=$purchaseOrderquery->get();
        // return $purchase_order;
        $creditquery = DB::table('credit_note_allocates')->where(['po_id'=>$request->po_id,'deleted_at'=>null]);
        
        $credit_allocate=$creditquery->get();
        $mergedData = $purchase_order->merge($credit_allocate);
        $sortedData = $mergedData->sortBy('date');

        $sortedArray = $sortedData->values()->all();
        // return $sortedArray;
        $html_data='';
        foreach($sortedArray as $val){
            if(isset($val->credit_id) && $val->credit_id !=''){
                $type='Credit Note';
                $ref=CreditNote::find($val->credit_id)->value('credit_ref');
                // $ref='';
                $reference='';
                $description='';
                $amount=$val->amount_paid;
                $delete_from='credit_allocate';
            }else{
                $type=Payment_type::find($val->record_payment_type)->value('title');
                if($val->record_type==1){
                    // $ref=PurchaseOrder::find($val->po_id)->value('purchase_order_ref');
                    $ref='';
                }else{
                    $ref=PurchaseOrderInvoiceReceives::find($val->po_id)->value('inv_ref');
                }
                $amount=$val->record_amount_paid;
                $reference='';
                $description='';
                $delete_from='record_payements';
            }
            $html_data.='<tr>
                        <td>'.date('m/d/Y',strtotime($val->created_at)).'</td>
                        <td>'.$val->loginUserName.'</td>
                        <td>'.$val->date.'</td>
                        <td>'.$type.'</td>
                        <td>'.$ref.'</td>
                        <td>'.$reference.'</td>
                        <td>'.$description.'</td>
                        <td>PAYMENT</td>
                        <td>'.$amount.'</td>
                        <td><img src="'.url("public/frontEnd/jobs/images/delete.png").'" alt="" class="image_delete_payment_paid" data-delete="'.$val->id.'" data-delete_from="'.$delete_from.'"></td>
                        </tr>';
        }
        return response()->json(['success'=>true,'data'=>$html_data,'len'=>count($sortedArray)]);

    }
    public function paymentPaidDelete(Request $request){
        // echo "<pre>";print_r($request->all());die;
        if($request->delete_from === 'record_payements'){
            try{
                // PurchaseOrderRecordPayment::find($request->id)->update(['deleted_at' => now()]);
                $data=PurchaseOrderRecordPayment::find($request->id);
                $data->update(['deleted_at'=>now()]);
                $purchaseOrder=PurchaseOrder::find($data->po_id);
                $calculated_amount=$purchaseOrder->outstanding_amount+$data->record_amount_paid;
                $purchaseOrder->update(['outstanding_amount'=>$calculated_amount]);
                return response()->json(['success'=>true,'message'=>'Deleted Successfully Done']);
            }catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }else{
            try{
                CreditNoteAllocate::find($request->id)->update(['deleted_at' => now()]);
                return response()->json(['success'=>true,'message'=>'Deleted Successfully Done']);
            }catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }
    }
    public function preview_multiple_purchaseOrders(Request $request){
        // echo "<pre>";print_r($request->all());die;
        try{
            $po_id = explode(',', $request->key);
            $po_details=PurchaseOrder::with('suppliers','purchaseOrderProducts')->whereIn('id', $po_id)->where('deleted_at', null)
            ->get();
            // echo "<pre>";print_r(count($po_details));die;
            
            $data=[
                'email'=>Auth::user()->email,
                'phone_no'=>Auth::user()->phone_no,
                'job_title'=>Auth::user()->job_title,
                'current_location'=>Auth::user()->current_location,
                'company'=>Admin::find(Auth::user()->company_id)->company ?? "",
                'po_details'=>$po_details,
            ];
            // echo "<pre>";print_r($data);die;
            $pdf = PDF::loadView('frontEnd.salesAndFinance.purchase_order.multiplepurchaseOrderPDF',$data);
            return $pdf->stream('frontEnd.salesAndFinance.purchase_order.multiplepurchaseOrderPDF');
            // return $pdf->download('purchaseOrderPDF.pdf');
        }catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function purchase_order_approveMultiple(Request $request){
        // return response()->json(['sueccess'=>true,'data'=>$request->all()]);
        $po_id=$request->approveids;
        // return response()->json(['sueccess'=>true,'data'=>$po_id]);
        try{
            for($i=0;$i<count($po_id);$i++){
                $purchaseOrder=PurchaseOrder::find($po_id[$i]);
                if($purchaseOrder->outstanding_amount != 0 && $purchaseOrder->status != 5){
                    $purchaseOrder->status=3;
                    $purchaseOrder->save();
                }
            }
            // PurchaseOrder::whereIn('id', $po_id)->update(['status' => 3]);
            return response()->json(['success'=>true,'message'=>'Purchase Order Apporoved']);
        }catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function searchPurchase_ref(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $query = $request->input('purchase_ref');  
        $home_id = Auth::user()->home_id;

        $QuoteSearchData = PurchaseOrder::with('suppliers')->where('purchase_order_ref', 'LIKE', "%$query%")
            ->where('home_id', $home_id)
            // ->where('status', 1)
            ->whereNull('deleted_at')
            ->take(10)
            ->get();
        return response()->json(['data' => $QuoteSearchData]);
    }
    public function saveBulkInvoiceModal(Request $request){
        // echo "<pre>";print_r($request->all());die;
        try {
            for($i=0;$i<count($request->inv_ref);$i++){
                if($request->inv_ref[$i] == ''){
                    return response()->json(['success'=>false,'message'=>'Invoice Ref field is required.','invoice'=>array()]);
                }
                $data=[
                    'home_id'=>Auth::user()->home_id,
                    'loginUserId'=>Auth::user()->id,
                    'po_id'=>$request->po_id[$i],
                    'supplier_id'=>$request->supplier_id[$i],
                    'inv_ref'=>$request->inv_ref[$i],
                    'net_amount'=>$request->net_amount[$i],
                    'vat_id'=>$request->vat_id[$i],
                    'vat_amount'=>$request->vat_amount[$i],
                    'gross_amount'=>$request->gross_amount[$i],
                    'oustanding_amount'=>$request->gross_amount[$i],
                    'invoice_date'=>Carbon::createFromFormat('d/m/Y', $request->invoice_date[$i])->format('Y-m-d'),
                ];
                $invoice=PurchaseOrderInvoiceReceives::purchaseOrderInvoiceReceives_save($data);
            }
            // return $data;die;
            
            if($request->id == ''){
                return response()->json(['success' => true,'message'=>'Invoice has been saved', 'invoice' => $invoice]);
            }else{
                return response()->json(['success' => true,'message'=>'Invoice has been updated', 'invoice' => $invoice]);
            }
            
        } catch (\Exception $e) {
            Log::error('Error saving Bulk Invoices purchase order: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function saveBulkRecordPaymentModal(Request $request){
        // echo "<pre>";print_r($request->all());die;
        try{
            for($i=0;$i<count($request->po_id);$i++){
                if($request->outstanding_amount[$i] == 0 || $request->record_amount_paid[$i] == 0){

                }else{
                    $data=[
                        'home_id'=>Auth::user()->home_id,
                        'loginUserId'=>Auth::user()->id,
                        'loginUserName'=>Auth::user()->name,
                        'po_id'=>$request->po_id[$i],
                        'supplier_id'=>$request->supplier_id[$i],
                        'record_amount_paid'=>$request->record_amount_paid[$i],
                        'record_payment_date'=>$request->record_payment_date[$i],
                        'record_payment_type'=>$request->record_payment_type[$i],
                        'record_reference'=>$request->record_reference[$i] ?? "",
                        'record_type'=>$request->record_type,
                    ];
                    $orderRecord=PurchaseOrderRecordPayment::savePurchaseOrderRecordPayment($data);
                    $calculation_amount=$request->outstanding_amount[$i]-$request->record_amount_paid[$i];
                    $tablePurchaseOrder=PurchaseOrder::find($request->po_id[$i]);
                    $tablePurchaseOrder->outstanding_amount=$calculation_amount;
                    if($calculation_amount == 0){
                        $tablePurchaseOrder->status=5;
                    }
                    $tablePurchaseOrder->save();
                }
                
            }
            
            return response()->json(['success'=>true,'message'=>'The Record Payment has been saved successfully.','data'=>array()]);
        }catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function finance_dashboard(){
        
        return view('frontEnd.salesAndFinance.common.finance_dasboard');
    }
    
}
