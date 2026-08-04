# FRONTEND4 — master log

**This is the single file for EVERYTHING to do with frontend4.**
**Lives in:** `docs/care-one-os/FRONTEND4/` — the folder that holds all frontend4 paperwork.

Every session, every decision, every question, every bit of work goes in here with the date — so you can pick this up from any terminal, any day, without remembering what happened last time.

- The rules and the plan live in [FRONTEND4-PLAN.md](FRONTEND4-PLAN.md).
- This file has **two parts**: the **Work log** (what was done and decided) and the **Conversation record** (what was actually said, both sides). Newest at the bottom in both.

---

## How to use this file

**Part 1 — Work log.** Every time we do frontend4 work, add an entry in this shape:

```
### YYYY-MM-DD — short title
**What we did:**
**Decisions made:**
**Open questions / what's next:**
**Files touched:**
```

**Part 2 — Conversation record.** The actual back-and-forth: what was asked, what was answered. Requests are written as they were meant (typos tidied, wording kept). Replies are the substance of what was said, not a paraphrase that loses the point.

Plain English throughout. If a decision was made, write down *why*.

---

## Quick status board

| Thing | State | As of |
|---|---|---|
| Git branch | `frontend3` (frontend4 is being built on the same branch) | 2026-08-04 |
| Route into frontend4 | **Built** — "Frontend 4" button in the Blade header → `/frontend4` | 2026-08-04 |
| Own root view + Vite entry + page dir | **Built** — `f4.blade.php`, `resources/js/f4.jsx`, `resources/js/F4Pages/` | 2026-08-04 |
| Own scoped CSS (`.f4-root`) | **Built & checked** — every rule scoped; no global stylesheet loaded | 2026-08-04 |
| Own tokens + shell component | **Built** — `frontend4/tokens.js`, `frontend4/components/F4Shell.jsx` | 2026-08-04 |
| Same database / same login | **Yes by design** — one `.env`, existing tables and models, existing auth | 2026-08-04 |
| Real screens reading real data | **None yet** — `Home.jsx` is a scaffold page only | 2026-08-04 |
| Design direction + shell + page plan | **Written** — [FRONTEND4-DESIGN.md](FRONTEND4-DESIGN.md) | 2026-08-04 |
| The owner's full specification | **In the repo** — [CARE-ONE-OS-VISUAL-AND-PAGE-SPEC.md](CARE-ONE-OS-VISUAL-AND-PAGE-SPEC.md) | 2026-08-04 |
| Milestone plan | **Written** — [FRONTEND4-MILESTONES.md](FRONTEND4-MILESTONES.md); M0 and M1 ticked off | 2026-08-04 |
| Palette | **Settled** — frontend4 keeps cool grey + indigo; hue off-spec by design, every craft rule adopted | 2026-08-04 |
| First role | **Support worker** — settled. Other roles are added as expansions of the same structure, later | 2026-08-04 |
| Sidebar | **Being reworked** — six areas shown to everyone is wrong for a carer; short role-gated nav instead | 2026-08-04 |
| Next milestone | **M2 — Medication Round — PAUSED** pending the navigation + roles plan | 2026-08-04 |
| Which page to build first | **Planned, not started** — Today/dashboard, then round list, then administration workspace | 2026-08-04 |

---

# Part 1 — Work log

### 2026-08-04 — Scaffold in place, isolation confirmed

**What we did:**
- Reviewed everything committed under frontend4 in `c65cf2a2` and checked it against the isolation requirement.
- Confirmed the four things that keep frontend4 from touching any other page:
  1. Every rule in `frontend4/f4.css` (220 lines) is written under `.f4-root`. The only top-level construct is one `@media (max-width: 600px)` block, and every rule inside it is `.f4-root`-scoped too.
  2. `resources/js/f4.jsx` imports **no** global CSS — no `app.css`, no component-library stylesheet, no `f3.css`. Frontend4's own file is the whole of it, so nothing leaks out and nothing reaches in.
  3. Its own Vite entry, resolving pages only from `./F4Pages/**` — a separate bundle from `app.jsx` (frontends 1/2) and `f3.jsx` (frontend 3).
  4. Its own root view `f4.blade.php`, selected per-action by `F4Controller::useF4Layout()`.
