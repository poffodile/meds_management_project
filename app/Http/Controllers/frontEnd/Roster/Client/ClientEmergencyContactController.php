<?php

namespace App\Http\Controllers\frontend\Roster\Client;

use App\Http\Controllers\Controller;
use App\Services\Client\EmergencyContactService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClientEmergencyContactController extends Controller
{
    protected EmergencyContactService $emergencyService;

    public function __construct(EmergencyContactService $emergencyService)
    {
        $this->emergencyService = $emergencyService;
    }
    public function index(Request $req)
    {
        $homeIds = explode(',', auth()->user()->home_id);
        $homeId  = $homeIds[0] ?? null;
        $reqData = $req->all();
        $reqData['home_id'] = $homeId;
        $type = $req->type;
        $subQuery = $this->emergencyService->list($reqData);
        if ($type == 'edit') {
            $arrr =  $subQuery->get();
        } else {
            $data =  $subQuery->paginate(15);
            $pagination =    [
                'total' => $data->total(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
                'total_pages' => $data->lastPage(),
            ];
            $nexPage = $data->nextPageUrl();
            $arrr = $data->items();
        }
        return response()->json([
            'status' => true,
            'data' => $arrr,
            'next_page' => $nexPage ?? null,
            'pagination' => $pagination ?? null,
        ]);
    }
    public function create(Request $req)
    {
        try {
            // return $req;
            $validator = Validator::make($req->all(), [
                'emergency_contact_id' => 'nullable|array',
                'client_id' => 'required|exists:service_user,id',
                'emergency_full_name' => 'required|array',
                'emergency_full_name.*' => 'required|string|max:255',

                'emergency_phone_number' => 'required|array',
                'emergency_phone_number.*' => ['required', 'regex:/^[0-9]{7,15}$/'],

                'emergency_relation' => 'required|array',
                'emergency_relation.*' => 'required|string|max:100',
            ],   [
                'emergency_full_name.*.required' => 'Full name is required.',
                'emergency_phone_number.*.required' => 'Phone number is required.',
                'emergency_phone_number.*.regex' => 'Enter a valid phone number.',
                'emergency_relation.*.required' => 'Relationship is required.',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()], 422);
            }


            $homeIds = explode(',', auth()->user()->home_id);
            $homeId  = $homeIds[0] ?? null;
            $reqData = $req->all();
            $reqData['home_id'] = $homeId;
            $reqData['user_id'] = auth()->id();
            // return $reqData;
            $data = $this->emergencyService->store($reqData);
            if (!$data) {
                return response()->json(['status' => false, 'message' => 'Contact not saved'], 422);
            }
            return response()->json(['status' => true, 'message' => 'Contact added successfully']);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function delete(Request $req)
    {
        try {
            $data = $this->emergencyService->delete($req->id);
            if (!$data) {
                return response()->json([
                    'status' => false,
                    'message' => 'Contact not deleted'
                ], 422);
            }
            return response()->json([
                'status' => true,
                'message' => 'Contact deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
