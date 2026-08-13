# Requirement 8: manager assurance and controlled reporting

Requirement 8 adds factual manager assurance and controlled exports to Frontend 4. It does not declare Care One OS, a service or a staff member compliant, certified or clinically safe.

## Evidence contract

- Every measure is calculated from the active organisation, service and optional location context on the server.
- Reporting periods are inclusive and limited to 366 days.
- A numeric zero means no matching records were found. `Data unavailable` is a separate state and blocks manager sign-off.
- No overall assurance percentage, target or trend is generated without an approved governance rule and denominator.
- Current stock, task and incident figures are labelled as current snapshots. Administration, CD and prescription-event figures use the selected period.
- Drill-down exports retain source record IDs and attribution. Aggregated exports do not include client-level detail.

## Manager review

A manager can sign the current evidence snapshot with a review note and action summary. The stored row contains typed counts, the period, context, reviewer and review time. It cannot be updated or deleted through the model. A later review creates a new row.

Administrators can read reports and create authorised exports, but cannot sign a clinical manager assurance review. Support workers and shift leads do not receive report access through the R8 permission matrix.

## Exports and privacy

Exports require:

- the `export_report` server permission;
- a declared report type and bounded period;
- a reason of at least ten characters;
- an explicit authorisation confirmation; and
- a choice between summary-only and identifiable client detail.

Each successful export creates an append-only event containing the requester, organisation, service, optional location, period, report type, identifiable-detail choice, reason, format, row count and generation time. The CSV content is streamed and is not copied into the audit table. Responses use `Cache-Control: no-store, private`.

No automatic email delivery or scheduled distribution is included. Those features need separate recipient verification, retention, delivery-security and failure-handling requirements.

## New tables

- `frontend4_assurance_reviews`
- `frontend4_report_export_events`

## Apply and verify

Apply only the R8 migration to the configured development and isolated test databases:

```bash
php artisan migrate --path=database/migrations/2026_08_13_000006_create_frontend4_assurance_and_exports.php
php artisan test --filter=Frontend4AssuranceReportingTest
php artisan test --filter=Frontend4HandoverIncidentTest
php artisan test --filter=Frontend4PrescriptionLifecycleTest
php artisan test --filter=Frontend4ClientLifecycleTest
php artisan test --filter=Frontend4AuthenticationIsolationTest
php artisan test --filter=Frontend4AccessScopeTest
php artisan test --filter=Frontend4PermissionTest
npm test
npm run build
```

Before a production migration, confirm the database target, take and verify a restorable backup, inspect the migration SQL, test rollback in staging and obtain explicit production approval.

## Still outstanding

Import the complete approved NHS dm+d catalogue into `medicine_catalogue`. R6 delivered the structure and picker but deliberately did not fabricate or bundle a terminology release. NHS terminology synchronisation and other integrations remain later requirements.
