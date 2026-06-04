# Frontend Plan (easy-read)

This is the simple, page-by-page plan for giving the whole app a new look.
We do it in **milestones**. Each milestone is a group of pages we finish together.
We do them **one at a time** — talk, build, check it works, tick the box, move on.
The old app keeps working the whole time. Nothing breaks.

*(A more technical version lives in `docs/ui-modernization-plan.md`. This file is the easy one.)*

**How to read each page line:** `Page name — what it's for → the new look we'll build.`

Tick boxes:  ⬜ = not started   🔄 = in progress   ✅ = done

---

## Milestone 0 — The base (build this first)
The frame and the shared parts every page needs. Without this, every page would be rebuilt from scratch.
- ⬜ **Design style** — pick the brand colours, fonts and spacing once → so every page looks the same and we can later give each company its own colours/logo.
- ⬜ **App frame** — the left menu, top bar, and user menu that wraps every page → one tidy, modern shell.
- ⬜ **Shared parts kit** — reusable building blocks (tables, buttons, pop-up forms, search box, "no data" message) → so new pages are fast to build and all look alike.
- ✅ **Pilot page proving it works** — Medication Stock (`/medication/stock-react`).

## Milestone 1 — Medications (we already started here)
Self-contained, already piloted, good momentum.
- ✅ **Medication Stock** — see and manage medicine quantities, low-stock and expiry alerts → clean table + stat cards + tabs *(done as the pilot)*.
- ⬜ **Medication Round** — the screen carers use to give meds at each time slot → simple checklist, big tap targets.
- ⬜ **Controlled Drugs** — the legally-required register for strong meds → clear running-balance table, witness fields.
- ⬜ **Missed Doses** — review and resolve meds that were missed or not given → list with a "resolve" pop-up.
- ⬜ **Shift Handover** — notes passed from one shift to the next → readable cards + a submit/acknowledge flow.

## Milestone 2 — The daily core (most-used screens)
The pages staff open every single day. Highest value to modernize next.
- ⬜ **Manager Dashboard** — the home screen with today's key numbers → modern cards, charts, quick links.
- ⬜ **Client (Service User) profile** — everything about one resident in one place → tidy profile with tabs (about, care plan, meds, notes).
- ⬜ **Daily Log** — the running diary of care given → fast list + quick "add entry" form.
- ⬜ **Schedule / Rota** — who is working when → clear calendar/grid view.

## Milestone 3 — Care & safety
The records that keep people safe and the service compliant.
- ⬜ **Care Plans** — the plan for each resident's care.
- ⬜ **Risk assessments** — known risks and how to handle them.
- ⬜ **Incidents** — log and follow up on things that went wrong.
- ⬜ **Safeguarding** — protect-from-harm referrals and tracking.
- ⬜ **Care Documents** — store and find a resident's documents.

## Milestone 4 — Staff & HR
Everything about the people who work there.
- ⬜ **Staff profiles** — details, role, qualifications.
- ⬜ **Onboarding** — bringing a new staff member on board.
- ⬜ **Training** — courses and who has done them.
- ⬜ **Leave Requests** — book and approve time off.
- ⬜ **Timesheets & Pay** — hours worked and pay rates.

## Milestone 5 — Compliance & tools
The "running the business properly" pages.
- ⬜ **Compliance Hub / Dashboard** — are we meeting the rules?
- ⬜ **Audits** — audit templates and logs.
- ⬜ **Policy Library** — company policies and sign-off.
- ⬜ **Task Center / Action Plans** — jobs to do and progress.
- ⬜ **Reports** — pull numbers out of the system.
- ⬜ **Form Builder** — build custom forms.

## Milestone 6 — Sales & money
- ⬜ **CRM Dashboard** — track enquiries/leads.
- ⬜ **Quotes** — price up new care packages.
- ⬜ **Invoices & Payroll/Finance** — billing and pay.

## Milestone 7 — Other care types
The same idea, applied to the other services in the menu.
- ⬜ **Domiciliary Care** — Visit Schedule, Runs, Communications.
- ⬜ **Supported Living** — Properties, Schedule.
- ⬜ **Day Centre** — Activities, Sessions, Attendance, Follow-up.

## Milestone 8 — System & admin (back office)
Settings most users never see (admins only).
- ⬜ **Companies & Charges**, **User Management**, **Role Management**, **Module Settings**, **Home Management**, shift categories, pay rates, access levels, labels/types.

## Milestone 9 — Tidy up
- ⬜ Remove the old look's files (Bootstrap 3 / jQuery / Angular) once each section is fully moved over.
- ⬜ **Speed pass** — make pages load fast (only load what each page needs).

---

## Two things happening alongside (not "pages", but important)

**Track A — Make the data safe for many companies (backend).**
Right now, keeping each company's data separate is done by hand on every query, which is risky. We'll make it automatic and enforced. This is the biggest safety job and runs in the background while we do the page milestones. *(Recommended: start early.)*

**Track B — The shared "API" for the mobile app.**
As we modernize, we build a clean data layer that both the website and the future **carers' mobile app (React Native)** use. The mobile app itself is **built after the website is done**.

---

## My recommendations
1. **Do M0, then finish M1 (Medications) first.** You already piloted it, it's self-contained, and finishing one whole module proves the pace and look before we commit to the rest.
2. **Build the shared parts kit (M0) properly.** It's the single biggest time-saver — every later page gets ~5× faster to build.
3. **Start Track A (data safety) early**, in parallel. It's the top risk for a many-companies product, and it's easier to fix before there are lots of new pages.
4. **Order pages by how often they're used** (daily screens before admin settings) — that's why M2 is the daily core and M8 (admin) is near the end.
5. **Pause for a review after M1.** Once one full module is live, we check the real time-per-page, then confidently plan the long tail (there are ~700 pages, so pacing matters).
6. **One page = one small, safe step.** Each page goes live next to the old one first, so we can compare and never break what works.

---

## Your turn
Please read this and tell me:
- Does the **milestone order** make sense for how you work?
- Anything **missing**, or any page you want **moved earlier/later**?
- Happy for **Track A (data safety)** to start in parallel, or later?

Once you're happy, we start **Milestone 0, step 1** together.
