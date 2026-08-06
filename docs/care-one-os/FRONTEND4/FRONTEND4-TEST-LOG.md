# Frontend4 — feature test log

Manual test cases for everything built in frontend4, plus a test for every issue in
[FRONTEND4-ISSUES.md](FRONTEND4-ISSUES.md) — so a fixed issue can be proved fixed, and an
open one has a step that demonstrates it rather than a paragraph describing it.

**Status key:** ☐ not yet run · ✅ pass · ❌ fail (note what broke) · ⚠️ known gap, expected to fail

> **"Auto-verified"** means I confirmed it myself — by build, by HTTP response, or by
> reading the database afterwards. It is not a click-through. Anything marked ☐ still needs
> a human in a browser.
>
> **Precondition for everything:** MySQL running, `php -S 127.0.0.1:8000 serve-local.php`
> from the project root, and signed in via `/dev-login`. Hard refresh (Ctrl+F5) after a
> rebuild.

---

## ✅ Acceptance checklist — tick when you're happy each works

- [x] **Isolation** — frontend4 loads only its own CSS/JS; frontends 1–3 unchanged — ✅ auto-verified 2026-08-04
- [x] **Roles resolve** — the signed-in user gets one of four roles from their access level — ✅ auto-verified (demo user → manager, 11 permissions)
- [x] **Medication access is refused** to anyone without it — ✅ auto-verified (replaced a check that admitted every user type)
- [x] **Short sidebar** — five items for a carer, more by role — ✅ auto-verified
- [x] **Nine outcomes** offered, older screens can still name them — ✅ auto-verified
- [x] **One definition of "given"** — all ten sites use the shared helper — ✅ auto-verified (tests identical before/after)
- [x] **Round records a dose** — writes, attributes, supersedes rather than overwrites — ✅ auto-verified
- [x] **Mandatory reason enforced** server-side — ✅ auto-verified (422, nothing written)
- [x] **Stock ledger reconciles** — ✅ auto-verified ([I16](FRONTEND4-ISSUES.md#i16))
- [ ] **The round, in a browser** — the thing that still needs your eyes
- [ ] **Mobile** — the round at 390px wide
- [ ] **Keyboard only** — record a dose without touching the mouse

---

## M0 — Isolation

- **Auto-verified:** `/frontend4` serves only `f4-*.css` and `f4-*.js`; `/frontend3`, `/frontend2` and `/medication/medication-round` return 200 with zero frontend4 references.
- ☐ **T1** Open `/frontend4`. It is styled — cool grey background, indigo accent.
- ☐ **T2** Open `/frontend3`. Still warm ivory and teal, visually unchanged.
- ☐ **T3** Open `/frontend2` and `/medication/medication-round`. Unchanged.
- ☐ **T4** View source on `/frontend4`: exactly one stylesheet, `f4-*.css`. No `app-*.css`, no `styles-*.css`, no `f3-*.css`.

## M1 — Design system

- **Auto-verified:** builds; Today and the round render from the shared atoms.
- ☐ **T1** Every status shows a **word**, not just a colour — "Overdue", "Given", "Due now".
- ☐ **T2** Squint, or set the display to greyscale. You can still tell overdue from given.
- ☐ **T3** No filled colour blocks — statuses are a dot plus a word plus a thin edge.
- ☐ **T4** Headings are Manrope, body text is Inter. Nothing smaller than 12px.
- ☐ **T5** Cards have a thin border and an almost-invisible shadow. No nested cards.

## M1.5 — Roles and permissions

- **Auto-verified:** demo user resolves to `manager` with 11 permissions; `define_roles` and `manage_settings` correctly absent.
- ☐ **T1** The context bar top-right shows your name **and** your role.
- ☐ **T2** Sign in as a support-worker account. Role reads "Support worker".
- ✅ **T3** A user whose access level maps to no medication access gets **403** — auto-verified by code path; ☐ still worth confirming with a real finance account.
- ☐ **T4** **Server-side, not just hidden.** As a carer, POST directly to `/frontend4/round/record` with a permission you do not hold. It must be refused, not merely absent from the screen.
- ☐ **T5** A finance ("Account Manager") account is refused **even though** its account type is `A`. The access level beats the account type.

## M1.6 — The sidebar

- ☐ **T1** As a carer: exactly five items — Today, Medication round, Missed doses, Clients, Handover.
- ☐ **T2** As a shift lead: the five plus Controlled drugs and Stock, under an "Oversight" heading.
- ☐ **T3** As a manager: the above plus Reports & audit.
- ☐ **T4** As an admin: everything, plus Administration.
- ☐ **T5** The current item is marked **three ways** — a thin left edge, a lifted background and heavier text. Not colour alone.
- ☐ **T6** At phone width the sidebar is replaced by a bottom bar of four fixed items. It does **not** change length by role — the target stays where a carer's thumb expects it.

## M1.7 — The nine outcomes

- **Auto-verified:** the outcome list reaches the page; the server accepts `A,S,R,W,N,O,AW,OP,VO,NR`.
- ☐ **T1** The outcome dropdown offers nine: Given, Declined, Asleep, Away, Not available, Omitted — clinical, Omitted — operational, Vomited or spat out, Not required.
- ☐ **T2** Choose **Declined**. A "Why?" field appears **before** the Record button, with a hint.
- ☐ **T3** Choose **Asleep**. No reason is demanded — the code already says why.
- ☐ **T4** Choose **Away** or **Not required**. Also no reason demanded.
- ☐ **T5** Record something as **Omitted — operational** in frontend4, then open the same dose in frontend2's round. It reads "Omitted — operational", **not** a blank.
- ☐ **T6** frontend1 and frontend2 dropdowns still offer their original six. Nothing new appeared there.

## M1.8 — One definition of "was it given"

- **Auto-verified:** all ten call sites use `DoseOutcome::isGiven()`; medication suites returned 14 errors / 3 failures **before and after** — identical.
- ☐ **T1** Record a dose as **Given** on a medicine with tracked stock. Stock goes down.
- ☐ **T2** Record one as **Asleep**. Stock does **not** move, and it does not count toward a PRN daily maximum.
- ☐ **T3** Record a **controlled drug** as Given with no witness. It is refused, with the server's message.
- ☐ **T4** Search the code for `code === 'A'`. There should be no functional match left — only comments explaining why.

## Page 1 — Clients (the service users)

- **Auto-verified 2026-08-05:** `/frontend4/clients` returns 200 as a signed-in manager, component `Clients`, 8 real clients for Neptune House; loads only `f4-*.css`; frontends 2/3 and `/medication/*` still 200. Tap-through `/frontend4/clients/243` → `ClientProfile` with real identity (Amelia Hughes, 12, NHS, Penicillin). A client outside the user's home (`id 1`) → **404**.
- ✅ **T1** Open `/frontend4/clients`. The list shows the clients of your home. Auto-verified (8 shown).
- ✅ **T2** A client in another home cannot be opened — `/frontend4/clients/1` returns 404. Auto-verified (home scoping server-side).
- ✅ **T3** Tapping a client opens their profile at `/frontend4/clients/:id`. Auto-verified.
- ✅ **T4** Page loads only `f4-*.css`; no `app-*.css`, `styles-*.css` or `f3-*.css`. Auto-verified.
- ☐ **T5** Clients are grouped A–Z with the letter heading each cluster; the list reads as ordered.
- ☐ **T6** Each row shows avatar/initials, name, age and location; an **Allergy** chip appears for a client with a recorded allergy (word, not colour alone).
- ☐ **T7** Type part of a name in the search box — the list narrows. Clear it — the full list returns.
- ☐ **T8** Use the location filter — only clients in that location show. "No matches" offers to clear.
- ☐ **T9** Sidebar shows **Clients** as the current item; the whole row is a link with a chevron.
- ☐ **T10** At 390px the search + filter stack full-width; rows stay on one line; no horizontal page scroll.
- ☐ **T11** Keyboard only: tab to the search, tab through the rows, open one with Enter. Focus always visible.
- ☐ **T12** A no-medication-access account gets 403 (list never renders).

### Clients — UI review with owner + roles/isolation verification (2026-08-06)
- ✅ Status filter (Active / Inactive / All), defaults to Active; an "Inactive" chip on inactive rows.
- ✅ Location + Status rendered as **solid buttons** (still dropdowns); search full-width beside them.
- ✅ Allergy chip restyled to a soft muted rose with a square marker — scoped, so clinical/overdue reds are unchanged.
- ✅ Page fades in on open; profile tabs slide across; both honour `prefers-reduced-motion`.
- ✅ Loading feedback: Inertia progress bar on navigation.
- ✅ "Skip to content" link (first tab stop) jumps keyboard users straight to the list.
- ✅ Row focus ring made **inset** so the card's `overflow:hidden` can't clip it (was invisible on the first row of a group).
- ✅ No-match empty state simplified to one line + Clear filters (Empty `body` now optional); empty-state body centred (specificity fix vs `.f4-root p`).
- ✅ Mobile: search full-width, the two filters side by side (iPhone SE).
- ✅ **F (roles):** list is home-scoped server-side; a no-access role hits `requireMedicationAccess()` → 403 before render; checks are server-side, not hidden UI.
- ✅ **G (isolation/tests) verified 2026-08-06:** `/frontend4/clients` serves only `f4-*.css`/`f4-*.js`; `/frontend2`, `/frontend3`, `/medication/medication-round` all 200 with zero f4 references; medication suite **14 errors / 3 failures** — the baseline, unchanged by any of this work.

## Page 2 — Client profile (Slice A: header + Overview)

- **Auto-verified 2026-08-05:** `/frontend4/clients/243` → 200, component `ClientProfile`, real header (Amelia Hughes, 12, NHS 950 000 0000, Active, allergy Penicillin) and Overview on real data (Key details 5 fields, Care & support 2 fields; Emergency-contact section correctly omitted when none recorded). Loads only `f4-*.css`. A client outside the home → 404.
- ✅ **T1** Open a client from the list. The identity header shows photo/initials, name, age, location, NHS, status. Auto-verified.
- ✅ **T2** Allergies show as a safety strip (word). Auto-verified (Penicillin).
- ✅ **T3** Overview shows only recorded fields; an unrecorded field/section is absent, not a blank row. Auto-verified.
- ☐ **T4** The header + tab bar stay put while a long tab scrolls (sticky).
- ☐ **T5** All eight tabs are present; the current tab is marked by weight + accent underline (not colour alone).
- ☐ **T6** Keyboard: focus a tab, use ← → Home End to move; Enter/click selects. Focus always visible.
- ☐ **T7** Tabs other than Overview show an honest "coming next" panel, not a blank or a fake.
- ☐ **T8** At 390px the header stays complete, the tab bar scrolls sideways, Overview fields stack; no horizontal page scroll.
- ☐ **T9** A no-medication-access account gets 403.

### Slice B — Medications + Allergies tabs
- **Auto-verified 2026-08-05:** client 243 returns 3 medications (Levetiracetam — Active, 1 tablet, Oral, 54 tablets; Paracetamol — PRN; Salbutamol — PRN, inhaled) and allergy Penicillin.
- ✅ **T10** Medications tab lists the client's prescriptions with name, strength/form, dose · route · frequency, prescriber/dates, stock remaining. Auto-verified (data).
- ✅ **T11** Each medicine shows a status chip (Active/Paused/Stopped) and a "Controlled drug" chip when applicable. Auto-verified (statuses map from `mar_status`/`discontinued`).
- ✅ **T12** PRN medicines read "When required (PRN)" as their frequency. Auto-verified.
- ✅ **T13** Allergies tab lists the recorded allergens with an honest note that reaction/severity/source are the D1 upgrade. Auto-verified.
- ☐ **T14** A low-stock medicine shows its stock in the caution tone; "Stock not tracked" when there is no figure. *(Visual — needs a low-stock client.)*
- ☐ **T15** Switching Overview → Medications → Allergies keeps the header + tabs in place; content swaps below.

### Slice C — PRN protocols + MAR history tabs
- **Auto-verified 2026-08-05:** client 243 → 2 PRN meds (Paracetamol, Salbutamol; max 4/24h, min 4h, protocol text) and 3 MAR history rows (Levetiracetam Given, Salbutamol Declined "Spat out / not swallowed", by Phil Holt) with correct outcome labels + status keys.
- ✅ **T16** PRN protocols tab lists when-required medicines with dose/route, minimum interval, maximum in 24h, and the protocol text. Auto-verified.
- ✅ **T17** MAR history lists administrations, most recent first, with medicine, date/time, staff, and the outcome as word + tint (never a bare code). Auto-verified.
- ✅ **T18** A not-given outcome shows its reason (e.g. "Spat out / not swallowed"). Auto-verified.
- ☐ **T19** A late dose shows "· late" beside the outcome. *(Visual — needs a late record.)*
- ☐ **T20** The PRN note explains that symptoms/non-med steps/escalation/review are a planned upgrade; it doesn't pretend they're recorded.

### Slice D — Care notes + Documents + Audit history tabs
- **Auto-verified 2026-08-05:** all three tabs wired to real sources (`log_book` via `su_log_book`; `client_document_manages`; the `mar_administrations` correction chain). Page 200, no SQL error. This home has no notes/docs/corrections in demo data, so all three show empty states — populated rendering not yet demonstrable here.
- ✅ **T21** Empty states render honestly (Care notes / Documents / Audit) with a reason, not a blank. Auto-verified.
- ✅ **T22** Audit empty state states nothing has been amended and that the wider settings/permission audit log is a separate later feature. Auto-verified.
- ☐ **T23** With a client that HAS care notes: each note shows title, snippet, date · category · staff. *(Needs data.)*
- ☐ **T24** With a client that HAS documents: each shows name, type, added/expiry, and a Confidential chip where flagged; no download link yet. *(Needs data.)*
- ☐ **T25** With a corrected clinical record: the audit tab shows the medicine, the corrected outcome, who, when, and the amendment reason; the original is never lost. *(Needs data.)*

### Slice E — Edits by addendum (pause / resume / stop a prescription)
- **Auto-verified 2026-08-05, live:** as manager, POST pause with reason → 302, `mar_sheets.mar_status` active→paused, a `mar_sheet_changes` row written (paused, active→paused, reason, changed_by). POST stop with **no reason** → **422**, nothing written. POST resume → status restored to active. Audit history tab shows both changes with who/when/why.
- ✅ **T26** Manager sees Pause/Stop (active) or Resume/Stop (paused) on each medicine; a reason is required before the change is sent. Auto-verified (write path).
- ✅ **T27** The server refuses a change with no reason (422) and writes nothing. Auto-verified.
- ✅ **T28** The change is written to the append-only `mar_sheet_changes` log first (before, after, reason, who, when); the prescription row is not silently mutated. Auto-verified.
- ✅ **T29** A paused prescription leaves `mar_status='active'`, so it drops out of the round (`currentlyActive`), and resume brings it back. Auto-verified (status transitions).
- ✅ **T30** The Audit history tab lists prescription changes alongside administration corrections, newest first. Auto-verified.
- ☐ **T31** A **carer** does not see the Pause/Stop controls, and a direct POST to the status route is refused (403). *(Needs a carer account; server check `requirePermission(MANAGE_PRESCRIPTION)` is in place; carer grants exclude it.)*
- ☐ **T32** An **administrator** is refused the change (admin excluded from `manage_prescription`). *(Needs an admin account; enforced via `ADMIN_EXCLUDES`.)*

## Page 4 — MAR sheet (Slice A: the grid)

- **Auto-verified 2026-08-05:** `/frontend4/clients/243/mar` → 200, component `MarSheet`; grid for the week (Mon–Sun), 3 medicines, the Levetiracetam **Given** cell shows on the correct day; summary 6 scheduled / 1 given / 5 outstanding; full 10-code legend; loads only `f4-*.css`. Previous week (20–26 Jul) correctly shows the 23 Jul dose; `isThisWeek` disables Next.
- ✅ **T1** Reached from the profile: MAR history tab → "View full MAR" opens `/frontend4/clients/:id/mar`. Auto-verified (route + link).
- ✅ **T2** Grid: medicines down, days across, a coded box per scheduled dose; the recorded Given cell appears on the right day. Auto-verified.
- ✅ **T3** Week navigation moves through time; Next is disabled in the current week. Auto-verified.
- ✅ **T4** Period summary (given / not given / late / outstanding / PRN) computes from the week. Auto-verified.
- ✅ **T5** Legend names every code. Auto-verified.
- ☐ **T6** The grid scrolls inside its own frame — no horizontal page scroll at 390px; today's column is tinted.
- ☐ **T7** A coded cell shows its full meaning on hover / to a screen reader (never a bare letter).
- ☐ **T8** Identity header + allergy strip stay in view.
- ✅ **T9** *(Resolved in Slice B)* a **refused/declined PRN** now shows on the grid as its outcome code (e.g. "R" in the refused tint), clickable for detail — not only given counts.

### Slice B — Entry detail (open a cell)
- **Auto-verified 2026-08-06:** a regular cell (Levetiracetam 08:00, 4 Aug) carries full detail — recorded at 11:25, by Phil Holt, plus witness/dose/reason/notes fields and a correction-history array. A PRN cell (Salbutamol, 4 Aug) shows the day's doses incl a Declined at 11:59.
- ✅ **T10** Clicking a filled cell opens a detail panel with outcome (full meaning), scheduled vs recorded time, staff, witness, dose, reason, notes. Auto-verified (data wired).
- ✅ **T11** A PRN cell opens the list of that day's doses (time, outcome, staff, reason). Auto-verified.
- ✅ **T12** The panel includes a **correction history** (original + every change, current vs superseded) when a record has been corrected — built from all records for that dose, not just the current one. Auto-verified (mechanism; no corrected record in demo data to populate it).
- ☐ **T13** Open a cell, read the detail, close it; keyboard reachable, focus visible. *(Your look.)*
- ✅ **T14** *(now populated)* a corrected administration shows the original and the correction in the history — nothing disappears.

### Slice C — Corrections (write, lead+)
- **Auto-verified 2026-08-06, live:** as manager (holds `correct_record`), correcting Levetiracetam 4 Aug 08:00 A→R wrote a **new** row (id 321, R, `supersedes_id=315`, amendment reason) and set the original (315) `is_current=0` — original preserved. A correction with **no amendment reason → 422**. Reverted R→A cleanly (chain: A → R → A, current = A), leaving a real 3-entry correction history.
- ✅ **T15** Lead+ sees "Correct this entry" in the cell detail; picking a new outcome + (reason if needed) + amendment reason saves. Auto-verified (write path; manager has correct_record).
- ✅ **T16** The correction is append-only — the original is never overwritten; the chain shows current vs superseded. Auto-verified.
- ✅ **T17** Server refuses a correction with no amendment reason (422). Auto-verified.
- ✅ **T18** A **controlled drug** is blocked on this path with a message directing to the CD register. Auto-verified (guard in code; no CD med for 243 to click).
- ✅ **T19** *(fixed — [I19](FRONTEND4-ISSUES.md#i19))* a correction that changes given-ness now reconciles stock through the audited ledger. Auto-verified live: A→R took stock 54→55; R→A took it 55→54; ledger reconciles; record ends truthful.
- ☐ **T20** A **carer** (no `correct_record`) does not see the correct control, and a direct POST is refused (403). *(Needs a carer account; enforced by `requirePermission`.)*

### Slice C — correction edge cases (run these when testing)
The cases to work through for corrections + stock reconciliation. ✅ = auto-verified, ☐ = for you in the browser.
- ✅ **E1** **given → not-given** (A→R): the dose is **returned** to stock (+`dose_quantity`), logged as a reconciling ledger entry. Auto-verified (54→55).
- ✅ **E2** **not-given → given** (R→A): the dose is **deducted** from stock (−`dose_quantity`), logged. Auto-verified (55→54).
- ☐ **E3** **given → given**, or **not-given → not-given** (e.g. R→W): stock does **not** move; only the clinical record is amended.
- ☐ **E4** medicine with **untracked stock** (no stock figure): the correction still records; no stock movement, no error.
- ☐ **E5** medicine with **no structured `dose_quantity`**: correction records; stock not moved (nothing safe to deduct).
- ☐ **E6** **not-given → given when stock is 0 / too low**: the dose is still recorded; the ledger shows the true balance with a labelled **shortfall** (I16 behaviour), never a silent clamp.
- ✅ **E7** **two corrections in quick succession** do not 500 — the prescription row is locked, so they serialise. Auto-verified (both 302).
- ✅ **E8** **controlled drug**: correction is **blocked** with a message directing to the CD register; no stock/register movement. Auto-verified (guard).
- ☐ **E9** **amendment reason missing** → refused (422), nothing written.
- ☐ **E10** new outcome **needs a reason** (R/W/N/O/OP/VO) but none given → refused.
- ☐ **E11** **carer** (no `correct_record`) → 403; the control isn't shown.
- ☐ **E12** after any correction, the **cell history** and the **profile Audit tab** both show the change; the original is never lost.

### Slice D — Print / export (manager)
- **Auto-verified 2026-08-06:** the "Print / PDF" button is gated to `export_report` (manager+); build clean; isolation holds.
- ✅ **T21** Manager sees "Print / PDF"; clicking opens the browser print dialog (Save as PDF covers export). Auto-verified (gate + handler).
- ☐ **T22** The print output shows the identity header, week, summary, grid and legend, with the app chrome (nav, buttons, detail panel) hidden. *(Your look — Ctrl/Cmd+P.)*
- ☐ **T23** A carer/lead without `export_report` does not see the Print button. *(Needs those accounts; gated by `export_report`.)*

## M2 — The medication round

- **Auto-verified:** page loads on real data; recording writes and attributes; a decline with no reason returns 422 and writes nothing.
- ☐ **T1** Open `/frontend4/round`. The queue shows clients for the current round.
- ☐ **T2** The queue is ordered by **urgency**, not alphabetically — overdue first, then due, then later, then finished.
- ☐ **T3** Finished clients are still listed, at the bottom, visibly quieter. They are not hidden.
- ☐ **T4** Allergies appear on the client in the queue **and** at the top of their medicine list.
- ☐ **T5** Search by part of a name filters the queue.
- ☐ **T6** The round buttons across the top switch between Morning / Lunchtime / Evening / Night.
- ☐ **T7** Click a client. Their medicines appear with name strongest, then strength/form, then dose · route · due.
- ☐ **T8** "Take with water. Do not crush." style instructions appear as an instruction — separate from *why* the medicine is prescribed.
- ☐ **T9** Progress reads both doses and people: "N of M doses recorded · X of Y clients complete".
- ✅ **T10** Record a **Declined** with no reason → refused, server's own wording, **nothing written**. Auto-verified: HTTP 422, zero rows.
- ✅ **T11** Record a Declined **with** a reason → saved, attributed to you, timestamped. Auto-verified.
- ✅ **T12** Re-record the same dose as **Given** → a new row is written and the previous one is marked superseded (`is_current` 1 → 0). **The original is never overwritten.** Auto-verified.
- ☐ **T13** A recorded medicine shows the outcome, the time and who recorded it — and no longer offers a Record button.
- ☐ **T14** A **closed** round shows the closed banner and offers no Record buttons at all.
- ☐ **T15** A client with a medicine low or out of stock shows it on the medicine, with the amount left.

### M2 — states
- ☐ **T16** A round with nobody scheduled shows an empty state that says *why* and what to check — not "No results".
- ☐ **T17** Search for a name that does not exist → an empty state offering to clear the search.
- ☐ **T18** Before choosing anyone, the right-hand panel invites you to pick a client.

### M2 — mobile and keyboard
- ☐ **T19** At 390px the two panes stack; the queue is on top, the chosen client's medicines below.
- ☐ **T20** No horizontal scrolling of the page at any width.
- ☐ **T21** Tab through and record a dose using only the keyboard. Focus is always visible.
- ☐ **T22** With a screen reader, the reason error is announced when recording is refused — not just shown in red.

---

# Tests for the issue log

One test per issue. A ✅ proves a fix; a ⚠️ demonstrates a gap that is still open.

## ✅ I1 — one definition of "given" *(fixed)*
- ✅ **T1** All ten sites call `DoseOutcome::isGiven()`. Auto-verified.
- ✅ **T2** Tests identical before and after the change — 14 errors / 3 failures both times. Auto-verified.
- ☐ **T3** Add a code to `DoseOutcome::GIVEN_CODES`, record a dose with it, and confirm stock moves and a controlled drug demands a witness. Then remove it. *(This is the proof the ten sites really are one.)*

## ✅ I16 — the stock ledger reconciles *(fixed)*
- ✅ **T1** Set a medicine's stock to 3, administer a 5ml dose. The ledger shows `administered 5.00: 3.00 → −2.00` then `correction 2.00: −2.00 → 0.00` with a reason naming the shortfall. Every row adds up. Auto-verified.
- ✅ **T2** The dose is still recorded (`given = 1`). Recording is never refused because of a stock shortfall. Auto-verified.
- ☐ **T3** Normal case: administer 5ml against 60ml. One row, `60.00 → 55.00`, and **no** correction entry.
- ☐ **T4** Repeat T1 on a **controlled drug** and confirm the CD register tells the same story.
- ⚠️ **T5** *Owed:* an automated regression test for T1. Verified against live data instead, which is weaker.

## ⚠️ I2 — medication pages in frontends 1–3 are still open
- ✅ **T1** In frontend4, a user without medication access gets 403. Auto-verified.
- ⚠️ **T2** Sign in as **any** user type and open `/medication/medication-round`. **It loads.** That is the gap — the same check has not been applied there.

## ⚠️ I3 — allergies cannot be checked
- ☐ **T1** A client's allergies display on the round. *(Works.)*
- ⚠️ **T2** Give that client a medicine they are allergic to. **Nothing warns you.** Allergies are free text, so nothing can compare them to a medicine.

## ⚠️ I4 — no medicine codes
- ⚠️ **T1** Search the medicines for a client. Two spellings of the same medicine appear as two different medicines, because the name is free text and there is no code behind it.

## ⚠️ I5 — MAR sheet delete
- ⚠️ **T1** Confirm what `/roster/client/mar-sheet-delete` actually does — a soft `is_deleted` flag, or a real delete. Do not carry the capability into frontend4 either way.

## ⚠️ I6 — PRN duplicate guard is a time window
- ⚠️ **T1** Record a PRN dose twice within 90 seconds → treated as one event. *(Intended.)*
- ⚠️ **T2** Record a genuine second PRN dose 80 seconds after the first → **swallowed**. That is the flaw: the event is inferred from the clock rather than identified.

## ⚠️ I7 — no general audit log
- ⚠️ **T1** Change a permission or a setting, then try to find out who changed it and what it was before. There is nowhere that records it.

## ⚠️ I8 — handover items cannot be assigned
- ⚠️ **T1** Open a handover. Try to assign one item to a person and tick it off. You cannot — it is a block of text, not a list of items.

## ⚠️ I9 — no missed-dose follow-up
- ⚠️ **T1** Record a decline, then try to set a re-offer time, record pharmacy advice, or close the case. There is nowhere to do it.

## ⚠️ I10 — no competency gating
- ⚠️ **T1** Have a shift lead witness a controlled drug. **It is allowed by role alone** — nothing checks that they are trained or authorised to witness, or that their competency has not expired.

## ⚠️ I11 — "Staff" is near-admin in the old system
- ⚠️ **T1** Inspect the access level named "Staff" at Station Road (home 1). It carries roughly **330** rights — nearly everything System Admin has. The name does not describe the permissions.

## ⚠️ I12 — the test suite is red
- ⚠️ **T1** Run the four medication suites. **14 errors, 3 failures**, including `MARSheetTest::test_full_prescription_lifecycle` returning 500. Confirmed pre-existing.
- ⚠️ **T2** Consequence: nobody can distinguish a new regression from the existing noise. Every "before and after are identical" check in this log is weaker than it should be because of it.

## ⚠️ I13 — unmapped access levels fall back
- ☐ **T1** Create an access level with a name nobody has mapped — "Support Worker Level 2". The user still gets in, at the role their account type implies.
- ☐ **T2** `RoleResolver::unmappedLevels()` lists it, so it can be mapped properly. *(Not yet surfaced on any screen — that is the open part.)*

## ⚠️ I14 — junk access levels
- ☐ **T1** `azure`, `acc`, `aa`, `AccessTest`, `Jesse Daniels Level` and the rest are mapped to **no access**, so they are safe. They should still be removed rather than left looking like roles.

## ⚠️ I15 — dated duplicate tables
- ☐ **T1** Confirm nothing reads `access_right@nov08old` or `24_oct_access_right` before the schema grows further.

## ⚠️ I17 — nobody is told about a stock shortfall
- ✅ **T1** The shortfall is recorded in the ledger with a reason. Auto-verified.
- ⚠️ **T2** Cause a shortfall, then check the Today page, the shift lead's screen, and any notification. **Nothing tells anyone.** It sits in a ledger until someone opens it.

---

## How to run the automated checks

```
# build (needed after any React change)
npm run build

# the medication suites — current baseline is 14 errors / 3 failures
php vendor/bin/phpunit tests/Feature/MedicationRoundSafetyTest.php \
  tests/Feature/MedicationRoundReactTest.php \
  tests/Feature/ControlledDrugRegisterTest.php \
  tests/Feature/MARSheetTest.php \
  tests/Feature/MedicationStockReactTest.php
```

Any run that differs from **14 errors / 3 failures** means something changed — in either
direction. That baseline is the only regression signal available until [I12](FRONTEND4-ISSUES.md#i12)
is dealt with.
