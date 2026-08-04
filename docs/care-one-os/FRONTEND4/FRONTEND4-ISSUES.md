# Frontend4 — issue log

**Things found that need doing, so they are not carried in someone's head.**

Every issue found while building frontend4 goes here with a number, a severity and
what actually has to happen. Nothing is closed until it is *done*, not until it is
worked around.

Related: [FRONTEND4-MILESTONES.md](FRONTEND4-MILESTONES.md) (the build plan) ·
[CARE-ONE-OS-MERGED-PLAN.md](CARE-ONE-OS-MERGED-PLAN.md) (the decisions) ·
[FRONTEND4.md](FRONTEND4.md) (the running log)

**Severity**
🔴 **Safety** — could contribute to a person getting the wrong medicine, or none.
🟠 **Integrity** — the record could be wrong, incomplete or unattributable.
🟡 **Correctness** — wrong behaviour, no direct clinical consequence.
⚪ **Housekeeping** — tidy-up, no behavioural risk.

| # | Issue | Sev | Status |
|---|---|:--:|---|
| [I16](#i16) | A stock transaction can record arithmetic that does not reconcile | 🔴 | ✅ **Closed** 2026-08-04 |
| [I17](#i17) | A stock shortfall is recorded but nobody is notified | 🟠 | **Open** |
| [I1](#i1) | Ten inline `code === 'A'` comparisons decide "was it given" | 🔴 | ✅ **Closed** 2026-08-04 |
| [I2](#i2) | Medication pages in frontends 1–3 have no permission rows | 🔴 | **Open** — closed for frontend4 only |
| [I3](#i3) | Allergies are free text, so they cannot be checked | 🔴 | Open |
| [I4](#i4) | No dm+d / SNOMED code on a medicine | 🟠 | Open |
| [I5](#i5) | `MAR Sheet Delete` permission exists in the old system | 🟠 | Open |
| [I6](#i6) | PRN duplicate protection is a 90-second time window, not an idempotency key | 🟠 | Open (pre-existing, documented in code) |
| [I7](#i7) | No general audit log of configuration and permission changes | 🟠 | Open |
| [I8](#i8) | Handover is stored as text blobs, so items cannot be assigned or ticked off | 🟠 | Open |
| [I9](#i9) | No missed-dose follow-up workflow | 🟠 | Open |
| [I10](#i10) | No competency gating on medication actions | 🟠 | Open |
| [I11](#i11) | "Staff" access level grants near-admin rights in the old system | 🟡 | Open |
| [I12](#i12) | Pre-existing medication test failures | 🟡 | Open |
| [I13](#i13) | Unmapped access-level names fall back to account type | 🟡 | Open by design |
| [I14](#i14) | Junk access levels in live data | ⚪ | Open |
| [I15](#i15) | Dated duplicate tables in the schema | ⚪ | Open |

---

## I16 — A stock transaction can record arithmetic that does not reconcile 🔴 {#i16}

**Found:** 2026-08-04, while verifying M1.8 against real data.

**What happened:** MAR sheet 199 (Amoxicillin 250mg/5ml oral suspension) had `stock_level = 3.000`. A dose of 5ml was recorded as given. The result:

| Field | Value |
|---|---|
| `balance_before` | 3.00 |
| `quantity` | 5.00 |
| `balance_after` | **0.00** |

**3 − 5 is not 0.** The deduction clamped at zero rather than refusing the administration or recording the true position.

**Why it matters:** the whole point of `balance_before` / `quantity` / `balance_after` is that a ledger reconciles — the specification's requirement is to *"prevent silent balance changes"* and keep an explainable balance from receipt to administration. A row where the three numbers contradict each other is not explainable. On a **controlled drug** this is worse than a wrong number: a CD register that does not balance is the exact condition the discrepancy workflow exists to detect, and here the system creates one itself.

There is already a guard that refuses to record when stock is **zero** (`BuildsMedicationRound` line 480). There is no guard for stock that is merely *insufficient*.

**Not caused by frontend4** — the deduction arithmetic was untouched by M1.8, which only replaced the comparison deciding *whether* to deduct.

### Fixed 2026-08-04

**The decision:** the dose is still recorded. Refusing to record a dose that was physically
given would be the worse error — the same principle the code already applies where a
prescription has no structured dose quantity. What must not happen is the *record* being
right while the *ledger* quietly lies.

**What changed** in `MedicationStockTransaction::apply()`:

1. Consuming movements now compute the **true** arithmetic — `$after = $before - $quantity`,
   negative permitted. `balance_before`, `quantity` and `balance_after` are signed
   `decimal(10,2)`, so the ledger can hold the truth.
2. `mar_sheets.stock_level` is `decimal(10,3) **unsigned**` and strict mode would reject a
   negative, so the held balance still floors at zero — but that flooring is now written
   down as a second, explicitly-labelled `correction` entry taking the balance from the
   negative figure to zero, naming the shortfall.

**The result, on the exact case that failed:**

| Entry | Quantity | Before | After |
|---|--:|--:|--:|
| administered | 5.00 | 3.00 | **−2.00** |
| correction — *"Stock count short by 2 — more was administered than the recorded balance held. Balance floored at zero; the count needs checking."* | 2.00 | −2.00 | **0.00** |

Every row now reconciles, the discrepancy is visible and explained, and the dose is still
recorded (`given = 1`).

**Verified:** the medication suites returned 14 errors / 3 failures before and after —
unchanged — with the three stock-suite tests passing.

**Owed:** a regression test for this case. The suite is red ([I12](#i12)) so it was verified
against live data instead, which is weaker.

---

## I17 — A stock shortfall is recorded but nobody is notified 🟠 {#i17}

**Raised by the fix to [I16](#i16).** The shortfall now appears in the ledger as a labelled
correction entry, which is a large improvement on a silently clamped number — but it is
passive. Nobody is told.

**Why it matters:** the specification requires a stock discrepancy to notify the shift lead
or manager, and to be linkable to an incident where necessary. A discrepancy that only
exists in a ledger nobody has opened is not much better than one that was hidden, and on a
controlled drug the requirement is stronger still.

**What has to happen:** surface shortfalls where they will be seen — the Today page's
attention list is the obvious first home — and route them to the responsible role. Properly
resolved as part of the Stock milestone (M8) with the discrepancy workflow.

---

## I1 — Ten inline `code === 'A'` comparisons decide "was it given" ✅ CLOSED {#i1}

**Closed 2026-08-04 (milestone M1.8).** All ten replaced with `App\Services\Medication\DoseOutcome::isGiven()`.

**Corrected on closing:** this was originally logged as **seven**. It was **ten** — the first search only covered one file. The three missed were the most important:
`MARSheetService.php:184` and `:237`, which write the **persisted `given` column** into the database, and `MarChartController.php:123`, which counts given doses on the MAR chart. A disagreement in the first two is not a display bug, it is a wrong clinical record.

**Verified behaviour-neutral:** the medication suites returned **14 errors / 3 failures both before and after** — identical. Then proved live: recording a dose as given set `given = 1`, superseded the previous entry (`is_current` 1 → 0) rather than overwriting it, and wrote a stock transaction.

**Original entry, kept for the record:**

**Where:** `app/Http/Controllers/frontEnd/Medication/Concerns/BuildsMedicationRound.php`
lines **151, 452, 467, 479, 533, 578, 593**.

**What:** each of those seven places decides whether the medicine actually went in by
comparing the outcome letter directly. They gate, in order: the PRN daily count; whether a
controlled drug needs a witness; blocking a controlled drug with no numeric dose; refusing
to record against zero stock; the PRN maximum and minimum-interval check; whether this is
an amendment of a dose already given; and the stock deduction.

**Why it matters:** those are seven independent definitions of "given" that happen to agree
today. Introduce any outcome where medicine *did* go in but the code is not `'A'`, and all
seven silently answer "no" — no witness, no stock movement, no PRN counting, and **no
error**. The dose bypasses every control quietly.

This is not hypothetical. `frontend/lib/medicationCodes.js` records the same failure
already happening: `'S'` (asleep) was once counted as given, so a sleeping child burned
their PRN daily allowance and restarted the interval clock, and was refused pain relief
when they woke.

**What was done:** one shared class, `App\Services\Medication\DoseOutcome`, now answers the
question, and all ten sites call it. The namespace is deliberately neutral rather than
`Frontend4` — this is shared logic all four front ends run, not frontend4's.

The refactor was **provably a no-op**: `DoseOutcome::GIVEN_CODES` contains only `'A'`, so
every call returns exactly what the inline comparison returned for every code that exists.
That is why it was safe to do while the test suite is still red (see [I12](#i12)) — there
was no behaviour to regress.

**Unblocks:** `PA` — *part administered* — which is now a one-line change in one place
rather than ten judgement calls. It still needs a quantity captured before it is enabled.

**The lesson worth keeping:** doing this while it changed nothing was cheap and verifiable.
Doing it later, at the moment it had to change something, would have been neither.

---

## I2 — Medication pages in frontends 1–3 have no permission rows 🔴 {#i2}

**What:** there are 814 route-based rows in `access_right`. The **old** roster MAR module is
covered (`MAR Administer` is a separate right from `MAR Sheet List`). The **new**
`/medication/*` React pages have **no rows at all**, and their controllers check only
`user_type` against a list that admits every type there is.

**Why it matters:** anyone who can log in can reach medication management in frontends 1, 2
and 3 — including finance staff.

**Current state:** **closed for frontend4 only.** `RoleResolver` + `Permissions` + the
checks in `F4Controller` now refuse anyone without medication access, and a finance account
is refused even when its account type says admin. The other three front ends are unchanged.

**What has to happen:** either add access-right rows for the `/medication/*` routes, or
apply the same role check in those controllers. Someone has to decide which, because it
affects three live front ends.

---

## I3 — Allergies are free text, so they cannot be checked 🔴 {#i3}

**What:** `service_user.allergies` is a comma-separated string;
`mar_sheets.allergies_warnings` is prose. There is no allergies table.

**Why it matters:** an allergy stored as a sentence can be **displayed** but not
**checked**. Nothing can warn that the medicine about to be given conflicts with a recorded
allergy. The specification asks for allergen, reaction, severity, recorded date, source and
who recorded it.

**What has to happen:** a structured allergies table, with the free text migrated and kept
for reference rather than discarded. Needed before the administration screen (M4) can do
this properly. Tracked as **D1**.

---

## I4 — No dm+d / SNOMED code on a medicine 🟠 {#i4}

**What:** `mar_sheets.medication_name` is free text. No coded identifier column, no
medicines catalogue.

**Why it matters:** two spellings are two different medicines. No interaction or allergy
checking, no medication reconciliation, and no GP Connect later. The Administration page in
the specification already lists dm+d synchronisation, so this is in the plan — it just
needs the column and the catalogue before anything can depend on it. Tracked as **D2**.

---

## I5 — `MAR Sheet Delete` permission exists in the old system 🟠 {#i5}

**What:** `access_right` contains a `MAR Sheet Delete` right on
`/roster/client/mar-sheet-delete`.

**Why it matters:** deleting a prescription record is destructive. Both specifications say
clinical records are corrected by addendum and never removed, and accounts holding
historical records are deactivated rather than deleted.

**What has to happen:** confirm what that route actually does — a soft delete via
`is_deleted` is very different from a real one. Do **not** carry the capability into
frontend4 either way.

---

## I6 — PRN duplicate protection is a time window, not an idempotency key 🟠 {#i6}

**What:** `PRN_DUPLICATE_WINDOW_SECONDS = 90`. Two identical PRN submissions inside 90
seconds are treated as one event.

**Why it matters:** it infers the event from the clock rather than identifying it. A slow
genuine re-dose inside the window is swallowed; a retry outside it is duplicated. The code
comment already says this plainly: *"This is a guard, not the right answer… do not mistake
this for a solved problem."*

**Pre-existing** — not introduced by frontend4. Recorded here so it is not lost.

**What has to happen:** a client-supplied idempotency key on the administration commit, as
the UX Specification's engineering guardrails require.

---

## I7 — No general audit log of configuration and permission changes 🟠 {#i7}

**What:** auditing is per table — the MAR supersedes chain, `shift_handovers.edit_log`,
stock transactions. Good for the clinical record; nothing answers "who changed this
prescription, this permission or this setting, and what was it before".

**Why it matters:** the Reports and Audit page specifies action, previous value, new value,
reason, staff, role, timestamp, device and linked record, not editable by ordinary users.
Tracked as **D4**.

---

## I8 — Handover is stored as text blobs 🟠 {#i8}

**What:** `shift_handovers` holds `general_notes`, `client_updates`, `medication_concerns`
and `priority_alerts` as text.

**Why it matters:** **you cannot assign or tick off an item that lives inside a text blob.**
The specification wants entries with person, category, priority, action required, assigned
staff, due time and status, and unresolved items that stay active after the handover.
Tracked as **D5**.

---

## I9 — No missed-dose follow-up workflow 🟠 {#i9}

**What:** `medication_dose_reviews` exists but is shaped for dose review, not for a
follow-up case with a lifecycle.

**Why it matters:** the Missed Doses page specifies re-offer and re-offer time, recorded
pharmacy and GP advice, escalation, assignment, follow-up status, manager review, close and
reopen, and a full history of all of it. Tracked as **D3**.

---

## I10 — No competency gating on medication actions 🟠 {#i10}

**What:** `staff_training` and `training` exist, but not competency assessment, witness
authorisation, restrictions or expiry.

**Why it matters:** the specification says **expired competency automatically restricts the
relevant medication actions**. That is behaviour, not just a table. Until it exists,
`witness_controlled_drug` is granted by role alone — with no check that the person is
actually authorised to witness. Tracked as **D6**.

---

## I11 — "Staff" access level grants near-admin rights in the old system 🟡 {#i11}

**What:** the access level named "Staff" at Station Road (home 1) carries roughly **330**
access rights — very nearly everything the System Admin level has.

**Why it matters:** the role names do not describe the actual permissions. Anyone reading
"Staff" and assuming it is restricted would be wrong. It does not affect frontend4, whose
permissions are defined fresh, but it means the old side of the app is more open than its
role names suggest.

**What has to happen:** someone should review what the "Staff" levels actually grant.

---

## I12 — Pre-existing medication test failures 🟡 {#i12}

**What:** `MedicationRoundSafetyTest`, `MedicationRoundReactTest`,
`ControlledDrugRegisterTest` and `MARSheetTest` together produce **14 errors and 2–3
failures**, including `MARSheetTest::test_full_prescription_lifecycle` returning 500 and
`test_duplicate_administration_updates_instead_of_creating` finding 0 records where 1 was
expected.

**Confirmed pre-existing** — the suites were run with the M1.7 shared changes stashed and
produced the same error count. Not introduced by frontend4.

**Why it matters:** a failing suite stops being a safety net. Nobody can tell a new
regression from the existing noise — which is exactly the situation a medication system
should not be in.

**What has to happen:** triage them. Some may be environment (the test database is a clone
that drifts); some are probably real.

---

## I13 — Unmapped access-level names fall back to account type 🟡 {#i13}

**What:** `RoleResolver` maps 40 known access-level names. A name it has not seen falls back
to `user_type` rather than denying.

**Why it matters:** the specification says deny by default. This deliberately does not,
because denying a real support worker whose home invented "Support Worker Level 2" last
week would stop medicines being given — a worse outcome than mild over-permission. Note
`N`, which is 281 of 414 accounts, lands on the least privileged role anyway.

**Open by design.** `RoleResolver::unmappedLevels()` lists every level currently falling
back, so they can be mapped properly.

**What has to happen:** surface `unmappedLevels()` on an administration screen so new names
get mapped rather than silently relied upon.

---

## I14 — Junk access levels in live data ⚪ {#i14}

`azure`, `acc`, `aa`, `ab`, `AccessTest`, `Initial Testing`, `Test Access Level`,
`Test HQ`, `Jesse Daniels Level`, `Vidhayak` are all real rows in `access_level` on live
data. All are mapped to **no access** in `RoleResolver`, so they are safe — but they should
be deleted or marked, not left looking like roles.

---

## I15 — Dated duplicate tables in the schema ⚪ {#i15}

`access_right@nov08old` and `24_oct_access_right` sit alongside `access_right` in a
249-table schema. Someone should confirm nothing reads them before the schema grows further.
