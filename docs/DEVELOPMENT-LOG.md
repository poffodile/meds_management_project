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

## 2026-07-16

- **Built a permanent Care One OS multi-agent review/build system inside the project.** This turns the ad-hoc "review panel" into a durable, versioned setup so every page can be designed, built and reviewed the same rigorous way. Nothing about the running app changed — this is tooling + documentation only.
- **15 specialist agents** in `.claude/agents/`: `care-one-orchestrator` (runs the whole per-page process and enforces the completion gate), `healthcare-researcher`, `uk-compliance-reviewer`, `clinical-safety-reviewer` (DCB0129/0160 thinking), `medication-workflow-specialist`, `dm-d-terminology-specialist`, `gp-connect-integration-specialist`, `barcode-and-medication-identification-specialist`, `information-governance-reviewer`, `security-and-permissions-reviewer`, `healthcare-ui-designer`, `responsive-accessibility-reviewer`, `frontend-implementer`, `backend-implementer`, `healthcare-qa-reviewer`. Each has a **limited responsibility, only the tools it needs, the stack, the official standards it must cite, a fixed output format, and hard rules** against unsupported compliance claims + instructions to preserve existing functionality and flag decisions needing a qualified human.
- **7 reusable slash commands** in `.claude/commands/`: `/care-one-research`, `/care-one-design-page`, `/care-one-build-page`, `/care-one-review-page`, `/care-one-safety-review`, `/care-one-mobile-review`, `/care-one-test-page`.
- **Shared registry** in `docs/care-one-os/`: product context, UK standards register (classified legal / NHS-standard / assurance / good-practice), official source register (with version + access date), design-system rules, medication-workflow requirements, a DCB0129-style clinical hazard log, a requirements traceability matrix, a per-page Definition of Done (completion gate), and the agent review workflow.
- **Guardrail baked into every agent + doc:** an AI review does **not** make Care One OS compliant, certified, clinically safe or NHS-approved — agents must name the exact requirement + official source and flag what a CSO / DPO / pharmacist / medication lead / security specialist / NHS assurance body must sign off. The orchestrator **refuses to mark a page "done" while any Critical finding is open**, and does not let two implementers edit the same file at once.
- Superseded the 5 earlier one-off reviewer agents (folded into the 15). Confirmed Claude Code v2.1.209 supports project subagents + slash commands; validated all 22 config files (frontmatter, required fields, agent name==filename) — all pass.

### Medication Round — review panel findings (no page code written yet)
- Ran 7 specialists over the **existing** round page. Headline: **the backend is much better than our own notes claimed** — `buildRoundProps()` already returns PRN limits/blocked state, allergies, risk flags, NHS number, CD schedule, stock and full audit fields. The frontend simply **never renders** most of it. So the rebuild is mostly a frontend job on working logic. (`docs/medication-round-requirements.md` is out of date — it describes a different variant.)
- **Critical defects found in the live page:** a **render crash** (`MedicationRound.jsx:208` renders a `{label,level}` object as a React child — blanks the page for any resident with an active care-plan risk, and there is no error boundary); **silent write failure** (`recordFrontend2()` redirects on failure so Inertia fires `onSuccess` → a failed dose can look recorded); **MAR code "S" (Sleeping) counted as Given** (and consuming PRN allowance) while "Resident asleep" *also* exists as a refusal reason — the same event recordable two opposite ways; **round can be locked with doses outstanding**, no confirmation; **no unique constraint** on `(mar_sheet_id,date,time_slot)` + no submit disable → double-administration race.
- **Security/IG:** `marReport()` had a **cross-home IDOR** (unscoped `ServiceUser::find()` → another home's name/DOB). The role gate lists *every* user_type, so it gates nothing — any carer can record any dose and lock a whole home's round (only *reopen* is manager-gated). CD "witness" is unverified free text. Round-closure rows are **hard-deleted** on reopen; corrections **overwrite in place**.
- **Accessibility:** a keyboard/screen-reader user **cannot complete a round at all** (the resident row is a `div` with `onClick`). On phone, status collapses to a **bare colour dot** (tooltip logic inverted). "Overdue"/"Given" text fails contrast (2.7:1 / 3.35:1) because the page defines **local hex** instead of importing the vetted `statusPalette`.
- Agents corrected me repeatedly, which is the point: CD **witness at administration is CQC/NICE good practice, not** a Misuse of Drugs Regs 2001 clause; there is **no nationally-mandated MAR code set** (provider policy); WCAG 2.2 is Oct 2023.

### Two regulatory regimes — the register was aimed at the wrong one
- The owner's company runs **children's & young people's** homes. Children's homes are regulated by **Ofsted** under the Care Standards Act 2000 + **Children's Homes (England) Regulations 2015** — **not CQC**. Our register was CQC/adult-only, and **NICE NG67 is confirmed adults-18+**, so we'd been citing inapplicable guidance for the owner's own service. Register now covers **both** regimes (adult/CQC STD-01–13; children's/Ofsted STD-60–65); agents must cite the one matching the **house's** setting.
- Key children's findings: **reg 23** = medicines (storage, right-child, records, supported self-administration); **reg 10** = GP + dentist registration; **reg 40** = Ofsted notifications. **MCA 2005 applies from 16**, so under-16s need **Gillick competence / parental responsibility** — MCA covers *none* of Neptune's younger residents. 16–17 overlap is **UNVERIFIED** (legal review). **No NICE children's-home medicines equivalent of SC1/NG67 exists.**

### Dataset — reset to an industry-standard demo ("Neptune House" build)
- The dummy data was **wrong for the market and clinically dangerous**: residents were **children aged 7–14 in an adult-social-care pitch**; **Warfarin 25mg×2 flagged as a Schedule 2 CD** (warfarin is not a CD; 50mg is a dangerous dose); **Cetirizine flagged as a CD**; **Metformin marked PRN**; "Test"/"test" medicines; Aries residents were triplicate names with **2025/2026 DOBs (babies)**; `care_plan_risks` held **one placeholder row** ("Risk Description"/"test") — which is exactly why the render crash stayed invisible.
- **Backup first:** `storage/backups/laravel-backup-2026-07-16-pre-reseed.sql` (34MB) — everything reversible.
- **Schema (additive, guarded):** `mar_sheets.form` + `mar_sheets.unit` (the brief requires *form*; `unit` was **read by the round payload but never existed** — always null); `home.care_setting` + `home.is_dual_registered` (nothing recorded which regime a house is under — and `number_of_child` is a *capacity count*, not a type flag); `service_user.date_of_birth` **TEXT → DATE** with `date_of_birth_raw` preserving the original.
- **DOB conversion was not trivial:** 169 ISO + 34 `DD-MM-YYYY` + 7 `DD/MM/YYYY` + 1 `DD.MM.YYYY` + a literal `.` + **36 rows of `1970-01-01`** (Unix epoch placeholder). 31 rows were genuinely ambiguous (`05-01-2007` = 5 Jan or 1 May?). Resolved as DD-MM on evidence (day>12 cases prove the ordering) but **kept the raw text** rather than trust the inference. Epoch placeholders **left in place, flagged** — deleting valid-but-implausible dates is data-cleaning, not type-conversion, and this migration will one day run on real customer data.
- **Seeded** `NeptuneHouseDemoSeeder`: **Neptune House (101) = children's home**, 8 residents aged **7–17**, 18 sheets; **Aries House (8) = adult supported living**, 6 residents aged 35–88, 12 sheets. Deliberate test cases: NKDA strings, **12 real care-plan risks** (arms the crash), PRN **at daily max** and **inside interval** (both must return `blocked`), low **and zero** stock, a 70-char medicine name, a discontinued sheet, and only genuine CDs (Methylphenidate/Morphine Sch 2). Removed orphaned sheets on deleted Station Road, and **Paracetamol + Warfarin rows in the CD register** (same misclassification, second table).
- **Honestly flagged, not faked:** paediatric PRN limits are a flat 4/24h — real paediatric dosing is **weight/age-banded (BNFc)** and needs a pharmacist; **Aries' CD requirements are unresolved** (CQC's position is a person's *own home*, incl. supported-living tenancy, needs **no** CD register/witness — that attaches to registered *care homes*), so the Morphine entry assumes the strict model and may be wrong. Resident **photos were not faked** — pointing at non-existent files renders broken images, which is worse than an honest avatar.

