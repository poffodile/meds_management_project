<?php

namespace App\Http\Controllers\frontEnd\Roster\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Client\RiskAssessmentService;
use Auth,Validator;

class RiskAssessmentController extends Controller
{
    protected $RiskAssessmentService;

    public function __construct(RiskAssessmentService $RiskAssessmentService)
    {
        $this->RiskAssessmentService = $RiskAssessmentService;
    }
    public function index(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $home_id = explode(',', Auth::user()->home_id)[0];
        $data = Auth::user()->user_type == 'M'
                ? $request->except('client_id')
                : $request->all();

        $data['home_id'] = $home_id;
        $risk_assesment_list = $this->RiskAssessmentService->list($data);
        return response()->json(['success'=>true,'message'=>"Risk Assessment List",'data'=>$risk_assesment_list]);
    }
    public function risk_assessment_delete(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:client_risk_assessments,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        try {
            $risk_assesment_list = $this->RiskAssessmentService->delete($request->id);
            return response()->json(['success' => true, 'message' => "Risk Assessment deleted successfully"]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => "Something went wrong: " . $e->getMessage()]);
        }
    }
    public function risk_assessment_details(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:client_risk_assessments,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }
        $riskassessmentdetails = $this->RiskAssessmentService->details($request->id);
        return response()->json(['success' => true, 'message' => "Risk Assessment details",'data'=>$riskassessmentdetails]);
    }
}
