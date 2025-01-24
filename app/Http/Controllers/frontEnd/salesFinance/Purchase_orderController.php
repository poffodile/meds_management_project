<?php

namespace App\Http\Controllers\frontEnd\salesFinance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\NewTaskRequest;
use Auth,Log;
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
use App\User;
use PDF;

class Purchase_orderController extends Controller
{
    public function departments(Request $request){
        $home_id = Auth::user()->home_id;
        $data['department']=Department::getAllDepartment($home_id);
        $data['home_id']=$home_id;
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
        if($key){
            $site=Constructor_customer_site::where('customer_id',$purchase_orders->customer_id)->get();
            $contact_name=Customer::find($purchase_orders->customer_id);
            $reminder_data=$this->reminder_check($key);
        }
        // echo "<pre>";print_r($reminder_data);die;
        $data['purchase_orders']=$purchase_orders;
        $data['attachments']=$attachments;
        $data['site']=$site;
        $data['projects']=Project::where(['status'=>1,'home_id'=>$home_id])->get();
        $data['customers']=Customer::get_customer_list_Attribute($home_id,'ACTIVE');
        $data['additional_contact'] = Constructor_additional_contact::where(['home_id'=> $home_id,'userType'=>2,'customer_id'=>$key,'deleted_at'=>null])->get();
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
        // echo "<pre>";print_r($data['country']);die;
        return view('frontEnd.salesAndFinance.purchase_order.new_purchase_order',$data);
    }
    public function purchase_order_save(Request $request){
        // echo "<pre>";print_r($request->all());die;
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
        $purchase_order_products = PurchaseOrder::with(['purchaseOrderProducts'])
        ->where(['id' => $request->id, 'deleted_at' => null])
        ->first();
        // return $purchase_order_products;
        if (!$purchase_order_products) {
            return response()->json(['success' => false, 'message' => 'Purchase Order not found.']);
        }
        
        $tax = Product::tax_detail($home_id);
        $all_job = Job::getAllJob($home_id)->where('status', 1)->get();
        $accountCode = Construction_account_code::getActiveAccountCode($home_id);
        
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
            ];
        }
        
        return response()->json([
            'success' => true,
            'data' => $data_array,
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

            for ($i = 0; $i < count($product_ids); $i++) {
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
                    'outstanding_amount'=>0
                ];
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
        // echo "<pre>";print_r($data['list']);die;
        return view('frontEnd.salesAndFinance.purchase_order.purchase_order_list',$data);
    }
    private function check_segment_purchaseOrder($lastSegment=null){
        if($lastSegment === 'AwaitingApprivalPurchaseOrders'){
            return ['status'=>2,'list_status'=>'Awaiting Approval Purchase Oreders'];
        }else if($lastSegment === 'Approved'){
            return ['status'=>3,'list_status'=>'Approved'];
        }else if($lastSegment === 'Rejected'){
            return ['status'=>8,'list_status'=>'Rejected'];
        }else if($lastSegment === 'Actioned'){
            return ['status'=>4,'list_status'=>'Actioned'];
        }else if($lastSegment === 'Paid'){
            return ['status'=>5,'list_status'=>'Paid'];
        }else{
            return ['status'=>1,'list_status'=>'Draft'];
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
        $query = PurchaseOrder::with('suppliers','purchaseOrderProducts')->where(['deleted_at'=>null,'status'=>$status]);
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
            $outstandingAmount=0;
            foreach($val->purchaseOrderProducts as $product){
                $purchaseProductId=$product->id;
                $qty=$product->qty*$product->price;
                $sub_total_amount=$sub_total_amount+$qty;
                $vat=$qty*$product->vat/100;
                $vat_amount=$vat_amount+$vat;
                $total_amount=$total_amount+$vat+$qty;
                $outstandingAmount=$total_amount-$product->outstanding_amount;
            }
            $all_subTotalAmount=$all_subTotalAmount+$sub_total_amount;
            $all_vatTotalAmount=$all_vatTotalAmount+$vat_amount;
            $all_TotalAmount=$all_TotalAmount+$total_amount;
            $outstandingAmountTotal=$outstandingAmountTotal+$outstandingAmount;
            
            $array_data .= '<tr>
                        <td><input type="checkbox" class="delete_checkbox" value="' . $val->id . '"></td>
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
                        <td>£' . $outstandingAmount . '</td>
                        <td>' . $list_status . '</td>';
                        if($status == 1){
                            $array_data.='<td>-</td>
                            <td>
                                <div class="d-flex justify-content-end">
                                    <div class="nav-item dropdown">
                                        <a href="#!" class="nav-link dropdown-toggle profileDrop" data-bs-toggle="dropdown" aria-expanded="false">
                                            Action
                                        </a>
                                        <div class="dropdown-menu fade-up m-0">
                                            <a href="'.url('purchase_order_edit?key=').''.base64_encode($val->id).'" class="dropdown-item">Edit</a>
                                            <hr class="dropdown-divider">
                                            <a href="'.url('preview?key=').''.base64_encode($val->id).'" target="_blank" class="dropdown-item">Preview</a>
                                            <hr class="dropdown-divider">
                                            <a href="'.url('purchase_order?duplicate=').''.base64_encode($val->id).'" target="_blank" class="dropdown-item">Duplicate</a>
                                            <hr class="dropdown-divider">
                                            <a href="javascript:void(0)" onclick="openApproveModal('.$val->id.','.$val->purchase_order_ref.')" class="dropdown-item">Approve</a>
                                            <hr class="dropdown-divider">
                                            <a href="#!" class="dropdown-item">CRM / History</a>
                                            <hr class="dropdown-divider">
                                            <a href="#!" class="dropdown-item">Start Timer</a>
                                        </div>
                                    </div>
                                </div>
                            </td>';
                        }else{
                            $array_data.='<td>';
                            if($val->delivery_status == 1){
                                $array_data.='<span class="grencheck"><i class="fa-solid fa-check"></i></span>';
                            }else{
                                $array_data.='<a href="javascript:void(0)" class="tutor-student-tooltip-col" style="color:red">X<span class="tutor-student-tooltiptext3">Not Delivered</span></a>';
                            }
                            $array_data .= '</td>
                                        <td>
                                            <div class="d-flex justify-content-end">
                                                <div class="nav-item dropdown">
                                                    <a href="#!" class="nav-link dropdown-toggle profileDrop" data-bs-toggle="dropdown" aria-expanded="false">
                                                        Action
                                                    </a>
                                                    <div class="dropdown-menu fade-up m-0">
                                                        <a href="javascript:void(0)" onclick="openRecordDeliveryModal(' . $val->id . ',\'' . $val->purchase_order_ref . '\')" class="dropdown-item">Record Delivery</a>
                                                        <hr class="dropdown-divider">
                                                        <a href="' . url('purchase_order_edit?key=') . base64_encode($val->id) . '" class="dropdown-item">Edit</a>
                                                        <hr class="dropdown-divider">
                                                        <a href="'.url('preview?key=').''.base64_encode($val->id).'" target="_blank" class="dropdown-item">Preview</a>
                                                        <hr class="dropdown-divider">
                                                        <a href="'.url('preview?key=').''.base64_encode($val->id).'" target="_blank" class="dropdown-item">Print</a>
                                                        <hr class="dropdown-divider">
                                                        <a href="#!" class="dropdown-item">Email</a>
                                                        <hr class="dropdown-divider">
                                                        <a href="' . url('purchase_order?duplicate=') . base64_encode($val->id) . '" target="_blank" class="dropdown-item">Duplicate</a>
                                                        <hr class="dropdown-divider">
                                                        <a href="javascript:void(0)" onclick="openRejectModal(' . $val->id . ',\'' . $val->purchase_order_ref . '\')" class="dropdown-item">Reject</a>
                                                        <hr class="dropdown-divider">
                                                        <a href="javascript:void(0)" onclick="openRecordPaymentModal(' . $val->id . ',\'' . $val->purchase_order_ref . '\',\'' . $val->suppliers->name . '\',' . $total_amount . ',\'' . date('d/m/Y', strtotime($val->purchase_date)) . '\',' . $purchaseProductId . ',' . $outstandingAmount . ')" class="dropdown-item">Record Payment</a>
                                                        <hr class="dropdown-divider">
                                                        <a href="javascript:void(0)" onclick="openInvoiceRecieveModal(' . $val->id . ',\'' . $val->purchase_order_ref . '\',\'' . $val->suppliers->name . '\',' . $val->suppliers->id . ',' . $sub_total_amount . ',\'' . date('d/m/Y', strtotime($val->purchase_date)) . '\',' . $vat . ',' . $outstandingAmount . ')" class="dropdown-item">Invoice Received</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>';
                        }
                        
                    $array_data.='</tr>';
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
                    PurchaseOrder::find($po_id)->update(['delivery_status' => 1]);
                    return response()->json(['success'=>true,'message'=>'The Purchase Order Delivered has been saved successfully.']);
                }catch (\Exception $e) {
                    Log::error('Error: ' . $e->getMessage());
                    return response()->json(['error' => $e->getMessage()], 500);
                }
                
            }
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
        $recordPayment_ppurchaseProduct=$request->recordPayment_ppurchaseProduct;
        try{
            $orderRecord=PurchaseOrderRecordPayment::savePurchaseOrderRecordPayment($data);
            $itt=PurchaseOrderProduct::find($recordPayment_ppurchaseProduct)->update(['outstanding_amount' => $request->record_amount_paid]);
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
            $data=$request->all();
            $data['home_id']=Auth::user()->home_id;
            $data['loginUserId']=Auth::user()->home_id;
            $data['to'] = json_encode(explode(',', $request->selectedToEmail));
            $data['cc'] = $request->selectedToEmail1 ? json_encode(explode(',', $request->selectedToEmail1)) : json_encode([]);
            // echo "<pre>";print_r($data);die;
            $email=PurchaseOrderEmail::saveEmail($data);
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
}
