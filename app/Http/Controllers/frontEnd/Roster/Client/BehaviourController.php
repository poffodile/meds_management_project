<?php

namespace App\Http\Controllers\frontEnd\Roster\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Client\BehaviourPlanService;
use Auth,Validator;

class BehaviourController extends Controller
{
    protected $BehaviourPlanService;
    public function __construct(BehaviourPlanService $BehaviourPlanService)    {
        $this->BehaviourPlanService = $BehaviourPlanService;
    }
    public function index(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $home_id = explode(',', Auth::user()->home_id)[0];
        $data = Auth::user()->user_type == 'M'
                ? $request->except('client_id')
                : $request->all();
        $data['home_id'] = $home_id;
        $behaviourList = $this->BehaviourPlanService->list($data);
        return response()->json(['success'=>true,'data'=>$behaviourList]);
    }

    public function details(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:client_behavior_support_plans,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }
        $behaviourDetails = $this->BehaviourPlanService->details($request->id);
        return response()->json(['success'=>true,'data'=>$behaviourDetails]);
    }
    public function behaviour_delete(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:client_behavior_support_plans,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }
        $this->BehaviourPlanService->delete($request->id);
        return response()->json(['success'=>true,'message'=>'Deleted successfully']);
    }

}
