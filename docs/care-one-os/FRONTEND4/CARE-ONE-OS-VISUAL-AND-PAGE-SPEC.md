# Care One OS — Visual Direction and Page Specification

**Source document, supplied by the owner 2026-08-04.** Recorded here verbatim in
substance so it lives in the repo rather than only in a chat window. This is the
product direction; where anything in `FRONTEND4-DESIGN.md` or
`FRONTEND4-FUNCTIONAL-PLAN.md` disagrees with this file, **this file wins** —
except where a conflict is flagged for a decision (see `FRONTEND4.md`).

---

# Part A — Visual direction

## The direction: Quiet Clinical Luxury

Trustworthy and medically safe, but warmer and more sophisticated than a typical
blue-and-white healthcare dashboard.

It must **not** look: childish; overly colourful; like a generic admin template;
like a hospital system from 15 years ago; empty and excessively white; covered in
pastel status boxes; full of heavy shadows and gradients.

## Core palette

| Purpose | Colour | Hex |
|---|---|---|
| Main background | Warm clinical ivory | `#F6F2E9` |
| Primary surface | Soft porcelain | `#FFFCF7` |
| Secondary surface | Warm mist | `#EEEAE2` |
| Primary brand | Deep Care One navy | `#17243B` |
| Main accent | Clinical teal | `#176B65` |
| Soft accent | Muted eucalyptus | `#7E9B90` |
| Primary text | Deep ink | `#202A35` |
| Secondary text | Slate | `#626D78` |
| Subtle border | Warm stone | `#D9D4CA` |

Overall: warm ivory page background, porcelain cards, deep navy navigation, teal
primary actions, dark ink text, muted eucalyptus used sparingly, fine warm-grey
borders, **very little pure white**.

**Navy** — trust, authority, clinical seriousness, strong contrast. Use for the
desktop sidebar, important headings, selected navigation, logo area, high-level
manager pages. *Do not use navy as the background of every card.*

**Clinical teal** — medical without the standard bright NHS blue. Use for primary
buttons, selected tabs, progress indicators, active navigation details, links,
focus outlines, positive interactive states.

**Warm ivory** — softer, more distinctive, easier on the eyes, less sterile, more
premium. Must stay light enough to preserve strong contrast.

## Status colours

| Status | Colour | Hex |
|---|---|---|
| Given or completed | Forest green | `#26705A` |
| Due now | Clinical blue | `#356A92` |
| Upcoming | Slate blue-grey | `#667789` |
| Late | Burnished amber | `#A46022` |
| Overdue or critical | Deep red | `#A23E45` |
| Refused | Muted plum | `#76516C` |
| Omitted or unavailable | Deep taupe | `#75695C` |
| Witness required | Dark indigo | `#515C86` |
| Information | Blue-grey | `#536E83` |
| Offline | Charcoal | `#535B63` |

**How statuses appear:** small icon, coloured text, thin vertical line, small
outlined badge, tiny status dot, very light background tint only when necessary.

> ● **Overdue** · 22 minutes

**Avoid:** huge red box; bright yellow background; a purple card beside a green
card; white text on several different bright colours; **colour without a word or
icon**.

## Colour intensity system

Every status colour has three versions:

- **Strong** — text, icons, small status indicators, thin borders.
- **Soft** — very light badge background, selected filter, small notification area.
- **Faint** — hover state, table-row emphasis, context-panel background.

Overdue, for example: strong `#A23E45`, soft border ≈ `#D8AEB2`, faint tint ≈
`#F8EBEC`. Visible without turning the page red.

## Sidebar

Desktop sidebar background `#17243B`. Main navigation text `#E8ECF1`; secondary
`#AEB9C6`; selected item `#FFFFFF`; selected indicator `#61A89D`.

Selected navigation uses a slightly lighter navy surface, a **thin teal line on
the left**, a white icon and a white label — **not** a large bright teal
rectangle. The thin teal indicator is a recognisable Care One OS detail.

