<?php

namespace App\Http\Controllers\frontEnd\Medication;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\ControlledDrugRegister;
use App\Models\MARSheet;
use App\ServiceUser;
use Inertia\Inertia;

class ControlledDrugRegisterController extends Controller
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

    /** Resolve the carer's primary home (matches the other medication screens). */
    private function getHomeId(): int
    {
        return (int) explode(',', Auth::user()->home_id)[0];
    }

    public function index(Request $request)
    {
        $request->validate([
            'client_id' => 'nullable|integer',
            'q'         => 'nullable|string|max:255',
        ]);

        $homeId   = $this->getHomeId();
        $clientId = $request->input('client_id');
        $q        = $request->input('q');

        $entries = ControlledDrugRegister::forHome($homeId)
            ->with('createdByUser:id,name')
            ->when($clientId, fn($query) => $query->where('client_id', $clientId))
            ->when($q, fn($query) => $query->where('medication_name', 'like', '%' . $q . '%'))
            ->orderByDesc('entry_date')
            ->orderByDesc('entry_time')
            ->orderByDesc('id')
            ->get();

        // Residents for the filter + the Add-Entry form.
        $residents = ServiceUser::where('home_id', $homeId)
            ->where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name']);

        // Each resident's active MAR meds, for the drug picker (free-type fallback handled in the view).
        $medsByClient = MARSheet::forHome($homeId)
            ->active()
            ->currentlyActive()
            ->orderBy('medication_name')
            ->get(['id', 'client_id', 'medication_name'])
            ->groupBy('client_id')
            ->map(fn($g) => $g->map(fn($m) => ['id' => $m->id, 'name' => $m->medication_name])->values());

        // Latest running balance per resident+drug, to auto-fill "balance before".
        $lastBalances = [];
        ControlledDrugRegister::forHome($homeId)
            ->orderByDesc('id')
            ->get(['client_id', 'medication_name', 'balance_after'])
            ->each(function ($e) use (&$lastBalances) {
                $key = $e->client_id . '|' . $e->medication_name;
                if (!array_key_exists($key, $lastBalances)) {
                    $lastBalances[$key] = $e->balance_after;
                }
            });

        return view('frontEnd.medication.controlled_drugs.index', [
            'entries'      => $entries,
            'residents'    => $residents,
            'medsByClient' => $medsByClient,
            'lastBalances' => $lastBalances,
            'filterClient' => $clientId,
            'filterQ'      => $q,
        ]);
    }

    /** React/Inertia version of the register. Same data, shaped into plain arrays. */
    public function indexReact(Request $request)
    {
        $homeId = $this->getHomeId();

        $entries = ControlledDrugRegister::forHome($homeId)
            ->with('createdByUser:id,name')
            ->orderByDesc('entry_date')
            ->orderByDesc('entry_time')
            ->orderByDesc('id')
            ->limit(300)
            ->get()
            ->map(fn ($e) => [
                'id'              => $e->id,
                'entry_date'      => $e->entry_date ? \Carbon\Carbon::parse($e->entry_date)->format('d M Y') : null,
                'entry_time'      => $e->entry_time,
                'client_name'     => $e->client_name,
                'medication_name' => $e->medication_name,
                'cd_schedule'     => $e->cd_schedule,
                'action_type'     => $e->action_type,
                'dose_quantity'   => $e->dose_quantity,
                'unit'            => $e->unit,
                'balance_after'   => $e->balance_after,
                'witness_name'    => $e->witness_name,
                'created_by'      => $e->createdByUser->name ?? null,
            ]);

        $residents = ServiceUser::where('home_id', $homeId)
            ->where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($r) => ['id' => $r->id, 'name' => $r->name]);

        $medsByClient = MARSheet::forHome($homeId)
            ->active()
            ->currentlyActive()
            ->orderBy('medication_name')
            ->get(['id', 'client_id', 'medication_name'])
            ->groupBy('client_id')
            ->map(fn ($g) => $g->map(fn ($m) => ['id' => $m->id, 'name' => $m->medication_name])->values());

        $lastBalances = [];
        ControlledDrugRegister::forHome($homeId)
            ->orderByDesc('id')
            ->get(['client_id', 'medication_name', 'balance_after'])
            ->each(function ($e) use (&$lastBalances) {
                $key = $e->client_id . '|' . $e->medication_name;
                if (!array_key_exists($key, $lastBalances)) {
                    $lastBalances[$key] = $e->balance_after;
                }
            });

        return Inertia::render('Medication/ControlledDrugs', [
            'entries'      => $entries,
            'residents'    => $residents,
            'medsByClient' => $medsByClient,
            'lastBalances' => $lastBalances,
        ]);
    }

    public function store(Request $request)
    {
        $this->createEntry($request);

        return redirect()->route('medication.controlled-drugs.index')
            ->with('success', 'Controlled drug register entry added.');
    }

    /** Same create, but returns to the React/Inertia page. */
    public function storeReact(Request $request)
    {
        $this->createEntry($request);

        return redirect()->route('medication.controlled-drugs.react')
            ->with('success', 'Controlled drug register entry added.');
    }

    /** Validate + create a register entry. Shared by the legacy + React pages. */
    private function createEntry(Request $request): void
    {
        $request->validate([
            'client_id'       => 'required|integer',
            'mar_sheet_id'    => 'nullable|integer',
            'medication_name' => 'required|string|max:255',
            'cd_schedule'     => 'nullable|string|max:50',
            'action_type'     => 'required|in:administered,received,disposed,returned,adjustment',
            'entry_date'      => 'required|date',
            'entry_time'      => 'required',
            'dose_quantity'   => 'nullable|numeric|min:0',
            'unit'            => 'nullable|string|max:50',
            'balance_before'  => 'nullable|numeric',
            'balance_after'   => 'required|numeric',
            'witness_name'    => 'required|string|max:255',
            'notes'           => 'nullable|string',
        ]);

        $homeId   = $this->getHomeId();
        $resident = ServiceUser::where('id', $request->input('client_id'))
            ->where('home_id', $homeId)
            ->first();

        ControlledDrugRegister::create([
            'home_id'            => $homeId,
            'client_id'          => $request->input('client_id'),
            'client_name'        => $resident->name ?? null,
            'mar_sheet_id'       => $request->input('mar_sheet_id') ?: null,
            'medication_name'    => $request->input('medication_name'),
            'cd_schedule'        => $request->input('cd_schedule'),
            'action_type'        => $request->input('action_type'),
            'entry_date'         => $request->input('entry_date'),
            'entry_time'         => $request->input('entry_time'),
            'dose_quantity'      => $request->input('dose_quantity'),
            'unit'               => $request->input('unit'),
            'balance_before'     => $request->input('balance_before'),
            'balance_after'      => $request->input('balance_after'),
            'witness_name'       => $request->input('witness_name'),
            'notes'              => $request->input('notes'),
            'created_by_user_id' => Auth::id(),
        ]);
    }
}
