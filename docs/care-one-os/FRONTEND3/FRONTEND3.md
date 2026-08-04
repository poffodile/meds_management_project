# FRONTEND3 — master log

**This is the single file for EVERYTHING to do with frontend3.**
**Lives in:** `docs/care-one-os/FRONTEND3/` — the folder that holds all frontend3 paperwork.

Every session, every decision, every question, every bit of work goes in here with the date and time — so you can pick this up from any terminal, any day, without remembering what happened last time.

- The rules and the plan live in [FRONTEND3-PLAN.md](FRONTEND3-PLAN.md).
- This file is the **running diary**. Newest entry at the **bottom**.

---

## How to use this file

Every time we do frontend3 work, add an entry in this shape:

```
### YYYY-MM-DD HH:MM — short title
**What we did:**
**Decisions made:**
**Open questions / what's next:**
**Files touched:**
```

Plain English. No jargon. If a decision was made, write down *why*, so future-you isn't guessing.

---

## Quick status board

| Thing | State | As of |
|---|---|---|
| `frontend3` git branch | **Not created yet** | 2026-08-04 |
| New documentation that drives frontend3 | **Not supplied yet** | 2026-08-04 |
| Link from Blade landing page into frontend3 | Not built | 2026-08-04 |
| frontend3's own layout + scoped CSS (`.f3-root`) | Not built | 2026-08-04 |
| Anything shared broken by frontend3 | No — nothing built yet | 2026-08-04 |

---

## The non-negotiables (copied here so they're always in front of you)

1. **Own branch.** All frontend3 work happens on the `frontend3` branch. `main` (frontend1 + frontend2) stays clean.
2. **Login is untouched.** You log in through the old Blade login and land on the normal old Blade page. The post-login redirect is NOT to be hijacked.
3. **One entry point.** A single link added to the Blade landing page — and that link is added on the `frontend3` branch, not on `main`.
4. **frontend3 brings its OWN CSS.** It must never edit shared/global stylesheets.
5. **Everything scoped under `.f3-root`.** Even a rule frontend3 thinks of as "global" can only match inside frontend3 pages.
6. **Own layout / own build entry.** Separate from `resources/js/app.jsx` so it can't collide with the frontend2 bundle.

**Files frontend3 must NEVER edit:**
- `public/frontEnd/css/*` (style.css, style-responsive.css, bootstrap-reset.css, developer.css)
- `frontend/tokens.js`
- `frontend/lib/font.js`, `resources/js/app.jsx`, shared Mantine theme/providers
- anything under `resources/js/Pages/...` or `frontend/` that frontend1 or frontend2 imports

---

## Log

### 2026-08-04 11:45 — File created, plan understood, nothing built yet

**What we did:**
- Read `FRONTEND3-PLAN.md` end to end and confirmed the understanding of it: frontend3 is a third, parallel front end (new Blade/Laravel work) sitting alongside frontend1 (old Blade) and frontend2 (Medication 2 / Inertia + React + Mantine). It's an experiment area and must not disturb anything that already works.
- Checked the repo state. Branches that exist right now: `main` and `origin/new_tester_branch`. **There is no `frontend3` branch yet.** We are currently sitting on `main` with a clean working tree.
- Created this file as the permanent home for all frontend3 conversation, decisions and progress, with dates and times, so no context is lost between terminal sessions.

**Decisions made:**
- All frontend3 chat and work gets logged here, newest at the bottom, each entry stamped with date and time. This is separate from `docs/DEVELOPMENT-LOG.md` — that one stays the master day-by-day log for the wider project; this one is frontend3-only and goes into more detail.
- `FRONTEND3-PLAN.md` stays as the rules/spec document (it doesn't change often). `FRONTEND3.md` (this file) is the diary (it changes every session).

**Open questions / what's next:**
1. **The new documentation hasn't been supplied yet.** The plan says frontend3 carries "new Blade/Laravel work driven by new documentation (to be supplied)". Until that arrives, we don't know what frontend3 is actually meant to *contain* — only how it's meant to be *wired up*.
2. **Do we create the `frontend3` branch now, or wait?** Two sensible options:
   - **Create now** and lay down the empty skeleton (branch + its own Blade layout + its own scoped `.f3-root` CSS file + the one link on the Blade landing page). Then when the documentation arrives, there's a safe container ready to drop it into.
   - **Wait** until the documentation arrives so the skeleton is shaped by what's actually going in it.
3. Note the timing risk from the plan: work on `main` is currently touching the **shared frontend2 CSS/tokens** for responsiveness fixes. frontend3 must stay off those files or there'll be a merge collision on top of the visual bleed.

**Files touched:**
- `docs/care-one-os/FRONTEND3/FRONTEND3.md` (new — this file)

---

### 2026-08-04 11:48 — Both frontend3 docs moved into their own folder

**What we did:**
- Owner moved both frontend3 documents out of the `docs/care-one-os/` root and into a **dedicated folder**: `docs/care-one-os/FRONTEND3/`. It now holds `FRONTEND3.md` (this log) and `FRONTEND3-PLAN.md` (the rules).
- Checked the cross-link between them still works — they moved together, so the relative link `[FRONTEND3-PLAN.md](FRONTEND3-PLAN.md)` still resolves. Fixed the two stale full paths written inside this log.

**Decisions made:**
- `docs/care-one-os/FRONTEND3/` is the **home for all frontend3 paperwork** from now on. Any new frontend3 document (notes, the supplied documentation when it arrives, screenshots, decisions) goes in this folder — not in the `care-one-os` root.

**Open questions / what's next:**
- Owner chose: **create the `frontend3` git branch only** — no code, no skeleton, no landing-page link — until the new documentation arrives. That's the next action.
- The new documentation is still **not supplied**.

**Files touched:**
- `docs/care-one-os/FRONTEND3/FRONTEND3.md` (path references corrected, this entry added)
- `docs/care-one-os/FRONTEND3/FRONTEND3-PLAN.md` (moved only — contents unchanged)
