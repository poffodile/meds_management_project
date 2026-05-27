<?php

namespace App\Http\Controllers\frontEnd\Roster\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Auth,DB,Session;
use Illuminate\Support\Facades\Validator;
use App\Services\Client\ClientDolsService;

class DolsController extends Controller
{
    protected $ClientDolsService;

    public function __construct(ClientDolsService $ClientDolsService)
    {
        $this->ClientDolsService = $ClientDolsService;
    }

    public function index(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $home_id = explode(',', Auth::user()->home_id)[0];
        $data = Auth::user()->user_type == 'M'
                ? $request->except('client_id')
                : $request->all();

        $data['home_id'] = $home_id;
        $dols = $this->ClientDolsService->list($data);
        return response()->json(['success'=>true,'message','data'=>$dols]);
    }

    public function save_dols(Request $request){
        // echo "<pre>";print_r($request->all());die;
        if(!empty($request->dols_id)){
            $validator = Validator::make($request->all(), [
                'dols_id'=>'required|exists:dols,id',
                'dols_status'=>'required',
            ]);
        }else{
            $validator = Validator::make($request->all(), [
                'dols_status'=>'required',
            ]);
        }
        
        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()->first()
            ];
        }
        try {
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id = $ex_home_ids[0];
            $requestData = $request->all();
            $requestData['home_id'] = $home_id;
            $requestData['user_id'] = Auth::user()->id;
            $requestData['id'] = $request->dols_id;
            // echo "<pre>";print_r($requestData);die;
            $clientDols = $this->ClientDolsService->store($requestData);
            // Session::flash('success','Client Care Dols save successfully done.');
            return response()->json(['success'=>true,'message'=>"Client Care Dols saved successfully",'data'=>$clientDols]);

        } catch (Exception $e) {
            return response()->json(['success'=>false,'message'=>"Something went wrong",'data'=>$e->getMessage()]);
        }
    }
}
