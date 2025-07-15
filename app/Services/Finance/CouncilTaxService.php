<?php

namespace App\Services\finance;

use App\Models\CouncilTax;
use Illuminate\Support\Carbon;

class CouncilTaxService
{
    public function saveCouncilTaxData($data)
    {

        $last_bill_date = Carbon::createFromFormat('d-m-Y', $data['last_bill_date'])->format('Y-m-d');
        $data['last_bill_date'] = $last_bill_date;
        $bill_period_start_date = Carbon::createFromFormat('d-m-Y', $data['bill_period_start_date'])->format('Y-m-d');
        $data['bill_period_start_date'] = $bill_period_start_date;
        $bill_period_end_date = Carbon::createFromFormat('d-m-Y', $data['bill_period_end_date'])->format('Y-m-d');
        $data['bill_period_end_date'] = $bill_period_end_date;
    
        // Save or update the record
        $council =  CouncilTax::updateOrCreate(['id' => $data['council_tax_id']], $data);
        return $council;
    }

    public function getCouncilTax()
    {
       return CouncilTax::whereNull('deleted_at')->orderBy('created_at', 'desc')->get();
    }

    public function deleteCouncilTax($id)
    {
        return CouncilTax::where('id', $id)->update(['deleted_at' => Carbon::now()]);
        
    }

    public function getCouncilTaxById($id)
    {
        return CouncilTax::where('id', $id)->first();
    }
}

