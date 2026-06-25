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

## 2026-06-25

> Covers a long run of work after the Lab 1.4.2 polish: a full **sidebar/header redesign**, **dark mode + the official brand colours**, a deep **Stock page redesign** (done on safe "Lab" copies), giving **the other medication screens the same "workspace" treatment**, making them **responsive**, and then a **functionality audit** that turned every dead-end button into something that works.

- **Redesigned the app frame (sidebar + header):**
  - The sidebar is now a **thin icon rail that slides open to the full menu on hover** (smooth, "languid" animation), and the logo auto-swaps (navy on white, white on dark).
  - Tried navy bars, then settled on a **clean white header + white sidebar** with **teal icons**, and a clear **"you are here"** highlight (a teal box that the current page sits in) plus a hover highlight on every row.
  - Added a proper **app-wide footer** that sticks to the bottom of the page.
- **Dark mode** for the medication screens (cards, tables, text all flip cleanly), and wove the **official Care One OS brand colours** (teal/orange/purple/green) through the screens.
- **Stock page — big redesign, done on throw-away "Lab" copies** so the live page stayed safe (made **Stock Lab** and **Stock Lab 2**). Turned a plain table into an **inventory dashboard**: colour-tinted summary cards, a **"Needs attention" / Low-stock** area (tried it four ways — banner, floating card, inside the activity panel, and a clickable card → drawer), a **Recent Activity timeline**, **search + filters**, **colour-graded stock bars**, **status badges**, **medication-type icons**, **sortable columns**, **hover row actions**, a **side drawer** with a medication's details + history, **bulk-action checkboxes**, and **top tabs** (Overview / Transactions / Reorders / Disposals) that swap the main panel while the sidebar stays put. Lots of small spacing/rounding/scale tweaks to taste.
- **Gave the other medication screens the same "workspace" look:**
  - **Missed Doses** rebuilt from a plain list into a **clinical follow-up workspace** — compact cards, an **"Outstanding follow-up"** strip, a **Recent Events** timeline, search/filter, **Resolve on hover**, coloured status, a real **Reason** column, and row icons.
  - **Controlled Drugs** and **Shift Handover** got the same treatment (compact cards, action/status colours + icons, a **Recent Activity / Attention** sidebar).
  - Made these pages **responsive** (columns stack on tablets/phones, cards reflow, tables scroll instead of getting crushed).
- **Functionality audit — made every button actually do something.** Went page by page and listed every control and what it should do (saved as `docs/medication-functionality-plan.md`), plus a step-by-step **test checklist** for later (`docs/medication-test-plan.md`). Found **7 dead-end buttons** and built all 7:
  - **Export** (Controlled Drugs + Missed Doses) — downloads a **CSV** of exactly what's on screen.
  - **Stock → View history** — opens a drawer of that medicine's movements.
  - **Stock → Filter** — working Status / Stock-level / Expiry filters.
  - **View Profile** (Round) — a **resident summary drawer** + a **"View full record"** link to the existing client page (new tab).
  - **Temporary Absence** (Round) — mark a resident away for a date range; their scheduled doses are recorded as **Omitted** with a reason so they don't pile up as "missed" (new server endpoint).
  - **View MAR Report** (Round) — a **printable MAR chart** (medicines × days grid) that opens in a new tab.
  - **Scan Medication** (Round) — a **manual stub**: type/paste a barcode or name → finds the matching dose → confirm (camera scanning left for when devices support it).
- All **20 automated tests still pass**.

## 2026-06-23

> Covers the run of work since the 2026-06-18 entry: trialling four resident-card designs on their own pages, putting the "extra" resident info **into the database for real**, adding **demo children clients**, and building **Lab 1.4.3** + heavily polishing **Lab 1.4.2**.

