<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AICarePlanTest extends TestCase
{
    private $user;
    private $homeId = 8;
    private $clientId;
    private int $maxPlanIdBefore;
    private int $maxLogIdBefore;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = \App\User::where('user_name', 'komal')->first();

        $this->clientId = DB::table('service_user')
            ->where('home_id', $this->homeId)
            ->where('is_deleted', 0)
            ->value('id');

        $this->maxPlanIdBefore = (int) DB::table('ai_care_plans')->max('id');
        $this->maxLogIdBefore = (int) DB::table('ai_usage_logs')->max('id');
    }

    protected function tearDown(): void
    {
        DB::table('ai_care_plans')->where('id', '>', $this->maxPlanIdBefore)->delete();
        DB::table('ai_usage_logs')->where('id', '>', $this->maxLogIdBefore)->delete();
        parent::tearDown();
    }

    private function actAsUser()
    {
        return $this->actingAs($this->user)->withoutMiddleware();
    }

    private function fakeOpenAIResponse(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'id' => 'chatcmpl-test',
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'summary' => 'Test care plan summary for the resident.',
                                'objectives' => [
                                    [
                                        'title' => 'Maintain medication adherence',
                                        'description' => 'Ensure 95% medication compliance',
                                        'success_measures' => 'MAR records, weekly compliance check',
                                        'target_date' => '2026-08-08',
                                        'status' => 'not_started',
                                        'priority' => 'high',
                                    ],
                                ],
                                'care_tasks' => [
                                    [
                                        'title' => 'Daily medication administration',
                                        'category' => 'medication',
                                        'description' => 'Administer prescribed medications',
                                        'frequency' => 'daily',
                                        'duration_minutes' => 15,
                                        'special_instructions' => 'Monitor for side effects',
                                        'assigned_role' => 'care_worker',
                                    ],
                                ],
                                'risk_factors' => [
                                    [
                                        'risk' => 'Falls risk',
                                        'likelihood' => 'medium',
                                        'impact' => 'high',
                                        'control_measures' => 'Grab rails, non-slip mats',
                                    ],
                                ],
                                'medication_summary' => [
                                    'total_medications' => 3,
                                    'key_concerns' => 'None identified',
                                    'notes' => 'Regular review recommended',
                                ],
                                'review_schedule' => [
                                    'next_review_date' => '2026-08-08',
                                    'review_frequency' => '3_months',
                                    'review_triggers' => ['Change in health status'],
                                ],
                                'consent_and_capacity' => [
                                    'capacity_assessment' => 'Has capacity',
                                    'consent_given' => true,
                                    'involvement_notes' => 'Client was consulted',
                                ],
                            ]),
                        ],
                    ],
                ],
                'usage' => ['prompt_tokens' => 2500, 'completion_tokens' => 800],
                'model' => 'gpt-4o',
            ], 200),
        ]);
    }

    private function fakeOpenAIErrorResponse(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response(['error' => ['message' => 'Service unavailable']], 500),
        ]);
    }

    private function fakeOpenAIInvalidJsonResponse(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'id' => 'chatcmpl-test',
                'choices' => [
                    ['message' => ['content' => '{"summary": "test"}']],
                ],
                'usage' => ['prompt_tokens' => 100, 'completion_tokens' => 50],
                'model' => 'gpt-4o',
            ], 200),
        ]);
    }

    // === Auth Tests ===

    public function test_unauthenticated_generate_redirects(): void
    {
        $response = $this->postJson('/roster/ai-care-plan/generate', [
            'client_id' => $this->clientId,
            'assessment_type' => 'initial',
            'care_setting' => 'residential',
        ]);
        $response->assertStatus(302);
    }

    public function test_unauthenticated_list_redirects(): void
    {
        $response = $this->getJson('/roster/ai-care-plan/list?client_id=' . $this->clientId);
        $response->assertStatus(302);
    }

    // === IDOR Tests ===

    public function test_generate_for_other_home_client_fails(): void
    {
        $otherClient = DB::table('service_user')
            ->where('home_id', '!=', $this->homeId)
            ->where('is_deleted', 0)
            ->value('id');

        if (!$otherClient) {
            $this->markTestSkipped('No client in other home for IDOR test');
        }

        $this->fakeOpenAIResponse();

        $response = $this->actAsUser()
            ->postJson('/roster/ai-care-plan/generate', [
                'client_id' => $otherClient,
                'assessment_type' => 'initial',
                'care_setting' => 'residential',
            ]);

        $response->assertStatus(404);
    }

    public function test_view_other_home_plan_fails(): void
    {
        DB::table('ai_care_plans')->insert([
            'home_id' => 999,
            'client_id' => 1,
            'created_by' => 1,
            'plan_status' => 'draft',
            'assessment_type' => 'initial',
            'care_setting' => 'residential',
            'plan_data' => json_encode(['summary' => 'test']),
            'ai_model' => 'gpt-4o',
            'is_deleted' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $planId = DB::table('ai_care_plans')->where('home_id', 999)->value('id');

        $response = $this->actAsUser()
            ->getJson('/roster/ai-care-plan/view?plan_id=' . $planId);

        $response->assertStatus(404);

        DB::table('ai_care_plans')->where('home_id', 999)->delete();
    }

    // === Generation Tests ===

    public function test_generate_care_plan_returns_valid_json(): void
    {
        $this->fakeOpenAIResponse();

        $response = $this->actAsUser()
            ->postJson('/roster/ai-care-plan/generate', [
                'client_id' => $this->clientId,
                'assessment_type' => 'initial',
                'care_setting' => 'residential',
            ]);

        $response->assertStatus(200)
            ->assertJson(['status' => true])
            ->assertJsonStructure([
                'status',
                'plan_data' => ['summary', 'objectives', 'care_tasks', 'risk_factors'],
                'tokens_used',
                'model',
            ]);
    }

    public function test_generate_logs_to_usage_table(): void
    {
        $this->fakeOpenAIResponse();

        $this->actAsUser()
            ->postJson('/roster/ai-care-plan/generate', [
                'client_id' => $this->clientId,
                'assessment_type' => 'initial',
                'care_setting' => 'residential',
            ]);

        $this->assertDatabaseHas('ai_usage_logs', [
            'home_id' => $this->homeId,
            'feature' => 'care_plan',
            'response_status' => 'success',
        ]);
    }

    public function test_generate_uses_quality_model(): void
    {
        $this->fakeOpenAIResponse();

        $response = $this->actAsUser()
            ->postJson('/roster/ai-care-plan/generate', [
                'client_id' => $this->clientId,
                'assessment_type' => 'initial',
                'care_setting' => 'residential',
            ]);

        $response->assertJson(['model' => 'gpt-4o']);
    }

    public function test_generate_with_api_error_returns_graceful_error(): void
    {
        $this->fakeOpenAIErrorResponse();

        $response = $this->actAsUser()
            ->postJson('/roster/ai-care-plan/generate', [
                'client_id' => $this->clientId,
                'assessment_type' => 'initial',
                'care_setting' => 'residential',
            ]);

        $response->assertStatus(200)
            ->assertJson(['status' => false]);

        $this->assertNotEmpty($response->json('error'));
    }

    public function test_generate_with_invalid_json_response_returns_error(): void
    {
        $this->fakeOpenAIInvalidJsonResponse();

        $response = $this->actAsUser()
            ->postJson('/roster/ai-care-plan/generate', [
                'client_id' => $this->clientId,
                'assessment_type' => 'initial',
                'care_setting' => 'residential',
            ]);

        $response->assertStatus(200)
            ->assertJson(['status' => false]);
    }

    // === Token Cap Test ===

    public function test_generate_at_token_cap_is_rejected(): void
    {
        DB::table('ai_usage_logs')->insert([
            'home_id' => $this->homeId,
            'user_id' => $this->user->id,
            'feature' => 'care_plan',
            'model_used' => 'gpt-4o',
            'tokens_input' => 60000,
            'tokens_output' => 50000,
            'tokens_total' => 110000,
            'response_status' => 'success',
            'prompt_hash' => 'cap-test',
            'created_at' => now(),
        ]);

        $response = $this->actAsUser()
            ->postJson('/roster/ai-care-plan/generate', [
                'client_id' => $this->clientId,
                'assessment_type' => 'initial',
                'care_setting' => 'residential',
            ]);

        $response->assertJson([
            'status' => false,
            'error' => 'Daily AI usage limit reached. Resets at midnight.',
        ]);
    }

    // === Save & CRUD Tests ===

    public function test_save_care_plan_creates_record(): void
    {
        $planData = [
            'summary' => 'Test plan',
            'objectives' => [['title' => 'Obj 1', 'description' => 'Desc']],
            'care_tasks' => [['title' => 'Task 1', 'category' => 'medication']],
            'risk_factors' => [['risk' => 'Falls', 'likelihood' => 'medium', 'impact' => 'high']],
        ];

        $response = $this->actAsUser()
            ->postJson('/roster/ai-care-plan/save', [
                'client_id' => $this->clientId,
                'plan_data' => $planData,
                'assessment_type' => 'initial',
                'care_setting' => 'residential',
                'status' => 'draft',
                'model' => 'gpt-4o',
                'tokens_input' => 2500,
                'tokens_output' => 800,
            ]);

        $response->assertStatus(200)->assertJson(['status' => true]);
        $this->assertDatabaseHas('ai_care_plans', [
            'home_id' => $this->homeId,
            'client_id' => $this->clientId,
            'plan_status' => 'draft',
        ]);
    }

    public function test_save_active_supersedes_previous(): void
    {
        DB::table('ai_care_plans')->insert([
            'home_id' => $this->homeId,
            'client_id' => $this->clientId,
            'created_by' => $this->user->id,
            'plan_status' => 'active',
            'assessment_type' => 'initial',
            'care_setting' => 'residential',
            'plan_data' => json_encode(['summary' => 'old', 'objectives' => [], 'care_tasks' => [], 'risk_factors' => []]),
            'ai_model' => 'gpt-4o',
            'is_deleted' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actAsUser()
            ->postJson('/roster/ai-care-plan/save', [
                'client_id' => $this->clientId,
                'plan_data' => ['summary' => 'new', 'objectives' => [], 'care_tasks' => [], 'risk_factors' => []],
                'assessment_type' => 'review',
                'care_setting' => 'residential',
                'status' => 'active',
                'model' => 'gpt-4o',
                'tokens_input' => 100,
                'tokens_output' => 50,
            ]);

        $response->assertJson(['status' => true]);

        $activeCount = DB::table('ai_care_plans')
            ->where('home_id', $this->homeId)
            ->where('client_id', $this->clientId)
            ->where('plan_status', 'active')
            ->where('is_deleted', 0)
            ->count();

        $this->assertEquals(1, $activeCount);
    }

    public function test_list_plans_returns_correct_client(): void
    {
        DB::table('ai_care_plans')
            ->where('home_id', $this->homeId)
            ->where('client_id', $this->clientId)
            ->delete();

        DB::table('ai_care_plans')->insert([
            'home_id' => $this->homeId,
            'client_id' => $this->clientId,
            'created_by' => $this->user->id,
            'plan_status' => 'draft',
            'assessment_type' => 'initial',
            'care_setting' => 'residential',
            'plan_data' => json_encode(['summary' => 'test', 'objectives' => [], 'care_tasks' => [], 'risk_factors' => []]),
            'ai_model' => 'gpt-4o',
            'is_deleted' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actAsUser()
            ->getJson('/roster/ai-care-plan/list?client_id=' . $this->clientId);

        $response->assertStatus(200)
            ->assertJson(['status' => true])
            ->assertJsonCount(1, 'plans');
    }

    public function test_view_plan_returns_plan_data(): void
    {
        $planId = DB::table('ai_care_plans')->insertGetId([
            'home_id' => $this->homeId,
            'client_id' => $this->clientId,
            'created_by' => $this->user->id,
            'plan_status' => 'draft',
            'assessment_type' => 'initial',
            'care_setting' => 'residential',
            'plan_data' => json_encode(['summary' => 'view test', 'objectives' => [], 'care_tasks' => [], 'risk_factors' => []]),
            'ai_model' => 'gpt-4o',
            'is_deleted' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actAsUser()
            ->getJson('/roster/ai-care-plan/view?plan_id=' . $planId);

        $response->assertStatus(200)
            ->assertJson(['status' => true])
            ->assertJsonPath('plan.plan_data.summary', 'view test');
    }

    public function test_update_plan_modifies_data(): void
    {
        $planId = DB::table('ai_care_plans')->insertGetId([
            'home_id' => $this->homeId,
            'client_id' => $this->clientId,
            'created_by' => $this->user->id,
            'plan_status' => 'draft',
            'assessment_type' => 'initial',
            'care_setting' => 'residential',
            'plan_data' => json_encode(['summary' => 'original', 'objectives' => [], 'care_tasks' => [], 'risk_factors' => []]),
            'ai_model' => 'gpt-4o',
            'is_deleted' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actAsUser()
            ->postJson('/roster/ai-care-plan/update', [
                'plan_id' => $planId,
                'plan_data' => ['summary' => 'updated', 'objectives' => [], 'care_tasks' => [], 'risk_factors' => []],
            ]);

        $response->assertJson(['status' => true]);

        $plan = DB::table('ai_care_plans')->find($planId);
        $data = json_decode($plan->plan_data, true);
        $this->assertEquals('updated', $data['summary']);
    }

    public function test_delete_plan_soft_deletes(): void
    {
        $planId = DB::table('ai_care_plans')->insertGetId([
            'home_id' => $this->homeId,
            'client_id' => $this->clientId,
            'created_by' => $this->user->id,
            'plan_status' => 'draft',
            'assessment_type' => 'initial',
            'care_setting' => 'residential',
            'plan_data' => json_encode(['summary' => 'del', 'objectives' => [], 'care_tasks' => [], 'risk_factors' => []]),
            'ai_model' => 'gpt-4o',
            'is_deleted' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actAsUser()
            ->postJson('/roster/ai-care-plan/delete', ['plan_id' => $planId]);

        $response->assertJson(['status' => true]);
        $this->assertDatabaseHas('ai_care_plans', ['id' => $planId, 'is_deleted' => 1]);
    }

    public function test_activate_plan_sets_status(): void
    {
        $planId = DB::table('ai_care_plans')->insertGetId([
            'home_id' => $this->homeId,
            'client_id' => $this->clientId,
            'created_by' => $this->user->id,
            'plan_status' => 'draft',
            'assessment_type' => 'initial',
            'care_setting' => 'residential',
            'plan_data' => json_encode(['summary' => 'act', 'objectives' => [], 'care_tasks' => [], 'risk_factors' => []]),
            'ai_model' => 'gpt-4o',
            'is_deleted' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actAsUser()
            ->postJson('/roster/ai-care-plan/activate', ['plan_id' => $planId]);

        $response->assertJson(['status' => true]);
        $this->assertDatabaseHas('ai_care_plans', ['id' => $planId, 'plan_status' => 'active']);
    }

    // === Validation Tests ===

    public function test_invalid_assessment_type_rejected(): void
    {
        $response = $this->actAsUser()
            ->postJson('/roster/ai-care-plan/generate', [
                'client_id' => $this->clientId,
                'assessment_type' => 'invalid',
                'care_setting' => 'residential',
            ]);

        $response->assertStatus(422);
    }

    public function test_invalid_care_setting_rejected(): void
    {
        $response = $this->actAsUser()
            ->postJson('/roster/ai-care-plan/generate', [
                'client_id' => $this->clientId,
                'assessment_type' => 'initial',
                'care_setting' => 'invalid',
            ]);

        $response->assertStatus(422);
    }

    public function test_missing_client_id_rejected(): void
    {
        $response = $this->actAsUser()
            ->postJson('/roster/ai-care-plan/generate', [
                'assessment_type' => 'initial',
                'care_setting' => 'residential',
            ]);

        $response->assertStatus(422);
    }

    // === Mass Assignment Test ===

    public function test_save_ignores_home_id_in_request(): void
    {
        $response = $this->actAsUser()
            ->postJson('/roster/ai-care-plan/save', [
                'client_id' => $this->clientId,
                'home_id' => 999,
                'plan_data' => ['summary' => 'mass assign test', 'objectives' => [], 'care_tasks' => [], 'risk_factors' => []],
                'assessment_type' => 'initial',
                'care_setting' => 'residential',
                'status' => 'draft',
                'model' => 'gpt-4o',
                'tokens_input' => 100,
                'tokens_output' => 50,
            ]);

        $response->assertJson(['status' => true]);
        $this->assertDatabaseMissing('ai_care_plans', ['home_id' => 999, 'client_id' => $this->clientId]);
        $this->assertDatabaseHas('ai_care_plans', ['home_id' => $this->homeId, 'client_id' => $this->clientId]);
    }
}
