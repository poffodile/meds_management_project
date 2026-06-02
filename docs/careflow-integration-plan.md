# Careflow-meds → OmegaLife Integration Plan

**Repo:** `C:\OmegaLife\socialcareitsolution`
**Branch:** `p_meds_management`
**Source app being ported:** `C:\OmegaLife\careflow-meds` (Base44 / React)
**Plan written:** 2026-05-28
**Status:** Awaiting sign-off before Phase 0 starts. No code changes have been made.

---

## 1. The goal in plain English

You have two apps:

1. **OmegaLife** — your existing Laravel website. Big, mature, has staff/rotas/logs/payroll/etc.
2. **careflow-meds** — a separate small app built on a low-code platform called **Base44**. It handles medication management (MAR charts, controlled drugs, stock, GP Connect, etc.).

We are going to **bring careflow-meds' features into OmegaLife**, so that staff log in once, use one website, and everything sits in one database. We are **not** keeping careflow-meds as a separate app, **not** running them side by side, and **not** rebuilding OmegaLife from scratch.

---

## 2. Why this approach (and not the others)

| Option | Verdict | Why |
|---|---|---|
| Embed careflow-meds as-is (iframe / link out) | ❌ | Two logins. Two databases. Users would hate it. |
| Run both apps, sync data between them | ❌ | Sync bugs are forever. Every schema change costs double. |
| **Port careflow-meds features into OmegaLife** | ✅ **Chosen** | One codebase. One login. One database. Long-term maintainable. |
| Rebuild OmegaLife on Base44 | ❌ | Throws away years of working Laravel code. |

---

## 3. The technical approach (still in plain English)

OmegaLife is built with **Laravel** — a PHP framework that traditionally renders pages on the server using "Blade" templates. That works fine for forms and tables, but careflow-meds' screens (MAR chart, body map, barcode scanner, etc.) are interactive React UIs that would be painful to rewrite as old-school Blade.

The fix is a library called **Inertia.js**. Inertia lets Laravel keep doing what it's good at (routing, auth, database) while letting us render **React pages** in the browser. One codebase, one login, one database — but modern React UI where we need it.

For the look-and-feel, we'll install **shadcn UI** — the same component library careflow-meds already uses. That means ported screens will look identical to what you've already seen and approved.

### What changes in OmegaLife as a result
- A handful of new dependencies (Inertia, React, Tailwind, shadcn).
- A new folder `resources/js/Pages/` where React pages live.
- New database migrations (one per careflow-meds entity, ~27 in total over time).
- New Eloquent models and controllers — but **the existing Blade pages keep working untouched.** Old and new UI live side by side during the transition.

---

## 4. The 6-phase roadmap

| Phase | What gets delivered | Estimated time |
|---|---|---|
| **0 — Plumbing** | Install Inertia + React + Tailwind + shadcn. One test page proves the wiring works. No user-facing features yet. | 2–4 days |
| **1 — First real slice** | Service Users + Medications + MAR chart. End state: staff can record a medication round inside OmegaLife. | 1.5–2 weeks |
| **2 — Controlled drugs + Stock** | CD register, stock transactions, reorder, disposal, reconciliation, stock alerts. | ~1 week |
| **3 — Appointments + Reminders** | Appointments, reminder preferences, scheduled job that sends reminders. | ~1 week |
| **4 — Staff features** | Clock-in, training modules + assignments, performance reviews, staff tasks. | ~1 week |
| **5 — GP Connect + AI Insights + Audit** | NHS GP Connect sync, AI insights panel, audit log, CQC compliance report. | ~1 week |

**Total realistic estimate:** 6–10 weeks of focused dev work.

---

## 5. What Phase 0 will actually do (step by step)

This is the only phase we are signing off right now. Each step is small, reversible, and adds no user-facing features.

### Step 1 — Server-side Inertia
```
composer require inertiajs/inertia-laravel
php artisan inertia:middleware
```
This installs the Laravel side of Inertia and adds a small middleware file so Laravel knows how to respond to React page requests. Adds one entry to `app/Http/Kernel.php`.

### Step 2 — Root HTML shell
Create `resources/views/app.blade.php` — a tiny Blade template whose only job is to host the React app. Existing Blade files are untouched.

### Step 3 — Client-side dependencies
```
npm install @inertiajs/react react react-dom @vitejs/plugin-react
npm install -D tailwindcss postcss autoprefixer
npx tailwindcss init -p
```
This installs React, Inertia's React adapter, and Tailwind (the CSS system shadcn is built on).

### Step 4 — Configure Tailwind + Vite
- Update `tailwind.config.js` content paths and CSS variables to match shadcn's defaults, so shadcn drops in cleanly in Phase 1.
- Update `vite.config.js` so the Vite + Laravel + React plugins all play nicely together.

### Step 5 — React entry point
Create `resources/js/app.jsx` — boots React and hands routing over to Inertia.

### Step 6 — One test page
- Create `resources/js/Pages/CareflowTest.jsx` — a single "Hello from React inside Laravel" page.
- Add one route in `routes/web.php`:
  ```php
  Route::get('/careflow-test', fn () => Inertia::render('CareflowTest'));
  ```

### Step 7 — Verify
Run `npm run dev` in one terminal, hit `/careflow-test` in a browser. If it renders, Phase 0 is done.

### What Phase 0 does **NOT** touch
- No careflow-meds files copied in yet.
- No new database tables.
- No existing Blade files modified.
- No existing controllers, models, or routes changed.
- No commits — we stop and review before committing.

---

## 6. Risks and things to watch

| Risk | How we'll handle it |
|---|---|
| Existing Vite build breaks when React plugin is added | Step 4 keeps both Laravel's Vite plugin and the React plugin together. We test the existing Blade pages still load before declaring Phase 0 done. |
| Some PHP/Node version mismatch on your machine | If `composer require` or `npm install` fails, we read the error together and fix the version. No silent retries. |
| Working alongside other developers on the same branch | `p_meds_management` is currently clean (no uncommitted changes), but if someone else is using it too, we agree before pushing. |
| Tailwind's CSS resets affect existing Blade styling | We scope Tailwind to only the new React pages in Step 4 by configuring the `content` paths carefully. Existing Blade pages won't load Tailwind. |

---

## 7. Open items to decide before Phase 1 (not blocking Phase 0)

1. **`ServiceUser` field reconciliation.** OmegaLife already has a `ServiceUser` model at `app/ServiceUser.php`. careflow-meds adds fields like `nhs_number`, `room_number`, `allergies[]`, `risk_flags[]`, `gp_name`, `gp_surgery`, `pharmacy_name`. Before Phase 1 we go field-by-field and decide what to add to OmegaLife's existing model.
2. **Where new controllers/models go.** OmegaLife's existing `app/Http/Controllers/` is sprawling. We may put careflow code under a `Careflow/` subfolder to keep it tidy.
3. **Whether to keep the old Blade medication pages (if any exist) once the React versions are live**, or remove them at the end of Phase 1.

These don't block Phase 0 — we only need answers before Phase 1 begins.

---

## 8. What I need from you to start Phase 0

A single "go" from you. When you give the go:
- I will run the steps in Section 5 **one at a time**, showing you the command and the result before moving on.
- I will not commit anything until you ask.
- If anything errors, I stop and we look at it together.

If you want to change anything in this plan first — UI library, phase order, branch, scope — say so and I'll update this doc before we start.
