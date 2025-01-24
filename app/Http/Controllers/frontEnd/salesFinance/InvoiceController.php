<?php

namespace App\Http\Controllers\frontEnd\salesFinance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Construction_account_code;
use App\Models\Construction_tax_rate;

class InvoiceController extends Controller
{
    public function account_codes(Request $request){
        $home_id = Auth::user()->home_id;
        $data['account_codes']=Construction_account_code::getAllAccount_Codes($home_id);
        $data['home_id']=$home_id;
        return view('frontEnd.salesAndFinance.jobs.account_code',$data);
    }

    public function save_account_code(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['vali_error' => $validator->errors()->first()]);
        }
        try {
            $data=Construction_account_code::saveAccount_Codes(array_merge(['home_id' => Auth::user()->home_id], $request->all()));
            return response()->json(['data' => $data,  'message' => $data ? "Account Code save succcessfully" : 'Account Code could not be added.']);
        } catch (\Exception $e) {
            Log::error('Error saving Tag: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to save Tag. Please try again.'], 500);
        }
    }

    public function tax_rate(Request $request){
        $mode=$request->mode;
        $home_id = Auth::user()->home_id;
        $data['tax_rate'] = Construction_tax_rate::getAllTax_rate($home_id,$mode);
        $data['home_id'] = $home_id;
        return view('frontEnd.salesAndFinance.jobs.tax_rate',$data);
    }

    public function save_tax_rate(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:construction_tax_rates,name|string',
            'tax_rate' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['vali_error' => $validator->errors()->first()]);
        }
        try{
            $data=Construction_tax_rate::saveTax_rate($request->all());
            if($request->id == ''){
                return response()->json(['success'=>true,'message'=>'Tax Rate added successfully done','data' => $data]);
            }else{
                return response()->json(['success'=>true,'message'=>'Tax Rate updated successfully done','data' => $data]);
            }
        }catch (\Exception $e) {
            Log::error('Error saving Tag: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
        
    }

    public function getAccountCode(){
        $data =  Construction_account_code::getAllAccount_Codes(Auth::user()->home_id);

        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? $data : 'No data.'
        ]);
    }

    public function getActiveAccountCode(){
        $data =  Construction_account_code::getActiveAccountCode(Auth::user()->home_id);

        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? $data : 'No data.'
        ]);
    }

    public function getActiveTaxRate(){
        $data = Construction_tax_rate::getAllTax_rate(Auth::user()->home_id, "Active");

        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? $data : 'No data.'
        ]);
    }

    public function getTaxRateOnTaxId(Request $request){

        $data = Construction_tax_rate::getTaxRateOnId($request->id);

        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? $data : 'No data.'
        ]);
    }


    

    
}
