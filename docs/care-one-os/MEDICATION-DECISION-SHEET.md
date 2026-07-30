# Medication pages — decisions needed from named humans
**Raised:** 2026-07-28 · **Owner of this sheet:** [owner]
**Purpose:** the four medication pages (Round, Missed doses, Controlled drugs, Stock) have each been through the full review. Every review finding that engineering could safely fix has been fixed. What remains blocking each page from "Ready" is a set of **decisions that are not engineering's to make** — they need a pharmacist, a Clinical Safety Officer (CSO), a registered manager, or the owner. This sheet is those decisions, so they can be signed off and then built against.

**How to use it:** for each item, read *the question* and *why it matters*, pick an option (my engineering recommendation is marked ⭐), then fill in **Decision / By / Date**. Return it and the corresponding build follows.

**Claim boundary:** the recommendations below are *engineering* recommendations. None of this makes Care One OS compliant, certified, clinically safe, or NHS-approved. The clinical, regulatory and information-governance calls belong to the named humans; software support for a record is not a determination that the record-keeping is lawful.

---

## A. Owner-level decisions (you can answer these directly)

### A1 — Who may CHANGE stock, and who may VIEW a controlled-drug register?
**Why it matters:** today every logged-in medication user can *view* stock and CD registers, and (until the fix this week) could *change* stock. The write side is now locked to "manager" (M/CM/A/O); a plain carer (N) can view but not change. Two open questions remain about *viewing* CD registers and the exact role split.
**Trace:** Stock C-1 (fixed to "manager"), CD I-1, review role-model note.

| Option | Effect |
|---|---|
| ⭐ **Keep "manager changes, carer views" for stock; restrict CD register *view* to manager-level too** | Simple, matches the current role split; CD registers (sensitive) not visible to every carer |
| Allow carers to view CD registers | More open; a carer can see the running balance but not write |
| Introduce a dedicated "medication lead" permission distinct from manager | More precise, but needs the role model extended (bigger change) |

**Decision:** ✅ **Manager changes stock; carers may VIEW stock; controlled-drug registers are MANAGER-ONLY (view + write).**  **By:** owner  **Date:** 2026-07-28

### A2 — Witness at administration / CD movement: typed name, or a real second sign-in?
**Why it matters:** the second person who witnesses a controlled-drug administration or movement is currently recorded as a **typed name**. Anyone can type any name — it's an honesty record, not a verified one. A verified witness (the second person signs in / enters a PIN) is stronger but needs building and changes the workflow.
**Trace:** HAZ-05, CD I-6, Round I4. Force class: witnessing is CQC/NICE/RPS **good practice**, not MDR 2001 statute.

| Option | Effect |
|---|---|
| ⭐ **Keep typed name for now; log it clearly as self-attested; plan verified sign-in as a later phase** | No workflow disruption now; honest about what it is; upgrade path noted |
| Require a verified second sign-in (PIN / login) before a CD can be recorded | Strongest control; more friction; needs build + every witness to have an account |
| Typed name in supported living, verified sign-in in care homes | Setting-aware; most complex |

