# Frontend4 — milestone plan

**One list. Tick a milestone off, move to the next.** No going back and forth.

Sources this is built from: [CARE-ONE-OS-VISUAL-AND-PAGE-SPEC.md](CARE-ONE-OS-VISUAL-AND-PAGE-SPEC.md)
(the product direction), [FRONTEND4-PLAN.md](FRONTEND4-PLAN.md) (isolation rules),
[FRONTEND4-DESIGN.md](FRONTEND4-DESIGN.md) and
[FRONTEND4-FUNCTIONAL-PLAN.md](FRONTEND4-FUNCTIONAL-PLAN.md).
Written 2026-08-04.

---

## How this works

- Each milestone has a **goal**, a **done when** list, and what it **depends on**.
- Nothing is "done" until the *done when* list is fully ticked, tested against real data, and the other three front ends still render unchanged.
- **M2 has a hard stop.** The spec says: build the Round with a working queue and recording, then show it before PRN, witnessing, stock deduction or sign-off are added. That stop is honoured.
- Database work is a **parallel track** (D1–D5). Each D-milestone is pulled in by the page that needs it, not built up front.

---

## Settled 2026-08-04 — the palette question

Your documented visual direction (warm clinical ivory `#F6F2E9` + clinical teal `#176B65`) is **exactly frontend3's palette**. Frontend4 is cool grey `#F7F8FA` + indigo `#3F4FD6`.

**Decision: frontend4 keeps cool/indigo**, as a genuine visual alternative you can put beside frontend3 and choose between. The hue is off-spec **by design**.

### What that does and does not mean

Off-spec *hue* is not off-spec *craft*. Almost everything in your visual specification is about how an interface is built, not which colour it is, and all of that still applies to frontend4:

| Still applies | Deliberately diverges |
|---|---|
| Three intensities per status — strong / soft / faint | The hues themselves: frontend4 uses cool equivalents, not ivory and teal |
| Status shown as dot or icon **plus a word**, never colour alone | Ivory canvas → frontend4's cool near-white canvas |
| Thin edge indicator on selected navigation, not a filled block | Navy sidebar → frontend4's deep ink sidebar |
| Manrope headings + Inter interface text, on your size scale | Teal primary → frontend4's indigo primary |
| Card style: thin border, 14px radius, near-invisible shadow | Muted eucalyptus accent — no frontend4 equivalent |
| Button hierarchy: primary / secondary outlined / tertiary text / destructive only when relevant | |
| Eight-point spacing; Round denser than the manager view | |
| Form rules: label above, never placeholder-as-label, error immediately below, 44px desktop / 48px+ mobile | |
| Table rules: sticky header, tabular numerals, right-aligned quantities, no value-in-a-badge | |
| Lucide line icons, one family, accessible labels | |
| "Given" is the primary button, not a bright green one; recorded state becomes quiet green text | |

So M1 is smaller than a repaint: it is bringing frontend4's existing cool palette up to the standard your specification sets.

---

# Track 1 — The front end

## ✅ M0 — Foundation *(complete)*

**Goal:** a fourth front end that shares the database and changes nothing about the others.

- [x] Own route, root view, Vite entry, page directory, tokens, stylesheet
- [x] Every CSS rule scoped under `.f4-root`; no global stylesheet loaded
- [x] Shell: context bar, left nav, page header, mobile bottom nav
- [x] Component kit: status badges, stats, identity, safety strip, rows, and all six states
- [x] Today dashboard reading real data
- [x] **Verified at runtime:** `/frontend4` serves only `f4-*.css` and `f4-*.js`; `/frontend3`, `/frontend2` and `/medication/medication-round` all still return 200 with zero frontend4 references
- [x] Fixed a real bug on the way — `.f4-root a` was beating `.f4-btn`, rendering indigo text on indigo buttons

---

## ✅ M1 — Cool palette brought up to specification standard *(complete 2026-08-04)*

**Goal:** frontend4's own colours, built to the craft rules in your visual specification — done now, so the Round is not built on a foundation that has to be redone underneath it.

