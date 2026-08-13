<?php

namespace App\Services\Frontend4;

use App\Models\Frontend4PrescriptionEvent;
use App\Models\Frontend4User;
use App\Models\MARSheet;
use App\Models\MedicineCatalogue;
use App\ServiceUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class PrescriptionRecordService
{
    public const EDITABLE_FIELDS = [
        'medication_name_as_written', 'dose_amount', 'dose_unit', 'route', 'frequency',
        'time_slots', 'as_required', 'prn_details', 'prn_max_daily',
        'prn_min_interval_hours', 'reason_for_medication', 'administration_instructions',
        'prescriber', 'pharmacy', 'start_date', 'end_date', 'review_due_date',
        'prescription_source',
    ];

    public function create(
        ServiceUser $client,
        MedicineCatalogue $medicine,
        array $data,
        Frontend4User $actor,
        int $organisationId,
        int $serviceId
    ): MARSheet {
        return DB::transaction(function () use ($client, $medicine, $data, $actor, $organisationId, $serviceId) {
            $medicine = MedicineCatalogue::whereKey($medicine->id)->lockForUpdate()->firstOrFail();
            $this->assertSelectable($medicine);
            $this->assertNoDuplicate($client->id, $serviceId, $medicine->id);

            $sheet = new MARSheet;
            $sheet->fill($this->sheetValues($medicine, $data));
            $sheet->home_id = $serviceId;
            $sheet->client_id = $client->id;
            $sheet->created_by = $actor->id;
            $sheet->mar_status = 'active';
            $sheet->discontinued = false;
            $sheet->is_deleted = false;
            $sheet->prescription_version = 1;
            $sheet->prescribed_at = now();
            $sheet->save();

            $this->event($sheet, $actor, $organisationId, 'created', $data['reason'] ?? null, [
                'after' => $this->snapshot($sheet),
            ]);

            return $sheet;
        });
    }

    public function amend(
        MARSheet $sheet,
        array $data,
        string $reason,
        Frontend4User $actor,
        int $organisationId
    ): MARSheet {
        return DB::transaction(function () use ($sheet, $data, $reason, $actor, $organisationId) {
            $sheet = MARSheet::whereKey($sheet->id)->lockForUpdate()->firstOrFail();
            if (! in_array($sheet->mar_status, ['active', 'paused'], true)) {
                throw ValidationException::withMessages([
                    'prescription' => 'Only an active or paused prescription can be amended. Create a new prescription for a stopped medicine.',
                ]);
            }
            if (! $sheet->medicine_id) {
                throw ValidationException::withMessages([
                    'medicine_id' => 'This legacy prescription must be reconciled to a catalogue medicine before it can be amended.',
                ]);
            }

            $medicine = MedicineCatalogue::whereKey($sheet->medicine_id)->lockForUpdate()->firstOrFail();
            $this->assertSelectable($medicine);
            $before = $this->snapshot($sheet);
            $sheet->fill($this->sheetValues($medicine, $data));
            $sheet->prescription_version = max(1, (int) $sheet->prescription_version) + 1;
            $sheet->save();
            $after = $this->snapshot($sheet);

            $this->event($sheet, $actor, $organisationId, 'amended', $reason, [
                'before' => $before,
                'after' => $after,
            ]);

            return $sheet;
        });
    }

    public function changeStatus(
        MARSheet $sheet,
        string $action,
        string $reason,
        Frontend4User $actor,
        int $organisationId
    ): MARSheet {
        return DB::transaction(function () use ($sheet, $action, $reason, $actor, $organisationId) {
            $sheet = MARSheet::whereKey($sheet->id)->lockForUpdate()->firstOrFail();
            $old = (string) $sheet->mar_status;
            $allowed = [
                'active' => ['pause' => 'paused', 'stop' => 'discontinued'],
                'paused' => ['resume' => 'active', 'stop' => 'discontinued'],
            ];
            $new = $allowed[$old][$action] ?? null;
            if ($new === null) {
                throw ValidationException::withMessages([
                    'action' => $old === 'discontinued'
                        ? 'A stopped prescription cannot be resumed. Create a new prescription if treatment restarts.'
                        : 'That prescription status change is not allowed.',
                ]);
            }

            $sheet->mar_status = $new;
            if ($new === 'discontinued') {
                $sheet->discontinued = true;
                $sheet->discontinued_date = now()->toDateString();
                $sheet->discontinued_reason = $reason;
            }
            $sheet->save();

            if (Schema::hasTable('mar_sheet_changes')) {
                DB::table('mar_sheet_changes')->insert([
                    'mar_sheet_id' => $sheet->id,
                    'home_id' => $sheet->home_id,
                    'client_id' => $sheet->client_id,
                    'change_type' => $action === 'stop' ? 'stopped' : ($action === 'pause' ? 'paused' : 'resumed'),
                    'field' => 'mar_status',
                    'old_value' => $old,
                    'new_value' => $new,
                    'reason' => $reason,
                    'changed_by' => $actor->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->event($sheet, $actor, $organisationId, $action === 'stop' ? 'stopped' : $action.'d', $reason, [
                'field' => 'mar_status', 'before' => $old, 'after' => $new,
            ]);

            return $sheet;
        });
    }

    private function sheetValues(MedicineCatalogue $medicine, array $data): array
    {
        $doseAmount = (float) $data['dose_amount'];
        $doseUnit = trim((string) $data['dose_unit']);

        return collect($data)->only(self::EDITABLE_FIELDS)->all() + [
            'medicine_id' => $medicine->id,
            'medication_name' => $medicine->name,
            'dosage' => $this->strengthLabel($medicine),
            'dose' => $this->number($doseAmount).' '.$doseUnit,
            'dose_quantity' => $this->doseQuantity($medicine, $doseAmount, $doseUnit),
            'form' => $medicine->form,
            'unit' => $medicine->countable_unit,
            'is_controlled' => (bool) $medicine->is_controlled,
            'cd_schedule' => $medicine->is_controlled ? $medicine->cd_schedule : null,
        ];
    }

    private function doseQuantity(MedicineCatalogue $medicine, float $doseAmount, string $doseUnit): ?float
    {
        $dose = $this->unit($doseUnit);
        $countable = $this->unit($medicine->countable_unit);
        if ($dose !== '' && $dose === $countable) {
            return round($doseAmount, 3);
        }

        $strengthUnit = $this->unit($medicine->strength_unit);
        $strength = (float) $medicine->strength_amount;
        if ($strength <= 0 || $dose === '' || $dose !== $strengthUnit) {
            return null;
        }

        $volume = (float) $medicine->strength_volume;
        if ($volume > 0 && $this->unit($medicine->strength_volume_unit) === $countable) {
            return round(($doseAmount / $strength) * $volume, 3);
        }

        if ($volume <= 0 && in_array($countable, ['tablet', 'capsule', 'patch', 'puff', 'dose', 'sachet', 'unit'], true)) {
            return round($doseAmount / $strength, 3);
        }

        return null;
    }

    private function strengthLabel(MedicineCatalogue $medicine): ?string
    {
        if ($medicine->strength_amount === null || ! $medicine->strength_unit) {
            return null;
        }
        $label = $this->number((float) $medicine->strength_amount).' '.$medicine->strength_unit;
        if ($medicine->strength_volume !== null && $medicine->strength_volume_unit) {
            $label .= ' / '.$this->number((float) $medicine->strength_volume).' '.$medicine->strength_volume_unit;
        }

        return $label;
    }

    private function assertSelectable(MedicineCatalogue $medicine): void
    {
        if ($medicine->dmd_status !== 'current') {
            throw ValidationException::withMessages([
                'medicine_id' => 'Choose a current catalogue medicine. This product is discontinued or invalid.',
            ]);
        }
        if ((bool) $medicine->is_controlled && ! $medicine->cd_schedule) {
            throw ValidationException::withMessages([
                'medicine_id' => 'This controlled medicine is missing its controlled-drug schedule and cannot be prescribed yet.',
            ]);
        }
        if (! $medicine->is_controlled && $medicine->cd_schedule) {
            throw ValidationException::withMessages([
                'medicine_id' => 'This catalogue item has inconsistent controlled-drug data and must be corrected before use.',
            ]);
        }
    }

    private function assertNoDuplicate(int $clientId, int $serviceId, int $medicineId): void
    {
        if (MARSheet::where('home_id', $serviceId)->where('client_id', $clientId)
            ->where('medicine_id', $medicineId)->where('is_deleted', 0)
            ->whereIn('mar_status', ['active', 'paused'])
            ->where(function ($query) {
                $query->whereNull('end_date')->orWhere('end_date', '>=', now()->toDateString());
            })->exists()) {
            throw ValidationException::withMessages([
                'medicine_id' => 'This client already has an active or paused prescription for that catalogue medicine.',
            ]);
        }
    }

    private function event(
        MARSheet $sheet,
        Frontend4User $actor,
        int $organisationId,
        string $type,
        ?string $reason,
        array $changes
    ): void {
        Frontend4PrescriptionEvent::create([
            'organisation_id' => $organisationId,
            'service_id' => $sheet->home_id,
            'client_id' => $sheet->client_id,
            'mar_sheet_id' => $sheet->id,
            'medicine_id' => $sheet->medicine_id,
            'actor_user_id' => $actor->id,
            'event_type' => $type,
            'effective_at' => now(),
            'reason' => $reason,
            'changes' => $changes,
            'created_at' => now(),
        ]);
    }

    private function snapshot(MARSheet $sheet): array
    {
        return collect($sheet->getAttributes())->only(array_merge(self::EDITABLE_FIELDS, [
            'medicine_id', 'medication_name', 'dosage', 'dose', 'dose_quantity', 'form',
            'unit', 'is_controlled', 'cd_schedule', 'mar_status', 'prescription_version',
        ]))->all();
    }

    private function unit($unit): string
    {
        $value = strtolower(trim((string) $unit));
        return match ($value) {
            'tablets' => 'tablet', 'capsules' => 'capsule', 'patches' => 'patch',
            'puffs' => 'puff', 'doses' => 'dose', 'sachets' => 'sachet', 'units' => 'unit',
            default => $value,
        };
    }

    private function number(float $number): string
    {
        return rtrim(rtrim(number_format($number, 4, '.', ''), '0'), '.');
    }
}