Section labels small and understated: HOME · MEDICATION ADMINISTRATION · PEOPLE ·
MEDICINES · OVERSIGHT. Not all sections expanded at once; less-used groups
collapse.

## Surfaces

Page canvas warm ivory `#F6F2E9`. Main cards soft porcelain `#FFFCF7`. Secondary
panels warm mist `#EEEAE2`. Important clinical information: a nearly white
surface with a strong heading, thin left indicator, clear icon, minimal tint.
Avoid pure white cards floating on a pure white background.

## Typography

- **Headings — Manrope.** Modern, refined, calm, slightly distinctive.
- **Interface and clinical text — Inter.** Highly readable for medication names,
  doses, tables, forms, MAR records, small labels, mobile.

| Element | Size |
|---|---|
| Main page title | 30–32 px |
| Section title | 20–22 px |
| Card heading | 16–18 px |
| Standard body | 15–16 px |
| Supporting information | 13–14 px |
| Small label | 12 px |
| Medication dose | 16–18 px |
| Critical medication name | 18–20 px |

No 10px text just to fit more information in.

**Text hierarchy** — medication information has a deliberate reading order. The
medicine name is strongest; strength, dose, route and time stay clearly visible
but do not compete equally.

## Cards

Porcelain surface, thin stone border, 12–16px radius, very subtle shadow,
generous but controlled spacing, clear headings, minimal decoration.

```
background: #FFFCF7;
border: 1px solid #D9D4CA;
border-radius: 14px;
box-shadow: 0 2px 8px rgba(23, 36, 59, 0.04);
```

Avoid: 30px radii; thick shadows; every section inside a card; nested cards;
glassmorphism; bright gradients; bulky coloured headers.

## Buttons

- **Primary** — clinical teal `#176B65`, white text. Start Round, Confirm
  Administration, Complete Review, Save Prescription.
- **Secondary** — porcelain/transparent, navy text, stone border. View Details,
  Add Note, Open MAR.
- **Tertiary** — text only, teal or navy, underline on hover. View History.
- **Destructive** — deep red, and only when the destructive action becomes
  relevant. Never bright red buttons sitting on normal pages.

**Medication administration buttons:** primary **Given** uses the teal primary
button, *not* a bright green one. Secondary **Other Outcome** is an outlined navy
button. Tertiary **Scan Medication** is icon + text. After recording, the status
becomes green text: `✓ Given at 09:14 by Precious Offodile`. This stops the
active screen becoming a mixture of green, red, purple and yellow buttons.

## Borders, shadows, icons, spacing

**Borders** `#D9D4CA`, strong divider `#C6C0B5`. Used for card boundary, table
row divider, form field boundary, sticky header separation, selected navigation
indicator. Not around every piece of information.

**Shadows** almost invisible. Stronger only for floating context panels,
dropdowns, modals, mobile bottom sheets.

**Icons** one consistent line family such as Lucide — rounded line endings,
1.75–2px stroke, simple shapes. No cartoon illustrations, no mixing filled and
outlined systems. Accessible labels whenever meaning is not obvious.

**Spacing** eight-point: 4 (tiny internal), 8 (icon to text), 12 (compact
controls), 16 (standard card padding), 24 (between related sections), 32 (between
major sections), 48 (page-level breathing space). Medication Round may be
slightly denser than the Manager Dashboard because it must stay efficient.

## Forms

Clear label above; porcelain or very pale surface; strong readable text; visible
border; clear focus outline; supporting instruction where needed; error
immediately below. Field height 42–44px desktop, 48–52px mobile. **Placeholder
text is never the only label.**

## Tables

For MAR desktop view, stock ledger, CD register, audit trail, reports. Warm
surface, sticky header, very subtle alternating rows where useful, clear row
hover, limited vertical borders, right-aligned numeric quantities, tabular
numerals, expandable row details, responsive mobile alternative. Not every value
in a colourful badge.

## Mobile

Warm ivory background, porcelain medication cards, deep navy header text, teal
primary actions, persistent safety strip, bottom navigation, large touch targets,
sticky administration controls. **Bottom navigation uses a light surface, not a
large navy block**, so the screen stays open and calm. Selected item: teal icon,
teal label, small indicator line.

