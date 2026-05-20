# Phase 1 — Patch & Polish: Complete What's Half Built

**Timeline:** Mar 26 – Apr 23, 2026
**Budget:** 68 hours
**Branch:** `komal`
**Goal:** Every half-built feature is usable end-to-end. No new modules — finish what exists.

---

## Pipeline Status

| #   | Feature             | Est | Pipeline Stage                                  | Status     |
| --- | ------------------- | --- | ----------------------------------------------- | ---------- |
| 1   | Incident Management | 3h  | PLAN → BUILD → TEST → REVIEW → AUDIT → **PUSH** | **DONE** ✓ |
| 2   | Staff Training      | 4h  | PLAN → BUILD → TEST → REVIEW → AUDIT → **PUSH** | **DONE** ✓ |
| 3   | Body Maps           | 3h  | PLAN → BUILD → TEST → REVIEW → AUDIT → **PUSH** | **DONE** ✓ |
| 4   | Handover Notes      | 4h  | PLAN → SCAFFOLD → BUILD → TEST → DEBUG → REVIEW → AUDIT → PROD-READY → **PUSH** | **DONE** ✓ |
| 5   | DoLS                | 4h  | PLAN → BUILD → TEST → DEBUG → REVIEW → AUDIT → **PUSH** | **DONE** ✓ |
| 6   | MAR Sheets          | 8h  | PLAN → BUILD → TEST → DEBUG → REVIEW → AUDIT → PROD-READY → **PUSH** | **DONE** ✓ |
| 7   | SOS Alerts          | 2h  | PLAN → BUILD → TEST → DEBUG → REVIEW → AUDIT → PROD-READY → **PUSH** | **DONE** ✓ |
| 8   | Notifications       | 5h  | PLAN → BUILD → TEST → DEBUG → REVIEW → AUDIT → PROD-READY → **PUSH** | **DONE** ✓ |
| 9   | Safeguarding        | 6h  | PLAN → SCAFFOLD → BUILD → TEST → DEBUG → REVIEW → AUDIT → PROD-READY → **PUSH** | **DONE** ✓ |
| 10  | Care Roster Wire-Up | 10h | —                                               | Pending    |

**Completed:** 9/10 features | **Commit:** `fab7dcfa`
**Feature 10 details:** [`docs/feature10-careroster-wireup.md`](../docs/feature10-careroster-wireup.md) — addendum covering ~60 unwired buttons in `client_details.blade.php` not already fixed by Features 1–9.

### Decisions & Scope Changes

- Incident Management: Skipped SCAFFOLD (files exist). AI report section removed from detail view (deferred to Phase 3). Safeguarding referral linking deferred to Feature 9 (Safeguarding).

---

## Current State Summary

Care OS has ~35% UI coverage and ~70% DB coverage for CareRoster features. Phase 1 targets 9 features that have backend pieces (tables, models, controllers) but lack usable frontends. The CareRoster Base44 React app is our **spec/reference only** — everything gets built in Laravel Blade.

**CareRoster source:** `/Users/vedangvaidya/Desktop/Omega Life/CareRoster/`
**CareRoster exports:** `/Users/vedangvaidya/Desktop/Omega Life/CareRoster/export/` (JSON + schema .md files)

---

## Feature 1: MAR Sheets (Medication Administration Records) — 8h

### What Exists

- **DB table:** `medication_logs`
- **Model:** `app/Models/medicationLog.php` — fillable: home_id, user_id, client_id, medication_name, dosage, frequesncy (typo), administrator_date, witnessed_by, notes, side_effect, status. SoftDeletes.
- **Service:** `app/Services/Staff/ClientManagementService.php` — store(), list(), report_details()
- **Controller (frontend):** `app/Http/Controllers/frontEnd/Roster/Client/ClientController.php` — medication_log_save() (line 306), medication_log_list() (line 330)
- **Controller (API):** `app/Http/Controllers/Api/medicalLogApiController.php` — index() with pagination
- **Routes:** POST `/client/medication-log-save`, POST `/client/medication-log-list`, POST API `/medical-logs`
- **Views:** NONE

### What's Missing (Build These)

