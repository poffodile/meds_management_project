<?php

namespace App\Http\Controllers\Frontend4;

use App\Models\Frontend4FollowUpTask;
use App\Models\Frontend4Handover;
use App\Models\Frontend4HandoverItem;
use App\Models\Frontend4MedicationIncident;
use App\Models\Frontend4User;
use App\ServiceUser;
use App\Services\Frontend4\AccessContext;
use App\Services\Frontend4\HandoverIncidentService;
use App\Services\Frontend4\Permissions;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class HandoverController extends F4Controller
{
    public function index(AccessContext $context)
    {
        $this->useF4Layout();
        $this->requirePermission(Permissions::VIEW_HANDOVER);
        $user = $this->user();
        $organisationId = $context->organisationId();
        $serviceId = $context->serviceId();
        $allowedClients = $context->allowedClientIds($user);

        $handovers = Frontend4Handover::forContext($organisationId, $serviceId)
            ->with(['items.tasks', 'acknowledgements'])->orderByDesc('shift_end')->limit(20)->get()
            ->map(fn (Frontend4Handover $handover) => [
                'id' => $handover->id, 'status' => $handover->status,
                'shiftStart' => $handover->shift_start->format('d M Y H:i'),
                'shiftEnd' => $handover->shift_end->format('d M Y H:i'),
                'generalNotes' => $handover->general_notes,
                'submittedAt' => $handover->submitted_at?->format('d M Y H:i'),
                'acknowledgedByMe' => $handover->acknowledgements->contains('user_id', $user->id),
                'acknowledgementCount' => $handover->acknowledgements->count(),
                'items' => $handover->items->map(fn ($item) => [
                    'id' => $item->id, 'category' => $item->category, 'priority' => $item->priority,
                    'summary' => $item->summary, 'detail' => $item->detail,
                    'occurredAt' => $item->occurred_at?->format('d M Y H:i'),
                    'requiresAction' => $item->requires_action,
                    'hasOpenTask' => $item->tasks->contains('status', 'open'),
                ])->values(),
            ])->values();

        $tasks = Frontend4FollowUpTask::forContext($organisationId, $serviceId)
            ->where('status', 'open')->orderBy('due_at')->get()->map(fn ($task) => [
                'id' => $task->id, 'title' => $task->title, 'instructions' => $task->instructions,
                'taskType' => $task->task_type, 'priority' => $task->priority,
                'ownerUserId' => $task->owner_user_id, 'dueAt' => $task->due_at->format('d M Y H:i'),
                'escalateAt' => $task->escalate_at->format('d M Y H:i'),
                'overdue' => $task->due_at->isPast(), 'escalated' => $task->escalate_at->isPast(),
                'canComplete' => (int) $task->owner_user_id === (int) $user->id
                    || app(Permissions::class)->allows($this->role(), Permissions::MANAGE_HANDOVER),
            ])->values();

        $incidents = Frontend4MedicationIncident::forContext($organisationId, $serviceId)
            ->orderByRaw("status = 'closed'")->orderByDesc('reported_at')->limit(30)->get()->map(fn ($incident) => [
                'id' => $incident->id, 'category' => $incident->category, 'severity' => $incident->severity,
                'description' => $incident->description, 'immediateAction' => $incident->immediate_action,
                'status' => $incident->status, 'reportedAt' => $incident->reported_at->format('d M Y H:i'),
                'outcome' => $incident->outcome, 'learning' => $incident->learning,
            ])->values();

        return Inertia::render('Handover', $this->roleProps() + [
            'place' => $this->placeName($serviceId), 'user' => $user->name,
            'handovers' => $handovers, 'tasks' => $tasks, 'incidents' => $incidents,
            'staff' => $this->staff($context, $serviceId),
            'clients' => ServiceUser::whereIn('id', $allowedClients)->orderBy('name')->get(['id', 'name']),
            'draftDefaults' => ['shift_start' => now()->subHours(12)->format('Y-m-d\TH:i'), 'shift_end' => now()->format('Y-m-d\TH:i')],
        ]);
    }

    public function createDraft(Request $request, AccessContext $context, HandoverIncidentService $service)
    {
        $this->requirePermission(Permissions::RECORD_HANDOVER);
        $data = $request->validate(['shift_start' => ['required', 'date'], 'shift_end' => ['required', 'date', 'after:shift_start']]);
        $user = $this->user();
        $handover = $service->createDraft($context->organisationId(), $context->serviceId(), $context->locationId(),
            Carbon::parse($data['shift_start']), Carbon::parse($data['shift_end']), $user, $context->allowedClientIds($user));
        return redirect()->route('frontend4.handover')->with('success', 'Automatic handover draft created with '.$handover->items->count().' linked source records.');
    }

    public function updateDraft(Request $request, int $handover, AccessContext $context, HandoverIncidentService $service)
    {
        $this->requirePermission(Permissions::RECORD_HANDOVER);
        $data = $request->validate(['general_notes' => ['nullable', 'string', 'max:8000']]);
        $service->updateDraft($this->handover($handover, $context), $data['general_notes'] ?? null, $this->user());
        return back()->with('success', 'Draft notes saved. Source records were not changed.');
    }

    public function submit(int $handover, AccessContext $context, HandoverIncidentService $service)
    {
        $this->requirePermission(Permissions::RECORD_HANDOVER);
        $service->submit($this->handover($handover, $context), $this->user());
        return back()->with('success', 'Handover submitted to the incoming shift.');
    }

    public function acknowledge(int $handover, AccessContext $context, HandoverIncidentService $service)
    {
        $this->requirePermission(Permissions::ACKNOWLEDGE_HANDOVER);
        $service->acknowledge($this->handover($handover, $context), $this->user());
        return back()->with('success', 'Receipt acknowledged. Follow-up work remains open until completed separately.');
    }

    public function createTask(Request $request, AccessContext $context, HandoverIncidentService $service)
    {
        $this->requirePermission(Permissions::RECORD_HANDOVER);
        $user = $this->user();
        $allowedClients = $context->allowedClientIds($user);
        $staffIds = collect($this->staff($context, $context->serviceId()))->pluck('id')->all();
        $data = $request->validate([
            'handover_item_id' => ['nullable', 'integer'], 'client_id' => ['nullable', 'integer', Rule::in($allowedClients)],
            'task_type' => ['required', Rule::in(['clinical_follow_up', 'professional_advice', 'stock', 'incident_action', 'other'])],
            'title' => ['required', 'string', 'max:255'], 'instructions' => ['nullable', 'string', 'max:4000'],
            'owner_user_id' => ['required', 'integer', Rule::in($staffIds)],
            'priority' => ['required', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'due_at' => ['required', 'date', 'after:now'], 'escalate_at' => ['required', 'date', 'after_or_equal:due_at'],
        ]);
        if (! empty($data['handover_item_id'])) {
            $item = $this->item((int) $data['handover_item_id'], $context);
            $data['client_id'] = $item->client_id;
        }
        $service->createTask($data, $user, $context->organisationId(), $context->serviceId(), $context->locationId());
        return back()->with('success', 'Follow-up task assigned with a deadline and escalation time.');
    }

    public function completeTask(Request $request, int $task, AccessContext $context, HandoverIncidentService $service)
    {
        $this->requirePermission(Permissions::COMPLETE_HANDOVER_TASK);
        $row = Frontend4FollowUpTask::forContext($context->organisationId(), $context->serviceId())->whereKey($task)->firstOrFail();
        $user = $this->user();
        $canManage = app(Permissions::class)->allows($this->role(), Permissions::MANAGE_HANDOVER);
        abort_unless((int) $row->owner_user_id === (int) $user->id || $canManage, 403);
        $data = $request->validate(['completion_note' => ['required', 'string', 'min:3', 'max:4000']]);
        $service->completeTask($row, $data['completion_note'], $user);
        return back()->with('success', 'Task completed. The handover acknowledgement record remains unchanged.');
    }

    public function reportIncident(Request $request, AccessContext $context, HandoverIncidentService $service)
    {
        $this->requirePermission(Permissions::REPORT_MEDICATION_INCIDENT);
        $user = $this->user();
        $data = $request->validate([
            'handover_item_id' => ['nullable', 'integer'],
            'client_id' => ['nullable', 'integer', Rule::in($context->allowedClientIds($user))],
            'category' => ['required', Rule::in(['administration', 'omission', 'controlled_drug', 'stock', 'prescription', 'other'])],
            'severity' => ['required', Rule::in(['low', 'moderate', 'high', 'critical'])],
            'description' => ['required', 'string', 'min:10', 'max:8000'],
            'immediate_action' => ['required', 'string', 'min:3', 'max:8000'],
        ]);
        if (! empty($data['handover_item_id'])) {
            $item = $this->item((int) $data['handover_item_id'], $context);
            $data = $data + ['source_type' => $item->source_type, 'source_id' => $item->source_id, 'mar_sheet_id' => $item->mar_sheet_id];
            $data['client_id'] = $item->client_id;
        }
        $service->reportIncident($data, $user, $context->organisationId(), $context->serviceId(), $context->locationId());
        return back()->with('success', 'Medication incident reported. It remains open for investigation.');
    }

    public function investigate(int $incident, AccessContext $context, HandoverIncidentService $service)
    {
        $this->requirePermission(Permissions::INVESTIGATE_MEDICATION_INCIDENT);
        $service->investigate($this->incident($incident, $context), $this->user());
        return back()->with('success', 'Investigation started.');
    }

    public function closeIncident(Request $request, int $incident, AccessContext $context, HandoverIncidentService $service)
    {
        $this->requirePermission(Permissions::INVESTIGATE_MEDICATION_INCIDENT);
        $data = $request->validate(['outcome' => ['required', 'string', 'min:10', 'max:8000'], 'learning' => ['required', 'string', 'min:5', 'max:8000']]);
        $service->closeIncident($this->incident($incident, $context), $data['outcome'], $data['learning'], $this->user());
        return back()->with('success', 'Incident closed with outcome and learning recorded.');
    }

    private function handover(int $id, AccessContext $context): Frontend4Handover
    { return Frontend4Handover::forContext($context->organisationId(), $context->serviceId())->whereKey($id)->firstOrFail(); }
    private function item(int $id, AccessContext $context): Frontend4HandoverItem
    { return Frontend4HandoverItem::whereKey($id)->whereHas('handover', fn ($q) => $q->forContext($context->organisationId(), $context->serviceId()))->firstOrFail(); }
    private function incident(int $id, AccessContext $context): Frontend4MedicationIncident
    { return Frontend4MedicationIncident::forContext($context->organisationId(), $context->serviceId())->whereKey($id)->firstOrFail(); }
    private function user(): Frontend4User
    { $user = Auth::guard('frontend4')->user(); abort_unless($user instanceof Frontend4User, 403); return $user; }
    protected function role(): string
    { return app(\App\Services\Frontend4\RoleResolver::class)->resolve($this->user()); }
    private function placeName(int $serviceId): string
    { return \App\Home::whereKey($serviceId)->value('title') ?: 'Current service'; }
    private function staff(AccessContext $context, int $serviceId): array
    {
        $roles = app(\App\Services\Frontend4\RoleResolver::class);
        $permissions = app(Permissions::class);
        return Frontend4User::where('status', 1)->where('is_deleted', 0)->get()->filter(fn ($staff) =>
            in_array($serviceId, $context->allowedServiceIds($staff, $context->organisationId()), true)
            && $permissions->allows($roles->resolve($staff), Permissions::COMPLETE_HANDOVER_TASK)
        )->map(fn ($staff) => ['id' => (int) $staff->id, 'name' => $staff->name])->sortBy('name')->values()->all();
    }
}
