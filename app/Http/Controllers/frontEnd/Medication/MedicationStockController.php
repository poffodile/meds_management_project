<?php

namespace App\Http\Controllers\frontEnd\Medication;

use App\Http\Controllers\Controller;
use App\Models\MARSheet;
use App\Models\MedicationStockBatch;
use App\Models\MedicationStockOrder;
use App\Models\MedicationStockTransaction;
use App\ServiceUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class MedicationStockController extends Controller
{
    use \App\Http\Controllers\frontEnd\Concerns\ResolvesCurrentHome;

    private const ALLOWED_USER_TYPES = ['N', 'M', 'A', 'CM', 'O'];

    /**
     * Manager-level roles that may CHANGE stock. Mirrors the canonical mapping in
     * HandleInertiaRequests (everyone else — i.e. 'N' — is a carer). The Stock page
     * already hides the write controls for carers (`canAdjust = role === 'manager'`);
     * this enforces the same rule on the SERVER so it can't be bypassed by posting the
     * request directly (review Stock C-1 / HAZ-27). Read access stays open to all
     * medication roles (ALLOWED_USER_TYPES) so carers can still view stock.
     */
    private const MANAGER_USER_TYPES = ['M', 'CM', 'A', 'O'];

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (! Auth::check() || ! in_array(Auth::user()->user_type, self::ALLOWED_USER_TYPES, true)) {
                abort(403, 'You do not have access to medication management.');
            }

            return $next($request);
        });
    }

    /**
     * The home currently in view — the manager's *selected* home, re-validated against
     * the homes they may access, not blindly the first one (review Stock I-4). Shared
     * with the Round via ResolvesCurrentHome so every read and stock write hits the home
     * the manager is actually looking at.
     */
    private function getHomeId(): int
    {
        return $this->currentHomeId();
    }

    /**
     * Guard a stock-write action: only a manager-level role may change stock. Aborts 403
     * otherwise. Enforces server-side what the Stock UI already assumes (review Stock C-1).
     */
    private function requireManager(): void
    {
        if (! in_array(Auth::user()->user_type, self::MANAGER_USER_TYPES, true)) {
            abort(403, 'Only a manager can change stock.');
        }
    }

    public function index(Request $request)
    {
        $homeId = $this->getHomeId();

        $sheets = MARSheet::forHome($homeId)
            ->active()
            ->currentlyActive()
            ->orderBy('medication_name')
            ->get();

        $residentNames = ServiceUser::whereIn('id', $sheets->pluck('client_id')->unique())
            ->pluck('name', 'id');

        // Low / out-of-stock / expired alerts.
        $today = now()->startOfDay();
        $alerts = $sheets->filter(function ($s) use ($today) {
            $lowOrOut = ! is_null($s->stock_level) && ! is_null($s->reorder_level) && $s->stock_level <= $s->reorder_level;
            $expired = $s->expiry_date && $s->expiry_date->lt($today);

            return $lowOrOut || $expired;
        });

        $transactions = MedicationStockTransaction::forHome($homeId)
            ->with('performedByUser:id,name')
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit(150)
            ->get();

        return view('frontEnd.medication.stock.index', [
            'sheets' => $sheets,
            'residentNames' => $residentNames,
            'alerts' => $alerts,
            'transactions' => $transactions,
        ]);
    }

    public function adjust(Request $request)
    {
        $error = $this->runAdjustment($request);

        return redirect()->route('medication.stock.index')
            ->with($error ? 'error' : 'success', $error ?? 'Stock updated.');
    }

    /** Same adjustment, but returns to the React/Inertia stock page. */
    public function adjustReact(Request $request)
    {
        $error = $this->runAdjustment($request);

        return redirect()->route('medication.stock.react')
            ->with($error ? 'error' : 'success', $error ?? 'Stock updated.');
    }

    /**
     * Validate and apply a stock adjustment.
     * Returns an error message, or null on success. Shared by the legacy + React pages.
     */
    private function runAdjustment(Request $request): ?string
    {
        $request->validate([
            'mar_sheet_id' => 'required|integer',
            'transaction_type' => 'required|in:received,disposed,returned,correction',
            'quantity' => 'nullable|numeric|min:0',
            'expiry_date' => 'nullable|date',
            'is_controlled' => 'nullable|boolean',
            'cd_schedule' => 'nullable|string|max:50',
            'reason' => 'nullable|string|max:255',
            'disposal_method' => 'nullable|string|max:255',
            'witness_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'batch_number' => 'nullable|string|max:100',
            'supplier' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|max:100',
        ]);

        $homeId = $this->getHomeId();
        $sheet = MARSheet::forHome($homeId)->active()->find($request->input('mar_sheet_id'));

        if (! $sheet) {
            return 'Medication not found.';
        }

        // Update tracked details (expiry + controlled-drug flag + barcode) on the medication.
        if ($request->filled('expiry_date')) {
            $sheet->expiry_date = $request->input('expiry_date');
        }
        if ($request->has('barcode') && Schema::hasColumn('mar_sheets', 'barcode')) {
            $sheet->barcode = $request->input('barcode') ?: null;
        }
        $sheet->is_controlled = $request->boolean('is_controlled');
        $sheet->cd_schedule = $request->boolean('is_controlled') ? $request->input('cd_schedule') : null;
        $sheet->save();

        // Apply a stock movement only when a quantity was entered (blank = details-only update).
        if ($request->filled('quantity')) {
            $resident = ServiceUser::where('id', $sheet->client_id)->first();
            MedicationStockTransaction::apply(
                $sheet,
                $request->input('transaction_type'),
                (float) $request->input('quantity'),
                (int) Auth::id(),
                [
                    'client_name' => $resident->name ?? null,
                    'reason' => $request->input('reason'),
                    'disposal_method' => $request->input('disposal_method'),
                    'witness_name' => $request->input('witness_name'),
                    'notes' => $request->input('notes'),
                ]
            );

            // Batch tracking (#30) — booking stock IN creates a batch (its own expiry/lot).
            if ($request->input('transaction_type') === 'received' && Schema::hasTable('medication_stock_batches')) {
                MedicationStockBatch::create([
                    'home_id' => $homeId,
                    'mar_sheet_id' => $sheet->id,
                    'client_id' => $sheet->client_id,
                    'batch_number' => $request->input('batch_number'),
                    'quantity' => (float) $request->input('quantity'),
                    'received_quantity' => (float) $request->input('quantity'),
                    'expiry_date' => $request->filled('expiry_date') ? $request->input('expiry_date') : null,
                    'supplier' => $request->input('supplier'),
                    'performed_by_user_id' => (int) Auth::id(),
                    'received_at' => now(),
                ]);
            }
        }

        return null;
    }

    /**
     * React/Inertia + Mantine pilot of the stock overview.
     * Same data as index(), shaped into plain arrays for the React page.
     */
    public function indexReact(Request $request)
    {
        return Inertia::render('Medication/Stock', $this->stockReactData());
    }

    /**
     * Sandbox copy of the React stock page — same data, separate component
     * so changes can be trialled without touching the live page.
     */
    public function indexReactLab(Request $request)
    {
        return Inertia::render('Medication/StockLab', $this->stockReactData());
    }

    /** Second sandbox copy of the React stock page. */
    public function indexReactLab2(Request $request)
    {
        return Inertia::render('Medication/StockLab2', $this->stockReactData());
    }

    /**
     * "Meds Stock 4.1" — warm/editorial stock page styled to match Medication
     * Round 4 (serif headings, stock-health donut, alerts + activity rails).
     * Same shared payload as the other React stock pages.
     */
    public function indexMedsStock41(Request $request)
    {
        return Inertia::render('Medication/MedsStock41', $this->stockReactData());
    }

    /** Stock adjustment that returns to the Meds Stock 4.1 page. */
    public function adjustMedsStock41(Request $request)
    {
        $error = $this->runAdjustment($request);

        return redirect()->route('medication.stock.v41')
            ->with($error ? 'error' : 'success', $error ?? 'Stock updated.');
    }

    /** Stock overview rendered in the frontend2 (CLINIK) shell. */
    public function indexFrontend2(Request $request)
    {
        return Inertia::render('Frontend2/Stock', $this->stockReactData());
    }

    /** Medication 2 → Stock. Same data helper as indexFrontend2; fresh view. */
    public function indexMedication2(Request $request)
    {
        return Inertia::render('Frontend2/Medication2/Stock', $this->stockReactData());
    }

    /** Stock adjustment that returns to the frontend2 stock page. */
    public function adjustFrontend2(Request $request)
    {
        $error = $this->runAdjustment($request);

        return redirect()->route('frontend2.stock')
            ->with($error ? 'error' : 'success', $error ?? 'Stock updated.');
    }

    /** "Stock 2" — redesigned copy (Missed-doses design language). Same data + adjust logic. */
    public function indexFrontend2Stock2(Request $request)
    {
        return Inertia::render('Frontend2/Stock2', $this->stockReactData());
    }

    public function adjustFrontend2Stock2(Request $request)
    {
        $this->requireManager();
        $error = $this->runAdjustment($request);

        return redirect()->route('frontend2.stock-2')
            ->with($error ? 'error' : 'success', $error ?? 'Stock updated.');
    }

    /**
     * Stock count / reconciliation (#31) — apply physical-count corrections in bulk.
     * Each changed item posts a `correction` transaction (absolute recount).
     * Controlled drugs require a witness.
     */
    public function reconcileFrontend2Stock2(Request $request)
    {
        $this->requireManager();
        $request->validate([
            'counts' => 'required|array|min:1',
            'counts.*.mar_sheet_id' => 'required|integer',
            'counts.*.counted' => 'required|numeric|min:0',
            'counts.*.reason' => 'nullable|string|max:255',
            'counts.*.witness_name' => 'nullable|string|max:255',
        ]);

        $homeId = $this->getHomeId();
        $applied = 0;
        $skipped = 0;

        foreach ($request->input('counts') as $row) {
            $sheet = MARSheet::forHome($homeId)->active()->find($row['mar_sheet_id']);
            if (! $sheet) {
                continue;
            }

            $counted = (float) $row['counted'];
            $current = (float) ($sheet->stock_level ?? 0);
            if ($counted === $current) {
                continue; // no discrepancy, nothing to post
            }

            // Controlled drugs must be witnessed.
            if ($sheet->is_controlled && empty($row['witness_name'])) {
                $skipped++;
                continue;
            }

            $resident = ServiceUser::where('id', $sheet->client_id)->first();
            MedicationStockTransaction::apply(
                $sheet,
                'correction',
                $counted,
                (int) Auth::id(),
                [
                    'client_name' => $resident->name ?? null,
                    'reason' => $row['reason'] ?: 'Stock count',
                    'witness_name' => $row['witness_name'] ?? null,
                    'notes' => 'Reconciled via stock count (was '.$current.', counted '.$counted.').',
                ]
            );
            $applied++;
        }

        $msg = $applied ? "{$applied} item".($applied === 1 ? '' : 's').' reconciled.' : 'No changes applied.';
        if ($skipped) {
            $msg .= " {$skipped} controlled-drug item".($skipped === 1 ? '' : 's').' skipped (missing witness).';
        }

        return redirect()->route('frontend2.stock-2')
            ->with($skipped && ! $applied ? 'error' : 'success', $msg);
    }

    /**
     * Build the React stock payload (meds, transactions, stats) shared by the
     * live page and the lab copy.
     */
    private function stockReactData(): array
    {
        $homeId = $this->getHomeId();

        $sheets = MARSheet::forHome($homeId)
            ->active()
            ->currentlyActive()
            ->orderBy('medication_name')
            ->get();

        $residentNames = ServiceUser::whereIn('id', $sheets->pluck('client_id')->unique())
            ->pluck('name', 'id');

        $today = now()->startOfDay();

        // Batch / lot tracking (#30) — live batches per sheet, FEFO-ordered.
        // Guarded so the page still works before the migration is run.
        $batchesBySheet = collect();
        if (Schema::hasTable('medication_stock_batches')) {
            $batchesBySheet = MedicationStockBatch::forHome($homeId)
                ->where('is_depleted', false)
                ->where('quantity', '>', 0)
                ->orderByRaw('expiry_date IS NULL, expiry_date ASC')
                ->orderBy('id')
                ->get()
                ->groupBy('mar_sheet_id');
        }

        $meds = $sheets->map(function ($s) use ($residentNames, $today, $batchesBySheet) {
            $low = ! is_null($s->stock_level) && ! is_null($s->reorder_level) && $s->stock_level <= $s->reorder_level;
            $expired = $s->expiry_date && $s->expiry_date->lt($today);
            $expiringSoon = $s->expiry_date && ! $expired && $s->expiry_date->lte($today->copy()->addDays(30));

            $sheetBatches = $batchesBySheet[$s->id] ?? collect();

            return [
                'id' => $s->id,
                'medication_name' => $s->medication_name,
                'client_id' => $s->client_id,
                'resident' => $residentNames[$s->client_id] ?? null,
                'stock_level' => $s->stock_level,
                'reorder_level' => $s->reorder_level,
                'unit' => $s->unit ?? null,
                'expiry_date' => $s->expiry_date ? $s->expiry_date->format('d M Y') : null,
                'is_controlled' => (bool) $s->is_controlled,
                'cd_schedule' => $s->cd_schedule,
                'barcode' => $s->barcode,
                'low' => $low,
                'expired' => $expired,
                'expiring_soon' => $expiringSoon,
                'batches' => $sheetBatches->map(fn ($b) => [
                    'id' => $b->id,
                    'batch_number' => $b->batch_number,
                    'quantity' => $b->quantity,
                    'expiry_date' => $b->expiry_date ? $b->expiry_date->format('d M Y') : null,
                    'supplier' => $b->supplier,
                ])->values(),
            ];
        })->values();

        $transactions = MedicationStockTransaction::forHome($homeId)
            ->with('performedByUser:id,name')
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit(150)
            ->get()
            ->map(function ($t) {
                return [
                    'id' => $t->id,
                    'date' => $t->transaction_date ? $t->transaction_date->format('d M Y H:i') : null,
                    'type' => $t->transaction_type,
                    'medication_name' => $t->medication_name,
                    'quantity' => $t->quantity,
                    'balance_after' => $t->balance_after,
                    'unit' => $t->unit,
                    'reason' => $t->reason,
                    'notes' => $t->notes,
                    'performed_by' => $t->performedByUser->name ?? null,
                ];
            });

        $stats = [
            'total' => $meds->count(),
            'low' => $meds->where('low', true)->count(),
            'expired' => $meds->where('expired', true)->count(),
            'out_of_stock' => $meds->filter(fn ($m) => $m['stock_level'] !== null && (float) $m['stock_level'] == 0)->count(),
            'expiring_soon' => $meds->where('expiring_soon', true)->count(),
            'controlled' => $meds->where('is_controlled', true)->count(),
        ];

        // Reorder → ordering (#32) — open orders for the home (guarded pre-migration).
        $orders = [];
        if (Schema::hasTable('medication_stock_orders')) {
            $orders = MedicationStockOrder::forHome($homeId)->open()
                ->orderByDesc('ordered_at')->orderByDesc('id')->get()
                ->map(fn ($o) => [
                    'id' => $o->id,
                    'mar_sheet_id' => $o->mar_sheet_id,
                    'medication_name' => $o->medication_name,
                    'quantity' => $o->quantity,
                    'supplier' => $o->supplier,
                    'ordered_at' => $o->ordered_at ? $o->ordered_at->format('d M Y') : null,
                    'resident' => $residentNames[$o->client_id] ?? null,
                ])->values();
        }

        // Residents that have medications on this home's stock — for client-first pickers (#37).
        $residents = $residentNames
            ->map(fn ($name, $id) => ['id' => (int) $id, 'name' => $name])
            ->sortBy('name')
            ->values();

        return [
            'meds' => $meds,
            'transactions' => $transactions,
            'stats' => $stats,
            'orders' => $orders,
            'residents' => $residents,
        ];
    }

    /** Raise a reorder line for a low medicine (#32). */
    public function createOrderFrontend2Stock2(Request $request)
    {
        $this->requireManager();
        $request->validate([
            'mar_sheet_id' => 'required|integer',
            'quantity' => 'required|numeric|min:1',
            'supplier' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $homeId = $this->getHomeId();
        $sheet = MARSheet::forHome($homeId)->active()->find($request->input('mar_sheet_id'));

        if (! $sheet) {
            // Throw, don't redirect-with-error: a 302+flash reads as success in Inertia and
            // the page would falsely toast "Order placed" (review Stock I-3 / same as Round CR-07).
            throw ValidationException::withMessages(['mar_sheet_id' => 'That medication could not be found for your home.']);
        }

        MedicationStockOrder::create([
            'home_id' => $homeId,
            'mar_sheet_id' => $sheet->id,
            'client_id' => $sheet->client_id,
            'medication_name' => $sheet->medication_name,
            'quantity' => (float) $request->input('quantity'),
            'supplier' => $request->input('supplier'),
            'status' => 'ordered',
            'notes' => $request->input('notes'),
            'created_by_user_id' => (int) Auth::id(),
            'ordered_at' => now(),
        ]);

        return redirect()->route('frontend2.stock-2')->with('success', 'Order placed.');
    }

    /** Receive an open order — books the stock in (creates a batch) and closes the order. */
    public function receiveOrderFrontend2Stock2(Request $request)
    {
        $this->requireManager();
        $request->validate([
            'order_id' => 'required|integer',
            'quantity' => 'required|numeric|min:0',
            'expiry_date' => 'nullable|date',
            'batch_number' => 'nullable|string|max:100',
        ]);

        $homeId = $this->getHomeId();
        $order = MedicationStockOrder::forHome($homeId)->open()->find($request->input('order_id'));

        if (! $order) {
            // Throw so the page shows the failure instead of silently toasting success (Stock I-3).
            throw ValidationException::withMessages(['order_id' => 'That order was not found or has already been closed.']);
        }

        $sheet = MARSheet::forHome($homeId)->active()->find($order->mar_sheet_id);
        if (! $sheet) {
            throw ValidationException::withMessages(['order_id' => 'The medication for that order could not be found.']);
        }

        $qty = (float) $request->input('quantity');
        if ($qty > 0) {
            $resident = ServiceUser::where('id', $sheet->client_id)->first();
            MedicationStockTransaction::apply($sheet, 'received', $qty, (int) Auth::id(), [
                'client_name' => $resident->name ?? null,
                'reason' => 'Order received',
                'notes' => 'Booked in against order #'.$order->id,
            ]);

            if (Schema::hasTable('medication_stock_batches')) {
                MedicationStockBatch::create([
                    'home_id' => $homeId,
                    'mar_sheet_id' => $sheet->id,
                    'client_id' => $sheet->client_id,
                    'batch_number' => $request->input('batch_number'),
                    'quantity' => $qty,
                    'received_quantity' => $qty,
                    'expiry_date' => $request->filled('expiry_date') ? $request->input('expiry_date') : null,
                    'supplier' => $order->supplier,
                    'performed_by_user_id' => (int) Auth::id(),
                    'received_at' => now(),
                ]);
            }
        }

        $order->status = 'received';
        $order->received_quantity = $qty;
        $order->received_at = now();
        $order->save();

        return redirect()->route('frontend2.stock-2')->with('success', 'Order received and booked in.');
    }

    /** Cancel an open order. */
    public function cancelOrderFrontend2Stock2(Request $request)
    {
        $this->requireManager();
        $request->validate(['order_id' => 'required|integer']);
        $homeId = $this->getHomeId();
        $order = MedicationStockOrder::forHome($homeId)->open()->find($request->input('order_id'));
        if (! $order) {
            // Was: silently report "cancelled" even when nothing was found (Stock I-3).
            throw ValidationException::withMessages(['order_id' => 'That order was not found or has already been closed.']);
        }
        $order->status = 'cancelled';
        $order->save();

        return redirect()->route('frontend2.stock-2')->with('success', 'Order cancelled.');
    }
}
