# Medication Round — Proposed Fix Plan
**Date:** 2026-07-16 · Companion to `ROUND-AUDIT-2026-07-16.md` (the findings) — this is the *proposed response* to them.
**Status:** PROPOSAL, except where marked ✅ DONE below.

> ## 🚧 BUILD GATE — owner, 2026-07-16
>
> **Do not start building the Medication 2 Round page until both of these are done and reported to the owner.**
>
> 1. **Self-audit of the fixes.** The 2026-07-16 audit checked the *original* code exhaustively. It did **not** check the several hundred lines of new write-path logic written in response to it (the trait, `deductionQuantity()`, the PRN row-per-dose model, the `S` reclassification, 7 migrations). Only the author reviewed those, and the author already shipped one bug inside their own fix — the `wasGiven` guard, caught only because a test printed stock after every dose rather than merely asserting the block fired. *Running: adversarial correctness · data/migration · clinical-safety.*
> 2. **One more industry-standards check on every single thing** (owner's explicit words). A fresh pass by `healthcare-researcher` + `uk-compliance-reviewer` over the *whole* design — MAR code set, dm+d/catalogue model, units, CD rules across BOTH regimes (Ofsted **and** CQC), weight, consent — against **current** official sources with version + access dates. The register has already been wrong more than once (STD-07 over-cited; the wrong regulator entirely; Reg 40 and Reg 23 both misattributed), so this is not a formality.
>
> Nothing here becomes "done" while a Critical remains (`DEFINITION-OF-DONE.md`). 4 Criticals are open: CR-06 (home switcher), CR-07 (a failed write still looks like success), CR-08 (no unique index / not append-only), CR-09 (weight).

**Owner decisions taken 2026-07-16:** weight = **kg only, lb rejected** (1.1) · medicine units **derived from dose form, never typed** (1.2) · **`W`/`O` scrapped** in favour of the Part 2 code set, *subject to pharmacist sign-off* · **trait not inheritance** (Part 4) ✅ done · **NULL `care_setting` fails safe = enforce witness** ✅ done · **"Pause" deleted** ✅ done.

**✅ Stage 0 complete (2026-07-16)** — CR-03a/b, IM-03, IM-04, IM-05, IM-10, IM-15, O1. Verified: `php -l` clean, migration applied, `npm run build` clean, `unit`/`form` persistence re-tested (was FAIL, now PASS).
**✅ CR-05 complete** — `cdWitnessRequired()`, fail-safe. **✅ Part 4 complete** — trait extracted; `buildRoundProps()` output verified **byte-identical** (md5 `8106aac…`, 39,265 bytes) across the refactor.

**✅ CR-02 complete** — `mar_sheets.dose_quantity` + `mar_administrations.quantity_given`. The regex is gone; quantity is structured or the deduction does not happen. Backfill filled 27 and deliberately left 5 NULL (two-number strings + `1 spray each nostril`); those are set explicitly in the seeder via `DOSE_QUANTITY_OVERRIDES` with the clinical reading written down. Verified: `10 ml (500mg)` deducts **10**, not 10500; typing `99999999` into the dose box deducts the prescribed quantity, not 99999999.

**✅ Fractional stock complete** — all five `mar_sheets` quantity columns `int unsigned` → `decimal(10,3) unsigned`; casts `integer` → `float`; `apply()` no longer `(int) round()`s the balance; 13 validation rules `integer` → `numeric`. Verified: 4 × 7.5 ml takes 60 → **30 exactly** (was 32, i.e. 2 ml/day of phantom stock). Round props + stock/low-stock decisions verified unchanged. `down()` refuses to narrow back if any fractional value exists.

> ### ✅ RE-FIXED 2026-07-16 (after the retraction below)
>
> Both faults the self-audit found are now fixed, and this time the claim is backed by tests that live in the repo rather than by a one-off script.
>
> **1. PRN limits are reachable through the product.** `prn_max_daily`/`prn_min_interval_hours` added to `MARSheet::$fillable`, both `$casts`, and the validation + `only()` lists in `MARSheetController::save()`/`update()` (bounded `min:1|max:24` — a 0 daily maximum is a discontinuation, not a limit).
>
> **2. The write path is atomic.** `applyRecord()` now runs every check and the write inside **one transaction with the `mar_sheets` row locked** (`applyRecordLocked()`). The lock is on the prescription, not the administration: a row lock on a row that does not exist yet locks nothing, which is exactly why the earlier `lockForUpdate()` on the administration lookup was worthless — and worse, it sat on the scheduled branch while the PRN branch inserted blind. The misleading lock in `MARSheetService` is removed and replaced with a comment explaining why it must not come back.
>
> **3. PRN double-submission guard.** Two taps on a PRN dose produce byte-identical payloads — nothing in the request distinguishes a double-tap from a genuine second dose, only elapsed time. An identical submission inside `PRN_DUPLICATE_WINDOW_SECONDS` (90s) is treated as the same event: idempotent success. **This is a stopgap, not the answer** — the right fix is a client-supplied idempotency key so the server identifies the event instead of inferring it. Recorded as such in the code.
>
> **Verified — including under real concurrency.** `tests/Feature/MedicationRoundSafetyTest.php` 14/14. Plus a genuine two-process collision test (two OS processes, real commits, spin-barrier to force overlap) on a PRN prescription in the state a real one is in (no limits): **1 row, 1 deduction, 1 ledger entry**, where the same collision previously produced 2 rows and 12→8.
>
> **Still open:** amending a PRN dose is impossible (audit D2 — insert-only, no row target); `POST /client/mar-administer` still bypasses every gate (D4); the backfill's missing unit check (C3); CR-06, CR-07, CR-09.

> ### ❌ RETRACTED 2026-07-16 — the CR-01 claim below is FALSE (kept for the record; superseded above)
>
> Three self-audit agents falsified it. **The PRN fix only works on hand-seeded demo data.**
>
> `prn_max_daily` and `prn_min_interval_hours` are **not in `MARSheet::$fillable` and not in any validation or `$request->only()` list** — there is no field anywhere in the product that can set them. The only writers are the migration and `NeptuneHouseDemoSeeder`, both direct DB writes. **Every PRN prescription a real user creates ships with both NULL.**
>
> In that state — the real state — the interval check never fires, and because PRN was changed to insert-only *without a lock*, a double-tap now produces **two rows and two stock deductions**. Smoke-tested: 12 → **8** where it should be 10. Before this change, find-or-update overwrote (one row, one deduction).
>
> **These changes made real PRN prescriptions worse, not better.** The verification below passed because the interval check was accidentally acting as a double-submit guard — and it only exists on seeded rows.
>
> Also false: the seeder inserts administrations without `administered_at` (`NeptuneHouseDemoSeeder.php:528-540`). The live rows only have values because migration `180000` backfilled them *afterwards*. **Re-seed and the interval block stops working entirely.**
>
> Root cause, and it is the same one three times today: **verification ran against a database already in the desired state, not against what the product produces.**

**~~✅ CR-01 complete~~** ❌ RETRACTED — `mar_administrations.administered_at` (backfilled from `created_at`, **not** `updated_at`). A PRN dose now gets its **own row** instead of overwriting the day's first dose; scheduled doses still find-or-update per slot. The `time_slot != ...` exclusion is removed, so the daily max and interval finally see prior doses. Both PRN clocks moved off `updated_at`. `lockForUpdate()` added (partial CR-08).
Verified end-to-end through the real carer path: 4 doses accepted → 5th **blocked** on daily max; an immediate second dose **blocked** on interval; editing a note leaves `administered_at` **unchanged**; scheduled re-submit still amends without double-deducting.
**Found and fixed while testing:** the double-deduct guard also keyed on `time_slot`, so PRN doses 2..n looked like amendments of dose 1 and skipped stock deduction entirely — a child could receive four doses and stock move once. Now `as_required` ⇒ always a new event.

**Regression suite: 7/7 pass** (CD witness ×3 settings, reason-required ×2, round lock, junk-input deduction). Build clean.

**✅ CR-07 complete (2026-07-23)** — a failed write no longer looks like success. Root cause was `applyRecord()` returning `false` on a missing sheet, which all 16 wrappers turned into a `redirect()->with('error')` 302 that Inertia resolves as onSuccess. Now it **throws** a ValidationException (one change, covers every wrapper); the JSON `record()` endpoint catches it to keep its 404 contract. Frontend: the one-tap path gained `onError`/`onSuccess` notifications, the modal gained an `onError` for field-less errors, and shared Inertia `flash.error`/`flash.success` (previously shared but never rendered on this page) now surface. Regression test `test_a_missing_prescription_does_not_report_success`.

**✅ IM-08 complete (2026-07-23)** — the PRN block is visible before the tap, not just on rejection. `row.prn` (computed server-side, previously dropped) now renders on the med line: `given_today/max_daily`, `next due HH:MM`, and the block reason. When blocked, the "Given" button is disabled with the reason in its tooltip. This matters more now that the limits actually fire.

**✅ Duplicate pages consolidated (2026-07-23, owner instruction)** — NOT deleted. All Frontend-1 experimental round variants (react, lab, lab2, lab1-1…1-4-3, v4, v4.2) are folded into the Frontend-2 shell's single "Duplicates" collapsible menu alongside the existing F2 variants. They open in the old shell via plain anchors (they are not Inertia pages). They still display `S`/`W` as given — flagged in the nav comment; the write path beneath them is the corrected shared one, so recorded data is right, only their display is stale.

**✅ Append-only records complete (2026-07-23)** — the compliance review's #1 gap (CQC Reg 17 / Children's Homes Regs reg 23(2)(c)). `mar_administrations` gained `is_current`, `supersedes_id`, `superseded_at`, `amendment_reason`. `MARSheetService::administer()` no longer edits a scheduled dose in place: a correction writes a NEW row pointing back at the one it replaces and flips the old row's `is_current` to 0 with `superseded_at` — the only in-place update that ever touches a row. Nothing is deleted. An identical re-submit is a no-op (no history spam). A global scope on the model makes every Eloquent reader see only the current version by default; history is opt-in via `->withHistory()`. The two raw `DB::table` readers (WorkflowEngineService) are patched individually. Migration `down()` refuses to roll back if any superseded row exists. Verified: `test_amending_a_dose_preserves_the_original_as_a_superseded_version`, `test_an_unchanged_resubmit_does_not_spam_the_history`, plus an end-to-end check that the round page shows the correction (1 current row) while the original "Given" survives in history. 18/18 suite, build clean.
**Note:** CR-08's UNIQUE index is still deliberately NOT added — a blanket unique on (sheet,date,slot) would wrongly block a second PRN dose (same nominal slot, both legitimately current). The concurrent-write race is already serialised by the row lock in `applyRecord`; a PRN-aware DB constraint is a separate change.

