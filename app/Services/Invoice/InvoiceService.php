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
            ->whereHas('shift', function ($q) use ($start, $end) {
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

        // Duplicate Check: Look for existing invoice for this client within the same period 
        // to prevent double billing (checking customer_ref which stores clientId)
        $existing = Invoice::where([
            'home_id' => $home_id,
            'customer_ref' => $clientId,
        ])
        ->whereIn('status', ['Draft', 'Invoiced', 'Outstanding', 'Paid'])
        ->where('invoice_date', now()->format('Y-m-d')) // Basic check for same generation day
        ->first();

        // Optional: More advanced check based on period string in product description could be added
        // but for now, we'll block multiple generations for the same client on the same day.
        if ($existing) return null;

        // Fetch client billing settings
        $billingFrequency = $client->billing_frequency; // 1 = Weekly, 2 = Monthly
        $billingRate = floatval($client->billing_rate ?? 0);

        // If periodType wasn't explicitly provided, use client's preference
        if (empty($periodType)) {
            $periodType = ($billingFrequency == 2) ? 'monthly' : 'weekly';
        }

        // 1. Calculate base amount
        $baseAmount = $billingRate;

        // 2. Calculate deductions from onboarding_details
        $onboardingDetails = \App\Models\OnboardingDetail::where('client_id', $clientId)->get();
        $totalDeductions = 0;
        foreach ($onboardingDetails as $detail) {
            if ($detail->type == 1) { // Percentage
                $totalDeductions += ($billingRate * floatval($detail->vat) / 100);
            } else { // Amount
                $totalDeductions += floatval($detail->vat);
            }
        }

        $finalAmount = $baseAmount - $totalDeductions;
        if ($finalAmount < 0) $finalAmount = 0;

        // 4. Create Invoice
        $invoice = Invoice::create([
            'home_id' => $home_id,
            'customer_id' => 0, // Decoupled from legacy customers table
            'customer_ref' => $clientId, // Direct link to service_user table
            'invoice_ref' => $this->generateInvoiceRef(),
            'invoice_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(14)->format('Y-m-d'),
            'sub_total' => $finalAmount,
            'VAT_id' => 0,
            'VAT_amount' => 0,
            'Total' => $finalAmount,
            'outstanding' => $finalAmount,
            'status' => 'Draft',
            'project_id' => 0,
            'site_delivery_add_id' => 0,
            'invoice_type' => 1,
            'deposit_percentage' => 0,
        ]);

        // 5. Create Product Line Item
        $periodStr = $start->format('d M') . ' to ' . $end->format('d M Y');
        $totalHours = 0;
        if ($timesheets) {
            foreach ($timesheets as $ts) {
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
            'customer_id' => 0, // Decoupled
            'product_id' => 0,
            'description' => ucfirst($periodType) . " Care Services for " . $client->name . " ($periodStr). Total shifts hours: " . number_format($totalHours, 1),
            'qty' => 1,
            'price' => $finalAmount,
            'discount' => 0,
            'discount_type' => 'fixed',
            'vat_id' => 0,
            'vat' => 0,
        ]);

        return $invoice;
    }

    /**
     * Regenerates amount for an existing invoice based on current client settings.
     */
    public function regenerateInvoiceAmount($invoiceId)
    {
        $invoice = Invoice::find($invoiceId);
        if (!$invoice || $invoice->status != 'Draft') return false;

        $clientId = $invoice->customer_ref; // We stored client_id here in generateInvoiceForClientPeriod
        $client = \App\ServiceUser::find($clientId);
        if (!$client) return false;

        $billingRate = floatval($client->billing_rate ?? 0);
        $onboardingDetails = \App\Models\OnboardingDetail::where('client_id', $clientId)->get();

        $totalDeductions = 0;
        foreach ($onboardingDetails as $detail) {
            if ($detail->type == 1) { // Percentage
                $totalDeductions += ($billingRate * floatval($detail->vat) / 100);
            } else { // Amount
                $totalDeductions += floatval($detail->vat);
            }
        }

        $finalAmount = $billingRate - $totalDeductions;
        if ($finalAmount < 0) $finalAmount = 0;

        $invoice->update([
            'sub_total' => $finalAmount,
            'Total' => $finalAmount,
            'outstanding' => $finalAmount
        ]);

        // Update main product line item too
        $product = \App\Models\Invoice\InvoiceProduct::where('invoice_id', $invoice->id)->first();
        if ($product) {
            $product->update(['price' => $finalAmount]);
        }

        return true;
    }

    /**
     * Bulk generate invoices for all clients who have completed shifts.
     */
    public function generateBatchInvoices($home_id, $start_date, $end_date)
    {
        $start = Carbon::parse($start_date);
        $end = Carbon::parse($end_date);

        // 1. Find all processed timesheets for this period
        $timesheets = \App\Models\Timesheet::where('home_id', $home_id)
            ->where('status', 'processed')
            ->whereHas('shift', function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start->format('Y-m-d'), $end->format('Y-m-d')]);
            })
            ->with(['shift'])
            ->get();

        if ($timesheets->isEmpty()) return 0;

        $clientTimesheets = $timesheets->groupBy('shift.service_user_id');
        $count = 0;

        foreach ($clientTimesheets as $clientId => $items) {
            if (!$clientId) continue;
            
            // Check if client has billing rate set
            $client = \App\ServiceUser::find($clientId);
            if (!$client || !$client->billing_rate) continue;

            $invoice = $this->generateInvoiceForClientPeriod(
                $clientId, 
                $start, 
                $end, 
                $home_id, 
                null, // PeriodType handled by client preference
                $items
            );

            if ($invoice) $count++;
        }

        return $count;
    }
}
