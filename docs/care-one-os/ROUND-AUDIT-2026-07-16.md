# Medication Round — Pre-Build Audit Register
**Date:** 2026-07-16 · **Gate:** owner required every function and object checked for correct behaviour and clashes *before* any build work begins.

Five independent audits ran in parallel: backend functions · frontend functions/objects · workflow logic · data layer · cross-cutting seams. This register collates them. Where a finding was reached independently by more than one audit, that is noted — independent convergence is the strongest evidence here.

**Claim boundary:** this is an engineering audit. It does **not** make Care One OS compliant, certified, clinically safe or NHS-approved. Items marked *human review* require a named qualified person (pharmacist / Clinical Safety Officer / registered manager / legal), not an agent and not the assistant.

**Method note.** "Smoke-tested" = executed against the live demo database inside a rolled-back transaction, then verified clean. "Code-traced" = read, not executed. "DB-introspected" = queried schema/data directly. Nothing below was assumed.

---

## The finding that reframes the work

The Medication Round is **18 page variants sharing one write path**. `buildRoundProps()` has 18 callers; `applyRecord()` has 18 callers. Every defect below in the write path is therefore present in all 18 pages *simultaneously* — including the production Frontend2 page and every experimental lab variant still routed and reachable.

**Consequence for the plan:** a new page built on top of this write path inherits every Critical listed here. The fixes belong *underneath* the page, not inside it. This is the main argument for extracting a shared service rather than adding a 19th caller.

---

## CRITICAL — all block the build

### CR-01 · PRN doses overwrite each other, and the PRN limits cannot fire
**Found by:** workflow · data · (independently corroborated by backend)
**Evidence:** `MARSheetService.php:149-164` · `MedicationRoundController.php:1131` · `RecordDoseModal.jsx:30` · `buildRoundProps():248-249`

A PRN sheet carries one *nominal* slot (e.g. `12:00`). Every real PRN administration is submitted against that fixed slot regardless of the actual clock time. `administer()` finds-or-updates on `(mar_sheet_id, date, time_slot)` — so **the second real PRN dose of the day overwrites the first**. The first dose's record is destroyed, not superseded (breaches REQ-MED-30, append-only).

The enforcement query then *excludes the current slot* (`:1131`), so its "other doses today" lookup returns empty by construction. **`prn_max_daily` and `prn_min_interval_hours` are structurally unenforceable through the real UI.**

**Why this was missed until now:** the seeded demo fixtures insert administrations directly with realistic clock times (08:10, 12:15, …), which don't equal the nominal slot — so the block *appears* to work. The fixtures proved the intended behaviour while the carer path does the opposite. **This was an error in the seeder authored by the assistant**, and it is exactly what the audit gate existed to catch.

**Resolution:** key administrations on a real `administered_at`, one row per dose event; count by `mar_sheet_id + date`; exclude the row being edited by its own `id`, not by `time_slot`.

### CR-02 · Recording "Given" can wipe a medicine's entire stock in one tap
**Found by:** frontend (smoke-tested)
**Evidence:** `MedicationRoundController.php:1174` · `MedicationRound.jsx:322` · `MedicationStockTransaction.php:74`

```php
$qty = (float) preg_replace('/[^0-9.]/', '', $request->input('dose_given') ?: $sheet->dose);
```
Deduction quantity is scraped from a free-text dose string. Every non-digit is stripped, so multiple numbers **concatenate**:

| Dose string | Parsed quantity | Live row |
|---|---|---|
| `1 Tablet` | 1 | (correct by luck) |
| `10 ml (500mg)` | **10500** | sheet 64, stock 60 |
| `7.5 ml (375mg)` | **7.5375** | sheet 68 |
| `10 ml (200mg)` | **10200** | sheet 73 |

Smoke-tested: one dose took stock 56 → **0**. `apply()` clamps at `max(0, …)`, so it empties silently — no error, and the logged quantity is clamped, hiding the scale of the miscount.

