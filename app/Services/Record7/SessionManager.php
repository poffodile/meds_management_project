<?php

namespace App\Services\Record7;

use App\Models\Record7\LoginSession;
use App\Models\Record7\Service;
use App\Models\Record7\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * The life of a Record7 session: started, scoped to a house, locked, switched,
 * ended.
 *
 * Section 0.9, plus the session half of 0.4.
 *
 * WHY A DATABASE ROW AND NOT JUST THE PHP SESSION
 * A shared tablet on a medicines round is used by several people in an hour.
 * The audit has to say which device a session ran on and whether it was locked
 * or signed out, and a manager needs to see that. A cookie cannot answer those
 * questions after the fact, so every session is also a row.
 *
 * LOCK IS NOT SIGN-OUT
 * Locking keeps the session and the chosen house and asks only for the
 * password again. That is the behaviour a round needs: put the tablet down,
 * pick it up, carry on. Signing out ends the session and clears the house.
 */
class SessionManager
{
    /** Idle minutes before the screen locks itself. */
    public const IDLE_LOCK_MINUTES = 5;

    /** Idle minutes before the session is over entirely. */
    public const IDLE_END_MINUTES = 60;

    /* ── Session keys ────────────────────────────────────────────────────── */

    public const ORGANISATION = 'record7.organisation_id';

    public const SERVICE = 'record7.service_id';

    public const SESSION_REF = 'record7.session_reference';

    public const LAST_ACTIVITY = 'record7.last_activity';

    public const INTENDED = 'record7.intended';

    public const PENDING_USER = 'record7.pending_user_id';

    public const PENDING_ORG = 'record7.pending_organisation_id';

    public const PENDING_AT = 'record7.pending_at';

    public function __construct(private readonly AuditRecorder $audit)
    {
    }

    /* ── Starting ────────────────────────────────────────────────────────── */

    public function start(Request $request, User $user, int $organisationId): LoginSession
    {
        Auth::guard('record7')->login($user);
        $request->session()->regenerate();

        $session = LoginSession::create([
            'reference' => (string) Str::uuid(),
            'user_id' => $user->id,
            'organisation_id' => $organisationId,
            'active_service_id' => null,
            'status' => 'active',
            'device_reference' => $this->device($request),
            'started_at' => now(),
            'last_activity_at' => now(),
        ]);

        $request->session()->put([
            self::ORGANISATION => $organisationId,
            self::SESSION_REF => $session->reference,
            self::LAST_ACTIVITY => time(),
        ]);

        return $session;
    }

    /* ── Choosing and switching a house ──────────────────────────────────── */

    public function selectService(Request $request, Service $service): void
    {
        $request->session()->put(self::SERVICE, $service->id);
        $this->touch($request);

        if ($session = $this->current($request)) {
            $session->active_service_id = $service->id;
            $session->last_activity_at = now();
            $session->save();
        }
    }

    /* ── Locking ─────────────────────────────────────────────────────────── */

    /**
     * Lock the screen without ending the session.
     *
     * The signed-in identity and the chosen house both survive; only the
     * ability to act is withheld until the password is given again.
     */
    public function lock(Request $request, string $reason = 'Locked by the user.'): void
    {
        $session = $this->current($request);

        if ($session && $session->status === 'active') {
            $session->status = 'locked';
            $session->locked_at = now();
            $session->save();

            $this->audit->record(
                'session_locked',
                AuditRecorder::INFORMATION,
                $this->user(),
                $request->session()->get(self::ORGANISATION),
                $request->session()->get(self::SERVICE),
                $reason,
                'none',
                [],
                $request,
                $session
            );
        }
    }

    public function unlock(Request $request): void
    {
        $session = $this->current($request);

        if ($session && $session->status === 'locked') {
            $session->status = 'active';
            $session->locked_at = null;
            $session->last_activity_at = now();
            $session->save();

            $this->audit->record(
                'session_unlocked',
                AuditRecorder::SUCCESS,
                $this->user(),
                $request->session()->get(self::ORGANISATION),
                $request->session()->get(self::SERVICE),
                null,
                'none',
                [],
                $request,
                $session
            );
        }

        $this->touch($request);
    }

    public function isLocked(Request $request): bool
    {
        return (bool) $this->current($request)?->isLocked();
    }

    /* ── Idle ────────────────────────────────────────────────────────────── */

    public function idleSeconds(Request $request): int
    {
        $last = (int) $request->session()->get(self::LAST_ACTIVITY, 0);

        return $last > 0 ? time() - $last : 0;
    }

    public function shouldAutoLock(Request $request): bool
    {
        return $this->idleSeconds($request) > self::IDLE_LOCK_MINUTES * 60;
    }

    public function shouldEnd(Request $request): bool
    {
        return $this->idleSeconds($request) > self::IDLE_END_MINUTES * 60;
    }

    public function touch(Request $request): void
    {
        $request->session()->put(self::LAST_ACTIVITY, time());

        if ($session = $this->current($request)) {
            $session->last_activity_at = now();
            $session->save();
        }
    }

    /* ── Ending ──────────────────────────────────────────────────────────── */

    public function end(Request $request, string $status = 'signed_out'): void
    {
        if ($session = $this->current($request)) {
            if ($session->isLive()) {
                $session->status = $status;
                $session->ended_at = now();
                $session->save();
            }
        }

        Auth::guard('record7')->logout();

        $request->session()->forget([
            self::ORGANISATION, self::SERVICE, self::SESSION_REF, self::LAST_ACTIVITY,
            self::INTENDED, self::PENDING_USER, self::PENDING_ORG, self::PENDING_AT,
        ]);

        $request->session()->regenerateToken();
    }

    /* ── Reading ─────────────────────────────────────────────────────────── */

    public function current(Request $request): ?LoginSession
    {
        $reference = $request->session()->get(self::SESSION_REF);

        return $reference ? LoginSession::where('reference', $reference)->first() : null;
    }

    public function user(): ?User
    {
        $user = Auth::guard('record7')->user();

        return $user instanceof User ? $user : null;
    }

    public function organisationId(Request $request): int
    {
        return (int) $request->session()->get(self::ORGANISATION, 0);
    }

    public function serviceId(Request $request): ?int
    {
        $id = (int) $request->session()->get(self::SERVICE, 0);

        return $id > 0 ? $id : null;
    }

    private function device(Request $request): ?string
    {
        $agent = $request->userAgent();

        return $agent ? 'device-'.substr(hash('sha256', $agent), 0, 12) : null;
    }
}
