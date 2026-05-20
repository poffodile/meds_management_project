# Feature 4: Handover Notes — Implementation Plan

## Goal

Staff can view, create, edit, and search handover logbook entries scoped by home, and hand over entries to incoming shift staff who can acknowledge receipt — all with proper security, validation, and multi-tenancy.

## Starting State

| Component | Exists? | Issues |
|-----------|---------|--------|
| DB table `handover_log_book` | YES | Missing `is_deleted`, `acknowledged_at`, `acknowledged_by` columns |
| Model `app/HandoverLogBook.php` | YES | Bare — no `$fillable`, `$casts`, relationships, scopes |
| Controller `HandoverController.php` | YES | Raw `echo`/`die`, XSS (unescaped output), no validation, no IDOR check, broken `home_id` for multi-home admins |
| `LogBookController::log_handover_to_staff_user()` | YES | Uses `$request->all()`, no validation, should be in HandoverController |
| View `handover_logbook.blade.php` | YES | 258 lines, modals + search UI — needs verification |
| View `handover_to_staff.blade.php` | YES | 140 lines, staff assignment modal — needs verification |
| Routes (3 in web.php) | YES | No rate limiting, no route constraints |
| Service layer | NO | Must create |
| Acknowledgment flow | NO | Must add DB columns + endpoint |

## Files to Touch

### New files
1. `database/migrations/xxxx_add_handover_columns.php` — add `is_deleted`, `acknowledged_at`, `acknowledged_by`
2. `app/Models/HandoverLogBook.php` — proper model with fillable, casts, relationships, scopes
3. `app/Services/HandoverService.php` — all business logic
4. `tests/Feature/HandoverTest.php` — PHPUnit tests

### Modified files
5. `app/HandoverLogBook.php` — convert to alias extending `App\Models\HandoverLogBook`
6. `app/Http/Controllers/frontEnd/HandoverController.php` — full rewrite with service layer, validation, JSON responses, IDOR checks, acknowledgment endpoint
7. `routes/web.php` — update routes: rate limiting, route constraints, move `/handover/service/log` to HandoverController, add acknowledge route
8. `resources/views/frontEnd/common/handover_logbook.blade.php` — verify/fix XSS, CSRF, button wiring
9. `resources/views/frontEnd/serviceUserManagement/elements/handover_to_staff.blade.php` — verify/fix
10. `docs/logs.md` — action log

### Files to leave alone
- `app/Http/Controllers/frontEnd/ServiceUserManagement/LogBookController.php` — remove the `log_handover_to_staff_user()` method after moving it

## Step-by-Step Implementation

### Step 1: Migration
Create migration to add columns to `handover_log_book`:
- `is_deleted` TINYINT DEFAULT 0 (soft-delete flag, consistent with codebase)
- `acknowledged_at` DATETIME NULLABLE (when incoming staff acknowledged)
- `acknowledged_by` INT NULLABLE (FK to `user.id` — who acknowledged)

### Step 2: Model (`app/Models/HandoverLogBook.php`)
- `$table = 'handover_log_book'`
- `$fillable` — all user-settable fields (user_id, assigned_staff_user_id, service_user_id, log_book_id, home_id, title, details, date, notes, is_deleted, acknowledged_at, acknowledged_by)
- `$casts` — integer FKs, `date` → datetime, `acknowledged_at` → datetime
- Relationships: `creator()` → User, `assignedStaff()` → User, `acknowledgedBy()` → User, `serviceUser()` → ServiceUser
- Scopes: `scopeForHome()`, `scopeActive()`
- Convert `app/HandoverLogBook.php` to alias: `class HandoverLogBook extends \App\Models\HandoverLogBook {}`

### Step 3: Service (`app/Services/HandoverService.php`)
Methods:
- `list(int $homeId, ?string $search, ?string $searchType, ?string $date, int $perPage = 50)` — paginated list with search
- `getById(int $homeId, int $id): ?HandoverLogBook` — single record with IDOR check
- `update(int $homeId, int $id, array $data): bool` — update details/notes
- `createFromLogBook(int $homeId, array $data): array` — create handover from logbook entry (moved from LogBookController)
- `acknowledge(int $homeId, int $id, int $staffId): bool` — mark handover as acknowledged
- `softDelete(int $homeId, int $id): bool` — soft-delete a record

