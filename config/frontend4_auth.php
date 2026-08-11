<?php

return [
    'max_attempts' => (int) env('FRONTEND4_AUTH_MAX_ATTEMPTS', 5),
    'decay_seconds' => (int) env('FRONTEND4_AUTH_DECAY_SECONDS', 900),
    'lockout_minutes' => (int) env('FRONTEND4_AUTH_LOCKOUT_MINUTES', 15),
    'password_token_minutes' => (int) env('FRONTEND4_PASSWORD_TOKEN_MINUTES', 30),
    'idle_minutes' => (int) env('FRONTEND4_IDLE_MINUTES', 30),
];