Three aggravating factors:
1. **It is the default path.** One-tap "Given" sends `dose_given: row.dose` — the carer types nothing.
2. **`X ml (Ymg)` is the standard format for liquid medicines** — i.e. children's doses.
3. **The assistant's paediatric seeding introduced these exact strings.** The prior adult-only data said "1 Tablet", which parses to 1 and conceals the bug. Making the data clinically realistic is what exposed it.

**Resolution:** deduction quantity must never derive from free text. Needs a validated numeric field (or per-dose consumption from the catalogue), and must refuse rather than clamp.

### CR-03 · Any resident with a risk flag white-screens the page, uncontained
**Found by:** frontend
**Evidence:** `MedicationRound.jsx:208` · `MedicationRoundController.php:284-286`

`{(resident.risk_flags ?? []).map((r, i) => <Badge>{r}</Badge>)}` — each `r` is `{label, level}`. React throws "Objects are not valid as a React child."

**There is no error boundary anywhere in `resources/js` or `frontend`** (repo-wide grep: zero matches for `componentDidCatch`/`ErrorBoundary`/`getDerivedStateFromError`). So this is not a contained failure — it blocks medication administration for that resident, for every carer. Resident 243 has both a penicillin allergy and active risks.

**Resolution:** render `r.label`, style by `r.level`. Separately: add an error boundary — the absence is its own Critical, since it converts any render bug into a total page loss during a clinical task.

### CR-04 · "Sleeping" is recorded as a given dose, bypassing every control
**Found by:** workflow · frontend · cross-cutting (three independent audits)
**Evidence:** `medicationCodes.js:9,26` · `MedicationRound.jsx:39` · `MARSheetService.php:156,171` · `MedicationRoundController.php:255,1120,1127,1171`

`S = 'Sleeping'` is counted as **given** in the progress ring, the activity feed ("PRN given"), the `given` column, and PRN accounting. But:

- **It consumes the PRN daily allowance and restarts the interval clock** — a child recorded asleep received *nothing*, then is blocked from pain relief when they wake.
- **For a controlled drug it bypasses everything**: the witness gate and stock deduction are both `code === 'A'` only. A CD can be logged as administered with **no witness, no reason, no stock movement**.
- **It is excluded from Missed Doses review** (`NOT_GIVEN_CODES = R,O,W,N`) — so it never surfaces for anyone to check.
- Meanwhile **"Resident asleep" is also offered as a *Refusal* reason** (`R`, not given). The same event, recorded two opposite ways.

"Given" is in fact defined **four times** across the codebase (UI ×2 copies, `given` column, stock deduction, Missed Doses). They agree numerically today and disagree semantically.

**Human review — pharmacist / medication lead:** is "asleep, dose deferred" a distinct clinical state needing its own code and mandatory reason, or should staff be routed to Refused? Not an engineering decision.

### CR-05 · CD witness ignores the care setting
**Found by:** workflow · backend (smoke-tested on Aries)
**Evidence:** `MedicationRoundController.php:1120` — no reference to `home.care_setting` anywhere in the file

REQ-MED-23 (owner, 2026-07-16): witness compulsory for registered care homes, **optional for supported living**. Smoke-tested against Aries (home 8, `adult_supported_living`): recording a CD "Given" with an empty witness **still throws**. The `care_setting` column exists and is populated; the code never reads it.

**Blocked by DA-01 below:** 12 of company 1's 14 active houses have `care_setting` NULL, and NULL has no defined rule. **Do not default NULL to lenient** — that would silently drop the witness requirement on a registered care home.

**Human review — owner + regulatory:** the `childrens_home` position is explicitly UNRESOLVED (STD-62). Do not assume either direction for Neptune.

### CR-06 · Managers silently see only their first house
**Found by:** backend (smoke-tested)
**Evidence:** `MedicationRoundController.php:49` · `Frontend2Controller.php:23` (duplicated)

