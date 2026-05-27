<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class OpenAIService
{
    private const API_URL = 'https://api.openai.com/v1/chat/completions';

    public function chat(array $messages, string $model = null, array $options = []): array
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('AI is not configured. Check OPENAI_API_KEY in .env.');
        }

        $model = $model ?? config('ai.default_model', 'gpt-4o-mini');

        $payload = [
            'model' => $model,
            'messages' => $messages,
            'max_tokens' => $options['max_tokens'] ?? config('ai.max_tokens_per_response', 2000),
            'temperature' => $options['temperature'] ?? 0.7,
        ];

        if (!empty($options['response_format'])) {
            $payload['response_format'] = $options['response_format'];
        }

        return $this->makeRequest($payload);
    }

    public function chatJson(array $messages, string $model = null, array $options = []): array
    {
        $options['response_format'] = ['type' => 'json_object'];
        return $this->chat($messages, $model, $options);
    }

    public function isConfigured(): bool
    {
        return config('ai.enabled') && !empty(config('ai.api_key'));
    }

    private function makeRequest(array $payload): array
    {
        $startTime = microtime(true);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('ai.api_key'),
                'Content-Type' => 'application/json',
            ])->timeout(config('ai.request_timeout', 30))
              ->post(self::API_URL, $payload);

            $latencyMs = (int) ((microtime(true) - $startTime) * 1000);

            if ($response->status() === 429) {
                throw new RuntimeException('AI rate limit exceeded. Please try again later.');
            }

            if (!$response->successful()) {
                Log::error('OpenAI API error', [
                    'status' => $response->status(),
                    'body' => substr($response->body(), 0, 500),
                ]);
                throw new RuntimeException('AI service is temporarily unavailable.');
            }

            $data = $response->json();

            if (empty($data['choices'][0]['message']['content'])) {
                throw new RuntimeException('AI returned an empty response.');
            }

            $usage = $data['usage'] ?? [];

            return [
                'content' => $data['choices'][0]['message']['content'],
                'tokens_input' => $usage['prompt_tokens'] ?? 0,
                'tokens_output' => $usage['completion_tokens'] ?? 0,
                'model' => $data['model'] ?? $payload['model'],
                'latency_ms' => $latencyMs,
            ];
        } catch (RuntimeException $e) {
            throw $e;
        } catch (\Exception $e) {
            $latencyMs = (int) ((microtime(true) - $startTime) * 1000);
            Log::error('OpenAI API exception', [
                'error' => $e->getMessage(),
                'latency_ms' => $latencyMs,
            ]);
            throw new RuntimeException('AI service is temporarily unavailable.');
        }
    }
}
