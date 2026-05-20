<?php

namespace Tests\Feature;

use App\Models\AIChatMessage;
use App\Models\AIChatSession;
use App\Models\AIUsageLog;
use App\Services\AI\PIIFilter;
use App\Services\AI\TokenTracker;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AICopilotTest extends TestCase
{
    protected $adminUser;
    protected $homeId = 8;
    private int $maxSessionIdBefore;
    private int $maxMessageIdBefore;
    private int $maxLogIdBefore;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = \App\User::where('user_name', 'komal')->first();

        $this->maxSessionIdBefore = (int) DB::table('ai_chat_sessions')->max('id');
        $this->maxMessageIdBefore = (int) DB::table('ai_chat_messages')->max('id');
        $this->maxLogIdBefore = (int) DB::table('ai_usage_logs')->max('id');
    }

    protected function createSession(array $overrides = []): AIChatSession
    {
        $session = new AIChatSession();
        $session->home_id = $overrides['home_id'] ?? $this->homeId;
        $session->user_id = $overrides['user_id'] ?? $this->adminUser->id;
        $session->session_title = $overrides['session_title'] ?? 'Test Chat';
        $session->context_type = $overrides['context_type'] ?? 'general';
        $session->message_count = 0;
        $session->total_tokens = 0;
        $session->is_active = 1;
        $session->is_deleted = 0;
        $session->save();
        return $session;
    }

    protected function fakeOpenAIResponse(string $content = 'Hello! I can help with care-related questions.', int $promptTokens = 100, int $completionTokens = 50): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => $content, 'role' => 'assistant']]
                ],
                'usage' => [
                    'prompt_tokens' => $promptTokens,
                    'completion_tokens' => $completionTokens,
                    'total_tokens' => $promptTokens + $completionTokens,
                ],
                'model' => 'gpt-4o-mini',
            ], 200),
        ]);
    }

    protected function tearDown(): void
    {
        AIChatMessage::where('id', '>', $this->maxMessageIdBefore)->forceDelete();
        AIChatSession::where('id', '>', $this->maxSessionIdBefore)->forceDelete();
        AIUsageLog::where('id', '>', $this->maxLogIdBefore)->forceDelete();
        parent::tearDown();
    }

    // === Auth Tests ===

    public function test_copilot_page_loads_for_authenticated_user()
    {
        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->get('/roster/ai-copilot');

        $response->assertStatus(200);
        $response->assertSee('AI Care Copilot');
    }

    public function test_copilot_page_redirects_unauthenticated()
    {
        $response = $this->get('/roster/ai-copilot');
        $response->assertStatus(302);
    }

    public function test_new_session_creates_successfully()
    {
        $this->fakeOpenAIResponse();
        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->postJson('/roster/ai-copilot/new-session', [
                'context_type' => 'general',
            ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => true]);

        $this->assertDatabaseHas('ai_chat_sessions', [
            'home_id' => $this->homeId,
            'user_id' => $this->adminUser->id,
            'context_type' => 'general',
            'is_deleted' => 0,
        ]);
    }

    // === Chat Tests (with Http::fake for OpenAI) ===

    public function test_send_message_returns_ai_response()
    {
        $this->fakeOpenAIResponse('Based on the records, the resident is doing well.');
        $session = $this->createSession();

        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->postJson('/roster/ai-copilot/send', [
                'message' => 'How is this resident doing?',
                'session_id' => $session->id,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => true]);
        $this->assertStringContains('doing well', $response->json('message'));
    }

    public function test_send_message_saves_both_messages_to_db()
    {
        $this->fakeOpenAIResponse('Test response');
        $session = $this->createSession();

        $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->postJson('/roster/ai-copilot/send', [
                'message' => 'Hello there',
                'session_id' => $session->id,
            ]);

        $this->assertDatabaseHas('ai_chat_messages', [
            'session_id' => $session->id,
            'role' => 'user',
            'content' => 'Hello there',
        ]);

        $this->assertDatabaseHas('ai_chat_messages', [
            'session_id' => $session->id,
            'role' => 'assistant',
        ]);
    }

    public function test_send_message_logs_tokens_to_usage_logs()
    {
        $this->fakeOpenAIResponse('Response text', 150, 75);
        $session = $this->createSession();

        $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->postJson('/roster/ai-copilot/send', [
                'message' => 'Test message',
                'session_id' => $session->id,
            ]);

        $this->assertDatabaseHas('ai_usage_logs', [
            'home_id' => $this->homeId,
            'user_id' => $this->adminUser->id,
            'feature' => 'copilot',
            'response_status' => 'success',
        ]);
    }

    public function test_send_message_with_api_error_returns_graceful_message()
    {
        Http::fake([
            'api.openai.com/*' => Http::response(['error' => ['message' => 'Server error']], 500),
        ]);
        $session = $this->createSession();

        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->postJson('/roster/ai-copilot/send', [
                'message' => 'Hello',
                'session_id' => $session->id,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => false]);
        $this->assertNotNull($response->json('error'));
    }

    public function test_send_message_with_rate_limit_returns_error()
    {
        Http::fake([
            'api.openai.com/*' => Http::response(['error' => ['message' => 'Rate limited']], 429),
        ]);
        $session = $this->createSession();

        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->postJson('/roster/ai-copilot/send', [
                'message' => 'Hello',
                'session_id' => $session->id,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => false]);
    }

    // === Token Cap Tests ===

    public function test_at_daily_cap_request_is_rejected()
    {
        $session = $this->createSession();

        $log = new AIUsageLog();
        $log->home_id = $this->homeId;
        $log->user_id = $this->adminUser->id;
        $log->feature = 'copilot';
        $log->model_used = 'gpt-4o-mini';
        $log->tokens_input = 50000;
        $log->tokens_output = 50000;
        $log->tokens_total = 100000;
        $log->response_status = 'success';
        $log->created_at = Carbon::now();
        $log->save();

        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->postJson('/roster/ai-copilot/send', [
                'message' => 'Hello',
                'session_id' => $session->id,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => false]);
        $this->assertStringContains('limit', strtolower($response->json('error')));
    }

    public function test_below_cap_request_proceeds()
    {
        $this->fakeOpenAIResponse();
        $session = $this->createSession();

        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->postJson('/roster/ai-copilot/send', [
                'message' => 'Hello',
                'session_id' => $session->id,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => true]);
    }

    // === PII Filter Tests ===

    public function test_pii_filter_anonymises_client_names()
    {
        $filter = new PIIFilter();
        $clientName = DB::table('service_user')
            ->where('home_id', $this->homeId)
            ->where('is_deleted', 0)
            ->value('name');

        if (!$clientName) {
            $this->markTestSkipped('No clients in test home');
        }

        $firstName = explode(' ', $clientName)[0];
        $result = $filter->filter("I need to check on {$firstName} today.", $this->homeId);

        $this->assertStringNotContainsString($firstName, $result);
        $this->assertStringContainsString('[Client', $result);
    }

    public function test_pii_filter_replaces_dob_patterns()
    {
        $filter = new PIIFilter();
        $result = $filter->filter('DOB is 15/03/1945 and also 1950-06-22', $this->homeId);

        $this->assertStringNotContainsString('15/03/1945', $result);
        $this->assertStringNotContainsString('1950-06-22', $result);
        $this->assertStringContainsString('[DOB]', $result);
    }

    public function test_pii_filter_replaces_nhs_numbers()
    {
        $filter = new PIIFilter();
        $result = $filter->filter('NHS number is 123 456 7890', $this->homeId);

        $this->assertStringNotContainsString('123 456 7890', $result);
        $this->assertStringContainsString('[NHS_NUMBER]', $result);
    }

    public function test_pii_filter_replaces_email_addresses()
    {
        $filter = new PIIFilter();
        $result = $filter->filter('Contact them at john@example.com', $this->homeId);

        $this->assertStringNotContainsString('john@example.com', $result);
        $this->assertStringContainsString('[EMAIL]', $result);
    }

    // === IDOR & Multi-Tenancy ===

    public function test_user_cannot_read_another_homes_sessions()
    {
        $otherSession = $this->createSession(['home_id' => 999]);

        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->getJson('/roster/ai-copilot/messages?session_id=' . $otherSession->id);

        $response->assertStatus(200);
        $this->assertEmpty($response->json('messages'));

        $otherSession->forceDelete();
    }

    public function test_user_cannot_send_to_another_homes_session()
    {
        $this->fakeOpenAIResponse();
        $otherSession = $this->createSession(['home_id' => 999]);

        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->postJson('/roster/ai-copilot/send', [
                'message' => 'Hello',
                'session_id' => $otherSession->id,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => false]);

        $otherSession->forceDelete();
    }

    public function test_sessions_list_only_returns_own_home()
    {
        $ownSession = $this->createSession();
        $otherSession = $this->createSession(['home_id' => 999]);

        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->getJson('/roster/ai-copilot/sessions');

        $response->assertStatus(200);
        $sessionIds = collect($response->json('sessions'))->pluck('id')->toArray();

        $this->assertContains($ownSession->id, $sessionIds);
        $this->assertNotContains($otherSession->id, $sessionIds);

        $otherSession->forceDelete();
    }

    // === Security ===

    public function test_xss_in_user_message_is_stored_raw()
    {
        $this->fakeOpenAIResponse();
        $session = $this->createSession();

        $xssPayload = '<script>alert("xss")</script>';

        $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->postJson('/roster/ai-copilot/send', [
                'message' => $xssPayload,
                'session_id' => $session->id,
            ]);

        $this->assertDatabaseHas('ai_chat_messages', [
            'session_id' => $session->id,
            'role' => 'user',
            'content' => $xssPayload,
        ]);
    }

    public function test_message_too_long_returns_422()
    {
        $session = $this->createSession();

        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->postJson('/roster/ai-copilot/send', [
                'message' => str_repeat('A', 2001),
                'session_id' => $session->id,
            ]);

        $response->assertStatus(422);
    }

    public function test_empty_message_returns_422()
    {
        $session = $this->createSession();

        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->postJson('/roster/ai-copilot/send', [
                'message' => '',
                'session_id' => $session->id,
            ]);

        $response->assertStatus(422);
    }

    public function test_delete_session_soft_deletes()
    {
        $session = $this->createSession();

        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->postJson('/roster/ai-copilot/delete-session', [
                'session_id' => $session->id,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => true]);

        $this->assertDatabaseHas('ai_chat_sessions', [
            'id' => $session->id,
            'is_deleted' => 1,
        ]);
    }

    public function test_usage_endpoint_returns_token_data()
    {
        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->getJson('/roster/ai-copilot/usage');

        $response->assertStatus(200);
        $response->assertJsonStructure(['status', 'daily_usage', 'daily_cap', 'remaining']);
    }

    public function test_get_messages_for_session()
    {
        $session = $this->createSession();

        $msg = new AIChatMessage();
        $msg->session_id = $session->id;
        $msg->home_id = $this->homeId;
        $msg->role = 'user';
        $msg->content = 'Test question';
        $msg->created_at = Carbon::now();
        $msg->save();

        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->getJson('/roster/ai-copilot/messages?session_id=' . $session->id);

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('messages'));
    }

    public function test_new_session_validates_context_type()
    {
        $response = $this->actingAs($this->adminUser)
            ->withoutMiddleware()
            ->postJson('/roster/ai-copilot/new-session', [
                'context_type' => 'invalid_type',
            ]);

        $response->assertStatus(422);
    }

    // Helper: assertStringContains for older PHPUnit versions
    private function assertStringContains(string $needle, string $haystack): void
    {
        $this->assertTrue(
            str_contains($haystack, $needle),
            "Failed asserting that '{$haystack}' contains '{$needle}'"
        );
    }
}