## Dark mode

Later; it must not delay the core light interface. Direction: deep navy canvas,
slightly lighter slate surfaces, soft off-white text, muted teal accents, reduced
brightness, strong contrast. Clinical statuses tested separately in dark mode.

## The three signature details

1. **Thin teal edge** — selected navigation, active records, important context.
2. **Warm clinical canvas** — the creamier ivory that differentiates Care One OS
   from ordinary white-and-blue healthcare software.
3. **Ink and eucalyptus accents** — deep ink text with muted eucalyptus supporting
   details, for a premium, calm feeling.

Enough to be recognisable without becoming decorative.

---

# Part B — Roles and the Home page

Different roles see different information and actions on the Home page, but
**not a completely different design per role.** Same structure; what changes is
the information shown, the order of sections, the available buttons, the alerts
and responsibilities, and the permission to edit, approve or only view. This
keeps the product consistent and easier to learn.

## The five role groups for version one

1. Support worker
2. Senior support worker / shift lead
3. Care manager
4. Pharmacist
5. System administrator

GPs, family members and external professionals come later as limited-access roles.

## Shared Home structure

1. Organisation and location selector
2. Current shift information
3. Main action button
4. Priority alerts
5. Role-specific overview
6. Today's work
7. Handover information
8. Tasks and messages
9. Navigation

## Support worker Home — build this one first

**Show:** current shift; shift handover; people due medication; start or continue
medication round; needs-attention items; assigned tasks; PRN reviews due;
messages; connection and synchronisation status.

**Hide:** organisation-wide reports; staff-management controls; system
configuration; prescription approval.

## Shift lead Home

Everything the support worker sees, plus: overall round progress; missed and
overdue doses; staff completing each round; unacknowledged handovers; medication
incidents; stock discrepancies; actions requiring approval; reopen-round controls.

## Care manager Home

Safety and compliance overview; medication incidents; missed-dose trends; MAR
gaps; controlled-drug discrepancies; expiring medicines; staff competency; audit
completion; location performance; reports and approvals.

## Pharmacist Home

Prescription queries; medication changes; stock and deliveries; low or
unavailable medicines; reorder requests; medication reconciliation; expiry
warnings; clinical or administration queries.

## System administrator Home

Organisations and locations; user accounts; roles and permissions; integrations;
dm+d synchronisation; GP Connect configuration; system activity; security and
access logs.

## The approach

One reusable Home page with role configuration:

```
Home
 ├── Shared header
 ├── Shared navigation
 ├── Role-specific main action
 ├── Role-specific priority sections
 └── Permission-controlled cards
```

---

# Part C — The 11 pages

1. Login and organisation selection
2. Home dashboard
3. Medication Round
4. People / Clients
5. Client profile
6. MAR sheet and medication history
7. Medication stock
8. Controlled drugs
9. Missed doses and follow-ups
10. Shift handover
11. Reports, audit logs and administration

Some have tabs and pop-up workflows, so dozens of separate pages are not needed.

## Build order

1. Medication Round
2. Client profile
3. MAR sheet
4. Missed doses
5. Stock
6. Controlled drugs
7. Shift handover
8. Reports and audit
9. Administration and settings

> **The stated current task:** build the Medication Round page with a working
> people queue and medicine-recording functionality. Once that basic page works,
> **show it before adding PRN, witnessing, stock deduction or final sign-off** —
> those get added gradually so the page does not become confusing.

---

## Page 3 — Medication Round (`/medication-round`)

1. **Round information** — morning/lunch/evening/night; scheduled time window;
   staff member completing the round; start, continue and finish round.
2. **People queue** — due now; upcoming; completed; needs attention; search and
   filters.
3. **Person summary** — name and photograph; date of birth; location; allergies;
   important medication instructions.
4. **Medication list** — medicine; strength; form; dose; route; scheduled time;
   special instructions; stock remaining.
