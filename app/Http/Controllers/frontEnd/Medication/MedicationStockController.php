<?php

namespace App\Http\Controllers\frontEnd\Medication;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\MARSheet;
use App\Models\MedicationStockTransaction;
use App\ServiceUser;
use Inertia\Inertia;

class MedicationStockController extends Controller
{
    private const ALLOWED_USER_TYPES = ['N', 'M', 'A', 'CM', 'O'];

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::check() || !in_array(Auth::user()->user_type, self::ALLOWED_USER_TYPES, true)) {
                abort(403, 'You do not have access to medication management.');
            }
            return $next($request);
        });
    }

    private function getHomeId(): int
    {
        return (int) explode(',', Auth::user()->home_id)[0];
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
            $lowOrOut = !is_null($s->stock_level) && !is_null($s->reorder_level) && $s->stock_level <= $s->reorder_level;
            $expired  = $s->expiry_date && $s->expiry_date->lt($today);
            return $lowOrOut || $expired;
        });

        $transactions = MedicationStockTransaction::forHome($homeId)
            ->with('performedByUser:id,name')
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit(150)
            ->get();

        return view('frontEnd.medication.stock.index', [
            'sheets'        => $sheets,
            'residentNames' => $residentNames,
            'alerts'        => $alerts,
            'transactions'  => $transactions,
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
            'mar_sheet_id'     => 'required|integer',
            'transaction_type' => 'required|in:received,disposed,returned,correction',
            'quantity'         => 'nullable|numeric|min:0',
            'expiry_date'      => 'nullable|date',
            'is_controlled'    => 'nullable|boolean',
            'cd_schedule'      => 'nullable|string|max:50',
            'reason'           => 'nullable|string|max:255',
            'disposal_method'  => 'nullable|string|max:255',
            'witness_name'     => 'nullable|string|max:255',
            'notes'            => 'nullable|string',
        ]);

        $homeId = $this->getHomeId();
        $sheet  = MARSheet::forHome($homeId)->active()->find($request->input('mar_sheet_id'));

        if (!$sheet) {
            return 'Medication not found.';
        }

        // Update tracked details (expiry + controlled-drug flag) on the medication.
        if ($request->filled('expiry_date')) {
            $sheet->expiry_date = $request->input('expiry_date');
        }
        $sheet->is_controlled = $request->boolean('is_controlled');
        $sheet->cd_schedule   = $request->boolean('is_controlled') ? $request->input('cd_schedule') : null;
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
                    'client_name'     => $resident->name ?? null,
                    'reason'          => $request->input('reason'),
                    'disposal_method' => $request->input('disposal_method'),
                    'witness_name'    => $request->input('witness_name'),
                    'notes'           => $request->input('notes'),
                ]
            );
        }

        return null;
    }

    /**
     * React/Inertia + Mantine pilot of the stock overview.
     * Same data as index(), shaped into plain arrays for the React page.
     */
    public function indexReact(Request $request)
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

        $meds = $sheets->map(function ($s) use ($residentNames, $today) {
            $low = !is_null($s->stock_level) && !is_null($s->reorder_level) && $s->stock_level <= $s->reorder_level;
            $expired = $s->expiry_date && $s->expiry_date->lt($today);
            $expiringSoon = $s->expiry_date && !$expired && $s->expiry_date->lte($today->copy()->addDays(30));

            return [
                'id'             => $s->id,
                'medication_name' => $s->medication_name,
                'resident'       => $residentNames[$s->client_id] ?? null,
                'stock_level'    => $s->stock_level,
                'reorder_level'  => $s->reorder_level,
                'unit'           => $s->unit ?? null,
                'expiry_date'    => $s->expiry_date ? $s->expiry_date->format('d M Y') : null,
                'is_controlled'  => (bool) $s->is_controlled,
                'cd_schedule'    => $s->cd_schedule,
                'low'            => $low,
                'expired'        => $expired,
                'expiring_soon'  => $expiringSoon,
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
                    'id'              => $t->id,
                    'date'            => $t->transaction_date ? $t->transaction_date->format('d M Y H:i') : null,
                    'type'            => $t->transaction_type,
                    'medication_name' => $t->medication_name,
                    'quantity'        => $t->quantity,
                    'balance_after'   => $t->balance_after,
                    'unit'            => $t->unit,
                    'reason'          => $t->reason,
                    'performed_by'    => $t->performedByUser->name ?? null,
                ];
            });

        $stats = [
            'total'         => $meds->count(),
            'low'           => $meds->where('low', true)->count(),
            'expired'       => $meds->where('expired', true)->count(),
            'out_of_stock'  => $meds->filter(fn ($m) => $m['stock_level'] !== null && (float) $m['stock_level'] == 0)->count(),
            'expiring_soon' => $meds->where('expiring_soon', true)->count(),
            'controlled'    => $meds->where('is_controlled', true)->count(),
        ];

        return Inertia::render('Medication/Stock', [
            'meds'         => $meds,
            'transactions' => $transactions,
            'stats'        => $stats,
        ]);
    }
}
