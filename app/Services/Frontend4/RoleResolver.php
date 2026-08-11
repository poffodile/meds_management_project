<?php

namespace App\Services\Frontend4;

use Illuminate\Support\Facades\DB;

/**
 * Works out which of frontend4's four roles a user has.
 *
 * WHY THIS EXISTS
 * The application has two overlapping ideas of "role" and neither is usable on
 * its own:
 *
 *   1. `user.user_type` — a coarse enum (N, A, M, CM, O) on the account.
 *   2. `user.access_level` — the ID of a row in `access_level`, which each HOME
 *      defines for itself. Across 46 homes there are 82 of these rows using
 *      **40 different names** for what is really about four roles: "Staff",
 *      "RSW", "Senior RSW", "Deputy Manager", "Floating Support", "Bank staff",
 *      and so on. Some are not roles at all — `azure`, `acc`, `AccessTest`.
 *
 * Neither tier alone answers "may this person witness a controlled drug". This
 * class turns both into one of four roles, and it is the ONLY place that
 * mapping is written down.
 *
 * WHAT THIS IS NOT
 * It is not the permission check. It answers "who is this person"; see
 * {@see Permissions} for "what may they do". Keeping the two apart means the
 * mapping can be corrected without touching a single permission rule.
 *
 * Mapping confirmed with the product owner 2026-08-04.
 */
class RoleResolver
{
    /** The four roles. Ordered least to most privileged. */
    public const CARER   = 'carer';
    public const LEAD    = 'lead';
    public const MANAGER = 'manager';
    public const ADMIN   = 'admin';

    /** No access to medication at all — finance staff, and test data. */
    public const NONE = 'none';

    public const ROLES = [self::CARER, self::LEAD, self::MANAGER, self::ADMIN];

    /** Human labels, for showing a person what they are signed in as. */
    public const LABELS = [
        self::CARER   => 'Support worker',
        self::LEAD    => 'Shift lead',
        self::MANAGER => 'Manager',
        self::ADMIN   => 'Administrator',
        self::NONE    => 'No medication access',
    ];

    /**
     * Every access-level name in the database, mapped to a role.
     *
     * Keys are normalised: lowercased, trimmed, internal whitespace collapsed.
     * That matters — the live data contains "Deputy manager" alongside
     * "Deputy Manager", and "Account  Manager" with two spaces.
     *
     * If a home invents a NEW name tomorrow it will not be here; see resolve()
     * for what happens then.
     */
    private const BY_NAME = [
        // ── Support workers ────────────────────────────────────────────────
        'staff'                       => self::CARER,
        'rsw'                         => self::CARER,
        'residential support worker'  => self::CARER,
        'support worker'              => self::CARER,
        'omega support worker'        => self::CARER,
        'bank support worker'         => self::CARER,
        'bank staff'                  => self::CARER,
        'agency support worker'       => self::CARER,
        'agent'                       => self::CARER, // agency worker — confirmed 2026-08-04
        'floating support'            => self::CARER,
        'carer'                       => self::CARER,
        'general'                     => self::CARER,
        'default staff access'        => self::CARER,
        'default access'              => self::CARER,

        // ── Shift leads ────────────────────────────────────────────────────
        // Confirmed 2026-08-04: seniors CAN check another carer's work —
        // witness a controlled drug, correct a record, reopen a round. Without
        // that, a senior alone on a night shift has nobody to witness with,
        // which stops a controlled drug being given at all.
        'senior staff'  => self::LEAD,
        'senior rsw'    => self::LEAD,
        'senior support worker' => self::LEAD,
        'senior carer'  => self::LEAD,
        'nurse'         => self::LEAD,
        'team leader'   => self::LEAD,
        'sr supervisor' => self::LEAD,

        // ── Managers ───────────────────────────────────────────────────────
        'manager'        => self::MANAGER,
        'deputy manager' => self::MANAGER,
        'home manager'   => self::MANAGER,
        'manager access' => self::MANAGER,
        'line manager'   => self::MANAGER,

        // ── Administrators ─────────────────────────────────────────────────
        // "Manager Admin" reads as the higher of the two. "Owner" sits here
        // because owners configure the organisation; if an owner should
        // outrank an administrator, that is a new role, not a remapping.
        'admin'         => self::ADMIN,
        'main admin'    => self::ADMIN,
        'home admin'    => self::ADMIN,
        'system admin'  => self::ADMIN,
        'manager admin' => self::ADMIN,
        'owner'         => self::ADMIN,

        // ── No medication access ───────────────────────────────────────────
        // Finance, confirmed 2026-08-04 — no clinical access of any kind.
        'account manager' => self::NONE,
        // Test and leftover rows found in the live data. Listed explicitly
        // rather than guessed at, so nobody later mistakes them for real roles.
        'aa'                 => self::NONE,
        'ab'                 => self::NONE,
        'acc'                => self::NONE,
        'azure'              => self::NONE,
        'accesstest'         => self::NONE,
        'initial testing'    => self::NONE,
        'test access level'  => self::NONE,
        'test hq'            => self::NONE,
        'jesse daniels level'=> self::NONE,
        'vidhayak'           => self::NONE,
    ];

