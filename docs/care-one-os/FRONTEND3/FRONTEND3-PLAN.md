# Frontend3 — plan & isolation rules

**Raised:** 2026-08-04 · **Status:** branch created 2026-08-04; driving documentation received; awaiting first build slice.

## What frontend3 is
A **third, parallel front end** — alongside `frontend1` (the old Blade app) and `frontend2` (the Medication 2 / Inertia+React+Mantine pages). It is an experiment area; it must not disturb what already works.

**Driven by:** [CARE-ONE-OS-UX-SPECIFICATION.md](CARE-ONE-OS-UX-SPECIFICATION.md) — the Care One OS Product & UX Blueprint v1.0 (original `.docx` kept beside it as the master).

**Stack — decided 2026-08-04:** **same stack as the existing app** — Laravel 10 / PHP 8.3 · React + Inertia.js + Mantine v7 · Vite · MySQL 8.4. This is frontend2's stack (recorded in `../PRODUCT-CONTEXT.md`) and it is also exactly what the specification names, so there is no conflict to resolve.

This **supersedes** the earlier wording in this document that said frontend3 would carry "new Blade/Laravel work" — that was written before the specification arrived. (frontend1 is the legacy Blade + Bootstrap 3 + jQuery app; it has no written spec and is not the model for anything new.)

**The rule going forward:** *stack from the existing app, everything else from the frontend3 specification.* Same technology; new design language, palette, typography, information architecture, page inventory and workflow — all taken from [CARE-ONE-OS-UX-SPECIFICATION.md](CARE-ONE-OS-UX-SPECIFICATION.md).

Sharing a stack with frontend2 makes the isolation rule below **more** important, not less: same technology, zero shared files.

## How you reach it
- Login is **unchanged** — you log in through the **old Blade login** exactly as today and **land on the normal old Blade page**. Do **not** hijack the post-login redirect.
- Add a **link** on the Blade landing page that takes you into frontend3. That link is the only new entry point. (Add the link **on the frontend3 branch** so `main` stays clean.)

## Where it lives
- Its **own git branch** (e.g. `frontend3`). While that branch is checked out, `main` (frontend1 + frontend2) is physically untouched — cross-branch isolation is automatic until a deliberate merge.

## THE ISOLATION RULE (the important one) — CSS must not leak
The goal: **changing "global" CSS in frontend3 must NOT affect frontend1 or frontend2 pages.**

Branch separation only protects you *across* branches. *Within* the frontend3 branch, the trap is the word **"global"**: if frontend3 edits a **shared** stylesheet, it bleeds into 1 and 2 because they load the same files.

**Rule: frontend3 brings its OWN stylesheet. It must NOT edit any shared/global CSS.**

Do **not** edit (these are shared by frontend1 / frontend2):
- `public/frontEnd/css/*` — style.css, style-responsive.css, bootstrap-reset.css, developer.css (old Blade / frontend1)
- `frontend/tokens.js` — the frontend2 design tokens/palette
- `frontend/lib/font.js`, `resources/js/app.jsx`, shared Mantine theme/providers (frontend2)
- anything under `resources/js/Pages/...` or `frontend/` that frontend1/2 pages import

**Instead, for frontend3, do this** (concrete file plan, given the React/Inertia decision):

| frontend2 uses | frontend3 gets its own |
|---|---|
| `resources/views/app.blade.php` (root view) | `resources/views/f3.blade.php` — own fonts (Manrope + Inter), own vite entry |
| `resources/js/app.jsx` (Vite entry) | `resources/js/f3.jsx` — own `createInertiaApp`, own MantineProvider |
| `resources/js/Pages/**` | `resources/js/F3Pages/**` — own page resolver glob |
| `frontend/theme.js` + `frontend/tokens.js` | `frontend3/theme.js` + `frontend3/tokens.js` — the Quiet Clinical Luxury palette |
| `resources/css/app.css` | `frontend3/f3.css` — everything nested under `.f3-root` |
| `@frontend` / `@frontend2` aliases | `@frontend3` alias |
| `HandleInertiaRequests` (`$rootView = 'app'`) | `Inertia::setRootView('f3')` in `Frontend3Controller::__construct()` |

**All of the above was built on 2026-08-04 and verified** (routes resolve, view found, `npx vite build` produces a separate `f3-*.js` / `f3-*.css` pair).

On that last row: a `HandleF3InertiaRequests` middleware was the obvious approach, but registering an alias means editing `app/Http/Kernel.php` — a file shared with frontend1 and frontend2. Setting the root view in the frontend3 controller's constructor achieves the same thing and touches nothing shared. Prefer it.

Three shared files need a **purely additive** touch, and nothing more:
- `vite.config.js` — add `resources/js/f3.jsx` to the `input` array and add the `@frontend3` alias. Adding an input does not alter the frontend2 bundle (verified: `app-*.js` is unchanged in size and content).
- `routes/web.php` — append the `/frontend3` routes and one `use` statement. Existing routes untouched.
- `resources/views/frontEnd/common/header.blade.php` — one `<li>` for the **Frontend 3** button, matching the existing Frontend 1 and Frontend 2 buttons. This is the single entry point the plan calls for.

Everything else frontend3 needs is a **new file**. If a change can only be made by editing an existing frontend1/frontend2 file, that is the signal to stop and copy it into `frontend3/` instead.

Do that and "change the global CSS in frontend3" really means "change *frontend3's own* CSS" — 1 and 2 never see it, on the branch or after merge.

## Coordination note
This-session work (main) is fixing responsiveness on the **frontend2 / Medication 2** pages, editing the **shared** frontend2 CSS/tokens. So frontend3 **must not** also edit those shared files — it owns only its own scoped CSS. Keeping the rule above avoids a merge collision.