- [x] **Ten statuses** matching your vocabulary (given, due now, upcoming, late, overdue, refused, not available, witness needed, information, offline), each at **three intensities** plus an edge value — strong for text/icons/dots/borders, soft for badge backgrounds, faint for hover and row emphasis. Every `strong` value clears 4.5:1 on white because it is used as text.
- [x] Status presentation rebuilt: **dot + word + thin edge**, never a filled colour block — `● Overdue · 22 minutes`. There is deliberately no way to render the component as colour alone.
- [x] **Manrope headings + Inter interface text**, on your size scale (31 / 21 / 17 / 16 / 14 / 12, and 19 for a critical medicine name). Nothing below 12px.
- [x] Sidebar on deep ink with the **thin accent left indicator**, a lifted surface and brighter text — three signals, not a filled block. Understated uppercase section labels.
- [x] Card style: thin border, 14px radius, `0 1px 2px rgba(17,24,39,.03)`. Nested cards lose their shadow.
- [x] Button hierarchy: indigo primary, outlined secondary, text tertiary, and a destructive variant that exists but is only rendered when the destructive action is relevant.
- [x] One line-icon family at one stroke — **@tabler/icons-react**, already a project dependency, same style as Lucide (24px, 2px stroke, round caps). Wrapped in `F4Icons.jsx` so stroke and size are decided once, and decorative by default with an opt-in label.
- [x] Form rules: `Field` renders a real label above the control (never placeholder-as-label), hint and error tied by `aria-describedby`, error immediately below with `role="alert"`, 44px desktop / 50px mobile.
- [x] Table rules ready for the MAR and registers: `Table` scrolls inside its own keyboard-reachable region, sticky header, tabular numerals, right-aligned quantities.
- [x] Mobile bottom nav stays on a **light** surface with a small indicator line.
- [x] `Medicine` atom built to your reading order — name strongest, then strength/form, then dose · route · due, then instruction. Instruction and indication kept separate, because an indication rendered as a directive is a hazard.
- [x] `OutcomeCode` renders a stored MAR letter as its full meaning — the specification's "never display only unexplained codes".
- [x] Today re-rendered in the new system.

**Verified:** build passes; `/frontend4` returns 200 serving only `f4-*.css` and `f4-*.js`; `/frontend3`, `/frontend2` and `/medication/medication-round` all still 200 with zero frontend4 references.

**Deviation on record:** Tabler rather than Lucide, so no new shared dependency is added to `package.json` — the icons tree-shake into frontend4's bundle and nothing changes for the other three front ends.

---

## ☐ M2 — Medication Round *(the stated current task)*

**Goal:** a working people queue and working medicine recording. Route `/frontend4/medication-round`.

- [ ] **Round information** — round name, scheduled window, who is doing it, start / continue / finish
- [ ] **People queue** — due now, upcoming, completed, needs attention, with search and filters
- [ ] **Person summary** — name, photo, date of birth, location, allergies, important instructions
- [ ] **Medication list** — medicine, strength, form, dose, route, scheduled time, special instructions, stock remaining
- [ ] **Recording** — given, not given, refused, unavailable, asleep, away, clinical instruction to omit, notes
- [ ] Selecting a person opens their medicines **on the same page** (no separate page per step)
- [ ] **Round progress** — medicines completed, medicines outstanding, people completed, warnings
- [ ] Recording goes through the existing `applyRecord()` — mandatory reasons, row locking and refusal messages all inherited
- [ ] Refusals surfaced with the server's own message, never "something went wrong"

### 🛑 HARD STOP — show it before going further

Per the spec: PRN, witnessing, stock deduction and final sign-off are **not** in M2. They come next, gradually, so the page does not become confusing.

---

## ☐ M3 — Round, part two

Added one at a time, each shown before the next:

- [ ] **3a — PRN section**, kept separate: reason required, symptoms or pain score, last dose, minimum interval, maximum daily dose, dose selected, effectiveness-review time
- [ ] **3b — Controlled drugs**: select witness, independent confirmation by both accounts, quantity, running balance, link to the MAR entry
- [ ] **3c — Stock deduction** on administration, with the before/after balance recorded
- [ ] **3d — Person sign-off**: recorded, outstanding, warnings, notes, complete person, continue to next
- [ ] **3e — Round end and reopen**, both attributed; reopening is a recorded act, not an undo

---

## ☐ M4 — Individual Administration screen

Route `/frontend4/medication-round/person/:clientId` — where the safe sequence lives.

- [ ] Person information **always visible at the top**: photo, name, DOB, location, allergies and reactions, support level, warnings, communication needs
- [ ] Round context: round, scheduled time, current time, late/overdue, who is administering, progress
- [ ] Each medicine shows reason for taking it, last administration, stock remaining, CD warning, time-sensitive warning
- [ ] Not-given path requires reason, notes where necessary, **re-offer option, escalation decision, handover update**
- [ ] Safe sequence enforced: identity → medicine → dose/route/time → outcome → confirm
- [ ] Role differences enforced server-side, not by hiding buttons

**Needs:** D1 (structured allergies) to do allergies properly rather than as a text string.

---

## ☐ M5 — Client Profile → ☐ M6 — MAR Sheet → ☐ M7 — Missed Doses → ☐ M8 — Stock → ☐ M9 — Controlled Drugs → ☐ M10 — Shift Handover → ☐ M11 — Reports and Audit → ☐ M12 — Administration and Settings

