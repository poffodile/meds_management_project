# Frontend 4 medicine catalogue and prescription lifecycle

## Boundary

Requirement 6 separates medicine identity from a person-specific prescription without replacing the existing MAR sheet. `medicine_catalogue` is shared reference data and has no organisation, service or client column. `mar_sheets` remains the current prescription/MAR anchor so existing administrations, stock transactions and controlled-drug records do not become orphaned.

The NHS describes dm+d as the recognised NHS standard for uniquely identifying and communicating medicines and devices. All dm+d unique identifiers are SNOMED CT codes. Official access is through the NHS Terminology Server or TRUD raw XML releases:

- https://www.nhsbsa.nhs.uk/pharmacies-gp-practices-and-appliance-contractors/nhs-dictionary-medicines-and-devices-dmd
- https://digital.nhs.uk/services/terminology-and-classifications/dm-d

This implementation does not bundle a dm+d release, perform BNF/BNFc dose checking, or claim that a selected product or entered dose is clinically correct.

## Source of each fact

- Catalogue: coded identity, preferred name, product/form metadata, countable stock unit and controlled-drug classification.
- Prescription: dose, dose unit, route, frequency, times, PRN limits, indication, directions, prescriber, source and dates.
- System: an administration/stock quantity only when the structured units reconcile exactly.

The user cannot submit `is_controlled` or `cd_schedule` on a prescription. Those values are copied from the selected catalogue row for compatibility with the existing medication round and CD workflows.

## Compatibility

`mar_sheets.medicine_id` is nullable. Existing free-text prescriptions therefore remain readable and retain their existing stock, MAR and audit relationships. They are labelled as legacy/unmapped until a human confirms the correct catalogue product. No string or fuzzy matching is performed.

New Frontend 4 prescriptions require a current catalogue row. A discontinued or internally inconsistent catalogue row cannot be selected. A client cannot have two active/paused prescriptions for the same catalogue identity.

A new prescription can only be created while the client lifecycle is active. Historical prescriptions remain viewable after inactivation, discharge or death, but those states cannot receive a new prescription through Frontend 4.

## Lifecycle

- New prescription: version 1, active, append-only `created` event.
- Amendment: retains the same MAR sheet ID so stock and administrations remain attached; increments the version; stores before/after snapshots in an append-only `amended` event.
- Pause: active to paused.
- Resume: paused to active.
- Stop: active/paused to discontinued.
- Discontinued is terminal. Restarting treatment requires a new prescription rather than rewriting the stopped record.

`frontend4_prescription_events` rejects update and delete through its model. Existing `mar_sheet_changes` status history is also retained for backward compatibility.

## Quantity derivation

The service derives a countable quantity only in these exact cases:

1. Prescribed dose unit equals the catalogue countable unit.
2. Prescribed dose unit equals the structured strength unit and the catalogue provides a matching strength volume/countable unit.
3. Prescribed dose unit equals the strength unit for a discrete countable form such as a tablet or capsule.

If none applies, `dose_quantity` remains null. The system does not parse free text or guess a conversion. Existing safety logic will not deduct stock or create a numeric CD movement without a structured quantity.

## Loading dm+d

The schema is import-ready but intentionally empty after migration. Load an approved official source through a separately reviewed importer or terminology-server integration. Store the source version and update time. Do not paste or seed a handful of clinically realistic products merely to make the picker look populated.

## Migration and verification

Inspect the target database first and apply only:

```bash
php artisan migrate --path=database/migrations/2026_08_13_000004_create_frontend4_medicine_catalogue.php
php artisan test --filter=Frontend4PrescriptionLifecycleTest
php artisan test --filter=Frontend4ClientLifecycleTest
php artisan test --filter=Frontend4AuthenticationIsolationTest
php artisan test --filter=Frontend4AccessScopeTest
php artisan test --filter=Frontend4PermissionTest
```

The migration adds `medicine_catalogue`, `frontend4_prescription_events` and additive nullable/version fields on `mar_sheets`. It does not map existing medicine names or rewrite existing prescriptions.
