<?php

namespace App\Http\Controllers\backEnd\homeManage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session,DB,Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\IncidentType;

class StaffIncidentTypeController extends Controller
{
    public function index(Request $request){
        $home_id = Session::get('scitsAdminSession')->home_id;
        if(empty($home_id)) {
            return redirect('admin/')->with('error',NO_HOME_ERR);
        }
        $incidentType = IncidentType::select('id','home_id','type','created_at','status')
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
            $incidentType = $incidentType->where('category','like','%'.$search.'%');
        }

        $incidentType = $incidentType->paginate($limit);
        $page = 'incidenttype';
       	return view('backEnd/homeManage/staff_incident_type', compact('page','limit','incidentType','search'));
    }
    public function save(Request $request){
        // echo "<pre>";print_r($request->all());die;
        if(!empty($request->id)){
            $validator = Validator::make($request->all(), [
                'id'=>'required|exists:incident_types,id',
                'type'=>'required',
                'status'=>'required|boolean',
            ]);
        }else{
            $validator = Validator::make($request->all(), [
                'type'=>'required',
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
            IncidentType::updateOrCreate(['id' => $request['id'] ?? null],$data);
            DB::commit();
            Session::flash('success','Saved successfully done.');
            return response()->json([
                'success'  => true,
                'message' => "Saved successfully done.",
                'data'=>json_decode('{}')
            ]);

        }catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving incident_types: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error save incident_types: ' . $e->getMessage(),
            ];
        }
    }
    public function delete($id){
        $validator = Validator::make(['id'=>$id], [
            'id'=>'required|exists:incident_types,id',
        ]);
        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()->first()
            ];
        }
        try{
            DB::beginTransaction();
            $table = IncidentType::find($id);
            $table->delete();
            DB::commit();
            Session::flash('success','Deleted successfully done.');
            return redirect()->back();

        }catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error incident types delete: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error incident types delete: ' . $e->getMessage(),
            ];
        }
    }
    public function status_change(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'id'=>'required|exists:incident_types,id',
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
            $table = IncidentType::find($request->id);
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
            Log::error('Error incident types status change: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error incident types status change: ' . $e->getMessage(),
            ];
        }

    }
}
