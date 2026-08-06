# RECORD7 — the detailed build plan

**The master plan for building the product page by page, to a finished standard, one at a
time.** This is the plan the pages get built from. It folds in the functionality from both
specifications (the UX Specification and the Visual & Page Specification) and the RECORD7
product identity.

Companion docs: [CARE-ONE-OS-MERGED-PLAN.md](CARE-ONE-OS-MERGED-PLAN.md) (the two specs
merged, C1–C8), [FRONTEND4-MILESTONES.md](FRONTEND4-MILESTONES.md) (milestone track),
[FRONTEND4-ISSUES.md](FRONTEND4-ISSUES.md) (issues), [FRONTEND4-NICE-AND-BNF.md](FRONTEND4-NICE-AND-BNF.md)
(where NICE plugs in), [RECORD7-NICE-LICENCE-APPLICATION.md](RECORD7-NICE-LICENCE-APPLICATION.md)
(the licence governance record). Written 2026-08-05.

---

## 0. Product identity

**The product is RECORD7 when it stands alone, and Care One OS when it is the integrated
module inside Omega's wider platform.** Same software; the name reflects the context. This
is branding to carry through the plan — it does not change the architecture.

> **Naming is not final (noted 2026-08-05).** For now it may simply be called **Care One OS**,
> or **"RECORD7, powered by Care One OS"**. Nothing in the build depends on which is chosen —
> keep product-name references easy to change (from configuration where they're shown), and
> don't hard-code a single brand string into components.

- **RECORD7** — a standalone medication management and **eMAR** platform, developed from
  within real frontline care by **Omega Care Group Ltd**. It supports medication rounds,
  refusals, omissions, PRN decisions, stock control, controlled drugs, witnessing,
  incidents, offline working, audit trails and management oversight — helping care teams
  record every medication action accurately, promptly and accountably.
- **The name.** The six recognised rights of medicines administration are the right
  **person, medicine, route, dose, time** and the person's **right to decline**. RECORD7 adds
  Omega's **seventh safeguard — the Right Record**: safe medication practice is not complete
  until every administration, decision and follow-up action has been properly recorded.
- **Setting.** Built first for **children's residential care and related care services** in
  **England and Wales**, but designed to be **standalone and versatile** — the same product
  serving a care home, a children's home, supported living, a domiciliary service or an
  individual. Nothing about a setting is hard-coded (see the versatility rules in the design
  doc).
- **Where RECORD7 shows in the interface.** The seven-rights idea is the product's spine, so
  it should surface honestly: the administration screen walks the six rights in order, and
  "the Right Record" is the audit trail, the append-only correction chain and the follow-up
  workflow — the thing that says an action isn't finished until it's recorded.

---

## 1. How we build — the method

**From scratch, page by page, little by little — each page finished to a real standard
before the next.** Not a rush, and nothing overlooked. The order is a difficulty ladder so we
build momentum on the safe pages and take the dangerous ones slowly.

Rules of the method:

1. **One page at a time, to "Done" (section 2).** A page is not "done" because it renders —
   it is done when it works end to end, is safe, is tested, and the other three front ends
   are provably untouched.
2. **Fix the issues as we build.** Most open issues (FRONTEND4-ISSUES.md) are the database
   gaps a specific page needs — they get closed *by* the page that needs them, not as
   separate work. Every new issue found goes in the issues doc, the test log, and the online
   issue tracker.
3. **External data is an enhancement layer, never a dependency** (section 3). No page waits
   on NICE, GP Connect or dm+d. Each is built on a fallback and the external source slots in
   where marked.
4. **Isolation holds** (FRONTEND4-PLAN.md). Every rule under `.f4-root`, no global CSS, own
   bundle and root view. Adding NICE or anything else never touches frontends 1–3.
5. **Record as we go.** Update the running log (FRONTEND4.md) and the test log each session.

---

## 2. Definition of Done — the bar every page must clear

A page is finished only when **all** of these are true:

- **Functionality wired end to end** against the real database for the signed-in user's own
  home, and only their own home.
- **Every action actually writes**, and is **refused server-side when it should be** — hiding
  a button is a courtesy, the server check is the feature.
- **Permissions enforced server-side** on every action, by role.
- **Attributable & append-only** — who, what, when, and why when a reason is required.
  Clinical records are corrected by addendum, never overwritten or deleted.
- **The six states** are wired to real conditions, not just built: loading, empty, error,
  offline, no-permission, conflict.
- **Safety patterns present** where relevant: the safe sequence, mandatory reason before
  confirm, persistent identity + allergy strip, real witness co-signature, offline refusal
  for CDs.
- **Responsive & accessible** — mobile-first (390 → 768 → 1280), no horizontal body scroll,
  WCAG 2.2 AA on the critical journey, full keyboard operability, status never colour-alone.
- **Tested** — a manual test case per feature in the test log; the medication suite still at
  its baseline (or better, with any change explained).
- **Issues logged** — anything found is written into all three issue records.
- **Isolation verified** — `/frontend4` serves only its own CSS/JS; frontends 1–3 unchanged.

---

## 3. The external data layers (enhancement, never dependency)

| Layer | What it is | Where it shows in RECORD7 | Fallback if absent |
|---|---|---|---|
| **NICE Syndication API** *(licence held — Full)* | NICE **guidance, quality standards, information for the public**. **NOT** BNF/BNFc/CKS, **NOT** dm+d. | Clearly-labelled **guidance panels**: title + reference, snippet/extract, a concise (optionally AI-assisted, clearly-labelled) summary, publication date, attribution, a link to the full guidance. Attached to a medication topic, condition or care activity — e.g. on Missed doses / escalation, on a condition in the client profile. Never Omega-authored advice; never autonomous clinical decisions. | Link to the **organisation's own policy** (from config). The workflow never generates clinical advice itself either way. |
| **dm+d / SNOMED CT** *(separate licence "where appropriately licensed" — not the NICE key)* | NHS stable coded identifiers for medicines (VTM/VMP/AMP) and clinical terms. | Coded identifier stored against each medicine; authoritative name/strength/form; two spellings become one medicine. Closes **D2 / I4**. | Free-text `medication_name` + light in-house normalisation. Adequate, not authoritative. |
| **GP Connect** *(planned, separate)* | Authorised patient information from GP records (structured meds, allergies). | Reconciliation into the client's medication and allergy record, with provenance. Behind a feature flag + mock until live. | Locally entered/verified records only. |
| **Cyber Essentials Plus** *(certified)* | UK gov audited security certification. | Not a screen — it's the security posture behind the whole product (DSPT/DTAC story). Underwrites the permissions, audit and access-log work (I2, I7, I11). | — |

**The governing rule (merged plan C8):** an unavailable external service must **never** block
administering a medicine. NICE/GP Connect/dm+d add reference, coding and reconciliation — they
never gate the frontline act.

---

## 4. The order we build in

From the difficulty ladder (easiest → hardest), reconciled with the spec's product order.
Status as of 2026-08-05.

| # | Page | Status | Difficulty |
|---|---|---|---|
| 1 | People / Clients | to build | ●●○○○ |
| 2 | Client profile | to build | ●●●○○ |
| 3 | Medication round (queue + recording) | built (M2) — finish to Done | ●●●○○ |
| 4 | MAR sheet & medication history | to build | ●●●○○ |
| 5 | Medication stock | to build | ●●●●○ |
| 6 | Missed doses & follow-ups | to build | ●●●●○ |
| 7 | Individual administration workspace | to build | ●●●●● |
| 8 | Shift handover | to build | ●●●●● |
| 9 | Controlled drugs | to build | ●●●●● |
| 10 | Reports, audit & administration | to build | ●●●●● |
| — | Today / Home dashboard | built | ●●○○○ |
| — | Login & organisation selection | mostly there | ●○○○○ |

Each page's full functional spec is in section 5. As we start a page we expand its section
into a build-level checklist; writing all ten checklists now is how a plan goes stale.

---

## 5. Per-page functional specs

Each page lists: **Purpose · Users · What it must do · Actions & permissions · States ·
Backend & data · Gaps to close · NICE/GP Connect/dm+d · Done when.** Functionality is drawn
from the Visual & Page Specification (Part C) and the merged plan.

### Page 1 — People / Clients  `/clients`

- **Purpose.** The way into a client. A searchable list of the people this user is
  responsible for; tap one to open their profile.
- **Users.** Everyone with medication access; scoped to the user's own home(s).
- **What it must do.** List clients (name, photo/initials, location if any, key flags —
  allergy, status); search by name; filter by home/location and status; tap-through to the
  profile. Show "stock remaining"-style flags only at the summary level for carers.
- **Actions & permissions.** Read-only for all roles at this stage. No client is created or
  edited here (that's admin/profile). Home scoping enforced server-side — never accept a home
  id from the request.
- **States.** Loading skeleton; empty ("no clients in this home yet" + why); no-permission;
  error with retry.
- **Backend & data.** Existing client/service_user tables and models via the home-scoping
  trait. No new tables.
- **Gaps to close.** None blocking. Allergy flag is display-only here (structured allergies
  is D1, needed later for *checking*).
- **NICE/GP Connect/dm+d.** None.
- **Done when.** List loads real clients for the user's home only; search + filter work;
  tap-through opens the profile route; the four states are wired; mobile + keyboard pass;
  isolation verified. **This page establishes the shell + list + tap-through patterns every
  later page reuses.**

### Page 2 — Client profile  `/clients/:id`

- **Purpose.** Everything known about one client, organised in tabs rather than more pages.
- **Users.** All roles view; editing is gated (below).
- **What it must do.** Persistent **header, always shown**: photo, full name, preferred name,
  DOB, location, NHS number, current status, allergies, main warnings. **Tabs:** Overview ·
  Medications · PRN protocols · Allergies · MAR history · Care notes · Documents · Audit
  history.
  - **Overview** — contacts, GP, pharmacy, next of kin, diagnoses, communication needs,
    capacity & consent, medication-support level, important care instructions, appointments.
  - **Medications** — active + previous prescriptions (medicine, dose, route, frequency,
    start/end, prescriber, review date, special instructions, current stock, status
    active/paused/stopped).
  - **PRN protocols** — when it can be offered, symptoms/conditions, min interval, max dose,
    non-medication steps to try first, escalation, effectiveness-review requirements.
  - **Allergies** — allergen, reaction, severity, recorded date, source, who recorded it.
  - **MAR history** — date range, given/missed, reasons/notes, staff & witness, export/print.
- **Actions & permissions.** Carers mainly view; shift leads update approved care info;
  managers approve changes and see audit; pharmacists (future) review medication info;
  administrators manage access but do not change clinical info. Authorised users can add a
  medication, record a change, pause/stop, upload prescription evidence, request a pharmacy
  review — **all by addendum**, never destructive.
- **States.** All six; a tab with no data shows a real empty state, not a blank.
- **Backend & data.** Existing tables. Medications tab reads the prescription/`mar_sheets`
  data.
- **Gaps to close.** **D1** (structured allergies) makes the Allergies tab real; **D2** (dm+d)
  makes the Medications tab authoritative. Page can be built displaying current data first,
  then upgraded.
- **NICE/GP Connect/dm+d.** NICE guidance panel may attach on Overview (a diagnosis/condition)
  and on PRN protocols. GP Connect later reconciles Medications + Allergies. dm+d codes on
  Medications.
- **Done when.** Header persists on every tab; all eight tabs render real data or a real empty
  state; edits write by addendum with attribution; permissions enforced server-side; states,
  mobile, keyboard, isolation all pass.

### Page 3 — Medication round  `/frontend4/round`  *(built at M2 — finish to Done)*

- **Purpose.** The working queue for a round, and recording what happened.
- **What it must do.** Round info (morning/lunch/evening/night, scheduled window, who is
  completing it, start/continue/finish); people queue (due now / upcoming / completed / needs
  attention, search + filters, ordered by urgency); person summary (name, photo, DOB,
  location, allergies, important instructions); medication list (medicine, strength, form,
  dose, route, scheduled time, special instructions, stock remaining); recording (given, not
  given, refused, unavailable, asleep, away, clinical instruction to omit, notes); round
  progress (medicines + people done/outstanding, warnings). Selecting a person opens their
  medicines on the same page.
- **Actions & permissions.** Recording through the shared `applyRecord()` — mandatory reason,
  row lock, refusal messages inherited. Carers+ record; server-enforced.
- **Gaps to close.** The **HARD STOP** holds: PRN, witnessing, stock deduction and sign-off
  are **not** in this page — they are the administration workspace (page 7) and M3. Owner
  reviews the round before those are added.
- **Done when.** Everything above renders on real data; recording writes/attributes/supersedes;
  reasons enforced; refusals show the server's own message; states + mobile + keyboard pass.
  *(Owner's visual review is the gate before M3.)*

### Page 4 — MAR sheet & medication history  `/mar`

- **Purpose.** The official medication administration record — the history, read and proven.
- **What it must do.** Filters (person, location, date range, medication, round, outcome,
  staff); header (name/photo, DOB, NHS number, location, GP, pharmacy, allergies, MAR period,
  instructions); medication rows (name, strength, form, dose, route, frequency, special
  instructions, start/review); administration entries (date, scheduled + actual time, outcome,
  staff, witness, notes, PRN reason + effectiveness, late/overdue); statuses shown with full
  meaning, **never bare codes**; entry detail (original record, corrections, who changed each,
  when, escalation/follow-up, linked incident, audit) — **records never disappear when
  corrected**; a period summary (scheduled, given, not given, late, outstanding, PRN, needing
  review).
- **Actions & permissions.** By permission: view detail, add authorised correction, record a
  late entry, add a note, link an incident, export PDF, print, send for manager review.
- **Backend & data.** `MarChartController` + the append-only `mar_administrations` chain.
- **Gaps to close.** **D2** (dm+d) for coded rows. The wide grid must scroll inside its own
  container (no body overflow).
- **NICE/dm+d.** dm+d codes on rows; NICE not central here.
- **Done when.** Grid renders and scrolls cleanly on a phone; corrections show the original +
  every change; export/print work; statuses always carry meaning; states/keyboard pass.

### Page 5 — Medication stock  `/medication-stock`

- **Purpose.** Know what is held, what is moving, and what does not reconcile.
- **What it must do.** Summary (below reorder, out of stock, deliveries awaiting confirmation,
  expiring soon, discrepancies, CD stock warnings); filters; list item (person, medicine,
  strength/form, current qty, reorder level, expiry, batch, storage, last movement, status);
  movements (delivery, dose administered, return, disposal, transfer, manual adjustment,
  damaged/missing) each recording qty before/changed/after, reason, staff, time, witness when
  required; deliveries (qty received, pharmacy, batch, expiry, prescription, barcode scan,
  confirmation, second-person check); reordering; discrepancies (record actual count,
  calculate difference, require explanation, notify lead/manager, link incident, **prevent
  silent balance changes**, preserve history).
- **Actions & permissions.** Carers view + report concerns; seniors receive deliveries + do
  counts; managers approve adjustments + investigate; pharmacists (future) review supply.
- **Backend & data.** `MedicationStockTransaction/Batch/Order` — balance_before/after exist.
- **Gaps to close.** **I17** — a shortfall must be **surfaced to the responsible role**, not
  left passive in the ledger.
- **Done when.** Every movement reconciles; discrepancies are visible, explained and routed;
  deliveries + counts + reorder work; states/mobile/keyboard pass.

### Page 6 — Missed doses & follow-ups  `/missed-doses`

- **Purpose.** The work that ages — chase the doses that were not given.
- **What it must do.** Summary (missed today, refusals, unavailable, overdue follow-ups,
  re-offers due, awaiting manager review); filters; list item (person, medicine + dose,
  scheduled time, reason, notes, staff, risk level, follow-up status); reasons (refused,
  asleep, away, vomited, unavailable, clinical instruction to omit, error, other); follow-up
  actions (re-offer, set re-offer time, contact lead, record pharmacy advice, record GP
  advice, create handover action, link/create incident, mark complete); escalation (system
  flags time-sensitive, repeated refusals, high-risk, CDs, multiple missed, unavailable,
  possible errors — but **never gives clinical advice automatically**; directs to policy and
  authorised professionals); manager review; full history.
- **Gaps to close.** **D3** — a follow-up case with a real lifecycle (new backend).
- **NICE.** The escalation → policy step is the **prime NICE guidance-panel touchpoint**: link
  the relevant NICE guidance/quality standard for the medication topic or condition, alongside
  the org's own policy. NICE informs; it never instructs.
- **Done when.** A missed dose can be followed up through its whole lifecycle and closed, with
  history; escalation points to policy/NICE, never auto-advises; states/mobile/keyboard pass.

### Page 7 — Individual administration workspace  `/frontend4/round/person/:id`

- **Purpose.** The safety-critical screen — giving a real dose to a real person, in order,
  under pressure. This is where the **six rights** are walked and the **Right Record** is made.
- **What it must do.** Person info **always visible at top** (photo, name, DOB, location,
  allergies + reactions, support level, warnings, communication needs); round context (round,
  scheduled + current time, late/overdue, who is administering, progress); each medicine
  (name, strength, form, prescribed dose, route, instructions, **reason for taking it**, stock
  remaining, last administration, CD warning, time-sensitive warning); administration actions
  per medicine (given, refused, asleep, away, unavailable, vomited, clinical instruction to
  omit, other) — if not given, require reason, notes where needed, **re-offer, escalation
  decision, handover update**; controlled medicines route to witness (select witness, witness
  confirmation, qty, running balance, staff + witness signatures); PRN section, separate
  (reason required, symptoms/pain score, last dose, min interval, max daily, dose selected,
  effectiveness-review time); completion (recorded, outstanding, warnings, notes, complete
  person, continue to next).
- **The safe sequence, enforced:** identity → medicine → dose/route/time → outcome → confirm.
  No step compressible; the reason is captured **before** confirm and the server rejects
  without it.
- **Actions & permissions.** Carers record; leads review/correct/reopen; managers view audit +
  approve escalations; **enforced server-side**.
- **Gaps to close.** **D1** (structured allergies) to *warn*, not just display. This is where
  RECORD7's seven-rights identity is most visible.
- **NICE.** A guidance panel may attach to a medicine's condition/topic, clearly labelled,
  never blocking the act.
- **Done when.** The sequence cannot be short-circuited; identity + allergy strip never scroll
  off; PRN and CD paths work; not-given path captures reason/re-offer/escalation/handover;
  server enforces everything; full a11y on the journey.

### Page 8 — Shift handover  `/shift-handover`

- **Purpose.** A safe start and end to every shift.
- **What it must do.** Shift info (current/previous shift, handing over / receiving, location,
  status, time); **automatic medication summary** (missed, refusals, late/overdue, unavailable,
  PRN given, PRN reviews due, CD concerns, stock discrepancies, incidents, prescription
  changes); entries (person, category, priority, description, action required, assigned staff,
  due time, records, status); categories + priorities (routine/important/urgent, urgent stays
  visible until resolved); **acknowledgement** (receiving staff review each required item,
  confirm understanding, name + time recorded, system shows who has/has not acknowledged);
  **AI support** (may draft a summary from approved records, staff must review, sources
  visible, must not invent, must not mark acknowledged, final record names the approver);
  history.
- **Gaps to close.** **D5** — itemised handover entries (you cannot assign/tick an item inside
  a text blob).
- **NICE / AI.** AI summary rules mirror the NICE governance: clearly labelled, sourced, never
  autonomous. NICE guidance panels may attach to flagged items.
- **Done when.** Items can be assigned, prioritised, acknowledged and closed; the auto medication
  summary is real; AI draft (if built) is labelled + sourced + never auto-acknowledges;
  history complete.

### Page 9 — Controlled drugs  `/controlled-drugs`

- **Purpose.** The hardest safety build — a register that must always balance, with two
  people and no offline shortcuts.
- **What it must do.** Summary (current CDs, awaiting witness, discrepancies, cabinet counts
  due, deliveries to enter, returns/disposals to complete); filters; register entry (date/time,
  person, medicine/strength/form, qty received, qty administered/removed, running balance,
  reason, staff, witness, linked MAR entry); movements (delivery, administration, return,
  disposal, transfer, correction, person leaving, medicine discontinued); **administration
  workflow** (confirm person, confirm prescription, select medicine + dose, check balance,
  select authorised witness, **staff and witness independently confirm**, record new balance,
  link to MAR); cabinet count (expected, actual, difference, staff, second-person confirm,
  notes, escalation on mismatch); discrepancy handling (do not silently change; lock the
  affected entry; notify manager; record actions; link incident; keep original + corrected);
  audit history.
- **Gaps to close.** **D6** (competency/witness authorisation) so witnessing checks authority,
  not just role. **C7** — offline completion is **refused, not queued**.
- **Done when.** Two independent accounts confirm; the register reconciles at every movement
  and cabinet count; discrepancies lock + escalate; offline is refused with a clear message;
  audit complete.

### Page 10 — Reports, audit & administration  `/reports` · `/admin`

- **Purpose.** Oversight and configuration — the biggest by surface.
- **What it must do (Reports & audit).** Overview (missed, refusals, late/overdue, errors,
  stock discrepancies, CD concerns, PRN usage + overdue reviews, expiring, MAR needing
  attention); filters; report types (administration, missed dose, PRN, CD, stock/expiry,
  incident, staff activity, MAR completeness, shift/round performance); **audit log** (action,
  previous value, new value, reason, staff, role, time, device/session, linked record — **not
  editable/deletable by ordinary users**); exporting (PDF/CSV/print/scheduled, role-scoped);
  manager actions (assign follow-up, review notes, resolve, reopen, link training, record
  improvements).
- **What it must do (Administration & settings).** Organisation settings; user management
  (invite, activate/deactivate — **accounts holding history are deactivated, never deleted**,
  assign locations/roles, reset access, activity); roles & permissions; medication settings
  (round times, late/overdue thresholds, PRN review intervals, reorder levels, expiry warnings,
  missed-dose escalation rules, witness requirements, MAR outcome reasons); integrations (dm+d,
  GP Connect, **NICE**, barcode, notifications, AI config — each with status + last sync);
  staff competency (training, assessment, witness authorisation, expiry, restrictions — **expired
  competency automatically restricts the relevant actions**); security (MFA, sessions, device
  access, password, login history, access logs, retention, consent); templates & reference data
  (handover categories, incident types, missed-dose reasons, task types, notification/report
  templates, organisation terminology).
- **Gaps to close.** **D4** (general audit log), **D6** (competency gating). Surface
  `RoleResolver::unmappedLevels()` here (I13). Do **not** carry `MAR Sheet Delete` (I5).
- **NICE.** Integrations panel manages the NICE Syndication connection + shows last sync, per
  the licence's QA requirements (version/update checks).
- **Done when.** The general audit log records config/permission changes and is tamper-evident;
  admin surfaces work with role scoping; competency expiry restricts actions; states/keyboard
  pass. *(Built last — shares patterns with everything above, none of it frontline safety.)*

---

## 6. What happens next each session

1. Pick the next page in the order.
2. Expand its section-5 spec into a build checklist.
3. Build it to the Definition of Done (section 2).
4. Close the issues it touches, in all three issue records.
5. Update the running log + test log.
6. Show it. Then the next page.