**Decision:** ✅ **Witness co-signature workflow (owner spec, 2026-07-28):** keep option 1 (typed name, self-attested) as the base, PLUS —
1. When the administering staff member (e.g. Adam) enters a witness name (e.g. Eve), a **pending witness confirmation** is created against that record.
2. **Eve receives a notification on her own account** prompting her to **confirm the signature** ("did you witness this?").
3. Until Eve confirms, the witness shows as **"awaiting confirmation"**; once she confirms it shows as **witness-confirmed** (with when).
4. A **manager can override** the confirmation (confirm on the witness's behalf / mark manager-overridden); the record must **clearly say** it was *manager-overridden* rather than *witness-confirmed*.
5. The typed witness name still shows throughout; the confirmation status is additional.

**Build note:** substantial — new fields (witness confirmation status + confirmed_by/at + overridden_by/at), a notification to the witness's account, a "pending confirmations" surface for the witness, and the override control for managers. Scoped as its own build; **CSO to confirm** this satisfies the good-practice witness intent.  **By:** owner  **Date:** 2026-07-28

---

## B. Registered-manager / pharmacist decisions

### B1 — What happens when a controlled-drug count doesn't add up? (CD I-2)
**Why it matters:** if someone records removing more of a CD than the running balance says exists, the register currently stores a tidy `0` — hiding the exact discrepancy a CD register exists to catch.
**Trace:** CD I-2, HAZ-26.

| Option | Effect |
|---|---|
| ⭐ **Record the true figure (allow it to go negative) and flag the entry as a discrepancy for review** | The impossible result is visible and auditable; nothing is hidden |
| Block the movement entirely until the balance is corrected | Prevents the bad entry, but could stop a genuine, urgent administration |
| Store `0` but raise a medication-incident flag | Keeps the balance clean but records that something was wrong |

**Decision:** ________________  **By:** ____________  **Date:** __________

### B2 — Zero stock on the round: block the dose, or warn and allow? (IM-06)
**Why it matters:** if the system thinks stock is zero, should it *stop* a carer recording the dose, or *warn* and let them proceed? Stock figures can be stale; a hard block on stale data could omit a genuinely-needed dose.
**Trace:** IM-06.

| Option | Effect |
|---|---|
| ⭐ **Warn + confirm, don't hard-block; steer to "not available" if truly out** | Avoids blocking a real dose on a possibly-wrong number |
| Hard-block when stock is zero | Prevents recording against no stock, but risks omitting a needed dose |

**Decision:** ✅ **Mostly block (owner, 2026-07-28):** a medicine that isn't in stock should NOT be recorded as given — but not a dead-end: the carer can record it as **"not available"**, and if the count is wrong a **manager can correct the stock** (carers can't change stock — see A1). So: block the "Given" action at zero stock, steer to "not available", and surface that a manager can correct the count.  **By:** owner  **Date:** 2026-07-28

### B3 — What should be recorded when a round is ENDED with doses still outstanding? (Round C2)
**Why it matters:** ending a round currently just locks it — it writes **no per-dose record** for the doses left outstanding. The UI now says so honestly ("left unrecorded"), but a proper record probably needs *something* written per dose (a reason, or an escalation).
**Trace:** Round C2, Missed I1, REQ-MED-06/08/30, HAZ-10.

| Option | Effect |
|---|---|
| ⭐ **On end, write each outstanding dose as a structured "not given — round ended" with a reason prompt** | Every dose has a record and a reason; nothing silently disappears |
| Require the round to be fully recorded before it can be ended | Cleanest record; but blocks ending a round when something genuinely can't be given |
| Keep "just lock it" (current) | Least work; leaves a gap in the record for outstanding doses |

**Decision:** ✅ **Require every dose recorded before a round can end (owner, 2026-07-28, pending CSO):** don't auto-write "not given". The round **cannot be ended while any dose is outstanding** — the carer must record each one (given, or a reason such as refused / not available). Ending shows an **"are you sure?"** confirm. Net effect: every dose carries a real, carer-entered outcome + reason; nothing is machine-filled.  **By:** owner  **Date:** 2026-07-28

### B4 — "Given late": should resolving a missed dose as "dose given late" write a MAR entry? (Missed I1)
**Why it matters:** on the Missed-doses page, resolving with "dose given late" records a *review* but writes **no MAR administration** — so the medication chart still shows a gap while the review says it was given. The two records disagree.
**Trace:** Missed I1, HAZ-26, REQ-MED-30/32.

| Option | Effect |
|---|---|
| ⭐ **"Dose given late" writes a late MAR administration (flagged late), so the chart and the review agree** | One consistent record of what the resident actually received |
| Keep review-only; the MAR gap stays | Simpler; but the chart and review contradict each other |

**Decision:** ________________  **By:** ____________  **Date:** __________

---

## C. Pharmacist / Clinical Safety Officer (clinical content)