- **Four resident-card design options, each on its own page** so the owner can compare side by side (all built from real data, scoped to the one page each):
  - **Lab 1.4** → Option 1 "Context Card" (everything stacked under the name).
  - **Lab 1.4.1** → Option 2 "Header Banner" (very compact, chips stacked).
  - **Lab 1.4.2** → Option 3 "Wristband" (two columns) — later rebuilt into the rich banner (see below).
  - **Lab 1.4.3** → Option 4, the full **clinical workspace** from the owner's mock-up (photo + identity + info boxes, allergy alert banner, rich medication cards with a **Record split-button**, PRN + collapsible Upcoming). Built via a focused sub-task, scoped to the one file.
- **Made the "extra" resident fields real in the database** (the mock-up showed Room / NHS / Mobility / Diet, which the app didn't store):
  - Added a **migration** giving `service_user` three new columns — `room_number`, `nhs_number`, `diet` (mobility reuses the existing `suMobility`). All nullable/additive, existing data untouched.
  - Added a **seeder** that fills those fields for every resident (only blanks, so it never overwrites real data), and surfaced them through the controller so the cards show real, persistent, editable values. Explained to the owner: this now lives in the DB (survives reloads/uploads, runs on deploy via migrate + seed) — versus the earlier dummy values which were just computed in the browser.
- **Added 5 demo children clients** (with medications) via a committed, re-runnable seeder — child-appropriate names/ages, varied allergies/diets/rooms, and a mix of **regular doses across all rounds, PRN meds, low-stock items, and controlled drugs** (so the witness flow can be tested). Gives plenty of data to build functionality against.
- **Heavily polished Lab 1.4.2** into the owner's preferred look (lots of small back-and-forth):
  - Rebuilt the header as a **banner** (photo + identity + a Fall Risk / PRN / Regular Meds summary), then the med cards into the **rich one-row card** (time/status, tinted pill tile, name + CD/REGULAR badges, instruction, Dose/Route/Stock, blue **Record** split-button), widened so each card sits on one row.
  - Right sidebar: shrank the **Round Progress** donut + tightened the legend, tucked the **"By round"** breakdown behind a small toggle, made **Alerts** and **By round** start **collapsed** with clearer (circular) chevrons.
  - **Allergy strip fix**: residents whose allergy field literally says "No/None/Nil/N/A" are now treated as **no allergy** (filtered), and the strip shows a green **"✓ No Known Allergies"** when clear vs a red warning when present — so it never reads "Allergy: No".
  - Reworked the identity block to the owner's taste: **DOB · Age · Weight** then **NHS**, the allergy + the three stats as **small pills** (dropped the big bottom cards and the View Care Plan button), spread the info out and shrank the coloured pill icons.
  - Also fixed a latent crash (the stats array now safely handles "no resident selected").
- Logged issues **#8** (the whole sidebar should be clickable/hoverable + alerts dismissible), **#9** (long alerts list should scroll in its own box — since **fixed**), and **#10** (room number needs adding to the DB — now effectively done by the migration above).
- **Fixed the demo children not showing up:** they'd been seeded into **home 1** (Mick Carter's home), but the owner is logged into **home 101** — so they were invisible. **Moved all 5 children + their medications to home 101** and pointed the seeder at home 101 so it stays correct.
- **Spread the children's dose times across all four rounds** (Morning / Lunchtime / Evening / Night) so they show in the residents list whichever round is open — was clustered on Morning before. Seeder updated to keep the spread on any re-run.
- More **Lab 1.4.2** polish: brought the **risk chips** back (small colour-coded row under the pills, only when there are risks); made the **Residents Due list clearly scrollable** (capped height + always-visible scrollbar); and **scaled the whole detail view to 90%** (resident card + med cards shrink together, same proportions).
- **Reorganised the Medication sidebar nav:** promoted the polished build so **"Medication Round" now opens Lab 1.4.2**, and tucked all the other trial versions into a single collapsed **"Round Versions"** dropdown (Original/React, Lab, 1.1, 1.2, 1.3, 1.4, 1.4.1, 1.4.3, Lab 2) to keep the sidebar tidy. Switched the nav highlighting to **exact-path matching** so 1.4 vs 1.4.2 no longer both light up. Removed the **"Lab 1.4.2" badge** from the page now that it's the primary.
- **Answered the owner's DB questions:** `service_user` has **65 columns** and **226 rows** (37 in home 1, 11 active). Explained the difference between the earlier browser-computed dummy values and the now-**real, persistent** care-context columns.
- **Started building real functionality from the backlog** (layout is settled):
  - **#13 — Refusal/Omitted reason:** when a dose is **Refused / Not given / Omitted**, a **required reason dropdown** now appears (sensible preset reasons per outcome), enforced **both** in the browser and on the server. Stored in a new **`reason` column** on `mar_administrations` (committed migration). The reason is also **shown on the card** under the outcome.
  - **Viewing a recorded dose:** recorded meds now show **"Given/Refused HH:MM by [name]"**, with a chevron that **expands the full details inline** (outcome, dose, reason, witness, notes), and an **"Open record"** button that re-opens the dialog **pre-filled** so it can be reviewed/corrected.
  - **#16 — End Round + safety (all of it):**
    - **End Round** button opens a **summary** (Given / Not given / Outstanding counts + a list of doses still unrecorded), then **locks the round** — recording is blocked in the UI **and** on the server. New **`medication_round_closures`** table records who ended it and when; the page shows a green "round ended" banner.
    - **Managers can Re-open** a locked round (button hidden from carers; server enforces manager-only).
    - **Special instructions stand out** — shown in a highlighted box: blue for info, **amber ⚠ for cautions** (with food / crush / do not / nil by mouth / swallow whole …).
    - **Double-dose warning** — a red **"May already be given"** badge appears if the same medication is already recorded as given to that resident today.
