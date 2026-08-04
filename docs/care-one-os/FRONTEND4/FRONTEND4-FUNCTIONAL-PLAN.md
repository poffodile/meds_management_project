# Frontend4 — functional plan

**Goal: get frontend4 working end to end, so the only thing left to change is how it looks.**

Companion to [FRONTEND4-DESIGN.md](FRONTEND4-DESIGN.md) (what it looks like) and
[FRONTEND4-PLAN.md](FRONTEND4-PLAN.md) (the isolation rules).
Written 2026-08-04.

---

## 1. The finding that shapes this plan

**The medication engine already exists, and it is better than it looks.** Frontend4 does not need to build clinical logic. It needs to wire to logic that is already written, already used by three other front ends, and already handling the hard cases.

`BuildsMedicationRound::applyRecord()` — the shared method every round screen records through — already does all of this:

- Validates the outcome code against the allowed set (`A, S, R, W, N, O`).
- **Refuses to record a non-administration without a reason** (`R`, `W`, `N`, `O`). Withheld is included deliberately; asleep is excluded because the code already states the reason.
- Runs **everything in one transaction with the prescription row locked** (`lockForUpdate` on `mar_sheets`), which is what stops two carers — or one double-tap — both reading "not yet at the maximum" and both recording a dose.
- Enforces **PRN daily maxima and minimum intervals** inside that lock.
- Handles **controlled drugs**, stock deduction and the witness record.
- **Throws** rather than returning false on a missing prescription, because a `false` return became a 302 that Inertia read as success — the modal closed as though the dose had saved. That bug is fixed in the shared code; frontend4 inherits the fix for free.

Alongside it: `MARSheetService`, `MedicationRoundClosure` (round end/reopen), `CdWitnessConfirmation` (co-signature), `MedicationStockTransaction` / `MedicationStockBatch` / `MedicationStockOrder`, `MedicationDoseReview`, and working controllers for stock, missed doses, CD register, MAR chart and shift handover.

**So the honest size of this job is: thin controllers, a clear data contract, and the screens.** Not a medication system.

---

## 2. What "functionally working" means here

Frontend4 is functionally done when, for a real logged-in user against the real database:

1. Every Phase-1 screen loads real data for the user's own home, and only their own home.
2. Every action a carer can take actually writes — and the write is refused when it should be.
3. Permissions are enforced **server-side**, not by hiding buttons.
4. Every write is attributable: who, what, when, and why when a reason is required.
5. Nothing is destructive. Clinical records are corrected by addendum, never overwritten or deleted.
6. The six states are wired to real conditions, not just built as components.

Point 3 is worth stating twice. Hiding a button is a UI change. If the route still accepts the request, the feature is not finished — it is disguised.

---

## 3. The trap: what looks like UI but is actually functionality

"Build the function, style it later" works for most screens. It does **not** work for these, and treating them as UI polish is how a medication system ships something dangerous:

| Looks like UI | Is actually functionality |
|---|---|
| The order of steps on the administration screen | The safe sequence — identity, then medicine, then dose/route/time, then outcome, then confirm. A wrong-order screen lets someone confirm before they have checked who they are standing in front of. |
| "Reason" appearing after you pick an outcome | The reason must be captured **before** the confirm, and the server must reject without it. Both halves, or neither. |
| The allergy strip | It is a control, not decoration. If it can scroll off during administration, it is not doing its job. |
| The witness field on a controlled drug | A second person's account, not a text box. A typed name is not a co-signature. |
| The offline banner | The queue behind it. A banner with no queue is a lie about what happened to the dose someone just recorded. |
| Disabled buttons | Server-side refusal. The disabled state is the courtesy; the check is the feature. |

Everything else — spacing, colour, typography, card layout, iconography, the whole look — can safely wait for the UI pass.

---

## 4. What exists vs what frontend4 needs

