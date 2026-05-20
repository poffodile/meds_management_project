<?php

namespace Tests\Feature;

use App\Models\AutomatedWorkflow;
use App\Models\WorkflowExecutionLog;
use App\Services\WorkflowEngineService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WorkflowEngineTest extends TestCase
{
    protected $adminUser;
    protected $homeId = 8;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = \App\User::where('user_name', 'komal')->first();
    }

    protected function authHeaders(): array
    {
        return [];
    }

    protected function createWorkflow(array $overrides = []): AutomatedWorkflow
    {
        $defaults = [
            'workflow_name' => 'Test Workflow',
            'category' => 'scheduling',
            'trigger_type' => 'event',
            'trigger_config' => ['entity' => 'shifts', 'status' => 'unfilled', 'min_count' => 1],
            'action_type' => 'send_notification',
            'action_config' => ['message' => 'Test alert message', 'is_sticky' => false],
            'cooldown_hours' => 24,
            'is_active' => 1,
            'home_id' => $this->homeId,
            'created_by' => $this->adminUser->id,
        ];

        $data = array_merge($defaults, $overrides);
        $wf = new AutomatedWorkflow();
        $wf->fill($data);
        $wf->home_id = $data['home_id'];
        $wf->created_by = $data['created_by'];
        if (isset($data['is_active'])) $wf->is_active = $data['is_active'];
        if (isset($data['last_triggered_at'])) $wf->last_triggered_at = $data['last_triggered_at'];
        if (isset($data['next_run_date'])) $wf->next_run_date = $data['next_run_date'];
        $wf->save();

        return $wf;
    }

    protected function tearDown(): void
    {
        AutomatedWorkflow::where('home_id', $this->homeId)->forceDelete();
        AutomatedWorkflow::where('home_id', 999)->forceDelete();
        WorkflowExecutionLog::where('home_id', $this->homeId)->delete();
        WorkflowExecutionLog::where('home_id', 999)->delete();
        DB::table('notification')->where('notification_event_type_id', 25)->delete();
        parent::tearDown();
    }

    public function test_workflow_page_loads()
    {
        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->get('/roster/workflows');

        $response->assertStatus(200);
        $response->assertSee('Workflow Automation');
    }

    public function test_workflow_list_returns_empty_array()
    {
        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->get('/roster/workflows/list');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertTrue($data['status']);
        $this->assertEmpty($data['workflows']);
    }

    public function test_create_workflow_success()
    {
        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->post('/roster/workflows/store', [
                'workflow_name' => 'Shift Alert',
                'category' => 'scheduling',
                'trigger_type' => 'event',
                'trigger_config' => json_encode(['entity' => 'shifts', 'status' => 'unfilled', 'min_count' => 3]),
                'action_type' => 'send_notification',
                'action_config' => json_encode(['message' => 'Unfilled shifts detected', 'is_sticky' => true]),
                'cooldown_hours' => 24,
            ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertTrue($data['status']);
        $this->assertEquals('Shift Alert', $data['workflow']['workflow_name']);

        $this->assertDatabaseHas('automated_workflows', [
            'workflow_name' => 'Shift Alert',
            'home_id' => $this->homeId,
        ]);
    }

    public function test_create_validates_required_fields()
    {
        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->postJson('/roster/workflows/store', [
                'category' => 'scheduling',
            ]);

        $response->assertStatus(422);
    }

    public function test_create_validates_trigger_type()
    {
        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->postJson('/roster/workflows/store', [
                'workflow_name' => 'Bad Trigger',
                'category' => 'scheduling',
                'trigger_type' => 'invalid_type',
                'trigger_config' => json_encode(['entity' => 'shifts']),
                'action_type' => 'send_notification',
                'action_config' => json_encode(['message' => 'test']),
                'cooldown_hours' => 24,
            ]);

        $response->assertStatus(422);
    }

    public function test_create_validates_action_type()
    {
        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->postJson('/roster/workflows/store', [
                'workflow_name' => 'Bad Action',
                'category' => 'scheduling',
                'trigger_type' => 'event',
                'trigger_config' => json_encode(['entity' => 'shifts', 'status' => 'unfilled', 'min_count' => 1]),
                'action_type' => 'create_task',
                'action_config' => json_encode(['message' => 'test']),
                'cooldown_hours' => 24,
            ]);

        $response->assertStatus(422);
    }

    public function test_update_workflow_success()
    {
        $wf = $this->createWorkflow();

        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->post('/roster/workflows/update', [
                'id' => $wf->id,
                'workflow_name' => 'Updated Name',
                'category' => 'compliance',
                'trigger_type' => 'event',
                'trigger_config' => json_encode(['entity' => 'shifts', 'status' => 'unfilled', 'min_count' => 5]),
                'action_type' => 'send_notification',
                'action_config' => json_encode(['message' => 'Updated message', 'is_sticky' => false]),
                'cooldown_hours' => 48,
            ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json('status'));

        $wf->refresh();
        $this->assertEquals('Updated Name', $wf->workflow_name);
        $this->assertEquals('compliance', $wf->category);
        $this->assertEquals(48, $wf->cooldown_hours);
    }

    public function test_toggle_active()
    {
        $wf = $this->createWorkflow(['is_active' => 1]);

        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->post('/roster/workflows/toggle', ['id' => $wf->id]);

        $response->assertStatus(200);
        $wf->refresh();
        $this->assertFalse($wf->is_active);

        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->post('/roster/workflows/toggle', ['id' => $wf->id]);

        $wf->refresh();
        $this->assertTrue($wf->is_active);
    }

    public function test_delete_workflow_soft_deletes()
    {
        $wf = $this->createWorkflow();

        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->post('/roster/workflows/delete', ['id' => $wf->id]);

        $response->assertStatus(200);
        $wf->refresh();
        $this->assertEquals(1, $wf->is_deleted);

        $listResponse = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->get('/roster/workflows/list');

        $this->assertEmpty($listResponse->json('workflows'));
    }

    public function test_home_isolation()
    {
        $this->createWorkflow(['workflow_name' => 'Home 8 Workflow']);

        $this->createWorkflow(['workflow_name' => 'Other Home Workflow', 'home_id' => 999]);

        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->get('/roster/workflows/list');

        $workflows = $response->json('workflows');
        $this->assertCount(1, $workflows);
        $this->assertEquals('Home 8 Workflow', $workflows[0]['workflow_name']);
    }

    public function test_idor_cannot_update_other_home_workflow()
    {
        $otherWf = $this->createWorkflow(['workflow_name' => 'Other Home WF', 'home_id' => 999]);

        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->post('/roster/workflows/update', [
                'id' => $otherWf->id,
                'workflow_name' => 'Hacked Name',
                'category' => 'scheduling',
                'trigger_type' => 'event',
                'trigger_config' => json_encode(['entity' => 'shifts', 'status' => 'unfilled', 'min_count' => 1]),
                'action_type' => 'send_notification',
                'action_config' => json_encode(['message' => 'hacked']),
                'cooldown_hours' => 24,
            ]);

        $response->assertStatus(404);
    }

    public function test_execution_logs_return_for_home_only()
    {
        $wf = $this->createWorkflow();

        WorkflowExecutionLog::create([
            'workflow_id' => $wf->id,
            'home_id' => $this->homeId,
            'trigger_type' => 'event',
            'trigger_data' => ['count' => 5],
            'action_type' => 'send_notification',
            'action_result' => 'success',
            'executed_at' => now(),
        ]);

        WorkflowExecutionLog::create([
            'workflow_id' => 0,
            'home_id' => 999,
            'trigger_type' => 'event',
            'trigger_data' => ['count' => 1],
            'action_type' => 'send_notification',
            'action_result' => 'success',
            'executed_at' => now(),
        ]);

        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->get('/roster/workflows/executions');

        $execs = $response->json('executions');
        $this->assertCount(1, $execs);
        $this->assertEquals($this->homeId, $execs[0]['home_id']);
    }

    public function test_artisan_evaluates_condition_trigger()
    {
        DB::table('scheduled_shifts')->insert([
            'home_id' => (string) $this->homeId,
            'care_type_id' => '1',
            'assignment' => 'test',
            'start_date' => now()->toDateString(),
            'status' => 'unfilled',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $wf = $this->createWorkflow([
            'workflow_name' => 'Artisan Eval Test',
            'trigger_type' => 'event',
            'trigger_config' => ['entity' => 'shifts', 'status' => 'unfilled', 'min_count' => 1],
        ]);

        $this->artisan('workflows:evaluate')->assertExitCode(0);

        $log = WorkflowExecutionLog::where('workflow_id', $wf->id)->first();
        $this->assertNotNull($log);
        $this->assertContains($log->action_result, ['success', 'failed']);

        DB::table('scheduled_shifts')
            ->where('home_id', (string) $this->homeId)
            ->where('status', 'unfilled')
            ->where('start_date', now()->toDateString())
            ->delete();
    }

    public function test_artisan_skips_inactive_workflows()
    {
        $wf = $this->createWorkflow(['is_active' => 0]);

        $this->artisan('workflows:evaluate')->assertExitCode(0);

        $log = WorkflowExecutionLog::where('workflow_id', $wf->id)->first();
        $this->assertNull($log);
    }

    public function test_artisan_respects_cooldown()
    {
        $wf = $this->createWorkflow([
            'cooldown_hours' => 24,
            'last_triggered_at' => Carbon::now()->subHour(),
        ]);

        $this->artisan('workflows:evaluate')->assertExitCode(0);

        $log = WorkflowExecutionLog::where('workflow_id', $wf->id)->first();
        $this->assertNull($log);
    }

    public function test_artisan_executes_send_notification()
    {
        $wf = $this->createWorkflow([
            'action_type' => 'send_notification',
            'action_config' => ['message' => 'Test notification from artisan', 'is_sticky' => true],
            'trigger_type' => 'event',
            'trigger_config' => ['entity' => 'shifts', 'status' => 'unfilled', 'min_count' => 1],
        ]);

        $this->artisan('workflows:evaluate')->assertExitCode(0);

        $notif = DB::table('notification')
            ->where('notification_event_type_id', 25)
            ->where('message', 'Test notification from artisan')
            ->first();

        if ($notif) {
            $this->assertEquals((string) $this->homeId, $notif->home_id);
            $this->assertEquals(1, $notif->is_sticky);
        }
    }

    public function test_max_workflows_per_home()
    {
        for ($i = 0; $i < 20; $i++) {
            $this->createWorkflow(['workflow_name' => "Workflow {$i}"]);
        }

        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->post('/roster/workflows/store', [
                'workflow_name' => 'Workflow 21',
                'category' => 'scheduling',
                'trigger_type' => 'event',
                'trigger_config' => json_encode(['entity' => 'shifts', 'status' => 'unfilled', 'min_count' => 1]),
                'action_type' => 'send_notification',
                'action_config' => json_encode(['message' => 'test']),
                'cooldown_hours' => 24,
            ]);

        $response->assertStatus(422);
        $this->assertStringContainsString('Maximum', $response->json('message'));
    }

    public function test_unauthenticated_redirects()
    {
        $response = $this->get('/roster/workflows');
        $response->assertStatus(302);
    }
}
