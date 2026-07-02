# Issues to fix

> A running list of problems, bugs, and "that looks off" things the owner spots while reviewing.
> **When the owner says "add this to issues", it gets added here.** We fix these once the UI design is settled.
>
> **Status key:** 🔲 open · 🔧 in progress · ✅ fixed
> Each issue has a number, the date it was noticed, what it is (plain English), and where (which screen/area).

## Open

### Roadmap — bigger features (added 2026-06-30)

- 🔲 **#17** — **Medications dictionary / formulary.** Build a proper meds reference so the app stops parsing free-text `dose`/`dosage` and auto-fills **form, strength, unit, route, default dose, CD schedule**. Foundation for #15. **Agreed direction (2026-06-30):**
  - **Provider-agnostic med model** — our own normalised `medication` model + a pluggable "drug-data provider" interface, so the source (dm+d / FDB / RxNorm…) can change without touching the app.
  - **UK base = dm+d** (NHS Dictionary of Medicines and Devices) — free, NHS-standard, and the **same codes GP Connect (#18) uses** → store the dm+d/SNOMED code on each `mar_sheet`.
  - **Clinical engine = FDB (First Databank)** or a peer (Medi-Span / Multum / Micromedex / Vidal) for the things dm+d doesn't have — allergy cross-reactivity + interactions (#23). FDB Multilex maps to dm+d.
  - **Worldwide architecture, UK-first rollout** — design for many markets (add providers per country: dm+d UK, RxNorm US…), but ship UK first. NB other markets need their own regulators/integrations (CQC & GP Connect are UK-only).
  - **dm+d source:** prefer the **FDB feed** (they keep it current, so we may never touch TRUD); otherwise a **free NHS TRUD account** (registration is quick; dm+d is Open Government Licence). **Never** use unofficial GitHub mirrors for a clinical system.
  — *Area: data model + drug-data provider + medication forms everywhere.*
- 🔲 **#23** — **Allergy & drug-interaction checking.** Flag when a prescribed/administered med clashes with a resident's allergies (and, later, with their other meds). **v1 = allergy checking** — match the product's **ingredients** (from dm+d) against the resident's **coded allergies** (entered manually now, fed by **GP Connect** once live), plus a curated **allergen-class map** (penicillins, NSAIDs, sulfonamides…) for common cross-reactivity. **Later = full drug–drug interactions** via the **FDB** clinical engine (licensed). Must be framed as **decision-support, not a substitute for clinical judgement**, with clinical sign-off. — *Area: prescribing + Medication Round (prescribe-time + administer-time checks).*
- 🔲 **#18** — **GP Connect integration.** Connect to **NHS GP Connect** to pull/sync each resident's GP **medication record, allergies and key clinical info**, so prescriptions and changes flow from the GP rather than being typed in by hand. Medications/allergies come back **coded in dm+d/SNOMED** — which is exactly why #17 builds on dm+d and why coded allergies make #23 reliable. UK-only. — *Area: integrations + prescriptions.*
- 🔲 **#19** — **Reports page (CQC-aligned).** A reporting area producing reports mapped to **CQC** expectations — MAR compliance, missed/refused doses, controlled-drug audit, stock & expiry, etc. — viewable and **exportable for inspections**. — *Area: new Reports section.*
- 🔲 **#20** — **Incident reporting.** A page to **raise, categorise and review incident reports** (falls, medication errors, safeguarding, behaviour…), each with status/follow-up, who-what-when, and a link to the resident. Shows the list of incidents and their detail. — *Area: new Incidents section.*
- 🔲 **#21** — **Trend / pattern alerts.** Watch the data for **recurring patterns** and flag them — e.g. a resident is repeatedly **asleep when a particular med is due** (so it keeps getting omitted), recurring refusals, time-of-day patterns. Surface a "**noticed a trend**" alert so staff/clinicians can act (e.g. review the dose timing with the GP). — *Area: analytics + alerts.*
- 🔲 **#22** — **Book-out (leave of absence) for meds.** When a resident **books out / goes on leave** and takes their medication with them, mark those meds as **"booked out"** — responsibility passes to the resident/family for that period, so the doses **aren't flagged as missed** and the MAR reflects the absence. **Book back in** on return. — *Area: Medication Round / MAR + resident leave.*

### Smaller fixes

- 🔲 **#16** (noticed 2026-06-30) — **Enforce an administration time window.** A scheduled dose shouldn't be givable **too early** (before its time) or **too late** (long after) — and repeat/PRN doses must honour a **minimum gap (e.g. 2 hours)** between administrations. Today the round lets you record any slot at any time. Proposal: a **configurable window** around each scheduled slot (e.g. ± a couple of hours), **block "Given" outside it** (with a manager override + an audit reason); PRN min-interval is already enforced server-side — extend the same idea to **scheduled** doses. Needs a policy call on the exact window per round/med. — *Area: Medication Round (record/administer) — `MedicationRoundController@applyRecord` + the round UI.*
- 🔲 **#15** (noticed 2026-06-30) — **CD register unit is guessed from free-text.** The Add-CD-entry form now auto-fills the unit by parsing the dose string (`"1 tablet"` → `tablet`), but the clinically-meaningful unit is often the **strength unit** (e.g. **mg / g**) — for "Methylphenidate 10 mg" the right answer is *mg*, not *tablet*. Free-text `dose`/`dosage` columns make this ambiguous. **Fix properly when we add a medications dictionary / formulary** (each med having a defined form, strength and unit) and auto-fill from that instead of string-parsing. — *Area: Controlled Drugs 4.1 — Add entry; medications data model.*
- 🔲 **#14** (noticed 2026-06-30) — **Witness should confirm, not just be typed.** When a controlled drug is administered, instead of the carer typing a witness name, the app should **send the named witness a request/notification to confirm** they witnessed it (they approve in-app). Bigger feature — needs a notifications channel + a confirm screen + an "awaiting witness confirmation" state on the dose. — *Area: Medication Round (CD administration) + notifications.*
- 🔲 **#13** (noticed 2026-06-30) — **Dose notes aren't surfaced.** The free-text **note** (and the chosen **reason**) recorded against a dose are saved but **not shown** on Medication Round 4 (the recorded row only shows the outcome pill), and Missed Doses shows the code label, not the typed reason/note. Surface them so staff can see *why* a dose was refused/omitted and any note. *(Started: Round 4 now shows reason/note/witness on a recorded dose.)* — *Area: Medication Round 4 + Missed Doses 4.1.*
- 🔲 **#12** (noticed 2026-06-30) — **Alerts should be actionable** — make each alert row **clickable so it navigates to the right place** (the relevant resident/med on the round, the low-stock med on Stock, the CD entry, the missed dose to resolve…), and give a way to **resolve / dismiss / swipe it away** so handled alerts leave the list. Right now the alert rails are read-only. — *Area: all 4.1 alert rails — Medication Round 4, Stock 4.1, Controlled Drugs 4.1, Missed Doses 4.1.*
- 🔲 **#11** (noticed 2026-06-23) — **Icons look a bit "silly"/playful** in places — the owner wants a more **professional icon set** across the medication screens (e.g. the round/stat/quick-action icons, the pill/box icons). Review and swap to cleaner, more clinical-looking icons (consistent weight/style). — *Area: all medication screens — iconography.*

## Fixed

> Most were addressed on the **primary Medication Round build** (the page formerly "Lab 1.4.2") + the app shell. The older trial lab pages keep their original behaviour.

- ✅ **#1** (fixed 2026-06-23) — The **logo now also sits in the top header bar** (a small navy chip next to the menu button), so the brand stays visible even when the sidebar is collapsed. — *Area: app shell.*
- ✅ **#2** (fixed 2026-06-23) — Empty Alerts now shows a small **"No alerts for this round"** line (teal ✓), not a big empty card. — *Area: Medication Round sidebar — Alerts.*
- ✅ **#3** (fixed 2026-06-23) — **Overdue alerts are clickable** — clicking one **selects that resident** (jumps to their detail). — *Area: Medication Round sidebar — Alerts.*
- ✅ **#4** (fixed 2026-06-23) — The **Round Progress doughnut is hoverable** — each segment shows a tooltip (e.g. "Completed · 4", "Overdue · 3"). — *Area: Medication Round sidebar — Round Progress.*
- ✅ **#5** (fixed 2026-06-23) — The **Alerts section is a collapsible accordion** (click the header to open/close); same for the "By round" breakdown. — *Area: Medication Round sidebar — Alerts.*
- ✅ **#6** (fixed 2026-06-23) — Each alert has a **dismiss (✕)** to clear it from the list. — *Area: Medication Round sidebar — Alerts.*
- ✅ **#7** (fixed 2026-06-23) — Clicking the **same resident again closes** the detail (one click opens, click again closes). — *Area: Medication Round — residents list.*
- ✅ **#8** (fixed 2026-06-23) — The sidebar is now interactive: **alerts clickable + hoverable + dismissible**, the **doughnut hoverable**, and the Quick Actions link out (Missed Doses / Handover). — *Area: Medication Round sidebar.*
- ✅ **#9** (noticed 2026-06-18, fixed 2026-06-18) — Long alerts list **pushed the Quick Actions down the page**. Now the **alerts list scrolls inside its own fixed-height area** so the Quick Actions stay put. — *Area: Medication Round sidebar — Alerts box.*
- ✅ **#10** (fixed 2026-06-23) — **Room number is now a real DB column** (`service_user.room_number`, surfaced in the controller), so the resident card shows the room. — *Area: data/backend — residents.*

_(fixed issues move down here, with the date they were fixed)_
