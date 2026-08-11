<?php

return [
    'max_attempts' => (int) env('AUTH_MAX_ATTEMPTS', 5),
    'decay_seconds' => (int) env('AUTH_DECAY_SECONDS', 900),
    'lockout_minutes' => (int) env('AUTH_LOCKOUT_MINUTES', 15),
    'password_token_minutes' => (int) env('AUTH_PASSWORD_TOKEN_MINUTES', 30),
];