Your documented build order, unchanged. Each expands into its own checklist when it is reached — writing all twelve out now is how a plan goes stale.

Dependencies worth knowing in advance:
- **M5** needs D1 (allergies) and D2 (dm+d codes)
- **M7** needs D3 (follow-up workflow)
- **M10** needs D5 (itemised handover entries)
- **M11** needs D4 (general audit log)
- **M12** needs the role mapping decision and competency gating

---

# Track 2 — Database

## Verdict: there is nothing *wrong* with the database

I checked the live schema rather than assuming. 249 tables; the medication core is in better shape than expected, and **it is strongest exactly where getting it wrong would be dangerous**:

| What the spec demands | What the database already does |
|---|---|
| "Records must never simply disappear when corrected" | `mar_administrations` has `is_current`, `supersedes_id`, `superseded_at`, `amendment_reason` — a proper append-only correction chain |
| "Prevent silent balance changes" | `medication_stock_transactions` records `balance_before`, `balance_after`, quantity, reason, disposal method, witness and who performed it |
| Witness "independently confirms" | `cd_witness_confirmations` holds a real second account — `witness_user_id`, `confirmed_by_user_id`, `confirmed_at` — plus `override_reason`, so an override is recorded rather than silent |
| PRN maximum and minimum interval | `prn_max_daily`, `prn_min_interval_hours` on `mar_sheets`, enforced inside a row lock |
| Handover acknowledgement | `shift_handovers.acknowledged_at` / `acknowledged_by_user_id` / `edit_log` |
| PRN effectiveness review | `medication_dose_reviews` with `clinical_action`, `status`, `reviewed_by_user_id` |
| Barcode scanning | `mar_sheets.barcode` |

**None of the gaps below block M1, M2 or M3.** They are places where your specification goes beyond what has been built, not places where what exists is broken.

## ☐ D1 — Structured allergies *(highest value)*

Today allergies are **free text**: `service_user.allergies` is a comma-separated string, `mar_sheets.allergies_warnings` is prose. Your spec wants allergen, reaction, severity, recorded date, source, and who recorded it.

Why it matters most: an allergy stored as a sentence can be *displayed*, but it cannot be *checked*. You cannot warn someone that the medicine they are about to give conflicts with a recorded allergy while the allergy is a string. Needed for M4 and M5.

## ☐ D2 — Coded medicines (dm+d / SNOMED)

`mar_sheets.medication_name` is free text. There is no dm+d or SNOMED column, and no dm+d table.

Consequences: no reliable interaction or allergy checking, no medication reconciliation, no GP Connect later, and two spellings of the same medicine are two different medicines. Your admin page lists dm+d synchronisation, so this is already in your plan — it just needs a column and a catalogue before the features that depend on it.

## ☐ D3 — Missed-dose follow-up workflow

Your page 6 wants re-offer, re-offer time, recorded pharmacy/GP advice, escalation, assignment, follow-up status, manager review, close and reopen, and a full history of all of it. `medication_dose_reviews` is close in spirit but shaped for dose review, not for a follow-up case with a lifecycle. Needed for M7.

## ☐ D4 — General audit log

Auditing today is **per table** — the MAR supersedes chain, `shift_handovers.edit_log`, stock transactions. That covers the clinical record well but does not answer "who changed this prescription, this permission, this setting, and what was the value before". Your page 10 specifies action / previous value / new value / reason / staff / role / time / device / linked record, non-editable by ordinary users. Needed for M11.

## ☐ D5 — Itemised handover entries

`shift_handovers` stores `general_notes`, `client_updates`, `medication_concerns` and `priority_alerts` as text. Your page 9 wants entries with person, category, priority, action required, assigned staff, due time and status. **You cannot assign or tick off an item that lives inside a text blob.** Needed for M10.

## ☐ D6 — Competency gating and the role model

`staff_training` and `training` exist, but not competency assessment, witness authorisation, restrictions or expiry — and your spec says **expired competency automatically restricts the relevant medication actions**, which is behaviour, not just a table. Separately, the current two-tier model (`user_type` plus per-home `access_levels` / `access_right`) has to be mapped onto your ten role groups. That is a decision, not necessarily new tables. Needed for M12.

## Housekeeping, low priority

`access_right@nov08old` and `24_oct_access_right` are dated duplicate tables sitting in a 249-table schema. Not harmful, but worth a decision on whether anything still reads them before the schema grows further.

---

## The one rule that governs every schema change

Any change to a shared table affects **all four front ends**. Nothing in Track 2 gets built to suit a frontend4 screen alone, and no migration reshapes a table that frontend1, 2 or 3 already reads without checking them first. The clinical record stays append-only.