- These are real backend + recording features (migrations, model, service, controller, routes + the shared modal/page), not just layout. Tasks **#13 and #16 marked done**.
  - **#14 — PRN ("as needed") flow:** added structured limits to medications (`prn_max_daily`, `prn_min_interval_hours` — committed migration, seeded onto the demo PRNs as max 3/day, 4h apart). PRN cards now show **Last Given / Next Available / Today (X of N)** and a **"Give PRN"** button that turns into a disabled **"Not available"** with the reason (*daily maximum reached* / *not due until HH:MM*) — **enforced on the server** too, so an early or over-limit PRN is rejected.
  - **#15 — Missed-dose + handover links:** the **Missed Doses** screen already auto-lists refused/omitted/withheld/not-available + overdue doses (so a recorded refusal shows there for free) — added a **"Missed Doses"** quick-link in the round to make that path obvious. New **"⚑ Flag to handover"** button on each resident: writes a **concern** (with an "action required" flag) into **today's shift handover** (creating a draft if none exists), tagged to the resident — it then appears under Medication Concerns on the Handover screen.
  - **All four functional backlog items (#13–#16) are now complete.** Remaining work is the smaller UI-polish issues in `docs/ISSUES.md`.

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
- **~13:40–14:00** — More Lab 2 sidebar fine-tuning: **shortened the boxes** a little (set their tall/wide shape to 4:4.5), added a bit more **space between the boxes**, **tightened the tinted alert rows** (less padding) and **shrank the alert description to one line**.
- **~14:00** — Logged three more things to **`docs/ISSUES.md`** to fix later: empty **Alerts** box should shrink to a small "no alerts" pill (#2); alerts should be **clickable** to open their detail (#3); the Round Progress **doughnut should be hoverable** to show each segment (#4).
- **~14:00–14:30** — Made the Lab 2 boxes **size to their content** (no more wasted blank space) with a **minimum height** so a near-empty box still looks like a box; tidied the alert spacing and **inset the tinted alert rows**; bumped the **box-header font**.
- **~14:30–15:00** — Rebuilt the **Round Progress** box to a richer "dashboard" look (like the owner's reference): **donut on the left**, and a big **"% complete"** + **overdue** summary on the right. Importantly, the headline **% now covers the whole day** (every round), while the **donut still shows the selected round** and changes when you switch Morning/Lunch/Evening/Night. (Tried an "estimated completion time" line too, then removed it.)
- **~15:00** — Logged two more issues: the **Alerts box should be a collapsible dropdown** (#5) and individual alerts should be **swipe/delete dismissible** (#6).
- **~15:15–15:45** — On **Lab**: turned it into a **3-column layout** (Next Medications Due + Recent Activity on the left, residents in the middle, the Round Progress/Alerts/Quick Actions block on the right); made the page **fill more of the screen** (and fixed it being pushed off-screen when the nav opens — the table now scrolls and columns wrap); **capped the residents list width** so rows don't stretch; and **shrank the Next Medications table** to fit.
- **~15:45** — Logged issue #7: clicking a resident should **toggle** the detail open/closed (clicking the same one again closes it).
- **~16:00** — Created a **third experimental copy, "Lab 1.1"** (`/medication/medication-round-lab1-1`) — a duplicate of Lab with its own routes.
- **~16:00–16:30** — Reworked **Lab 1.1**'s top area: date/round bar + residents as one left section, the **Round Progress / Alerts / Quick Actions block on the right pulled up the page**, Refresh/End Round aligned above the round tabs, and fine-tuned the box sizes (donut, %, legend, headers) and the spacing between the centre and the right.
- **~15:00–15:15** — More Round Progress polish (bigger %, nudged the donut and the % into position).
- **~15:15** — **Added two new sections under the residents list on Lab 2**, built from the data we already have:
  - **Next Medications Due** — a table (Time · Resident · Medication · Type · Status) of doses still to give this round, soonest first.
  - **Recent Activity** — a timeline of doses already recorded this round (e.g. "Paracetamol given · resident · by [staff]"), using the real "recorded by" name from the database.
  - Both update when you switch rounds. (No "Room" column — there's no room data; and the mockup's "round started / witness added" lines need backend event logging we don't have yet.)
- **~15:15** — Switched the residents list to **stack vertically** (one per row) instead of two across.
- **~16:30–17:30** — On **Lab 1.1**: made the resident detail a **click-to-expand dropdown (accordion)** — clicking a resident expands their details inline under their row; click again to close. Logged issues #5 (Alerts as a collapsible dropdown), #6 (alerts swipe/delete dismissible) and #7 (clicking a resident should toggle the detail).
- **~17:30** — Talked through the master-detail "empty gap" problem and made **Lab 1.2** (`/medication/medication-round-lab1-2`): residents as a **narrow side list** with the **Next Due + Recent Activity overview always visible**, and a resident's detail opening beside it on click — so it never shows an empty "click here" gap. Then tuned it (overview tucks under the residents when one is open, recent activity on top, residents box made narrower/neater).
- **~18:00** — Made **Lab 1.3** (`/medication/medication-round-lab1-3`), a copy of Lab 1.2, to trial a full visual redesign.

## 2026-06-18

- **~11:00** — Restarted/checked the local servers after a couple of days; gave the owner the lab page links again.
- **~11:40** — **Big redesign of Lab 1.3** (done via a focused sub-task, scoped so no shared components or other pages changed): turned it into **one calm single-workspace** look inspired by Apple Health / Linear — a **compact one-row header**; the left boxes merged into **one panel with hairline dividers** (Residents Due + Recent Activity + Next Due); a wide **centre** that leads with the resident's details then the **medication cards as the clear focus** (taller, white, just a thin coloured status stripe, the three action buttons); a single tidy right sidebar; and removed all the heavy coloured borders in favour of **white cards + soft shadows**. Verified: 20 tests pass, compiles, renders.
- **~13:00–14:00** — Spent the afternoon refining **Lab 1.4** "little by little" (it's the copy of the 1.3 redesign we're polishing). Moved the **title bar** (title + date + round tabs + Refresh/End Round) around to line it up with the **centre/Round Overview** column instead of spanning the whole page: tried it inside the centre column, then settled on it sitting over the **residents + centre** with its right edge at the centre boundary, the **right sidebar now a full-height column** coming up to the top beside it.
- **~14:00** — **Made the "Medication Round" title bigger** (18 → 24px).
- **~14:10** — **Gave the times-of-day bar more life** (it looked greyed-out): the **active round tab** now has a soft fill in its own colour with bold coloured text (not a flat white pill), **each tab icon stays vivid** in its round colour, **inactive labels** un-dimmed to a readable dark grey, and the **date field** got an indigo calendar icon and bolder text.
- **~14:30** — **Reworked the right sidebar to feel less cramped and more "Apple".** First tried three separate grouped cards, then (on the owner's preference) put it **back into one combined card** with hairline dividers — keeping the nicer internals: a **prominent centred ring**, **soft tinted alert rows** with white icon-chips and a count badge, a friendly "all clear" empty state, and roomier spacing.
- **~14:45** — Fixed the **doughnut/percentage mismatch**: the ring filled by the **selected round** but the centre % showed the **whole day**, so they disagreed. Now the **ring + centre % are both about the current round** (with a small round badge — Morning/Evening — in the header), and a clearly-labelled **"Whole day"** bar underneath shows the **general percentage across morning, afternoon & night** (e.g. `33% · 4/12`).
- **~15:00** — After a couple of false starts (narrowing the column alone looked wrong), **scaled the whole sidebar down proportionally** — same look and rhythm, just smaller: padding `lg → md`, ring 128 → 108, legend/alert/quick-action fonts and chips shrunk together, thinner whole-day bar, column 248 → 220.
- **~15:30** — Made the **flash notification fit nicely**: it used to span the full page width (so it stretched over the sidebar and looked oversized) — now it sits **inside the content column, just under the title bar**, at the same scale as the page.
- **~15:45** — Sidebar sizing back-and-forth: landed on a clean trick to **scale the whole sidebar by one number** (a CSS `transform: scale`) so it can be made smaller/bigger while keeping the **exact same proportions and spacing** — tried 90% → 85% → 95%, and the column width auto-matches.
- **~16:00** — **Alerts cleanup:** made the rows **denser** (less height), trialled a one-line version (dropped the "Overdue Medication" label) then **put the two-line version back**; finally **removed the coloured tint blocks** so each alert is just a **coloured warning icon + text**.
- **~16:15** — Gave the **"Whole day" figure its own emphasis** (tried a bold tinted block, then reverted to the simple line) and made its **progress bar clearly visible**. Then **increased the sidebar section-title font** (Round Progress / Alerts / Quick Actions).
- **~16:30** — **Restored the sidebar to full size** (dropped the scale trick) and **bumped all the text up** (legend, alerts, quick actions), then later **scaled it back down a touch** (92%) on request, and **tightened the gap right after the doughnut**.
- **~16:45** — **Swapped the doughnut and the whole-day meaning** per the owner: the **doughnut now shows the WHOLE DAY** (all rounds combined — ring, centre %, and legend), and underneath there's a new **"By round" breakdown** — Morning / Lunchtime / Evening / Night, each with **its own mini progress bar in its round colour** and a done/total count, with the current round highlighted.
- **~17:00** — Added a **"+N more"** expander so a long alerts list only shows the **first 3** with the rest one click away; then made the **whole Alerts section a collapsible accordion** — click the header (which shows a **"N notifications"** pill + chevron) to fold it away.
- **~17:15** — Logged two issues: **#8** — the whole right sidebar should be **clickable/hoverable and the alerts dismissible** (broadens the older #3/#4/#6); **#9** — a long alerts list **pushes the Quick Actions down the page**, so the alerts should **scroll inside their own fixed-height area** instead.

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
