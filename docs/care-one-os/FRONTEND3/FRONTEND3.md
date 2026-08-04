# FRONTEND3 — master log

**This is the single file for EVERYTHING to do with frontend3.**
**Lives in:** `docs/care-one-os/FRONTEND3/` — the folder that holds all frontend3 paperwork.

Every session, every decision, every question, every bit of work goes in here with the date and time — so you can pick this up from any terminal, any day, without remembering what happened last time.

- The rules and the plan live in [FRONTEND3-PLAN.md](FRONTEND3-PLAN.md).
- This file has **two parts**: the **Work log** (what was done and decided) and the **Conversation record** (what was actually said, both sides). Newest at the bottom in both.

---

## How to use this file

**Part 1 — Work log.** Every time we do frontend3 work, add an entry in this shape:

```
### YYYY-MM-DD HH:MM — short title
**What we did:**
**Decisions made:**
**Open questions / what's next:**
**Files touched:**
```

**Part 2 — Conversation record.** The actual back-and-forth, timestamped: what was asked, what was answered. Requests are written as they were meant (typos tidied, wording kept). Replies are the substance of what was said, not a paraphrase that loses the point. This is the part you read when you want to remember *why* a conversation went the way it did, not just what came out of it.

Plain English throughout. No jargon. If a decision was made, write down *why*, so future-you isn't guessing.

---

## Quick status board

| Thing | State | As of |
|---|---|---|
| `frontend3` git branch | **Created — currently checked out** | 2026-08-04 |
| The driving documentation | **Supplied** — `CARE-ONE-OS-UX-SPECIFICATION.md` (+ original .docx) in this folder | 2026-08-04 |
| The 12 wireframe prototypes it references | **Missing** — not in repo, not in Downloads | 2026-08-04 |
| First build slice chosen | Not yet — spec suggests shell + dashboard + round | 2026-08-04 |
| Stack for frontend3 | **Decided: React + Inertia + Mantine**, fully isolated from frontend2 | 2026-08-04 |
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

## Part 1 — Work log

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

---

### 2026-08-04 11:52 — `frontend3` branch created; the UX Specification arrived

**What we did:**
- Owner committed the two frontend3 documents.
- **Created the `frontend3` git branch** and switched onto it. No code written — no layout, no CSS, no landing-page link. That was deliberate: branch only, until the documentation landed.
- **The documentation landed.** Owner supplied `Care-One-OS-UX-Specification.docx` — the "new documentation to be supplied" the plan was waiting for. It's the Care One OS Product & UX Blueprint, version 1.0 planning baseline, 20 numbered sections plus two appendices.
- Saved it into this folder two ways: the **original `.docx`** (master copy) and a **text conversion `CARE-ONE-OS-UX-SPECIFICATION.md`** so it can be read and diffed in the repo. The conversion carries a provenance header saying the .docx is the master and to re-convert rather than hand-edit.

**What the specification actually says (the short version):**
- **Design direction: "Quiet Clinical Luxury"** — calm, exact, human, visibly trustworthy. Safety-critical information prominent without feeling alarmist.
- **Not an elderly-care template.** One platform, six configurable service modes: supported living, residential, nursing, children's home, domiciliary, pharmacy. Terminology (Person/Resident/Young Person, Site, Team) is renameable per organisation, but configuration must **never** remove identity checks, outcome coding, audit capture or CD witnessing.
- **Six-area navigation:** Today · People · Medicines · Operations · Assurance · Settings.
- **52 primary page templates**, with a **26-template MVP** and a recommended build sequence that starts with domain model / identity / tenancy / permissions / audit before any screens.
- **Its own palette and type**, which are **NOT the same as frontend2's**: ivory `#F6F2E9`, porcelain `#FFFCF7`, warm mist `#EEEAE2`, navy `#17243B`, clinical teal `#176B65`, eucalyptus `#7E9B90`, ink `#202A35`, slate `#626D78`, stone `#D9D4CA`. Headings **Manrope**, body **Inter**. Radius 12–16px, eight-point spacing.
- **Hard rules** that are architecture, not decoration: append-only medication events (corrections are linked events, never overwrites); server-enforced permissions and validation; idempotency keys on administration/stock/witness commits; two distinct authenticated users for CD witnessing with self-witnessing impossible; hard stops that AI cannot override; deterministic safety rules kept separate from generative AI services.
- **Appendix A lists 12 wireframe prototype HTML files** (`careone-dashboard-wireframe.html`, `careone-medication-round-wireframe.html`, etc.).

