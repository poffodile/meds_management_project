<?php

namespace App\Services\AI;

use App\Models\AIChatMessage;
use App\Models\AIChatSession;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class AICopilotService
{
    private OpenAIService $openAI;
    private PIIFilter $piiFilter;
    private TokenTracker $tokenTracker;
    private PromptBuilder $promptBuilder;

    public function __construct(
        OpenAIService $openAI,
        PIIFilter $piiFilter,
        TokenTracker $tokenTracker,
        PromptBuilder $promptBuilder
    ) {
        $this->openAI = $openAI;
        $this->piiFilter = $piiFilter;
        $this->tokenTracker = $tokenTracker;
        $this->promptBuilder = $promptBuilder;
    }

    public function sendMessage(int $sessionId, string $userMessage, int $homeId, int $userId): array
    {
        if (!$this->openAI->isConfigured()) {
            return ['status' => false, 'error' => 'AI is not configured. Please contact your administrator.'];
        }

        if ($this->tokenTracker->isCapExceeded($homeId)) {
            return ['status' => false, 'error' => 'Daily AI usage limit reached. Resets at midnight.'];
        }

        $session = AIChatSession::forHome($homeId)
            ->forUser($userId)
            ->notDeleted()
            ->find($sessionId);

        if (!$session) {
            return ['status' => false, 'error' => 'Chat session not found.'];
        }

        $userMsg = new AIChatMessage();
        $userMsg->session_id = $session->id;
        $userMsg->home_id = $homeId;
        $userMsg->role = 'user';
        $userMsg->content = $userMessage;
        $userMsg->created_at = Carbon::now();
        $userMsg->save();

        $systemPrompt = $this->promptBuilder->buildCopilotSystemPrompt(
            $homeId,
            $session->context_id
        );

        $history = AIChatMessage::where('session_id', $session->id)
            ->orderByDesc('id')
            ->limit(config('ai.max_context_messages', 10))
            ->get()
            ->reverse()
            ->values();

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        foreach ($history as $msg) {
            $content = $msg->content;
            if ($msg->role === 'user') {
                $content = $this->piiFilter->filter($content, $homeId, skipNames: true);
                $content = '<user_input>' . $content . '</user_input>';
            }
            $messages[] = ['role' => $msg->role, 'content' => $content];
        }

        $promptHash = hash('sha256', json_encode($messages));

        try {
            $result = $this->openAI->chat($messages);

            $assistantMsg = new AIChatMessage();
            $assistantMsg->session_id = $session->id;
            $assistantMsg->home_id = $homeId;
            $assistantMsg->role = 'assistant';
            $assistantMsg->content = $result['content'];
            $assistantMsg->model_used = $result['model'];
            $assistantMsg->tokens_input = $result['tokens_input'];
            $assistantMsg->tokens_output = $result['tokens_output'];
            $assistantMsg->created_at = Carbon::now();
            $assistantMsg->save();

            $session->message_count = $session->message_count + 2;
            $session->total_tokens = $session->total_tokens + $result['tokens_input'] + $result['tokens_output'];
            $session->save();

            $this->tokenTracker->log(
                $homeId, $userId, 'copilot', $result['model'],
                $result['tokens_input'], $result['tokens_output'], 'success',
                $promptHash, null, $result['latency_ms'] ?? null
            );

            if ($session->message_count <= 2) {
                $this->autoTitle($session->id, $userMessage, $result['content']);
            }

            return [
                'status' => true,
                'message' => $result['content'],
                'tokens_used' => $result['tokens_input'] + $result['tokens_output'],
                'session_id' => $session->id,
            ];

        } catch (RuntimeException $e) {
            $this->tokenTracker->log(
                $homeId, $userId, 'copilot', config('ai.default_model'),
                0, 0, 'error', $promptHash, $e->getMessage()
            );

            return ['status' => false, 'error' => $e->getMessage()];
        }
    }

    public function createSession(int $homeId, int $userId, ?string $contextType = 'general', ?int $contextId = null): AIChatSession
    {
        $session = new AIChatSession();
        $session->home_id = $homeId;
        $session->user_id = $userId;
        $session->session_title = 'New Chat';
        $session->context_type = $contextType ?? 'general';
        $session->context_id = $contextId;
        $session->message_count = 0;
        $session->total_tokens = 0;
        $session->is_active = 1;
        $session->is_deleted = 0;
        $session->save();

        return $session;
    }

    public function listSessions(int $homeId, int $userId): Collection
    {
        return AIChatSession::forHome($homeId)
            ->forUser($userId)
            ->notDeleted()
            ->orderByDesc('updated_at')
            ->limit(50)
            ->get(['id', 'session_title', 'context_type', 'message_count', 'total_tokens', 'created_at', 'updated_at']);
    }

    public function getMessages(int $sessionId, int $homeId): Collection
    {
        $session = AIChatSession::forHome($homeId)->notDeleted()->find($sessionId);
        if (!$session) {
            return collect();
        }

        return AIChatMessage::where('session_id', $sessionId)
            ->where('home_id', $homeId)
            ->orderBy('id')
            ->get(['id', 'role', 'content', 'tokens_input', 'tokens_output', 'created_at']);
    }

    public function deleteSession(int $sessionId, int $homeId): void
    {
        $session = AIChatSession::forHome($homeId)->notDeleted()->find($sessionId);
        if ($session) {
            $session->is_deleted = 1;
            $session->save();
        }
    }

    private function autoTitle(int $sessionId, string $firstUserMessage, string $firstAssistantMessage): void
    {
        try {
            $messages = [
                ['role' => 'system', 'content' => 'Generate a short title (5 words max) for this conversation. Return ONLY the title, nothing else.'],
                ['role' => 'user', 'content' => "User: " . mb_substr($firstUserMessage, 0, 200) . "\nAssistant: " . mb_substr($firstAssistantMessage, 0, 200)],
            ];

            $result = $this->openAI->chat($messages, config('ai.default_model'), [
                'max_tokens' => 20,
                'temperature' => 0.3,
            ]);

            $title = trim($result['content'], " \"\n");
            $title = mb_substr($title, 0, 100);

            if (!empty($title)) {
                AIChatSession::where('id', $sessionId)->update(['session_title' => $title]);
            }
        } catch (\Exception $e) {
            Log::warning('Auto-title failed', ['session_id' => $sessionId, 'error' => $e->getMessage()]);
        }
    }
}
