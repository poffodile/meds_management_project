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

    public function downloadPdf($id)
    {
        $invoice = Invoice::with(['customers', 'invoiceProducts'])->find($id);
        if (!$invoice) return abort(404);

        $home = \App\Home::find($invoice->home_id);
        
        $data = [
            'invoice' => $invoice,
            'home' => $home,
            'customer' => $invoice->customers,
            'products' => $invoice->invoiceProducts
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('frontEnd.roster.payroll_finance.invoice_management.pdf', $data);
        return $pdf->download('Invoice-' . $invoice->invoice_ref . '.pdf');
    }

    public function index(Request $request)
    {
        $home_id = Auth::user()->home_id;
        $query = Invoice::with('customers')
            ->where('home_id', $home_id)
            ->whereNull('deleted_at');
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_ref', 'like', "%$search%")
                  ->orWhereHas('customers', function($q2) use ($search) {
                      $q2->where('name', 'like', "%$search%");
                  });
            });
        }

        $invoices = $query->orderBy('created_at', 'desc')->get();
        
        $clients = ServiceUser::where(['home_id' => $home_id, 'is_deleted' => 0])->get();
        $payers = Customer::where(['home_id' => $home_id, 'status' => 1])->get();

        // Summary stats always based on all non-deleted invoices for this home
        $allInvoices = Invoice::where('home_id', $home_id)->whereNull('deleted_at')->get();
        $totalInvoiced = $allInvoices->sum('Total');
        $outstanding = $allInvoices->whereIn('status', ['Draft', 'Invoiced', 'Outstanding'])->sum('outstanding');
        $paid = $allInvoices->where('status', 'Paid')->sum('Total');
        $overdueCount = $allInvoices->where('status', 'Outstanding')
            ->where('due_date', '<', now()->format('Y-m-d'))
            ->count();

        return view('frontEnd.roster.payroll_finance.invoice_management.index', compact(
            'invoices', 'clients', 'payers', 'totalInvoiced', 'outstanding', 'paid', 'overdueCount'
        ));
    }

    public function createInvoice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required',
            'period_type' => 'required|in:weekly,monthly',
            'start_date' => 'required',
            'end_date' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        try {
            $home_id = Auth::user()->home_id;
            
            $invoice = $this->invoiceService->generateInvoiceForClientPeriod(
                $request->client_id, 
                $request->start_date, 
                $request->end_date, 
                $home_id, 
                $request->period_type
            );

            if ($invoice) {
                return response()->json(['success' => true, 'message' => 'Invoice created successfully.']);
            } else {
                return response()->json(['success' => false, 'message' => 'Could not create invoice.']);
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

        $invoice = Invoice::find($request->id);
        
        // Status check logic
        if ($invoice->status != 'Draft') {
            return response()->json(['success' => false, 'message' => 'Cannot edit invoice in ' . $invoice->status . ' status.']);
        }

        $invoice->invoice_date = $request->invoice_date;
        $invoice->due_date = $request->due_date;
        $invoice->save();

        return response()->json(['success' => true, 'message' => 'Invoice details updated successfully.']);
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
}