All methods:
- Accept `$homeId` as first param
- Use `->forHome($homeId)->active()` scopes
- Use `Log::info()` for audit trail
- Return typed results (array, bool, ?Model)

### Step 4: Controller Rewrite (`HandoverController.php`)
Methods:
- `index(Request $request)` — GET/POST: list handover entries with search/pagination. Returns HTML partial for AJAX, full page for GET.
- `update(Request $request)` — POST: update details/notes. Validate input. Return JSON `{success: true}`.
- `handoverToStaff(Request $request)` — POST: create handover record. Validate input. Return JSON `{success: true/false, message: '...'}`.
- `acknowledge(Request $request)` — POST: mark handover acknowledged. Validate. Return JSON.

Each method:
- Extracts `$homeId = explode(',', Auth::user()->home_id)[0]`
- Validates input with `$request->validate()`
- Calls service layer
- Returns proper JSON (not raw echo/die)
- Checks IDOR (record home_id matches user home)

### Step 5: Routes Update
```php
// Handover routes — all with rate limiting and auth
Route::match(['get', 'post'], '/handover/daily/log', [HandoverController::class, 'index']);
Route::post('/handover/daily/log/edit', [HandoverController::class, 'update'])
    ->middleware('throttle:30,1');
Route::post('/handover/service/log', [HandoverController::class, 'handoverToStaff'])
    ->middleware('throttle:30,1');
Route::post('/handover/acknowledge', [HandoverController::class, 'acknowledge'])
    ->middleware('throttle:30,1');
```

No parameterised routes (no `{id}` in URLs — IDs come via POST body), so no `->where()` needed.

### Step 6: View Verification & Fixes
- `handover_logbook.blade.php` — verify CSRF on forms, XSS safety, button handlers call correct endpoints
- `handover_to_staff.blade.php` — verify staff selection modal works, add acknowledgment UI (show acknowledged status on records)
- Ensure `esc()` helper is defined for any JS that renders API data into DOM

### Step 7: Remove old LogBookController method
- Delete `log_handover_to_staff_user()` from LogBookController (lines 847-907)
- Verify no other code calls it

## Security Checklist (Feature-Specific)

### Input validation rules
| Endpoint | Field | Rules |
|----------|-------|-------|
| POST /handover/daily/log/edit | handover_log_book_id | required, integer, exists:handover_log_book,id |
| POST /handover/daily/log/edit | detail | nullable, string, max:5000 |
| POST /handover/daily/log/edit | notes | nullable, string, max:5000 |
| POST /handover/service/log | log_id | required, integer, exists:log_book,id |
| POST /handover/service/log | staff_user_id | required, integer, exists:user,id |
| POST /handover/service/log | servc_use_id | required, integer |
| POST /handover/acknowledge | handover_log_book_id | required, integer, exists:handover_log_book,id |

### Rate limiting
- All POST routes: `throttle:30,1`

### XSS risks
- Controller currently echoes raw `$value->title`, `$value->details`, `$value->notes`, `$value->staff_name` without escaping — MUST fix
- View JS uses `.html()` to render AJAX response — response is raw HTML from controller, so the controller must escape all user data
- Any new JS that renders data must use `esc()` helper

### Access control
- All endpoints require authenticated user (checkUserAuth middleware)
- No admin-only actions in handover (all staff can create/view/edit)
- IDOR: every record access verifies `home_id` matches

### Middleware check
- Handover routes: `/handover/daily/log`, `/handover/daily/log/edit`, `/handover/service/log`, `/handover/acknowledge`
- After digit-stripping: same paths (no digits in URLs) — should work without whitelisting
- But must verify these routes are in the user's `access_rights` table or in `$allowed_path`

## Verification Steps
1. Login as komal/123456, house Aries
2. Navigate to handover logbook (via service user → logbook → handover)
3. Verify list loads with existing records, grouped by date
4. Edit a record's details/notes → verify save works
5. Hand over a logbook entry to a staff member → verify record created
6. Acknowledge a handover as incoming staff → verify acknowledged_at/by populated
7. Search by title → verify filtering works
8. Search by date → verify filtering works
9. Run PHPUnit tests — all pass
10. Check laravel.log — no new errors
