<?php

namespace App\Services\Record7;

/**
 * The outcome of one authorisation question.
 *
 * A boolean would be enough to gate the action, but not enough to audit it or
 * to tell the person anything useful. Every refusal carries a machine code for
 * the audit trail and a sentence written for the person on the screen.
 */
class AccessDecision
{
    private function __construct(
        public readonly bool $allowed,
        public readonly ?string $code = null,
        public readonly ?string $message = null
    ) {
    }

    public static function allow(): self
    {
        return new self(true);
    }

    public static function deny(string $code, string $message): self
    {
        return new self(false, $code, $message);
    }

    public function denied(): bool
    {
        return ! $this->allowed;
    }

    /** How severely a refusal should be logged. */
    public function riskLevel(): string
    {
        return match ($this->code) {
            'account_suspended', 'account_security_locked' => 'high',
            'explicit_deny' => 'medium',
            default => $this->allowed ? 'none' : 'low',
        };
    }
}