**Decisions made:**
- This specification is the brief that drives frontend3. It lives in this folder alongside the plan.
- The spec's palette/type is frontend3's own visual language and belongs in **frontend3's own scoped stylesheet** — it must NOT be merged into `frontend/tokens.js`, which is frontend2's. This is exactly the isolation rule the plan warned about, and the spec having a different palette makes the risk concrete rather than theoretical.

**Open questions / what's next:**
1. **The 12 wireframe HTML files are not in the repo and are not in Downloads.** Only three loose HTML files exist there (`administer-modal.html`, `Med Round -standalone-.dc.html`, `resident-meds-card.html`, all dated 2 July) and none match the Appendix A names. Need to know whether those wireframes exist somewhere else, or still need to be produced.
2. **Scope for frontend3's first build has not been chosen.** The spec's own closing line recommends starting with the **global shell, frontline dashboard and medication round**. That is a sensible first slice but the owner has not confirmed it.
3. **Overlap with existing work is unresolved.** frontend2 already has Medication Round, MAR, Stock, Missed Doses and CD pages. The spec covers all of those too. Needs a decision: is frontend3 rebuilding them to this blueprint, or only building the parts frontend2 doesn't have?
4. **Blade vs Inertia is now genuinely in question.** `FRONTEND3-PLAN.md` says frontend3 carries "new **Blade/Laravel** work". The specification names the stack as "Laravel • React • Inertia • Mantine • SQL" and describes component behaviour (drawers, command palette, skeletons) that suits React. These two statements disagree and the owner needs to settle it before any structural code is written.

**Files touched:**
- `docs/care-one-os/FRONTEND3/CARE-ONE-OS-UX-SPECIFICATION.md` (new — text conversion of the spec)
- `docs/care-one-os/FRONTEND3/Care-One-OS-UX-Specification.docx` (new — original master copy)
- `docs/care-one-os/FRONTEND3/FRONTEND3.md` (this entry)
- git: branch `frontend3` created from `main`

---

### 2026-08-04 11:58 — Stack decided; isolation plan made concrete; conversation record started

**What we did:**
- Settled the Blade-vs-React contradiction (see Conversation record below). **frontend3 is React + Inertia + Mantine**, following the specification.
- Inspected how frontend2 is actually wired, so frontend3's isolation could be planned against reality rather than guesswork: `vite.config.js` (single input `resources/js/app.jsx`, aliases `@frontend` and `@frontend2`), `resources/js/app.jsx` (createInertiaApp + MantineProvider + `@frontend/theme` + font init), `resources/views/app.blade.php` (root view, Google Fonts links, font-before-React script), `HandleInertiaRequests` (`$rootView = 'app'`), pages under `resources/js/Pages/{Frontend2,Medication}`, routes appended inside `routes/web.php`.
- Wrote the resulting **concrete isolation file plan** into `FRONTEND3-PLAN.md` as a side-by-side table (what frontend2 uses → what frontend3 gets of its own), and updated that document so it no longer says "Blade/Laravel work".
- Restructured this log into **two parts** — Work log and Conversation record — after the owner pointed out the conversation itself wasn't being captured, only the decisions.