`(int) explode(',', Auth::user()->home_id)[0]`. Smoke-tested: user 80 (`home_id="1,9,8,15"`) always resolves to home **1**; user 194 always resolves to home **8**. No switcher, no indication other houses exist. Breaches REQ-MED-106.

`checkUserAuth.php:44-49` already populates an active-home session value that this ignores.

### CR-07 · A failed write looks like success
**Found by:** frontend · backend (HAZ-08, re-confirmed)
**Evidence:** all 18 `record*` wrappers · `RecordDoseModal.jsx:54`

Every `record*` wrapper returns `redirect()->with($ok ? 'success' : 'error', …)` — a **302 for both success and failure**. Inertia resolves `onSuccess` regardless, which clears the form and closes the modal. And **no `FlashAlerts`/`useFlash` is mounted anywhere in Frontend2**, so even the error flash is invisible.

`record()` (`:401`) is the *only* variant returning a real machine-readable status.

**Note for the fix:** this cannot be fixed inside `applyRecord()` — the divergence lives in the wrapper pattern, duplicated **18 times**.

### CR-08 · No unique constraint and no row lock on administrations
**Found by:** data · backend (both DB-introspected)
**Evidence:** `SHOW INDEX` → `NON_UNIQUE=1` plain index · `MARSheetService.php:149-158` (no `lockForUpdate`)

Read-then-write TOCTOU. Two concurrent submits both read `null` → **two inserts**. `applyRecord:1153` computes `$wasGiven` from the same absent row, so **both deduct stock**: double administration recorded *and* double deduction.

**Sequencing:** the unique index conflicts with append-only (REQ-MED-30). Resolve via `is_current`/`supersedes_id` scoping, not by preserving the overwrite. Currently 5 rows, 0 duplicates — it will apply cleanly today.

### CR-09 · Weight is undated free text, in mixed units, already shown during administration
**Found by:** data (owner-requested; DB-introspected)
**Evidence:** `service_user.weight varchar(255)` + `weight_unit ENUM('kg','lbs')` · shipped at `MedicationRoundController.php:294` · rendered on **5 pages**

- **2 residents are recorded in `lbs`.** A child logged `76` lbs read as kg is a **2.2× dose error**.
- **No measurement date exists.** The only timestamp is the row's `updated_at`, bumped by any profile edit — and demonstrably not a weighing date: Neptune residents 243–247 all share `updated_at = 2026-06-18 15:38:51` (a bulk seed; five children "weighed" in the same second).
- Neptune is ages 7–17 holding paracetamol/ibuprofen/amoxicillin **suspensions** — the classic mg/kg medicines. The exposure is live.

Same defect class as `is_controlled` drifting from `cd_schedule`: a denormalised current value with no provenance. See DA-02 for the proposed design.

---

## IMPORTANT

