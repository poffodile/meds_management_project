# FRONTEND4 — master log

**This is the single file for EVERYTHING to do with frontend4.**
**Lives in:** `docs/care-one-os/FRONTEND4/` — the folder that holds all frontend4 paperwork.

Every session, every decision, every question, every bit of work goes in here with the date — so you can pick this up from any terminal, any day, without remembering what happened last time.

- The rules and the plan live in [FRONTEND4-PLAN.md](FRONTEND4-PLAN.md). The merged product plan is [CARE-ONE-OS-MERGED-PLAN.md](CARE-ONE-OS-MERGED-PLAN.md). **Anything found that needs doing goes in [FRONTEND4-ISSUES.md](FRONTEND4-ISSUES.md)** — nothing is closed there until it is done, not until it is worked around. Every issue also has a test case in [FRONTEND4-TEST-LOG.md](FRONTEND4-TEST-LOG.md), so a fix can be proved and an open gap can be demonstrated rather than described.
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
> **▶ Picking this up again? Jump to [RESUME HERE](#-resume-here--2026-08-04) at the bottom of this file.**

| Git branch | **`frontend4`** — own branch, fast-forwarded from `frontend3`. **Everything uncommitted.** | 2026-08-04 |
| Route into frontend4 | **Built** — "Frontend 4" button in the Blade header → `/frontend4` | 2026-08-04 |
| Own root view + Vite entry + page dir | **Built** — `f4.blade.php`, `resources/js/f4.jsx`, `resources/js/F4Pages/` | 2026-08-04 |
| Own scoped CSS (`.f4-root`) | **Built & checked** — every rule scoped; no global stylesheet loaded | 2026-08-04 |
| Own tokens + shell component | **Built** — `frontend4/tokens.js`, `frontend4/components/F4Shell.jsx` | 2026-08-04 |
| Same database / same login | **Yes by design** — one `.env`, existing tables and models, existing auth | 2026-08-04 |
| Real screens reading real data | **Two built** — Today (`/frontend4`) and the medication round (`/frontend4/round`), both on live data | 2026-08-04 |
| Roles & permissions | **Built** — 40 access-level names → four roles; enforced server-side. Closed a hole where any logged-in user could reach medication management | 2026-08-04 |
| Outcomes | **Nine of ten** recording. *Part administered* deferred — needs a quantity; decision open | 2026-08-04 |
| Issue log | **17 issues** — [FRONTEND4-ISSUES.md](FRONTEND4-ISSUES.md); I1 and I16 closed | 2026-08-04 |
| Test log | **Written** — [FRONTEND4-TEST-LOG.md](FRONTEND4-TEST-LOG.md), a case per feature and per issue | 2026-08-04 |
| Test suite baseline | **14 errors / 3 failures** (pre-existing, [I12](FRONTEND4-ISSUES.md#i12)). Any other result = something changed | 2026-08-04 |
| Design direction + shell + page plan | **Written** — [FRONTEND4-DESIGN.md](FRONTEND4-DESIGN.md) | 2026-08-04 |
| The owner's full specification | **In the repo** — [CARE-ONE-OS-VISUAL-AND-PAGE-SPEC.md](CARE-ONE-OS-VISUAL-AND-PAGE-SPEC.md) | 2026-08-04 |
| Milestone plan | **Written** — [FRONTEND4-MILESTONES.md](FRONTEND4-MILESTONES.md); M0 and M1 ticked off | 2026-08-04 |
| Palette | **Settled** — frontend4 keeps cool grey + indigo; hue off-spec by design, every craft rule adopted | 2026-08-04 |
| First role | **Support worker** — settled. Other roles are added as expansions of the same structure, later | 2026-08-04 |
| Sidebar | **Built** — five items for a carer, more added by role. Nothing deleted, everything gated | 2026-08-04 |
| Terminology | **`client`** is canonical (already is — `client_id` in 37 tables). Display label stays configurable | 2026-08-04 |
| Next milestone | **M3 — waiting on you to look at the round.** PRN, CD witnessing, stock deduction and sign-off come after | 2026-08-04 |

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

### 2026-08-04 — M1.5 and M1.6 built: roles, permissions, role-gated sidebar

**What we did:**
- Built `App\Services\Frontend4\RoleResolver` — the only place the 40 access-level names are mapped onto four roles (carer, lead, manager, admin, plus "none").
- Built `App\Services\Frontend4\Permissions` — what each role may do, with roles inheriting the one below.
- Extended `F4Controller` with `role()`, `can()`, `requirePermission()`, `requireMedicationAccess()` and `roleProps()`.
- **Closed a real hole.** `TodayController` previously admitted user types `N, A, M, CM, O` — every type there is — so anyone who could log in could reach medication management. It now resolves a role and refuses anyone without medication access.
- Built `frontend4/roles.js` and rewired `F4Shell` so the sidebar is generated from permissions: five items for a carer, more added by role.
- Bottom nav fixed at four items on purpose — a bar that changed length by role would move the target a carer reaches for without looking.

**Verified:** build passes; the demo user resolves to **manager** with 11 permissions (inheriting `record_administration`, gaining `manage_staff`, not granted `define_roles` or `manage_settings`); `/frontend3`, `/frontend2` and `/medication/medication-round` all still 200.

**Decisions confirmed by the owner:**
- Senior Staff / Senior RSW / Team Leader / Sr Supervisor = **shift leads** — they can witness, correct and reopen. Without it, a senior alone on nights has nobody to witness a controlled drug with, which stops the dose entirely.
- "Account Manager" = **finance, no medication access at all** — and that beats their account type, so a finance account typed `A` does not become a clinical administrator on a technicality.
- "Agent" = **agency worker**, so a carer.
- Pharmacist and registered nurse **dropped for now**; four roles, not six.

**Flagged, needs the owner's call:**
1. **Managers can record doses as built**, because roles inherit. The approved matrix said they could not. Inheriting looks right — a manager covering a shift must be able to give medicines — but it differs from sign-off.
2. **M1.7, the outcome codes.** `CODE_LABELS[code]` is a plain lookup used across **20 screens** in frontends 1 and 2; an unknown code renders blank. Doing the ten outcomes properly needs a small fallback added to `frontend/lib/medicationCodes.js` — a shared file that frontend4's isolation rule says not to touch. Split out rather than slipped in.

**Files touched:** `app/Services/Frontend4/{RoleResolver,Permissions}.php` (new), `app/Http/Controllers/Frontend4/{F4Controller,TodayController}.php`, `frontend4/roles.js` (new), `frontend4/components/F4Shell.jsx`, `resources/js/F4Pages/Today.jsx`

### 2026-08-04 — M1.7: nine of the ten outcomes, and one deliberately deferred

**What we did:**
- Owner confirmed both open questions: **managers can record doses** (they are "practically staff with more access" — which is the inheritance model already built), and **go ahead with the small shared change** rather than let the other pages be affected.
- Widened `CODE_LABELS` in `frontend/lib/medicationCodes.js` to cover the new codes, while leaving `MED_CODES` at the original six. **Two lists on purpose:** what a user is *offered* versus what a stored code *means*. Lookups widen; dropdowns do not. So no journey in frontend1 or frontend2 changes, and none of their ~20 screens can render a blank where an outcome should be.
- Added `AW` (away), `OP` (omitted — operational), `VO` (vomited/spat out), `NR` (not required) to the server validation and, where they need one, to the reason-required list.
- Built `App\Services\Frontend4\Outcomes` — frontend4's vocabulary, with `isGiven()`, `needsReason()`, `status()` and per-outcome reason hints.

**Deferred, deliberately: `PA` (part administered).**
Seven places in `BuildsMedicationRound` decide "did the medicine go in?" by comparing `code === 'A'` inline — lines 151, 452, 467, 479, 533, 578, 593. They gate the PRN daily count, the controlled-drug witness, the no-numeric-dose block, the zero-stock refusal, the PRN maximum/interval check, the amendment test and the stock deduction. Part administered means medicine *did* go in, so all seven would silently answer "no": no witness, no stock movement, no PRN counting, and no error. That is the same failure the file already documents for `'S'` — a sleeping child burning their PRN allowance and being refused pain relief on waking.

**Nothing was fixed there.** The seven lines are untouched. The problem was *avoided* by checking them before adding codes and leaving out the one outcome that would trip over them.

**New milestone M1.8** — replace those seven comparisons with one shared helper (`Outcomes::isGiven()`, already written), with tests around it, then enable `PA`. It changes logic all four front ends run, so it is its own piece of work.

**Verified honestly:** the medication suites were run **before and after** the shared change by stashing only the two shared files. Before: 14 errors, 3 failures. After: 14 errors, 2 failures. The failures are **pre-existing**, not introduced here — and the 3→2 difference is not claimed as a fix. All five pages (`/frontend4`, `/frontend3`, `/frontend2`, `/medication/medication-round`, `/medication/missed-doses`) return 200.

**Files touched:** `frontend/lib/medicationCodes.js` *(shared — agreed with the owner)*, `app/Http/Controllers/frontEnd/Medication/Concerns/BuildsMedicationRound.php` *(shared — validation + reason list only)*, `app/Services/Frontend4/Outcomes.php` (new)

### 2026-08-04 — M2 (the medication round) and M1.8 both built

**What we did:**
- **M2 — the medication round.** `RoundController` + `F4Pages/Round.jsx`: the queue grouped by urgency rather than alphabet, search, a round switcher, progress in both doses and people, and the chosen client's medicines beside the list. Recording goes through the shared `applyRecord()`, so no second set of rules exists.
- **M1.8 — one definition of "given".** The owner asked why it could not be done now, and the answer was that it could. Built `App\Services\Medication\DoseOutcome` (neutral namespace — it is shared logic, not frontend4's) and pointed all call sites at it, including `Frontend4\Outcomes::isGiven()`.

**The correction that mattered:** I had logged this as **seven** comparisons. It was **ten** — the first search only covered one file. The three missed were the most consequential: `MARSheetService:184` and `:237` write the **persisted `given` column** into the database, and `MarChartController:123` counts given doses on the MAR chart. A disagreement in the first two is a wrong clinical record, not a display bug.

**Why it was safe to do with a red test suite:** the refactor is provably a no-op. `GIVEN_CODES` holds only `'A'`, so every call returns exactly what the inline comparison returned. Doing it while it changed nothing was cheap and verifiable; doing it later, when it had to change something, would have been neither.

**Verified:**
- Medication suites: **14 errors / 3 failures before and after** — identical, so behaviour-neutral.
- Round page loads on real data (Sofia Martins, Room C3, allergies tree nuts + latex, one overdue dose at 14:00).
- **Mandatory reason enforced:** posting a decline with no reason returned **422** with the server's own wording and wrote **zero rows**.
- **Recording works and is append-only:** recording as given wrote a new row with `given = 1`, flipped the previous entry's `is_current` 1 → 0 rather than overwriting it, and wrote a stock transaction.

**New issue found while verifying — [I16](FRONTEND4-ISSUES.md#i16), safety-level.** A 5ml dose recorded against 3ml of stock produced a ledger row reading `balance_before 3.00, quantity 5.00, balance_after 0.00`. Three minus five is not zero — the balance clamps silently instead of refusing. There is a guard for *zero* stock but none for *insufficient* stock. On a controlled drug this is a register that does not balance, which is precisely what the discrepancy workflow exists to detect. **Pre-existing** — the deduction arithmetic was untouched by M1.8.

**Files touched:** `app/Services/Medication/DoseOutcome.php` (new), `app/Http/Controllers/Frontend4/RoundController.php` (new), `resources/js/F4Pages/Round.jsx` (new), `routes/web.php`, and the ten call sites across `BuildsMedicationRound.php`, `MARSheetService.php`, `MarChartController.php`, `Frontend4/Outcomes.php`

**Next:** this is the stop-and-look point. The round works; PRN, witnessing, stock deduction and sign-off wait until it has been seen.

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

---

### 2026-08-04 (continued) — the rest of the session

**Asked:** which devices, own branch, wireframes?
**Answered/decided:** desktop **and** mobile both properly · **own `frontend4` branch** (fast-forwarded from `frontend3`, no commits lost) · **straight to built screens**, no wireframes.

**Asked:** "I don't like seeing all those options on the side. I'd rather have the main 4 or 5. But different roles… the current pages are useful too, maybe for admin."
**Answered:** that is a role question wearing a navigation costume. **A page is not a menu item.** Eleven pages exist; a carer *navigates* to five. The rest are reached in context — you tap a client to open their profile, and open the MAR from inside it. Reaching the MAR from a menu loses the client you meant. So nothing is deleted; it is gated. The long list belongs to the administrator, and to us.

**Asked:** "Make it a plan I can see on the screen, not you typing out what each thing does."
**Answered:** built two pages instead of prose —
· **Plan** https://claude.ai/code/artifact/8d49b4f3-b17a-439c-9a92-a5645f0d5a39 (sidebar drawn per role, where the 11 pages live, the roles found in the database, the 8 conflicts)
· **Milestones** https://claude.ai/code/artifact/654f4328-e604-4cc1-9755-c3092feeb3ef (every milestone in plain English, after "I don't know what M2 is")

**Asked:** send the documentation.
**Answered:** the `.docx` was **byte-identical** to the copy already in the repo. No third document — the UX Specification plus the pasted visual/page spec is the whole set. Merged into `CARE-ONE-OS-MERGED-PLAN.md` with eight conflicts (C1–C8) surfaced rather than silently resolved.

**Asked:** is anything wrong with the database?
**Answered:** no. It is strongest exactly where being wrong would be dangerous — an append-only correction chain on `mar_administrations`, `balance_before`/`balance_after` on stock movements, a real second account for CD witnessing, PRN maxima enforced inside a row lock. The gaps are where the specification goes *beyond* what was built, not breakage.

**Asked:** what roles already exist?
**Answered:** two tiers — `user_type` (N 281 · A 75 · M 45 · CM 11 · O 2) and `access_level`, **82 levels across 46 homes under 40 different names**. Four of the five role groups already exist in some form. The finding that mattered: the new `/medication/*` pages had **no permission rows at all** — anyone who could log in could reach medication management.

**Asked:** "My company calls the people clients — people can also be staff."
**Answered:** you were right and my C3 recommendation was wrong. The database already says `client_id` in **37 tables** plus dozens of `client_*` tables, and "people" genuinely is ambiguous when staff are people too. `client` is canonical; the display label stays configurable.

**Asked:** managers should edit clinical records, but with a permanent log; and manage staff.
**Answered:** that *is* the correction model, already in the schema — `supersedes_id`, `is_current`, `amendment_reason`. My matrix row saying "never" meant *overwrite or delete*, which stays impossible for everyone. Managers manage staff **in their own homes**; defining what a role may do stays with the administrator, or a manager could grant themselves anything.

**Asked:** "Managers should record doses — they're practically staff with more access."
**Answered:** exactly how it was built. Roles inherit the one below.

**Asked:** why can't we fix M1.8 now?
**Answered:** we could, and the deferral did not survive the question. The refactor is **provably a no-op** — the "given" list holds only `'A'`, so every call returns what the old comparison returned. Cheap and verifiable while it changes nothing; expensive and risky later. **Correction on doing it: it was ten sites, not seven.** The first search only covered one file, and the three missed were the worst — two write the persisted `given` column.

**Asked:** where did "part administered" come from?
**Answered:** your own UX Specification, section 10. It is not a way of giving medicines — it records what happened when only some of the dose went in. Neither "given" nor "refused" would be true. It is also the **only** thing M1.8 was blocking.

**Asked:** fix I16, then let me look at the round.
**Answered:** fixed. The dose is still recorded — refusing to record a dose someone physically gave is the worse error — but the ledger now tells the truth: `administered 3.00 → −2.00`, then a labelled `correction −2.00 → 0.00` naming the shortfall. The `?` I had flagged in the reason text turned out to be the Windows MySQL client mis-displaying an em-dash; nothing was wrong, so nothing was "fixed".

**Asked:** make an issue log; then add the issues to the test log.
**Answered:** `FRONTEND4-ISSUES.md` (17 issues, severity-rated, nothing closed until done) and `FRONTEND4-TEST-LOG.md` (a test per issue — ✅ proves a fix, ⚠️ demonstrates a gap, so I3 reads "give a client a medicine they're allergic to; nothing warns you" rather than a paragraph).

---

# ▶ RESUME HERE — 2026-08-04

**Branch:** `frontend4` · **Everything is uncommitted** (nothing has been committed this session).

**Say:** *"continue frontend4"*

### Where we stopped
The medication round is **built and working**, and you were about to open it and tell me what is wrong with it. That look is the gate — **M3 (PRN, controlled-drug witnessing, stock deduction, sign-off) waits until you have seen it.** That is your own instruction from the specification and it is the right one.

**To run it:**
```
"C:/laragon/bin/mysql/mysql-8.4.3-winx64/bin/mysqld.exe" --datadir=C:/laragon/data/mysql-8.4
"C:/laragon/bin/php/php-8.3.30-Win32-vs16-x64/php.exe" -S 127.0.0.1:8000 serve-local.php
```
then `http://127.0.0.1:8000/dev-login` → `http://127.0.0.1:8000/frontend4/round`

### Done
M0 isolation · M1 design system · M1.5 roles & permissions · M1.6 role-gated sidebar · M1.7 nine outcomes · M1.8 one definition of "given" · M2 the medication round · I16 stock ledger reconciles

### Open decisions waiting on you
1. **Part administered** — do you want it as an outcome? M1.8 is done so it is now a one-line change, but it needs a quantity captured. If you do not want it, nothing is waiting.
2. **I2** — the medication pages in frontends 1, 2 and 3 are still reachable by anyone who can log in. Add permission rows, or apply the same role check? It touches three live front ends, so it is not mine to just do.

### Owed
- A regression test for the I16 fix (verified against live data, which is weaker).
- **I12** — the suite is red at **14 errors / 3 failures**. That is the baseline; any different result means something changed. Until it is triaged, every "identical before and after" check in this session is weaker evidence than it should be.

### The files that hold everything
| File | What it is |
|---|---|
| `FRONTEND4.md` | this log — work log + conversation record + this resume point |
| `FRONTEND4-MILESTONES.md` | the build plan, M0 → M12 plus the database track |
| `CARE-ONE-OS-MERGED-PLAN.md` | both specifications merged; the C1–C8 conflicts and how they were settled |
| `CARE-ONE-OS-VISUAL-AND-PAGE-SPEC.md` | your pasted specification, saved verbatim |
| `FRONTEND4-ISSUES.md` | 17 issues, severity-rated |
| `FRONTEND4-TEST-LOG.md` | a test case per feature and per issue |
| `FRONTEND4-PLAN.md` | the isolation rules |
| `FRONTEND4-DESIGN.md` | the design direction |