5. **Recording actions** — given; not given; refused; unavailable; asleep; away;
   clinical instruction to omit; notes; witness where required.
6. **Round progress** — medicines completed; medicines outstanding; people
   completed; warnings and unresolved actions.

Clicking a person may open their medicines on the same page. A separate page for
every small step is not needed.

## Page 3b — Individual Medication Administration (`/medication-round/person/:clientId`)

1. **Person information, always visible at the top** — photograph; full name;
   date of birth; room or location; allergies and reactions; support level;
   important warnings; communication needs.
2. **Round information** — round; scheduled time; current time; late/overdue
   status; staff member administering; round progress.
3. **Medicines due**, each showing — name; strength; form; prescribed dose;
   route; instructions; reason for taking it; stock remaining; last
   administration; controlled-drug warning; time-sensitive warning.
4. **Administration actions**, per medicine — given; refused; asleep; away;
   unavailable; vomited; clinical instruction to omit; other. If not given,
   require a reason, notes where necessary, a re-offer option, an escalation
   decision and a handover update.
5. **Controlled medicines** — select witness; witness confirmation; quantity
   administered; running stock balance; staff signature; witness signature.
6. **PRN medicines**, in a separate section — reason required; symptoms or pain
   score; last dose; minimum interval; maximum daily dose; dose selected;
   effectiveness-review time.
7. **Completion** — recorded medicines; outstanding medicines; warnings; notes;
   complete person; continue to next person.

**Role differences:** support workers record administration; shift leads review,
correct and reopen; managers view audit history and approve escalations;
pharmacists review medication information and respond to queries.

## Page 4 — Client Profile (`/clients/:clientId`)

**Header, always shown:** photograph; full name; preferred name; date of birth;
room or location; NHS number; current status; allergies; main warnings.

**Tabs** (rather than more pages): Overview · Medications · PRN protocols ·
Allergies · MAR history · Care notes · Documents · Audit history.

- **Overview** — contact information; GP; pharmacy; next of kin; diagnoses;
  communication needs; capacity and consent; medication-support level; important
  care instructions; upcoming appointments.
- **Medications** — all active and previous prescriptions with medicine details,
  dose and route, frequency, start/end dates, prescriber, review date, special
  instructions, current stock, and active/paused/stopped status. Authorised users
  can add a medication, record a change, pause or stop, upload prescription
  evidence, request a pharmacy review.
- **PRN protocols** — when it can be offered; symptoms or conditions; minimum
  interval; maximum dose; non-medication support to try first; escalation
  instructions; effectiveness-review requirements.
- **Allergies** — allergen; reaction; severity; recorded date; information
  source; who recorded it.
- **MAR history** — date range; given and missed doses; reasons and notes; staff
  and witness names; export or print.

**Permissions:** support workers mainly view; shift leads update approved care
information; managers approve changes and access audits; pharmacists review
medication and prescription information; administrators manage access but do not
change clinical information.

## Page 5 — MAR Sheet (`/mar`)

The official medication-administration record.

- **Filters** — person; location; date range; medication; round; outcome; staff.
- **Header** — name and photograph; DOB; NHS number; location; GP; pharmacy;
  allergies and reactions; MAR period; important instructions.
- **Medication rows** — name; strength; form; dose; route; frequency; special
  instructions; start and review dates.
- **Administration entries** — date; scheduled time; actual time; outcome; staff;
  witness where required; notes; PRN reason and effectiveness; late/overdue.
- **Statuses** — given; refused; asleep; away; unavailable; vomited; omitted
  following clinical advice; self-administered; not yet recorded. **Never display
  only unexplained codes** — the full meaning must be available.
- **Entry details** — complete outcome details; the original record; corrections;
  who made each change; date and time of each change; escalation and follow-up;
  linked incident; audit history. **Records must never simply disappear when
  corrected.**
- **Actions**, by permission — view details; add an authorised correction; record
  a late entry; add a note; link an incident; export PDF; print; send for manager
  review.
