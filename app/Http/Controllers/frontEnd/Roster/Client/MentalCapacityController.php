<?php

namespace App\Http\Controllers\frontEnd\Roster\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth,Validator;
use App\Services\Client\MentalCapacityService;

class MentalCapacityController extends Controller
{
    protected $MentalCapacityService;
    public function __construct(MentalCapacityService $MentalCapacityService)    {
        $this->MentalCapacityService = $MentalCapacityService;
    }

    public function index(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $home_id = explode(',', Auth::user()->home_id)[0];
        $data = Auth::user()->user_type == 'M'
                ? $request->except('client_id')
                : $request->all();
        $data['home_id'] = $home_id;
        $mentalCapacityList = $this->MentalCapacityService->list($data);
        return response()->json(['success'=>true,'data'=>$mentalCapacityList]);
    }
    public function details(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:client_mental_capacities,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }
        $mentalCapacity = $this->MentalCapacityService->details($request->id);
        return response()->json(['success'=>true,'data'=>$mentalCapacity]);
    }
    public function mental_capacity_delete(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:client_mental_capacities,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }
        $this->MentalCapacityService->delete($request->id);
        return response()->json(['success'=>true,'message'=>'Deleted successfully']);
    }
}
