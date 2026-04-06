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

    public function saveInvoiceData($data, $request, $home_id)
    {
        $invoice = [
            'home_id' => $home_id,
            'customer_id' =>  $request->customer_id,
            'project_id' =>  $data->project_id ?? 0,
            'site_delivery_add_id' =>  $data->site_delivery_add_id ?? null,
            'invoice_ref' => $this->generateInvoiceRef(),
            'invoice_type' => 1,
            'customer_ref' => $data->customer_ref ?? null,
            'invoice_date' => Carbon::now()->format('Y-m-d'),
            'due_date' => Carbon::now()->addDays(14)->format('Y-m-d'),
            'status' => 'Draft',
            'deposit_percentage' => $request->deposit_percentage ?? 0,
            'sub_total' => $request->sub_total ?? 0,
            'VAT_id' => $request->VAT_id ?? 0,
            'VAT_amount' => $request->VAT_amount ?? 0,
            'Total' => $request->total ?? 0,
            'outstanding' => $request->total ?? 0,
        ];

        return Invoice::create($invoice);
    }

    /**
     * Automated generation triggered by Payroll.
     */
    public function generateInvoicesFromProcessedTimesheets($week_start, $home_id)
    {
        $start = Carbon::parse($week_start)->startOfWeek();
        $end = Carbon::parse($week_start)->endOfWeek();

        // Find timesheets processed for this home in this week
        $timesheets = \App\Models\Timesheet::where('home_id', $home_id)
            ->where('status', 'processed')
            ->whereHas('shift', function($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start->format('Y-m-d'), $end->format('Y-m-d')]);
            })
            ->with(['shift', 'staff'])
            ->get();

        $clientTimesheets = $timesheets->groupBy('shift.service_user_id');

        foreach ($clientTimesheets as $clientId => $items) {
            if (!$clientId) continue;
            // By default, payroll triggers a WEEKLY invoice
            $this->generateInvoiceForClientPeriod($clientId, $start, $end, $home_id, 'weekly', $items);
        }
    }

    /**
     * Core logic to generate an invoice for a specific period (weekly/monthly).
     */
    public function generateInvoiceForClientPeriod($clientId, $start, $end, $home_id, $periodType = 'weekly', $timesheets = null)
    {
        $client = \App\ServiceUser::find($clientId);
        if (!$client) return null;

        $start = Carbon::parse($start);
        $end = Carbon::parse($end);

        // 1. Find or Create Customer for this client
        $customer = $this->findOrCreateCustomerForClient($client, $home_id);

        // 2. Calculate amount based on weekly_rate
        $rate = floatval($client->weekly_rate ?? 1200);
        $amount = $rate;

        if ($periodType == 'monthly') {
            $amount = $rate * 4.33; // Approx monthly for flat rate care
        } else {
            // Weekly is the default behavior for payroll sync
            $amount = $rate;
        }

        // 3. Create Invoice
        $invoice = Invoice::create([
            'home_id' => $home_id,
            'customer_id' => $customer->id,
            'invoice_ref' => $this->generateInvoiceRef(),
            'invoice_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(14)->format('Y-m-d'),
            'sub_total' => $amount,
            'VAT_id' => 0,
            'VAT_amount' => 0,
            'Total' => $amount,
            'outstanding' => $amount,
            'status' => 'Draft',
            'project_id' => 0,
            'site_delivery_add_id' => 0,
            'invoice_type' => 1,
            'deposit_percentage' => 0,
        ]);

        // 4. Create Product Line Item
        $periodStr = $start->format('d M') . ' to ' . $end->format('d M Y');
        $totalHours = 0;
        if ($timesheets) {
            foreach($timesheets as $ts) {
                if ($ts->clock_in && $ts->clock_out) {
                    $s = Carbon::parse($ts->clock_in);
                    $e = Carbon::parse($ts->clock_out);
                    if ($e->lessThan($s)) $e->addDay();
                    $totalHours += $s->diffInMinutes($e) / 60;
                }
            }
        }

        \App\Models\Invoice\InvoiceProduct::create([
            'home_id' => $home_id,
            'invoice_id' => $invoice->id,
            'customer_id' => $customer->id,
            'product_id' => 0,
            'description' => ucfirst($periodType) . " Care Services for " . $client->name . " ($periodStr). Total shifts hours: " . number_format($totalHours, 1),
            'qty' => 1,
            'price' => $amount,
            'discount' => 0,
            'discount_type' => 'fixed',
            'vat_id' => 0,
            'vat' => 0,
        ]);

        return $invoice;
    }

    public function findOrCreateCustomerForClient($client, $home_id)
    {
        $customer = \App\Models\Customer::where('name', $client->name)->where('home_id', $home_id)->first();
        if (!$customer) {
            $customer = \App\Models\Customer::create([
                'home_id' => $home_id,
                'name' => $client->name,
                'contact_name' => $client->name,
                'email' => $client->email,
                'telephone' => $client->phone_no,
                'status' => 1,
                'is_converted' => 1
            ]);
        }
        return $customer;
    }
}
