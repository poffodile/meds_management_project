<?php

namespace App\Services;

use App\Models\AuthenticationEvent;
use App\Models\PasswordActionToken;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuthenticationSecurityService
{
    public function isLocked(Model $account): bool
    {
        return $account->locked_until !== null && now()->isBefore($account->locked_until);
    }

    public function registerFailure(Model $account, Request $request, string $identifier): void
    {
        $attempts = ((int) $account->failed_login_attempts) + 1;
        $account->failed_login_attempts = $attempts;

        if ($attempts >= config('auth_security.max_attempts')) {
            $account->locked_until = now()->addMinutes(config('auth_security.lockout_minutes'));
        }

        $account->save();
        $this->record($request, 'login_failed', false, $account, $identifier);
    }

    public function registerSuccess(Model $account, Request $request, string $identifier): void
    {
        $account->forceFill([
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        $this->record($request, 'login_succeeded', true, $account, $identifier);
    }

    public function issuePasswordToken(
        Model $account,
        Request $request,
        string $purpose = 'password_reset'
    ): string {
        return DB::transaction(function () use ($account, $request, $purpose) {
            PasswordActionToken::query()
                ->where('authenticatable_type', $this->accountType($account))
                ->where('authenticatable_id', $account->getKey())
                ->where('purpose', $purpose)
                ->whereNull('used_at')
                ->update(['used_at' => now()]);

            $plainToken = Str::random(64);

            PasswordActionToken::create([
                'id' => (string) Str::uuid(),
                'authenticatable_type' => $this->accountType($account),
                'authenticatable_id' => $account->getKey(),
                'token_hash' => hash('sha256', $plainToken),
                'purpose' => $purpose,
                'expires_at' => now()->addMinutes(config('auth_security.password_token_minutes')),
                'requested_ip' => $request->ip(),
            ]);

            $this->record($request, $purpose.'_requested', true, $account, $account->email ?? null);

            return $plainToken;
        });
    }

    public function validPasswordToken(string $plainToken, ?string $purpose = null): ?PasswordActionToken
    {
        return PasswordActionToken::query()
            ->where('token_hash', hash('sha256', $plainToken))
            ->when($purpose, fn ($query) => $query->where('purpose', $purpose))
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();
    }

    public function consumePasswordToken(
        PasswordActionToken $token,
        Model $account,
        Request $request,
        string $passwordHash
    ): void {
        DB::transaction(function () use ($token, $account, $request, $passwordHash) {
            $affected = PasswordActionToken::query()
                ->whereKey($token->getKey())
                ->whereNull('used_at')
                ->where('expires_at', '>', now())
                ->update(['used_at' => now()]);

            if ($affected !== 1) {
                throw new \RuntimeException('The password link is no longer valid.');
            }

            $account->forceFill([
                'password' => $passwordHash,
                'password_changed_at' => now(),
                'force_password_reset' => false,
                'failed_login_attempts' => 0,
                'locked_until' => null,
                'security_code' => null,
            ])->save();

            $this->record($request, $token->purpose.'_completed', true, $account, $account->email ?? null);
        });
    }

    public function record(
        Request $request,
        string $event,
        bool $successful,
        ?Model $account = null,
        ?string $identifier = null,
        array $metadata = []
    ): void {
        AuthenticationEvent::create([
            'actor_type' => $account ? $this->accountType($account) : null,
            'actor_id' => $account?->getKey(),
            'identifier_hash' => $identifier
                ? hash('sha256', Str::lower(trim($identifier)))
                : null,
            'event_type' => $event,
            'successful' => $successful,
            'ip_address' => $request->ip(),
            'user_agent_hash' => $request->userAgent()
                ? hash('sha256', $request->userAgent())
                : null,
            'metadata' => $metadata ?: null,
        ]);
    }

    public function accountModel(PasswordActionToken $token): ?Model
    {
        $class = match ($token->authenticatable_type) {
            'user' => \App\User::class,
            'admin' => \App\Admin::class,
            'service_user' => \App\ServiceUser::class,
            default => null,
        };

        return $class ? $class::query()->find($token->authenticatable_id) : null;
    }

    private function accountType(Model $account): string
    {
        return match (true) {
            $account instanceof \App\User => 'user',
            $account instanceof \App\Admin => 'admin',
            $account instanceof \App\ServiceUser => 'service_user',
            default => throw new \InvalidArgumentException('Unsupported account type.'),
        };
    }
}