- Confirmed nothing outside frontend4 imports `@frontend4/…`, and frontend4 imports nothing from `@frontend`, `@frontend2` or `@frontend3`.
- Confirmed "same database" is automatic: one Laravel app, one `.env`, one connection. Frontend4 controllers will use the existing tables and models.
- Wrote the rules down in `FRONTEND4-PLAN.md` and started this log.

**Decisions made:**
- Frontend4 is **standalone in look, shared in data**: its own CSS/bundle/layout, but the same database, the same login and the same role model as the rest of the app. Written up as the two rules in the plan.
- Frontend4 gets no tables of its own. If a screen needs data the schema does not hold, that gets raised before building — a schema change affects all four front ends.

**Open questions / what's next:**
- Which screen is built first, and what it is modelled on (the Care One OS UX Specification used for frontend3, the existing frontend1 pages, or something new).
- Whether frontend4 stays on the `frontend3` branch or gets its own.

**Files touched:**
- `docs/care-one-os/FRONTEND4/FRONTEND4-PLAN.md` (new)
- `docs/care-one-os/FRONTEND4/FRONTEND4.md` (new — this file)

### 2026-08-04 — Design direction, shell and page plan (no code)

**What we did:**
- Chose to design the whole front end before building any screen, rather than growing it page by page.
- Wrote [FRONTEND4-DESIGN.md](FRONTEND4-DESIGN.md): the look and its five rules, the desktop shell, the mobile shell, the page set in build order, the component kit, the six required states, the responsive/accessibility floor, and how the CSS isolation survives as the front end grows.
- Grounded it in two things that already exist, so it isn't a guess: the **Care One OS UX Specification** in `docs/care-one-os/FRONTEND3/` for the information architecture, and the **existing backend** (`BuildsMedicationRound`, `MARSheetService`, the existing models) for what the database can actually serve today.

**Decisions made:**
- **Frontend4's look is deliberately the opposite of frontend3's.** Frontend3 is warm ivory + teal; frontend4 is cool grey + a single indigo accent, high-contrast, dense but calm. Two designs of *one* product, not two guesses at a product — mixing them would look like a bug.
- **One accent colour.** Indigo means "this is the action" or "this is where you are", nothing else. Status uses the five tints only, and **always with a word** — never colour alone.
- **Hairlines, not shadows.** The single soft shadow is reserved for things that genuinely float (sheets, drawers, sticky action bar).
- **Navigation is the spec's six areas** — Today, People, Medicines, Operations, Assurance, Settings. Mobile gets a bottom nav of Today / People / Round / More.
- **Phase 1 is five screens**, not the spec's 26-template MVP: Today dashboard → round list → administration workspace → exceptions → CD witness. Five screens built properly establish every pattern the other 47 reuse, and they surface a bad design direction before it's baked into fifty pages.
- **No component library**, ever — that is what keeps the isolation absolute.
- **The six global states get built with the first screen**, not retrofitted, because retrofitting is how they get skipped.
- **Frontend4 reuses the existing backend rather than growing its own.** Same rows, same rules, new interface.

**Open questions / what's next:** (all five are in section 9 of the design doc)
1. Is the scope assumption right — same product, new design?
2. Who is frontend4 for? Carers-on-phones would make the desktop shell over-built and shrink Phase 1 to three screens.
3. Default wording — "Resident" or "Person"?
4. Own git branch, or stay on `frontend3`?
5. HTML wireframes first (as frontend3 got), or straight to built screens from the design doc?

**Files touched:**
- `docs/care-one-os/FRONTEND4/FRONTEND4-DESIGN.md` (new)
- `docs/care-one-os/FRONTEND4/FRONTEND4.md` (status board + this entry)

### 2026-08-04 — Scope settled: versatile, not age- or setting-specific

