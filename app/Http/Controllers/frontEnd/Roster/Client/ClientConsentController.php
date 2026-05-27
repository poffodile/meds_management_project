<?php

namespace App\Http\Controllers\frontEnd\Roster\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Auth, Validator;
use App\Services\Client\ConsentService;

class ClientConsentController extends Controller
{
    protected $consentService;

    public function __construct(ConsentService $consentService)
    {
        $this->consentService = $consentService;
    }

    public function index(Request $request)
    {
        try {
            $home_id = explode(',', Auth::user()->home_id)[0];
            $data = Auth::user()->user_type == 'M'
                ? $request->except('client_id')
                : $request->all();
            $data['home_id'] = $home_id;           
            $consent = $this->consentService->list($data);
            return response()->json(['success' => true, 'message' => 'Client Consent record list', 'data' => $consent]);
        } catch (\Exception $e) {
            Log::error('Error fetching Client Consent list:', [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['success' => false, 'message' => 'An error occurred while fetching the consent list.', 'errors' => $e->getMessage()]);
        }
    }
    public function save_consent(Request $request)
    {
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:service_user,id',
            'consent_type' => 'required|string',
            'consent_title' => 'required|string',
            'description' => 'required|string',
            'status' => 'required|string',
            'date_granted' => 'required|date',
            'granted_by' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()->first()]);
        }
        try {
            $home_id = explode(',', Auth::user()->home_id)[0];
            $requestData = $request->all();
            $requestData['home_id'] = $home_id;
            $requestData['user_id'] = Auth::user()->id;
            $consent = $this->consentService->store($requestData);
            return response()->json(['success' => true, 'message' => 'Client Consent record saved successfully.', 'data' => $consent]);
        } catch (\Exception $e) {
            Log::error('Error saving Client Consent record:', [
                'error' => $e->getMessage(),
                'data'  => $request->all()
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to save Client Consent record.', 'errors' => $e->getMessage()]);
        }
    }
    public function consent_status_change(Request $request)
    {
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:client_consents,id',
            'status' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()->first()]);
        }
        try {
            $consent = $this->consentService->changeStatus($request->id, $request->status);
            return response()->json(['success' => true, 'message' => 'Consent status updated successfully.', 'data' => $consent]);
        } catch (\Exception $e) {
            Log::error('Error changing consent status:', [
                'error' => $e->getMessage(),
                'data'  => $request->all()
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to change consent status.', 'errors' => $e->getMessage()]);
        }
    }
}
