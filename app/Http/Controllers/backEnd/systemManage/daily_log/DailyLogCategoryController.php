<?php

namespace App\Http\Controllers\backEnd\systemManage\daily_log;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session,DB,Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\DailyLogCategory;
use App\Models\DailyLogSubCategory;

class DailyLogCategoryController extends Controller
{
    public function index(Request $request){
        $home_id = Session::get('scitsAdminSession')->home_id;
        if(empty($home_id)) {
            return redirect('admin/')->with('error',NO_HOME_ERR);
        }
        $categorys = DailyLogCategory::select('id','home_id','category','created_at','status')
                                        // ->where('home_id',$home_id)
                                        ->orderBy('id','desc');
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
            $categorys = $categorys->where('category','like','%'.$search.'%');
        }

        $categorys = $categorys->paginate($limit);
        $page = 'daily_log_category';
       	return view('backEnd/systemManage/daily_log/daily_log_category', compact('page','limit','categorys','search'));
    }
    public function save(Request $request){
        if(!empty($request->id)){
            $validator = Validator::make($request->all(), [
                'id'=>'required|exists:daily_log_categories,id',
                'category'=>'required',
                'status'=>'required|boolean',
            ]);
        }else{
            $validator = Validator::make($request->all(), [
                'category'=>'required',
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
            DailyLogCategory::updateOrCreate(['id' => $request['id'] ?? null,'home_id'=>Session::get('scitsAdminSession')->home_id],$request->all());
            DB::commit();
            Session::flash('success','Saved successfully done.');
            return response()->json([
                'success'  => true,
                'message' => "Saved successfully done.",
                'data'=>json_decode('{}')
            ]);

        }catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving Daily log category: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error save daily log: ' . $e->getMessage(),
            ];
        }
    }
    public function delete($id){
        $validator = Validator::make(['id'=>$id], [
            'id'=>'required|exists:daily_log_categories,id',
        ]);
        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()->first()
            ];
        }
        try{
            DB::beginTransaction();
            $table = DailyLogCategory::find($id);
            $table->delete();
            DB::commit();
            Session::flash('success','Deleted successfully done.');
            return redirect()->back();

        }catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error Daily log category delete: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error daily log delete: ' . $e->getMessage(),
            ];
        }
    }
    public function status_change(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'id'=>'required|exists:daily_log_categories,id',
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
            $table = DailyLogCategory::find($request->id);
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
            Log::error('Error Daily log category status change: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error daily log status change: ' . $e->getMessage(),
            ];
        }

    }
    public function sub_category(Request $request){
        $home_id = Session::get('scitsAdminSession')->home_id;
        if(empty($home_id)) {
            return redirect('admin/')->with('error',NO_HOME_ERR);
        }
        // $data = DailyLogSubCategory::with('dailyLogCategory')->get();
        // return response()->json(['data'=>$data]);die;
        $sub_categorys = DailyLogSubCategory::select('id','home_id','daily_cat_id','sub_cat','icon','color','created_at','status')
                // ->where('home_id',$home_id)
                ->with(['dailyLogCategory'=>function($q) use ($home_id){
                    $q->select('id','home_id','category','status');
                    // ->where('status',1);
                    // ->where('home_id',$home_id);
                }])
                ->orderBy('id','desc');
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
            $sub_categorys = $sub_categorys->where('sub_cat','like','%'.$search.'%');
        }

        $sub_categorys = $sub_categorys->paginate($limit);
        // return $sub_categorys;
        $categorys = DailyLogCategory::select('id','home_id','category','status')
        // ->where('home_id',$home_id)
        ->where('status',1)->get();
        $page = 'daily_log_sub_category';
       	return view('backEnd/systemManage/daily_log/daily_log_sub_category', compact('page','limit','sub_categorys','search','categorys'));
    }
    public function sub_category_save(Request $request){
        // echo "<pre>";print_r($request->all());die;
         if(!empty($request->id)){
            $validator = Validator::make($request->all(), [
                'id'=>'required|exists:daily_log_sub_categories,id',
                'daily_cat_id'=>'required|exists:daily_log_categories,id',
                'sub_cat'=>'required',
                'icon'=>'required',
                'color'=>'required',
                'status'=>'required|boolean',
            ]);
        }else{
            $validator = Validator::make($request->all(), [
                'daily_cat_id'=>'required|exists:daily_log_categories,id',
                'sub_cat'=>'required',
                'icon'=>'required',
                'color'=>'required',
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
            // echo "<pre>";print_r($data);die;
            DailyLogSubCategory::updateOrCreate(['id' => $request['id'] ?? null],$data);
            DB::commit();
            Session::flash('success','Saved successfully done.');
            return response()->json([
                'success'  => true,
                'message' => "Saved successfully done.",
                'data'=>json_decode('{}')
            ]);

        }catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving Daily log sub category: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error save daily log sub category: ' . $e->getMessage(),
            ];
        }
    }
    public function sub_category_status_change(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'id'=>'required|exists:daily_log_sub_categories,id',
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
            $table = DailyLogSubCategory::find($request->id);
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
            Log::error('Error Daily log sub category status change: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error daily log sub category status change: ' . $e->getMessage(),
            ];
        }
    }
    public function sub_category_delete($id){
        $validator = Validator::make(['id'=>$id], [
            'id'=>'required|exists:daily_log_sub_categories,id',
        ]);
        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()->first()
            ];
        }
        try{
            DB::beginTransaction();
            $table = DailyLogSubCategory::find($id);
            $table->delete();
            DB::commit();
            Session::flash('success','Deleted successfully done.');
            return redirect()->back();

        }catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error Daily log sub category delete: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error daily log sub category delete: ' . $e->getMessage(),
            ];
        }
    }
}
