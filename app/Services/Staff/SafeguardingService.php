<?php

namespace App\Services\Staff;

use App\Models\SafeguardingReferral;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SafeguardingService
{
    public function store(array $data, int $homeId, int $userId): SafeguardingReferral
    {
        DB::beginTransaction();
        try {
            $data['home_id'] = $homeId;
            $data['created_by'] = $userId;
            $data['reported_by'] = $userId;
            $data['reference_number'] = SafeguardingReferral::generateReferenceNumber($homeId);

            $referral = new SafeguardingReferral();
            $referral->fill($data);
            $referral->home_id = $homeId;
            $referral->created_by = $userId;
            $referral->reported_by = $userId;
            $referral->reference_number = $data['reference_number'];
            $referral->save();

            DB::commit();

            Log::info('Safeguarding referral created', [
                'action' => 'create',
                'record_id' => $referral->id,
                'reference' => $referral->reference_number,
                'home_id' => $homeId,
                'user_id' => $userId,
            ]);

            return $referral;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating safeguarding referral: ' . $e->getMessage());
            throw $e;
        }
    }

    public function update(int $id, array $data, int $homeId): ?SafeguardingReferral
    {
        $referral = SafeguardingReferral::forHome($homeId)->active()->find($id);
        if (!$referral) {
            return null;
        }

        DB::beginTransaction();
        try {
            unset($data['home_id'], $data['created_by'], $data['reported_by'], $data['reference_number'], $data['is_deleted']);
            $referral->fill($data);
            $referral->save();
            DB::commit();

            Log::info('Safeguarding referral updated', [
                'action' => 'update',
                'record_id' => $id,
                'home_id' => $homeId,
            ]);

            return $referral;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating safeguarding referral: ' . $e->getMessage());
            throw $e;
        }
    }

    public function list(int $homeId, ?string $status = null, ?string $riskLevel = null, ?string $search = null)
    {
        $query = SafeguardingReferral::forHome($homeId)
            ->active()
            ->with(['reportedByUser:id,name'])
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        if ($riskLevel) {
            $query->where('risk_level', $riskLevel);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', '%' . $search . '%')
                  ->orWhere('details_of_concern', 'like', '%' . $search . '%')
                  ->orWhere('location_of_incident', 'like', '%' . $search . '%');
            });
        }

        return $query->paginate(20);
    }

    public function details(int $id, int $homeId): ?SafeguardingReferral
    {
        return SafeguardingReferral::forHome($homeId)
            ->active()
            ->with(['reportedByUser:id,name', 'createdByUser:id,name'])
            ->find($id);
    }

    public function delete(int $id, int $homeId): bool
    {
        $referral = SafeguardingReferral::forHome($homeId)->active()->find($id);
        if (!$referral) {
            return false;
        }

        $referral->is_deleted = true;
        $referral->save();

        Log::info('Safeguarding referral deleted', [
            'action' => 'delete',
            'record_id' => $id,
            'reference' => $referral->reference_number,
            'home_id' => $homeId,
        ]);

        return true;
    }

    public function statusChange(int $id, string $newStatus, int $homeId): ?SafeguardingReferral
    {
        $referral = SafeguardingReferral::forHome($homeId)->active()->find($id);
        if (!$referral) {
            return null;
        }

        $validTransitions = [
            'reported' => 'under_investigation',
            'under_investigation' => 'safeguarding_plan',
            'safeguarding_plan' => 'closed',
        ];

        if (!isset($validTransitions[$referral->status]) || $validTransitions[$referral->status] !== $newStatus) {
            return null;
        }

        $referral->status = $newStatus;
        if ($newStatus === 'closed') {
            $referral->closed_date = now();
        }
        $referral->save();

        Log::info('Safeguarding referral status changed', [
            'action' => 'status_change',
            'record_id' => $id,
            'new_status' => $newStatus,
            'home_id' => $homeId,
        ]);

        return $referral;
    }
}
