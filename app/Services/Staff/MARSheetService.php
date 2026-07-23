<?php

namespace App\Services\Staff;

use App\Models\MARAdministration;
use App\Models\MARSheet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class MARSheetService
{
    public function store(array $data, int $homeId, int $userId): MARSheet
    {
        DB::beginTransaction();
        try {
            $sheet = new MARSheet;
            $sheet->fill($data);
            $sheet->home_id = $homeId;
            $sheet->created_by = $userId;
            $sheet->save();

            DB::commit();

            Log::info('MAR sheet created', [
                'action' => 'create',
                'record_id' => $sheet->id,
                'medication' => $sheet->medication_name,
                'home_id' => $homeId,
                'user_id' => $userId,
            ]);

            return $sheet;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating MAR sheet: '.$e->getMessage());
            throw $e;
        }
    }

    public function update(int $id, array $data, int $homeId): ?MARSheet
    {
        $sheet = MARSheet::forHome($homeId)->active()->find($id);
        if (! $sheet) {
            return null;
        }

        DB::beginTransaction();
        try {
            unset($data['home_id'], $data['created_by'], $data['is_deleted']);
            $sheet->fill($data);
            $sheet->save();
            DB::commit();

            Log::info('MAR sheet updated', [
                'action' => 'update',
                'record_id' => $id,
                'home_id' => $homeId,
            ]);

            return $sheet;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating MAR sheet: '.$e->getMessage());
            throw $e;
        }
    }

    public function list(int $clientId, int $homeId, ?string $status = null)
    {
        $query = MARSheet::forHome($homeId)
            ->active()
            ->where('client_id', $clientId)
            ->with(['createdByUser:id,name'])
            ->orderBy('created_at', 'desc');

        if ($status === 'active') {
            $query->currentlyActive();
        } elseif ($status === 'discontinued') {
            $query->where('mar_status', 'discontinued');
        }

        return $query->paginate(10);
    }

    public function details(int $id, int $homeId): ?MARSheet
    {
        return MARSheet::forHome($homeId)
            ->active()
            ->with([
                'administrations' => function ($q) {
                    $q->orderBy('date', 'desc')->orderBy('time_slot', 'asc');
                },
                'administrations.administeredByUser:id,name',
                'createdByUser:id,name',
            ])
            ->find($id);
    }

    public function delete(int $id, int $homeId): bool
    {
        $sheet = MARSheet::forHome($homeId)->active()->find($id);
        if (! $sheet) {
            return false;
        }

        $sheet->is_deleted = 1;
        $sheet->save();

        Log::info('MAR sheet deleted', [
            'action' => 'delete',
            'record_id' => $id,
            'home_id' => $homeId,
        ]);

        return true;
    }

    public function discontinue(int $id, array $data, int $homeId): ?MARSheet
    {
        $sheet = MARSheet::forHome($homeId)->active()->currentlyActive()->find($id);
        if (! $sheet) {
            return null;
        }

        $sheet->discontinued = true;
        $sheet->mar_status = 'discontinued';
        $sheet->discontinued_date = $data['discontinued_date'] ?? now()->toDateString();
        $sheet->discontinued_reason = $data['discontinued_reason'] ?? null;
        $sheet->save();

        Log::info('MAR sheet discontinued', [
            'action' => 'discontinue',
            'record_id' => $id,
            'home_id' => $homeId,
        ]);

        return $sheet;
    }

    public function administer(int $marSheetId, array $data, int $homeId, int $userId): ?MARAdministration
    {
        $sheet = MARSheet::forHome($homeId)->active()->find($marSheetId);
        if (! $sheet) {
            return null;
        }

        DB::beginTransaction();
        try {
            // A PRN ("as needed") dose is a DISCRETE EVENT, so it always gets its own
            // row. A scheduled dose belongs to a slot, so it is found-or-updated.
            //
            // Why (audit CR-01): a PRN sheet carries one fixed nominal slot — every
            // PRN dose all day is submitted against e.g. "12:00". The find-or-update
            // below therefore matched the FIRST dose of the day and overwrote it, so
            // the second real dose destroyed the first one's record (who gave it, when,
            // what they wrote). It also meant the PRN daily-maximum and interval checks
            // could never see more than one dose, making them unenforceable.
            //
            // Found-or-update is still correct for scheduled meds: Risperidone at
            // 08:00 and 20:00 are genuinely different slots and must not collide, and
            // re-submitting the same slot should amend, not duplicate.
            // NOTE ON LOCKING: there is deliberately no lockForUpdate() here. A row lock
            // on a row that does not exist yet locks nothing — two concurrent callers
            // would both read "none", both insert. Callers that need serialising take a
            // lock on the mar_sheets row instead and hold it across their checks and this
            // write (see BuildsMedicationRound::applyRecord). A lock added here would
            // read as protection while providing none.
            $admin = $sheet->as_required
                ? null
                : MARAdministration::where('mar_sheet_id', $marSheetId)
                    ->where('date', $data['date'])
                    ->where('time_slot', $data['time_slot'])
                    ->first();

            if ($admin) {
                // APPEND-ONLY amendment (compliance: CQC Reg 17 / Children's Homes Regs
                // reg 23(2)(c)). We do NOT edit the existing row in place any more. We write
                // a NEW row with the corrected values, pointing back to the one it replaces,
                // and mark the old row superseded. The prior value, its author and its time
                // are preserved for audit — nothing is destroyed.
                $newValues = [
                    // 'A' only. 'S' (asleep) is NOT given — the resident received nothing.
                    'given' => $data['code'] === 'A',
                    'dose_given' => $data['dose_given'] ?? null,
                    'administered_by' => $userId,
                    'witnessed_by' => $data['witnessed_by'] ?? null,
                    'code' => $data['code'],
                    'reason' => $data['reason'] ?? null,
                    'notes' => $data['notes'] ?? null,
                ];

                // If nothing actually changed, this is a no-op re-submit (e.g. a double-tap).
                // Do not spam the history with an identical superseding version.
                $unchanged = ((bool) $admin->given === $newValues['given'])
                    && ((string) $admin->code === (string) $newValues['code'])
                    && ((string) $admin->dose_given === (string) $newValues['dose_given'])
                    && ((string) $admin->reason === (string) $newValues['reason'])
                    && ((string) $admin->notes === (string) $newValues['notes'])
                    && ((string) $admin->witnessed_by === (string) $newValues['witnessed_by']);

                if ($unchanged) {
                    DB::commit();

                    return $admin;
                }

                $amended = new MARAdministration;
                $amended->fill(array_merge($newValues, [
                    'mar_sheet_id' => $marSheetId,
                    'date' => $data['date'],
                    'time_slot' => $data['time_slot'],
                    'supersedes_id' => $admin->id,
                    'amendment_reason' => $data['amendment_reason'] ?? null,
                ]));
                $amended->home_id = $homeId;
                // The administration TIME carries over from the original — an edit is not a
                // new dose event, so the PRN interval clock must not move (audit CR-01).
                $amended->administered_at = $admin->administered_at ?? ($data['administered_at'] ?? now());
                $amended->save();

                // Retire the prior version. This flip is the only in-place update that ever
                // touches an administration row.
                $admin->is_current = false;
                $admin->superseded_at = now();
                $admin->save();

                $admin = $amended;
            } else {
                $admin = new MARAdministration;
                $admin->fill([
                    'mar_sheet_id' => $marSheetId,
                    'date' => $data['date'],
                    'time_slot' => $data['time_slot'],
                    // 'A' only. 'S' (asleep) is NOT given — the resident received nothing.
                    'given' => $data['code'] === 'A',
                    'dose_given' => $data['dose_given'] ?? null,
                    'administered_by' => $userId,
                    'witnessed_by' => $data['witnessed_by'] ?? null,
                    'code' => $data['code'],
                    'reason' => $data['reason'] ?? null,
                    'notes' => $data['notes'] ?? null,
                ]);
                $admin->home_id = $homeId;
                if (Schema::hasColumn('mar_administrations', 'administered_at')) {
                    $admin->administered_at = $data['administered_at'] ?? now();
                }
                $admin->save();
            }

            DB::commit();

            Log::info('MAR administration recorded', [
                'action' => 'administer',
                'mar_sheet_id' => $marSheetId,
                'administration_id' => $admin->id,
                'date' => $data['date'],
                'time_slot' => $data['time_slot'],
                'code' => $data['code'],
                'home_id' => $homeId,
                'user_id' => $userId,
            ]);

            return $admin;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error recording MAR administration: '.$e->getMessage());
            throw $e;
        }
    }

    public function updateStock(int $id, array $data, int $homeId): ?MARSheet
    {
        $sheet = MARSheet::forHome($homeId)->active()->find($id);
        if (! $sheet) {
            return null;
        }

        $sheet->fill([
            'quantity_received' => $data['quantity_received'] ?? $sheet->quantity_received,
            'quantity_carried_forward' => $data['quantity_carried_forward'] ?? $sheet->quantity_carried_forward,
            'quantity_returned' => $data['quantity_returned'] ?? $sheet->quantity_returned,
        ]);
        $sheet->save();

        Log::info('MAR sheet stock updated', [
            'action' => 'stock_update',
            'record_id' => $id,
            'home_id' => $homeId,
        ]);

        return $sheet;
    }

    public function getMonthlyGrid(int $clientId, int $homeId, int $year, int $month)
    {
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));

        $sheets = MARSheet::forHome($homeId)
            ->active()
            ->where('client_id', $clientId)
            ->currentlyActive()
            ->with(['administrations' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate])
                    ->with('administeredByUser:id,name')
                    ->orderBy('date')
                    ->orderBy('time_slot');
            }])
            ->orderBy('medication_name', 'asc')
            ->get();

        $daysInMonth = (int) date('t', strtotime($startDate));

        return [
            'sheets' => $sheets,
            'year' => $year,
            'month' => $month,
            'days_in_month' => $daysInMonth,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }

    public function getAdministrationsForDate(int $clientId, int $homeId, string $date)
    {
        $sheets = MARSheet::forHome($homeId)
            ->active()
            ->where('client_id', $clientId)
            ->currentlyActive()
            ->with(['administrations' => function ($q) use ($date) {
                $q->forDate($date)->with('administeredByUser:id,name');
            }])
            ->orderBy('medication_name', 'asc')
            ->get();

        return $sheets;
    }
}