- [ ] **MAR list view** — table of all medication records for a client, filterable by date/status
- [ ] **MAR create/edit form** — full form with all fields from CareRoster spec
- [ ] **MAR detail view** — single record with administration history
- [ ] **Admin MAR dashboard** — home-level view of all clients' medications
- [ ] Fix model typo: `frequesncy` → `frequency` (check if DB column also has typo)
- [ ] Add missing fields from CareRoster spec that don't exist in current model:
    - `prescribed_by`, `route`, `pharmacy`, `frequency`, `time_slots`, `as_required` (PRN), `start_date`, `end_date`, `stock_level`, `reorder_level`, `storage_requirements`, `allergies_warnings`, `discontinued`, `discontinued_date`, `discontinued_reason`, `last_audited`
    - `administration_records` — sub-records tracking each dose given (time, administered_by, witnessed_by, status: given/refused/withheld/not_available)
- [ ] Server-side validation on all form inputs

### CareRoster Reference

- **Export:** `CareRoster/export/MARSheet.json` (39 records), `MARSheet.md` (schema)
- **Pages:** Look at `CareRoster/src/pages/` for MAR-related pages
- **Key fields (32):** medication_name, dosage, dose, route, frequency, time_slots, as_required, prescribed_by, prescriber, pharmacy, start_date, end_date, stock_level, reorder_level, storage_requirements, allergies_warnings, discontinued, administration_records, etc.

---

## Feature 2: DoLS (Deprivation of Liberty Safeguards) — 4h

### What Exists

- **DB table:** `dols`
- **Model:** `app/Models/Dol.php` — comprehensive fillable (22 fields including dols_status, authorisation_type, dates, assessors, IMCA, appeal rights). SoftDeletes.
- **Service:** `app/Services/Client/ClientDolsService.php` — store(), list(), details(), delete()
- **Controller:** `app/Http/Controllers/frontEnd/Roster/Client/DolsController.php` — index(), save_dols()
- **Routes:** POST `/client/save-dols`, POST `/client/dols-list`
- **Views:** NONE

### What's Missing (Build These)

- [ ] **DoLS list view** — table showing all DoLS records per client, with status badges (screening_required, application_submitted, authorised, expired, not_applicable)
- [ ] **DoLS create/edit form** — full form matching the model fields
- [ ] **DoLS detail view** — single record with timeline of events (referral → assessment → authorisation → review)
- [ ] **DoLS expiry alerts** — flag records approaching `authorisation_end_date` or `review_date`
- [ ] Add missing fields from CareRoster spec not in current model:
    - `capacity_assessment_date`, `capacity_assessment_outcome`, `capacity_assessment_completed`
    - `conditions_attached` (array), `restrictions_in_place` (array)
    - `monitoring_requirements`, `imca_name`, `imca_contact`
    - `appeal_rights_explained_date`, `appeal_rights_explained_by`
    - `last_reviewed_date`, `last_reviewed_by`
    - `relevant_persons_representative`, `documents` (file attachments)
- [ ] Server-side validation on all form inputs

### CareRoster Reference

- **Export:** `CareRoster/export/DoLS.json` (5 records), `DoLS.md` (schema)
- **Key fields (37):** dols_status (screening_required/application_submitted/not_applicable/expired), authorisation_type (standard/urgent), capacity assessments, IMCA details, conditions, restrictions, monitoring

---

## Feature 3: Handover Notes — 4h

### What Exists

- **DB table:** `handover_log_book` (referenced in code)
- **Model:** NO dedicated model file (uses raw DB or imported class)
- **Controller:** `app/Http/Controllers/frontEnd/HandoverController.php` — index() with search/date filtering, handover_log_edit()
- **Routes:** POST/GET `/handover/daily/log`, POST/GET `/handover/daily/log/edit`, POST `/handover/service/log`
- **Views:** `resources/views/frontEnd/common/handover_logbook.blade.php`, `resources/views/frontEnd/serviceUserManagement/elements/handover_to_staff.blade.php`

### What's Missing (Build These)

- [ ] **Create model:** `app/Models/HandoverLogBook.php` with proper fillable, casts, relationships
- [ ] **Create service:** `app/Services/HandoverService.php` — move DB logic out of controller
- [ ] **Verify/fix list view** — ensure the existing blade view works, shows entries in date order, search works
- [ ] **Verify/fix create/edit form** — ensure staff can add new handover entries with: shift info, key events, client updates, follow-up actions, priority flags
- [ ] **Add staff-to-staff handover flow** — outgoing shift staff writes notes, incoming shift staff acknowledges
- [ ] Server-side validation on all form inputs