**Decisions made:**
- **frontend3 = React + Inertia + Mantine.** Reason: the specification names that stack explicitly, and its component language (contextual drawers, command palette, skeletons matching final geometry, full-screen mobile drawers) assumes it. The plan document's "Blade/Laravel" wording predated the specification.
- **Same stack as frontend2 means stricter isolation, not looser.** Every layer gets its own file: root view `f3.blade.php`, entry `f3.jsx`, pages `F3Pages/`, theme/tokens `frontend3/`, stylesheet `frontend3/f3.css` scoped under `.f3-root`, middleware `HandleF3InertiaRequests`.
- **Exactly two shared files may be touched, additively only:** `vite.config.js` (add the `f3.jsx` input and `@frontend3` alias — does not alter the frontend2 bundle) and `routes/web.php` (append a `/f3/...` group). Anything else that seems to need an edit to an existing frontend1/2 file is the signal to **stop and copy it into `frontend3/` instead**.
- **This log records the conversation too**, not just outcomes.

**Open questions / what's next:**
- Still unanswered from the previous entry: the **12 missing wireframes**, the **first build slice**, and the **overlap with frontend2's existing med pages**.

**Files touched:**
- `docs/care-one-os/FRONTEND3/FRONTEND3-PLAN.md` (stack decision recorded; isolation plan made concrete; status line updated)
- `docs/care-one-os/FRONTEND3/FRONTEND3.md` (restructured into two parts; this entry; conversation record added)

---

## Part 2 — Conversation record

### 2026-08-04 ~11:40 — "Look into FRONTEND3-PLAN.md and tell me what you understand"

**Asked:** Read `docs/care-one-os/FRONTEND3-PLAN.md` and say what I understand from it.

**Answered:** Summarised it back. frontend3 is a third front end beside frontend1 (old Blade) and frontend2 (Medication 2 / Inertia+React+Mantine), carrying new work driven by documentation not yet supplied, and it's an experiment area that must not break what works. Login stays exactly as it is — old Blade login, landing on the normal old Blade page, post-login redirect untouched — and the only new entry point is a single link on the Blade landing page, added on the frontend3 branch so `main` stays clean. Then the part the document is really about: branch separation protects you *across* branches, but *within* the branch the trap is the word "global" — if frontend3 edits a shared stylesheet it bleeds into 1 and 2, because they all load the same files. So frontend3 brings its own stylesheet, scoped under a root wrapper class, with its own layout and its own build entry, and never touches `public/frontEnd/css/*`, `frontend/tokens.js`, `frontend/lib/font.js`, `resources/js/app.jsx`, the shared Mantine theme, or anything under `resources/js/Pages/` that 1 or 2 import. Also checked the repo and reported that **the `frontend3` branch did not exist yet** — only `main` and `origin/new_tester_branch`. Offered to either create the branch and skeleton, or wait for the documentation.

### 2026-08-04 ~11:45 — "Are you ready? And I want a file with all our conversation, with time and date"

**Asked:** Confirm readiness. Also create a file holding all of our conversation with the time and date — so that if I don't remember what a session went into, it's written down. Call it FRONTEND3 in capitals, since everything concerning frontend would live there.

**Answered:** Created this file. Explained its structure — how-to-use section, a quick status board, the non-negotiables copied in so they're always in front of you, and the first dated entry. Set out the split: `FRONTEND3-PLAN.md` is the rules (rarely changes), `FRONTEND3.md` is the diary (changes every session). Updated the persistent memory so any future session in any terminal knows to open and update this log. Then asked the one open question: create the branch and skeleton now, or wait for the documentation.

### 2026-08-04 ~11:46 — Choice: "Just create the branch, nothing else"

**Asked:** Create the `frontend3` git branch only. No code, no skeleton, no landing-page link, until the documentation arrives.

**Answered:** Agreed and proceeded.

### 2026-08-04 ~11:48 — "I moved both files to a new directory"

**Asked:** Both frontend3 documents have been moved into a new folder — `docs/care-one-os/FRONTEND3/`.

**Answered:** Verified the move, confirmed the cross-link between the two documents still resolves (they moved together, so the relative link still works), fixed the two stale full paths written inside this log, and recorded the move as a dated entry. Established that **the folder is the home for all frontend3 paperwork** and new frontend3 documents go there rather than the `care-one-os` root. Updated memory with the new paths. Then created the `frontend3` branch and reported that both documents were still **untracked by git** — `FRONTEND3-PLAN.md` had never been committed — and that untracked files follow you across branches, so the folder would still appear in the working tree on `main`. Offered to commit; did not commit unasked.