**What we did:**
- Settled the scope question. Frontend4 covers the **whole Care One OS system**, and it must be **versatile** — not aimed at one age group or one kind of setting. Same front end for a care home, a children's home, supported living, a domiciliary service or an individual.
- Turned that from a slogan into design constraints in section 0 of the design doc, because "versatile" only means something if it rules things out.
- Confirmed the header link into frontend4 already exists — it came with the scaffold commit, so nothing was outstanding there.

**Decisions made:**
- **Nothing about a setting gets baked into the interface.** No hard-coded "Resident", no assumption a person has a room number, a unit, or a care home at all. Nothing that reads wrong in a children's home or in someone's own flat.
- **Terminology comes from configuration, never from the component**, with **"Person" as the neutral default** — it's the wording that needs overriding least often. This also answers the old open question about nav labels.
- **Modules toggle per service mode; interaction patterns do not.** What's on a screen varies. Identity check, outcome coding, mandatory reasons, witnessing and audit capture are identical everywhere and cannot be configured away.
- **No age-specific visual design.** The cool, plain, high-contrast direction suits this — it reads as clinical infrastructure rather than something aimed at a demographic.
- **A screen must look composed when a module is switched off**, not like something broke.
- **Build order must not start with a care-home-only screen.** The Phase 1 five are universal: something is due, it gets given or it doesn't, and exceptions need chasing — true in every setting.

**Open questions / what's next:** down from five to three.
1. Which devices frontend4 is designed around — full desktop + mobile, or phone-first with desktop as a courtesy. (This is about screen size and posture, not about who the software is for; that one is settled.)
2. Own git branch, or stay on `frontend3`?
3. HTML wireframes first, or straight to built screens?

**Files touched:**
- `docs/care-one-os/FRONTEND4/FRONTEND4-DESIGN.md` (section 0 rewritten, section 9 updated)
- `docs/care-one-os/FRONTEND4/FRONTEND4.md` (this entry)

### 2026-08-04 — Today built and verified; the big documentation arrived; milestone plan written

**What we did:**
- Branched to `frontend4` (fast-forwarded from `frontend3`, no commits lost) and built the real shell, the component kit, the Today controller and the Today page.
- **Verified at runtime, not just in source:** `/frontend4` serves only `f4-*.css` and `f4-*.js`. `/frontend3`, `/frontend2` and `/medication/medication-round` all return 200 with **zero** frontend4 references and their own CSS unchanged. Today rendered real data — Neptune House, evening round 14:00–18:00, 1 overdue, 2 of 17 doses recorded, a live out-of-stock supply problem, and a person carrying allergy and swallowing-difficulty flags.
- Fixed a real bug found on the way: `.f4-root a` (0,1,1) was beating `.f4-btn` (0,1,0), so the scaffold's buttons were indigo text on an indigo background — invisible. Same trap frontend3 hit; fixed the same way, with `a:not([class])`.
- Received the big documentation and saved it into the repo as [CARE-ONE-OS-VISUAL-AND-PAGE-SPEC.md](CARE-ONE-OS-VISUAL-AND-PAGE-SPEC.md) — it existed only in a chat window until then.
- Inspected the **live database schema** (249 tables) against what that specification demands, rather than guessing.
- Wrote [FRONTEND4-MILESTONES.md](FRONTEND4-MILESTONES.md): one tick-off list, front end and database as two tracks.

**Decisions made:**
- **Build order now follows your documentation, not my earlier plan.** Medication Round is the current task, with the documented hard stop: show it working before PRN, witnessing, stock deduction or sign-off are added.
- **Database work is a parallel track pulled in by the page that needs it**, not built up front. Nothing in D1–D6 blocks the Round.
- Any shared-table change affects all four front ends, so nothing gets reshaped to suit a frontend4 screen alone.

**The database verdict (asked directly: is anything wrong with it?):** No. It is strongest exactly where being wrong would be dangerous — `mar_administrations` already has a proper append-only correction chain (`is_current`, `supersedes_id`, `superseded_at`, `amendment_reason`); `medication_stock_transactions` records balance before and after with reason and witness; `cd_witness_confirmations` holds a real second account with a recorded override reason; PRN maxima and intervals exist and are enforced inside a row lock. The gaps are places the specification goes *beyond* what was built: free-text allergies (D1), no dm+d codes (D2), no follow-up workflow (D3), no general audit log (D4), handover stored as text blobs rather than assignable items (D5), no competency gating (D6).

