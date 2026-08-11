<?php

namespace App\Services\Frontend4;

use App\Models\Frontend4AuthenticationEvent;
use App\Models\Frontend4Credential;
use App\Models\Frontend4PasswordToken;
use App\Models\Frontend4User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthenticationSecurityService
{
    public function passwordMatches(Frontend4User $user, string $plainPassword): bool
    {
        $hash = Frontend4Credential::where('user_id', $user->id)->value('password_hash')
            ?: $user->getAuthPassword();

        return is_string($hash) && $hash !== '' && Hash::check($plainPassword, $hash);
    }

    public function isLocked(Frontend4User $user): bool
    {
        $credential = Frontend4Credential::where('user_id', $user->id)->first();

        return $credential?->locked_until !== null && now()->isBefore($credential->locked_until);
    }

    public function registerFailure(Frontend4User $user, Request $request, string $identifier): void
    {
        $credential = $this->credentialFor($user);
        $attempts = ((int) $credential->failed_login_attempts) + 1;
        $credential->failed_login_attempts = $attempts;

        if ($attempts >= config('frontend4_auth.max_attempts')) {
            $credential->locked_until = now()->addMinutes(config('frontend4_auth.lockout_minutes'));
        }

        $credential->save();
        $this->record($request, 'login_failed', false, $user, $identifier);
    }

    public function registerSuccess(Frontend4User $user, Request $request, string $identifier): void
    {
        $this->credentialFor($user)->forceFill([
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        $this->record($request, 'login_succeeded', true, $user, $identifier);
    }

    public function issuePasswordToken(Frontend4User $user, Request $request): string
    {
        return DB::transaction(function () use ($user, $request) {
            Frontend4PasswordToken::where('user_id', $user->id)
                ->whereNull('used_at')
                ->update(['used_at' => now()]);

            $plainToken = Str::random(64);
            Frontend4PasswordToken::create([
                'id' => (string) Str::uuid(),
                'user_id' => $user->id,
                'token_hash' => hash('sha256', $plainToken),
                'expires_at' => now()->addMinutes(config('frontend4_auth.password_token_minutes')),
                'requested_ip' => $request->ip(),
                'created_at' => now(),
            ]);

            $this->record($request, 'password_reset_requested', true, $user, $user->email);

            return $plainToken;
        });
    }

    public function validPasswordToken(string $plainToken): ?Frontend4PasswordToken
    {
        return Frontend4PasswordToken::where('token_hash', hash('sha256', $plainToken))
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();
    }

    public function consumePasswordToken(
        Frontend4PasswordToken $token,
        Frontend4User $user,
        Request $request,
        string $plainPassword
    ): void {
        DB::transaction(function () use ($token, $user, $request, $plainPassword) {
            $updated = Frontend4PasswordToken::whereKey($token->getKey())
                ->whereNull('used_at')
                ->where('expires_at', '>', now())
                ->update(['used_at' => now()]);

            if ($updated !== 1) {
                throw new \RuntimeException('The password link is no longer valid.');
            }

            $this->credentialFor($user)->forceFill([
                'password_hash' => Hash::make($plainPassword),
                'password_changed_at' => now(),
                'force_password_reset' => false,
                'failed_login_attempts' => 0,
                'locked_until' => null,
            ])->save();

            $this->record($request, 'password_reset_completed', true, $user, $user->email);
        });
    }

    public function record(
        Request $request,
        string $event,
        bool $successful,
        ?Frontend4User $user = null,
        ?string $identifier = null,
        array $metadata = []
    ): void {
        Frontend4AuthenticationEvent::create([
            'user_id' => $user?->id,
            'identifier_hash' => $identifier ? hash('sha256', Str::lower(trim($identifier))) : null,
            'event_type' => $event,
            'successful' => $successful,
            'ip_address' => $request->ip(),
            'user_agent_hash' => $request->userAgent() ? hash('sha256', $request->userAgent()) : null,
            'metadata' => $metadata ?: null,
            'created_at' => now(),
        ]);
    }

    private function credentialFor(Frontend4User $user): Frontend4Credential
    {
        return Frontend4Credential::firstOrCreate(
            ['user_id' => $user->id],
            ['password_hash' => (string) $user->getAuthPassword()]
        );
    }
}
