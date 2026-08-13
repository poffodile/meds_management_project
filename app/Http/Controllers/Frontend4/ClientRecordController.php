<?php

namespace App\Http\Controllers\Frontend4;

use App\Home;
use App\Models\Frontend4ClientEvent;
use App\Models\Frontend4ClientTransferRequest;
use App\Models\Frontend4User;
use App\ServiceUser;
use App\Services\Frontend4\AccessContext;
use App\Services\Frontend4\ClientRecordService;
use App\Services\Frontend4\Permissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

/** Manager/admin maintenance for the canonical service_user identity row. */
class ClientRecordController extends F4Controller
{
    public function create(AccessContext $context)
    {
        $this->useF4Layout();
        $this->requirePermission(Permissions::MANAGE_CLIENTS);

        return Inertia::render('ClientRecord', $this->roleProps() + $this->formProps($context) + [
            'mode' => 'create',
            'client' => null,
            'events' => [],
            'pendingTransfer' => null,
        ]);
    }

    public function store(Request $request, AccessContext $context, ClientRecordService $records)
    {
        $this->requirePermission(Permissions::MANAGE_CLIENTS);
        $user = $this->user();
        $data = $this->validatedRecord($request, $context);
        $client = $records->create($data, $user, $context->organisationId(), $context->serviceId());

        return redirect()->route('frontend4.clients.show', $client->id)
            ->with('success', 'Client record created.');
    }

    public function edit(int $client, AccessContext $context)
    {
        $this->useF4Layout();
        $this->requirePermission(Permissions::MANAGE_CLIENTS);
        $record = $this->client($client);

        return Inertia::render('ClientRecord', $this->roleProps() + $this->formProps($context) + [
            'mode' => 'edit',
            'client' => collect($record->getAttributes())->only(ClientRecordService::EDITABLE_FIELDS)->all() + [
                'id' => (int) $record->id,
                'lifecycle_status' => $record->lifecycle_status ?: ((int) $record->status === 1 ? 'active' : 'inactive'),
            ],
            'events' => Frontend4ClientEvent::where('client_id', $record->id)
                ->latest('created_at')->limit(30)->get()
                ->map(fn ($event) => [
                    'id' => $event->id,
                    'type' => $event->event_type,
                    'from' => $event->from_status,
                    'to' => $event->to_status,
                    'effectiveAt' => optional($event->effective_at)->format('Y-m-d H:i'),
                    'reason' => $event->reason,
                ])->all(),
            'pendingTransfer' => Frontend4ClientTransferRequest::where('client_id', $record->id)
                ->where('status', 'pending_review')->latest('requested_at')->first(),
        ]);
    }

    public function update(Request $request, int $client, AccessContext $context, ClientRecordService $records)
    {
        $this->requirePermission(Permissions::MANAGE_CLIENTS);
        $record = $this->client($client);
        $data = $this->validatedRecord($request, $context, $record);
        $records->update($record, $data, $this->user(), $context->organisationId());

        return back()->with('success', 'Client details updated.');
    }

    public function lifecycle(Request $request, int $client, AccessContext $context, ClientRecordService $records)
    {
        $this->requirePermission(Permissions::MANAGE_CLIENTS);
        $data = $request->validate([
            'lifecycle_status' => ['required', Rule::in(ClientRecordService::STATUSES)],
            'effective_at' => ['required', 'date', 'before_or_equal:now'],
            'reason' => ['required', 'string', 'min:5', 'max:2000'],
        ]);
        $records->changeLifecycle(
            $this->client($client), $data['lifecycle_status'], $data['reason'],
            $data['effective_at'], $this->user(), $context->organisationId()
        );

        return back()->with('success', 'Client lifecycle updated and recorded in history.');
    }

    public function restore(Request $request, int $client, AccessContext $context, ClientRecordService $records)
    {
        $this->requirePermission(Permissions::MANAGE_CLIENTS);
        $data = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:2000']]);
        $records->restore($this->client($client), $data['reason'], $this->user(), $context->organisationId());

        return back()->with('success', 'Client record restored.');
    }

    public function transfer(Request $request, int $client, AccessContext $context, ClientRecordService $records)
    {
        $this->requirePermission(Permissions::MANAGE_CLIENTS);
        $data = $request->validate([
            'to_service_id' => ['required', 'integer'],
            'reason' => ['required', 'string', 'min:5', 'max:2000'],
        ]);
        $user = $this->user();
        $target = (int) $data['to_service_id'];
        if (! in_array($target, $context->allowedServiceIds($user, $context->organisationId()), true)) {
            abort(403, 'You do not have access to the destination service.');
        }
        $records->requestTransfer(
            $this->client($client), $target, $data['reason'], $user, $context->organisationId()
        );

        return back()->with('success', 'Transfer request recorded for reconciliation review.');
    }

