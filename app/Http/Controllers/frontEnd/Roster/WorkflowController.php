<?php

namespace App\Http\Controllers\frontEnd\Roster;

use App\Http\Controllers\Controller;
use App\Services\WorkflowEngineService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkflowController extends Controller
{
    private function homeId(): int
    {
        return (int) explode(',', Auth::user()->home_id)[0];
    }

    public function index()
    {
        return view('frontEnd.roster.workflow.index');
    }

    public function list(WorkflowEngineService $service)
    {
        $homeId = $this->homeId();
        $workflows = $service->listForHome($homeId);
        $stats = $service->getStats($homeId);

        return response()->json(['status' => true, 'workflows' => $workflows, 'stats' => $stats]);
    }

    public function store(Request $request, WorkflowEngineService $service)
    {
        $request->validate([
            'workflow_name' => 'required|string|max:255',
            'category' => 'required|in:scheduling,compliance,clinical,training,hr,engagement,reporting',
            'trigger_type' => 'required|in:scheduled,condition,event',
            'trigger_config' => 'required|string',
            'action_type' => 'required|in:send_notification,send_email',
            'action_config' => 'required|string',
            'cooldown_hours' => 'required|integer|min:1|max:168',
        ]);

        $triggerConfig = json_decode($request->trigger_config, true);
        $actionConfig = json_decode($request->action_config, true);

        if (!is_array($triggerConfig) || !is_array($actionConfig)) {
            return response()->json(['status' => false, 'message' => 'Invalid JSON in config fields.'], 422);
        }

        $data = $request->only(['workflow_name', 'category', 'trigger_type', 'action_type', 'cooldown_hours']);
        $data['trigger_config'] = $triggerConfig;
        $data['action_config'] = $actionConfig;

        try {
            $workflow = $service->store($data, $this->homeId(), Auth::user()->id);
            return response()->json(['status' => true, 'workflow' => $workflow]);
        } catch (\RuntimeException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function update(Request $request, WorkflowEngineService $service)
    {
        $request->validate([
            'id' => 'required|integer',
            'workflow_name' => 'required|string|max:255',
            'category' => 'required|in:scheduling,compliance,clinical,training,hr,engagement,reporting',
            'trigger_type' => 'required|in:scheduled,condition,event',
            'trigger_config' => 'required|string',
            'action_type' => 'required|in:send_notification,send_email',
            'action_config' => 'required|string',
            'cooldown_hours' => 'required|integer|min:1|max:168',
        ]);

        $triggerConfig = json_decode($request->trigger_config, true);
        $actionConfig = json_decode($request->action_config, true);

        if (!is_array($triggerConfig) || !is_array($actionConfig)) {
            return response()->json(['status' => false, 'message' => 'Invalid JSON in config fields.'], 422);
        }

        $data = $request->only(['workflow_name', 'category', 'trigger_type', 'action_type', 'cooldown_hours']);
        $data['trigger_config'] = $triggerConfig;
        $data['action_config'] = $actionConfig;

        try {
            $workflow = $service->update($request->id, $data, $this->homeId());
            return response()->json(['status' => true, 'workflow' => $workflow]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => false, 'message' => 'Workflow not found.'], 404);
        } catch (\RuntimeException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function toggle(Request $request, WorkflowEngineService $service)
    {
        $request->validate(['id' => 'required|integer']);

        try {
            $workflow = $service->toggleActive($request->id, $this->homeId());
            return response()->json(['status' => true, 'workflow' => $workflow]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => false, 'message' => 'Workflow not found.'], 404);
        } catch (\RuntimeException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function delete(Request $request, WorkflowEngineService $service)
    {
        $request->validate(['id' => 'required|integer']);

        try {
            $service->delete($request->id, $this->homeId());
            return response()->json(['status' => true]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => false, 'message' => 'Workflow not found.'], 404);
        } catch (\RuntimeException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function runAll(WorkflowEngineService $service)
    {
        $results = $service->evaluateAllForHome($this->homeId());
        return response()->json(['status' => true, 'results' => $results]);
    }

    public function runSingle(Request $request, WorkflowEngineService $service)
    {
        $request->validate(['id' => 'required|integer']);

        try {
            $result = $service->runSingleForHome($request->id, $this->homeId());
            return response()->json(['status' => true, 'result' => $result]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => false, 'message' => 'Workflow not found.'], 404);
        } catch (\RuntimeException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function executions(WorkflowEngineService $service)
    {
        $logs = $service->getExecutionLogs($this->homeId());
        return response()->json(['status' => true, 'executions' => $logs]);
    }

    public function templates(WorkflowEngineService $service)
    {
        $templates = $service->getTemplates($this->homeId());
        return response()->json(['status' => true, 'templates' => $templates]);
    }

    public function installTemplate(Request $request, WorkflowEngineService $service)
    {
        $request->validate([
            'template_id' => 'required|string|max:50',
        ]);

        if (!in_array($request->template_id, \App\Services\WorkflowTemplateRegistry::validIds())) {
            return response()->json(['status' => false, 'message' => 'Invalid template.'], 422);
        }

        try {
            $workflow = $service->installTemplate($request->template_id, $this->homeId(), Auth::user()->id);

            $needsConfig = $workflow->action_type === 'send_email' && empty($workflow->action_config['recipients']);

            return response()->json([
                'status' => true,
                'workflow' => $workflow,
                'needs_config' => $needsConfig,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
