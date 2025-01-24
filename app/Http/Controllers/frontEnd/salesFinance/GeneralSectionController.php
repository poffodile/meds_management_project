<?php

namespace App\Http\Controllers\frontEnd\salesFinance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\AttachmentType;
use App\Models\Payment_type;
use App\Models\Region;
use App\Models\Task_type;
use App\Models\Tag;

class GeneralSectionController extends Controller
{
    public function attachments_types(){
        $home_id = Auth::user()->home_id;
        $data['attachmentType']=AttachmentType::getAllAttachmentType();
        $data['home_id']=$home_id;
        return view('frontEnd.salesAndFinance.general.attachment_type',$data);
    }

    public function save_attachment_type(Request $request){
        $data=AttachmentType::saveAttachment($request->all());
        return $data;
    }

    public function Payment_type(){
        $home_id = Auth::user()->home_id;
        $data['payment_type']=Payment_type::getAllPayment_type();
        $data['home_id']=$home_id;
        return view('frontEnd.salesAndFinance.general.payment_type',$data);
    }

    public function save_payment_type(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'title' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['vali_error' => $validator->errors()->first()]);
        }
        $requestData=$request->all();
        $requestData['home_id']=Auth::user()->home_id;
        // echo "<pre>";print_r($requestData);die;
        try {
            $data=Payment_type::savePayment_type($requestData);
            if($request->id == ''){
                return response()->json(['success'=>true,'message'=>'Payment type Added Successfully.','data' => $data]);
            }else{
                return response()->json(['success'=>true,'message'=>'Payment type Updated Successfully.','data' => $data]);
            }
        }catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function regions(){
        $home_id = Auth::user()->home_id;
        $data['region']=Region::getAllRegion($home_id);
        $data['home_id']=$home_id;
        return view('frontEnd.salesAndFinance.general.region',$data);
    }

    public function task_types(){
        $home_id = Auth::user()->home_id;
        $data['task_type']=Task_type::getAllTask_type($home_id);
        $data['home_id']=$home_id;
        return view('frontEnd.salesAndFinance.general.task_type',$data);
    }

    public function save_task_type(Request $request){
        $validator = Validator::make($request->all(), [
            'title' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['vali_error' => $validator->errors()->first()]);
        }

        $data=Task_type::saveTask_type($request->all());
        return response()->json(['data' => $data]);
    }

    
    public function save_task_type_data(Request $request){
        
        $validator = Validator::make($request->all(), [
            'title' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data =  Task_type::saveTaskTypeData($request->all(), Auth::user()->home_id);
        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? "Task type added successfully !" : 'Failed to add task type .'
        ]);
    }

    public function getTaskTypeList(){
        $data = Task_type::getAllAciveTask_type(Auth::user()->home_id);
        if ($data) {
            return response()->json(['success' => true, 'data' => $data]);
        } else {
            return response()->json(['success' => false, 'data' => 'No Data']);
        }
    }

    public function tags(){
        $home_id = Auth::user()->home_id;
        $data['tags']=Tag::getAllTag($home_id);
        $data['home_id']=$home_id;
        return view('frontEnd.salesAndFinance.jobs.tag',$data);
    }

    public function save_tag(Request $request){
        
        $validator = Validator::make($request->all(), [
            'title' => [
                'required',
                Rule::unique('tags')->ignore($request->id),
            ],
        ]);
        if ($validator->fails()) {
            return response()->json(['vali_error' => $validator->errors()->first()]);
        }
        try {
            $data=Tag::saveTag(array_merge(['home_id' => Auth::user()->home_id], $request->all()));
            if($request->id == ''){
                return response()->json(['success'=>true,'data' => $data, 'message' => "Tags added successfully"]);
            }else{
                return response()->json(['success'=>true,'data' => $data, 'message' => "Tags Updated successfully"]);
            }
        }catch (\Exception $e) {
            Log::error('Error saving Payment Type: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function getTags(){
        $data = Tag::getAllTag(Auth::user()->home_id);

        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? $data : 'No data.'
        ]);
    }
}
