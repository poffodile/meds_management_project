# Medication pages — functionality plan & checklist

Audit of every interactive control across the 5 core medication pages, plus the
prioritised build plan for the dead-ends. Tick items off as they're built.

Effort key: **S** ≈ <½ day · **M** ≈ ½–1 day · **L** ≈ multi-day.
FE-only = no backend/schema change needed (data already on the page).

Last audited: 2026-06-25.

---

## Status summary

**Already functional end-to-end** (no work needed): recording doses, ending/
reopening rounds, flag-to-handover, adjusting stock, adding CD entries, resolving
missed doses, creating/acknowledging handovers, all date nav / filters / search /
tabs.

**Dead-ends to build: 7** (listed below).

---

## Per-page audit

### Medication Round (`/medication/medication-round-lab1-4-2`)
- ✅ Date picker, round tabs, refresh, End Round (+modal), Reopen
- ✅ Resident search, resident select, Flag to handover (+modal), Close resident
- ✅ Administer / Refused / Not-given, Give PRN, dose details, record-note, Record modal
- ✅ Alerts collapse, alert → resident, alert dismiss, progress toggle
- ✅ Quick: Missed Doses, View Handover Notes
- ⚠️ **View Profile** · ⚠️ **Scan Medication** · ⚠️ **Temporary Absence** · ⚠️ **View MAR Report**

### Medication Stock (`/medication/stock-react`)
- ✅ Adjust stock (+modal), tabs, search, row → Adjust stock
- ⚠️ **Filter** · ⚠️ **View history**

### Controlled Drugs (`/medication/controlled-drugs-react`)
- ✅ Add entry (+modal), search, action filter, Recent Activity
- ⚠️ **Export**

### Missed Doses (`/medication/missed-doses-react`)
- ✅ Date nav, status filter, search, issue filter, Resolve (+modal), timeline
- ⚠️ **Export**

### Shift Handover (`/medication/shift-handover-react`)
- ✅ New handover (+modal), date nav, Acknowledge, Attention sidebar
- ✅ No dead-ends

---

## Build plan (in priority order)

### Tier 1 — Quick wins (FE-only, low risk)
- [x] **1. Export — Controlled Drugs & Missed Doses** · S · FE-only ✅ 2026-06-25
  - Shared `frontend/lib/csv.js` → `downloadCsv(filename, columns, rows)`; Export buttons dump the current *filtered* rows; disabled when empty.
  - CD → `controlled-drugs-register.csv`; Missed Doses → `missed-doses-<date>.csv`.
- [x] **2. Stock → View history** · S–M · FE-only ✅ 2026-06-25
  - Row "⋯ → View history" opens a right drawer: med summary + full transaction history (±, balance, who/when).
- [x] **3. Stock → Filter** · S · FE-only ✅ 2026-06-25
  - Filter button toggles a Status / Stock level / Expiry bar (with active-count badge + Clear); filters the inventory client-side; tab count reflects the filtered set.
- [x] **4. View Profile (Round)** · S–M ✅ 2026-06-25 — hybrid
  - Profile **drawer** built from round data (avatar, name, DOB/age, allergies, fall risk, PRN/regular counts, NHS, weight, active risks) **+ "View full record →"** link opening the legacy `/client-details/{id}` in a new tab.

### Tier 2 — Medium (needs backend)
- [x] **5. Temporary Absence (Round)** · M · BE + FE ✅ 2026-06-25
  - Quick Action → modal (resident + from/until + reason) → `POST /…/temporary-absence` bulk-records each scheduled (non-PRN, not-already-recorded, not-locked) dose in range as Omitted (code O) with the reason. 31-day safety cap.
- [x] **6. View MAR Report (Round)** · M–L · BE + FE ✅ 2026-06-25
  - Quick Action → modal (resident + date range) → opens standalone printable page `/medication/mar-report` in a new tab. Grid = medication×slot rows × day columns, colour-coded codes + legend + Print button. 31-day cap.

### Tier 3 — Larger / hardware-dependent
- [x] **7. Scan Medication (Round)** · manual stub ✅ 2026-06-25
  - Quick Action → modal: type/paste a barcode or medication name → matches a due dose in the round → selecting it opens the record dialog to confirm. Camera scanning deferred until devices/barcodes support it.

---

## Open decisions
- [ ] **View Profile (#4):** profile drawer (from existing data) **or** link to legacy client page?
- [ ] **Export (#1):** CSV download enough, **or** also a Print/printable layout?

## Recommended sequence
1 → 2 → 3 → 4 (Tier 1, ~1 day total) → 5 → 6 (Tier 2) → 7 (Tier 3).
