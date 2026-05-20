<?php

namespace Tests\Feature;

use App\Models\AutomatedWorkflow;
use App\Models\WorkflowExecutionLog;
use App\Services\WorkflowEngineService;
use App\Services\WorkflowTemplateRegistry;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WorkflowTemplateTest extends TestCase
{
    protected $adminUser;
    protected $homeId = 8;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = \App\User::where('user_name', 'komal')->first();
    }

    protected function tearDown(): void
    {
        AutomatedWorkflow::where('home_id', $this->homeId)->whereNotNull('template_id')->forceDelete();
        AutomatedWorkflow::where('home_id', 999)->whereNotNull('template_id')->forceDelete();
        parent::tearDown();
    }

    public function test_templates_endpoint_returns_all_8_templates()
    {
        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->get('/roster/workflows/templates');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertTrue($data['status']);
        $this->assertCount(8, $data['templates']);
    }

    public function test_templates_endpoint_marks_installed_templates()
    {
        $wf = new AutomatedWorkflow();
        $wf->workflow_name = 'Incident → Notify Manager';
        $wf->template_id = 'incident_notify_manager';
        $wf->category = 'compliance';
        $wf->trigger_type = 'event';
        $wf->trigger_config = ['entity' => 'incidents', 'status' => 'new', 'min_count' => 1];
        $wf->action_type = 'send_notification';
        $wf->action_config = ['message' => 'Test', 'is_sticky' => true];
        $wf->cooldown_hours = 24;
        $wf->is_active = 1;
        $wf->home_id = $this->homeId;
        $wf->created_by = $this->adminUser->id;
        $wf->save();

        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->get('/roster/workflows/templates');

        $templates = $response->json('templates');
        $installedCount = 0;
        foreach ($templates as $t) {
            if ($t['template_id'] === 'incident_notify_manager') {
                $this->assertTrue($t['installed']);
                $installedCount++;
            } else {
                $this->assertFalse($t['installed']);
            }
        }
        $this->assertEquals(1, $installedCount);
    }

    public function test_install_template_creates_workflow()
    {
        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->post('/roster/workflows/install-template', [
                'template_id' => 'incident_notify_manager',
            ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertTrue($data['status']);
        $this->assertEquals('Incident → Notify Manager', $data['workflow']['workflow_name']);
        $this->assertFalse($data['needs_config']);
    }

    public function test_install_template_sets_template_id()
    {
        $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->post('/roster/workflows/install-template', [
                'template_id' => 'unfilled_shift_alert',
            ]);

        $wf = AutomatedWorkflow::where('home_id', $this->homeId)
            ->where('template_id', 'unfilled_shift_alert')
            ->first();

        $this->assertNotNull($wf);
        $this->assertEquals('unfilled_shift_alert', $wf->template_id);
    }

    public function test_install_template_prevents_duplicate()
    {
        $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->post('/roster/workflows/install-template', [
                'template_id' => 'incident_notify_manager',
            ]);

        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->post('/roster/workflows/install-template', [
                'template_id' => 'incident_notify_manager',
            ]);

        $response->assertStatus(422);
        $this->assertStringContainsString('already installed', $response->json('message'));
    }

    public function test_install_template_respects_max_limit()
    {
        for ($i = 0; $i < 20; $i++) {
            $wf = new AutomatedWorkflow();
            $wf->workflow_name = "Filler #{$i}";
            $wf->category = 'scheduling';
            $wf->trigger_type = 'event';
            $wf->trigger_config = ['entity' => 'shifts', 'status' => 'unfilled', 'min_count' => 1];
            $wf->action_type = 'send_notification';
            $wf->action_config = ['message' => 'test', 'is_sticky' => false];
            $wf->cooldown_hours = 24;
            $wf->is_active = 0;
            $wf->home_id = $this->homeId;
            $wf->created_by = $this->adminUser->id;
            $wf->save();
        }

        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->post('/roster/workflows/install-template', [
                'template_id' => 'incident_notify_manager',
            ]);

        $response->assertStatus(422);
        $this->assertStringContainsString('Maximum', $response->json('message'));

        AutomatedWorkflow::where('home_id', $this->homeId)->whereNull('template_id')->forceDelete();
    }

    public function test_email_template_installs_inactive_without_recipients()
    {
        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->post('/roster/workflows/install-template', [
                'template_id' => 'incident_spike_alert',
            ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json('needs_config'));

        $wf = AutomatedWorkflow::where('home_id', $this->homeId)
            ->where('template_id', 'incident_spike_alert')
            ->first();

        $this->assertFalse((bool) $wf->is_active);
    }

    public function test_scheduled_template_sets_next_run_date()
    {
        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->post('/roster/workflows/install-template', [
                'template_id' => 'daily_summary_email',
            ]);

        $response->assertStatus(200);

        $wf = AutomatedWorkflow::where('home_id', $this->homeId)
            ->where('template_id', 'daily_summary_email')
            ->first();

        $this->assertNotNull($wf->next_run_date);
    }

    public function test_install_rejects_invalid_template_id()
    {
        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->post('/roster/workflows/install-template', [
                'template_id' => 'nonexistent_template',
            ]);

        $response->assertStatus(422);
    }

    public function test_installed_template_is_editable()
    {
        $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->post('/roster/workflows/install-template', [
                'template_id' => 'unfilled_shift_alert',
            ]);

        $wf = AutomatedWorkflow::where('home_id', $this->homeId)
            ->where('template_id', 'unfilled_shift_alert')
            ->first();

        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->post('/roster/workflows/update', [
                'id' => $wf->id,
                'workflow_name' => 'My Custom Shift Alert',
                'category' => 'scheduling',
                'trigger_type' => 'event',
                'trigger_config' => json_encode(['entity' => 'shifts', 'status' => 'unfilled', 'min_count' => 1]),
                'action_type' => 'send_notification',
                'action_config' => json_encode(['message' => 'Custom message', 'is_sticky' => false]),
                'cooldown_hours' => 12,
            ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json('status'));
        $this->assertEquals('My Custom Shift Alert', $response->json('workflow.workflow_name'));
    }

    public function test_installed_template_is_deletable()
    {
        $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->post('/roster/workflows/install-template', [
                'template_id' => 'feedback_new_alert',
            ]);

        $wf = AutomatedWorkflow::where('home_id', $this->homeId)
            ->where('template_id', 'feedback_new_alert')
            ->first();

        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->post('/roster/workflows/delete', ['id' => $wf->id]);

        $response->assertStatus(200);
        $this->assertTrue($response->json('status'));

        $wf->refresh();
        $this->assertEquals(1, $wf->is_deleted);
    }

    public function test_home_isolation_on_template_installed_status()
    {
        $wf = new AutomatedWorkflow();
        $wf->workflow_name = 'Incident → Notify Manager';
        $wf->template_id = 'incident_notify_manager';
        $wf->category = 'compliance';
        $wf->trigger_type = 'event';
        $wf->trigger_config = ['entity' => 'incidents', 'status' => 'new', 'min_count' => 1];
        $wf->action_type = 'send_notification';
        $wf->action_config = ['message' => 'Test', 'is_sticky' => true];
        $wf->cooldown_hours = 24;
        $wf->is_active = 1;
        $wf->home_id = 999;
        $wf->created_by = $this->adminUser->id;
        $wf->save();

        $service = app(WorkflowEngineService::class);
        $templates = $service->getTemplates($this->homeId);

        foreach ($templates as $t) {
            $this->assertFalse($t['installed']);
        }

        AutomatedWorkflow::where('home_id', 999)->forceDelete();
    }

    public function test_template_id_not_mass_assignable()
    {
        $wf = new AutomatedWorkflow();
        $wf->fill(['template_id' => 'injected_value']);
        $this->assertNull($wf->template_id);
    }

    public function test_install_validation_requires_template_id()
    {
        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->postJson('/roster/workflows/install-template', []);

        $response->assertStatus(422);
    }

    public function test_unauthenticated_templates_redirects()
    {
        $response = $this->get('/roster/workflows/templates');
        $response->assertStatus(302);
    }

    public function test_unauthenticated_install_redirects()
    {
        $response = $this->post('/roster/workflows/install-template', [
            'template_id' => 'incident_notify_manager',
        ]);
        $response->assertStatus(302);
    }

    public function test_registry_returns_8_templates()
    {
        $templates = WorkflowTemplateRegistry::all();
        $this->assertCount(8, $templates);
    }

    public function test_registry_find_returns_correct_template()
    {
        $t = WorkflowTemplateRegistry::find('medication_missed_alert');
        $this->assertNotNull($t);
        $this->assertEquals('Missed Medication Alert', $t['workflow_name']);
        $this->assertEquals('clinical', $t['category']);
    }

    public function test_registry_find_returns_null_for_unknown()
    {
        $t = WorkflowTemplateRegistry::find('does_not_exist');
        $this->assertNull($t);
    }
}
