<?php

namespace App\Http\Controllers\frontEnd\Medication;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\MARSheet;
use App\Models\ShiftHandover;
use App\ServiceUser;
use Inertia\Inertia;

class ShiftHandoverController extends Controller
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
     * The home currently in view — the manager's *selected* home, re-validated against the
     * homes they may access (via ResolvesCurrentHome), matching the other medication pages.
     * (The trait exposes currentHomeId(); this is the thin wrapper the Med2 methods call.)
     */
    private function getHomeId(): int
    {
        return $this->currentHomeId();
    }

    public function index(Request $request)
    {
        $request->validate(['date' => 'nullable|date']);

        $homeId = Auth::user()->home_id;
        $date   = $request->input('date', now()->toDateString());
        $carbon = \Carbon\Carbon::parse($date);

        // The log shows the handovers for the chosen day (default today); staff page back/forward by day.
        $handovers = ShiftHandover::forHome($homeId)
            ->whereDate('handover_date', $date)
            ->with(['fromCarer:id,name', 'toCarer:id,name', 'acknowledgedByUser:id,name', 'createdByUser:id,name'])
            ->orderByDesc('handover_time')
            ->get();

        $serviceUsers = ServiceUser::where('home_id', $homeId)
            ->where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('frontEnd.medication.shift_handover.index', [
            'handovers'    => $handovers,
            'serviceUsers' => $serviceUsers,
            'selectedDate' => $date,
            'prevDate'     => $carbon->copy()->subDay()->toDateString(),
            'nextDate'     => $carbon->copy()->addDay()->toDateString(),
            'todayDate'    => now()->toDateString(),
        ]);
    }

    /** React/Inertia version of the handover log. */
    public function indexReact(Request $request)
    {
        $request->validate(['date' => 'nullable|date']);

        $homeId = Auth::user()->home_id;
        $date   = $request->input('date', now()->toDateString());
        $carbon = \Carbon\Carbon::parse($date);

        $handovers = ShiftHandover::forHome($homeId)
            ->whereDate('handover_date', $date)
            ->with(['acknowledgedByUser:id,name', 'createdByUser:id,name'])
            ->orderByDesc('handover_time')
            ->get()
            ->map(fn ($h) => [
                'id'                  => $h->id,
                'location'            => $h->location,
                'handover_date'       => $h->handover_date ? $h->handover_date->format('d M Y') : null,
                'handover_time'       => $h->handover_time ? \Carbon\Carbon::parse($h->handover_time)->format('H:i') : null,
                'from_carer_name'     => $h->from_carer_name,
                'to_carer_name'       => $h->to_carer_name,
                'general_notes'       => $h->general_notes,
                'client_updates'      => $h->client_updates ?? [],
                'medication_concerns' => $h->medication_concerns ?? [],
                'priority_alerts'     => $h->priority_alerts ?? [],
                'status'              => $h->status,
                'acknowledged_by'     => $h->acknowledgedByUser->name ?? null,
                'acknowledged_at'     => $h->acknowledged_at ? $h->acknowledged_at->format('d M Y H:i') : null,
                'created_by'          => $h->createdByUser->name ?? null,
            ]);

        $serviceUsers = ServiceUser::where('home_id', $homeId)
            ->where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($s) => ['id' => $s->id, 'name' => $s->name]);

        return Inertia::render('Medication/ShiftHandover', [
            'handovers'    => $handovers,
            'serviceUsers' => $serviceUsers,
            'selectedDate' => $date,
            'prevDate'     => $carbon->copy()->subDay()->toDateString(),
            'nextDate'     => $carbon->copy()->addDay()->toDateString(),
            'todayDate'    => now()->toDateString(),
        ]);
    }

    /**
     * Medication 2 → Shift Handover. Fresh CLINIK-shell page; reuses the same handover
     * logic as the legacy pages. Home-switcher-scoped (getHomeId via ResolvesCurrentHome),
     * and offers a staff picker for the "from"/"to" carer + residents for per-client updates.
     */
    public function indexMedication2(Request $request)
    {
        $request->validate(['date' => 'nullable|date']);

        $homeId = $this->getHomeId();
        $date   = $request->input('date', now()->toDateString());
        $carbon = \Carbon\Carbon::parse($date);

        $handovers = ShiftHandover::forHome($homeId)
            ->whereDate('handover_date', $date)
            ->with(['acknowledgedByUser:id,name', 'createdByUser:id,name'])
            ->orderByDesc('handover_time')
            ->get()
            ->map(fn ($h) => [
                'id'                  => $h->id,
                'location'            => $h->location,
                'handover_date'       => $h->handover_date ? $h->handover_date->format('d M Y') : null,
                'handover_time'       => $h->handover_time ? \Carbon\Carbon::parse($h->handover_time)->format('H:i') : null,
                'from_carer_name'     => $h->from_carer_name,
                'to_carer_name'       => $h->to_carer_name,
                'general_notes'       => $h->general_notes,
                'client_updates'      => $h->client_updates ?? [],
                'medication_concerns' => $h->medication_concerns ?? [],
                'priority_alerts'     => $h->priority_alerts ?? [],
                'status'              => $h->status,
                'acknowledged_by'     => $h->acknowledgedByUser->name ?? null,
                'acknowledged_at'     => $h->acknowledged_at ? $h->acknowledged_at->format('d M Y · H:i') : null,
                'created_by'          => $h->createdByUser->name ?? null,
                'can_acknowledge'     => $h->status === 'submitted',
            ]);

        $residents = ServiceUser::where('home_id', $homeId)->where('status', 1)
            ->orderBy('name')->get(['id', 'name'])
            ->map(fn ($s) => ['value' => (string) $s->id, 'label' => $s->name])->all();

        // Prefill a medication concern when arriving from the round's "flag to handover".
        // The round passes only IDENTIFIERS (sheet + date + slot); the concern text and the
        // resident are rebuilt HERE from the dose record, home-scoped — so no resident name
        // or clinical text ever travels in the URL (review I-3), and a cross-home or bogus
        // sheet id simply yields no prefill (review I-5).
        $prefill = null;
        if ($request->filled('flag_sheet')) {
            $sheet = MARSheet::forHome($homeId)->find((int) $request->input('flag_sheet'));
            if ($sheet) {
                $admin = \App\Models\MARAdministration::where('mar_sheet_id', $sheet->id)
                    ->where('date', $request->input('flag_date'))
                    ->where('time_slot', $request->input('flag_slot'))
                    ->first();
                $resident = ServiceUser::where('id', $sheet->client_id)->where('home_id', $homeId)->first();
                $codeLabels = ['A' => 'Given', 'S' => 'Asleep — not given', 'R' => 'Refused', 'W' => 'Withheld', 'N' => 'Not available', 'O' => 'Omitted'];
                $outcome = $admin ? ($codeLabels[$admin->code] ?? $admin->code ?? 'Not given') : 'Not given';
                $slot = $request->input('flag_slot');
                $concern = $sheet->medication_name.($slot ? " ({$slot})" : '').': '.$outcome
                    .($admin && $admin->reason ? ' — '.$admin->reason : '');
                $prefill = [
                    'client_id'   => $sheet->client_id,
                    'client_name' => $resident->name ?? '',
                    'concern'     => $concern,
                ];
            }
        }

        return Inertia::render('Frontend2/Medication2/ShiftHandover', [
            'handovers'    => $handovers,
            'residents'    => $residents,
            'staff'        => $this->homeStaffOptions(),
            'selfName'     => Auth::user()->name,
            'prefill'      => $prefill,
            'selectedDate' => $date,
            'prevDate'     => $carbon->copy()->subDay()->toDateString(),
            'nextDate'     => $carbon->copy()->addDay()->toDateString(),
            'todayDate'    => now()->toDateString(),
            'isManager'    => in_array(Auth::user()->user_type, ShiftHandover::MANAGER_TYPES, true),
        ]);
    }

    /** Create a handover from the Medication 2 page (reuses the shared persist logic). */
    public function storeMedication2(Request $request)
    {
        $result = $this->persistHandover($request);
        if ($result instanceof \Illuminate\Http\RedirectResponse) {
            return $result;
        }

        return redirect()->route('frontend2.medication2.shift-handover', ['date' => $request->input('handover_date')])
            ->with('success', $result === 'submitted' ? 'Handover submitted.' : 'Handover saved as draft.');
    }

    /** Acknowledge a submitted handover from the Medication 2 page. Manager-only. */
    public function acknowledgeMedication2(Request $request, $id)
    {
        // Server-side match for the UI, which only offers Acknowledge to managers (review
        // C-2 / HAZ-32). Acknowledging forges the accountability field, so it must be
        // enforced here, not just hidden. (Owner to confirm the intended role set.)
        if (! in_array(Auth::user()->user_type, ShiftHandover::MANAGER_TYPES, true)) {
            abort(403, 'Only a manager can acknowledge a handover.');
        }

        $error = $this->runAcknowledge($id);

        return redirect()->route('frontend2.medication2.shift-handover', ['date' => $request->input('date')])
            ->with($error ? 'error' : 'success', $error ?? 'Handover acknowledged.');
    }

    public function store(Request $request)
    {
        $result = $this->persistHandover($request);
        if ($result instanceof \Illuminate\Http\RedirectResponse) {
            return $result;
        }

        return redirect()->route('medication.shift-handover.index')
            ->with('success', $result === 'submitted' ? 'Handover submitted.' : 'Handover saved as draft.');
    }

    /** Same create, but returns to the React/Inertia page (keeping the date). */
    public function storeReact(Request $request)
    {
        $result = $this->persistHandover($request);
        if ($result instanceof \Illuminate\Http\RedirectResponse) {
            return $result;
        }

        return redirect()->route('medication.shift-handover.react', ['date' => $request->input('handover_date')])
            ->with('success', $result === 'submitted' ? 'Handover submitted.' : 'Handover saved as draft.');
    }

    /**
     * Validate + create a handover. Returns the action string ('draft'|'submitted') on success,
     * or a redirect-back response on validation failure. Shared by the legacy + React pages.
     */
    private function persistHandover(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'location' => 'nullable|string|max:255',
            'handover_date' => 'required|date',
            'handover_time' => 'required',
            'from_carer_name' => 'nullable|string|max:255',
            'to_carer_name' => 'nullable|string|max:255',
            'general_notes' => 'nullable|string',
            'client_updates' => 'nullable|array',
            'client_updates.*.client_id' => 'nullable|integer',
            'client_updates.*.client_name' => 'nullable|string|max:255',
            'client_updates.*.update' => 'nullable|string',
            'client_updates.*.priority' => 'nullable|in:low,medium,high,urgent',
            'medication_concerns' => 'nullable|array',
            'medication_concerns.*.client_id' => 'nullable|integer',
            'medication_concerns.*.client_name' => 'nullable|string|max:255',
            'medication_concerns.*.concern' => 'nullable|string',
            'medication_concerns.*.action_required' => 'nullable|boolean',
            'priority_alerts' => 'nullable|array',
            'submit_action' => 'required|in:draft,submitted',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $action = $request->input('submit_action');

        ShiftHandover::create([
            'home_id' => Auth::user()->home_id,
            'location' => $request->input('location'),
            'handover_date' => $request->input('handover_date'),
            'handover_time' => $request->input('handover_time'),
            'from_carer_name' => $request->input('from_carer_name'),
            'to_carer_name' => $request->input('to_carer_name'),
            'general_notes' => $request->input('general_notes'),
            'client_updates' => array_values(array_filter($request->input('client_updates', []), fn($u) => !empty($u['update'] ?? null))),
            'medication_concerns' => array_values(array_filter($request->input('medication_concerns', []), fn($c) => !empty($c['concern'] ?? null))),
            'priority_alerts' => array_values(array_filter($request->input('priority_alerts', []), fn($a) => !empty($a['alert'] ?? null))),
            'status' => $action,
            'submitted_at' => $action === 'submitted' ? now() : null,
            'created_by_user_id' => Auth::id(),
        ]);

        return $action;
    }

    public function update(Request $request, $id)
    {
        $handover = ShiftHandover::forHome(Auth::user()->home_id)->findOrFail($id);

        if (!$handover->canBeEditedBy(Auth::user())) {
            return redirect()->route('medication.shift-handover.index')
                ->with('error', 'This handover has been acknowledged and can only be edited by a manager.');
        }

        $validator = Validator::make($request->all(), [
            'location' => 'nullable|string|max:255',
            'handover_date' => 'required|date',
            'handover_time' => 'required',
            'from_carer_name' => 'nullable|string|max:255',
            'to_carer_name' => 'nullable|string|max:255',
            'general_notes' => 'nullable|string',
            'client_updates' => 'nullable|array',
            'client_updates.*.client_id' => 'nullable|integer',
            'client_updates.*.client_name' => 'nullable|string|max:255',
            'client_updates.*.update' => 'nullable|string',
            'client_updates.*.priority' => 'nullable|in:low,medium,high,urgent',
            'medication_concerns' => 'nullable|array',
            'medication_concerns.*.client_id' => 'nullable|integer',
            'medication_concerns.*.client_name' => 'nullable|string|max:255',
            'medication_concerns.*.concern' => 'nullable|string',
            'medication_concerns.*.action_required' => 'nullable|boolean',
            'priority_alerts' => 'nullable|array',
            'submit_action' => 'required|in:draft,submitted',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $action = $request->input('submit_action');

        $newValues = [
            'location' => $request->input('location'),
            'handover_date' => $request->input('handover_date'),
            'handover_time' => $request->input('handover_time'),
            'from_carer_name' => $request->input('from_carer_name'),
            'to_carer_name' => $request->input('to_carer_name'),
            'general_notes' => $request->input('general_notes'),
            'client_updates' => array_values(array_filter($request->input('client_updates', []), fn($u) => !empty($u['update'] ?? null))),
            'medication_concerns' => array_values(array_filter($request->input('medication_concerns', []), fn($c) => !empty($c['concern'] ?? null))),
            'priority_alerts' => array_values(array_filter($request->input('priority_alerts', []), fn($a) => !empty($a['alert'] ?? null))),
            // Acknowledged handovers (edited by a manager) keep their status; otherwise follow the button used.
            'status' => $handover->status === 'acknowledged' ? 'acknowledged' : $action,
        ];

        $changes = $this->detectChanges($handover, $newValues);

        $handover->fill($newValues);
        if ($newValues['status'] === 'submitted' && is_null($handover->submitted_at)) {
            $handover->submitted_at = now();
        }

        if (!empty($changes)) {
            $log = $handover->edit_log ?? [];
            $log[] = [
                'user_id'   => Auth::id(),
                'user_name' => Auth::user()->name,
                'at'        => now()->toDateTimeString(),
                'changes'   => $changes,
            ];
            $handover->edit_log = $log;
        }

        $handover->save();

        return redirect()->route('medication.shift-handover.index')
            ->with('success', 'Handover updated.');
    }

    /** Build a human-readable list of what changed between the saved handover and the new values. */
    private function detectChanges(ShiftHandover $h, array $new): array
    {
        $changes = [];

        // Short scalar fields — show the new value.
        $labelled = [
            'location'        => 'Location',
            'from_carer_name' => 'From carer',
            'to_carer_name'   => 'To carer',
        ];
        foreach ($labelled as $field => $label) {
            if ((string) ($h->$field ?? '') !== (string) ($new[$field] ?? '')) {
                $changes[] = $label . ' changed to "' . ($new[$field] ?: '—') . '"';
            }
        }

        // General notes — long, so just note that it changed.
        if ((string) ($h->general_notes ?? '') !== (string) ($new['general_notes'] ?? '')) {
            $changes[] = 'General notes changed';
        }

        $oldDate = $h->handover_date ? $h->handover_date->format('Y-m-d') : '';
        if ($oldDate !== (string) $new['handover_date']) {
            $changes[] = 'Date changed to ' . $new['handover_date'];
        }

        $oldTime = $h->handover_time ? \Carbon\Carbon::parse($h->handover_time)->format('H:i') : '';
        $newTime = \Carbon\Carbon::parse($new['handover_time'])->format('H:i');
        if ($oldTime !== $newTime) {
            $changes[] = 'Time changed to ' . $newTime;
        }

        $arrays = [
            'client_updates'      => 'Client updates',
            'medication_concerns' => 'Medication concerns',
            'priority_alerts'     => 'Priority alerts',
        ];
        foreach ($arrays as $field => $label) {
            $oldArr = $h->$field ?? [];
            $newArr = $new[$field] ?? [];
            if (count($oldArr) !== count($newArr)) {
                $changes[] = $label . ': ' . count($oldArr) . ' → ' . count($newArr) . ' item(s)';
            } elseif (json_encode($oldArr) !== json_encode($newArr)) {
                $changes[] = $label . ' edited';
            }
        }

        if ($h->status !== $new['status']) {
            $changes[] = 'Status changed from ' . $h->status . ' to ' . $new['status'];
        }

        return $changes;
    }

    public function acknowledge($id)
    {
        $error = $this->runAcknowledge($id);

        if ($error) {
            return response()->json(['ok' => false, 'message' => $error], 422);
        }

        $handover = ShiftHandover::forHome(Auth::user()->home_id)->find($id);

        return response()->json([
            'ok' => true,
            'acknowledged_at' => $handover->acknowledged_at->toDateTimeString(),
            'acknowledged_by_name' => Auth::user()->name,
        ]);
    }

    /** Same acknowledge, but returns to the React/Inertia page (keeping the date). */
    public function acknowledgeReact(Request $request, $id)
    {
        $error = $this->runAcknowledge($id);

        return redirect()->route('medication.shift-handover.react', ['date' => $request->input('date')])
            ->with($error ? 'error' : 'success', $error ?? 'Handover acknowledged.');
    }

    /** Mark a submitted handover acknowledged. Returns an error message, or null on success. */
    private function runAcknowledge($id): ?string
    {
        $handover = ShiftHandover::forHome(Auth::user()->home_id)->findOrFail($id);

        if ($handover->status !== 'submitted') {
            return 'Only submitted handovers can be acknowledged.';
        }

        $handover->update([
            'status' => 'acknowledged',
            'acknowledged_at' => now(),
            'acknowledged_by_user_id' => Auth::id(),
        ]);

        return null;
    }
}
