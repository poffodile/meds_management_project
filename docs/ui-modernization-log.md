# UI Modernization — Discussion & Session Log

> **Status:** 🟢 BUILD phase. Stack agreed; M0 (base) + M1 (medication pages) done. Now restyling pages to a new reference design.
> **Last updated:** 2026-06-09
>
> ### ▶ RESUME HERE (next step)
> M1 medication pages are live at `/medication/*-react` (legacy untouched). On 2026-06-09 the **global shell + Medication Round page** were redesigned to a new reference mock.
> Open items: guard `/dev-login` before prod; confirm manager/carer split is enough (vs separate Admin/Owner view); decide if/when to wire the rich Medication-Round card fields (needs backend); then continue to the next page / Milestone 2.
> **See "Session 2026-06-09" at the bottom of this file for the latest.**
>
> _(historical discussion-phase notes from 2026-06-04 are kept below for context.)_

---

## Goal
Modernize the UI of the whole app, **gradually, page by page**. Current UI looks dated / "like child's play" and is not seamless. Want the **most professional-looking** result achievable.

## Hard constraints (from owner)
- Keep **Laravel / PHP as the backend** (data, logic, auth).
- Use a **modern frontend** ("something else") for the UI.
- **Gradual** migration — no big-bang rewrite.
- **Leave the login alone (for now).** Focus on everything **after** login.
- ⚠️ **Future:** login/auth method may change and **may not stay Laravel-managed** long-term.
  - Affects **only the architecture layer** (Inertia vs decoupled SPA), **NOT** the React/component-library choice (those are auth-agnostic & fully portable).
  - Need to clarify which kind: **(A)** Laravel still issues the session, creds come from SSO/OAuth/OIDC → **Inertia stays fine**; **(B)** Laravel removed from auth path, frontend auths against external provider + Laravel becomes pure API → **favors decoupled SPA**. *(awaiting owner)*
- ⚠️ **Future: AWS integration** is being discussed by stakeholders ("they"). Mostly an **infra/backend** concern (hosting / RDS / S3 / SES / SQS) that **does NOT change the React/UI choice**. Only touches the architecture decision **if AWS = Cognito for auth** (a concrete form of the (A)/(B) question — and even Cognito can run with Laravel sessions = still Inertia-friendly).
- ❓ Possible **mobile app** later would favor an API/decoupled approach (one API feeds web + mobile).
- Note: requirements are partly **stakeholder-driven ("they")** and may not be firm/finalized.
- Discuss & agree the **stack first**, then write a very detailed plan.

## Current stack (assessed 2026-06-04)
- **Bootstrap 3.1.0** (2014, EOL), **jQuery**, **AngularJS 1.4**, DataTables, select2.
- **700 Blade views**, ~8.5 MB vendored frontend CSS/JS.
- New medication pages use modern hand-written **inline styles** (divergent from the old theme) → app is already visually split.
- **Vite** already configured. **laravel/sanctum ^3.2** already installed.
- Session auth via `user` table (singular). See [[local-run-setup]] for how to run locally.

---

## Options discussed

### Architecture fork (who owns the page + how auth works)
1. **Inertia.js (Laravel + Vue/React)** — ⭐ recommended. Keeps Laravel routes/controllers + **session login** (login stays untouched). Inertia & legacy Blade **coexist** → convert one page at a time. Uses existing Vite.
2. **Fully decoupled SPA (Laravel API + separate React/Vue app)** — most "modern" but **worst fit**: must rebuild auth as tokens (breaks "leave login alone"), CORS, two deploys, **cannot go gradual**. Steered away from for now.
3. **Livewire (stay in PHP/Blade, reactive)** — lowest risk, reuses session auth, but keeps you in PHP/Blade (owner wants a real JS frontend). Noted, not chosen.

### The "professional look" is a separate axis from architecture
- Driven by the **component library + design system**, not the framework.
- React → **shadcn/ui + Tailwind** (gold standard, best look, React-only, steeper curve).
- Vue → **PrimeVue** or **shadcn-vue** (very good, gentler curve, very Laravel-native).

---

## Final stack (DECIDED 2026-06-04 — owner deferred to Claude's best call)
- **Backend:** Laravel + a clean **service layer** (logic written once) + **tenant/company scoping** for multi-tenant SaaS.
- **Web:** **Inertia + React**, migrated **gradually** page-by-page, coexisting with legacy Blade, reusing the **existing session login** (login untouched now).
- **API:** build the web **"API-ready"** (logic in services, thin controllers) → exposes a **Sanctum-protected JSON API** that **React Native reuses later**.
- **Mobile (later):** **React Native** on the same API. All-React reuse (language, API client, types, skills).
- **Auth:** Web = sessions; Mobile = Sanctum tokens; both at once. Survives a future external-auth/Cognito move without a rewrite.
- **Web component library:** **Mantine** (professional look + batteries-included data components + per-tenant theming + great TS DX). **Ant Design** = safe swap-in alternative, no architecture change.
- **Multi-tenancy:** design system + theming handle per-company branding on the front; data isolation is a parallel **backend** workstream (app already has `home_id`/company).