| # | Finding | Evidence | Note |
|---|---|---|---|
| IM-01 | **"Withheld" accepted with no reason** — client and server agree with each other and both contradict REQ-MED-06. The one reason string naming withholding ("Withheld — clinical advice") sits in the *Omitted* list, unreachable when W is chosen. | `medicationCodes.js:20` · `:1097` — **smoke-tested twice**, no exception | Fix is one code in two lists |
| IM-02 | **8 UI outcomes collapse into 6 codes** — `missed`/`delayed`/`vomited` all become `O`; the true outcome survives only as free text. Unrecoverable for audit. | `AdministerModal.jsx:24-34` | Split A/B/C import *both* modals — one page writes one column through two vocabularies |
| IM-03 | **Tenant leak in `marReport()`** — `ServiceUser::find($clientId)` is unscoped by home. The med grid stays correctly empty, but another tenant's resident **name + DOB** is returned and rendered. | `:674` | UK GDPR / Caldicott |
| IM-04 | **`unit`/`form` are silently dropped on write** — the columns exist (migrated 2026-07-16) and are *read* at `:318`, but are absent from `MARSheet::$fillable` and from every validation list. Smoke-tested: stored `unit='ml'` came back **NULL**. | `MARSheet.php:11-42` · `MARSheetController.php:55-79,106-130` | **The assistant's own migration was incomplete** — it added columns without the write path |
| IM-05 | **"Pause" is a decorative no-op** — changes a word and its own icon; no submit path checks it. A carer who taps Pause believing the round is suspended can still record, and so can a colleague. | `MedicationRound.jsx:241,315,354-355` | Misleading control with a real safety implication |
| IM-06 | **Zero stock blocks nothing** — no pre-check; `apply()` clamps to 0. Recording "Given" against an empty cupboard succeeds silently. Smoke-tested: client 247, sheet 73, stock 0 → `ok=true`. | `:1170-1184` · `MedicationStockTransaction.php:74` | **Human review — registered manager:** block or warn? A hard block could itself omit a genuinely needed dose |
| IM-07 | **A refused medication consent is invisible** — `client_consents` holds a **Medication consent with status `Refused` on home 8**. The round never reads the table. | 17 rows, keyed `home_id`+`client_id` | Wire it in; don't duplicate it |
| IM-08 | **PRN block state computed, never shown** — `prn.blocked`/`block_reason`/`next_available` all dropped by the UI. The one-tap path has no `onError` at all, so a rejection is silent. | `:263-271` (computed) · zero reads in `MedicationRound.jsx` | Carer discovers the block only on submit, or not at all |
| IM-09 | **`Frontend2Controller` has no role gate** — no constructor, no middleware. Access depends solely on the generic `AccessRight` system, while identical data via `MedicationRoundController` is hard-gated by `ALLOWED_USER_TYPES`. | grep: no `__construct`/`middleware` | Two paths to the same clinical data, two different minimum roles |
| IM-10 | **"Preview as carer" is not honoured** — the page reads the *real* role directly, ignoring `viewAs`, so "Re-open round" stays visible during a carer preview. Not a security hole (server re-checks) — a broken feature. | `MedicationRound.jsx:237` vs `role.js` `useRole()` | Stock2 already does this correctly |
| IM-11 | **Stale-state / cross-row race in the modal** — one long-lived instance reused for every row. Submit row A, open row B before A resolves: A's `onSuccess` wipes **B's** in-progress notes and force-closes B. Cancel doesn't reset the form either. | `RecordDoseModal.jsx:26-38,54` | Key the modal by row, or reset on close |
| IM-12 | **Late vs overdue collapsed** — `doseBucket()` returns `due_now`/`upcoming`/`later`/`overdue`, but the UI only ever checks `overdue`. 5 minutes late and 6 hours late render identically. | `:367-395` vs `MedicationRound.jsx:89` | Backend already computes what the requirement asks for |
| IM-13 | **Round ends with no check for outstanding doses** — unconditional lock, no confirmation, then `applyRecord:1111` blocks recording. Doses are stranded. `roundDone`/`roundTotal` are in scope at the button. | all 8 `end*` methods, byte-identical | Any carer can end; a manager testing a lab variant locks the production round for real carers |
| IM-14 | **Local palette diverges from `tokens.js`** and is re-exported as `V1THEME` to four pages — so brand/dark-mode fixes to the token file will silently miss this whole page family. | `MedicationRound.jsx:19-23,34` | `ORANGE #E8842B` vs `brand.orange #F58321` |
| IM-15 | **`care_plan_risks` is PK-only** — the `whereIn(client_id)` at `:234` is a full table scan on every round load. `home.admin_id` — the tenant-scoping column — is also unindexed. | DB-introspected | Trivial at 13 rows; degrades linearly with tenants |

---

## DATA ARCHITECTURE

### DA-01 · `care_setting` is NULL on 12 of 14 active houses — blocks CR-05
Company 1 has **30** homes (14 active, 16 soft-deleted) — not the 16 previously reported. Only 101 and 8 are set. Since CD enforcement reads this column, a NULL house has **no defined rule**. **Owner decision, house by house.** No default is safe.