**✅ IM-01 complete (2026-07-23)** — `W` (Withheld) now requires a reason, client (`REASON_REQUIRED_CODES`) and server. Withholding is a deliberate clinical decision; the record must say why (REQ-MED-06). Test: `test_a_withheld_dose_requires_a_reason`.

**✅ CR-06 complete (2026-07-23)** — the manager home switcher. New `ResolvesCurrentHome` trait is the single source of truth; both `getHomeId()` copies (medication trait + `Frontend2Controller`) delegate to it. A `POST /frontend2/switch-home` endpoint validates the target against the user's own list and stores it in the session; the shell header shows a switcher menu when the user has >1 home (single-home users see a plain label). **Key discovery:** `App\User` already had a `home_id` accessor returning `session('active_home_id')` unvalidated — so the switching *mechanism* existed but had no UI/endpoint to set it, and read the session without checking access. The real list is `real_home_id`, not `home_id` (the latter is the resolved single home). The resolver re-validates the session's chosen home against `real_home_id`, so a tampered session cannot cross tenants on the medication/frontend2 paths. Verified incl. `test_a_tampered_session_cannot_cross_tenants` and `test_switch_endpoint_rejects_a_forbidden_home`. **Noted, not fixed (owner-owned, wide blast radius):** the `App\User::home_id` accessor itself still returns an unvalidated session value to any *other* code path that reads it directly.

