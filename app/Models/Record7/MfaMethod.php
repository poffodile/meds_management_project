<?php

namespace App\Models\Record7;

class MfaMethod extends Record7Model
{
    protected $table = 'record7_mfa_methods';

    protected $casts = [
        'is_primary' => 'boolean',
        'registered_at' => 'datetime',
        'last_verified_at' => 'datetime',
    ];

    /** How the person is asked for their code, in words they will recognise. */
    public function prompt(): string
    {
        return match ($this->method_type) {
            'passkey' => 'Use your passkey',
            'security_key' => 'Use your security key',
            'authenticator_app' => 'Open your authenticator app',
            'hardware_otp' => 'Use your security token',
            'work_email' => 'Check your work email',
            'sms' => 'Check your text messages',
            default => 'Verify your identity',
        };
    }
}
