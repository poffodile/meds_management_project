<?php

namespace App\Http\Controllers\Frontend4;

use App\Home;
use App\Models\Frontend4User;
use App\Models\MARSheet;
use App\Models\MedicineCatalogue;
use App\ServiceUser;
use App\Services\Frontend4\AccessContext;
use App\Services\Frontend4\Permissions;
use App\Services\Frontend4\PrescriptionRecordService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

/** Catalogue-backed Frontend 4 prescription creation and lifecycle. */
class PrescriptionController extends F4Controller
{
    public function create(int $client, AccessContext $context)
    {
        $this->useF4Layout();
        $this->requirePermission(Permissions::MANAGE_PRESCRIPTION);
        $record = $this->client($client);
        $this->assertActiveClient($record);

        return Inertia::render('PrescriptionRecord', $this->roleProps() + $this->formProps($record, $context) + [
            'mode' => 'create',
            'prescription' => null,
            'medicine' => null,
        ]);
    }

    public function store(
        Request $request,
        int $client,
        AccessContext $context,
        PrescriptionRecordService $records
    ) {
        $this->requirePermission(Permissions::MANAGE_PRESCRIPTION);
        $record = $this->client($client);
        $this->assertActiveClient($record);
        $data = $this->validatedPrescription($request, true);
        $medicine = MedicineCatalogue::whereKey($data['medicine_id'])->firstOrFail();
        $sheet = $records->create(
            $record, $medicine, $data, $this->user(), $context->organisationId(), $context->serviceId()
        );

        return redirect()->route('frontend4.clients.show', $record->id)
            ->with('success', 'Catalogue-backed prescription created as version '.$sheet->prescription_version.'.');
    }

    public function edit(int $client, int $sheet, AccessContext $context)
    {
        $this->useF4Layout();
        $this->requirePermission(Permissions::MANAGE_PRESCRIPTION);
        $record = $this->client($client);
        $prescription = $this->sheet($record, $sheet);
        if (! $prescription->medicine_id) {
            throw ValidationException::withMessages([
                'medicine_id' => 'This legacy prescription must be reconciled to a catalogue medicine before it can be amended.',
            ]);
        }

        return Inertia::render('PrescriptionRecord', $this->roleProps() + $this->formProps($record, $context) + [
            'mode' => 'edit',
            'prescription' => collect($prescription->getAttributes())
                ->only(PrescriptionRecordService::EDITABLE_FIELDS)->all() + [
                    'id' => (int) $prescription->id,
                    'medicine_id' => (int) $prescription->medicine_id,
                    'version' => (int) $prescription->prescription_version,
                    'amendment_reason' => '',
                ],
            'medicine' => $this->medicineProps($prescription->medicine),
        ]);
    }

    public function update(
        Request $request,
        int $client,
        int $sheet,
        AccessContext $context,
        PrescriptionRecordService $records
    ) {
        $this->requirePermission(Permissions::MANAGE_PRESCRIPTION);
        $record = $this->client($client);
        $prescription = $this->sheet($record, $sheet);
        $data = $this->validatedPrescription($request, false);
        $reason = $request->validate([
            'amendment_reason' => ['required', 'string', 'min:5', 'max:2000'],
        ])['amendment_reason'];
        $records->amend($prescription, $data, $reason, $this->user(), $context->organisationId());

        return redirect()->route('frontend4.clients.show', $record->id)
            ->with('success', 'Prescription amendment recorded in append-only history.');
    }

    public function changeStatus(
        Request $request,
        int $client,
        int $sheet,
        AccessContext $context,
        PrescriptionRecordService $records
    ) {
        $this->requirePermission(Permissions::MANAGE_PRESCRIPTION);
        $data = $request->validate([
            'action' => ['required', Rule::in(['pause', 'resume', 'stop'])],
            'reason' => ['required', 'string', 'min:3', 'max:1000'],
        ]);
        $record = $this->client($client);
        $records->changeStatus(
            $this->sheet($record, $sheet), $data['action'], $data['reason'],
            $this->user(), $context->organisationId()
        );

        return back()->with('success', 'Prescription status updated and recorded.');
    }

    public function catalogue(Request $request)
    {
        $this->requirePermission(Permissions::MANAGE_PRESCRIPTION);
        $query = trim((string) $request->query('q'));
        if (mb_strlen($query) < 2) {
            return response()->json(['medicines' => []]);
        }

        $medicines = MedicineCatalogue::selectable()
            ->where(function ($builder) use ($query) {
                $builder->where('name', 'like', '%'.$query.'%')
                    ->orWhere('dmd_code', $query);
            })
            ->orderBy('name')->limit(30)->get()
            ->map(fn (MedicineCatalogue $medicine) => $this->medicineProps($medicine))->values();

        return response()->json(['medicines' => $medicines]);
    }

