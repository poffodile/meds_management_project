# Issues to fix

> A running list of problems, bugs, and "that looks off" things the owner spots while reviewing.
> **When the owner says "add this to issues", it gets added here.** We fix these once the UI design is settled.
>
> **Status key:** 🔲 open · 🔧 in progress · ✅ fixed
> Each issue has a number, the date it was noticed, what it is (plain English), and where (which screen/area).

## Open

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
