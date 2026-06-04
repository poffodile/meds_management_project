# UI Modernization — Implementation Plan (DRAFT for agreement)

> **How we work this:** this is the agreed checklist. We do it **one step at a time** — talk first, implement, verify, tick the box, move on. Nothing big-bang. Legacy app keeps running the whole time.
>
> **Status:** DRAFT — awaiting owner agreement/tweaks before we start Phase 1.
> **Companion docs:** decisions & rationale in [`ui-modernization-log.md`](./ui-modernization-log.md).
> **Last updated:** 2026-06-04

## The goal (recap)
Modernize the whole web UI **gradually**, page by page, to a **professional** standard, on a stack that **scales to many care companies** (multi-tenant SaaS), is **reliable**, and where **pages load fast** — while keeping Laravel as the backend, leaving login alone for now, and feeding a future **React Native** carers' app from the same backend.

## The agreed stack (decided)
Laravel + service layer → **Inertia + React + Mantine** (web), **Sanctum JSON API** (shared) → **React Native** (mobile, later). See log for full rationale.

---

## Phase 0 — Pilot & scaffolding  ✅ (done 2026-06-04)
- [x] Install Inertia (Laravel) + React + Mantine + PostCSS
- [x] Wire Vite for React, Inertia middleware, root `app.blade.php`, `app.jsx`
- [x] Isolated pilot page: `/medication/stock-react` (legacy page untouched)
- [x] Confirm it builds & serves (Vite dev server + healthy route)
- [ ] **Owner reviews the pilot look** ← *we are here*

## Phase 1 — Design system & app shell  (the "professional look" foundation)
Goal: one consistent frame + reusable parts so every future page is fast to build and looks the same.
- [ ] Define **brand design tokens** in the Mantine theme (colors, typography, spacing, radius) — built **white-label-ready** (per-tenant theme override hook) for the multi-company requirement.
- [ ] Build the **AppShell**: sidebar navigation, top bar, user menu, breadcrumbs — the frame all pages live in.
- [ ] Share global data via Inertia middleware: current **user**, **flash messages**, **tenant/home context**, permissions.
- [ ] Build a starter **shared component kit**: `PageHeader`, `DataTable` (sortable/paginated wrapper), `FormModal`, `ConfirmDialog`, `StatusBadge`, `EmptyState`.
- [ ] Re-skin the **medication stock pilot** onto the AppShell + components (first "real" page).
- [ ] Nail the **dev + production serving** story (Vite dev server now; production build path vs the project-root docroot — pick the clean approach).

## Phase 2 — Backend hardening  (parallel; critical for "many companies")
These are reliability/scalability fixes the scan surfaced. Can run alongside Phase 1/3.
- [ ] **Multi-tenancy (top priority):** replace manual per-query `->forHome()` scoping with **enforced tenant scoping** (global scope + middleware) so data isolation can't be forgotten. Plan migration of the comma-separated `home_id` model.
- [ ] **API + Sanctum:** stand up a clean, **versioned, Sanctum-protected API**; move logic into **services** so web (Inertia) and mobile (RN) share one source of truth. Harden/replace the weak existing Android API auth.
- [ ] **RBAC:** formalize the single-letter `user_type` checks into Laravel **policies/gates**.

## Phase 3 — Page migration  (the bulk — one page at a time)
- [ ] Agree the **migration order** (proposed: finish the **medication module** first → then highest-use **manager** screens → then the long tail of ~700 views).
- [ ] Per-page **"definition of done":** feature parity, responsive, uses shared components, retires the legacy blade.
- [ ] Migrate pages in batches; legacy + new coexist until each section is complete.

## Phase 4 — Mobile (React Native)  (after the web + a mature API)
- [ ] Build the **carers' RN app** on the Phase-2 Sanctum API, reusing React skills/types/API client.

## Phase 5 — Cutover & polish
- [ ] Remove legacy Bootstrap 3 / jQuery / AngularJS assets as sections are fully migrated.
- [ ] **Performance pass:** route-based code-splitting, lazy loading, caching, fast page loads.
- [ ] Final cleanup once the last legacy page is gone.

---

## Cross-cutting (kept in mind throughout)
- **Per-tenant theming / white-label**, **page-load performance** (Inertia SPA nav + Vite splitting), **testing**, and keeping the **legacy app fully working** at every step.

## Open items to agree before Phase 1 starts
1. Sign-off on the **Mantine look** (pilot) — or adjust direction.
2. Agree the **migration order** for Phase 3.
3. Decide whether **Phase 2 (multi-tenancy)** runs in parallel from the start or after the first few pages.