**Open — one blocking decision:**
Your visual direction (warm ivory `#F6F2E9` + clinical teal `#176B65`) is **exactly frontend3's palette**. Frontend4 is currently cool grey + indigo, which the design doc justified as deliberately the opposite of frontend3. Your documentation overrides that reasoning. Adopt Quiet Clinical Luxury in full for frontend4, or keep it cool/indigo as a visual alternative? Only M1 depends on the answer.

**Files touched:**
- `docs/care-one-os/FRONTEND4/CARE-ONE-OS-VISUAL-AND-PAGE-SPEC.md`, `FRONTEND4-FUNCTIONAL-PLAN.md`, `FRONTEND4-MILESTONES.md` (new)
- `frontend4/f4.css`, `frontend4/components/F4Shell.jsx`, `frontend4/components/F4Atoms.jsx` (new)
- `app/Http/Controllers/Frontend4/TodayController.php` (new), `resources/js/F4Pages/Today.jsx` (new), `Home.jsx`, `routes/web.php`

### 2026-08-04 — Palette settled, M1 built

**What we did:**
- Settled the palette: **frontend4 keeps cool grey + indigo.** The hue is off-spec by choice, so frontend4 and frontend3 can be put side by side and chosen between.
- Made the distinction explicit in the milestone doc: off-spec *hue* is not off-spec *craft*. Almost everything in the visual specification is about how an interface is built rather than what colour it is, and all of that was adopted.
- Built M1: ten statuses at three intensities in cool equivalents, dot-plus-word status presentation, Manrope + Inter on the specification's size scale, deep-ink sidebar with a thin accent indicator, the card and button hierarchy, one icon family, form rules, table rules, and the `Medicine` and `OutcomeCode` atoms the Round will need.

**Decisions made:**
- **Tabler icons, not Lucide.** `@tabler/icons-react` is already a project dependency and is the same style of family — 24px, 2px stroke, round caps. Using it adds no new shared dependency and the icons tree-shake into frontend4's bundle, so nothing changes for the other three front ends. Wrapped in `F4Icons.jsx` so stroke and size are decided in one place.
- **Instruction and indication stay separate** in the `Medicine` atom. An indication rendered as a directive is a hazard, not a formatting choice.
- **Stored MAR codes are never shown bare.** `OutcomeCode` renders the letter as its full meaning, per the specification.

**Verified:** build passes; `/frontend4` serves only `f4-*.css` and `f4-*.js`; `/frontend3`, `/frontend2` and `/medication/medication-round` all still 200 with zero frontend4 references.

**Next:** M2 — Medication Round, stopping at the documented point before PRN, witnessing, stock deduction and sign-off.

**Files touched:** `frontend4/tokens.js`, `frontend4/f4.css`, `frontend4/components/{F4Shell,F4Atoms,F4Icons}.jsx`, `resources/views/f4.blade.php`, `resources/js/F4Pages/{Today,Home}.jsx`, `docs/care-one-os/FRONTEND4/FRONTEND4-MILESTONES.md`

### 2026-08-04 — The two specifications merged; eight conflicts surfaced

**What we did:**
- The `.docx` supplied was **byte-identical** (same md5) to the copy already in `docs/care-one-os/FRONTEND3/`. There is no third document — the UX Specification plus the pasted visual/page spec is the complete set. Saying so saved a round trip.
- Read the UX Specification end to end (833 lines) rather than the sections skimmed earlier.
- Merged both into [CARE-ONE-OS-MERGED-PLAN.md](CARE-ONE-OS-MERGED-PLAN.md), and built a visual version to look at rather than read: https://claude.ai/code/artifact/8d49b4f3-b17a-439c-9a92-a5645f0d5a39

