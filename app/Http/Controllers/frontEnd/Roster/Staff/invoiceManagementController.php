<?php

namespace App\Http\Controllers\frontEnd\Roster\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice\Invoice;
use App\Models\Invoice\InvoiceProduct;
use App\ServiceUser;
use App\Models\Customer;
use App\Models\Timesheet;
use App\Models\ScheduledShift;
use App\Services\Invoice\InvoiceService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class invoiceManagementController extends Controller
{
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function downloadPdf(Request $request, $id)
    {
        $invoice = Invoice::with(['serviceUser', 'invoiceProducts'])->find($id);
        if (!$invoice) return abort(404);

        $home = \App\Home::find($invoice->home_id);

        $data = [
            'invoice' => $invoice,
            'home' => $home,
            'customer' => $invoice->serviceUser,
            'products' => $invoice->invoiceProducts,
            'onboardingDetails' => \App\Models\OnboardingDetail::where('client_id', $invoice->customer_id)->get(),
            'download_type' => $request->type ?? 'self'
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('frontEnd.roster.payroll_finance.invoice_management.pdf', $data);
        return $pdf->download('Invoice-' . $invoice->invoice_ref . ($request->type ? '-' . $request->type : '') . '.pdf');
    }

    public function index(Request $request)
    {
        $home_id = Auth::user()->home_id;
        $query = Invoice::with('serviceUser')
            ->where('home_id', $home_id)
            ->whereNull('deleted_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_ref', 'like', "%$search%")
                    ->orWhereHas('serviceUser', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%$search%");
                    });
            });
        }

        $invoices = $query->orderBy('created_at', 'desc')->get();

        $clients = ServiceUser::where(['home_id' => $home_id, 'is_deleted' => 0])->get();
        $payers = $clients; // All payers are now clients (service users)

        // Summary stats always based on all non-deleted invoices for this home
        $allInvoices = Invoice::where('home_id', $home_id)->whereNull('deleted_at')->get();
        $totalInvoiced = $allInvoices->sum('Total');
        $outstanding = $allInvoices->whereIn('status', ['Draft', 'Invoiced', 'Outstanding'])->sum('outstanding');
        $paid = $allInvoices->where('status', 'Paid')->sum('Total');
        $overdueCount = $allInvoices->where('status', 'Outstanding')
            ->where('due_date', '<', now()->format('Y-m-d'))
            ->count();

        return view('frontEnd.roster.payroll_finance.invoice_management.index', compact(
            'invoices',
            'clients',
            'payers',
            'totalInvoiced',
            'outstanding',
            'paid',
            'overdueCount'
        ));
    }

    public function createInvoice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        try {
            $home_id = Auth::user()->home_id;

            // 1. Overlap Check: Block if any part of the requested period is already invoiced
            $overlap = $this->invoiceService->checkForOverlap($request->client_id, $request->start_date, $request->end_date);

            if ($overlap) {
                return response()->json(['success' => false, 'message' => "An invoice (Ref: {$overlap['ref']}) already exists covering the period {$overlap['period']}."]);
            }

            // The service now handles checking the billing_type from service_user table
            $invoice = $this->invoiceService->generateInvoiceForClientPeriod(
                $request->client_id,
                $request->start_date,
                $request->end_date,
                $home_id,
                $request->period_type ?? null // Service will fallback to client's preferred type
            );

            if ($invoice) {
                return response()->json(['success' => true, 'message' => 'Invoice created successfully.']);
            } else {
                return response()->json(['success' => false, 'message' => 'Could not create invoice (Duplicate or Error).']);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function updateInvoice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'invoice_date' => 'required',
            'due_date' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }
        $id = $request->id;
        $invoice = Invoice::find($id);
        if (!$invoice) {
            return response()->json(['success' => false, 'message' => 'Invoice not found.']);
        }

        $invoice->update([
            'invoice_date' => $request->invoice_date,
            'due_date' => $request->due_date,
        ]);

        if (!empty($invoice->customer_id)) {
            $this->invoiceService->regenerateInvoiceAmount($invoice->id);
        }

        return response()->json(['success' => true, 'message' => 'Invoice updated and recalculated successfully.']);
    }

    public function regenerateInvoice(Request $request)
    {
        $id = $request->id;
        $success = $this->invoiceService->regenerateInvoiceAmount($id);

        if ($success) {
            return response()->json(['success' => true, 'message' => 'Invoice amount recalculated and breakdown updated.']);
        } else {
            return response()->json(['success' => false, 'message' => 'Failed to recalculate invoice. It might not be in Draft status.']);
        }
    }

    public function updateInvoiceStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        $invoice = Invoice::find($request->id);
        $invoice->status = $request->status;

        if ($request->status == 'Paid') {
            $invoice->outstanding = 0;
        }

        $invoice->save();

        return response()->json(['success' => true, 'message' => 'Invoice status updated successfully.']);
    }

    public function getClientBillingInfo(Request $request)
    {
        $client = ServiceUser::find($request->id);
        if (!$client) return response()->json(['success' => false]);

        return response()->json([
            'success' => true,
            'billing_frequency' => $client->billing_frequency == 2 ? 'monthly' : 'weekly',
            'billing_rate' => $client->billing_rate
        ]);
    }

    public function bulkGenerate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        try {
            $home_id = Auth::user()->home_id;
            $count = $this->invoiceService->generateBatchInvoices($home_id, $request->start_date, $request->end_date);

            return response()->json([
                'success' => true,
                'message' => "Successfully generated $count invoices for clients with completed shifts."
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function getInvoiceDetails(Request $request)
    {
        $id = $request->id;
        $invoice = Invoice::find($id);
        if (!$invoice) return response()->json(['success' => false]);

        $products = \App\Models\Invoice\InvoiceProduct::where('invoice_id', $id)->get();
        $onboardingDetails = \App\Models\OnboardingDetail::where('client_id', $invoice->customer_id)->get();

        return response()->json([
            'success' => true,
            'invoice' => $invoice,
            'products' => $products,
            'onboardingDetails' => $onboardingDetails
        ]);
    }
}
