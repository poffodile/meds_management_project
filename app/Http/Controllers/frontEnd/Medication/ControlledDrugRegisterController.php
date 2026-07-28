<?php

namespace App\Http\Controllers\frontEnd\Medication;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use App\Models\ControlledDrugRegister;
use App\Models\MARSheet;
use App\ServiceUser;
use Inertia\Inertia;

class ControlledDrugRegisterController extends Controller
{
    use \App\Http\Controllers\frontEnd\Concerns\ResolvesCurrentHome;

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

    /**
     * The home currently in view — the manager's *selected* home, re-validated against
     * the homes they may access, not blindly the first one (review CD I-5). Shared with
     * the Round via ResolvesCurrentHome so all medication pages agree, and so a CD
     * movement is written against the home the manager is actually looking at.
     */
    private function getHomeId(): int
    {
        return $this->currentHomeId();
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
        return Inertia::render('Medication/ControlledDrugs', $this->cdReactData());
    }

    /**
     * "Controlled Drugs 4.1" — warm/editorial register styled to match Medication
     * Round 4 and Meds Stock 4.1 (serif headings, register-health donut, alerts +
     * activity rails). Same shared payload as the other React register page.
     */
    public function indexControlledDrugs41(Request $request)
    {
        return Inertia::render('Medication/ControlledDrugs41', $this->cdReactData());
    }

    /** Add a register entry, returning to the Controlled Drugs 4.1 page. */
    public function storeControlledDrugs41(Request $request)
    {
        $this->createEntry($request);

        return redirect()->route('medication.controlled-drugs.v41')
            ->with('success', 'Controlled drug register entry added.');
    }

    /** CD register rendered in the frontend2 (CLINIK) shell. */
    public function indexFrontend2(Request $request)
    {
        $data = $this->cdReactData();
        $data['home'] = \DB::table('home')->where('id', $this->getHomeId())->value('title');

        return Inertia::render('Frontend2/ControlledDrugs', $data);
    }

    /**
     * Medication 2 → Controlled drugs register. Builds a per-medicine append-only ledger
     * with the server-computed running balance — the witnessed CD register.
     */
    public function indexMedication2(Request $request)
    {
        $homeId = $this->getHomeId();

        // Every controlled drug currently prescribed in this home.
        $sheets = MARSheet::forHome($homeId)->active()->currentlyActive()
            ->where('is_controlled', 1)
            ->orderBy('medication_name')
            ->get();

        $names = ServiceUser::whereIn('id', $sheets->pluck('client_id')->unique())->pluck('name', 'id');

        // All register entries for these medicines, oldest first (the ledger reads down).
        $entries = ControlledDrugRegister::forHome($homeId)
            ->whereIn('mar_sheet_id', $sheets->pluck('id'))
            ->with('createdByUser:id,name')
            ->orderBy('entry_date')->orderBy('entry_time')->orderBy('id')
            ->get()
            ->groupBy('mar_sheet_id');

        $registers = $sheets->map(function ($s) use ($names, $entries) {
            $rows = ($entries->get($s->id) ?? collect())->map(fn ($e) => [
                'id' => $e->id,
                'date' => $e->entry_date ? \Carbon\Carbon::parse($e->entry_date)->format('d M Y') : null,
                'time' => $e->entry_time ? substr($e->entry_time, 0, 5) : null,
                'action' => $e->action_type,
                'quantity' => $e->dose_quantity,
                'balance_before' => $e->balance_before,
                'balance_after' => $e->balance_after,
                'witness' => $e->witness_name,
                'by' => $e->createdByUser->name ?? null,
                'notes' => $e->notes,
            ])->values();

            return [
                'mar_sheet_id' => $s->id,
                'client_id' => $s->client_id,
                'resident' => $names[$s->client_id] ?? ('Resident #'.$s->client_id),
                'medication_name' => $s->medication_name,
                'cd_schedule' => $s->cd_schedule,
                'unit' => $s->unit,
                // Current balance = last register entry, else the opening stock (the register
                // bootstraps from real stock on its first movement).
                'balance' => $rows->isNotEmpty() ? (float) $rows->last()['balance_after'] : (float) ($s->stock_level ?? 0),
                'has_entries' => $rows->isNotEmpty(),
                'entries' => $rows,
            ];
        })->values();

        return Inertia::render('Frontend2/Medication2/ControlledDrugs', [
            'registers' => $registers,
            'home' => \DB::table('home')->where('id', $homeId)->value('title'),
        ]);
    }

