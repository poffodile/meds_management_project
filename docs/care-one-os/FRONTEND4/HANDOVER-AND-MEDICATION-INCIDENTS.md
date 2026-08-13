# Requirement 7: handover and medication incidents

Requirement 7 adds a Frontend 4 handover and medication-safety lifecycle without replacing or rewriting the legacy `shift_handovers` records.

## Safety contract

- A handover draft is built from existing source records. Each generated item stores its source type and source ID; it does not copy authority away from the original record.
- Submitting freezes the handover. A submitted handover cannot be edited into a different clinical account.
- Acknowledgement means “I received and read this handover”. It never means that its follow-up work is complete.
- A follow-up task must have an accountable owner, due time and escalation time. Completion requires a note and creates an append-only event.
- A medication incident is a separate safety record. Reporting, investigation and closure are distinct states. Closure requires an outcome and recorded learning.
- All lifecycle transitions create rows in `frontend4_clinical_events`. Those rows cannot be updated or deleted through the model.
- Organisation, service, location and client boundaries are revalidated server-side for every read and write.

## Automatic draft sources

The configured shift window can aggregate:

- refused, withheld, unavailable, other and late dose records;
- administered PRN doses requiring an effectiveness review;
- controlled-drug entries already marked as discrepancies;
- active prescriptions at or below their reorder level;
- Frontend 4 prescription creation, amendment and lifecycle events.

The source record remains the clinical truth. The handover item is a structured pointer and summary for communication.

## New tables

- `frontend4_handovers`
- `frontend4_handover_items`
- `frontend4_handover_acknowledgements`
- `frontend4_follow_up_tasks`
- `frontend4_medication_incidents`
- `frontend4_clinical_events`

## Permissions

Support workers can read and prepare handovers, acknowledge receipt, complete tasks assigned to them and report medication incidents. Shift leads can manage handover work. Managers can investigate and close medication incidents. Administrators retain read access but are excluded from the clinical write permissions.

## Apply and verify

Apply only the R7 migration to the configured local and isolated test databases:

```bash
php artisan migrate --path=database/migrations/2026_08_13_000005_create_frontend4_handover_and_incidents.php
php artisan test --filter=Frontend4HandoverIncidentTest
php artisan test --filter=Frontend4PrescriptionLifecycleTest
php artisan test --filter=Frontend4ClientLifecycleTest
php artisan test --filter=Frontend4AuthenticationIsolationTest
php artisan test --filter=Frontend4AccessScopeTest
php artisan test --filter=Frontend4PermissionTest
npm test
npm run build
```

## Still outstanding

Import the complete approved NHS dm+d catalogue data into `medicine_catalogue`. Requirement 6 delivered the catalogue structure and picker but deliberately did not bundle or fabricate a dm+d release.
