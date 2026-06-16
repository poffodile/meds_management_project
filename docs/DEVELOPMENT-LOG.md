# OmegaLife — Development Log (plain-English)

> **This is the one place that records what was done, day by day, newest first.**
> It replaces the older scattered notes. New work gets logged **here** going forward.
> Each day is a heading (the date). Items show a rough **time** (e.g. **~14:30**) where I have it, so you can see when things happened. Newest day at the top; earliest-to-latest within a day.

## What this project is (in simple terms)
The care app is old (built on dated technology). We're **giving it a modern look, one screen at a time**, without breaking the old version. The new screens are built with modern tools (React + Mantine) and sit next to the old ones at web addresses ending in `-react`. The **login is left alone**. The first area being modernised is **Medication** (the screens carers use to give residents their medicine).

To run it on this computer: `start-local.bat` (starts the database, the web server on port 8000, and the live-reload tool on port 5173). Open `http://127.0.0.1:8000`.

## Other documents (the "what it is", kept separate from this "what was done")
| Document | What it covers |
|---|---|
| `docs/design-system.md` | The reusable building blocks (colours, fonts, shared pieces) and which medication-round info is real vs still missing |
| `docs/medication-round-requirements.md` | The owner's full wish-list for the Medication Round screen + what's done vs to-do |
| `docs/ui-modernization-plan.md` | The overall plan for modernising the app |
| `docs/milestones/M0.md`, `M1.md` | Detail on the first two milestones (the base setup + the medication pages) |
| `FRONTEND.md`, `FRONTEND-PLAN.md` | A map of the new front-end code + an easy-read plan |

---

## 2026-06-16