### 2026-08-04 ~11:50 — "I've committed them. And you've seen what I'm trying to achieve — here's the spec"

**Asked:** The documents are committed. Pointed to `C:\Users\lambe\Downloads\Care-One-OS-UX-Specification.docx` as what the Care One OS system is trying to achieve.

**Answered:** Read it — a binary `.docx`, so it was unzipped and its `word/document.xml` converted to markdown preserving headings, lists and tables. Saved into this folder both ways: the original `.docx` as master, and `CARE-ONE-OS-UX-SPECIFICATION.md` as a readable, diffable conversion carrying a provenance header. Then summarised the understanding of it:

> Care One OS is a medication-management platform that refuses to be an elderly-care template — one foundation serving six service modes (supported living, residential, nursing, children's home, domiciliary, pharmacy), each with its own default language and emphasis. Organisations can rename things and switch modules on, but configuration **must never** remove identity checks, outcome coding, audit capture or CD witnessing. Design direction is "Quiet Clinical Luxury" — calm, exact, human, visibly trustworthy; safety-critical information prominent without being alarmist. Six-area navigation (Today · People · Medicines · Operations · Assurance · Settings), 52 page templates, 26 in the MVP.
>
> The parts that are architecture rather than decoration: medication events are **append-only** (a correction is a *linked* event with a reason, both stay visible); **Laravel owns authorisation, validation, transaction boundaries and audit** and React never becomes the security boundary; **idempotency keys** on administration, stock and witness commits; controlled drugs need **two distinct authenticated users** with self-witnessing impossible and offline CD completion disabled; **hard stops** (identity mismatch, inactive prescription, unresolved allergy conflict) block completion and **AI cannot override one**; AI finds, summarises and drafts but never prescribes, administers, witnesses or closes an incident, with deterministic safety rules kept separate from generative services so an AI outage can't stop someone giving a medicine.
>
> And one line singled out as a genuinely good instinct: **"never label every non-administration as missed"** — declined, unavailable, omitted-clinical, omitted-operational, late, part-administered, spat-out, not-required, each with its own minimum capture and follow-up. It stops reporting from erasing context.

Also raised four things needing a decision: the Blade-vs-React contradiction between the plan and the spec; the **12 wireframes in Appendix A that don't exist** (not in the repo, not in Downloads — the only loose HTML there is `administer-modal.html`, `Med Round -standalone-.dc.html` and `resident-meds-card.html` from 2 July, none matching the `careone-*-wireframe.html` names); the spec's palette **not** being frontend2's palette, which makes the isolation rule concrete rather than theoretical; and the unresolved overlap with frontend2's existing Round/MAR/Stock/Missed/CD pages.

### 2026-08-04 ~11:56 — Choice: "React + Inertia + Mantine (follow the spec)"

**Asked:** frontend3 is built in React + Inertia + Mantine, following the specification — its own Vite entry separate from `resources/js/app.jsx`, its own Mantine theme carrying the Quiet Clinical Luxury palette, its own scoped CSS, no shared tokens and no shared theme.

**Answered:** Recorded the decision in both documents, and inspected how frontend2 is actually wired so the isolation could be planned against the real files rather than assumed ones. Produced the side-by-side isolation table now in `FRONTEND3-PLAN.md`, and identified that exactly two shared files need a purely additive touch (`vite.config.js` and `routes/web.php`) with everything else being new files.

### 2026-08-04 ~11:58 — "Is all our conversation being recorded in the log as well?"

**Asked:** Confirm the conversation itself is going into the log, not just the outcomes.

**Answered:** It wasn't — the log was capturing decisions and work only. Fixed it: this file now has two parts, a Work log and this Conversation record, and the whole session up to this point has been written back in retrospectively. Going forward both parts get updated together.