## Code scan findings (2026-06-04)
- **Existing mobile API already in code**: `routes/api.php` (223 lines) + `Android\AndroidApiController` + `Api\*` controllers (ServiceUser/Staff/Schedule/Education/DailyLog). A prior Android effort exists → API surface to learn from for the React Native build.
- **⚠️ Existing API auth is weak**: manual `Hash::check`, `auth:api` commented, **no `api` guard** in `config/auth.php`. **Sanctum is installed but unused** → wire it properly for the RN app. (reliability/security)
- **⚠️⚠️ Multi-tenancy is fragile** (top risk for multi-company SaaS): user `home_id` is a **comma-separated string**; scoping is **manual per-query** (`->forHome()`), **no global scope/middleware enforcement**. A forgotten scope = cross-tenant data leak. → **Priority backend workstream: real tenant scoping** (global scope or stancl/tenancy-style), parallel to UI.
- **✅ Service layer exists** (53 files under `app/Services/`) → good base for "API-ready".
- **✅ Pilot target clean**: `MedicationStockController@index` returns 4 arrays to the view; `adjust()` = validated POST + redirect/flash → ideal first Inertia conversion.
- Roles via single-letter `user_type` (N/M/A/CM/O), ad-hoc `in_array` checks (no RBAC package).

## Sequence from here
1. ✅ Stack decided. → 2. (optional) **Code scan** for existing API/mobile/tenancy structure. → 3. **Visual pilot**: medication stock page in Inertia+React+Mantine (see the look). → 4. **Detailed migration plan** + rollout order across the 700 pages.

