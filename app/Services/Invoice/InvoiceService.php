<?php

namespace App\Services\Invoice;

use App\Models\Invoice\Invoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

// use App\Models\QuoteCallBack;



class InvoiceService
{

    public function generateInvoiceRef()
    {
        $lastInvoice = Invoice::orderBy('id', 'desc')->first();
        $nextId = $lastInvoice ? $lastInvoice->id + 1 : 1;
        return 'INV-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
    }

    public function saveInvoiceData($data, $request, $home_id){

        // dd($data);
        // print_r($data->project_id);
        // die;
    $invoice = [
        'home_id' => $home_id,
        'customer_id' =>  $request->customer_id, 
        'project_id' =>  $data->project_id,
        'site_delivery_add_id' =>  $data->site_delivery_add_id ?? null,
        'invoice_ref' => $this->generateInvoiceRef(),
        'invoice_type' => 1,
        'customer_ref' => $data->customer_ref ?? null,
        'invoice_date' =>  Carbon::createFromFormat('d/m/Y', $request->invoice_date)->format('Y-m-d'),
        'customer_job_ref' => $data->customer_job_ref ?? null,
        'purchase_order_ref' => $data->purchase_order_ref ?? null,
        'payment_terms' => $data->payment_terms ?? 21,
        'due_date' => Carbon::createFromFormat('d/m/Y', $request->due_date)->format('Y-m-d'),
        'status' => 'Draft',
        'deposit_percentage' => $request->deposit_percentage,
        'tags' => $data->tags ?? null,
        'customer_notes' => $data->customer_notes ?? null,    
        'terms' => $data->tearms ?? null,
        'internal_notes' => $data->internal_notes ?? null,
        'sub_total' => $request->sub_total ,
        'VAT_id'=> $request->VAT_id,
        'VAT_amount' => $request->VAT_amount,
        'Total' => $request->total,
        'outstanding' => $request->outstanding,
        'terms' => $request->terms ?? null
    ];

    return Invoice::create($invoice);
       
}
}
 