# Care One OS current engineering handoff

Last updated: 13 August 2026

This is the current starting point for Codex, Claude Code or another engineer. Read it before editing the repository. The older `CODEX-CLAUDE-HANDOVER.md` remains historical context; where it conflicts with this file, this file is authoritative.

## Repository and branch safety

- Repository: `poffodile/meds_management_project`
- Active integration branch: `care-one-integration`
- Preserved baseline branch: `frontend4`
- Do not commit directly to, merge into, rebase or rewrite `frontend4`.
- Continue production-readiness work only on `care-one-integration` unless the owner explicitly changes this instruction.
- GitHub commits must not name Codex, ChatGPT, Claude, Anthropic or OpenAI as author or co-author.
- The latest confirmed Requirement 2b commit is `2199a57ffc7982a673e63c2781fbe49da2738e3e`.

## Why this branch exists

The user wanted a safe copy of the existing Laravel project so production work could continue without changing the working Frontend 4 baseline. `care-one-integration` is that copy. It will receive database, authentication, permissions and other production requirements one at a time.

Frontend 4 already has isolated React, Inertia and CSS entry points. Authentication originally still used shared legacy routes, which meant hardening `/login`, `/admin/login`, `/logout` and mobile API login could affect Frontends 1–3. That was not acceptable to the owner.

## Requirement 1: database structure

The supplied MySQL dump was inspected as confidential material. It contains the Laravel system's real schema and data, including medication, MAR, stock, controlled-drug, handover, client and account tables. Do not reproduce personal information, password hashes, API keys or sensitive records in documentation, logs, fixtures or commits.

Important ongoing database concerns include weak explicit foreign-key coverage, duplicated/hardcoded clinical units and values, and relationships enforced mainly by application code. These require staged review; do not make broad destructive schema changes.

## Requirement 2: login and password handling

Requirement 2 is implemented specifically for Frontend 4.

What was done:

- Added a separate `frontend4` Laravel session guard and provider.
- Added a dedicated Care One OS login at `/frontend4/login`.
- Added Frontend 4-only logout, forgotten-password and reset-password routes.
- Added server-side throttling, persistent lockouts, generic failure responses and 30-minute idle handling.
- Added strong 12-character password validation for Frontend 4 resets.
- Added one-use, hashed and expiring Frontend 4 password tokens.
- Added append-only Frontend 4 authentication events.
- Added a responsive React login/reset experience using only the isolated `f4` bundle and `.f4-root` CSS.
- Moved every Frontend 4 clinical route outside the legacy `checkUserAuth` group and behind `frontend4.auth`.
- Added a Frontend 4 sign-out action to its own shell.

Why separate credential tables were used:

- A Frontend 4 password reset must not change the legacy `user.password` value.
- Frontend 4 lockouts and sessions must not lock users out of the older application.
- Legacy `/login`, `/logout`, `/admin/login`, API login controllers, views and session configuration had to remain unchanged.
- When a Frontend 4 credential is first needed, it starts from the existing one-way password hash. Plaintext passwords are never copied or stored.

Dedicated tables:

- `frontend4_credentials`
- `frontend4_password_tokens`
- `frontend4_authentication_events`

Primary implementation files:

- `app/Http/Controllers/Frontend4/AuthenticationController.php`
- `app/Http/Middleware/Frontend4Authenticate.php`
- `app/Services/Frontend4/AuthenticationSecurityService.php`
- `app/Models/Frontend4Credential.php`
- `app/Models/Frontend4PasswordToken.php`
- `app/Models/Frontend4AuthenticationEvent.php`
- `database/migrations/2026_08_11_000001_create_frontend4_authentication_tables.php`
- `resources/js/F4Pages/Auth/`
- `tests/Feature/Frontend4AuthenticationIsolationTest.php`

## Authentication migration and test status

The owner confirmed on 11 August 2026 that the dedicated authentication migration was applied locally to both the development and test databases. Exactly the three Frontend 4 authentication tables were added. `Frontend4AuthenticationIsolationTest` passed with 4 tests and 14 assertions. Two unrelated migrations were deliberately left pending.

Run this first in a configured local or staging environment—not production:

```bash
php artisan migrate:status
php artisan migrate
```

On the owner's Windows/Laragon setup, PHP may not be globally available. If needed, use the configured Laragon PHP executable from the project environment. Do not guess a production database target.

After migrating, verify that only the three `frontend4_*` authentication tables were added. This migration must not alter `user`, `admin`, `service_user` or the legacy password/session tables.

Then run:

```bash
php artisan route:list --path=frontend4
php artisan test --filter=Frontend4AuthenticationIsolationTest
npm test
npm run build
```

Before any production migration:

1. Confirm the environment and database name.
2. Take and verify a restorable backup.
3. Review the migration SQL or use `php artisan migrate --pretend`.
4. Test login, reset, logout and legacy-login isolation in staging.
5. Prepare and test rollback.
6. Obtain explicit production approval.

## Verification already completed

- All 22 available JavaScript tests passed.
- The Vite production build passed.
- All changed PHP files passed PHP syntax parsing.
- Full Laravel/PHP feature tests remain to be executed after the migration in a configured PHP environment.
- GitHub confirmed the integration branch is two commits ahead of `frontend4` and that the baseline is unchanged.

## Non-negotiable isolation checks

When changing Frontend 4 authentication or permissions:

- Do not replace or redirect `/login`, `/logout` or `/admin/login`.
- Do not change legacy API authentication routes.
- Do not use the default `web` guard for Frontend 4.
- Do not write Frontend 4 reset passwords into `user.password`.
- Do not store plaintext passwords, reset tokens or API secrets.
- Do not place Frontend 4 routes back inside `checkUserAuth`.
- Keep all Frontend 4 styles under `.f4-root` and out of global stylesheets.
- Preserve organisation/service scoping and revalidate selected service IDs server-side.

## Requirement 3: role permissions

Requirement 3 is implemented and confirmed. On 11 August 2026, `Frontend4PermissionTest` passed with 6 tests and 40 assertions against the isolated `laravel_test` database.

What was added:

- Explicit page permissions for Today, Round, Clients and MAR.
- Route middleware enforcement before controllers are reached, plus controller checks for defence in depth.
- Append-only `permission_denied` authentication audit events with permission and route metadata only.
- Fail-closed handling for explicit, unrecognised access-level names.
- Clinical separation preventing administrators from recording doses, witnessing controlled drugs, correcting MAR or changing prescriptions.
- Navigation gating that hides unfinished Frontend 4 routes rather than sending users to 404 pages.
- Tests covering the role matrix, direct URL/post denials, denial audit and cross-service client access.

The authoritative matrix and route map are in `docs/care-one-os/FRONTEND4/ROLE-PERMISSION-MATRIX.md`.

Run locally:

```bash
php artisan test --filter=Frontend4PermissionTest
npm test
npm run build
```

## Requirement 4: organisation, service and location separation

Requirement 4 is implemented and confirmed. On 12 August 2026, `Frontend4AccessScopeTest` passed 8/8 and `Frontend4PermissionTest` passed 6/6 against the owner's isolated test database.

The confirmed hierarchy is organisation = `admin`, service = `home`, and location = `home_areas`. The implementation adds:

- a Frontend 4-only access context revalidated on every request;
- explicit service and location assignment tables with legacy compatibility;
- structured `service_user.home_area_id` location assignment;
- cross-organisation, cross-service, deleted-service and tampered-session refusal;
- Frontend 4-only client, round, MAR and prescription query scoping;
- audited service/location switching;
- organisation-scoped password reset and post-authentication service selection;
- context selectors in the isolated Frontend 4 header;
- `Frontend4AccessScopeTest` for the new boundary.

Read `docs/care-one-os/FRONTEND4/ACCESS-SCOPE.md` before applying the new migration. Because unrelated migrations may still be pending, apply `2026_08_11_000002_create_frontend4_access_scope_tables.php` by path in local and test databases first.

## Requirement 2b: step-based login

Requirement 2b is implemented and confirmed in the configured local environment. It changes only Frontend 4:

- Step 1 resolves an active, unique organisation into the server session.
- Step 2 verifies username and password without accepting a service ID.
- Step 3 displays services only after authentication.
- One active service is selected automatically; several services open a picker.
- No active service ends the partial session and records `no_active_service`.
- `frontend4.identity` permits only service selection and logout before a service is selected.
- Clinical routes still require `frontend4.auth` and redirect an authenticated user without a service to the picker.
- Invalid selections are rejected and recorded as `service_selection_denied`.
- The former unauthenticated `/frontend4/services` endpoint is removed.
- Legacy login, admin login, logout and API authentication routes remain unchanged.

No new database migration is required. Run:

```bash
php artisan test --filter=Frontend4AuthenticationIsolationTest
php artisan test --filter=Frontend4AccessScopeTest
php artisan test --filter=Frontend4PermissionTest
npm test
npm run build
```

Owner-confirmed result: Authentication 8/8, Access Scope 8/8 and Permissions 6/6; 22 tests and 88 assertions total, with no failures.

## Requirement 5: real client records and lifecycle rules

Requirement 5 is implemented on the integration branch and awaits database-backed PHP verification in the owner's configured Laragon environment. Read `CLIENT-LIFECYCLE.md` before applying its migration.

The implementation adds:

- manager/admin client creation and editing within the active service/location boundary;
- NHS number checksum and duplicate validation;
- explicit active, inactive, discharged, deceased and archived states;
- archive/restore instead of destructive deletion;
- append-only client events for creation, edits, lifecycle changes and transfer requests;
- reviewable transfer requests that deliberately do not change `service_user.home_id`;
- active-client filtering for live medication rounds while historical records stay viewable;
- nullable legacy placeholder fields so unknown measurements, units, usernames, passwords and narrative values are no longer fabricated;
- preferred name, pronouns, language, communication and record provenance fields.

Apply the pending profile migration and R5 migration by path to local and test databases, after checking the database target and taking a backup:

```bash
php artisan migrate --path=database/migrations/2026_08_06_120000_add_profile_fields_to_service_user.php
php artisan migrate --path=database/migrations/2026_08_12_000003_create_frontend4_client_lifecycle.php
php artisan test --filter=Frontend4ClientLifecycleTest
php artisan test --filter=Frontend4AuthenticationIsolationTest
php artisan test --filter=Frontend4AccessScopeTest
php artisan test --filter=Frontend4PermissionTest
npm test
npm run build
```

Do not approve/apply transfers by changing only `service_user.home_id`: the supplied database contains more than 90 client-linked table families. Transfer application needs its own later reconciliation workflow for MAR, prescriptions, stock, controlled drugs, documents and care records.