### CareRoster Reference

- No HandoverNotes export exists in CareRoster — this feature was not in Base44. Build from the existing Laravel implementation and care home best practices.

---

## Feature 4: Body Maps — 3h

### What Exists

- **DB table:** `body_maps` (referenced in code)
- **Model:** NO dedicated model file
- **Controller:** `app/Http/Controllers/frontEnd/ServiceUserManagement/BodyMapController.php` — index(), addInjury(), removeInjury()
- **API Controller:** `app/Http/Controllers/Api/frontEnd/ServiceUserManagement/BodyMapController.php`
- **Routes:** GET/POST `/service/body-map/{risk_id}`, `/service/body-map/injury/add`, `/service/body-map/injury/remove/{service_user_id}`
- **Views:** `resources/views/frontEnd/serviceUserManagement/elements/risk_change/body_map.blade.php`, `body_map_popup.blade.php`

### What's Missing (Build These)

- [ ] **Create model:** `app/Models/BodyMap.php` with fillable, relationships
- [ ] **Verify body map UI works** — visual body diagram with clickable injury points
- [ ] **Fix controller responses** — currently returns raw `"1"` instead of proper JSON/redirect
- [ ] **Add injury detail form** — when marking an injury point, capture: type (bruise/wound/rash/burn/swelling), size, colour, description, date discovered, discovered by, photo upload
- [ ] **Body map history** — versioned snapshots so you can see how injuries change over time
- [ ] Server-side validation on all form inputs

### CareRoster Reference

- No BodyMap export exists in CareRoster — this feature was not in Base44. Build from the existing Laravel implementation.

---

## Feature 5: Safeguarding Case UI — 6h

### What Exists

- **DB tables:** `safeguarding_types`, `staff_report_incidents_safeguardings` (junction)
- **Models:** `app/Models/Staff/SafeguardingType.php`, `app/Models/Staff/StaffReportIncidentsSafeguarding.php`
- **Controller (backend):** `app/Http/Controllers/backEnd/homeManage/SafeguardingTypeController.php` — type CRUD
- **Service:** Integrated into `StaffReportIncidentService`
- **Routes:** `prefix('safeguarding-type')` group, API POST `/safegaurding_list`
- **Views:** `resources/views/backEnd/homeManage/safeguarding_type.blade.php`

### What's Missing (Build These)

- [ ] **Safeguarding Referral model** — new model `SafeguardingReferral.php` for tracking individual safeguarding concerns (separate from incident types)
- [ ] **Migration** for `safeguarding_referrals` table with fields from CareRoster spec:
    - reference_number, client_id, home_id, reported_by, date_of_concern, status (reported/under_investigation/safeguarding_plan/closed)
    - details_of_concern, immediate_action_taken, risk_level (low/medium/high/critical)
    - safeguarding_type (array: physical_abuse, sexual_abuse, emotional_abuse, financial_abuse, neglect, self_neglect, discriminatory_abuse, organisational_abuse, domestic_abuse, modern_slavery)
    - alleged_perpetrator (name, relationship, details)
    - witnesses (name, role, statement)
    - capacity_to_make_decisions, client_wishes
    - police_notified, police_reference, police_notification_date
    - local_authority_notified, local_authority_reference, local_authority_notification_date
    - cqc_notified, cqc_notification_date
    - family_notified, family_notification_details
    - advocate_involved, advocate_details
    - safeguarding_plan, investigation details
    - outcome, outcome_details, lessons_learned, closed_date
    - ongoing_risk, strategy_meeting (required, date, outcome)
- [ ] **Safeguarding list view** — all referrals for a home, filterable by status/risk level/type
- [ ] **Safeguarding create/edit form** — multi-step form covering concern details → notifications → investigation → outcome
- [ ] **Safeguarding detail view** — full case view with timeline
- [ ] Server-side validation on all form inputs

### CareRoster Reference

