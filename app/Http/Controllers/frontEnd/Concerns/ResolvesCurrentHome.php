<?php

namespace App\Http\Controllers\frontEnd\Concerns;

use Illuminate\Support\Facades\Auth;

/**
 * Single source of truth for "which home is this manager currently looking at".
 *
 * Replaces the copy-pasted `explode(',', $user->home_id)[0]` (audit CR-06), which
 * silently pinned every multi-home manager to their FIRST home with no way to switch
 * and no indication the others existed — a real oversight risk (a manager could
 * believe they had reviewed every home's round having only ever seen home #1). The
 * middleware (checkUserAuth) already tracks a chosen home in the session; this reads
 * it, and the switch endpoint writes it.
 *
 * IMPORTANT — App\User already has a `home_id` ACCESSOR that returns
 * session('active_home_id') when set (else the first of the comma list). So
 * `$user->home_id` is the ALREADY-RESOLVED single home, NOT the list — and the accessor
 * returns the session value WITHOUT checking the user is allowed it. To get the true list
 * of homes a user may access we must read `$user->real_home_id` (the raw column for
 * M/A/CM; the whole company's homes for an owner O), never `$user->home_id`.
 *
 * SECURITY: the session's chosen home is always re-validated here against that real list.
 * A tampered session cannot make THIS resolver select a home outside the user's access.
 * (The accessor itself is not hardened — that is owner-owned App\User with a wide blast
 * radius — but the switch endpoint validates before writing the session, so no legitimate
 * flow ever puts a forbidden home there.)
 */
trait ResolvesCurrentHome
{
    /** The homes this user may access, as a list of ints, in their stored order. */
    protected function allowedHomeIds(): array
    {
        // real_home_id, NOT home_id — see the class note. home_id is the resolved single
        // home; real_home_id is the actual list.
        $raw = (string) (Auth::user()->real_home_id ?? '');

        return collect(explode(',', $raw))
            ->map(fn ($h) => (int) trim($h))
            ->filter()          // drop empties / zeros
            ->unique()
            ->values()
            ->all();
    }

    /**
     * The home currently in view: the session's chosen home if the user is genuinely
     * allowed it, otherwise the first home they can access. Never returns a home
     * outside the user's own list.
     */
    protected function currentHomeId(): int
    {
        $allowed = $this->allowedHomeIds();

        $chosen = (int) session('active_home_id');
        if ($chosen && in_array($chosen, $allowed, true)) {
            return $chosen;
        }

        return $allowed[0] ?? 0;
    }
}
