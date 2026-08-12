# Frontend 4 organisation, service and location scope

Last updated: 12 August 2026

## Canonical hierarchy

- Organisation: `admin`
- Service: `home`, owned by an organisation through `home.admin_id`
- Location: `home_areas`, owned by a service through `home_areas.home_id`
- Client: `service_user`, owned by a service through `service_user.home_id` and optionally assigned to a location through `service_user.home_area_id`

Free-text `room_number`, addresses and GPS/location-history tables are display or operational data. They are not access-control boundaries.

## Request boundary

`App\Services\Frontend4\AccessContext` is the single Frontend 4 source for organisation, service and location scope. `Frontend4Authenticate` recalculates and validates the context on every authenticated request.

The context rejects:

- a service outside the selected organisation;
- a service the user is not assigned to;
- a deleted service;
- a location outside the active service;
- a location outside the user's explicit location assignments;
- a stale or tampered session context.

Rejected scope attempts are recorded as `access_scope_denied` in the append-only Frontend 4 security audit.

## Compatibility and explicit assignments

`user.home_id` is a legacy comma-separated service list. It remains a compatibility source only when a user has no rows in `frontend4_user_service_access` for the selected organisation.

Once explicit service-access rows exist for that user and organisation, those rows are authoritative. Inactive rows grant no access.

Location access works similarly. No location-access rows means service-wide access. Once location rows exist, only active locations belonging to the active service are allowed. A client without a structured `home_area_id` is not visible to a location-restricted account.

## Migration

`2026_08_11_000002_create_frontend4_access_scope_tables.php` creates:

- `frontend4_user_service_access`
- `frontend4_user_location_access`
- nullable `service_user.home_area_id`
- nullable unique `admin.frontend4_slug` for organisations with duplicate display names
- indexes, uniqueness rules and foreign keys for the new relationships

The migration does not rewrite `user.home_id`, move clients automatically or modify legacy authentication. Existing clients remain service-wide until deliberately assigned to a location.

Because this repository may contain unrelated pending migrations, inspect status and apply this migration by path in local/test first:

```bash
php artisan migrate:status
php artisan migrate --path=database/migrations/2026_08_11_000002_create_frontend4_access_scope_tables.php
php artisan test --filter=Frontend4AccessScopeTest
php artisan test --filter=Frontend4PermissionTest
```

Apply the same migration to the isolated test database before running the feature test. Do not migrate production without a verified backup, migration preview, rollback test and explicit approval.

## Interface behaviour

The context bar shows the organisation and offers service switching when the account has more than one permitted service. It offers location filtering only for structured locations that currently have assigned clients. Every switch is checked on the server and audited.

The shared medication-round logic invokes a Frontend 4-only scope hook. Frontends 1, 2 and 3 continue using their existing behaviour.

Service discovery is post-authentication. The former public `/frontend4/services` lookup has been removed, so an unauthenticated visitor cannot enumerate a username's allocated services. The selected organisation is kept in the server session, credentials are verified next, and only then are the authenticated user's active services returned by the service-selection screen.