- **Summary** — total scheduled; given; not given; late; outstanding; PRN
  administrations; records requiring review.

## Page 6 — Missed Doses and Follow-ups (`/missed-doses`)

- **Summary** — missed today; refusals; unavailable medicines; overdue
  follow-ups; re-offers due; items awaiting manager review.
- **Filters** — person; location; date; medicine; reason; risk level; follow-up
  status; assigned staff.
- **List item** — person; medicine and dose; scheduled time; reason not given;
  notes; staff; risk level; follow-up status.
- **Reasons** — refused; asleep; away; vomited; medicine unavailable; clinical
  instruction to omit; administration error; other.
- **Follow-up actions** — re-offer; set a re-offer time; contact the shift lead;
  record pharmacy advice; record GP or prescriber advice; create a handover
  action; link or create an incident; mark follow-up complete.
- **Escalation** — the system identifies time-sensitive medicines, repeated
  refusals, high-risk medicines, controlled drugs, multiple missed doses,
  unavailable medicines and possible medication errors. **It must not give
  clinical advice automatically** — it directs staff to the organisation's policy
  and to authorised healthcare professionals.
- **Manager review** — review the original record; check actions taken; add
  comments; confirm escalation; assign further actions; close; reopen.
- **History** — original entry; every follow-up; re-offer attempts; advice
  received; changes made; staff names; dates and times; final resolution.

## Page 7 — Medication Stock (`/medication-stock`)

- **Summary** — below reorder level; out of stock; deliveries awaiting
  confirmation; expiring soon; discrepancies; controlled-drug stock warnings.
- **Filters** — person; location; medicine; stock status; expiry status;
  controlled medicine; supplier or pharmacy.
- **List item** — person; medicine; strength and form; current quantity; reorder
  level; expiry date; batch number; storage location; last movement; status.
- **Movements** — delivery received; dose administered; return to pharmacy;
  disposal; transfer; manual adjustment; damaged or missing stock. Every movement
  records quantity before, quantity changed, quantity after, reason, staff, date
  and time, and a witness when required.
- **Deliveries** — person and medicine; quantity received; pharmacy or supplier;
  batch number; expiry date; prescription details; barcode scanning; staff
  confirmation; second-person check where required.
- **Reordering** — current stock; estimated remaining doses; reorder threshold;
  suggested reorder date; request status; pharmacy response; expected delivery.
- **Discrepancies** — record the actual count; calculate the difference; require
  an explanation; notify the shift lead or manager; link an incident where
  necessary; **prevent silent balance changes**; preserve the original history.
- **Permissions** — support workers view and report concerns; senior staff
  receive deliveries and complete counts; managers approve adjustments and
  investigate; pharmacists review supply and respond.

## Page 8 — Controlled Drugs (`/controlled-drugs`)

- **Summary** — current controlled medicines; administrations awaiting witness;
  discrepancies; cabinet counts due; deliveries awaiting entry; returns or
  disposals awaiting completion.
- **Filters** — person; location; medicine; date; movement type; staff; witness;
  discrepancy status.
- **Register entry** — date and time; person; medicine, strength, form; quantity
  received; quantity administered or removed; running balance; movement reason;
  staff; witness; linked MAR entry.
- **Movements** — delivery; administration; return; disposal; transfer; stock
  correction; person leaving the service; medicine discontinued.
- **Administration workflow** — confirm the person; confirm the prescription;
  select medicine and dose; check the current balance; select an authorised
  witness where required; **staff and witness independently confirm**; record the
  new balance; link the entry to the MAR. Witness requirements follow the care
  setting, the law and the organisation's medication policy.
- **Cabinet count** — expected quantity; actual quantity; difference; staff
  completing; second-person confirmation; notes; escalation where it does not
  match.
- **Discrepancy handling** — do not silently change it; lock the affected entry
  from ordinary editing; notify the manager; record immediate actions; link an
  incident or investigation; record pharmacy or professional advice; keep both
  the original and corrected figures.
- **Audit history** — original entries; corrections; staff and witness
  identities; dates and times; reasons for changes; manager reviews; linked
  incidents.

