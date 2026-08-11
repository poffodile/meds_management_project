<?php

namespace App\Http\Controllers\Frontend4;

use App\Http\Controllers\frontEnd\Concerns\ResolvesCurrentHome;
use App\Models\MARSheet;
use App\Services\Frontend4\Permissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * frontend4 — changing a prescription (Page 2, Slice E).
 *
 * Pause, resume or stop a prescription. Manager-and-above only (owner decision
 * 2026-08-05); an administrator is deliberately excluded, because changing a
 * prescription is a clinical-record edit and an administrator manages access,
 * not the clinical record.
 *
 * ATTRIBUTABLE, NOT DESTRUCTIVE.
 * The change is written to the append-only `mar_sheet_changes` log first — who,
 * when, why, and the before/after status — and only then is the sheet's status
 * updated, both inside one transaction with the row locked. The history is never
 * lost, and a pause can be resumed. This is what makes the edit meet the
 * Definition of Done rather than silently mutating a clinical record (I18).
 *
 * The check here is the feature; hiding the buttons for other roles is a
 * courtesy the React side adds on top.
 */
class PrescriptionController extends F4Controller
{
    use ResolvesCurrentHome;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (! Auth::guard('frontend4')->check()) {
                abort(403, 'You do not have access to medication management.');
            }

            return $next($request);
        });
    }

    public function changeStatus(Request $request, int $client, int $sheet)
    {
        $this->requirePermission(Permissions::MANAGE_PRESCRIPTION);

        $data = $request->validate([
            'action' => 'required|in:pause,resume,stop',
            'reason' => 'required|string|min:3|max:1000',
        ], [
            'reason.required' => 'Give a reason — it is recorded against the change.',
            'reason.min' => 'Give a little more detail so the change is clear later.',
        ]);

        $homeId = $this->currentHomeId();
        $userId = Auth::id();

        $clientAllowed = $this->scopeFrontend4Clients(\App\ServiceUser::query())
            ->where('is_deleted', 0)
            ->where('id', $client)
            ->exists();
        if (! $clientAllowed) {
            abort(404);
        }

        DB::transaction(function () use ($data, $homeId, $userId, $client, $sheet) {
            // Locked so two managers cannot change the same prescription at once.
            $row = MARSheet::forHome($homeId)
                ->where('id', $sheet)
                ->where('client_id', $client)
                ->lockForUpdate()
                ->first();

            if (! $row) {
                abort(404);
            }

            $old = $row->mar_status;
            [$new, $type] = match ($data['action']) {
                'pause'  => ['paused', 'paused'],
                'resume' => ['active', 'resumed'],
                'stop'   => ['discontinued', 'stopped'],
            };

            // Append-only record first — who, when, why, before → after.
            DB::table('mar_sheet_changes')->insert([
                'mar_sheet_id' => $row->id,
                'home_id' => $homeId,
                'client_id' => $client,
                'change_type' => $type,
                'field' => 'mar_status',
                'old_value' => $old,
                'new_value' => $new,
                'reason' => $data['reason'],
                'changed_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Then the current state.
            $row->mar_status = $new;
            if ($data['action'] === 'stop') {
                $row->discontinued = 1;
                $row->discontinued_date = now()->toDateString();
                $row->discontinued_reason = $data['reason'];
            } elseif ($data['action'] === 'resume') {
                $row->discontinued = 0;
                $row->discontinued_date = null;
                $row->discontinued_reason = null;
            }
            $row->save();
        });

        return redirect()
            ->route('frontend4.clients.show', ['client' => $client])
            ->with('success', 'Prescription updated.');
    }
}
