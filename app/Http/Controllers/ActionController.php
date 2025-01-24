<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class ActionController extends Controller
{
    public function status_change(Request $request)
    {
        // echo "<pre>";print_r($request->all());die;
        $id = $request->id;
        if($id =='' || $request->model == ''){
            return false;
        }
        
        if($request->status == 1){
            $status=0;
        }else {
            $status=1;
        }
        try {
            if($request->model == "Customer"){
                $modelName = "App\\" . $request->model;
            }else{
                $modelName = "App\Models\\" . $request->model;
            }
            
            $model = app($modelName);
            $data = $model->find($id);
            $data->status = $status;
            $data->save();
            return true;
        } catch (\Exception $e) {
            return response()->json(['success'=>'false','message' => $e->getMessage()], 500);
        }
        // if($data){
        //     return 1;
        // }else {
        //     return 0;
        // }
        
    }
    public function bulk_delete(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $id = $request->ids;
        if($id =='' || $request->model == ''){
            return false;
        }
        try {
            if($request->model == "Customer"){
                $modelName = "App\\" . $request->model;
            } else if ($request->model == "Lead"){
                $modelName = "App\\" . $request->model;
            } else {
                $modelName = "App\Models\\" . $request->model;
            }
            
            $model = app($modelName);
            $delete=$model::whereIn('id',$id)->update(['deleted_at' => Carbon::now()]);
            return true;
        } catch (\Exception $e) {
            return response()->json(['success'=>'false','message' => $e->getMessage()], 500);
        }
    }
}