### C1 — A real administration-directions field ("do not crush", "with food", "half-hour before food") (Round C1)
**Why it matters:** there is currently **no field** that holds a genuine administration directive. The round can only show the *indication* (why it's prescribed). A safety directive like "do not crush" can't be carried at all.
**Question for the pharmacist/CSO:** what should this field contain, where does it come from (prescriber entry? catalogue?), and which directives are safety-critical enough to show prominently?
**Trace:** Round C1, HAZ-22, REQ-MED-04.
**Decision / spec:** ________________  **By:** ____________  **Date:** __________

### C2 — Controlled-drug reconciliation + the free-text-dose CD (CD C-2 / Stock C-2 / Round I3)
**Why it matters:** (a) a CD given with a **free-text dose** (e.g. "10ml (500mg)") records on the chart but **skips the register**, so no balance moves; (b) CD **stock** movements (disposal, count corrections) don't post to the register either. Both need the dose captured as a proper quantity, which needs the medicine mapped.
**Question:** confirm the approach — require a structured quantity before a CD can be given/moved, and post every CD movement to the register.
**Trace:** CD C-2, Stock C-2, Round I3, HAZ-24, REQ-MED-20/21/22.
**Decision / spec:** ________________  **By:** ____________  **Date:** __________

### C3 — The MAR outcome code set (proposal) (CR-04 / Part 2)
**Why it matters:** the current codes mix "what happened", "why", and "who decided". A cleaner set was proposed (e.g. asleep `Z` = not given; `H` held-on-clinical-advice requires an authoriser; splitting the catch-all "omitted" into absent / no-stock / unexplained-incident). **This must not ship without pharmacist sign-off**, and it's unverified whether any national code set is mandated.
**Question:** approve, amend, or replace the proposed code set in `ROUND-FIX-PLAN.md` Part 2.3.
**Trace:** CR-04, ROUND-FIX-PLAN Part 2.
**Decision / spec:** ________________  **By:** ____________  **Date:** __________

### C4 — Stale-weight threshold and behaviour (CR-09)
**Why it matters:** weight is used for weight-based dosing. "Stale after 90 days" is a **placeholder**. The real threshold is probably age-dependent, and it's unclear whether a stale weight should *block* or *warn* on a weight-based dose.
**Question:** the threshold(s), whether age-banded, and block vs warn.
**Trace:** CR-09, REQ-MED-112.
**Decision / spec:** ________________  **By:** ____________  **Date:** __________

### C5 — dm+d medicine codes (ISSUE #17)
**Why it matters:** medicines are identified by **free-typed name** today, not a stable NHS dm+d/SNOMED code. This underlies several findings (a rename can fork a CD balance; no interoperability). Needs a pharmacist-reviewed mapping of the catalogue.
**Question:** commission the dm+d mapping; confirm the concept level (VTM/VMP/AMP).
**Trace:** O3 across pages, CD I-3, REQ-MED-50/107.
**Decision / spec:** ________________  **By:** ____________  **Date:** __________

---

## Summary — what unblocks when

| Decision | Signs it off | Unblocks |
|---|---|---|
| A1 role split | Owner | CD view scope, tidy role model |
| A2 witness | Owner + org policy | HAZ-05 across CD + Round |
| B1 CD discrepancy | Manager + pharmacist | CD I-2 |
| B2 zero stock | Registered manager | IM-06 |
| B3 round-end record | Manager + CSO | Round C2 (a Critical) |
| B4 given-late MAR | Pharmacist + CSO | Missed I1 |
| C1 directions field | Pharmacist + CSO | Round C1 (a Critical) |
| C2 CD reconciliation | Pharmacist + CSO + manager | CD C-2 + Stock C-2 (Criticals) |
| C3 code set | Pharmacist / med lead | CR-04 |
| C4 stale weight | Pharmacist / CSO | CR-09 |
| C5 dm+d codes | Pharmacist | interoperability + CD I-3 |

**The three page-blocking Criticals that need a human are B3, C1 and C2.** Answer those and the Round, CD and Stock pages can move toward Ready; the rest are Important/Optional hardening.