**Decisions made:**
- **A page is not a menu item.** This dissolves the navigation tension. Eleven pages exist; a support worker navigates to five. The rest are reached in context — you tap a person to open their profile, and open the MAR from inside it. Reaching the MAR from a menu loses the person you meant.
- **Support worker sidebar: Today · Medication round · Missed doses · People · Handover.** Lead adds controlled drugs and stock; manager adds reports; administrator adds administration. Nothing deleted, everything gated.
- **The pharmacist is not "carer plus extras"** — a different set entirely, with no medication round. The one role where that shortcut breaks.
- **Setting-agnostic means four concrete rules:** nouns from configuration, nothing assumes a building, modules switch off cleanly, regulation is a swappable layer. And configuration may never weaken identity checks, outcome coding, audit capture or witnessing.
- **Two new milestones before the round** — M1.5 (role model + outcome vocabulary) and M1.6 (role-gated sidebar) — because every screen reads from both and they are painful to change afterwards.

**Eight conflicts recorded for your decision (C1–C8).** The one that matters most is **C1, the outcome vocabulary**: Spec A, Spec B and the database each use a different set, and it is written into every dose ever recorded, so changing it later means migrating live clinical data.

**Open:** C1 answer; confirmation of the five sidebar items; agreement or objection on C2–C8.

**Files touched:** `CARE-ONE-OS-MERGED-PLAN.md` (new), `FRONTEND4.md`

---

# Part 2 — Conversation record

### 2026-08-04

**Asked:** "I'm going into frontend 4 now. This should be standalone, such that it uses the same database, but the global CSS of the other pages does not change."

**Answered:** That is already how the committed scaffold is set up, and it was checked rather than assumed. All 220 lines of `frontend4/f4.css` are scoped under `.f4-root`; the entry point loads no global stylesheet at all, so frontend4 cannot reach frontends 1/2/3 and they cannot reach it; the bundle, the page directory and the root Blade view are all separate. Same database and same login come for free — it is one Laravel app with one `.env`, and frontend4 controllers will use the existing tables and models rather than any of their own. The rules were written down in `FRONTEND4-PLAN.md` so they survive between sessions. What is not yet decided: which screen gets built first, since `Home.jsx` is only a scaffold page that proves the wiring and reads nothing from the database.

**Asked (choosing what to build first):** "Design first, no code yet" — produce a layout and component plan for frontend4 before any screen is built, so the whole front end is coherent rather than grown page by page.

**Answered:** Wrote [FRONTEND4-DESIGN.md](FRONTEND4-DESIGN.md) rather than starting a screen. The design direction is cool grey with one indigo accent, deliberately the opposite of frontend3's warm ivory and teal, because two front ends of one product should be clearly distinguishable and a half-blend of both would read as a mistake. The navigation is the six areas the UX Specification already agreed (Today, People, Medicines, Operations, Assurance, Settings), so frontend4 isn't a fresh guess at the information architecture. Phase 1 is five screens rather than the spec's 26-template MVP, because five built properly establish every pattern the rest reuse and they reveal a bad direction before it's baked into fifty pages. The doc ends with five things that need your call — the biggest being whether frontend4 is the full product or a phone-first carer app, since that decides whether the desktop shell is needed at all.

**Asked:** "When you say who is it for — it's for the Care One OS system. It's not going to be for just a certain age group, it's going to be versatile, if that makes sense. Does that help in any way? And have you added the link to the Blade page?"

**Answered:** Both. The link was already there — the indigo Frontend 4 button in `resources/views/frontEnd/common/header.blade.php` came with the scaffold commit, and that header is included by the master layouts, so it shows on the old Blade pages you land on after login.

On versatility: it helps more than it might look, because it rules things out rather than just describing an ambition. No hard-coded "Resident", no assumption a person has a room or a unit, terminology taken from configuration with "Person" as the neutral default, modules that toggle per service mode while the safety patterns stay identical, no visual design aimed at an age group, and layouts that still look composed when a whole module is switched off. It also constrains the build order — Phase 1 can't start with a screen only a care home would use, which the current five don't.

It was also flagged that the original question had been asked badly. "Who is it for" was meant as *which devices and working posture* — a carer on a phone mid-round versus someone at a desk — not which people the software serves. That part is still open; the scope question is now closed.