**✅ CR-09 complete (2026-07-23)** — weight is now a dated, append-only series (`service_user_weights`), stored as integer **grams, kg-only** (no unit column — a unit choice is how a unit error happens). The 2 residents recorded in lbs were converted on backfill (76 lb → 34.5 kg); 61 legacy values seeded as `is_estimated`. Current weight is **derived** as the latest non-voided reading via `ServiceUserWeight::currentFor()` (one query, no N+1), carrying the measurement age and a stale flag. The round shows "38 kg · weighed 35 days ago" and flags a stale weight in orange. **STALE_AFTER_DAYS = 90 is a PLACEHOLDER** — the real threshold (likely age-dependent) and whether a stale weight should block or warn a weight-based dose are flagged for a clinician (REQ-MED-112). Verified: `ServiceUserWeightTest` (latest-wins, stale flag, voided ignored, lb-conversion). `service_user.weight` kept for now (other screens read it); deprecate later.

**Still open:** CR-04 full code-set (9-code proposal) awaits the pharmacist; the round-overview step + "not due until HH:MM" early-record guard are designed (agents advised) and awaiting owner sign-off before build. **No Criticals remain open in the round write path.** CR-07 (silent write failure, 18 wrappers), CR-06 (home switcher), CR-08 (UNIQUE index — needs the append-only rework first), IM-06 (zero-stock block vs warn — registered manager). Items marked **[OWNER]** need the owner's decision; **[CLINICAL]** need a pharmacist / CSO / registered manager.