- **Export:** `CareRoster/export/SafeguardingReferral.json` (5 records), `SafeguardingReferral.md` (schema)
- **Key fields (39):** Full case management with witnesses, alleged perpetrator, multi-agency notifications (police, LA, CQC, family), strategy meetings, safeguarding plans, outcomes

---

## Feature 6: Notifications Frontend — 5h

### What Exists

- **DB tables:** `workflow_notifications`, `purchase_order_approve_notifications`
- **Models:** `Workflow_notification`, `PurchaseOrderApproveNotification`
- **Controller:** `app/Http/Controllers/frontEnd/StickyNotificationController.php` — ack_master(), ack_individual()
- **API Controller:** `app/Http/Controllers/Api/frontEnd/StickyNotificationController.php`
- **Routes:** API POST `/notifications/list`, `/notifications/count`
- **Views:** `alert_messages.blade.php`, `popup_alert_messages.blade.php` (frontend + backend)

### What's Missing (Build These)

- [ ] **Generic notification model** — new `Notification.php` (or use Laravel's built-in notification system) with: title, message, type, priority, recipient_id, related_entity_type, related_entity_id, is_read
- [ ] **Notification centre view** — dropdown/panel showing all notifications for the logged-in user, with read/unread status, clickable to navigate to related entity
- [ ] **Notification bell icon** with unread count badge in the main layout header
- [ ] **Mark as read** — individual and mark-all-as-read
- [ ] **Notification preferences** — user can choose which notification types they want (stretch goal, do if time allows)
- [ ] Wire up existing events to create notifications: incident reported, DoLS expiring, training due, handover posted, safeguarding raised
- [ ] Server-side validation where applicable

### CareRoster Reference

- **Export:** `CareRoster/export/Notification.json` (69 records), `Notification.md` (schema)
- **Key fields (14):** title, message, type, priority, recipient_id, is_read, related_entity_type, related_entity_id

---

## Feature 7: Staff Training — 4h

### What Exists

- **DB tables:** `training`, `staff_training`
- **Models:** Referenced but no explicit model files found in `app/Models/`
- **Controllers (frontend):** `app/Http/Controllers/frontEnd/StaffManagement/TrainingController.php` — index(), add(), view(), completed_training(), not_completed_training(), active_training(), status_update(), add_user_training(), view_fields(), edit_fields(), delete()
- **Controllers (backend):** `app/Http/Controllers/backEnd/generalAdmin/StaffTrainingController.php` — index(), view()
- **API Controllers:** 3 separate API controllers exist
- **Routes:** 13+ web routes under `/staff/training/*` and `/general-admin/staff/training*`
- **Views:** `training_listing.blade.php`, `training_view.blade.php`, `staff_training.blade.php`, `staff_training_form.blade.php`, `education_training_form.blade.php`, `education_trainings.blade.php`

### What's Missing (Build These)

- [ ] **Verify model files exist** — if not, create `app/Models/Training.php` and `app/Models/StaffTraining.php`
- [ ] **Verify all views render correctly** — this feature has the most existing code, test each view
- [ ] **Fix create/edit forms** — ensure training module creation works end-to-end (title, description, category, duration, is_mandatory, expiry_months)
- [ ] **Fix staff assignment flow** — assign staff to training, track status (not_started → in_progress → completed)
- [ ] **Add expiry tracking** — flag training that's expired or expiring soon based on completion_date + expiry_months
- [ ] **Create service layer** if missing — move DB logic out of controllers
- [ ] Server-side validation on all form inputs

### CareRoster Reference

- **Export:** `CareRoster/export/TrainingModule.json` (2 records), `TrainingAssignment.json` (9 records)
- **TrainingModule fields (21):** title, description, category (mandatory/policy), type (in_person), duration_minutes, is_mandatory, expiry_months, required_for_roles, prerequisites, tags, material_url
- **TrainingAssignment fields (22):** training_module_id, staff_id, status (not_started/completed), assigned_date, due_date, started_date, completed_date, expiry_date, score, pass_status, certificate_url, completion_notes, reminder_sent

---

## Feature 8: SOS Alerts UI — 2h

### What Exists

- **DB table:** `sos_alerts`
- **Model:** `app/Models/staffManagement/sosAlert.php` — EXISTS but fillable is empty (stubbed)
- **Controller (API):** `app/Http/Controllers/Api/Staff/StaffManagementController.php` — sos_alert() method creates alert + notifies managers (event type 24)
- **Routes:** API POST `/staff/sos_alert`
- **Views:** NONE
- **Web routes:** NONE

### What's Missing (Build These)

- [ ] **Fix model** — add fillable fields: staff_id, home_id, location, latitude, longitude, message, status (active/acknowledged/resolved), acknowledged_by, acknowledged_at, resolved_by, resolved_at
- [ ] **SOS alert list view** — for managers/admins, showing active alerts with urgency styling (red/flashing)
- [ ] **SOS alert detail view** — who triggered, when, where, response timeline
- [ ] **SOS trigger button** — prominent UI element for staff (this may be mobile-app only, but add a web fallback)
- [ ] **Acknowledge/resolve flow** — manager can acknowledge alert and mark as resolved with notes
- [ ] **Web routes** for the above views
- [ ] Server-side validation on all form inputs

### CareRoster Reference

- No SOS export exists in CareRoster — build from the existing API logic and care home emergency protocols.

---

## Feature 9: Incident Management — 3h

### What Exists (Most Complete Feature)

- **DB tables:** `staff_report_incidents`, `incident_types`, `alert_types`, `staff_report_incidents_safeguardings`
- **Models:** `StaffReportIncidents.php` (20 fillable, relations to incidentType, clients, safeguarddetails), `IncidentType.php`, `AlertType.php`, `StaffReportIncidentsSafeguarding.php`
- **Service:** `StaffReportIncidentService.php` — store() with ref generation (INC-timestamp-seq), list() with comprehensive filtering, report_details()
- **Controllers:** 4 controllers (frontend roster, frontend service user, backend home manage, backend service user)
- **Routes:** 12+ web routes, 7 API routes
- **Views:** 7 blade files covering list, detail, form, AI prevention

### What's Missing (Build These)

- [ ] **Verify all views render** — test incident list, create form, detail view
- [ ] **Fix any broken flows** — create new incident → save → appears in list → view details
- [ ] **Add severity badges** — colour-coded (Low=green, Medium=amber, High=orange, Critical=red)
- [ ] **Add status workflow** — Reported → Under Investigation → Resolved → Closed with status change buttons
- [ ] **Link to safeguarding** — when `is_safeguarding=true`, prompt to create a SafeguardingReferral
- [ ] Server-side validation on all form inputs

### CareRoster Reference

- **Export:** `CareRoster/export/Incident.json` (24 records, 7 critical), `IncidentReport.json` (6 records)
- **Incident fields (54):** Very comprehensive — includes investigation objects, preventive measures, authority notifications, care plan updates
- **IncidentReport fields (15):** AI-generated reports with root_cause_analysis, impact_assessment, preventive_measures — defer AI features to Phase 3

---

## Testing & QA — 18h

### Manual E2E Testing (7h)

For each of the 9 features above:

- [ ] Create a new record → verify it saves to DB
- [ ] List records → verify all display correctly with pagination
- [ ] Edit a record → verify changes persist
- [ ] Delete/archive a record → verify soft delete works
- [ ] Check permissions — admin vs staff vs manager see appropriate data
- [ ] Check home_id scoping — users only see data from their assigned home(s)

### Cross-Browser Check (2h)

- [ ] Chrome (primary)
- [ ] Safari
- [ ] Edge
- [ ] Check all forms submit correctly, tables render, modals open/close

### Mobile / Responsive Check (2h)

- [ ] All list views readable on mobile width (375px)
- [ ] All forms usable on tablet width (768px)
- [ ] Navigation menu works on mobile
- [ ] No horizontal scroll on any page

### DB Integrity Checks (2h)

- [ ] All new writes include `home_id` (multi-tenancy)
- [ ] All new writes include `user_id` (audit trail)
- [ ] SoftDeletes working — deleted records have `deleted_at` set, not physically removed
- [ ] No orphaned records (foreign keys valid)
- [ ] No N+1 queries on list views (check Laravel Debugbar or query log)

### Permission Checks (2h)

- [ ] Admin can see all homes' data
- [ ] Home manager sees only their home's data
- [ ] Staff sees only data relevant to them
- [ ] Unauthenticated users redirected to login
- [ ] All new routes have auth middleware

### Regression Testing (3h)

- [ ] Existing features still work: login, staff list, client list, rota/scheduling, daily logs, tasks
- [ ] No new errors in `storage/logs/laravel.log`
- [ ] Dashboard loads without errors
- [ ] Navigation to all existing sections works

---

## Audit & Debug — 7h

### Laravel Log Review (1h)

- [ ] Clear `storage/logs/laravel.log`
- [ ] Use each feature, then check log for errors/warnings
- [ ] Fix any new exceptions

### Fix N+1 Queries (2h)

- [ ] Add `with()` eager loading on all new list views
- [ ] Check query count on each list page (aim for < 10 queries per page load)

### Validate All Form Inputs Server-Side (2h)

- [ ] Every form POST has a FormRequest or inline validation
- [ ] Required fields enforced
- [ ] Date fields validated as dates
- [ ] Enum fields validated against allowed values
- [ ] No raw user input used in queries (prevent SQL injection)
- [ ] XSS prevention — all user content displayed with `{{ }}` not `{!! !!}`

### File Upload Paths & Permissions (1h)

- [ ] Any file uploads go to `storage/app/public/` (not `public/` directly)
- [ ] Storage symlink exists: `php artisan storage:link`
- [ ] Uploaded files accessible via URL

### Remove Backup/Stub Files (1h)

- [ ] No `.bak`, `.old`, `Copy of` files in codebase
- [ ] No commented-out controller methods longer than 5 lines
- [ ] No empty/stub controllers or models left behind

---

## Buffer — 8h

Reserved for unexpected fixes, scope adjustments, and anything that takes longer than estimated.

---

## Process

For each feature, follow the `/workflow` pipeline:

1. **PLAN** — confirm scope, identify files to create/modify
2. **SCAFFOLD** — create models, migrations, form requests, routes
3. **BUILD** — implement controllers, services, blade views
4. **TEST** — manual E2E test the feature
5. **REVIEW** — code review for quality, security, consistency

After all 9 features are done: 6. **AUDIT** — full codebase audit (logs, N+1, validation, cleanup) 7. **PUSH** — commit and push to GitHub

---

## Feature Build Order (Recommended)

Start with the most complete features (quick wins) and work toward the ones needing the most new code:

1. **Incident Management** (3h) — 95% done, mostly verification
2. **Staff Training** (4h) — 80% done, has views, needs verification + service layer
3. **Body Maps** (3h) — 60% done, has views, needs model + fixes
4. **Handover Notes** (4h) — 60% done, has views, needs model + service
5. **DoLS** (4h) — 70% backend, needs all views
6. **MAR Sheets** (8h) — 70% backend, needs all views + schema expansion
7. **SOS Alerts** (2h) — 50% done, stubbed model, needs everything frontend
8. **Notifications** (5h) — 70% backend, needs generic system + UI
9. **Safeguarding** (6h) — needs new referral model + full UI

---

## Phase 1.5 — Enhancement Backlog

Items discovered during Phase 1 that go beyond "finish what's half-built" but should land before Phase 2. This list will grow as we work through the remaining features.

| # | Feature | Enhancement | Est | Source |
|---|---------|-------------|-----|--------|
| 1 | Body Maps | **Sub-region selection** — click part of a limb instead of the whole body part. Requires splitting SVG paths into smaller sub-paths, new path IDs, schema update for granularity level, update paint/load logic. | 4–6h | Session 12 |
| 2 | Body Maps | **Side views** — left/right profile views in addition to front/back. Requires new SVG artwork with matching path ID conventions, view-switcher UI, wiring to existing save/load flow. | 4–6h | Session 12 |

---

## Key Rules

- **Do not start new modules** — Phase 1 is finishing what exists
- **Do not build AI features** — defer to Phase 3
- **Do not build client/staff portals** — defer to Phase 2
- **Use existing patterns** — match the code style already in the codebase (controller structure, view layouts, route naming)
- **Every view must use the existing master layout** — `layouts.frontEnd.master` or `layouts.backEnd.master`
- **Log everything** — all actions go in `docs/logs.md` with teaching notes
- **Home-scoped** — all data must be filtered by `home_id` for multi-tenancy
- **SoftDeletes** — all models use soft deletes (already the convention)
