<?php

namespace App\Models\Record7;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * A Record7 person.
 *
 * Record7 keeps its own accounts in its own database. It shares no row, and no
 * login, with the legacy system or with Frontend 4.
 */
class User extends Authenticatable implements AuthenticatableContract
{
    protected $connection = 'record7';

    protected $table = 'record7_users';

    protected $guarded = [];

    protected $hidden = ['password_hash'];

    protected $casts = [
        'access_starts_at' => 'datetime',
        'access_ends_at' => 'datetime',
        'locked_until' => 'datetime',
        'password_set_at' => 'datetime',
        'last_signed_in_at' => 'datetime',
        'failed_attempts' => 'integer',
    ];

    public function getAuthPassword(): string
    {
        return (string) $this->password_hash;
    }

    /* ── Relationships ───────────────────────────────────────────────────── */

    public function organisation()
    {
        return $this->belongsTo(Organisation::class, 'organisation_id');
    }

    public function roleAssignments()
    {
        return $this->hasMany(UserRole::class, 'user_id');
    }

    public function serviceAccess()
    {
        return $this->hasMany(UserServiceAccess::class, 'user_id');
    }

    public function permissionRules()
    {
        return $this->hasMany(UserPermission::class, 'user_id');
    }

    public function competencies()
    {
        return $this->hasMany(UserCompetency::class, 'user_id');
    }

    public function mfaMethods()
    {
        return $this->hasMany(MfaMethod::class, 'user_id');
    }

    public function invitations()
    {
        return $this->hasMany(AccountInvitation::class, 'user_id');
    }

    public function passwordResets()
    {
        return $this->hasMany(PasswordReset::class, 'user_id');
    }

    public function sessions()
    {
        return $this->hasMany(LoginSession::class, 'user_id');
    }

    /* ── State ───────────────────────────────────────────────────────────── */

    /** The account's own role, ignoring service scope. */
    public function primaryRole(): ?Role
    {
        return $this->roleAssignments()->with('role')->get()
            ->map(fn (UserRole $assignment) => $assignment->role)
            ->filter()
            ->sortByDesc('privilege_level')
            ->first();
    }

    public function isLockedOut(): bool
    {
        return $this->locked_until !== null && now()->lessThan($this->locked_until);
    }

    /** Is today inside the account's access window? */
    public function withinAccessWindow(): bool
    {
        $now = now();

        if ($this->access_starts_at && $now->lessThan($this->access_starts_at)) {
            return false;
        }

        return ! ($this->access_ends_at && $now->greaterThan($this->access_ends_at));
    }

    /**
     * Why this account may not sign in, or null when it may.
     *
     * A single place that names every refusal, so the sign-in screen, the
     * per-request guard and the audit trail all agree on the reason.
     */
    public function accessRefusalReason(): ?string
    {
        if ($this->account_status === 'invited') {
            return 'invited';
        }

        if ($this->account_status === 'suspended') {
            return 'suspended';
        }

        if ($this->account_status === 'inactive') {
            return 'inactive';
        }

        if ($this->account_status === 'access_expired' || ! $this->withinAccessWindow()) {
            return 'access_expired';
        }

        if ($this->account_status === 'security_locked' || $this->isLockedOut()) {
            return 'security_locked';
        }

        return null;
    }

    public function displayName(): string
    {
        return $this->preferred_name ?: $this->full_name;
    }
}