    private function validatedPrescription(Request $request, bool $creating): array
    {
        $request->merge([
            'as_required' => $request->boolean('as_required'),
            'time_slots' => $this->timeSlots($request->input('time_slots')),
        ]);
        $rules = [
            'medication_name_as_written' => ['nullable', 'string', 'max:255'],
            'dose_amount' => ['required', 'numeric', 'gt:0', 'max:999999'],
            'dose_unit' => ['required', 'string', 'max:30'],
            'route' => ['required', 'string', 'max:100'],
            'frequency' => ['required', 'string', 'max:255'],
            'time_slots' => ['array', 'max:12'],
            'time_slots.*' => ['date_format:H:i'],
            'as_required' => ['boolean'],
            'prn_details' => ['nullable', 'string', 'max:4000'],
            'prn_max_daily' => ['nullable', 'integer', 'min:1', 'max:24'],
            'prn_min_interval_hours' => ['nullable', 'numeric', 'min:0.25', 'max:168'],
            'reason_for_medication' => ['nullable', 'string', 'max:1000'],
            'administration_instructions' => ['nullable', 'string', 'max:500'],
            'prescriber' => ['required', 'string', 'max:255'],
            'pharmacy' => ['nullable', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'review_due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'prescription_source' => ['required', Rule::in([
                'paper_prescription', 'gp_record', 'hospital_discharge', 'pharmacy_label', 'other',
            ])],
        ];
        if ($creating) {
            $rules['medicine_id'] = ['required', 'integer', Rule::exists('medicine_catalogue', 'id')];
        }

        $validator = validator($request->all(), $rules);
        $validator->after(function ($validator) use ($request) {
            $prn = $request->boolean('as_required');
            if (! $prn && count($this->timeSlots($request->input('time_slots'))) === 0) {
                $validator->errors()->add('time_slots', 'Add at least one administration time for a scheduled prescription.');
            }
            if ($prn && ($request->input('prn_max_daily') === null || $request->input('prn_min_interval_hours') === null)) {
                $validator->errors()->add('prn_details', 'PRN prescriptions require a maximum in 24 hours and a minimum interval.');
            }
        });

        return $validator->validate();
    }

    private function timeSlots($value): array
    {
        $values = is_array($value) ? $value : preg_split('/[,\s]+/', trim((string) $value));
        return collect($values)->map(fn ($slot) => trim((string) $slot))->filter()->unique()->sort()->values()->all();
    }

    private function client(int $id): ServiceUser
    {
        return $this->scopeFrontend4Clients(ServiceUser::query())->where('is_deleted', 0)->whereKey($id)->firstOrFail();
    }

    private function sheet(ServiceUser $client, int $id): MARSheet
    {
        return MARSheet::with('medicine')->where('home_id', $client->home_id)
            ->where('client_id', $client->id)->where('is_deleted', 0)->whereKey($id)->firstOrFail();
    }

    private function assertActiveClient(ServiceUser $client): void
    {
        $active = $client->lifecycle_status === 'active'
            || ($client->lifecycle_status === null && (int) $client->status === 1);
        if (! $active) {
            throw ValidationException::withMessages([
                'client' => 'A new prescription can only be created for an active client.',
            ]);
        }
    }

    private function user(): Frontend4User
    {
        $user = Auth::guard('frontend4')->user();
        abort_unless($user instanceof Frontend4User, 403);
        return $user;
    }

    private function formProps(ServiceUser $client, AccessContext $context): array
    {
        return [
            'client' => ['id' => (int) $client->id, 'name' => $client->name],
            'place' => Home::whereKey($context->serviceId())->value('title') ?: 'Current service',
            'user' => $this->user()->name,
            'catalogueLoaded' => MedicineCatalogue::selectable()->exists(),
        ];
    }

    private function medicineProps(?MedicineCatalogue $medicine): ?array
    {
        if (! $medicine) {
            return null;
        }
        $strength = $medicine->strength_amount !== null
            ? rtrim(rtrim((string) $medicine->strength_amount, '0'), '.').' '.$medicine->strength_unit
            : null;
        if ($strength && $medicine->strength_volume !== null) {
            $strength .= ' / '.rtrim(rtrim((string) $medicine->strength_volume, '0'), '.').' '.$medicine->strength_volume_unit;
        }

        return [
            'id' => (int) $medicine->id,
            'name' => $medicine->name,
            'dmdCode' => $medicine->dmd_code,
            'conceptLevel' => $medicine->dmd_concept_level,
            'form' => $medicine->form,
            'route' => $medicine->default_route,
            'countableUnit' => $medicine->countable_unit,
            'strength' => $strength,
            'isControlled' => (bool) $medicine->is_controlled,
            'cdSchedule' => $medicine->cd_schedule,
            'isLocal' => (bool) $medicine->is_local,
        ];
    }
}