## Decisions log
| Date | Decision | Notes |
|------|----------|-------|
| 2026-06-04 | Modernize whole app gradually; backend stays Laravel | Owner directive |
| 2026-06-04 | Leave login as-is, focus post-login | Owner directive → favors Inertia/Livewire (session auth) |
| 2026-06-04 | Architecture leaning **Inertia** | Recommended, pending owner confirmation |
| 2026-06-04 | **Framework = React** | Owner has used React a little before |
| 2026-06-04 | **Mobile app CONFIRMED** | Manager directive: carers get a mobile app; managers use the web. → A JSON API is now a definite deliverable (mobile can't use Inertia). |
| 2026-06-04 | **Architecture firming: Inertia (web) + Sanctum API (mobile) + shared service layer** | Mobile does NOT force web decoupling. Standard Laravel combo; `laravel/sanctum ^3.2` already installed. Web=sessions (login untouched), mobile=tokens, both at once. |
| 2026-06-04 | Audience split: **web = managers (data-dense admin)**, **mobile = carers (task-focused)** | Reinforces batteries-included web lib (Mantine/Ant) over shadcn. |
| 2026-06-04 | **Mobile = React Native, built AFTER the web** | All-React reuse (shared language/API client/types/skills). API built for web is reused by RN. |
| 2026-06-04 | **NEW hard requirement: multi-tenant SaaS** (many care companies/homes) | Makes a design system mandatory; adds per-tenant theming/white-label as a criterion. Heavy tenancy = backend (app already has home_id/company). |
| 2026-06-04 | **Component library = Mantine** (Claude's best call; owner deferred) | Best balance: professional look + batteries-included data components + strong theming + great TS DX. Ant Design = safe alternative, swap changes no architecture. |
| 2026-06-04 | **STACK DECIDED** (pending a visible pilot to confirm the look) | Laravel + service layer + Inertia/React (web) + Sanctum API + React Native (mobile, later) + Mantine. |
| 2026-06-04 | **AGREED: Inertia (not decoupled SPA)** — formally signed off by owner | Reuses existing login (incl. future SSO/Cognito scenario A), gradual page-by-page beside legacy, components portable if ever decoupled later. Build API-ready so a future decouple is cheap. Mobile (RN+Sanctum API) is independent of this choice. |
| 2026-06-04 | **Pilot extras**: `frontend/` shared-component folder + `@frontend` Vite alias | Owner wanted a clearly-named home for components; pages/entry stay standard in `resources/js`. |

## Open decisions (blocking the detailed plan)
- [ ] Confirm **Inertia** as the architecture (vs decoupled SPA).
- [x] **Vue vs React** → **React** (2026-06-04).
- [ ] **Component library** — data-dense CRUD: weigh batteries-included (Ant Design / Mantine) vs bespoke-premium (shadcn/ui + Tailwind). Needs a **reference site** the owner admires.
- [ ] Team size & timeline (solo vs team) — affects how aggressive the plan should be.
- [ ] Pick the **pilot page** to convert first (candidate: the new medication module).

### Questions blocking the final architecture call (asked 2026-06-04, awaiting answers)
1. How **firm/soon** is "login won't be Laravel"? (Firm+soon → decoupled; vague → Inertia now.) — *still open, but lower stakes now: the Inertia+API+Sanctum shape absorbs a future external-auth move.*
2. ~~**Mobile app** ever on the roadmap?~~ → **ANSWERED 2026-06-04: YES, confirmed** (carers' app). Locks in a definite JSON API; settled on Inertia(web)+Sanctum API(mobile).
3. What does **"AWS"** concretely mean — hosting only (no impact) vs **Cognito-for-auth** (impacts decision)?
4. Who is **"they"**, and how much do they drive/finalize requirements?
5. **Team size** going forward (solo vs more devs)? Decoupled = more moving parts to maintain.
6. **What is the mobile app built in** (React Native / Flutter / native)? RN → max reuse with React web (shared API client, types, skills).
7. **Mobile timeline** — parallel with web revamp, or after? Decides whether the API is built now or web-first.

---

## Session 2026-06-09 — first build sessions: M1 polish, role/login audit, Med Round + shell redesign

**Where we are:** stack is built. M0 (base: theme, AppShell, shared components, testing foundation) and M1 (all 4–5 medication pages in React/Inertia/Mantine) are done. Each React page lives at `/medication/*-react`; the legacy pages are untouched. Now in a **restyle pass** to match a new visual reference the owner provided.

### Done this session
- **Local run verified.** `start-local.bat` (MySQL :3306 + PHP `serve-local.php` :8000 + Vite :5173). All 5 React medication pages load (auth-gated → 302 to `/login` when logged out). Confirmed Vite serves the client + page modules cleanly.
- **Frontend test layer completed & committed.** 6 component test files (StatCard, PageHeader, DataTable, FormModal, ConfirmDialog, StatusBadge), **14 Vitest tests passing**. (`npm run test`.)
- **New reference design** provided for the **Medication Round** page (rich dashboard look). Agreed scope below.
- **Global shell redesigned** (`frontend/Layouts/AppShell.jsx`): Mantine `layout="alt"` (full-height sidebar); **Care One OS** transparent logo (white text) on a dark **navy band `#16223a`**; grouped nav with **MEDICATIONS** / **DOMICILIARY CARE** section labels; unbuilt items shown as greyed "Coming soon" placeholders; user identity moved to **top-right** header with dark-mode + logout in its menu; **Collapse** control + header burgers.
- **Medication Round page redesigned** (`resources/js/Pages/Medication/MedicationRound.jsx`): hero (round icon + title + date/window/counts), **4 stat cards** (Due / Completed / Remaining / Round-Progress bar), **vertical timeline** of resident cards (initials avatars, status pills, per-med Administer buttons), round switcher, Start-Next-Round, secure footer. **Frontend-only — no backend change.**
- **Logo asset** saved to `frontend/assets/logo-careoneos.png` (transparent confirmed via alpha check; "Care One" text is white → needs dark bg).

### Audits (saved to memory)
- **Role model** → `memory/role-model.md`. **414 users.** Two tiers: (1) `user_type` enum **N**=carer/support-worker (281), **A**=admin (75), **M**=manager (45), **CM**=care-manager (11), **O**=owner (2); (2) per-home **`access_level`** custom roles (82 active, 40 names) built from **814 `access_right`** permissions across **125 homes**. The React UI collapses all of this to **manager** (`M,CM,A,O`) vs **carer** (`N`) in `HandleInertiaRequests.php` — drives the AppShell preview toggle.
- **Login** is in code: `/login` → `frontEnd/UserController@login` (legacy `login.blade.php`, **left untouched** per plan); separate `admin/login` → `backEnd/AdminController@login`. ⚠️ **`GET /dev-login`** (`web.php:116`) logs in the first non-deleted user **with no password** (sets session home scoping, refreshes CSRF) → handy for local testing but a **security hole**; must be env-guarded/removed before prod.

### Decisions this session
| Date | Decision | Notes |
|------|----------|-------|
| 2026-06-09 | **Restyle scope = page + global shell** (for the Med Round reference) | shell change affects every React page |
| 2026-06-09 | **Med Round cards: restyle with current data, defer rich fields** | photos / room / DOB / allergies / per-med stock·route·frequency / CD badge all need backend wiring — deferred |
| 2026-06-09 | **Logo** = transparent "Care One OS" PNG on dark navy band `#16223a`; shell uses `layout="alt"` | white-text logo requires a dark header band |
| 2026-06-09 | **Frontend test layer complete** (14 Vitest tests, 6 component files) | committed |

### Open items / next
- [ ] **Guard `/dev-login`** behind `app()->environment('local')` (or remove) before any prod deploy.
- [ ] Confirm **manager/carer** two-view split is enough, or design a distinct **Admin/Owner** experience.
- [ ] Decide if/when to **wire the rich Medication-Round card fields** (backend: ServiceUser room/DOB/allergies/photo + MARSheet stock/CD/route/frequency).
- [ ] Optional polish: logo size / navy band shade vs the teal/orange/green/purple ring.
- [ ] Then: continue restyling remaining medication pages to the reference, or proceed to **Milestone 2** (Dashboard, Client profile, Daily Log, Schedule) + backend **Track A** (real tenant scoping).

---

## Session 2026-06-09 (cont.) — Medication Round rebuilt on a reusable design system

Owner provided a richer **Medication Round** mockup (resident-centric 3-column workspace) and asked to rebuild it **without duplicating code/colours** — i.e. on a documented, reusable design system with a "global CSS" (tokens). Planned in plan mode (`~/.claude/plans/snug-inventing-token.md`, approved) and built in 7 phases.

**Decisions:** Brand stays **Care One OS** (mockup's "OmegaLife" was a placeholder); **layout-first then phase in data**; **central tokens in `frontend/theme`** (not a separate CSS file); components built generic / mobile-aware; documented in `docs/design-system.md`.

**Built (all verified — `npm run test` 20 passing, Vite transforms, authed controller returns 200):**
- **Phase 1 — tokens:** `frontend/tokens.js` (brand incl. navy, `statusColors`, `roundTokens`, avatar palette, radius/type); `theme.js` consumes it; `StatusBadge` reads `statusColors`. **Fixed the `low` duplicate-key bug** (stock "low" was rendering grey). Replaced hardcoded `#16223a` in AppShell with `brand.navy`.
- **Phase 2 — utils/hooks + dedupe:** `lib/{dateUtils,avatarColor,medicationCodes}`, `hooks/{useFlash,usePageReload}`, `components/FlashAlerts`. Removed real duplication: the twice-defined `pad()`, `CODE_OPTIONS` clone, and the flash-Alert block copy-pasted across 4 pages → now shared.
- **Phase 3 — generic primitives:** `MetricChip, AlertItem, QuickActionItem, RiskFlag, RoundProgressDonut` (+ tests).
- **Phase 4 — composites:** `ResidentCard, ResidentListItem, MedicationCard` (Administer/Refused/Omitted).
- **Phase 5 — page:** rebuilt `MedicationRound.jsx` as the 3-column workspace; added **Phase A** fields to `indexReact` (strength/route/instruction/stock/low-stock/CD/Regular-PRN tag; resident dob/allergies/counts).
- **Phase 6 — derivations (Phase B):** `doseBucket()` derives per-dose **overdue/due_now/upcoming/later/completed** from slot-vs-now; round-progress buckets; overdue-aware resident status; Upcoming-next-2h split; overdue + low-stock + CD alerts; resident **photo** (`public/images/serviceUserProfileImages/`), gender, weight; **generic risk flags** from `care_plan_risks` (surfaced with their `impact` level).
- **Phase 7 — docs:** `docs/design-system.md` (token + component catalogue + Phase A/B/C data map); this log entry; memory updated.

**Still placeholder (Phase C — no DB source / fragile, documented in design-system.md):** NHS number, room label (no room lookup table), medication form, therapeutic class, conditions, PRN last-given/next-available, care-plan link. Quick actions Scan/Add-PRN/Temp-Absence/MAR-report are stubbed.

**Files:** `frontend/tokens.js`, `frontend/theme.js`, `frontend/{lib,hooks}/*`, `frontend/components/{FlashAlerts,MetricChip,AlertItem,QuickActionItem,RiskFlag,RoundProgressDonut}.jsx`, `frontend/features/medications/{ResidentCard,ResidentListItem,MedicationCard}.jsx`, `resources/js/Pages/Medication/MedicationRound.jsx`, `app/Http/Controllers/frontEnd/Medication/MedicationRoundController.php`, `docs/design-system.md`. New logo asset `frontend/assets/logo-careoneos.png`.

**Open / next:** wire Phase C items when their data is sourced; apply the same design system to the other medication pages; still pending from before — guard `/dev-login`, multi-tenancy Track A, Milestone 2.