### DA-02 · Proposed `service_user_weights` (owner requirement REQ-MED-112)
Append-only dated series. **`weight_grams` integer, kg only, lb rejected at input** — the 2 existing `lbs` rows prove a unit *choice* becomes a unit *error*. Captures `measured_at` (weighed) separately from `recorded_at` (typed), plus `recorded_by`, `method`, `is_estimated`, `supersedes_id`. **Current weight is derived** as the latest non-voided row — never cached back to `service_user`.

- **Hard reject** outside 500g–500kg. **Soft warn** on >10% change from previous, or outside an age-plausible band (`date_of_birth` is now a real DATE).
- **Staleness derived at read** (`DATEDIFF`), never stored. Round shows "Weighed 3 days ago", never a naked number.
- **Resident-agnostic, not children-only** — per REQ-MED-109, *whether* a weight is prompted is the conditional part. Adults need it too (MUST/malnutrition, renal dosing), though usually not mg/kg.
- **Legacy:** backfill 61 values as `is_estimated=1, method='legacy_import'`, `measured_at` flagged unreliable. Leave `service_user.weight` in place (read by 9+ controllers); deprecate later; **do not dual-write**.
- **Human review — pharmacist/CSO:** staleness threshold per age band, and refuse-vs-warn. The mechanism supports either without a schema change; the policy is not an engineering call.

### DA-03 · All 19 stock transactions are orphaned
Every `medication_stock_transactions` row points at a `mar_sheet_id` that no longer exists (live sheets are 6,7,62–91). **There are no foreign keys on any of these tables.** The prescriptions were deleted and the ledger silently dangled; free-text `medication_name` is now the only surviving record of what those movements were.

**This is REQ-MED-100 failing in the live data today** — a medicine has no independent existence, so it dies with the prescription. Strongest available evidence for the catalogue.

### DA-04 · Medicine name drift is total, and clinically material
`mar_sheets` and `medication_stock_transactions` use **different vocabularies**:

| MAR sheet | Stock ledger | Difference |
|---|---|---|
| `Melatonin 2mg modified-release tablets` | `Melatonin 2mg Tablet` | **MR vs IR — different medicines** |
| `Amoxicillin 250mg/5ml oral suspension` | `Amoxicillin 250mg Capsule` | **different form** |
| `Salbutamol 100micrograms/dose inhaler` | `Salbutamol 100mcg Inhaler` | spelling |

`mar_sheets` alone holds both `Metformin` and `Metformin 500mg tablets`, both `Paracetamol` and `Paracetamol 500mg tablets`. `controlled_drug_register` is empty (0 rows) — structural clash only, no drift yet.

**Consequence for the catalogue backfill:** string-matching is **unsafe**. MR vs IR and suspension vs capsule are different products, not typos. Backfill needs a reviewed, **pharmacist-checked** mapping; the 19 orphans may be unmappable and should be left NULL and declared.

### DA-05 · Proposed `medicine_catalogue` — system reference data
**No `home_id`, no `admin_id`** — that absence *is* REQ-MED-103, enforced structurally. dm+d-coded (`dmd_code`, `dmd_concept_level` VTM/VMP/AMP/VMPP/AMPP), `replaced_by_id` for REQ-MED-51, `is_local` for un-coded fallback.

```sql
CHECK ((is_controlled=0 AND cd_schedule IS NULL) OR (is_controlled=1 AND cd_schedule IS NOT NULL))
```
This makes **"Warfarin as a Schedule 2 CD" structurally impossible** — REQ-MED-107's literal ask, enforced by the database rather than by discipline. `cd_schedule` as ENUM kills format drift before it starts.

Satisfies REQ-MED-100: a pharmacy tenant gets a medicine entity with zero residents and zero MAR sheets.

Current state is *correct by luck, not construction*: 29× `(is_controlled=0, NULL)` + 3× `(1,'2')`, no contradictions, Warfarin correctly not controlled.