| Capability | Where the logic lives | What frontend4 still needs |
|---|---|---|
| Dose status derivation (overdue / due / upcoming / complete) | `BuildsMedicationRound::buildRoundProps()` | **Nothing** — already used by frontend4's Today |
| Recording an outcome | `BuildsMedicationRound::applyRecord()` | A `record` action on an F4 controller that calls it and returns the Inertia redirect |
| Mandatory reason | Inside `applyRecord()` | Client-side mirror so the carer is told before submitting, not after |
| Double-tap / race protection | Row lock inside `applyRecord()` | Nothing server-side; the UI must not assume optimistic success |
| PRN maxima and intervals | Inside `applyRecord()` | Surface the refusal message properly instead of a generic error |
| Ending / reopening a round | `MedicationRoundClosure` + frontend3's `end()` / `reopen()` as the pattern | Own thin actions |
| CD witness co-signature | `CdWitnessConfirmation` + `WitnessConfirmationController` | Own screen + confirm/override actions |
| Missed doses / exceptions | `MissedDosesController` | Read the same data, present it frontend4's way |
| Stock | `MedicationStockController`, stock models | Phase 3 — read-only view first |
| Home scoping | `getHomeId()` in the trait | Use it everywhere; never accept a home id from the request |
| Access control | `user_type` + per-home `access_levels` | Same middleware check on every F4 controller |

---

## 5. Build order — functional slices

Each slice is "wire it up and prove it writes", with plain markup. The UI pass comes after.

**Slice 0 — done.** Today dashboard, read-only, real data. Verified rendering `Neptune House`, evening round, 1 overdue, 2 of 17 doses recorded, with a live out-of-stock supply problem and a person carrying allergy and swallowing-difficulty flags.

**Slice 1 — Round list.** Read-only. Who is due, who is overdue, who is done, for the current round. Reuses `buildRoundProps`. No writes, so it is quick, and it is the spine every other screen hangs off.

**Slice 2 — Administration workspace.** *The one that matters.*
- Renders one person at a time: identity, allergies, then their due medicines.
- Records an outcome via `applyRecord()`.
- Enforces the safe sequence and the mandatory reason on both sides.
- Handles the refusal cases properly: PRN maximum reached, minimum interval not elapsed, already recorded, prescription not found — each with the server's own message, not "something went wrong".
- Controlled drugs route into the witness flow rather than recording straight away.

**Slice 3 — End and reopen a round.** Closure with attribution. Reopening is a deliberate, recorded act, not an undo.

**Slice 4 — CD witness.** The named witness signs in on their own account and confirms. Override is possible but recorded as an override.

**Slice 5 — Exceptions / missed doses.** Read the same data as `MissedDosesController`; the follow-up decision is recorded, not just displayed.

**Then** the UI pass across all six, in one go, with the design doc in hand — which is the point of doing it this way round.

---

## 6. The data contract

One shape, so the UI pass never has to change a controller:

- Controllers return **presentation-ready** props. No dates to format, no codes to translate, no counts to compute in the component. The `terms` object travels with every page.
- Actions are Inertia `post`s that redirect back. Validation failures come back in the error bag and render against the field.
- Nothing in `F4Pages/` calls the database or derives clinical state. If a component needs to know whether a dose is overdue, the controller says so.

That contract is what makes "only the UI is left" true rather than aspirational.

---

## 7. Open — needs your documentation

The plan above is derived from the code and from the Care One OS UX Specification already in the repo. **The big documentation mentioned on 2026-08-04 has not been read yet** — point at where it lives and this file gets reconciled against it. Specifically, it likely settles:

1. Which outcomes and reasons the product actually uses, versus the current `A/S/R/W/N/O` set.
2. What must be configurable per service mode, beyond terminology.
3. Whether anything in Phase 1 is missing from this plan entirely.
4. Any rules that contradict what the existing backend does — those matter most, because the backend is shared and changing it affects all four front ends.