    /** Record a witnessed CD movement (received / disposed / returned / adjustment). */
    public function storeMedication2(Request $request)
    {
        $request->validate([
            'mar_sheet_id' => 'required|integer',
            'action_type' => 'required|in:received,disposed,returned,adjustment',
            'quantity' => 'required|numeric|min:0',
            'witness_name' => 'required|string|max:255',   // manual movements are always witnessed
            'notes' => 'nullable|string|max:2000',
        ]);

        $homeId = $this->getHomeId();
        $sheet = MARSheet::forHome($homeId)->active()->where('is_controlled', 1)->find($request->input('mar_sheet_id'));
        if (! $sheet) {
            return back()->with('error', 'That controlled drug could not be found for your home.');
        }

        $resident = ServiceUser::where('id', $sheet->client_id)->first();
        ControlledDrugRegister::record(
            $sheet,
            $request->input('action_type'),
            (float) $request->input('quantity'),
            (int) Auth::id(),
            $request->input('witness_name'),
            ['client_name' => $resident->name ?? null, 'notes' => $request->input('notes')]
        );

        return redirect()->route('frontend2.medication2.controlled-drugs')
            ->with('success', 'Register entry recorded.');
    }

    /** Add a register entry + return to the frontend2 register page. */
    public function storeFrontend2(Request $request)
    {
        $this->createEntry($request);

        return redirect()->route('frontend2.controlled-drugs')
            ->with('success', 'Controlled drug register entry added.');
    }

    /** Build the React register payload shared by the React + 4.1 pages. */
    private function cdReactData(): array
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
            ->get(['id', 'client_id', 'medication_name', 'stock_level', 'cd_schedule', 'dose', 'dosage'])
            ->groupBy('client_id')
            ->map(fn ($g) => $g->map(fn ($m) => [
                'id' => $m->id, 'name' => $m->medication_name, 'stock' => $m->stock_level,
                'cd_schedule' => $m->cd_schedule, 'dose' => $m->dose, 'dosage' => $m->dosage,
            ])->values());

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

        return [
            'entries'      => $entries,
            'residents'    => $residents,
            'medsByClient' => $medsByClient,
            'lastBalances' => $lastBalances,
        ];
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

    /**
     * Validate + create a register entry. Shared by the legacy + React pages.
     *
     * Balance integrity (review CD C-1 / HAZ-25): the running balance is NEVER taken from
     * the client. This routes through ControlledDrugRegister::record(), which reads the
     * previous entry's balance under a row lock and computes the new one itself — the same
     * safe path the Medication 2 page uses. The entry now hangs off a real prescription
     * (`mar_sheet_id` required), so medicine identity and opening stock come from the
     * record, not free text. The ONLY figure an operator legitimately supplies is the
     * absolute total for an `adjustment` (a physical recount), which record() stores as-is.
     */
    private function createEntry(Request $request): void
    {
        $request->validate([
            'mar_sheet_id'    => 'required|integer',
            'action_type'     => 'required|in:administered,received,disposed,returned,adjustment',
            'entry_date'      => 'nullable|date',
            'entry_time'      => 'nullable',
            'dose_quantity'   => 'nullable|numeric|min:0',
            'unit'            => 'nullable|string|max:50',
            // Accepted ONLY as the absolute recount total for an `adjustment`; ignored for
            // every movement (those balances are computed, never client-supplied).
            'balance_after'   => 'nullable|numeric|min:0',
            'witness_name'    => 'required|string|max:255',
            'notes'           => 'nullable|string',
        ]);

        $homeId = $this->getHomeId();
        $sheet  = MARSheet::forHome($homeId)->where('is_controlled', 1)->find($request->input('mar_sheet_id'));
        if (! $sheet) {
            throw ValidationException::withMessages([
                'mar_sheet_id' => 'That controlled drug could not be found for your home.',
            ]);
        }

        $action = $request->input('action_type');
        // For a movement, the quantity moved drives the computed balance. For an adjustment,
        // the operator's absolute recount total is the figure record() stores (before was
        // the last balance); prefer the count field, fall back to dose_quantity.
        $qty = $action === 'adjustment'
            ? (float) ($request->input('balance_after') ?? $request->input('dose_quantity') ?? 0)
            : (float) ($request->input('dose_quantity') ?? 0);

        $resident = ServiceUser::where('id', $sheet->client_id)->first();

        ControlledDrugRegister::record(
            $sheet,
            $action,
            $qty,
            (int) Auth::id(),
            $request->input('witness_name'),
            [
                'client_name' => $resident->name ?? null,
                'entry_date'  => $request->input('entry_date'),
                'entry_time'  => $request->input('entry_time'),
                'unit'        => $request->input('unit'),
                'notes'       => $request->input('notes'),
            ]
        );
    }
}