### DA-06 · Two risk systems — refuted as a conflict, confirmed as a gap
`care_plan_risks` (13 rows) is **all medication-safety** ("Penicillin allergy – do NOT administer", "Pureed diet IDDSI L4 – do not give whole tablets"). `su_risk` (365 rows) is **youth safeguarding** ("Harm to Self" ×298, CSE, Absconding). Only 2 clients overlap; zero taxonomy overlap.

**The round reads the right table.** Do not merge. Separately: `risk`/`care_team_job_title` are reference data stored *per-home* (Doctor = ids 1, 13, 42; "Harm to Self" = ids 1 and 19) — the same anti-pattern the catalogue fixes. `su_risk.home_id` is `varchar` joining `risk.home_id` `int`; `su_risk.status` has an undocumented value `3`.

### DA-07 · Allergies live in two places
`service_user.allergies` (what the round reads, `:288`) and `care_plan_pharmacies.allergies` (unread, 1 row). Source of truth is ambiguous. Pick one; make the other a view.

### DA-08 · Dual stock-mutation paths
`MedicationStockTransaction::apply()` logs every movement with before/after balance and FEFO drawdown. `MARSheetService::updateStock()` sets quantity fields directly with **no transaction log and no batch interaction**. Two paths mutate `stock_level`; change one and the audit trail diverges from reality.

### DA-09 · Proposed conditional fields (REQ-MED-109) — typed columns, no EAV
Every field a plain indexable column; extraction stays a `SELECT` in every setting.

**Common:** `gp_practice_name`, `gp_practice_ods_code` (interop-ready), `gp_phone`, `self_administers` + assessment/review dates.
**Conditional — childrens_home:** `parental_responsibility_holder`/`_relationship`, `ofsted_reg40_notifiable`.
**Conditional — age <16:** `gillick_assessed_on`/`_competent`/`_by`.
**Conditional — age ≥16:** `mca_capacity_assessed_on`, `mca_has_capacity_meds`, `mca_best_interests_ref`, `covert_admin_*`.

**The overlap is deliberate:** a 16–17-year-old at Neptune can hold **both** Gillick and MCA columns non-NULL — which is precisely why the model must not fork. Samuel Cooper (177, age 17) and Susanna Craven (233, age 16) are live proof. **The schema stores both and decides neither** — correct posture while MCA-vs-Gillick precedence remains UNVERIFIED (STD-63, legal review).

Also flagged: `su_health_record` (445 rows) is a `formdata` **text-blob EAV store** with test junk titles ("Test", "a", "eeeee"). Per REQ-MED-110, do not put weight or conditional fields there.

---

## OPTIONAL

