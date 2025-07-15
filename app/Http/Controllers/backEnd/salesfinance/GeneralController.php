<?php

namespace App\Http\Controllers\backEnd\salesfinance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttachmentType;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\Payment_type;
use App\Models\Region;
use App\Models\Task_type;
use App\Models\Tag;

class GeneralController extends Controller
{
    // Attachment Types
    public function attachment_types_index(){
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        if($home_id){
            $page = 'attachment_types';
            $attachment_types = AttachmentType::where('deleted_at', null)->get();
            return view('backEnd/salesFinance/general/attachment_types', compact('page', 'attachment_types'));
        }else{
            return redirect('admin/')->with('error',NO_HOME_ERR);
        }
    }

    public function saveAttachmentType(Request $request){
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        AttachmentType::updateOrCreate(['id' => $request->attachment_type_id], array_merge($request->all(), ['home_id' =>  Session::get('scitsAdminSession')->home_id]));

        if(isset($request->attachment_type_id)){
            return response()->json(['message' => 'Record updated successfully!']);
        } else {
            return response()->json(['message' => 'Attachment Type added successfully!']);
        }
    }
    public function delete_attachment_type($id){
        $affectedRows  = AttachmentType::where('id', $id)->update(['deleted_at' => Carbon::now()]);
        if($affectedRows ){
            return redirect()->route('attachment_types.view')->with('success', "Record deleted successfully");
        } else {
            return redirect()->route('attachment_types.view')->with('error', "Record not found");
        }
    }

    public function payment_types(){
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        if($home_id){
            $data['page'] = 'payment_types';
            $data['payment_types'] = Payment_type::whereNull('deleted_at')->get();
            $data['home_id']=Session::get('scitsAdminSession')->home_id;
            return view('backEnd/salesFinance/general/payment_types', $data);
        }else{
            return redirect('admin/')->with('error',NO_HOME_ERR);
        }
    }
    public function SavePaymentType(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'title' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['vali_error' => $validator->errors()->first()]);
        }
        $data=Payment_type::savePayment_type($request->all());
        return response()->json(['data' => $data]);
    }
    public function payment_type_delete(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $id=base64_decode($request->key);
        $payment_delete=Payment_type::where('id', $id)->update(['deleted_at' => Carbon::now()]);
        if($payment_delete ){
            return redirect()->back()->with('success', "Record deleted successfully");
        } else {
            return redirect()->back()->with('error', "Record not found");
        }
    }
    public function regins(){
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        if($home_id){
            $data['page'] = 'regions';
            $data['region'] = Region::whereNull('deleted_at')->get();
            $data['home_id']=Session::get('scitsAdminSession')->home_id;
            return view('backEnd/salesFinance/general/region', $data);
        }else{
            return redirect('admin/')->with('error',NO_HOME_ERR);
        }
    }
    public function saveRegion(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'title' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['vali_error' => $validator->errors()->first()]);
        }

        $saveData = Region::updateOrCreate(['id'=>$request->id ?? null],$request->all());
        if($saveData){
            if($saveData->status ==1){
                echo '<option value="'.$saveData->id.'">'.$saveData->title.'</option>';
            }
        }else{
            echo "error";
        }
        // return response()->json(['data' => $saveData]);
    }
    public function region_delete(Request $request){
        $id=base64_decode($request->key);
        $payment_delete=Region::where('id', $id)->update(['deleted_at' => Carbon::now()]);
        if($payment_delete ){
            return redirect()->back()->with('success', "Record deleted successfully");
        } else {
            return redirect()->back()->with('error', "Record not found");
        }
    }
    public function task_types(){
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        if($home_id){
            $data['page'] = 'task_type';
            $data['task_type'] = Task_type::whereNull('deleted_at')->get();
            $data['home_id']=Session::get('scitsAdminSession')->home_id;
            return view('backEnd/salesFinance/general/task_type', $data);
        }else{
            return redirect('admin/')->with('error',NO_HOME_ERR);
        }
    }
    public function saveTaskType(Request $request){
        $validator = Validator::make($request->all(), [
            'title' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['vali_error' => $validator->errors()->first()]);
        }

        $saveData = Task_type::saveTask_type($request->all());
        return response()->json(['data' => $saveData]);
    }
    public function task_type_delete(Request $request){
        $id=base64_decode($request->key);
        $task_delete=Task_type::where('id', $id)->update(['deleted_at' => Carbon::now()]);
        if($task_delete ){
            return redirect()->back()->with('success', "Record deleted successfully");
        } else {
            return redirect()->back()->with('error', "Record not found");
        }
    }
    public function tags(){
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        if($home_id){
            $data['page'] = 'tags';
            $data['tags'] = Tag::whereNull('deleted_at')->get();
            $data['home_id']=Session::get('scitsAdminSession')->home_id;
            return view('backEnd/salesFinance/general/tags', $data);
        }else{
            return redirect('admin/')->with('error',NO_HOME_ERR);
        }
    }
    public function saveTag(Request $request){
        $validator = Validator::make($request->all(), [
            'title' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['vali_error' => $validator->errors()->first()]);
        }

        $saveData = Tag::saveTag($request->all());
        return response()->json(['data' => $saveData]);
    }
    public function tags_delete(Request $request){
        $id=base64_decode($request->key);
        $task_delete=Tag::where('id', $id)->update(['deleted_at' => Carbon::now()]);
        if($task_delete ){
            return redirect()->back()->with('success', "Record deleted successfully");
        } else {
            return redirect()->back()->with('error', "Record not found");
        }
    }

}
