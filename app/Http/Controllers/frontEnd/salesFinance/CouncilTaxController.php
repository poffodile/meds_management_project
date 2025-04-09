<?php

namespace App\Http\Controllers\frontend\salesFinance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\CouncilTax;
use Illuminate\Support\Carbon;

use App\Http\Requests\CouncilTaxRequests;

class CouncilTaxController extends Controller
{
    
    public function index(){    
        $data['councilTaxs'] = CouncilTax::whereNull('deleted_at')->get();
        // dd($data);   
        return view('frontEnd/salesAndFinance/council_tax/council_tax', $data);
    }

    public function saveCouncilTaxData(CouncilTaxRequests $req){
        // dd($req);

        $data = CouncilTax::updateOrCreate(
            ['id' => $req->council_tax_id], 
            $req->validated() 
        );

        if ($data->wasRecentlyCreated) {
            return response()->json([  'success' => true, 'message' => 'Council Tax record created successfully!', 'data' => $data], 201);
        } elseif ($data->wasChanged()) {
            return response()->json([  'success' => true, 'message' => 'Council Tax record updated successfully!', 'data' => $data], 200);
        } else {
            return response()->json([  'success' => false, 'message' => 'No changes made.', 'data' => $data], 200);
        }

    }   

    public function destroy($id){
        $affected =CouncilTax::where('id', $id)->update(['deleted_at' => Carbon::now()]);
        if ($affected) {
            return response()->json(['message' => 'Deleted successfully']);
        } else {
            return response()->json(['message' => 'Record not found or already deleted'], 404);
        }
    }
}
