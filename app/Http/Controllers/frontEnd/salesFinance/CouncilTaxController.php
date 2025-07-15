<?php

namespace App\Http\Controllers\frontend\salesFinance;

use App\Http\Controllers\Controller;

use App\Models\CouncilTax;
use Illuminate\Support\Carbon;
use App\Http\Requests\CouncilTaxRequests;
use App\Services\Finance\CouncilTaxService;

class CouncilTaxController extends Controller
{

    public function index()
    {
        $data['councilTaxs'] = CouncilTax::whereNull('deleted_at')->get();
        return view('frontEnd/salesAndFinance/council_tax/council_tax', $data);
    }

    public function saveCouncilTaxData(CouncilTaxRequests $req)
    {
        // dd($req->all());
        $data = $req->validated();
        $last_bill_date = Carbon::createFromFormat('d-m-Y', $data['last_bill_date'])->format('Y-m-d');
        $data['last_bill_date'] = $last_bill_date;
        $bill_period_start_date = Carbon::createFromFormat('d-m-Y', $data['bill_period_start_date'])->format('Y-m-d');
        $data['bill_period_start_date'] = $bill_period_start_date;
        $bill_period_end_date = Carbon::createFromFormat('d-m-Y', $data['bill_period_end_date'])->format('Y-m-d');
        $data['bill_period_end_date'] = $bill_period_end_date;


        $response = CouncilTax::updateOrCreate(['id' => $req->council_tax_id], $data );

        if ($response->wasRecentlyCreated) {
            return response()->json(['success' => true, 'message' => 'Council Tax record created successfully!', 'data' => $response], 201);
        } elseif ($response->wasChanged()) {
            return response()->json(['success' => true, 'message' => 'Council Tax record updated successfully!', 'data' => $response], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'No changes made.', 'data' => $response], 200);
        }
    }

    public function destroy($id)
    {
        $affected = CouncilTax::where('id', $id)->update(['deleted_at' => Carbon::now()]);
        if ($affected) {
            return response()->json(['message' => 'Deleted successfully']);
        } else {
            return response()->json(['message' => 'Record not found or already deleted'], 404);
        }
    }
}

