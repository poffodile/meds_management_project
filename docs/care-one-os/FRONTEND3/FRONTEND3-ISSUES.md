# FRONTEND3 — issues & things to come back to

Open items for frontend3. This is the "don't lose it" list — things parked deliberately, not forgotten.
Rules and stack: [FRONTEND3-PLAN.md](FRONTEND3-PLAN.md) · Diary: [FRONTEND3.md](FRONTEND3.md) · Brief: [CARE-ONE-OS-UX-SPECIFICATION.md](CARE-ONE-OS-UX-SPECIFICATION.md)

| # | Issue | Status | Raised |
|---|---|---|---|
| 1 | The 12 wireframe prototypes named in Appendix A are missing | **Parked — own versions built instead** | 2026-08-04 |
| 2 | First build slice not chosen | Open | 2026-08-04 |
| 3 | Overlap with frontend2's existing medication pages undecided | Open | 2026-08-04 |
| 4 | Spec palette vs existing brand guidelines are close but not identical | Open — watch | 2026-08-04 |

---

## Issue 1 — The 12 wireframe prototypes are missing

**Status:** Parked. Own versions built in the meantime, so this does not block anything.

Appendix A of the specification ("Wireframe Inventory and Prototype Coverage") says its purpose is to *"maintain a direct bridge from this specification to the approved interactive concept screens"*, and lists twelve prototype files:

| # | Concept | Prototype file named in the spec | Present? |
|---|---|---|---|
| 01 | Frontline dashboard | `careone-dashboard-wireframe.html` | ✗ |
| 02 | Medication round | `careone-medication-round-wireframe.html` | ✗ |
| 03 | MAR chart | `careone-mar-wireframe.html` | ✗ |
| 04 | Person profile | `careone-person-profile-wireframe.html` | ✗ |
| 05 | PRN workflow | `careone-prn-wireframe.html` | ✗ |
| 06 | Missed doses / exceptions | `careone-missed-doses-wireframe.html` | ✗ |
| 07 | Stock and pharmacy | `careone-stock-pharmacy-wireframe.html` | ✗ |
| 08 | Controlled drugs | `careone-controlled-drugs-wireframe.html` | ✗ |
| 09 | Shift handover | `careone-handover-wireframe.html` | ✗ |
| 10 | Manager and compliance | `careone-manager-compliance-wireframe.html` | ✗ |
| 11 | Administration and integrations | `careone-admin-integrations-wireframe.html` | ✗ |
| 12 | AI workspace | `careone-ai-workspace-wireframe.html` | ✗ |

**Where we looked:** not in this repository, and not in `C:\Users\lambe\Downloads\`. The only loose HTML in Downloads is `administer-modal.html`, `Med Round -standalone-.dc.html` and `resident-meds-card.html`, all dated 2 July 2026, and none of them match these names or this design direction.

**What was done instead (2026-08-04):** rather than wait, a **complete original set of twelve** was designed and built from the specification, in `wireframes/`, using the spec's own file names. They implement the Quiet Clinical Luxury palette and typography, work from 390px to 1440px, and follow the spec's own prototype review checklist (fictional data only, every status and empty state exercised, one-handed primary actions).

**Why this is still an open issue:** if the *original* twelve exist somewhere, they represent someone's approved thinking and should be compared against these before either set is treated as settled. Two things to resolve when you come back to it:

1. **Do the originals exist?** Another machine, a Downloads clear-out, a chat thread, a design tool export.
2. **If they turn up, which set wins?** Compare, merge the better ideas, and record the decision in `FRONTEND3.md`. If they never turn up, mark this issue closed and treat the built set as the baseline.

---

## Issue 2 — First build slice not chosen

The specification's closing line recommends moving *"into a clickable design-system prototype: start with the global shell, frontline dashboard and medication round, then validate the workflow with support workers, nurses, managers and pharmacy partners."*

That is a sensible first slice, but it has not been confirmed, and the recommended engineering sequence in section 20 actually starts further back — *"domain model, terminology, identity, tenancy, permissions and audit"* before any screens. Needs a decision on which comes first: the clickable prototype, or the foundation underneath it.

---

## Issue 3 — Overlap with frontend2's existing medication pages

frontend2 already has Medication Round, MAR, Stock, Missed Doses and Controlled Drugs. The specification covers all of them, to a different design and a fuller workflow. Undecided:

- Does frontend3 **rebuild** those to the blueprint, or only build what frontend2 lacks?
- If it rebuilds, do the two versions run side by side for a while, and who decides when the frontend2 version retires?
- Does frontend3 reuse frontend2's **backend** controllers and routes, or get its own? (The isolation rule is about CSS and front-end files; it does not by itself say anything about controllers.)

---

## Issue 4 — Spec palette vs existing brand guidelines

The two are close enough to be confused and different enough to look wrong if mixed:

| | Existing brand guidelines | Frontend3 specification |
|---|---|---|
| Navy | `#13233F` | `#17243B` |
| Accent | teal / orange / purple / green | clinical teal `#176B65` only |
| Background | white / neutral | warm clinical ivory `#F6F2E9` |
| Headings | per `docs/brand-guidelines.md` | Manrope |

Keeping them in separate token files (`frontend/tokens.js` vs `frontend3/tokens.js`) is what prevents accidental blending. Open question for later: does the official brand eventually move to the spec's palette, or do the two stay permanently distinct?