    private function validatedRecord(Request $request, AccessContext $context, ?ServiceUser $client = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:150'],
            'preferred_name' => ['nullable', 'string', 'max:100'],
            'pronouns' => ['nullable', 'string', 'max:80'],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today', 'after_or_equal:1900-01-01'],
            'gender' => ['nullable', Rule::in(['M', 'F'])],
            'nhs_number' => ['nullable', 'string', 'max:20'],
            'admission_number' => ['nullable', 'string', 'max:100'],
            'start_date' => ['required', 'date', 'before_or_equal:today'],
            'room_number' => ['nullable', 'string', 'max:100'],
            'home_area_id' => ['nullable', 'integer'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:2000'],
            'primary_language' => ['nullable', 'string', 'max:100'],
            'communication_needs' => ['nullable', 'string', 'max:4000'],
            'medication_support' => ['nullable', 'string', 'max:255'],
            'capacity_consent' => ['nullable', 'string', 'max:255'],
            'key_worker' => ['nullable', 'string', 'max:255'],
            'allergies' => ['nullable', 'string', 'max:2000'],
            'allergy_reaction' => ['nullable', 'string', 'max:1000'],
            'gp_name' => ['nullable', 'string', 'max:255'],
            'gp_practice' => ['nullable', 'string', 'max:255'],
            'pharmacy_name' => ['nullable', 'string', 'max:255'],
            'pharmacy_phone' => ['nullable', 'string', 'max:40'],
            'em_name' => ['nullable', 'string', 'max:255'],
            'relationship' => ['nullable', 'string', 'max:100'],
            'em_phone' => ['nullable', 'string', 'max:40'],
        ]);

        $allowedLocations = $context->allowedLocationIds(
            $this->user(), $context->organisationId(), $context->serviceId()
        );
        if ($allowedLocations !== null && empty($data['home_area_id'])) {
            throw ValidationException::withMessages(['home_area_id' => 'Choose one of your assigned locations.']);
        }
        if (! empty($data['home_area_id']) && ! $context->validContext(
            $this->user(), $context->organisationId(), $context->serviceId(), (int) $data['home_area_id']
        )) {
            throw ValidationException::withMessages(['home_area_id' => 'Choose a location within your active service.']);
        }

        $providedNhs = trim((string) ($data['nhs_number'] ?? ''));
        $nhs = preg_replace('/\D/', '', $providedNhs);
        if ($providedNhs !== '') {
            if (! $this->validNhsNumber($nhs)) {
                throw ValidationException::withMessages(['nhs_number' => 'Enter a valid 10-digit NHS number.']);
            }
            $duplicate = ServiceUser::query()
                ->when($client, fn ($query) => $query->where('id', '!=', $client->id))
                ->whereRaw("REPLACE(REPLACE(REPLACE(nhs_number, ' ', ''), '-', ''), '.', '') = ?", [$nhs])
                ->exists();
            if ($duplicate) {
                throw ValidationException::withMessages(['nhs_number' => 'A client record with this NHS number already exists.']);
            }
            $data['nhs_number'] = $nhs;
        } else {
            $data['nhs_number'] = null;
        }

        return $data;
    }

    private function validNhsNumber(string $number): bool
    {
        if (! preg_match('/^\d{10}$/', $number)) {
            return false;
        }
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += ((int) $number[$i]) * (10 - $i);
        }
        $check = 11 - ($sum % 11);
        if ($check === 11) {
            $check = 0;
        }

        return $check !== 10 && $check === (int) $number[9];
    }

    private function client(int $id): ServiceUser
    {
        return $this->scopeFrontend4Clients(ServiceUser::query())->whereKey($id)->firstOrFail();
    }

    private function user(): Frontend4User
    {
        $user = Auth::guard('frontend4')->user();
        abort_unless($user instanceof Frontend4User, 403);

        return $user;
    }

    private function formProps(AccessContext $context): array
    {
        $user = $this->user();
        $serviceId = $context->serviceId();
        $restricted = $context->allowedLocationIds($user, $context->organisationId(), $serviceId);

        return [
            'place' => Home::whereKey($serviceId)->value('title') ?: 'Current service',
            'user' => $user->name,
            'locations' => DB::table('home_areas')->where('home_id', $serviceId)->where('is_deleted', 0)
                ->when($restricted !== null, fn ($query) => $query->whereIn('id', $restricted))
                ->orderBy('name')->get(['id', 'name']),
            'targetServices' => Home::whereIn('id', $context->allowedServiceIds($user, $context->organisationId()))
                ->where('id', '!=', $serviceId)->where('is_deleted', 0)->orderBy('title')->get(['id', 'title']),
        ];
    }
}
