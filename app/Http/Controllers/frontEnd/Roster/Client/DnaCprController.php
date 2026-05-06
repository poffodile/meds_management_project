<?php

namespace App\Http\Controllers\frontEnd\Roster\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Client\DnaCprService;
use Auth, Validator;

class DnaCprController extends Controller
{
    protected $dnaCprService;
    public function __construct(DnaCprService $dnaCprService)
    {
        $this->dnaCprService = $dnaCprService;
    }
    public function index(Request $request){
        $home_id = explode(',', Auth::user()->home_id)[0];
        $data = Auth::user()->user_type == 'M'
                ? $request->except('client_id')
                : $request->all();
        $data['home_id'] = $home_id;
        $dncpr = $this->dnaCprService->list($data);
        return ['success' => true, 'message' => 'Do Not Attempt CPR record list', 'data' => $dncpr];
    }
    public function dna_cpr_save(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:service_user,id',
            'status' => 'required|string',
            'decision_date' => 'required|date',
            'decision_made_by' => 'required|string',
            'clinical_reasons' => 'required|string',
        ]);
        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()->first()
            ];
        }
        try {
            $home_id = explode(',', Auth::user()->home_id)[0];
            $requestData = $request->all();
            $requestData['home_id'] = $home_id;
            $requestData['user_id'] = Auth::user()->id;
            $requestData['discussion_held_check'] = $request->has('discussion_held_check') ? 1 : 0;
            $requestData['involved_check'] = $request->has('involved_check') ? 1 : 0;
            $requestData['emergency_services_check'] = $request->has('emergency_services_check') ? 1 : 0;
            $requestData['care_plan_updated_check'] = $request->has('care_plan_updated_check') ? 1 : 0;
            $requestData['all_staff_briefed_check'] = $request->has('all_staff_briefed_check') ? 1 : 0;
            $requestData['gp_notified_check'] = $request->has('gp_notified_check') ? 1 : 0;
            $dncpr = $this->dnaCprService->store($requestData);
            return ['success' => true, 'message' => 'Do Not Attempt CPR record saved successfully.', 'data' => $dncpr];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Failed to save Do Not Attempt CPR record.', 'errors' => $e->getMessage()];
        }
    }
    public function dna_cpr_details(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:do_not_attempt_cprs,id',
        ]);
        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()->first()
            ];
        }
        $id = $request->id;
        $dncpr = $this->dnaCprService->details($id);
        if($dncpr){
            return ['success' => true, 'message' => 'Do Not Attempt CPR record details', 'data' => $dncpr];
        }else{
            return ['success' => false, 'message' => 'Do Not Attempt CPR record not found'];
        }
    }
    public function dna_cpr_delete(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:do_not_attempt_cprs,id',
        ]);
        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()->first()
            ];
        }
        $id = $request->id;
        $dncpr = $this->dnaCprService->delete($id);
        if($dncpr){
            return ['success' => true, 'message' => 'Do Not Attempt CPR record deleted successfully.'];
        }else{
            return ['success' => false, 'message' => 'Do Not Attempt CPR record not found'];
        }
    }
}
