<?php

namespace App\Http\Controllers\frontEnd\Medication;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\MARSheet;
use App\Models\MedicationStockTransaction;
use App\ServiceUser;

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
            return redirect()->route('medication.stock.index')->with('error', 'Medication not found.');
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

        return redirect()->route('medication.stock.index')->with('success', 'Stock updated.');
    }
}
