<?php

return [
    'enabled' => env('AI_ENABLED', true),
    'api_key' => env('OPENAI_API_KEY'),
    'default_model' => env('AI_DEFAULT_MODEL', 'gpt-4o-mini'),
    'quality_model' => env('AI_QUALITY_MODEL', 'gpt-4o'),
    'max_tokens_per_response' => 2000,
    'daily_token_cap' => env('AI_DAILY_TOKEN_CAP', 100000),
    'pii_mode' => env('AI_PII_MODE', 'anonymise'),
    'max_context_messages' => 10,
    'request_timeout' => 120,
];
