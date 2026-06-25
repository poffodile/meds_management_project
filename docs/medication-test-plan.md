# Medication pages — manual test plan (test.md)

Run this end-to-end once the functionality work is complete. Go page by page,
tick each check. Test as **both roles** where it matters (manager vs carer) and
at **2–3 widths** (desktop ≥1200, tablet ~900, phone ~390).

How to run: log in, then visit each page; the React pages are the `*-react`
routes (Round is `/medication/medication-round-lab1-4-2`).

Legend: `[ ]` not tested · `[x]` pass · `[!]` fail (note it).

---

## 0. Cross-cutting (do once)
- [ ] Each page loads with no console errors.
- [ ] **Dark mode** (user menu → toggle): cards, tables, sidebars stay readable.
- [ ] **Responsive**: workspace columns stack on narrow; tables scroll sideways, not crushed; cards reflow 4→2; header title shrinks.
- [ ] **Role**: as a **carer**, manager-only actions are hidden/disabled (Adjust stock, Reorder, Reopen); as a **manager** they appear.
- [ ] Sidebar nav: active page highlighted (teal box); hover shows highlight; footer visible at bottom.

---

## 1. Medication Round
- [ ] Date picker changes day and reloads doses.
- [ ] Round tabs (Morning/Lunchtime/Evening/Night) switch the round.
- [ ] Refresh (↻) re-fetches.
- [ ] Search residents filters the list; clicking a resident selects/deselects.
- [ ] **Administer (Given)** records a dose → row updates to given.
- [ ] **More ▾ → Refused / Not given** opens record modal with that outcome; saving records it.
- [ ] **Give PRN** records a PRN dose.
- [ ] Dose details (ⓘ) expands; record-note (📄) opens the modal.
- [ ] **Flag to handover** → modal → "Add to handover" posts (needs text); appears on Shift Handover.
- [ ] **End Round** → modal → End round marks complete; **Round Ended** shows; manager **Reopen** re-opens.
- [ ] Alerts: collapse toggle; clicking an alert jumps to that resident; dismiss (✕) removes it.
- [ ] Quick links: Missed Doses & View Handover Notes navigate correctly.
- [ ] **View Profile** opens a drawer (allergies, fall risk, PRN/regular, NHS, weight, risks); **View full record →** opens `/client-details/{id}` in a new tab. ✅ built 2026-06-25
- [ ] **Temporary Absence** (Quick Action) → modal (resident + from/until + reason) → saves; those scheduled doses become Omitted with the reason and stop showing as missed; PRN/already-recorded untouched; ended rounds skipped. ✅ built 2026-06-25
- [ ] **View MAR Report** (Quick Action) → modal (resident + range) → opens printable chart in a new tab; grid shows codes per day; legend + **Print** works (landscape). ✅ built 2026-06-25
- [ ] **Scan Medication** (Quick Action) → modal; typing a med name lists matching due doses; picking one selects the resident + opens the record dialog to confirm. ✅ built 2026-06-25 (manual stub; camera later)

## 2. Medication Stock
- [ ] Tabs: Inventory / Recent Transactions switch.
- [ ] Search filters rows.
- [ ] Stock bars are colour-graded (green/amber/red), status badges correct.
- [ ] **Adjust stock** (manager) → modal → save updates the stock + adds a transaction.
- [ ] **Row ⋯ → View history** opens the drawer with that med's transactions (+/− signs, balance, who/when). ✅ built 2026-06-25
- [ ] **Filter** button toggles Status/Stock/Expiry selects; filtering narrows the inventory; active-count badge shows; Clear resets; tab count matches. ✅ built 2026-06-25

## 3. Controlled Drugs
- [ ] **Add entry** → modal → save appends to the register (with witness + balance).
- [ ] Search + Action filter narrow the table; Recent Activity shows latest.
- [ ] Action icons/badges + dose ± sign render correctly.
- [ ] **Export** downloads `controlled-drugs-register.csv` matching the filtered rows; disabled when empty. ✅ built 2026-06-25

## 4. Missed Doses
- [ ] Date nav (← / date / → / Today) reloads.
- [ ] Status filter (Outstanding / Resolved / All) reloads the set.
- [ ] Search + Issue filter narrow rows.
- [ ] **Outstanding follow-up** strip + **Recent Events** timeline reflect the data.
- [ ] **Resolve** (hover a row) → modal → save records the clinical action; row flips to Resolved.
- [ ] **Export** downloads `missed-doses-<date>.csv` matching the filtered rows; disabled when empty. ✅ built 2026-06-25

## 5. Shift Handover
- [ ] Date nav reloads.
- [ ] **New handover** → modal → Submit / Save draft creates it.
- [ ] **Acknowledge** (on a submitted handover) marks it acknowledged; status badge/accent updates.
- [ ] **Attention sidebar** lists priority alerts + action-required concerns from the day's handovers.
- [ ] Status accent bar/icon: Submitted=orange, Acknowledged=green, Draft=grey.

---

## Sign-off
- [ ] All five pages pass on desktop.
- [ ] All five pass on tablet + phone widths.
- [ ] Dark mode pass.
- [ ] Manager + carer roles pass.
