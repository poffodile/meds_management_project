# Body Maps — Implementation Plan

**Goal:** Body Maps feature is secure, functional, and follows all Care OS patterns — with proper model, service layer, multi-tenancy, validation, role-based access, audit trail, and injury detail capture.

**Estimate:** 3h | **Branch:** komal

---

## Current State

**What exists:**
- DB table `body_map` with columns: `id, service_user_id, staff_id, su_risk_id, sel_body_map_id, is_deleted, created_at, updated_at`
- Stub model `app/BodyMap.php` — empty (no fillable, no relationships)
- Frontend controller `app/Http/Controllers/frontEnd/ServiceUserManagement/BodyMapController.php` — 3 methods (`index`, `addInjury`, `removeInjury`)
- API controller (duplicate, same code)
- 2 views: `body_map.blade.php` (full page with SVG body diagram), `body_map_popup.blade.php` (modal version)
- 3 routes at web.php:1187-1189 inside `checkUserAuth` middleware
- 25 existing rows in `body_map` table
- Related table: `su_risk` has `home_id` column — body maps are linked via `su_risk_id`

**What's broken/missing:**
1. **No `home_id` on `body_map` table** — multi-tenancy relies on joining through `su_risk` (fragile)
2. **No model fillable/relationships** — stub model
3. **No validation** on `addInjury` or `removeInjury`
4. **No CSRF token** in AJAX calls (jQuery AJAX posts without `_token`)
5. **Raw `echo "1"; die;`** responses instead of proper JSON
6. **No role-based access** — any authenticated user can add/remove
7. **No audit trail** — no `created_by`, `updated_by` columns
8. **No injury details** — only stores which SVG region was clicked, not type/description/date
9. **`removeInjury`** doesn't filter by `home_id` or `staff_id` — any user can delete any injury
10. **No service layer** — business logic in controller
11. **`index()` filters by `staff_id`** — so each staff member only sees their own marks (should see all for that service user)
12. **No history/timeline** — no way to see how injuries changed over time

---

## Step-by-Step Implementation

### Step 1: Migration — add columns to `body_map`
- Add `home_id` (bigint unsigned, indexed) — backfill from `su_risk.home_id`
- Add `injury_type` (varchar nullable) — bruise, wound, rash, burn, swelling, pressure_sore, other
- Add `injury_description` (text nullable)
- Add `injury_date` (date nullable) — date discovered
- Add `injury_size` (varchar nullable)
- Add `injury_colour` (varchar nullable)
- Add `created_by` (bigint unsigned nullable)
- Add `updated_by` (bigint unsigned nullable)
- Add composite index on `(home_id, is_deleted)`
- Backfill `home_id` from `su_risk` join for existing 25 rows
- Backfill `created_by` from `staff_id` for existing rows

### Step 2: Model — `app/Models/BodyMap.php`
- Proper namespace, fillable, casts
- Relationships: `belongsTo` ServiceUserRisk, User (staff), User (creator)
- Scopes: `forHome($homeId)`, `active()` (where is_deleted = 0)
- Create alias at `app/BodyMap.php` extending `App\Models\BodyMap`

### Step 3: Service layer — `app/Services/BodyMapService.php`
- `list(int $homeId, int $serviceUserId)` — all active body map entries for a service user
- `listForRisk(int $homeId, int $suRiskId)` — entries for a specific risk assessment
- `addInjury(int $homeId, array $data)` — create with validation + audit
- `removeInjury(int $homeId, int $id)` — soft delete with audit
- `getHistory(int $homeId, int $serviceUserId)` — injury timeline grouped by date

### Step 4: Fix Controller
- Inject BodyMapService
- `index()`: Remove `staff_id` filter (show all injuries for service user), add `home_id` filter via `su_risk` join
- `addInjury()`: Add `$request->validate()`, proper JSON response, `home_id` check, audit columns
- `removeInjury()`: Add `home_id` filter, validate input, proper JSON response
- Add `viewInjury()` method — return injury detail as JSON for popup
- Add `updateInjury()` method — update injury details (type, description, date)
- Add role check: only type=A can remove injuries
- Remove all `echo "1"; die;` patterns

### Step 5: Routes
- Change `addInjury` from `Route::match` to `Route::post` (write operation)
- Change `removeInjury` from `Route::match` to `Route::post` (write operation)
- Add `POST /service/body-map/injury/update` route
- Add `GET /service/body-map/injury/{id}` route (get injury detail JSON)
- Add `GET /service/body-map/history/{service_user_id}` route (injury timeline)

### Step 6: Fix Views — AJAX CSRF + JSON responses
- Add CSRF token to all AJAX calls (`headers: {'X-CSRF-TOKEN': ...}`)
- Handle JSON responses instead of raw "1"
- Add injury detail modal — when clicking an active (red) body part, show a form to capture: injury type (dropdown), description, size, colour, date discovered
- Show injury count badge
- Remove `confirm()` dialogs, replace with modal confirmation

### Step 7: Add Body Map History View
- New route/method for timeline view
- Shows all injuries (active + resolved) with dates, who recorded them, injury details
- Accessible from service user profile

### Step 8: Tests
- Auth: unauthenticated → redirect
- Multi-tenancy: wrong home → empty/403
- Validation: missing fields → errors
- Happy path: add injury → 200 + DB row created
- Remove injury: soft delete works, home_id enforced
- Role check: non-admin can't delete

---

## Files to Create
1. `database/migrations/2026_04_11_XXXXXX_enhance_body_map_table.php`
2. `app/Models/BodyMap.php`
3. `app/Services/BodyMapService.php`
4. `tests/Feature/BodyMapTest.php`

## Files to Modify
1. `app/BodyMap.php` — convert to alias
2. `app/Http/Controllers/frontEnd/ServiceUserManagement/BodyMapController.php` — full rewrite
3. `resources/views/frontEnd/serviceUserManagement/elements/risk_change/body_map.blade.php` — CSRF, JSON, injury detail modal
4. `routes/web.php` — fix route methods, add new routes
5. `docs/logs.md` — log all actions

## Verification
- [ ] Body map page loads for a service user
- [ ] Click body part → injury detail form appears → saves to DB with home_id
- [ ] Click active body part → removal confirmation → soft deletes
- [ ] Wrong home_id → cannot see/modify injuries
- [ ] All AJAX calls include CSRF token
- [ ] All tests pass