### Product architecture decided with the owner
- **SaaS two-tier data:** *reference* data ships with the product (dm+d catalogue, MAR codes, CD schedules) **shared across tenants**; *tenant* data is imported per company. This resolves the dm+d agent's open question — the medicine catalogue is **shared, not per-company**. A customer shouldn't have to type in the BNF to start.
- **Medication must stand alone** (a pharmacy has no MAR sheets) — today a medicine has **no independent existence**, it only exists as a `mar_sheets` row. Needs a **medicine catalogue** as the identity anchor (REQ-MED-100/101/102).
- **One resident model, common core + conditional fields** (REQ-MED-109/110/111): companies running both children's and adult services must never enter data twice. **Do not fork the model** — a 16–17-year-old in a children's home sits in *both* consent frames, and a resident turning 18 must transition without losing history. **Structure stays static and queryable; only *which fields are asked* is dynamic** — explicitly **no EAV/JSON blobs**, which would destroy the extractability the owner asked for.
- `is_controlled` should be **derived from coded reference data**, never typed per prescription — that's what let Warfarin become a Schedule 2 drug (REQ-MED-107).

---

## 2026-07-14

- **New standing bar: the whole site must look premium / "Apple-quality" / like real money was spent.** The owner found the existing colours, spacing and layouts "childish" and "not unique/professional." Captured this as a permanent design directive (see `memory/premium-design-bar.md`): one ink colour, neutral greys, hairline dividers, **muted** semantic hues (never fully saturated), lighter type weights, tabular numerals, soft single-layer shadows, and colour used only as **small signals** (a dot, a thin bar) — no rainbow Mantine `variant="light"` badges/tiles, no cream/tan surfaces, no heavy glows.
- **Controlled Drug register (`Frontend2/ControlledDrugs.jsx`) rebuilt to that bar.** Replaced the cream/tan tab strip with a clean neutral segmented control; swapped rainbow icon tiles + coloured badges for neutral graphite tiles with a single muted-coloured icon and a small dot; dropped `fw 800`/34px black numbers for lighter weights + tabular numerals; thinned the "by action" bars; softened shadows to hairlines. All behaviour (stats, tab filters, search, export, witness compliance, add-entry modal) unchanged.
- **Medication Stock (`Frontend2/Stock2.jsx`) restyled to match** — surgical visual pass, no logic touched. Killed the gradient "banner" card backgrounds (now clean white/dark surfaces), the teal→navy gradient search frame (now a clean hairline input), and the saturated status/sub-view pills (now muted fills + soft shadows). Introduced shared premium tokens + three small helpers (`MedTile`, `CdTag`, `StatusChip`) and reused them across the overview table, detail panel, Stock count, Reorder/On-order, and Disposal views. Muted every semantic hue (status buckets, transaction types, forecast tones) to the new palette; buttons moved to navy/muted-teal/muted-terracotta.
- **Latent bug fixed while in Stock2:** the Stock-count error toast rendered `IconAlertTriangle` which was never imported — posting a controlled-drug count discrepancy **without a witness** would have thrown a ReferenceError instead of showing the "add a witness" toast. Import added; frontend builds clean.

## 2026-07-09