## Page 9 — Shift Handover (`/shift-handover`)

- **Shift information** — current shift; previous shift; staff handing over;
  staff receiving; location; handover status; date and time.
- **Automatic medication summary** — missed doses; refusals; late or overdue
  medicines; unavailable medicines; PRN administered; PRN reviews still due;
  controlled-drug concerns; stock discrepancies; medication incidents;
  prescription changes.
- **Entries** — person; category; priority; clear description; action required;
  assigned staff; due time; supporting records; status.
- **Categories** — medication; health; behaviour; appointment; incident; stock;
  controlled drugs; pharmacy or GP query; general information.
- **Priorities** — routine; important; urgent. Urgent items stay visible until
  acknowledged and resolved.
- **Acknowledgement** — the receiving staff member reviews every required item,
  confirms understanding, acknowledges, and their name and time are recorded. The
  system shows who has and has not acknowledged.
- **AI support** — AI may draft a summary from approved records, but staff must
  review it, sources must be visible, **AI must not invent information**, AI must
  not mark the handover as acknowledged, and the final record must identify the
  approving staff member.
- **History** — original handover; added notes; completed actions;
  acknowledgements; corrections; staff names; dates and times.

## Page 10 — Reports and Audit (`/reports`)

- **Overview** — missed doses; refusals; late and overdue; medication errors;
  stock discrepancies; controlled-drug concerns; PRN usage and overdue reviews;
  expiring medicines; MAR records requiring attention.
- **Filters** — organisation; location; person; medicine; date range; staff;
  incident type; risk level; status.
- **Reports** — medication administration; missed dose; PRN usage; controlled
  drug; stock and expiry; medication incident; staff activity; MAR completeness;
  shift and round performance.
- **Audit log** — action completed; previous value; new value; reason for change;
  staff; role; date and time; device or session information; linked person or
  medicine. **Audit entries must not be editable or deleted by ordinary users.**
- **Exporting** — PDF; CSV; print; scheduled regular reports; selectable
  content. Exports follow role permissions and confidentiality requirements.
- **Manager actions** — assign follow-up; add review notes; mark resolved;
  reopen; link staff training; record improvements made.
- **Permissions** — support workers see only records relevant to their work;
  shift leads their location and shift; managers authorised locations and
  reports; pharmacists relevant medication and supply reports; administrators
  manage access but do not alter clinical audit records.

## Page 11 — Administration and Settings (`/admin`)

- **Organisation settings** — organisation details; care locations;
  supported-living properties; pharmacies; GP practices; contact information;
  medication policies; reporting requirements.
- **User management** — invite; activate or deactivate; assign locations; assign
  roles; reset access; account status; recent activity. **Accounts holding
  historical medication records are deactivated, never deleted.**
- **Roles and permissions** — support worker; senior support worker; shift lead;
  care manager; pharmacist; GP or prescriber; witness; agency worker; family or
  representative; system administrator. Permissions control what can be viewed,
  what actions can be performed, what can be corrected, what can be approved,
  which locations are accessible, and which reports can be exported.
- **Medication settings** — round times; late and overdue thresholds; PRN review
  intervals; reorder levels; expiry warnings; missed-dose escalation rules;
  witness requirements; MAR outcome reasons.
- **Integrations** — dm+d; GP Connect; pharmacy connections; barcode scanning;
  notifications; email or messaging; AI configuration. Each shows connection
  status and last successful synchronisation.
- **Staff competency** — medication training; competency assessment; witness
  authorisation; expiry date; restrictions; assessor; supporting documents.
  **Expired competency automatically restricts the relevant medication actions.**
- **Security** — multi-factor authentication; session management; device access;
  password settings; login history; access logs; data-retention settings; privacy
  and consent configuration.
- **Templates and reference data** — handover categories; incident types;
  missed-dose reasons; task types; notification templates; report templates;
  organisation terminology.

---

**Stated next planning stage:** map how information moves between these pages
before building their detailed UI.
