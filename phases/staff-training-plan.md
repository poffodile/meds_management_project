# Staff Training — Build Plan

**Goal:** Staff Training is fully usable end-to-end — training modules can be created/edited/deleted, staff can be assigned and tracked through statuses, expiry tracking flags overdue training, all queries are home-scoped, all inputs validated server-side, and a service layer holds the business logic.

**Baseline commit:** `6631e8d2`

---

## Issues Found (Priority Order)

### BLOCKER — Security / Data Integrity
1. **No home_id filter on view()** — `TrainingController@view()` queries `staff_training` without home_id. Any user can view any training by ID.
2. **No home_id filter on status_update()** — updates any `staff_training` row without verifying ownership.
3. **No home_id filter on backend view()** — `StaffTrainingController@view()` does `Training::where('id', $training_id)->first()` with no home check.
4. **XSS in AJAX responses** — `completed_training()`, `active_training()`, `not_completed_training()` echo `$name` via string concat (unescaped). Must use `e()` or `htmlspecialchars()`.
5. **No server-side validation** — `add()` and `edit_fields()` accept any input. `add_user_training()` doesn't validate user_ids.

### HIGH — Bugs
6. **Wrong variable in not-completed check** — `training_view.blade.php:144` checks `$completed_training->isEmpty()` instead of `$not_completed_training->isEmpty()`.
7. **Duplicate assignments** — `add_user_training()` creates duplicate `staff_training` rows if same staff assigned twice.
8. **Uses `$_GET` directly** — `status_update()` uses `$_GET['status']` instead of `$request->input()`.
9. **Delete via GET** — `delete()` route is GET. Should be POST/DELETE for CSRF safety.
10. **Missing URL segment in active_training()** — Line 132: `$active->id.'completed'` missing `/` separator.

### IMPORTANT — Missing Features
11. **No expiry tracking** — DB has no `expiry_months`, `due_date`, `completed_date`, `expiry_date` fields. CareRoster tracks all of these.
12. **No `is_mandatory` flag** — CareRoster has `is_mandatory` and `category` (mandatory/policy) on training modules.
13. **No service layer** — all business logic in controllers.

### MINOR — Code Quality
14. **Models are bare** — no `$fillable`, no relationships, no SoftDeletes (uses manual `is_deleted` flag).
15. **Models in wrong location** — `app/Training.php` and `app/StaffTraining.php` instead of `app/Models/`.
16. **Commented-out code** — 15-line block at TrainingController lines 311-325.

---

## Files to Touch

### Modify
| File | Changes |
|------|---------|
| `app/Training.php` | Move to `app/Models/Training.php`, add fillable, relationships, SoftDeletes, scopes |
| `app/StaffTraining.php` | Move to `app/Models/StaffTraining.php`, add fillable, relationships |
| `app/Http/Controllers/frontEnd/StaffManagement/TrainingController.php` | Add validation, home_id checks, use service, fix XSS, fix bugs |
| `app/Http/Controllers/backEnd/generalAdmin/StaffTrainingController.php` | Add home_id filter on view(), use service |
| `resources/views/frontEnd/staffManagement/training_listing.blade.php` | Add is_mandatory badge, fix minor HTML |
| `resources/views/frontEnd/staffManagement/training_view.blade.php` | Fix not-completed bug, add expiry info, fix alert() calls |
| `routes/web.php` | Change delete to POST, update controller namespaces |

### Create
| File | Purpose |
|------|---------|
| `app/Services/Staff/TrainingService.php` | Service layer for all training business logic |
| `database/migrations/xxxx_add_training_fields.php` | Add expiry_months, is_mandatory, category to training table |
| `database/migrations/xxxx_add_staff_training_fields.php` | Add due_date, completed_date, expiry_date, completion_notes to staff_training |

---

## Implementation Steps

### Step 1: Database Migrations
- Add to `training`: `is_mandatory` (boolean, default 0), `category` (varchar, nullable — mandatory/recommended/optional), `expiry_months` (int, nullable)
- Add to `staff_training`: `due_date` (date, nullable), `started_date` (date, nullable), `completed_date` (date, nullable), `expiry_date` (date, nullable), `completion_notes` (text, nullable)
- Run migrations

### Step 2: Move & Upgrade Models
- Move `app/Training.php` → `app/Models/Training.php`
- Add: `$fillable`, `SoftDeletes` (add `deleted_at` migration or keep using `is_deleted`), `staffTrainings()` relationship, `scopeForHome()`, `scopeActive()`
- Move `app/StaffTraining.php` → `app/Models/StaffTraining.php`  
- Add: `$fillable`, `training()` and `user()` relationships, `scopeExpired()`, `scopeExpiringSoon()`
- Update all `use App\Training` → `use App\Models\Training` across controllers
- **Decision: Keep `is_deleted` flag** (not SoftDeletes) since existing data uses it and 23 rows already have `is_deleted=1`. Adding `deleted_at` would be confusing with two systems.

### Step 3: Create Service Layer
- `TrainingService.php` with methods:
  - `list($homeId, $year)` — get trainings grouped by month
  - `create($homeId, $data)` — validated create
  - `update($homeId, $trainingId, $data)` — validated update
  - `delete($homeId, $trainingId)` — soft delete
  - `getDetail($homeId, $trainingId)` — training with staff breakdown
  - `assignStaff($homeId, $trainingId, $userIds)` — assign with duplicate check + expiry calc
  - `updateStaffStatus($homeId, $staffTrainingId, $status)` — home-scoped status change
  - `getExpiringTrainings($homeId, $days = 30)` — trainings expiring within N days

### Step 4: Fix Frontend Controller
- Replace raw DB logic with service calls
- Add `$request->validate()` on all POST endpoints
- Fix `status_update()` to use `$request->input('status')` and validate
- Fix XSS in AJAX methods — use `e()` for names
- Add home_id filtering to `view()` and all AJAX methods
- Remove commented-out code block (lines 311-325)
- Fix `active_training()` missing `/` in URL generation

### Step 5: Fix Backend Admin Controller
- Add home_id filter to `view()` method
- Use service layer

### Step 6: Fix Views
- `training_view.blade.php:144` — change `$completed_training->isEmpty()` to `$not_completed_training->isEmpty()`
- Replace `alert("COMMON_ERROR")` with `console.error()` in AJAX error handlers
- Add `is_mandatory` badge on training listing
- Add expiry info display on training view

### Step 7: Fix Routes
- Change `Route::get('/staff/training/delete/{training_id}')` to `Route::post()`
- Update delete link in view to use a form with CSRF

### Step 8: Add Expiry Tracking Logic
- When assigning staff: if training has `expiry_months`, calculate `expiry_date = completed_date + expiry_months`
- On training view: show expiry badges (green=valid, amber=expiring within 30 days, red=expired)
- Service method `getExpiringTrainings()` for future dashboard use

---

## Verification Steps

1. Navigate to `/staff/trainings` — calendar view loads with existing trainings
2. Add a new training via modal — saves to DB with all fields including is_mandatory
3. Edit a training — pre-fills fields, saves changes
4. Delete a training — soft-deletes (is_deleted=1)
5. View training detail — shows staff breakdown (active/completed/not completed)
6. Assign staff — multi-select works, no duplicates, email sent
7. Update staff status — complete/activate/uncomplete all work
8. Try to view training from wrong home — blocked (403 or empty)
9. Submit form with empty fields — server-side validation errors shown
10. Check training with expiry_months — expiry_date calculated when staff completes
11. Backend admin views work with home_id filtering
