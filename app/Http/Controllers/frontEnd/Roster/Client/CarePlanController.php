<?php

namespace App\Http\Controllers\frontEnd\Roster\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Auth,Session;
use App\Services\Client\ClientCarePlanService;

class CarePlanController extends Controller
{
    protected $clientCarePlanService;
    
    public function __construct(ClientCarePlanService $clientCarePlanService)
    {
        $this->clientCarePlanService = $clientCarePlanService;
    }
    public function index(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $home_id = explode(',', Auth::user()->home_id)[0];
        $data = Auth::user()->user_type == 'M'
                ? $request->except('client_id')
                : $request->all();

        $data['home_id'] = $home_id;
        $carePlans = $this->clientCarePlanService->list($data);
        return response()->json(['success'=>true,'message','data'=>$carePlans]);
    }

    public function care_plan_save(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'care_setting'=>'required',
            'assessment_date'=>'required',
            'assessed_by'=>'required',
        ]);
        
        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()->first()
            ];
        }
        DB::beginTransaction();
        try {
            $home_id = explode(',', Auth::user()->home_id)[0];
            $requestData = $request->all();
            $requestData['home_id'] = $home_id;
            $requestData['user_id'] = Auth::user()->id;
            // echo "<pre>";print_r($requestData);die;
            $overview = $this->clientCarePlanService->store_overview($requestData);
            if(!empty($request->objectives)){
                $objectData = $request->objectives;
                for($i=0;$i<count($request->objectives);$i++){
                    $objectData[$i]['overview_id'] = $overview->id;
                    $objectData[$i]['home_id'] = $home_id;
                    $objectData[$i]['user_id'] = Auth::user()->id;
                    $objectData[$i]['client_id'] = $request->client_id;
                }
                $objective = $this->clientCarePlanService->store_objective($objectData);
            }
            if(!empty($request->tasks)){
                $taskData = $request->tasks;
                for($i=0;$i<count($request->tasks);$i++){
                    $taskData[$i]['overview_id'] = $overview->id;
                    $taskData[$i]['home_id'] = $home_id;
                    $taskData[$i]['user_id'] = Auth::user()->id;
                    $taskData[$i]['client_id'] = $request->client_id;
                }
                $task = $this->clientCarePlanService->store_task($taskData);
            }
            $requestData['overview_id'] = $overview->id;
            $pharmacy = $this->clientCarePlanService->store_pharmacy($requestData);
            if(!empty($request->medication)){
                $medicationData = $request->medication;
                for($i=0;$i<count($request->medication);$i++){
                    $medicationData[$i]['overview_id'] = $overview->id;
                    $medicationData[$i]['home_id'] = $home_id;
                    $medicationData[$i]['user_id'] = Auth::user()->id;
                    $medicationData[$i]['client_id'] = $request->client_id;
                    $medicationData[$i]['pharmacy_id'] = $pharmacy->id;
                }
                $medication = $this->clientCarePlanService->store_medication($medicationData);
            }
            if(!empty($request->preferences)){
                $preferencesData = $request->preferences;
                $preferencesData['home_id'] = $home_id;
                $preferencesData['user_id'] = Auth::user()->id;
                $preferencesData['client_id'] = $request->client_id;
                $preferencesData['overview_id'] = $overview->id;
                // echo "<pre>";print_r($preferencesData);die;
                $preferences = $this->clientCarePlanService->store_preferences($preferencesData);
            }
            $emergency = $this->clientCarePlanService->store_emergency($requestData);
            if(!empty($request->risk)){
                $riskData = $request->risk;
                for($i=0;$i<count($request->risk);$i++){
                    $riskData[$i]['overview_id'] = $overview->id;
                    $riskData[$i]['home_id'] = $home_id;
                    $riskData[$i]['user_id'] = Auth::user()->id;
                    $riskData[$i]['client_id'] = $request->client_id;
                    $riskData[$i]['pharmacy_id'] = $pharmacy->id;
                    $riskData[$i]['emergency_information_id'] = $emergency->id;
                }
                // echo "<pre>";print_r($riskData);die;
                $preferences = $this->clientCarePlanService->store_risk($riskData);
            }
            DB::commit();
            return response()->json(['success'=>true,'message'=>"Client Care Plan saved successfully",'data'=>$overview]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success'=>false,'message'=>"Something went wrong",'data'=>$e->getMessage()]);
        }
    }
    public function care_plan_delete(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $preferences = $this->clientCarePlanService->delete($request->id);
        Session::flash('success','Care plan deleted successfully done.');
        return response()->json(['success'=>true,'message'=>"Client Care Plan deleted successfully"]);
    }
    public function care_plan_details(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $carePlan = $this->clientCarePlanService->details($request->all());
        return response()->json(['success'=>true,'message'=>"Client Care Plan details",'data'=>$carePlan]);
    }
    public function objective_delete(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $objective = $this->clientCarePlanService->objective_delete($request->id);
        return response()->json(['success'=>true,'message'=>"Client Care Plan Objective delete",'data'=>$objective]);
    }
    public function task_delete(Request $request){
        $task = $this->clientCarePlanService->task_delete($request->id);
        return response()->json(['success'=>true,'message'=>"Client Care Plan Objective delete",'data'=>$task]);
    }
    public function medical_delete(Request $request){
        $medical = $this->clientCarePlanService->medical_delete($request->id);
        return response()->json(['success'=>true,'message'=>"Client Care Plan Objective delete",'data'=>$medical]);
    }
    public function risk_delete(Request $request){
        $risk = $this->clientCarePlanService->risk_delete($request->id);
        return response()->json(['success'=>true,'message'=>"Client Care Plan Objective delete",'data'=>$risk]);
    }
}
