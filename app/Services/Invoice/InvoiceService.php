<?php

namespace App\Services\Invoice;

use App\Models\Invoice\Invoice;
use Illuminate\Support\Carbon;

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
            'invoice_ref' => $this->generateInvoiceRef(),
            'invoice_type' => 1,
            'invoice_date' => Carbon::now()->format('Y-m-d'),
            'payment_terms' => 14,
            'due_date' => Carbon::now()->addDays(14)->format('Y-m-d'),
            'status' => 'Draft',
            'sub_total' => $request->sub_total ?? 0,
            'deposit_percentage' => 0,
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

        // 1. Overlap Check: Prevent invoices with overlapping periods
        $overlap = $this->checkForOverlap($clientId, $start, $end);
        if ($overlap) return null;

        // Fetch client billing settings
        $billingFrequency = $client->billing_frequency; // 1 = Weekly, 2 = Monthly
        $billingRate = floatval($client->billing_rate ?? 0);

        // If periodType wasn't explicitly provided, use client's preference
        if (empty($periodType)) {
            $periodType = ($billingFrequency == 2) ? 'monthly' : 'weekly';
        }

        // 1. Calculate base amount
        $baseAmount = 0;
        $totalHours = 0;

        if ($timesheets && $timesheets->count() > 0) {
            foreach ($timesheets as $ts) {
                if ($ts->clock_in && $ts->clock_out) {
                    $s = Carbon::parse($ts->clock_in);
                    $e = Carbon::parse($ts->clock_out);
                    if ($e->lessThan($s)) $e->addDay();
                    
                    $hours = $s->diffInMinutes($e) / 60;
                    $totalHours += $hours;

                    $rate = 0;
                    if ($ts->shift && $ts->shift->hourly_rate > 0) {
                        $rate = $ts->shift->hourly_rate;
                    } else {
                        $rate = $billingRate;
                    }
                    $baseAmount += ($hours * $rate);
                }
            }
        } else {
            $baseAmount = $billingRate;
        }

        // 3. Fetch Unbilled Expenses for the client
        $expenses = \App\Models\ServiceUserManagement\ServiceUserExpense::where('service_user_id', $clientId)
            ->whereNull('invoice_id')
            ->whereBetween('expense_date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->get();
        $totalExpenses = $expenses->sum('amount');

        $finalAmount = $baseAmount + $totalExpenses;
        if ($finalAmount < 0) $finalAmount = 0;

        $vatRate = 0.20;
        $vatAmount = $finalAmount * $vatRate;
        $totalWithVat = $finalAmount + $vatAmount;

        // 4. Create Invoice
        $invoice = Invoice::create([
            'home_id' => $home_id,
            'customer_id' => $clientId,
            'invoice_ref' => $this->generateInvoiceRef(),
            'invoice_date' => now()->format('Y-m-d'),
            'payment_terms' => 14,
            'due_date' => now()->addDays(14)->format('Y-m-d'),
            'sub_total' => $finalAmount,
            'deposit_percentage' => 0,
            'VAT_id' => 0,
            'VAT_amount' => $vatAmount,
            'Total' => $totalWithVat,
            'outstanding' => $totalWithVat,
            'status' => 'Draft',
            'invoice_type' => 1,
        ]);

        // Mark expenses as invoiced
        foreach ($expenses as $expense) {
            $expense->invoice_id = $invoice->id;
            $expense->save();
        }

        // 5. Create Line Item for Base Care Services
        $periodStr = $start->format('d M') . ' to ' . $end->format('d M Y');
        
        \App\Models\Invoice\InvoiceProduct::create([
            'home_id' => $home_id,
            'invoice_id' => $invoice->id,
            'customer_id' => 0,
            'product_id' => 0,
            'description' => ucfirst($periodType) . " Care Services for " . $client->name . " ($periodStr). Total hours: " . number_format($totalHours, 1),
            'qty' => 1,
            'price' => $baseAmount,
            'discount' => 0,
            'discount_type' => 'fixed',
            'vat_id' => 0,
            'vat' => 20,
        ]);

        // 6. Create Line Items for each Expense
        foreach ($expenses as $expense) {
            \App\Models\Invoice\InvoiceProduct::create([
                'home_id' => $home_id,
                'invoice_id' => $invoice->id,
                'customer_id' => 0,
                'product_id' => 0,
                'description' => "Expense: " . $expense->title . " (" . Carbon::parse($expense->expense_date)->format('d M Y') . ")",
                'qty' => 1,
                'price' => $expense->amount,
                'discount' => 0,
                'discount_type' => 'fixed',
                'vat_id' => 0,
                'vat' => 20,
            ]);
        }

        return $invoice;
    }

    /**
     * Regenerates amount for an existing invoice based on current client settings.
     */
    public function regenerateInvoiceAmount($invoiceId)
    {
        $invoice = Invoice::find($invoiceId);
        if (!$invoice || $invoice->status != 'Draft') return false;

        $clientId = $invoice->customer_id; 
        $client = \App\ServiceUser::find($clientId);
        if (!$client) return false;

        $billingRate = floatval($client->billing_rate ?? 0);

        // Fetch Unbilled Expenses for the client
        $totalExpenses = \App\Models\ServiceUserManagement\ServiceUserExpense::where('invoice_id', $invoiceId)->sum('amount');

        $finalAmount = $billingRate + $totalExpenses;
        if ($finalAmount < 0) $finalAmount = 0;

        $vatRate = 0.20;
        $vatAmount = $finalAmount * $vatRate;
        $totalWithVat = $finalAmount + $vatAmount;

        $invoice->update([
            'sub_total' => $finalAmount,
            'Total' => $totalWithVat,
            'outstanding' => $totalWithVat,
            'VAT_amount' => $vatAmount,
        ]);

        // 1. Update Care Services line item
        $careProduct = \App\Models\Invoice\InvoiceProduct::where('invoice_id', $invoiceId)
            ->where(function($q) {
                $q->where('description', 'like', '%Care Services%');
            })
            ->first();

        if ($careProduct) {
            $careProduct->update(['price' => $billingRate, 'vat' => 20]);
        }

        // 1b. Update any other line items (like expenses) to have 20% VAT
        \App\Models\Invoice\InvoiceProduct::where('invoice_id', $invoiceId)
            ->update(['vat' => 20]);

        // 2. Remove any existing Service Charge line items
        \App\Models\Invoice\InvoiceProduct::where('invoice_id', $invoiceId)
            ->where('description', 'like', 'Service Charge: %')
            ->delete();

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

    /**
     * Checks if a requested period overlaps with any existing invoices for a client.
     */
    public function checkForOverlap($clientId, $start, $end)
    {
        $start = Carbon::parse($start)->startOfDay();
        $end = Carbon::parse($end)->endOfDay();

        $invoices = Invoice::where('customer_id', $clientId)
            ->whereIn('status', ['Draft', 'Invoiced', 'Outstanding', 'Paid'])
            ->with(['invoiceProducts' => function($q) {
                $q->where('description', 'like', '% Care Services %');
            }])
            ->get();

        foreach ($invoices as $invoice) {
            foreach ($invoice->invoiceProducts as $product) {
                // Match "DD MMM to DD MMM YYYY" pattern
                if (preg_match('/(\d{2} [A-Z][a-z]{2}) to (\d{2} [A-Z][a-z]{2} (\d{4}))/', $product->description, $matches)) {
                    try {
                        $year = $matches[3];
                        $invEnd = Carbon::parse($matches[2])->endOfDay();
                        $invStart = Carbon::parse($matches[1] . " " . $year)->startOfDay();
                        
                        // Handle potential year wrap
                        if ($invStart->gt($invEnd)) {
                            $invStart->subYear();
                        }

                        // Overlap condition: max(start1, start2) <= min(end1, end2)
                        if ($start->lte($invEnd) && $end->gte($invStart)) {
                            return [
                                'ref' => $invoice->invoice_ref,
                                'period' => $matches[1] . " to " . $matches[2]
                            ];
                        }
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
        }
        return false;
    }
}