    /**
     * Fallback when a user has no access level.
     *
     * An account with an explicit but unrecognised access level fails closed in
     * resolve(). Otherwise a newly invented or misspelled level could silently
     * acquire clinical access from the coarse account type.
     */
    private const BY_USER_TYPE = [
        'N'  => self::CARER,
        'M'  => self::MANAGER,
        'CM' => self::MANAGER,
        'A'  => self::ADMIN,
        'O'  => self::ADMIN,
    ];

    /** Cache of access_level id => normalised name, for this request only. */
    private array $levelNames = [];

    /**
     * The role for a user.
     *
     * An access level that maps to NONE always wins over the account type: a
     * finance account with `user_type = 'A'` must not become an administrator
     * of clinical data on a technicality.
     */
    public function resolve($user): string
    {
        if (! $user) {
            return self::NONE;
        }

        $accessLevelId = (int) ($user->access_level ?? 0);
        $name = $this->levelName($accessLevelId);

        if ($name !== null && isset(self::BY_NAME[$name])) {
            return self::BY_NAME[$name];
        }

        if ($accessLevelId > 0) {
            return self::NONE;
        }

        return self::BY_USER_TYPE[$user->user_type ?? ''] ?? self::NONE;
    }

    /** Does this user have any medication access at all? */
    public function hasAccess($user): bool
    {
        return $this->resolve($user) !== self::NONE;
    }

    public function label(string $role): string
    {
        return self::LABELS[$role] ?? $role;
    }

    /**
     * Access-level rows whose name this class does not recognise.
     *
     * For an administration screen: these levels are denied until somebody
     * confirms and explicitly maps them.
     * Returns ['id' => ..., 'name' => ..., 'home_id' => ...].
     */
    public function unmappedLevels(): array
    {
        return DB::table('access_level')
            ->where('is_deleted', 0)
            ->get(['id', 'name', 'home_id'])
            ->filter(fn ($row) => ! isset(self::BY_NAME[$this->normalise($row->name)]))
            ->values()
            ->map(fn ($row) => (array) $row)
            ->all();
    }

    /** Look up an access level's name by its id, once per request. */
    private function levelName($accessLevelId): ?string
    {
        $id = (int) $accessLevelId;
        if ($id <= 0) {
            return null;
        }

        if (! array_key_exists($id, $this->levelNames)) {
            $name = DB::table('access_level')->where('id', $id)->value('name');
            $this->levelNames[$id] = $name === null ? null : $this->normalise($name);
        }

        return $this->levelNames[$id];
    }

    /** Lowercase, trim, collapse internal whitespace. */
    private function normalise(?string $name): string
    {
        return preg_replace('/\s+/', ' ', trim(strtolower((string) $name)));
    }
}
