<?php

namespace App\Services\Frontend4;

use App\Models\Frontend4ClientEvent;
use App\Models\Frontend4ClientTransferRequest;
use App\Models\Frontend4User;
use App\ServiceUser;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClientRecordService
{
    public const STATUSES = ['active', 'inactive', 'discharged', 'deceased', 'archived'];

    public const EDITABLE_FIELDS = [
        'name', 'preferred_name', 'pronouns', 'date_of_birth', 'gender',
        'nhs_number', 'admission_number', 'start_date', 'room_number', 'home_area_id',
        'email', 'mobile', 'address', 'primary_language', 'communication_needs',
        'medication_support', 'capacity_consent', 'key_worker',
        'allergies', 'allergy_reaction', 'gp_name', 'gp_practice',
        'pharmacy_name', 'pharmacy_phone', 'em_name', 'relationship', 'em_phone',
    ];

    public function create(array $data, Frontend4User $actor, int $organisationId, int $serviceId): ServiceUser
    {
        return DB::transaction(function () use ($data, $actor, $organisationId, $serviceId) {
            $client = new ServiceUser();
            $client->forceFill(Arr::only($data, self::EDITABLE_FIELDS) + [
                'home_id' => $serviceId,
                'lifecycle_status' => 'active',
                'lifecycle_changed_at' => now(),
                'status' => 1,
                'is_deleted' => 0,
                'frontend4_created_by' => $actor->id,
                'frontend4_updated_by' => $actor->id,
            ]);
            $client->save();

            $this->event($client, $actor, $organisationId, 'created', null, 'active', $data['start_date'] ?? now(), null, [
                'fields_recorded' => array_keys(Arr::where(Arr::only($data, self::EDITABLE_FIELDS), fn ($value) => $value !== null && $value !== '')),
            ]);

            return $client;
        });
    }

    public function update(ServiceUser $client, array $data, Frontend4User $actor, int $organisationId): ServiceUser
    {
        if ($client->lifecycle_status === 'archived') {
            throw ValidationException::withMessages(['client' => 'Restore this client before editing their record.']);
        }

        return DB::transaction(function () use ($client, $data, $actor, $organisationId) {
            $before = Arr::only($client->getAttributes(), self::EDITABLE_FIELDS);
            $client->forceFill(Arr::only($data, self::EDITABLE_FIELDS));
            $client->frontend4_updated_by = $actor->id;
            $client->save();

            $changed = [];
            foreach ($client->getChanges() as $field => $value) {
                if (in_array($field, self::EDITABLE_FIELDS, true)) {
                    $changed[$field] = ['from' => $before[$field] ?? null, 'to' => $value];
                }
            }
            if ($changed !== []) {
                $this->event($client, $actor, $organisationId, 'profile_updated', null, null, now(), null, $changed);
            }

            return $client;
        });
    }

    public function changeLifecycle(
        ServiceUser $client,
        string $target,
        string $reason,
        $effectiveAt,
        Frontend4User $actor,
        int $organisationId
    ): ServiceUser {
        if (! in_array($target, self::STATUSES, true)) {
            throw ValidationException::withMessages(['lifecycle_status' => 'Select a valid client status.']);
        }
        $from = $client->lifecycle_status ?: ((int) $client->status === 1 ? 'active' : 'inactive');
        if ($from === $target) {
            throw ValidationException::withMessages(['lifecycle_status' => 'The client already has that status.']);
        }
        if ($from === 'archived') {
            throw ValidationException::withMessages(['lifecycle_status' => 'Use restore to reopen an archived client record.']);
        }

        return DB::transaction(function () use ($client, $from, $target, $reason, $effectiveAt, $actor, $organisationId) {
            if ($target === 'archived') {
                $client->lifecycle_status_before_archive = $from;
            }
            $client->lifecycle_status = $target;
            $client->lifecycle_changed_at = $effectiveAt;
            $client->status = $target === 'active' ? 1 : 0;
            $client->is_deleted = $target === 'archived' ? 1 : 0;
            $client->frontend4_updated_by = $actor->id;
            $client->save();

            $eventType = $target === 'archived' ? 'archived'
                : ($from === 'deceased' ? 'lifecycle_corrected'
                    : ($from === 'discharged' && $target === 'active' ? 'readmitted' : 'lifecycle_changed'));
            $this->event($client, $actor, $organisationId, $eventType, $from, $target, $effectiveAt, $reason);

            return $client;
        });
    }

    public function restore(ServiceUser $client, string $reason, Frontend4User $actor, int $organisationId): ServiceUser
    {
        if ($client->lifecycle_status !== 'archived') {
            throw ValidationException::withMessages(['client' => 'Only an archived client can be restored.']);
        }

        return DB::transaction(function () use ($client, $reason, $actor, $organisationId) {
            $target = $client->lifecycle_status_before_archive;
            if (! in_array($target, ['active', 'inactive', 'discharged', 'deceased'], true)) {
                $target = 'inactive';
            }
            $client->lifecycle_status = $target;
            $client->lifecycle_status_before_archive = null;
            $client->lifecycle_changed_at = now();
            $client->status = $target === 'active' ? 1 : 0;
            $client->is_deleted = 0;
            $client->frontend4_updated_by = $actor->id;
            $client->save();
            $this->event($client, $actor, $organisationId, 'restored', 'archived', $target, now(), $reason);

            return $client;
        });
    }

    public function requestTransfer(
        ServiceUser $client,
        int $toServiceId,
        string $reason,
        Frontend4User $actor,
        int $organisationId
    ): Frontend4ClientTransferRequest {
        $status = $client->lifecycle_status ?: ((int) $client->status === 1 ? 'active' : 'inactive');
        if (! in_array($status, ['active', 'inactive'], true)) {
            throw ValidationException::withMessages(['to_service_id' => 'Only an active or inactive client can be transferred.']);
        }
        if ((int) $client->home_id === $toServiceId) {
            throw ValidationException::withMessages(['to_service_id' => 'Choose a different service.']);
        }
        if (Frontend4ClientTransferRequest::where('client_id', $client->id)->where('status', 'pending_review')->exists()) {
            throw ValidationException::withMessages(['to_service_id' => 'This client already has a transfer awaiting review.']);
        }

        return DB::transaction(function () use ($client, $toServiceId, $reason, $actor, $organisationId) {
            $request = Frontend4ClientTransferRequest::create([
                'organisation_id' => $organisationId,
                'client_id' => $client->id,
                'from_service_id' => $client->home_id,
                'to_service_id' => $toServiceId,
                'status' => 'pending_review',
                'reason' => $reason,
                'requested_by' => $actor->id,
                'requested_at' => now(),
            ]);
            $this->event($client, $actor, $organisationId, 'transfer_requested', null, null, now(), $reason, [
                'transfer_request_id' => $request->id,
                'from_service_id' => (int) $client->home_id,
                'to_service_id' => $toServiceId,
            ]);

            return $request;
        });
    }

    private function event(
        ServiceUser $client,
        Frontend4User $actor,
        int $organisationId,
        string $eventType,
        ?string $from,
        ?string $to,
        $effectiveAt,
        ?string $reason = null,
        ?array $changes = null
    ): void {
        Frontend4ClientEvent::create([
            'organisation_id' => $organisationId,
            'service_id' => $client->home_id,
            'client_id' => $client->id,
            'actor_user_id' => $actor->id,
            'event_type' => $eventType,
            'from_status' => $from,
            'to_status' => $to,
            'effective_at' => $effectiveAt,
            'reason' => $reason,
            'changes' => $changes,
            'created_at' => now(),
        ]);
    }
}
