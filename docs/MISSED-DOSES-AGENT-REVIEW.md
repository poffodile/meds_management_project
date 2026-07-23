# Missed Doses — agent review panel (2026-07-16)

Reviews of the current **`/frontend2/missed-doses`** page (`resources/js/Pages/Frontend2/MissedDoses.jsx` + `MissedDosesController`). These findings are the spec for building a **fresh, improved version in the Medication 2 slot** (`/frontend2/medication-2/missed-doses`), leaving the original untouched as a fallback.

> Panel: 3 agents so far (Compliance, Functionality, UI). User adding 2 more → 5 total, then merge into one build.

---

## 1 · Compliance (UK CQC / NICE SC1・NG5 / RPS / Misuse of Drugs Regs)

Bones are good (who/what/when captured, gap detection sound), but **partial, not compliant**. Priorities:

1. **Stop deleting clinical records + tamper-evidence** — `runUnresolve` hard-deletes the resolution (`MissedDosesController.php:415`); log table is freely mutable. Switch to **void/supersede (soft-delete) + append-only immutable log**. [CQC Reg 12 & 17]
2. **Controlled-drug handling** — a missed CD is treated like paracetamol: no witness/second signatory, no running-balance/register link. Detect CDs → require witness → link register. [Misuse of Drugs Regs 2001; RPS; CQC CD guidance]
3. **Real clinical escalation & monitoring** — "GP informed" is only a dropdown label. Capture **who contacted, when, advice received, monitoring required**; flag time-critical/high-risk meds (insulin, anticoagulants, Parkinson's, epilepsy). [NICE SC1 §1.11, NG5]
4. **Mandatory structured reason** for refusal/omission (currently only *derived* from MAR code; notes optional). [NICE SC1 §1.11]
5. **No box-tick closure + reason-for-amendment** — block clearing Refused/Withheld/CD with "No action needed" and no notes; require a reason on every edit. [CQC Reg 17]
6. Restrict Undo + full-audit export to managers; **log exports** of resident health data. [GDPR]
7. Define **retention** (retain resolved/voided records; don't delete).
- ✅ Met: who/what/when audit trail; MAR-gap detection.

## 2 · Functionality (all controls traced + backend rolled-back smoke-tested)

Everything is wired and persists correctly. Bugs:

- **D1 (med)** Business-logic failures ("Medication not found") redirect with `->with('error')` but Inertia still fires `onSuccess` → **success toast on failure** and no flash rendered anywhere. Throw 422 / gate toast on `!flash.error`.
- **D2 (low)** `reload` uses `preserveState` → an open `DosePanel` keeps the **previous date**; resolving after nav posts the stale date. `closePanel()` in reload or key panel on date.
- **D3 (low)** Donut/legend use outstanding-only counts but clicking a legend opens a tab that includes resolved rows → **numbers disagree**.
- **D4 (low)** "Follow-ups pending" sets `tab='outstanding'` (not in TABS) → **no visible active tab**.
- **D5 (low)** No loading/disabled state on **Undo/Refresh** → possible double-post.

## 3 · UI / mobile-first (phone → tablet → desktop)

Table handles mobile well (hides cols, surfaces reason). Blockers:

1. **Filter-tab strip can overflow** → body horizontal scroll. Wrap with `overflowX:auto` like Stock 2. (highest UI)
2. **Remove `zoom: 0.9`** (`MissedDoses.jsx:306`) — hurts mobile legibility, desyncs from `useMediaQuery` breakpoints, inconsistent with Stock 2.
3. **Detail panel opens off-screen on mobile** (`:517`) — use a Drawer/bottom-sheet or `scrollIntoView`.
4. **Touch targets < 40px** — date prev/next, refresh, Today, clear-search.
5. Header toolbar wraps awkwardly on phone; legend cards draw stray divider on wrap.

**Design consistency:** use shared tokens (`palette.ink`, `palette.cardBg/tableBg`, `statusColors`, `palette.teal`) instead of hardcoded hex; add **tabular numerals**; `HEADING_FONT` on titles; align panel width/radius with Stock 2.

---

## 4 · (pending — user's note)
## 5 · (pending — user's note)
