# Issues to fix

> A running list of problems, bugs, and "that looks off" things the owner spots while reviewing.
> **When the owner says "add this to issues", it gets added here.** We fix these once the UI design is settled.
>
> **Status key:** 🔲 open · 🔧 in progress · ✅ fixed
> Each issue has a number, the date it was noticed, what it is (plain English), and where (which screen/area).

## Open

- 🔲 **#1** (noticed 2026-06-16) — The **Care One OS logo** lives in the sidebar, so when the sidebar is collapsed/hidden the logo **disappears with it**. Want the logo to **stay visible on the page** even when the sidebar is gone (e.g. show it in the top header bar instead). — *Area: app shell (sidebar + header), affects all pages.*
- 🔲 **#2** (noticed 2026-06-16) — When there are **no alerts**, the Alerts box still shows as a **big empty white box** (the fixed box height reserves the space). Instead it should **shrink to a small "No alerts" pill/box** rather than a full-size empty card. — *Area: Medication Round sidebar — Alerts box (seen on Lab 2).*
- 🔲 **#3** (noticed 2026-06-16) — The **alerts aren't clickable**. Clicking an alert should **open/show that alert's details** (e.g. jump to the resident/medication or a popup). — *Area: Medication Round sidebar — Alerts box.*
- 🔲 **#4** (noticed 2026-06-16) — The **Round Progress doughnut should be hoverable** — hovering a segment should show what it represents (e.g. the count/label for Completed / Overdue / Due Soon / Not Started). — *Area: Medication Round sidebar — Round Progress doughnut.*
- 🔲 **#5** (noticed 2026-06-16) — The **Alerts box should be a collapsible dropdown** — the alerts list should expand/collapse (e.g. click the "Alerts" header to open/close it) rather than always being fully shown. — *Area: Medication Round sidebar — Alerts box.*
- 🔲 **#6** (noticed 2026-06-16) — Each alert should be **dismissible** — give the user a way to **swipe or delete** an alert to clear it. — *Area: Medication Round sidebar — Alerts box.*
- 🔲 **#7** (noticed 2026-06-16) — Clicking a resident should **toggle** the detail panel — clicking the **same resident again should close** the detail (right now it only opens; you have to use the ✕). So one click opens, clicking the same one again closes. — *Area: Medication Round — residents list / resident detail.*

## Fixed

_(fixed issues move down here, with the date they were fixed)_
