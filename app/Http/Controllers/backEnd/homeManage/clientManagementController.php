<?php

namespace App\Http\Controllers\backEnd\homeManage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session,DB,Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\clientTaskType;
use App\Models\clientTaskCategory;
use App\Models\AlertType;

class clientManagementController extends Controller
{
    public function index(Request $request){
        $home_id = Session::get('scitsAdminSession')->home_id;
        if(empty($home_id)) {
            return redirect('admin/')->with('error',NO_HOME_ERR);
        }
        $task_type = clientTaskType::select('id','home_id','title','created_at','status')
                                        // ->where('home_id',$home_id)
                                        ->orderBy('id','desc');
        // echo "<pre>";print_r($task_type);die;
        $search = '';
        
        if(isset($request->limit)) {
            $limit = $request->limit;
            Session::put('page_record_limit',$limit);
        } else {

            if(Session::has('page_record_limit')) {
                $limit = Session::get('page_record_limit');
            } else{
                $limit = 20;
            }
        }
        if(isset($request->search)) {
            $search      = trim($request->search);
            $task_type = $task_type->where('title','like','%'.$search.'%');
        }

        $task_type = $task_type->paginate($limit);
        $page = 'client_taskTyep';
       	return view('backEnd/homeManage/client/client_task_type', compact('page','limit','task_type','search'));
    }
    public function save(Request $request){
        // echo "<pre>";print_r($request->all());die;
        if(!empty($request->id)){
            $validator = Validator::make($request->all(), [
                'id'=>'required|exists:client_task_types,id',
                'title'=>'required',
                'status'=>'required|boolean',
            ]);
        }else{
            $validator = Validator::make($request->all(), [
                'title'=>'required',
                'status'=>'required|boolean',
            ]);
        }
        
        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()->first()
            ];
        }
        try{
            DB::beginTransaction();
            $data = $request->except('id');
            $data['home_id'] = Session::get('scitsAdminSession')->home_id;
            clientTaskType::updateOrCreate(['id' => $request['id'] ?? null],$data);
            DB::commit();
            Session::flash('success','Saved successfully done.');
            return response()->json([
                'success'  => true,
                'message' => "Saved successfully done.",
                'data'=>json_decode('{}')
            ]);

        }catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving Client task type: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error save Client task type: ' . $e->getMessage(),
            ];
        }
    }
    public function delete($id){
        $validator = Validator::make(['id'=>$id], [
            'id'=>'required|exists:client_task_types,id',
        ]);
        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()->first()
            ];
        }
        try{
            DB::beginTransaction();
            $table = clientTaskType::find($id);
            $table->delete();
            DB::commit();
            Session::flash('success','Deleted successfully done.');
            return redirect()->back();

        }catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error client task types delete: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error client task types delete: ' . $e->getMessage(),
            ];
        }
    }
    public function status_change(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'id'=>'required|exists:client_task_types,id',
            'status'=>'required|boolean',
        ]);
        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()->first()
            ];
        }
        try{
            DB::beginTransaction();
            $table = clientTaskType::find($request->id);
            $table->status = $request->status;
            $table->save();
            DB::commit();
            Session::flash('success','Status changed successfully done.');
            return response()->json([
                'success'  => true,
                'message' => "Status changed successfully done.",
                'data'=>json_decode('{}')
            ]);

        }catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error client task types status change: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error client task types status change: ' . $e->getMessage(),
            ];
        }

    }
    public function task_category(Request $request){
        $home_id = Session::get('scitsAdminSession')->home_id;
        if(empty($home_id)) {
            return redirect('admin/')->with('error',NO_HOME_ERR);
        }
        $task_category = clientTaskCategory::select('id','home_id','title','created_at','status')
                                        // ->where('home_id',$home_id)
                                        ->orderBy('id','desc');
        // echo "<pre>";print_r($task_category);die;
        $search = '';
        
        if(isset($request->limit)) {
            $limit = $request->limit;
            Session::put('page_record_limit',$limit);
        } else {

            if(Session::has('page_record_limit')) {
                $limit = Session::get('page_record_limit');
            } else{
                $limit = 20;
            }
        }
        if(isset($request->search)) {
            $search      = trim($request->search);
            $task_category = $task_category->where('title','like','%'.$search.'%');
        }

        $task_category = $task_category->paginate($limit);
        $page = 'client_taskTyep';
       	return view('backEnd/homeManage/client/task_category', compact('page','limit','task_category','search'));
    }
    public function save_task_category(Request $request){
        // echo "<pre>";print_r($request->all());die;
        if(!empty($request->id)){
            $validator = Validator::make($request->all(), [
                'id'=>'required|exists:client_task_categories,id',
                'title'=>'required',
                'status'=>'required|boolean',
            ]);
        }else{
            $validator = Validator::make($request->all(), [
                'title'=>'required',
                'status'=>'required|boolean',
            ]);
        }
        
        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()->first()
            ];
        }
        try{
            DB::beginTransaction();
            $data = $request->except('id');
            $data['home_id'] = Session::get('scitsAdminSession')->home_id;
            clientTaskCategory::updateOrCreate(['id' => $request['id'] ?? null],$data);
            DB::commit();
            Session::flash('success','Saved successfully done.');
            return response()->json([
                'success'  => true,
                'message' => "Saved successfully done.",
                'data'=>json_decode('{}')
            ]);

        }catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving Client task category: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error save Client task category: ' . $e->getMessage(),
            ];
        }
    }
    public function status_change_task_category(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'id'=>'required|exists:client_task_categories,id',
            'status'=>'required|boolean',
        ]);
        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()->first()
            ];
        }
        try{
            DB::beginTransaction();
            $table = clientTaskCategory::find($request->id);
            $table->status = $request->status;
            $table->save();
            DB::commit();
            Session::flash('success','Status changed successfully done.');
            return response()->json([
                'success'  => true,
                'message' => "Status changed successfully done.",
                'data'=>json_decode('{}')
            ]);

        }catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error client task category status change: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error client task category status change: ' . $e->getMessage(),
            ];
        }

    }
    public function delete_task_category($id){
        $validator = Validator::make(['id'=>$id], [
            'id'=>'required|exists:client_task_categories,id',
        ]);
        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()->first()
            ];
        }
        try{
            DB::beginTransaction();
            $table = clientTaskCategory::find($id);
            $table->delete();
            DB::commit();
            Session::flash('success','Deleted successfully done.');
            return redirect()->back();

        }catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error client task category delete: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error client task category delete: ' . $e->getMessage(),
            ];
        }
    }
    public function alert_type(Request $request){
        $home_id = Session::get('scitsAdminSession')->home_id;
        if(empty($home_id)) {
            return redirect('admin/')->with('error',NO_HOME_ERR);
        }
        $alert_type = AlertType::select('id','home_id','title','created_at','status')
                                        // ->where('home_id',$home_id)
                                        ->orderBy('id','desc');
        // echo "<pre>";print_r($alert_type);die;
        $search = '';
        
        if(isset($request->limit)) {
            $limit = $request->limit;
            Session::put('page_record_limit',$limit);
        } else {

            if(Session::has('page_record_limit')) {
                $limit = Session::get('page_record_limit');
            } else{
                $limit = 20;
            }
        }
        if(isset($request->search)) {
            $search      = trim($request->search);
            $alert_type = $alert_type->where('title','like','%'.$search.'%');
        }

        $alert_type = $alert_type->paginate($limit);
        $page = 'alert_type';
       	return view('backEnd/homeManage/client/alert_type', compact('page','limit','alert_type','search'));
    }
    public function save_alert_type(Request $request){
        // echo "<pre>";print_r($request->all());die;
        if(!empty($request->id)){
            $validator = Validator::make($request->all(), [
                'id'=>'required|exists:alert_types,id',
                'title'=>'required',
                'status'=>'required|boolean',
            ]);
        }else{
            $validator = Validator::make($request->all(), [
                'title'=>'required',
                'status'=>'required|boolean',
            ]);
        }
        
        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()->first()
            ];
        }
        try{
            DB::beginTransaction();
            $data = $request->except('id');
            $data['home_id'] = Session::get('scitsAdminSession')->home_id;
            AlertType::updateOrCreate(['id' => $request['id'] ?? null],$data);
            DB::commit();
            Session::flash('success','Saved successfully done.');
            return response()->json([
                'success'  => true,
                'message' => "Saved successfully done.",
                'data'=>json_decode('{}')
            ]);

        }catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving Alert Type: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error save Alert Type: ' . $e->getMessage(),
            ];
        }
    }
    public function status_change_alert_type(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'id'=>'required|exists:alert_types,id',
            'status'=>'required|boolean',
        ]);
        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()->first()
            ];
        }
        try{
            DB::beginTransaction();
            $table = AlertType::find($request->id);
            $table->status = $request->status;
            $table->save();
            DB::commit();
            Session::flash('success','Status changed successfully done.');
            return response()->json([
                'success'  => true,
                'message' => "Status changed successfully done.",
                'data'=>json_decode('{}')
            ]);

        }catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error Alert Type status change: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error Alert Type status change: ' . $e->getMessage(),
            ];
        }

    }
    public function delete_alert_type($id){
        $validator = Validator::make(['id'=>$id], [
            'id'=>'required|exists:alert_types,id',
        ]);
        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()->first()
            ];
        }
        try{
            DB::beginTransaction();
            $table = AlertType::find($id);
            $table->delete();
            DB::commit();
            Session::flash('success','Deleted successfully done.');
            return redirect()->back();

        }catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error Alert Type delete: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error Alert Type delete: ' . $e->getMessage(),
            ];
        }
    }
}
