<?php

namespace App\Http\Controllers\frontEnd\Roster\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth,Validator;
use App\Services\Client\PeepService;

class ClientPeepController extends Controller
{
    protected $PeepService;
    public function __construct(PeepService $PeepService)    {
        $this->PeepService = $PeepService;
    }
    public function index(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $home_id = explode(',', Auth::user()->home_id)[0];
        $data = Auth::user()->user_type == 'M'
                ? $request->except('client_id')
                : $request->all();
        $data['home_id'] = $home_id;
        $peepList = $this->PeepService->list($data);
        return response()->json(['success'=>true,'data'=>$peepList]);
    }

    public function details(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:client_peeps,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }
        $peepDetails = $this->PeepService->details($request->id);
        return response()->json(['success'=>true,'data'=>$peepDetails]);
    }
    public function peep_delete(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:client_peeps,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }
        $this->PeepService->delete($request->id);
        return response()->json(['success'=>true,'message'=>'Deleted successfully']);
    }
}