- **~10:00** — On the test **"Lab" page**, tidied the top bar: moved the **date to the top-left**, put **Refresh** and **End Round** on that same line, made the date stand out (bold text, light-purple outline, sized like the buttons), and tried different positions for the round buttons.
- **~10:40** — The local app had stopped running between days, so I **started everything back up** (database, web server, live-reload tool).
- **~10:50** — It was showing an **old version of a page**, so I **cleared the cached files and forced a clean reload** — it now shows the latest.
- **~11:00** — **Gathered all the scattered logs into this single file** and pointed the old log here; **rewrote it in plain English**, and set a standing rule to keep it updated (with date + time) each session.
- **~11:30** — Started an **issues list** (`docs/ISSUES.md`): whenever the owner says "add this to issues" it gets logged there to fix once the design is settled. First entry (#1): the **Care One OS logo disappears when the sidebar is collapsed** — it should stay visible (e.g. in the top header). Saved this as a standing rule too.
- **~12:00** — Made a **second experimental copy of the round screen, "Lab 2"** (`/medication/medication-round-lab2`), a clean baseline separate from the first Lab page, so we can trial a different layout without touching anything live.
- **~12:00–13:00** — **Redesigned the Lab 2 layout to the owner's taste** (lots of small back-and-forth tweaks):
  - Put **Round Progress, Alerts and Quick Actions into one right-hand sidebar column** (built a shared `SidebarCard` so all three always look identical), made them **compact, upright "portrait" boxes**, and **narrowed** that column.
  - Reworked the **date + round-times bar**: date pinned left, round tabs (Morning/Lunchtime/Evening/Night) made **compact, bolder and right-aligned** with a little end padding; tidied spacing and padding.
  - **Aligned** the title and main content, nudged the main column slightly left, and adjusted the **gap between the main content and the sidebar**.
- **~13:00–13:40** — **Polished the inside of the Lab 2 sidebar boxes** so the content fits neatly:
  - **Round Progress donut** — fixed the centre label that was overlapping the ring (thinner ring + sized text), made it bigger, and sat it right under its header.
  - Made the box content **fit the box** (centre or top-align as needed) so there's no awkward empty space, and made **Quick Actions compact** so all five fit.
  - **Bigger box headers**, and tuned the internal padding so text isn't jammed against the edges.
  - **Alerts** — trialled two looks (a coloured left-bar vs a compact row) and went with the **compact** style (smaller icon, tighter rows), top-aligned under the header.

## 2026-06-11

- **Answered questions about the system:** there are **414 users**; for the new screens, roles come down to two — **manager** and **carer**. Found the login pages, and flagged a **test-only "dev-login" shortcut that lets anyone in with no password** — this must be switched off before going live.
- **Gave the other four medication screens** (Stock, Controlled Drugs, Missed Doses, Shift Handover) the **same fresh look** as the main one (matching headers with icons, tidy filter bars).
- **Wrote down the owner's full wish-list** for the Medication Round screen and checked off what's done vs still to do (`medication-round-requirements.md`).
- **Built the actual recording** on the Medication Round screen:
  - **"Administer" records a normal medicine in one tap** and automatically reduces its stock count, keeping you on the same resident.
  - **Controlled drugs now require a witness** (a second staff member) — the screen asks for it, and the server refuses to save without one.
- **Reshaped the Medication Round screen many times** to match the owner's example pictures — settled on a **3-part layout**: a list of residents → click one → see their full details, with a summary panel on the right. Added a **smooth slide** when opening a resident, and fixed cards that looked squashed.
- **Made the screen work on phones/small screens.**
- **Fixed "everything looks too big"** by making the text and spacing a little smaller in a clean way.
- **Created some pretend data** (residents, a controlled drug, allergies, low stock, etc.) so the owner could do a **live demo**, then removed it afterwards.
- **Made a copy of the Medication Round screen — the "Lab" page** (`/medication/medication-round-lab`) — so we can try out new designs there **without touching the real screen**.

## 2026-06-09

- **Finished the automatic tests** for the small reusable pieces.
- **Redesigned the sidebar** (logo, grouped menu) and the **Medication Round screen**.
- **Checked how users, roles, and login work** in the code (and saved notes for next time).
- **Big rebuild:** created a **reusable "design system"** — one place for colours, fonts, and shared pieces — so we stop copy-pasting code, and rebuilt the Medication Round screen on top of it. Wired in **real data** (medicines, times, stock, allergies, risks) and worked out which doses show as **due / overdue / upcoming**.
- **Set the brand logo** (Care One OS) and a nicer **font** (Plus Jakarta Sans) for the whole app.

## 2026-06-04 *(before the day-by-day log started)*

- **Looked at the old app** (very dated technology) and **decided the approach:** keep the existing Laravel back-end, build the new screens with modern tools (React + Mantine), one page at a time, and leave the login alone.
- **Built the starting pieces** and converted the **first set of medication pages** to the new look.
- More detail: `docs/milestones/M0.md`, `M1.md`.

---

## Still to do (carried forward)

**Medication Round screen** (from the wish-list):
- A **drop-down reason** when a dose is refused or not given (needed for proper records).
- **"As needed" (PRN) medicines** — show when last given, when allowed next, and the daily limit.
- **Send problems on:** a refused/missed dose should appear on the **Missed Doses** screen; let staff flag an issue to the **Shift Handover**.
- Make **special instructions** stand out; show **"already given at HH:MM by [name]"**; an **End Round** button that locks the round and shows a summary.

**Bigger picture:**
- ⚠️ **Turn off the `/dev-login` shortcut** before the app ever goes live.
- **Proper company/home data separation** (so one care home can't see another's data).
- Decide if **manager/carer** (two views) is enough, or if owners/admins need their own.
- Fill in the **missing Medication Round info** (room number, NHS number, etc.) once that data is available.
- **Next area:** Dashboard, Client profile, Daily Log, Schedule.
- A few **workflow questions** still open (when a round officially "starts", two carers at once, how late counts as overdue, etc.).

**Things the owner told us:** carers do **one resident at a time**; **controlled drugs always need a witness**; barcode scanners are a "later" idea.