- **Missed doses (Page 1) — big polish + full tester sweep.** Restarted the local servers (MySQL + PHP on :8000 + Vite on :5173 — the white pages were because Vite/`public/hot` wasn't running). Added **Frontend 1 / Frontend 2 shortcut buttons** to the shared legacy top bar (`resources/views/frontEnd/common/header.blade.php`) so you can jump to the new screens from any old page.
- **Missed doses UI work:** made the detail panel **toggle closed** when you click the same row again; tuned the **doughnut** size; gave the doughnut card a **soft blue-grey→white gradient** (dark-mode aware) and the log table a **centred** version of it; row-hover made a translucent navy so it reads over the tint; **search bar** rounded + shadowed + enlarged; **"Log"** heading enlarged; title renamed to **"Missed doses"** and enlarged; **date** shown in brand navy; action buttons (Export/Refresh/Save) moved to **brand navy** (steel-blue kept only for the "Follow-ups" status); tidied the **date-nav / Download audit / Export report** trio to equal height + shadow + even spacing; added **success toasts** (installed `@mantine/notifications`, provider in `app.jsx`, bottom-centre, branded).
- **Tester sweep of Missed doses — 8 issues logged, then fixed one-by-one (with the owner ticking each):** (1) **log rows keyboard-accessible** (role/tabindex/Enter + focus ring); (2) removed **dead `ResolveDoseModal` import**; (3) **capped Next-day at today** (no future paging); (4) **UTF-8 BOM** on both CSV exports (Excel accents); (5) **doughnut reconciled to an "outstanding" view** — every slice counts unresolved-only, maths now adds up (owner chose this over a totals view); (6) **doughnut legend consistent** — every card filters (Follow-ups → outstanding), plus **per-slice hover tooltips** like a pie chart; (7) **Undo now needs confirmation** — two-step "Undo → Are you sure?" (managers-only + reason coming, see #27), refined to a grown-up muted look after feedback (dropped childish bright-red + warning icon). (#8 client-side validation still pending.)
- **Discussed audit integrity of "Undo" and agreed a "Strong" plan → logged as ISSUE #27.** Concern: a manager could undo everything and erase records. Explained the current safety net (append-only change-log table snapshots who/what/when on every resolve/edit/remove; "Download audit" CSV carries it). Agreed to harden before go-live: **never hard-delete (void instead)**, **require a reason**, **restrict undo to managers**, **surface undone history on-screen**, **DB-level triggers** to make the log truly append-only, and — owner's addition — **record even privileged/direct-database access** so changes made outside the app still leave a trace. Not yet built; open decisions (manager set incl. CM?, DB triggers now vs go-live) noted in #27.

## 2026-07-06

- **Duplicated the Med Round (split) page to try a new right-rail design** the owner mocked up. New page `/frontend2/medication-round-split-b` ("Med round (split B)" in the sidebar) — a full clone of the split page (same residents master–detail, same administer flow, its own record/end/reopen endpoints so it stays put). The **only** difference is the right-hand column, which now matches the mockup:
  - **Quick actions** card (replaces the mockup's staff-profile card) — tappable rows to Missed doses, Controlled drugs, Medication stock, Residents, each with a tinted icon chip, in the same card styling.
  - **Stat trio** — three small cards: **Given** (green), **Flagged** (orange = this round's recorded-but-not-given), **Left** (navy = remaining to record).
  - **Today's outcomes** donut — an "On track / Behind" pill (behind if any dose is overdue-and-unrecorded), a ring with the whole-day **Doses** total in the middle, and a **Given / Due / Refused / Missed** legend with live percentages (green / grey / purple / red, matching the mockup).
  - Left side + header + round tabs are untouched. Wired via new controller methods (`indexFrontend2SplitB` + record/end/reopen) and routes; the original split page is unchanged. Builds clean.
- **Then made a Split C** (`/frontend2/medication-round-split-c`, "Med round (split C)" in the sidebar) to copy the **exact box layout/sizes** from a second (zoomed) mockup — bigger, more spacious rail. Same three-box stack, but scaled to the mockup: top card sized like the mockup's staff-profile box (radius 26) but holding **Quick actions** fitted inside (owner didn't want the Aisha Khan profile content, just its box shape/size); larger stat trio (30px numbers, radius 20); and a bigger **Today's outcomes** donut (ring 200/thickness 20, 40px total) with a roomier legend. Its own controller methods + routes; original split + Split B untouched. Builds clean.

## 2026-07-07

- **Missed doses (Page 1) — master–detail rework + full resolve lifecycle.** Rebuilt the page around a **master–detail layout**: the log is now **full-width** and clicking a row **slides a detail panel in from the right** (smooth open *and* close) — this **replaces the old popups AND the entire right rail** (the "By reason", "N need follow-up" and "Audit ready" cards are gone). The panel (`DosePanel` in `MissedDoses.jsx`, driven by Inertia `useForm`) is context-aware: a **resolve form** for outstanding doses, **read-only details + Edit/Undo** for resolved ones. Added: a **Resolved** filter tab; **edit** a resolution (server-side `updateOrCreate`) and **Undo** it (new `unresolveFrontend2` route + `runUnresolve` that deletes the `MedicationDoseReview`); surfaced `notes` + `reviewed_at` so resolved rows show the clinical action inline (green) and the panel shows who/when (chips away at ISSUE #13); a **Download audit** button in the header (full audit CSV incl. clinical action / notes / reviewer, all events — separate from the filtered "Export report"); CSV downloads made robust (append anchor before click). Uniform `PAGE_ZOOM` (0.9); date-nav + Today font enlarged. **Stat-pill trials** added to compare (navy pills with tinted count-chips, an events **stacked-bar** card, a **donut+legend** card, **white left-cap pills**) — owner still choosing a winner; stacked-bar card removed after review. Verified resolve / export / undo end-to-end via headless clicks.

- **Dark mode — "sidebar ⇄ body" swap (inverted rail).** New dark-mode concept: light mode = navy rail + light body; **dark mode inverts it** = a light rail on a dark body. Sidebar colours were module constants, so they're now provided **reactively via a `NavToneContext`** keyed off `useComputedColorScheme` — the rail flips to a light gradient with dark nav text (logo → on-light tone, Care Copilot card → navy so it still pops), while the body stays deep navy. Refinements after first pass: fixed the active-nav pill (it was resolving `light-dark()` to **dark** navy on a light rail — the "broken" look; now an explicit light lavender pill), gave the rail a **top-lit gradient + seam shadow** cast onto the body for depth, lifted the body to a **richer navy** (`#131E33`) with the top bar/footer a step lighter (`#182740`, elevated), and added **hairline borders** on the content cards so they crisp against the navy. Light mode is untouched. Header/footer/canvas all in `frontend2/Layouts/AppShell.jsx`; nav pill CSS in `AppShell.module.css`.

- **Cool blue-grey theme + data-freshness stamp + app footer.** Swapped the warm cream canvas for a cool light blue-grey (`#ECEFF4`) app-wide — body, canvas, top bar, footer, with cool border (`#DCE2EC`) and cool hover/active tints (`#E4EAF3`) so hover/selected stay visible. Missed doses header gained a **"Live · data as of HH:MM"** pill (green dot + a bold teal circular **refresh** button) that re-stamps on every fresh payload, so staff can see the data is current. The panel shows a **"Last updated · time · who"** stamp beside the Resolved badge (reads the newest change-log entry). Added a **slim app-wide footer** (© Care One OS · version | Support/Privacy/Terms | "All systems operational" status pill). Fixed washed-out `dimmed` subtitles on the new canvas by moving key labels to a readable slate (`#586A85`). Change-log entries now also show **before → after** ("was: …") and a **"· N entries"** count in a scroll area; donut legend rows made clickable filters.

- **Resolution change log / audit trail.** New table **`medication_dose_review_logs`** (+ model, migration run in isolation via `--path` so it didn't disturb the dump-loaded schema) records **every** resolve / edit / undo with the **user, timestamp, and a snapshot** of the clinical action + notes at that moment (resolve auto-detects "resolved" vs "edited" via `wasRecentlyCreated`; undo snapshots then logs "removed" before deleting). The detail panel shows a colour-coded **Change log** timeline, and each edit shows a **"was:"** line with the previous action + note (before → after). Doses resolved before the table existed show a fallback line. The **Download audit** CSV gained a **Change log** column with the full per-dose history. Verified end-to-end (resolve → edit → log shows both entries + the before value).

- **Agreed a "finish 4 pages properly" plan** (Missed → Controlled drugs → Stock → Med round, then Shift Handover): drive each page to a "done" tier (every button works, responsive at all sizes incl. 14" laptop zoom, hover/active/focus states, soft design, dark mode) one at a time. Superseded/trial variants are **parked in a new "Duplicates" sidebar dropdown** (not deleted). Placeholder data for now (real meds dictionary/#17 later). Plan saved to memory.
- **Missed doses = Page 1, promoted to canonical** `/frontend2/missed-doses` (the new designed page replaced the old basic one; old `-b` route kept as an alias). Added interaction polish: **clickable stat cards** that filter the log (hover-lift + active ring + tooltips), tab hover, date-nav tooltips, keyboard focus.
- **Controlled drugs = Page 2, rebuilt in place** to the same standard (soft cards, clickable stat filters, pill tabs + search, scrollable register with hover, right rail = By action bars / witness-compliance / Audit ready, CSV export, responsive + laptop zoom).
- **Dark-mode teal accent (navy + teal + white).** Owner wants dark mode to echo the Care Copilot card (navy card, teal button, white text), and flagged that selected states weren't showing (the selected tab was navy-on-navy, invisible). Added brand teal (`#45C1BF` tint / `#1F9E93` fills, text `#6FE0DB`) as the **dark-mode interaction accent**: selected tabs now get a teal tint + teal ring + teal text in dark; primary CTAs ("Export report", "Add entry") go teal in dark to match the Copilot button. Light mode unchanged (white pill on warm beige). Status colours (overdue orange / resolved green / refused rose) kept for clinical meaning. TODO: the rose "Start follow-up" button is still a bit faint in dark.
- **Warm "cream" light theme.** Owner chose `#F6F2E8` as the light-mode background — applied to the page **canvas** (`frontend2/Layouts/AppShell.jsx` `CANVAS`), the **body** (`resources/css/app.css`, newly imported from `app.jsx`, scoped to `[data-mantine-color-scheme='light']`), and the **top bar** (Header). To keep hover/selected states visible and on-theme, swapped the cool greys for warm tints across Missed doses + Controlled drugs: tab track `#F1F3F7`→`#EFE9DC`, tab hover `#E6EBF2`→`#E7DFCD`, row hover `#F8FAFC`→`#FBF6EC`. White cards float on the cream; dark mode untouched.
- **Fixed the "logged out on every change" bug.** Root cause: the app enforces one-session-per-user via `users.session_token` vs `csrf_token()` in `app/Http/Middleware/checkUserAuth.php`; `/dev-login` overwrites that column each run, so the verification screenshots (which log in as the same demo user) kept stealing the human tester's session and bouncing them to `/login`. Fix: the token-mismatch logout is now skipped when `app()->environment('local')` — production still enforces single-session. Verified with a two-browser test (session B steals the token, session A stays logged in → PASS). Note: still no Vite dev server (no HMR) — changes need `npm run build` + refresh unless `npm run dev` is running.
- **Fixed dark mode properly (ISSUE #24).** The old dark mode used Mantine's default grey/black (no relation to the brand) — "forced black". Added a **navy-tinted `dark` colour scale** to the theme (`frontend/theme.js`), derived from the light palette the owner shared (BACKGROUND #FFFFFF, SURFACE #F5F5F5, PRIMARY #1C325A, SECONDARY #FF9800, TEXT #212121): canvas `#0C1524`, card surface `#18243D`, borders `#2B3B57`, text `#E7ECF4`. One theme-level change re-tints **every page's** dark mode to a cohesive deep navy at once.

- **Split B / Split C right-rail iterations.** Refined the redesigned rail on both split trial pages: softened the Quick-action tiles (muted slate icons on soft neutral chips + drop shadows, no sharp per-category colour), moved to a **2×2 → single-row of squares** layout, swapped the icons to **Iconsax** (`iconsax-react`, installed; TwoTone variant), and made the whole rail **responsive** via a dedicated `railBelow` (≤1000px) breakpoint — full-width un-zoomed below the list on tablet/phone, tight zoomed side-rail on desktop (B at 0.75, C at 0.5625). Icon-library choice logged as ISSUE #25 (decide one set app-wide, drop unused deps). Verified with headless screenshots at 375/800/1440.
- **New "Missed & refused doses" page** (`/frontend2/missed-doses-b`, "Missed doses (new)" in the sidebar) — rebuilt from the owner's CLINIK-style mockup with Split B's soft styling. Header (soft red chip + title + "date · home · N need follow-up") with date nav + **Export report** (client-side CSV); four **stat cards** (Refused / Omitted / Overdue unresolved [orange-highlighted] / Follow-ups pending); a **Log** with client-side filter tabs (All / Refused / Omitted / Overdue), avatar + room, medication + friendly reason, when, and a soft outcome badge; a right rail with **By reason** bars, a **needs-follow-up** card (Start follow-up → resolve modal) and an **Audit ready** card. Fully responsive (rail drops full-width ≤1000px, stat cards 2/4 cols, log columns collapse). Wired to the existing missed-doses data (new controller `indexFrontend2MissedB` loads the full list + home name so tabs/stats derive client-side; added `room` to the shared payload); resolve reuses `ResolveDoseModal` posting to a B-specific endpoint. Builds + PHP lint clean. NB the demo only shows **Overdue** rows (never-given doses) until real Refused/Omitted admin records exist.

- **~14:55 — Split B right-rail made bigger on wide screens.** The redesigned right rail was scaled down uniformly (`zoom`) so it looked too small on larger monitors. Bumped it from **0.75 → 0.9** (and raised its `maxWidth` 350 → 420 so the now-larger boxes aren't clipped). Only affects desktop (>1000px); below that the rail still drops full-width and un-zoomed. `resources/js/Pages/Frontend2/MedicationRoundSplitB.jsx`.
- **~14:55 — More breathing room between the two Split B columns.** Increased the gap between the "Residents to give meds" list and the right rail from **22 → 40**. (Because the container wraps, this also becomes the vertical gap when the rail drops below the list on narrow screens.)
- **~14:55 — Sidebar colour experiment: light rail option.** Tried the owner's idea of a **white/light sidebar** instead of the navy gradient. Reworked `frontend2/Layouts/AppShell.jsx` so the whole sidebar look is driven by one switch, **`SIDEBAR_MODE`** = `'navy'` (original deep-navy gradient, light text), `'warm'` (soft warm-white rail `#FBF8F1` + warm border) or `'cream'` (same cream as the page, separated only by a subtle divider — seamless). Light modes flip nav text/icons to dark slate, tint the active pill (indigo) and hover (faint navy wash) instead of the white-on-navy versions (which would vanish on white), switch the logo to the navy wordmark, and keep the Care Copilot card navy so it still pops. New `.lightNav` overrides added to `AppShell.module.css`. Currently left on **`'navy'`** (original look) — the light variants are parked behind the switch for the owner to try. Affects every Frontend2 page (shared shell).

## 2026-07-02

- **Started a second app "shell" (`frontend2`)** — a separate sidebar/layout that lives inside the *same* app + login (not a separate front-end). You reach it from a **"Frontend 2"** link in the main sidebar; it renders with its own sidebar, and a **"Back to main app"** button returns you. Under the hood it's just a second layout (`frontend2/Layouts/AppShell.jsx`, alias `@frontend2`) that pages opt into — clean, not messy.
- **Styled `frontend2` on the "CLINIK" clinic mockup the owner shared** — a full-height **blue→indigo gradient sidebar** (logo, nav, bottom card), a light **lavender canvas**, and a **white title bar** over the content (Mantine `layout="alt"`). Nav: Dashboard, Residents, Medications, + placeholders (Scheduled visits, Statistics, Reports, Settings).
- **Built a Residents section (real data)** modelled on the mockup's *Patient profile*:
  - **Dashboard** (`/frontend2`) — headline counts (residents, active meds, PRN, controlled) + entry cards.
  - **Residents list** (`/frontend2/residents`) — searchable cards (photo, age/gender, room, med count).
  - **Resident profile** (`/frontend2/residents/{id}`) — photo + contact, **General information** (DOB, gender, room, NHS, address, registration), **Health & care** (allergies chips, diet, mobility, weight), a tabbed **Medications** list (All/Scheduled/PRN), and Files/Notes placeholders. Print button works; Edit is a placeholder.
  - **Medications** (`/frontend2/medications`) — every active prescription with resident link, PRN/CD badges, stock, and All/PRN/Controlled filter.
  - Back-end: new `Frontend2Controller` (index/residents/resident/medications) reading the same live `service_user` + MAR data; routes `frontend2.*`. Resident fields read defensively (Eloquent returns null for any column the DB doesn't have).

- **Rebuilt the Medication Round page inside `frontend2` from the owner's mockup.** Copied **only the sizing/spacing/padding** from the mockup CSS (page padding, card radius 22 + soft shadow, section margins, two-column gap, rail width) — **not** its colours or content — then made the whole page **extremely responsive** (scrollable round tabs, dot-only status, stacking buttons at 768/576px). Added the **Care One logo + navy gradient** to the `frontend2` sidebar, a **"Care Copilot"** card, and a temporary "back to main app" link.
- **Matched the shared header to the mockup** — a boxed bell (white rounded chip + orange dot) and a bare "P" avatar — **while keeping the avatar dropdown working** (name / dark-mode toggle / log out).
- **Three interaction styles for the round, so the owner can pick a favourite** — all feature-equal, only the *transition* differs:
  - **V1** (`/frontend2/medication-round`) — **in-row reveal**: click a resident and the row slides aside to show their details + meds *beside* it (not a drawer, not underneath).
  - **V2** (`/frontend2/medication-round-v2`) — **click-through**: opens a full per-resident administration page (teal theme, Plus Jakarta Sans) with a proper **signature modal** (barcode field, reason drop-down, notes, signature pad, witness).
  - **V3 "Split"** (`/frontend2/medication-round-split`) — **master–detail**: the table shrinks to a side list and the chosen resident's details open next to it, other residents still visible. Built as a **full clone of V1** (filter tabs, Today donut, Alert, Recent-activity rail, End/Pause/Re-open, record modal) — the split is the *only* difference. Has its own record/end/reopen endpoints so recording keeps you on the page.
- **Universal dark mode** across V1/V2/V3 via Mantine `light-dark()` tokens (text, cards, inner panels, dividers, the signature modal). Logged **issue #24** to give dark mode a proper dedicated palette later.
- **Surfaced dose notes** (issue #13) — a recorded dose now shows the chosen **reason**, the typed **note**, the **witness** and **who recorded it**, right under the medicine (on V1, V2 and V3).
- **Added a "View profile" button** (on-brand teal pill with a person icon) to each variant's resident detail, linking to the full resident profile (`/frontend2/residents/{id}`).
- **Made V3's split transition more polished** — a springy easing curve, the detail panel now **slides in + fades + scales** from the list edge, its contents (header → allergies → info chips → each medicine) **cascade in one after another**, switching residents replays the whole thing, and the selected list row gets a teal accent + slight nudge.
- **Tidied V3's right rail** — narrowed the three boxes (Today / Alert / Recent) **without** widening the left column, shifted them right so the spare space sits in the **middle gap** rather than at the page edge, and cleaned up each box's internals (donut size, spacing, safe truncation) so nothing looks squished. Also **matched V3's Alert box styling to V2's** (white card + peach border + orange icon-chip + "Resolve now" button).
- **Noted future integrations in the issues list** so they're on record before we build: **#17 dm+d medicines dictionary/formulary**, **#18 GP Connect** (syncs coded meds/allergies from the GP), and a note on **#16** that a dose's *next-due* should later be **calculated from the actual administration time + the med's dosing interval** from the dictionary (so a late dose shifts the schedule instead of stacking) — flagged as a placeholder until #17/#18 land.

### Later that day — the "carer flow" / administer experience on V3 (split)

- **Built a new `AdministerModal`** from the owner's Care One OS mockups (the `administer-modal.html` + "Medication round carer flow" zip). It's the full administer form: coloured avatar header (med + resident·room·dose·route), an **amber safety banner** for controlled/high-risk meds, **eight outcome chips** (Given, Refused, Missed, Held, Delayed, Vomited, Resident away, Self-administered) each in its own colour, a conditional **Reason**, a **Witness** picker with a teal **co-sign alert**, notes, a **tap-to-sign** signature, an audit-stamp footer, and a smart **Confirm & sign / co-sign** button that stays disabled until valid. Uses the mockup's Hanken Grotesk font.
- Added a temporary **Classic / New** toggle in V3's header so the owner can compare the new modal against the old form side by side. (Caveat logged: the 8 outcomes map to the 6 MAR codes, so the exact outcome is preserved in the reason — proper fix is extending the code set with the dictionary, #17.)
- **Transformed V3's detail pane into the "resident meds card"** and matched it to the mockup CSS token-for-token: light pill-box icon, med name / dose line, a single **Administer** button (opens the modal), and — once recorded — a coloured **outcome badge** + a **"Given/Recorded by"** line + a green **"✓ Witnessed by"** line + a **note box**. Added a **progress bar** ("X / Y done", amber→green) to the header.
- **Reason as a dropdown** — for **Refused / Missed** the reason is now a preset dropdown (our refusal/omission lists) with an **"Other"** option that reveals a free-text box; the self-describing outcomes (Vomited/Held/…) don't force a reason.
- **Made the specific outcome visible** — the badge now shows exactly what was chosen (Vomited/Held/Delayed/Resident away/…), **each in a distinct colour**, instead of a generic "Omitted". The **selected reason shows first (coloured to match the outcome)** with the **note underneath** and a **timestamp beside** it.
- **Reworked the info fields** (Age/Gender/NHS/Weight/Mobility/Diet) into a **segmented strip** — uppercase label over bold value, a DOB sub-line under Age, split by vertical dividers — then scaled it down with rounder corners.
- **Recent activity no longer grows the page** — it's capped to ~4 rows with a scrollbar (keeps the latest 30).
- **Right-rail polish** — the three boxes were narrowed and shifted so the spare space sits in the middle rather than the page edge; the **Alert box** was matched to V2's styling; the note box was made visible (it was too faint); **Administer + End round buttons** set to dark navy; and a **subtle divider line** now separates each medication row.
- *All committed by the owner (latest commit: "implement AdministerModal…"). **Next time:** V3 (split) is the lead candidate — decide whether to keep the Classic/New toggle or drop the old form, and revisit the 8-outcome → 6-code mapping when the dictionary (#17) lands.*

## 2026-06-30

- **Got the four "4.1" screens demo-ready and tested them end-to-end.**
  - **Demo grouping:** added a **"Demo · 4.1"** group at the top of the sidebar listing the four screens in click-through order — **1 Medication Round → 2 Medication Stock → 3 Controlled Drugs → 4 Missed Doses** — so they can be walked through in sequence.
  - **Made the data actually show:** the `dev-login` shortcut was logging into an empty home (home 1). Pointed it at the **demo home 101 = "Neptune House"** (where the seeded residents/meds live), preferring a manager. Added a **home-name chip** at the top of Medication Round 4 so it's clear which home you're in.
  - **Fixed a blank-page trap:** with no `npm run dev` running, `@vite` served built assets from `/build/...`, but this app is served from the project root where they live under `/public/build/...` → every Inertia page went blank. Patched `serve-local.php` to map `/build/*` → `public/build/*`, so the built assets load with **no dev server needed** (workflow: edit → `npm run build` → refresh).
  - **Tested the cross-screen links live (verified in the DB):** (1) give a dose on the round → **stock auto-deducts** + audit transaction; (2) refuse a dose → shows in **Missed Doses** as outstanding, resolve → it clears; (3) give a **controlled drug** → **witness enforced**, stock deducts; the CD **register entry** auto-balances; (4) **receive stock** → level rises past reorder → **low-stock alert clears**. All four passed.
  - **Small improvements found while testing:** the round now **shows the reason / note / witness** on a recorded dose; **Missed Doses table is sortable** (click any column; default newest-time first); the **Add-CD-entry form auto-fills** schedule, unit, dose and both balances from the prescription (balance-after is read-only/auto-calculated); the **Adjust stock** button is always visible on Stock 4.1 (with a "View only" badge for carers).
  - **Logged as issues for later (`docs/ISSUES.md`):** #12 alerts should be clickable + navigate (all 4.1 rails); #13 surface dose notes (started on Round 4); #14 witness should *confirm via a notification*, not just be typed; #15 CD unit is guessed from free-text (fix with a meds dictionary); #16 enforce an administration **time window** (no giving too early/late; honour the min gap between doses).
- **Deleted the "Medication Round 3" screen entirely** (the dashboard-style trial built earlier today) on request — removed the page (`MedsRound3.jsx`), its four controller methods, its routes, and the sidebar link. Medication Round 4 is unaffected. *(The "built Medication Round 3" note below is kept as history.)*
- **Built two new "4.1" medication screens** — a **Controlled Drugs 4.1** register and a **Missed Doses 4.1** review — styled to match Medication Round 4 / Meds Stock 4.1 (cream panel, **Fraunces serif** headings, soft white cards, a health/progress **donut**, status **pill-tabs** that filter, and **alerts + recent-activity** side rails). Both read the **same live data** as the existing `-react` pages, so the four 4.1 screens stay in sync, and they **cross-link** to each other from their Quick Actions (Round 4 ↔ Stock 4.1 ↔ CD 4.1 ↔ Missed 4.1).
  - **Controlled Drugs 4.1** (`/medication/controlled-drugs-4-1`): append-only register with a per-action breakdown (Administered/Received/Disposed/Returned/Adjustment), +/− dose flow, running balance, a witness check per row, and compliance alerts (entries missing a witness, low running balances). Adds entries via the shared Add-entry modal, which now posts back to the 4.1 page.
  - **Missed Doses 4.1** (`/medication/missed-doses-4-1`): missed vs not-given review with date navigation + Outstanding/Resolved/All filter, a resolved/outstanding donut, follow-up alerts, and the shared Resolve modal (now posts back to the 4.1 page).
  - Pages: `resources/js/Pages/Medication/ControlledDrugs41.jsx`, `MissedDoses41.jsx`. Back-end: new methods on the existing Controlled-Drugs and Missed-Doses controllers (shared payload helpers so nothing duplicated) + routes. The two shared modals (`AddCdEntryModal`, `ResolveDoseModal`) gained an optional `action` prop so each page submits to and returns to itself. (Note: a pre-existing JSX error in the unrelated `MedicationRoundLab142.jsx` shows during `npm run build` — not touched here.)
- **Tidied the code formatting** on the recently-changed medication files (the 11 files touched in the last few commits) using the standard formatter — purely whitespace/import-ordering, no behaviour change. Spotted a long-standing typo in the routes file (`fApp\...BmpController` — a stray "f") that breaks the `route:list` admin command; left it untouched pending the go-ahead to fix.
- **Built a new "Medication Round 3" screen** — a dashboard-style take on the round, modelled on a reference mockup but recoloured to our own brand. It shows: six summary tiles (Due Now, Overdue, Missed, PRN Given, Low Stock, Controlled Drugs), a five-step progress strip for the round (Not started → In progress → Paused → Review → Completed), and a **resident table** where each row shows age, room, due-med count, allergy/Warfarin flags and a progress bar — click a resident to expand their medicines and record them (one-tap *Given*, or *Refused/Omitted* with a reason). **Start round / Pause / End round / Re-open** wired to the real round-closure logic; managers can re-open. Uses the **same live data** as the other round pages.
  - Address: `/medication/medication-round-3` (in the sidebar as "Medication Round 3"). Page: `resources/js/Pages/Medication/MedsRound3.jsx`; back-end methods + routes added to the existing Medication Round controller. Later scaled the whole page content to 75% (single `CONTENT_SCALE` knob) on request.
- **Built a second new round screen, "Medication Round 4"** — a warm/editorial look based on two reference dashboards the owner shared (Crextio + a learning app). Cream panel, **Fraunces serif** display headings and big numbers, a yellow accent, soft white cards. Shows: round **pill-tabs** (Morning/Lunch/Evening/Night), three big serif stats (Residents / Doses due / Given), a **resident table** (avatar, age, room, due count, progress bar, status pill — click to expand and record), a **round-progress donut** (Given / Outstanding / Refused, with the scheduled-dose total in the middle) and a **Schedule timeline** of the round's doses by time (next-due highlighted). Same live data + record/end/re-open actions as the other round pages; managers can re-open.
  - Address: `/medication/medication-round-4` ("Medication Round 4" in the sidebar). Page: `resources/js/Pages/Medication/MedsRound4.jsx`. Deliberately uses its own warm palette (not the teal Care One tokens) to match the references — easy to reconcile later if we want one house style.

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

### Functionality pass — making every button work (same run)

- **Brought the live Medication Stock page up to the Lab 2 standard** (it had been the simpler original): segmented tabs (Overview/Transactions/Reorders/Disposals), **tick-box bulk select** with a teal selected-row bar, medication-type icons + form/route/strength, colour-graded stock bars, sortable headers, hover row actions, "last activity", a richer **view-history drawer** (route/form/batch/supplier/last delivery), an **"Updated X ago"** stamp, and inline search + filters + Export. Also fixed the table so it **fits the card neatly** (capped width, centred, columns hide on smaller screens / when the sidebar opens) and turned the summary cards into **clickable filter pills**.
- **Audited every button on all five medication screens** and wrote it up: `docs/medication-functionality-plan.md` (per-page map + build plan) and a step-by-step **test checklist** `docs/medication-test-plan.md`.
- **Built the 7 dead-end buttons** the audit found — all now work:
  - **Export to CSV** on Controlled Drugs, Missed Doses and Stock (downloads exactly what's on screen; Stock also exports just the ticked rows).
  - **Stock "View history"** (drawer) and **inline Status/Stock/Expiry filters**.
  - **Round "View Profile"** — a resident summary drawer + a link to the full legacy record.
  - **Round "Temporary Absence"** — mark a resident away for a date range; their scheduled doses are recorded as omitted with a reason (new server endpoint) so they don't pile up as "missed".
  - **Round "MAR Report"** — a printable medication chart (medicines × days) in a new tab.
  - **Round "Scan Medication"** — a manual stand-in (type/scan a name → find the dose → confirm); camera left for later.
- **Walked the save path of every screen end-to-end** (clicked through the code from button → server → database). Confirmed each write is **validated on the server, limited to the logged-in care home, and stamped with who did it**. Notable: controlled-drug entries **require a witness**; the **balance now auto-calculates** (in/out by action) to avoid maths slips; resolving a missed dose can't create duplicates; handover acknowledge only works on submitted ones.
- **Result: no dead-end buttons left** on any of the five screens — everything either works or is clearly marked "coming soon". The only outstanding items are small product decisions (bulk Transfer/Archive/Print on Stock, editing a review after it's resolved, editing/submitting a saved handover draft), captured in the plan doc.

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

## 2026-07-14 — Stock 2: finished the feature checklist + polished the tables

Worked entirely on the **Stock 2** page today. In plain English:

**Made the whole feature list work and proved it.** We went through the acceptance checklist (`docs/STOCK-TEST-LOG.md`) one item at a time. Because I can't click through the browser myself, I ran small **safe backend tests** (they change nothing — every test is rolled back afterwards) against the real database to prove each feature actually works:
- **Adjust stock** — receiving adds stock, disposing removes it, notes save, a batch is created. ✅
- **Batch / lot tracking + FEFO** — receiving creates a batch; disposing draws down the earliest-expiry batch first. ✅
- **Stock count** — entering a counted number posts a correction to that exact figure, writes an audit note ("was X, counted Y"), skips items with no change, and blocks controlled drugs without a witness. ✅
- **Reorder** — place an order → it shows in "on order" → receive it (stock goes up, batch created, order closes) → or cancel it (no stock change). ✅
- **Barcode** — the code saves against a medicine and the header scan box jumps to it. ✅

**Fixed the "nothing happens" problems.** The disposal/adjust forms *were* saving all along — the issue was there was **no feedback** (no success/error message) and typed **notes weren't shown back** on the page. Added success/error toasts to both forms and made notes appear in the History/Transactions lists.

**Rebuilt the "Adjust stock" button properly.** It used to just grab the first medicine in the table. Now it opens a proper **pick-a-resident → pick-a-medicine** box (like the disposal one) with the full adjust form. The same form is now shared between that box and the medicine detail panel, so there's only one copy to maintain.

**Tidied the medicine detail panel** (opens when you click a row). It was one very long scroll; now it's split into **Overview / Adjust / History** tabs so nothing runs off the screen.

**Polished the adjust form** — rounder corners, grouped the batch fields and controlled-drug fields into soft tinted boxes (teal for a new batch, muted purple for controlled drugs), a little colour so it's easier to read, and made it more compact.

**Tables now match across all the tabs** — added column headers to Transactions, Stock count, Reorder and Disposal, and gave every table the same airy, right-grouped column spacing (one shared setting). On the Stock count tab specifically, moved the Expected/Counted/Difference columns across so Difference sits under the "Post corrections" button (tuned for a wide screen).

**Left to finish on Stock 2 (small):** a couple of purely visual click-throughs in the browser (disposal success message; the reorder inline forms), and the Stock count column position is tuned to a wide monitor so it'll shift on narrow windows if we ever care.

---

## 16 July 2026 — we audited the Medication Round before rebuilding it, and found a lot

The owner asked for something sensible before any building started: have the agents check **every function and every object**, prove nothing clashes, and leave nothing out. Five audits ran in parallel — backend, frontend, workflow logic, the database, and the joins between them. Everything is written up in `docs/care-one-os/ROUND-AUDIT-2026-07-16.md`, with the proposed responses in `ROUND-FIX-PLAN.md`.

**The big realisation:** the Medication Round isn't one page, it's **18 pages sharing one save routine**. So every fault in that save routine exists on all 18 at once — and a shiny new page built on top would inherit all of them. **The fixes have to go underneath the page, not into it.** That changed the plan.

**Nine serious faults.** The worst four:
- **The "as needed" (PRN) limits can't actually work.** Every PRN dose is saved against the same fixed time slot, so the second dose of the day **overwrites the first**, and the check that counts earlier doses skips that very slot — so it always finds nothing. The 4-hour rule and the audit trail are broken by the same root cause.
- **One tap can wipe a medicine's stock.** The amount to deduct is worked out by stripping the letters out of the dose text, so `"10 ml (500mg)"` becomes **10500**. Tested for real: stock went 56 → 0, silently.
- **Any resident with a risk flag crashed the whole page** — and there was **no safety net anywhere in the app**, so it took the screen down completely.
- **"Sleeping" counted as GIVEN.** A child who was asleep and got nothing used up their PRN allowance and started the 4-hour clock. For a controlled drug it skipped the witness, the reason and the stock deduction entirely.

**Two of these were ours.** The paracetamol doses we added yesterday (`10 ml (500mg)`) are exactly the format that breaks the stock maths — the old data said "1 Tablet", which hid it. And the PRN demo we seeded was proving the wrong thing: it inserted doses at realistic clock times, which is *not* what happens when a carer taps the button. Honest lesson: **making the dummy data realistic is what exposed the real bugs**, which is a point in favour of doing it.

**Fixed today (the small, safe ones — no database redesign needed):**
- The risk-flag crash, and a proper **safety net** so one bad row can never blank the screen again mid-round.
- **Weight, form and unit now actually save.** We'd added the columns and the page read them, but nothing wrote them — values were silently thrown away. Verified with a real (rolled-back) test.
- A **privacy leak** on the printable MAR chart: it would show another company's resident's name and date of birth. Now locked to your own home.
- **Deleted the "Pause" button.** It did nothing at all — it changed a word on screen while a carer could carry straight on recording, and so could a colleague. A control that lies is worse than none.
- "Preview as carer" now actually hides the manager-only button on this page.
- Speed: three tables had no indexes at all, including the one that separates companies.

**Decisions the owner made:** weight will be **kg only, pounds refused** (two residents are recorded in pounds right now — read as kg that's a 2.2× overdose). Medicine amounts will come from the **dose form** (tablets in tablets, liquids in ml) instead of being typed, so counting in matches counting out. And **"Withheld" and "Omitted" are being scrapped** — they're confusing because they mix up *what happened*, *why*, and *who decided*. "Omitted" especially is a dustbin that lets a real error be papered over with one tap.

**Also done today — the two structural decisions:**

**Controlled drugs now follow the house type.** Aries is supported living, which is someone's own home, so a second signature isn't automatically required there — but the code demanded one everywhere. It now checks the house. The important bit is what happens when a house has *no* type set (12 of them don't): it **requires the witness anyway**. Being too strict is an inconvenience; being too lax in a care home is a real risk. It means the unset houses aren't blocked while the owner works through them one by one.

**The shared logic moved out into a reusable piece ("a trait").** The plan had been for the new Medication 2 page to *inherit* from the old controller. That turned out to be a trap: it would also inherit all 62 methods and all 18 pages' worth of surface, so one edit for the new page could quietly change every old page at once. It now shares only the round-building and dose-recording logic, and each page keeps its own front door. To prove nothing shifted, we took a fingerprint of exactly what the round produces before the move and again after — **identical, to the byte**. Then re-tested the round's four safety rules: all still working.

**Then we fixed the two worst faults properly.**

**The stock bug is dead.** How much to take off stock is now a proper number stored against the prescription, not something worked out by pulling the letters out of the dose text. `"10 ml (500mg)"` now takes off **10**, not 10,500. And if a carer types nonsense into the "dose given" box, it makes no difference at all any more — the typed text can't touch the quantity.

We were deliberately cowardly with the old data: the automatic fill-in only touched doses it could read with total certainty, and left 5 alone. A NULL means "don't touch stock" — wrong, but honestly wrong, and it can't destroy a balance. We then set those 5 by hand with the reasoning written down. One of them is a genuine trap: **"1 spray each nostril" uses TWO sprays, not one.** No automatic reader would ever have got that right.

**Liquids now count properly.** Stock could only be whole numbers, so a 7.5 ml dose quietly rounded. Four of Sofia's doses took 60 ml down to 32 when it should be 30 — 2 ml of medicine that doesn't exist, every single day, on one child. Now it's exact.

**The "as needed" limits actually work now.** Every PRN dose used to be filed under the same fixed time, so the second dose of the day *overwrote the first* and the safety check could never see any earlier doses. Each dose is now its own record with its own real time. Tested the way a carer actually works: four doses go through, the fifth is refused ("daily maximum reached"), a dose straight after another is refused ("not due until 19:13") — and editing a note no longer restarts the clock and locks a child out of their pain relief.

**Something the testing caught that we'd have shipped.** Fixing the above exposed a second bug hiding behind the first: the "don't take stock off twice" guard also went by that same fixed time, so PRN doses 2, 3 and 4 looked like edits of dose 1 and took **nothing** off stock. A child could have four doses and the cupboard count move once. Only visible because we tested it the way a carer works rather than the way the code was written.

**Everything re-checked afterwards:** 7 out of 7 safety rules still pass, and the round produces identical output to before. Nothing was taken on trust.

**Then we checked our own work, and most of it didn't survive.**

The owner asked for the same agents to be turned on our own fixes. They found four serious faults in them — and the worst one is that **the "as needed" safety limits can't be set through the app at all.** There's no field for them anywhere. The only reason they exist in our demo is that the seeder writes them straight into the database. So every prescription a real user creates has no daily maximum and no minimum gap — and because we'd changed those doses to record separately, a double-tap now recorded **two doses and took stock off twice**. Before the change it recorded one. **We made the real path worse and told the owner it was fixed**, because we only ever tested the one dataset where it works.

Same story three times in one day: **we kept checking against a database we'd already arranged to be right, instead of against what the app actually does.**

**An accident proved the point.** While setting up a clean test database, the backup file turned out to name its own database inside it, so restoring it overwrote the working one instead. Nothing was lost — it's all migrations and a seeder, both re-runnable — but rebuilding it from scratch immediately exposed a fault the agents had predicted on paper: **the seeder never recorded the time a dose was given**, so on a clean rebuild the "too soon, not due until 7:39" block quietly stopped working. It had only ever appeared to work because a later step happened to fill that gap in afterwards. A second paracetamol an hour after the first went straight through, silently.

**So now there's a proper test file** (`tests/Feature/MedicationRoundSafetyTest.php`) — 14 checks covering the whole write path. Three rules baked into it:
- **Build the test the way the app does.** If a prescription can only be made safe by a database write no user can perform, the safety rule doesn't exist.
- **Check the side effects, not just the happy path.** Every dose test counts the rows *and* the stock, because that's where the hidden bug was.
- **Prove a guard by breaking it**, not by watching it not complain.

12 pass. **2 fail — and they're meant to.** They are the two real bugs, and they'll keep failing until those are fixed rather than quietly forgotten.

The seeder now also **refuses to finish** if its own demo data doesn't demonstrate what it claims — no more fixtures that silently prove nothing.

**One landmine found on the way:** the existing tests run against the *live* database, and three of them use a setting that wipes it. Anyone running the full test suite today would destroy the working data. Needs a separate test database before that happens by accident.

## 23 July 2026 — a failed dose stops looking like a saved one, and the "as needed" block becomes visible

Two things the carer actually sees.

**A failed recording no longer pretends it worked.** Deep down, when a dose couldn't be saved (a prescription that doesn't belong to this house, say), the code quietly returned "not found" — and every page turned that into a page-refresh the screen read as *success*, so the box cleared and closed as if the dose had gone in. It now raises a proper error instead, in one place that covers all the pages at once. On screen: tap "Given" and if it fails you now get a red "Not recorded" message with the reason, and the box stays open. The same is true inside the record pop-up and for the End/Reopen round messages, which were being sent but never shown.

**The "as needed" limit is now visible before you tap, not after.** The daily count, the next-due time, and the reason a dose is blocked were all being worked out on the server and thrown away. They now show on the medicine line — "2/4 today · next due 19:39" — and when a dose isn't due yet the "Given" button is greyed out with the reason on hover. This matters because the limit now actually fires; a silent block would just be a button that does nothing.

**The duplicate round pages are tidied, not deleted** (as asked). Every old experimental version of the round — all the "lab" ones and the v4 ones — is now tucked into the single "Duplicates" drop-down in the new menu, next to the ones that were already there. They still show "Sleeping" as given on screen, but the machinery underneath them is the corrected one, so what they *record* is right.

**Caught another rotting fixture, and killed it properly.** Two of the safety tests were passing only because the demo data had been re-created that same day — leave it a week and they quietly stopped testing anything. Those two tests now build their own same-day data, so they can't go stale. Proved it by deliberately ageing the demo data a week and watching them still pass. The suite is 15 checks now, and the two that are *meant* to fail (the unfixed bugs) still do.

**One honest wobble:** while setting up a separate test database, a backup file named its own database inside it and overwrote the working one. Nothing was lost — it all rebuilds from code — but it's a reminder to read a dump's header before restoring it.

---

## 23 July 2026 (later) — the industry check, and a bug in our own homework

We ran the two agents over the whole design against current official sources: one to gather what the rules actually say, one to judge our decisions against them and hunt for over-claims in our own paperwork. Both were told to trust nothing we'd written.

**They caught our documents contradicting the real sources — including a mistake we'd made an hour earlier.**
- Our register said the main "medicines in care homes" guidance (NICE SC1) was **adult-only**. Verified against the actual guidance: it explicitly covers children's homes too. We'd even acted on the wrong label — a warning note added earlier that day repeated it. Both are now corrected.
- The rule that a controlled drug needs a second person to witness it — we'd written it up as if it were **the law**. It isn't; it's strongly-expected good practice, and the regulations themselves name no such witness. Corrected to say exactly that, so nobody mistakes a good-practice choice for a legal duty.
- A citation pointing at a standard number that **doesn't exist** ("STD-17") — a typo for a real one. Fixed.
- The actual law covering whether a 16- or 17-year-old can consent to their own medicine (the Family Law Reform Act 1969) was **missing entirely** from our list. Added. The harder question — what happens when such a young person *refuses* — is flagged for a real lawyer.

**One thing they let us fix on the spot, and we did:** a "Withheld" dose no longer records with no explanation. Withholding a prescribed medicine is a deliberate decision, so the record now has to say why — same bar as refused or omitted. On screen the reason box appears; the server rejects a blank one. Test added, 16 checks pass.

**The honest headline from the compliance side:** what we've built is careful safety engineering, but it is **not** something an inspector could look at as evidence of compliance today. The biggest single gap they named is one we already knew about — when a dose record is corrected, the old version is still silently overwritten with no trail of who changed it or why. For a regulator that's the first thing they'd look for. It's on the list, not done.

**Confirmed still open** (nothing hidden): the overwrite-without-a-trail problem; the manager who can only see their first house; the medicine dictionary that would make "Warfarin as a controlled drug" impossible doesn't exist yet; and the consent rules for 16–17-year-olds must not go live until a lawyer has ruled on them.

**Two licensing facts worth knowing for later:** the dosing reference (BNF/BNFc) that a "this dose looks too high" feature would need is **commercially licensed** — it can't just be embedded; that needs a paid agreement. And the medicines dictionary (dm+d) is free to register for but its exact redistribution terms for a SaaS still need someone with an account to read the small print.

---

## 23 July 2026 (later still) — corrections now leave a trail

Fixed the biggest thing the compliance check flagged: **a corrected dose record no longer erases the original.**

Before, if someone recorded a dose as "Given" and later fixed it to "Refused", the "Given" entry was simply overwritten — gone, with no record that it had ever said that, who wrote it, or why it changed. That's the first thing an inspector looks for, and it was the single worst gap we had.

Now a correction keeps both. The original stays in the table, marked as an old version, with the time it was replaced; the correction is a fresh entry that points back to what it replaced and can carry a "why". Nothing is ever deleted. The screens still show only the live version — the history sits underneath for audit. And tapping the same thing twice by accident doesn't clutter the history, because an identical re-entry changes nothing.

To make sure no screen accidentally shows an old version, the rule is enforced in one central place rather than page by page — this codebase has been bitten too many times by a single reader that got missed. Proved it end to end: record Given, correct to Refused, and the round page shows Refused with exactly one live entry, while the original Given is still there in the history.

**Also done:** "Withheld" now needs a reason, same as refused or omitted — withholding a medicine is a deliberate decision and the record has to say why.

18 checks pass, build clean, and the change safely re-runs from scratch. The rollback deliberately refuses if any history exists, rather than quietly flattening it.

**Not touched yet, on purpose:** a database-level guard against two identical entries racing in at once. The obvious version of that would wrongly block a legitimate second "as needed" dose (they share a nominal time), and the app already serialises those writes, so it needs a more careful design — flagged, not forgotten.

---

## 23 July 2026 (end of day) — the new Medication Round page is built

After a week of fixing the foundation, the actual page exists. The owner picked the **focused one-resident flow** from three mockups (shown as an interactive comparison first).

**What it is:** one resident fills the screen — their photo, age, weight and allergies pinned at the top so they can't scroll away while a dose is being recorded (the wrong-resident risk the clinical review flagged). Record their doses, then "Next resident". A progress bar shows how far through the round you are. Round-of-day tabs across the top.

**The payoff of all the groundwork:** the new page got every safety fix for *free*, because it shares the same underlying machinery rather than copying it. Verified end to end: recording a dose deducts stock correctly, a controlled drug still demands a witness, a failed save raises a real error instead of pretending to succeed, and corrections still leave an audit trail. None of that had to be rebuilt for the new page.

**Kept honest:** the page shows what genuinely exists. It does not invent a "weighed N days ago" age (weight isn't dated yet — that's still to build) and it shows the real prescribed dose rather than a computed volume (the medicine dictionary that would compute it isn't built yet). No fake data on screen.

**Wiring:** the new page is served by its own controller that *composes* the shared round logic (not inherits it), so an edit here can't reshape the 18 old round pages. Its route is registered before the catch-all so it isn't shadowed into an empty placeholder. 23 automated checks pass across the round and the home switcher; build clean.

**Still open, honestly:** the full new code list, weight-as-a-dated-series, and the "block or warn on zero stock" policy all still wait on a pharmacist. And the page is built but not yet clicked through in a real browser on a real phone — that's the next check.

---

## 23 July 2026 (end of day, part 2) — the round overview, weights, and the "not yet due" guard

Three things the owner asked for, all built on the Round page.

**Weight now means something.** It was a plain undated number, with two children secretly recorded in pounds. It's now a proper dated history: shown as "38 kg · weighed 35 days ago", kept in kg only (pounds refused and converted on the way in), and flagged in orange when it's too old to trust. How old is "too old" still needs a pharmacist — that number is a clearly-marked placeholder — but the machinery is there.

**A round now opens with an overview.** Instead of dropping straight onto the first resident, the carer first sees everyone in the round — who's done, who's overdue, who has an allergy, who has a controlled drug due — then taps "Start round". If they've already done some, the button says "Resume — [next person]" and jumps to the first person still outstanding, not back to the top. Tapping anyone opens them directly. This also means an interrupted round no longer loses its place. The overview is deliberately look-only — you can't record a dose from it, so the safety of the one-resident-at-a-time screen is never bypassed.

**Ending a round now checks first.** "End round" lives on the overview and, if doses are still outstanding, asks "3 doses still due — end anyway?" and records that they were left. Managers can re-open; carers can't (checked on the server).

**The owner's "gave night meds in the afternoon" catch is fixed.** Recording a dose whose time hasn't arrived yet now asks "not due until 20:00 — record early?" rather than silently going through. It's a confirmation, not a block, because giving early is sometimes legitimate.

Everything was checked the way that actually catches things: the rollup status the overview shows is computed in one place so it can't drift from the focused screen, end/reopen were tested end-to-end (including a carer being correctly refused a re-open), and the page was loaded through the real server. 28 automated checks pass; build clean.

---

## 23 July 2026 (end of day, part 3) — the rest of the Medication 2 pages

The four remaining pages in the Medication 2 area were empty placeholders. They're now real, and they all reuse the existing data rather than duplicating it — each new page points at the same query the old version already used, so there's one source of truth and nothing to drift.

- **Medications** — every active prescription in the current home, searchable, with quick filters (all / as-needed / controlled / low stock). Shows dose, form, route, and stock at a glance.
- **Missed doses** — the day's missed and not-given doses, with day-to-day navigation and a resolve flow: pick the clinical action taken, add notes, mark it resolved (or undo). Pairs with the round.
- **Stock** — a clean overview: how much of each medicine is in stock, what's low, out, expiring or expired, with the ones needing attention floated to the top. Deliberately read-only — receiving and adjusting stock stays on the fuller Stock 2 page, and the page says so.
- **Controlled drugs** — the controlled medicines in the home, by resident, with their schedule and current stock. It's **honest about a real gap**: there is no witnessed running-balance register being kept yet, so the page says the stock figures come from the medication record, not a register, rather than dressing up an empty register as a working one. Building that register is noted as real work still to do.

All four open with the manager's currently-selected home (the home switcher works across them), each was loaded through the real server, the resolve flow was tested end-to-end, and every existing safety check still passes. The routes are all registered ahead of the old catch-all so none of them fall back to a blank placeholder.

---

## 23 July 2026 (end of day, part 4) — the witnessed controlled-drug register

The compliance review's single biggest gap is now closed. Controlled drugs are meant to have a register: a running tally where every movement — a dose given, stock received, something disposed of — is written down with the balance after it and a second person's name as witness. The table for it existed but nothing ever wrote to it, so it sat empty while controlled drugs were being given.

Now:
- **Giving a controlled drug on the round writes a register entry by itself**, using the witness the carer already entered, and works out the new balance itself — the running total can never be a number someone just typed in.
- **The balance starts from the real recorded stock** on the first movement and chains from there, so it always adds up.
- **Receiving, disposing, returning or recounting** stock are recorded from the controlled-drugs page, each with a witness.
- **Nothing can be edited or deleted** — a mistake is fixed with a recount entry, not by changing history. That's the whole point of a register.
- Where a witness genuinely isn't required (a person's own home in supported living), the entry says "Not witnessed" rather than leaving the legally-required witness field blank.

The controlled-drugs page now shows each medicine's current balance and its full history, newest movements expandable.

Honest boundary, written into the docs: the register is a legal duty for registered care homes; the witness-at-administration is strongly-expected good practice, not a statute. This is software support for keeping that record — it doesn't make anyone compliant, and we don't claim it does.

Checked properly: the balance opens from stock, chains correctly across mixed movements, a recount sets it absolutely, giving a controlled drug on the round writes a witnessed entry, and an unwitnessed movement is recorded as such rather than blank. 33 automated checks pass in total; build clean.

---

## 23 July 2026 (housekeeping) — two safety landmines defused

**The "instant login" shortcut can no longer work in production.** `/dev-login` logs someone in as a manager with no password — fine for demoing, a wide-open door in production. It now refuses to work anywhere except a local/testing environment (returns a plain "not found"). Still works exactly as before on this machine.

**Running the tests can no longer wipe the real database.** A couple of the older tests rebuild the database from scratch as they run — and there was nothing stopping them from doing that to the *live* data. Tests now run against a separate throwaway copy (`laravel_test`), proven by checking which database a test actually connects to (it's the copy, not the real one) and by confirming the live data was byte-for-byte unchanged after a full test run. If the copy ever gets stale, it's one command to refresh: `mysqldump laravel | mysql laravel_test`.

Both were flagged earlier as things to fix before this goes anywhere near real use; both are now closed. All 33 of our checks still pass, against the copy.

---

**Still waiting on a real pharmacist:** the new code list, whether zero stock should block or just warn, and how old a child's weight can be before it's untrustworthy. We're not deciding those ourselves.

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