**Claim boundary:** engineering proposals. None of this makes Care One OS compliant, certified, clinically safe or NHS-approved.

---

## Part 1 — Units of measurement

### 1.1 Weight: **kg only. One standard. No choice.** [OWNER decision, engineering recommendation]

**Recommendation: store `weight_grams` as an integer. Reject lb/st at input. Do not store a unit discriminator at all.**

Rationale:
- UK clinical practice is kg. mg/kg dosing is kg. The BNFc is kg.
- **A unit *choice* becomes a unit *error*.** This is not hypothetical — 2 live residents are recorded in `lbs` today. A child logged `76` lb read as kg is a **2.2× overdose**.
- Offering both means *every read site* must check the unit. There are 5+ render sites already. One missed check = a dose error. Storing one unit makes the error **structurally impossible** rather than merely discouraged — the same principle as the `cd_schedule` CHECK constraint.
- Integer grams (not decimal kg) avoids float drift and gives 3 dp of kg free — needed for infants and precise paediatric scales.

**If a scale reads lb:** convert **at the UI**, store kg, record the original reading in `notes`. The conversion happens once, at the point of entry, by the system — never in a carer's head, and never stored as an ambiguous number.

**Display:** kg to 1–2 dp, always with the measurement age ("32.4 kg · weighed 3 days ago"). Never a naked number.

### 1.2 Medicine units: **derive from the dose form. Never type it. Never parse it.**

This is the owner's insight and it is the correct fix for CR-02. Expanded:

**The unit follows the form, and the form comes from dm+d:**

| Form | Countable unit | Stock held in |
|---|---|---|
| Tablet | tablet | tablets |
| Capsule | capsule | capsules |
| Oral suspension / solution | ml | ml |
| Inhaler | puff (actuation) | puffs (or devices) |
| Patch | patch | patches |
| Nasal spray | spray | sprays |
| Injection | ml | ml (or ampoules) |

**The critical subtlety — a dose has TWO units, and `"10 ml (500mg)"` is the symptom of them being jammed into one string:**

For `Paracetamol 250mg/5ml suspension`, dose 500 mg:
- The **prescriber** thinks in **500 mg** — the clinical dose.
- The **carer** measures **10 ml** — the physical volume.
- The **bottle** holds **100 ml** — the stock.
- Stock must deduct **10** (ml), not 500, and certainly not 10500.

These are all correct and all different. So store them separately and **compute the link**:

```
mar_sheets:
  dose_amount        DECIMAL(10,3)   -- 500
  dose_unit          VARCHAR(20)     -- 'mg'      (clinical dose, from catalogue vocab)
  -- volume is DERIVED, never typed:
  --   volume = dose_amount ÷ strength_amount × strength_volume
  --          = 500 ÷ 250 × 5 = 10 ml

medicine_catalogue:
  strength_amount    DECIMAL(10,3)   -- 250
  strength_unit      VARCHAR(20)     -- 'mg'
  strength_volume    DECIMAL(10,3)   -- 5
  strength_volume_unit VARCHAR(20)   -- 'ml'
  countable_unit     VARCHAR(20)     -- 'ml'      -- what stock is held in
```

For a tablet the two collapse: `Paracetamol 500mg tablet`, dose `2 tablets` → `dose_amount=2, dose_unit='tablet'`, stock deducts 2. No conversion needed.

**Why this is worth doing beyond fixing the stock bug:** "500 mg of a 250mg/5ml suspension = 10 ml" is arithmetic a carer currently does in their head, at 8am, under time pressure. Paediatric liquid-dose arithmetic is a well-known error source. Having the system compute the volume from the catalogue strength removes that calculation from the human — and lets the round *show* both ("Give 10 ml — 500 mg").

**One unit, end to end.** Received → administered → returned → disposed → counted, all in the medicine's countable unit, all numeric. Counting in and counting out then reconcile by construction, which is exactly the owner's point. Free text disappears from the quantity path entirely.

**Migration reality [CLINICAL]:** the existing `dose` strings must be re-entered against the catalogue, not parsed. `"10 ml (500mg)"` cannot be safely auto-split — see `ROUND-AUDIT-2026-07-16.md` DA-04, where `Melatonin MR` vs `Melatonin Tablet` proves string-matching is unsafe. **Pharmacist-reviewed mapping required.**

---

## Part 2A — Correction: "ask a pharmacist" is not a design (owner, 2026-07-16)

The owner pushed back on this document, and was right. Two separate errors were being made:

**1. Confusing a fact with a clinical judgement.** "If they were sleeping they were not given the meds" is not a matter of clinical opinion — it is what happened. Marking `S` as needing pharmacist sign-off was false deference: it dressed an obvious factual correction up as a decision requiring expertise, and meanwhile left a live defect in place that could lock a child out of pain relief. ✅ **Fixed 2026-07-16** — see below.

**2. Building a SaaS around a role its customers may not have.** Care One OS is sold to pharmacies, care homes, supported living, children's services and adult services. A pharmacy tenant *is* pharmacists. A supported-living house may have none on staff. So "a pharmacist decides X" cannot be a **build-time dependency for product behaviour** — it silently assumes one customer shape.

**The rule going forward:**

| Kind of question | How it is resolved |
|---|---|
| **Fact** — did the resident receive the medicine? | Just make the code true. No sign-off. |
| **Policy** — block or warn on zero stock? How stale is a stale weight? | **Tenant setting with a fail-safe default.** Ships working; each customer tightens it to their own governance. |
| **Clinical content** — actual paediatric dose values, dm+d mappings | Belongs to the customer's own prescriber/pharmacist **at the point of data entry**, not to a gate on our build. |
| **Claim** — "this is compliant/safe/approved" | Never made. Unchanged. |

This replaces the "human review required" gating below wherever it was blocking a factual fix or a configurable policy. It does **not** license inventing clinical content: the paediatric doses in the seeder stay marked as unverified demo values, because that is data a customer supplies, not behaviour we choose.

