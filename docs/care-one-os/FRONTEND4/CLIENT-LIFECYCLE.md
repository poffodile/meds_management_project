# Frontend 4 client records and lifecycle

## Boundary

Frontend 4 uses the existing `service_user` row as the canonical client identity. It does not create a competing client table. Every query is limited by the authenticated organisation, active service and permitted location through `AccessContext`.

`manage_clients` is granted at manager level and inherited by administrators. Carers and leads can view client records in their assignment but cannot create, edit, archive, restore or request a transfer. Medication permissions remain separate.

## Status compatibility

`service_user.lifecycle_status` is authoritative for Frontend 4:

| Lifecycle | Legacy `status` | Legacy `is_deleted` | Live medication round |
|---|---:|---:|---|
| active | 1 | 0 | Included |
| inactive | 0 | 0 | Excluded |
| discharged | 0 | 0 | Excluded |
| deceased | 0 | 0 | Excluded |
| archived | 0 | 1 | Excluded |

Migration backfill is deliberately conservative: existing `is_deleted=1` rows become archived, other `status=1` rows become active and other rows become inactive. It does not guess that an old inactive record was discharged or deceased.

Archive is reversible. `lifecycle_status_before_archive` retains the status restored later. A reason and effective time are required for lifecycle changes. A correction from deceased and a readmission from discharged are recorded as distinct events.

## Append-only history

`frontend4_client_events` records the organisation, service, client, actor, event type, effective time, reason and structured change set. The model rejects update and delete operations. Profile events record actual changed values; creation records which fields were supplied without duplicating every value.

## Transfers

`frontend4_client_transfer_requests` records a pending review. Submitting a request does not change the client service.

This is intentional. The supplied database has medication, MAR, stock, controlled-drug, care-plan, incident and document records tied to both a client and a service/home. Updating only `service_user.home_id` would split the record across services. A later approval workflow must inventory and reconcile those records transactionally before applying a transfer.

## Unknown values and units

The legacy table required values for fields that can genuinely be unknown. The lifecycle migration makes those placeholder-only columns nullable without changing existing values. Frontend 4 creation therefore does not generate a client username/password, pick `kg` or `cm`, or store empty clinical narratives.

Weights remain in `service_user_weights` as dated, append-only readings in canonical grams. A display/input unit may be chosen by the user interface and converted; it must not be duplicated as an independent hardcoded fact beside the stored value.

## Migration safety

Inspect `migrate:status` first because other migrations may be pending. Apply the profile-field migration before the lifecycle migration. The latter adds two Frontend 4 tables, adds lifecycle/profile provenance columns and relaxes legacy placeholder columns to nullable. Existing populated values are not rewritten.

The rollback intentionally does not make legacy placeholder columns NOT NULL again. After a real client has a genuinely unknown value, restoring the old constraint would fail or require inventing data.