- **Dead code:** `isOutcome` (`:40`), `reload` (`:317` — cited by HAZ-11 as the page's refresh mechanism; it is never called, so the page has *less* capability than the hazard log claims), `isMobile` prop unused in `ResidentRow`, `RoleContext` provided but consumed nowhere.
- **`index()` legacy Blade** (`:71`) independently duplicates the grid logic and has drifted — no PRN, no allergies/risks, no `is_controlled`/`unit`/`form`.
- **`flagToHandover` and `temporaryAbsence` exist on one lab variant only** (`lab1-4-2`) — the production Frontend2 page has neither. `temporaryAbsence` calls `administer()` directly, bypassing `applyRecord`'s reason/CD/PRN checks (it hardcodes `code='O'`).
- **`.claude/worktrees/frontend2-search-visibility/`** holds a full stale copy of the controller and pages. It will match future greps and could be edited by mistake.
- **`artisan route:list` is broken** — `ReflectionException: Class "ManagerController" does not exist`, from a stale string-syntax route. Pre-existing and unrelated, but it blocks route tooling. Both audits had to boot the router directly to work around it.
- `mar_administrations.given` is derivable from `code`; `service_user.height_ft`'s comment says "height in feet **or cm**" — one column, two meanings.

---

## Route sequencing (preventive)

`/frontend2/medication-2/{page}` is registered at `routes/web.php:1662` and `medication2()` already maps `'round' => 'MedicationRound'`, rendering a **propless placeholder**. Laravel returns the **first registered matching route** — it does not prefer literal segments over a wildcard. An explicit `/frontend2/medication-2/round` added *after* line 1662 is **dead code**: the placeholder renders empty and silently.

Register **before** :1662, or constrain the wildcard (`->where('page', 'medications|missed-doses|controlled-drugs|stock')`). Verb collision refuted (catch-all is GET-only). Duplicate `->name()` is last-wins at runtime but **hard-fails `route:cache`** — a latent deploy break.

---

## Architecture recommendation — reverse the inheritance decision

The owner approved making `buildRoundProps()`/`applyRecord()` `protected` so `Medication2RoundController` could **extend** and inherit them. The one-word change is inert and safe (all 37 call sites internal, verified).

**The inheritance plan is not.** Those methods have 18 callers each, all of them owner-owned pages. Inheritance keeps the parent body live for all 18, so one edit reaches every page the owner has open. PRODUCT-CONTEXT.md:74 requires agents add *"new, clearly-named backend methods — never edit the owner's existing controller methods."*

**Recommendation:** extract the shared logic into a **trait or service the new controller composes**. Same reuse, same no-duplication argument that justified the original approval — but the blast radius stops at Medication 2. Needs owner approval to reverse.

**PHP semantics note (verified):** `getHomeId()`/`roundForTime()`/`doseBucket()` staying `private` is safe — private methods bind to the declaring class, so `$this->getHomeId()` from inside an inherited `buildRoundProps()` resolves correctly even when `$this` is a subclass. It only breaks if a *new subclass method* calls them directly.

---

## File-ownership collisions — need explicit owner approval

| File | Why the rebuild touches it |
|---|---|
| `MedicationRoundController.php` | CR-01/02/04/05/06/07 all live here. Already modified (private→protected — **announced and owner-approved**, contrary to one agent's reading of the bare git diff) |
| `Frontend2/MedicationRound.jsx` | **De-facto shared module** — Split A/B/C import `V1THEME`, `metrics`, `statusOf`, `isGiven`, `RoundTab` from it. Also carries CR-03 |
| `Frontend2/MedicationRoundSplit{,B,C}.jsx` | Break if the above's exports change |
| `Frontend2/AdministerModal.jsx` | Where IM-02's lossy outcome mapping lives |
| `frontend/lib/medicationCodes.js` | Shared read-mostly — *adding* is allowed; changing `REASON_REQUIRED_CODES` is not, without approval |
| `routes/web.php` | The new route must go **above :1662** — inside the owner's block |

`Medication2*` does not exist anywhere — the namespace is clean.

---

## Rollout order (data layer)

`medicine_catalogue → medicine_id FK (pharmacist-reviewed mapping) → administered_at → append-only → UNIQUE index → weights → conditional fields → wiring/indexes/backfill`

Every step additive, independently deployable, guarded with `Schema::hasTable`/`hasColumn`. Nothing removes a column a live read depends on.

---

## Open items for named humans

| Item | Who | Blocking |
|---|---|---|
| "Sleeping" semantics — own code + reason, or route to Refused? | Pharmacist / medication lead | CR-04 |
| "Withheld" — ever reason-free? | Pharmacist / medication lead | IM-01 |
| Zero stock — block or warn? (a block could itself omit a needed dose) | Registered manager | IM-06 |
| Stale weight — threshold per age band; refuse or warn? | Pharmacist / CSO | CR-09 / DA-02 |
| `childrens_home` CD witness position | Owner + regulatory (STD-62) | CR-05 |
| `care_setting` for 12 active houses | Owner | CR-05 |
| MCA vs Gillick precedence for 16–17s | Legal (STD-63) | DA-09 |
| `medicine_id` backfill mapping | Pharmacist | DA-04/05 |
| Reverse the inheritance decision → trait/service | Owner | architecture |
| Hazard severity/likelihood ratings | CSO | all — still `_CSO to rate_` |
