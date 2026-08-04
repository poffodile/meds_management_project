# Frontend3 — plan & isolation rules

**Raised:** 2026-08-04 · **Status:** planned, to be built on its own branch in a separate terminal session.

## What frontend3 is
A **third, parallel front end** — alongside `frontend1` (the old Blade app) and `frontend2` (the Medication 2 / Inertia+React+Mantine pages). It carries **new Blade/Laravel work** driven by new documentation (to be supplied). It is an experiment area; it must not disturb what already works.

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

**Instead, for frontend3, do this:**
- Give frontend3 its **own layout** (its own Blade layout file, or its own Inertia/Vite entry) that loads **only its own CSS file(s)**.
- Scope frontend3's CSS under a **root wrapper class** (e.g. everything under `.f3-root { … }`) so even a rule frontend3 *thinks* of as "global" can only match inside frontend3 pages.
- If frontend3 uses its own build entry, keep it separate from `resources/js/app.jsx` so it can't collide with the frontend2 bundle.

Do that and "change the global CSS in frontend3" really means "change *frontend3's own* CSS" — 1 and 2 never see it, on the branch or after merge.

## Coordination note
This-session work (main) is fixing responsiveness on the **frontend2 / Medication 2** pages, editing the **shared** frontend2 CSS/tokens. So frontend3 **must not** also edit those shared files — it owns only its own scoped CSS. Keeping the rule above avoids a merge collision.