**✅ CR-04 complete (2026-07-16) — `S` is NOT given.** Server (`MARSheetService` `given` column, PRN counting, PRN enforcement), shared code list (new `GIVEN_CODES` / `isGivenCode()` — the single definition the four drifted copies now delegate to), `MedicationRound.jsx`, `MedicationRoundV2.jsx`, `MedicationDetail.jsx`, `MarReport.jsx` (no longer teal — a green-family marker on a printed chart reads as "taken"), and `MissedDosesController::NOT_GIVEN_CODES` (now includes `S`, so a sleeping resident's missed dose finally surfaces for review). Label is now "Asleep — not given". `S` requires no free-text reason: the code already states the reason. "Resident asleep" removed from `REFUSAL_REASONS` — asleep is not a refusal.
Verified: four "asleep" records consume **no** PRN allowance, move **no** stock, and the child can still receive pain relief on waking; a CD marked asleep records `given=false` with no stock movement, so it no longer claims a dose went in without a witness. **9/9 regression pass.**

## Part 2 — The MAR code set [proposal — see Part 2A for how it gets resolved]

### 2.1 The owner's instinct is right: "Withheld" and "Omitted" are confusing

They're confusing because **the current code set mixes three different questions into one list**:
- *What happened?* (given / not given)
- *Why?* (refused, asleep, no stock)
- *Who decided?* (the resident, a clinician, or nobody)

`Withheld` and `Omitted` overlap because they're answering different questions and nobody said which. That's why the codebase ended up with `"Withheld — clinical advice"` filed under **Omitted**, while **Withheld** itself requires no reason at all.

### 2.2 The real distinction worth keeping

There *is* a genuine clinical difference underneath the bad naming, and it's about **who decided**:

- **Refused** — the *resident* decided. → consent/capacity implications; escalate if repeated.
- **Withheld** — a *clinician* decided (BP too low, GP said hold, pre-blood-test). → someone with authority made a call. **The record must name who authorised it.** This is a clinical action.
- **Omitted** — *nobody* decided; it just didn't happen. → this is usually a **medication error**, not a routine outcome.

That last one is the problem. **"Omitted" is a catch-all that makes it easy to paper over an error with one tap.** A MAR should never be able to say "didn't happen" without explaining itself.

### 2.3 Proposed code set

Principle: **the code records what happened; the reason records why; and the catch-all is never the easy option.**

**Given:**
| Code | Meaning | Why distinct |
|---|---|---|
| `A` | Given | — |
| `SA` | Self-administered | Reg 23 — staff didn't administer; different accountability |
| `V` | Given, then vomited | **Was** administered but not retained → needs a re-dose decision. Currently collapses to `O`, which is factually wrong |

**Not given — all require a structured reason:**
| Code | Meaning | Why distinct |
|---|---|---|
| `R` | Refused by resident | Consent/capacity; escalate if repeated |
| `Z` | Asleep / not woken per care plan | **Normal and expected at a night round** — must NOT be treated as an error, and must NOT be treated as given |
| `B` | Absent (hospital / social leave / away) | Not an error; may need TTO reconciliation on return |
| `H` | Held on clinical advice | **Requires an authoriser** — who said so, and why |
| `U` | Unavailable (no stock / not supplied) | Supply failure → triggers reorder + escalation |
| `X` | Not given — other | Mandatory free text **+ raises a medication-incident flag** |

**Changes from today:**
- **`S` (Sleeping) is destroyed.** It becomes `Z`, and `Z` is **not given**. This single change closes CR-04 — no more PRN allowance consumed by a sleeping child, no more CD administered without a witness.
- **`W` (Withheld) → `H` (Held on clinical advice).** Renamed so the name forces the authoriser question, and made reason-mandatory. Closes IM-01.
- **`O` (Omitted) is destroyed.** Split into the things it was actually hiding: `B` (absent), `U` (no stock), `X` (unexplained → incident). No more one-tap paper-over.
- **`V` and `SA` added** — recovering the outcomes currently lost by `AdministerModal`'s 8→6 collapse (IM-02).
- **"Resident asleep" is removed from `REFUSAL_REASONS`.** Asleep is not a refusal.

**Objection — 9 codes is a lot of taps on a phone.** Real, and the mitigation is progressive disclosure, not fewer codes: "Given" is the big primary action; the 2–3 contextually likely outcomes surface next (Asleep is prominent on a night round, not a morning one); the rest sit behind "Other outcome". The code set should be as detailed as the clinical record needs; the *interface* is what should be simple. Collapsing real clinical distinctions to save a tap is how `O` became a dustbin in the first place.

**[CLINICAL] — must be signed off by a pharmacist / medication lead before implementation:**
- Is `Z` (asleep) genuinely distinct from `R` (refused), or should staff be routed to one of them?
- Does `V` (vomited) need a mandatory "re-dose decision" prompt, or is that the clinician's call off-system?
- Is `X` raising an incident flag proportionate, or will it drive under-reporting (staff pick a *wrong but quieter* code to avoid the flag)? **This is a real risk and argues for careful wording, not for removing the flag.**

**[UNVERIFIED — for `healthcare-researcher` + pharmacist]:** my understanding is that there is **no single legally mandated national MAR code set** for adult social care or children's homes in England — which is why providers and pharmacy suppliers all differ — and that the binding requirement is the *principle* (a complete, contemporaneous record; every non-administration explained) rather than specific letters. **I have not verified this and it must not be relied on.** If a mandated or strongly-expected set exists (e.g. via NICE SC1, CQC guidance, or a supplier standard), it wins over this proposal.

**Data migration:** this is dummy data — per the owner's standing instruction, fix it rather than preserve it. Forward-only semantics apply to real deployments (DCB0160 handover), not here.

---

## Part 3 — Fix plan, defect by defect

Ordered by dependency, not severity. **Nothing here is built.**

### Stage 0 — Stop the bleeding (small, isolated, no schema)

| # | Fix | Files | Note |
|---|---|---|---|
| CR-03a | Render `r.label`, style by `r.level` | `MedicationRound.jsx:208` | One line. Owner-owned file |
| CR-03b | **Add an error boundary** around the page shell | new component + `AppShell.jsx` | The absence is its own Critical — any render bug currently = total page loss mid-clinical-task |
| IM-04 | Add `'unit'`,`'form'` to `MARSheet::$fillable` + both validation lists | `MARSheet.php`, `MARSheetController.php:55-79,106-130` | **Completes the assistant's own incomplete migration** |
| IM-03 | Scope the resident lookup by home | `MedicationRoundController.php:674` | Tenant leak of name+DOB |
| IM-05 | **Remove "Pause"** (recommendation) or wire it to block every submit path | `MedicationRound.jsx:241,315,354-355` | A control that lies is worse than no control. Removing is honest; wiring is more work. **[OWNER]** |
| IM-10 | Use `useRole()` instead of reading the real role | `MedicationRound.jsx:237` | Stock2 already does this |
| IM-15 | Indexes: `care_plan_risks(client_id,status,deleted_at)`, `home(admin_id)`, `client_consents(client_id,consent_type)` | migration | `home.admin_id` — the tenant-scoping column — is unindexed |

### Stage 1 — The write path (the real work; underneath all 18 pages)

| # | Fix | Approach |
|---|---|---|
| CR-02 | **Structured dose quantity** | Per Part 1.2. Delete the regex at `:1174`. Deduction takes a validated numeric + catalogue unit. **Refuse** rather than clamp when qty > stock |
| CR-01 | **`administered_at`** | Add column, backfill from `created_at`, drive all PRN maths from it. One row per real dose event. Count by `mar_sheet_id + date`; exclude the edited row **by its own `id`** |
| CR-08 | **Lock + unique** | `lockForUpdate()` inside `administer()`'s existing transaction; UNIQUE index scoped by `is_current` so it doesn't fight append-only |
| CR-01b | **Append-only** | `supersedes_id`, `superseded_at`, `is_current`, `amendment_reason`. Stop `$admin->save()` overwriting. **This independently fixes the PRN `updated_at` clock** — no in-place update, no clock reset |
| CR-04 | **`S` → `Z`, not given** | Per Part 2. Remove `S` from `isGiven` (both copies), the `given` column, PRN accounting, and the CD/stock gates |
| CR-05 | **CD witness by setting** | Read `home.care_setting`. Enforce for `adult_care_home`. **Recommendation: NULL fails SAFE = enforce**, and show an "unconfigured house" banner. Rationale: the harm of a missing witness in a care home exceeds the friction of an unnecessary one in supported living. This unblocks the 12 NULL houses without forcing 12 decisions first. **[OWNER]** · `childrens_home` stays **[OWNER + regulatory]** |
| CR-07 | **Real failure status** | `applyRecord` returning `false` (sheet not found) must **throw**, not return a 302 with a flash nobody renders. Plus: mount `FlashAlerts` in Frontend2, add `onError` to the one-tap path. **Note: cannot be fixed inside `applyRecord` alone — the 302 pattern is duplicated across all 18 wrappers.** Argues for the shared wrapper below |
| CR-06 | **Home switcher** | Read `Session::get('active_home_id')` (already populated by `checkUserAuth.php:44-49`); add a switcher UI. Fix **both** copies (`MedicationRoundController.php:49`, `Frontend2Controller.php:23`) |
| IM-01 | Reason mandatory for every non-given code | Falls out of Part 2 automatically |
| IM-06 | Zero stock | **[CLINICAL — registered manager]** Recommendation: **warn + confirm, don't hard block**, and steer to `U`. A hard block on stale stock data could omit a genuinely needed dose — a worse hazard than the one it prevents |
| IM-09 | Role gate on `Frontend2Controller` | Same `ALLOWED_USER_TYPES` as `MedicationRoundController` — two paths to the same clinical data must not have two different minimum roles |

### Stage 2 — Data architecture (additive, staged)

Per `ROUND-AUDIT-2026-07-16.md` DA-02/05/09. Order:

`medicine_catalogue` → `medicine_id` FK (**pharmacist-reviewed mapping**) → `administered_at` → append-only → UNIQUE → `service_user_weights` → conditional fields → wiring/backfill

All guarded with `Schema::hasTable`/`hasColumn`. Nothing removes a column a live read depends on.

### Stage 3 — The page itself

| # | Fix |
|---|---|
| IM-08 | Surface `row.prn` — next-due time, blocked state, **before** the tap. Disable the button. Data already exists and is thrown away |
| CR-09 | Replace the naked weight with "32.4 kg · weighed 3 days ago" + stale flag |
| IM-07 | Read `client_consents` — surface the refused medication consent on home 8 |
| IM-12 | Thread `due_now`/`upcoming`/`later` through; add minutes-overdue. Backend already computes all of it |
| IM-13 | Count outstanding doses before "End round"; confirm when > 0. `roundDone`/`roundTotal` are already in scope at the button |
| IM-11 | Key the modal by row id (force remount) and reset on close |
| IM-14 | Migrate to `tokens.js` — but **this touches `V1THEME`, which Split A/B/C import**. Owner-owned. **[OWNER]** |
| — | Stock/`unit`/`low_stock`/`instruction` are computed and dropped — surface them |

---

## Part 4 — Architecture [OWNER]

**Reverse the inheritance decision.** The owner approved `protected` so `Medication2RoundController` could `extend`. The keyword change is inert and safe. The *plan* is not: 18 callers each, all owner-owned pages, parent body stays live for all of them.

**Recommend instead:** extract to a **trait or service the new controller composes**. Same reuse, same no-duplication argument that justified the approval — blast radius stops at Medication 2.

**Bonus:** it gives the 18 `record*` wrappers somewhere to converge, which is the only sane way to fix CR-07 once instead of 18 times.

**Route sequencing:** the new route must be registered **above** `routes/web.php:1662`, or the catch-all shadows it and the placeholder renders empty and silently. Owner-owned file. **[OWNER]**

---

## Part 5 — Open items for named humans

| Item | Who |
|---|---|
| The proposed code set (Part 2) | Pharmacist / medication lead |
| Is a national MAR code set mandated? (**UNVERIFIED** — do not rely on Part 2.3's assumption) | `healthcare-researcher` + pharmacist |
| `V` (vomited) — re-dose prompt on-system or off? | Pharmacist |
| `X` incident flag — proportionate, or drives under-reporting? | Registered manager + CSO |
| Zero stock — block or warn? | Registered manager |
| Stale weight — threshold per age band; refuse or warn? | Pharmacist / CSO |
| `childrens_home` CD witness position | Owner + regulatory (STD-62) |
| NULL `care_setting` fails safe = enforce? (recommendation) | Owner |
| `care_setting` for 12 active houses | Owner |
| MCA vs Gillick precedence for 16–17s | Legal (STD-63) |
| `medicine_id` + `dose` re-entry mapping | Pharmacist |
| Weight: kg-only, lb rejected? (recommendation) | Owner |
| Remove "Pause" vs wire it? (recommend remove) | Owner |
| Reverse inheritance → trait/service? (recommend yes) | Owner |
| Hazard severity/likelihood ratings | CSO |
