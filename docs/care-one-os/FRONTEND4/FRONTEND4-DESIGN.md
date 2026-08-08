# Frontend4 — design direction, shell and page plan

**No code. This is the plan the screens get built from.**
Rules live in [FRONTEND4-PLAN.md](FRONTEND4-PLAN.md). Running log is [FRONTEND4.md](FRONTEND4.md).
Written 2026-08-04.

---

## 0. Scope — confirmed 2026-08-04

**Frontend4 covers the whole Care One OS system, with a fresh design.** Not a different product, not a cut-down tool, and **not pitched at one age group or one kind of setting.** It has to be versatile: the same front end serving a care home, a children's home, supported living, a domiciliary service or an individual.

The information architecture is taken from the **Care One OS UX Specification** already in the repo (`docs/care-one-os/FRONTEND3/CARE-ONE-OS-UX-SPECIFICATION.md`), so frontend4 and frontend3 are two designs of one agreed product rather than two different guesses at it.

### What "versatile" forces on the design

This is a constraint, not a slogan. It rules things out:

- **No setting baked into the interface.** No hard-coded "Resident", no assumption that a person has a room number, a unit or a care home at all. Nothing that reads wrong in a children's home or in someone's own flat.
- **Terminology comes from configuration, never from the component.** The spec lets an organisation rename Person, Site and Team per service mode — *Person / home / support team* in supported living, *Resident / unit / care team* in residential, *Young person* in a children's home, *Patient / prescription / supply* in pharmacy. Every frontend4 component takes its noun from config and falls back to the neutral default. **Default wording is "Person"** — it is the one that needs overriding least often.
- **Modules toggle per mode; patterns do not.** What appears on a screen varies by service mode. How it behaves — identity check, outcome coding, mandatory reasons, witnessing, audit capture — is identical everywhere and cannot be configured away.
- **No age-specific visual design.** Nothing styled for "elderly care" and nothing styled for young people. The cool, plain, high-contrast direction in section 1 suits this: it reads as clinical infrastructure rather than as a brochure aimed at a demographic.
- **Layout survives an empty module.** A screen must look composed when a whole section is switched off, not like something broke.

That also means the **build order in section 4 must not start with a screen only a care home would use.** The Phase 1 five are all universal: something is due, it gets given or it doesn't, and the exceptions need chasing. That is true in every setting.

---

## 1. What frontend4 is trying to be

Frontend3 is warm — ivory, clinical teal, soft. Frontend4 is deliberately its opposite: **cool, quiet, high-contrast, and dense without being cramped.** Think an instrument panel that has been tidied, not a brochure.

Its tokens already say this (`frontend4/tokens.js`): near-white cool canvas `#F7F8FA`, white cards, a single indigo accent `#3F4FD6`, near-black ink `#111827`, Figtree. **One accent colour only.** Indigo means "this is the action" or "this is where you are" — nothing else in the interface is allowed to be indigo, which is what makes it readable at a glance on a busy screen.

Five rules that decide most arguments later:

1. **One accent.** Indigo is for the primary action and the current location. Status uses the five tints (neutral / good / caution / risk / info) and nothing else.
2. **Status is never colour alone.** Every tinted thing carries a word — "Given", "Refused", "Overdue". A carer with colour-blindness, or a phone in sunlight, must read the same information.
3. **Hairlines, not shadows.** Separation comes from the 1px `--f4-line` border. The one soft shadow (`--f4-lift`) is reserved for things that genuinely float: sheets, drawers, the sticky action bar.
4. **Whitespace does the grouping.** Eight-point spacing, generous vertical rhythm. If a screen feels busy, remove a divider before removing information — the spec's principle is *calm under pressure*, not *less information*.
5. **Urgency is rationed.** The risk tint appears for genuine clinical risk (overdue dose, CD discrepancy, allergy conflict) and nowhere else. If everything is red, nothing is.

This also has to clear the standing bar for the product: it should look expensive and restrained, closer to a well-made native app than a dashboard template.

---

## 2. The desktop shell

```
┌──────────────────────────────────────────────────────────────────────────┐
│ F4   Oakfield House ▾   Early shift · 07:00–15:00      ⌕   ◍ online   MC │  context bar (56px)
├────────────┬─────────────────────────────────────────────────────────────┤
│            │  Medication round                        Last sync 09:41    │  page header
│  Today   ● │  12 due · 3 overdue · 1 awaiting witness        [ Continue ]│  title · summary · ONE primary action
│  People    ├─────────────────────────────────────────────────────────────┤
│  Medicines │                                                             │
│  Operations│   ┌───────────────────────────────────────────────────────┐ │
│  Assurance │   │  content                                              │ │
│  Settings  │   └───────────────────────────────────────────────────────┘ │
│            │                                                             │
│  ─────────  │                                          ┌────────────────┐ │
│  Handover  │                                          │ context drawer │ │  optional, right side
│            │                                          │ (evidence,     │ │  never replaces the list
│            │                                          │  history)      │ │
└────────────┴──────────────────────────────────────────┴────────────────┘
```

- **Left nav** — the six areas from the spec: Today, People, Medicines, Operations, Assurance, Settings. Labelled icons; collapses to an icon rail on narrow desktop but the current area always stays labelled. Current area marked with an indigo left edge **and** the label in ink weight 600 — position, not colour alone.
- **Context bar** — organisation/home selector, current shift, global search, connectivity, profile. It answers "where am I and as whom" and never scrolls away. Home selection matters: it's the thing that silently changes what every other screen means.
- **Page header** — title, a one-line state summary in plain words, last sync, and **exactly one primary action**. If a screen seems to need two primary actions, it's two screens.
- **Context drawer** — right-hand panel for evidence and detail (prescription source, recent administrations, notes) so you never lose your place in the list. On tablet and below it becomes a full-screen page with back navigation that restores filters and scroll position.

## 3. The mobile shell

Mobile is not a narrowed desktop. It's the primary case for a carer mid-round.

```
┌─────────────────────────┐
│ Doris Ashworth · Rm 12  │  sticky identity header — stays put during administration
│ Allergy: penicillin     │  safety strip, always visible
├─────────────────────────┤
│                         │
│  content                │
│                         │
│                         │
├─────────────────────────┤
│   [ Record given ]      │  sticky action bar, thumb reach
├──┬──┬──┬──┬─────────────┤
│Today│People│Round│More │  bottom nav
└─────────────────────────┘
```

- **Bottom navigation:** Today, People, Round, More. Round is the fat middle target.
- **Sticky identity + safety strip** on every administration journey. Who this is, and what could hurt them, never scrolls off.
- **Primary action in the thumb zone**, bottom of the screen. **Destructive or irreversible actions are separated** — never adjacent to the confirm button, never the same shape.
- **Filters open as full-height sheets** with explicit Apply and Reset. No filter state hidden behind a chevron.
- **Touch targets 44px minimum**, and larger for anything recorded during a round — assume gloves.

---

## 4. The page set, in the order it should be built

Grounded in what the database and existing services can already serve. Frontend4 reuses the existing backend (`BuildsMedicationRound`, `MARSheetService`, the existing models) rather than growing its own — same rows, same rules, new interface.

**Phase 1 — prove the front end (the frontline loop).**

| # | Page | Why first | Data it needs |
|---|---|---|---|
| 1 | **Today / dashboard** | Answers "what's due, what's at risk, what next". Replaces the scaffold `Home.jsx` and proves the database link with real numbers. | Existing round build + closure state |
| 2 | **Round list** | The queue: who is due, who is overdue, who is done. | `BuildsMedicationRound` |
| 3 | **Administration workspace** | The safety-critical screen. Identity → medicine → dose/route/time → outcome → confirmation, in that order, with no step compressible. | Same as above + outcome recording |
| 4 | **Exceptions / missed doses** | Where the risk actually lives; the outcome taxonomy has to be visible somewhere. | Existing missed-dose data |
| 5 | **Controlled-drug witness** | Already built once in frontend3 (`CdWitnessConfirmation`), so it's a known quantity in a new shell. | `CdWitnessConfirmation` |

**Phase 2 — the record.** MAR chart; administration detail + correction/addendum; person overview; medication profile; PRN dashboard and outcome review.

**Phase 3 — operations and oversight.** Stock overview and ledger; ordering; CD register; shift handover; manager dashboard and reports.

Settings, pharmacy and AI workspace are last; they share patterns with everything above and none of them are frontline safety.

> The spec lists 52 templates with an MVP target of 26. Phase 1 is five. That is deliberate — five screens built properly establish every pattern the other 47 reuse, and it's how you find out whether the design direction survives contact with real data before it's baked into fifty pages.

---

## 5. Components to build (the frontend4 kit)

Everything lives in `frontend4/components/`, styled by classes under `.f4-root` in `f4.css`. **No component library** — that's what keeps the isolation absolute.

Already built: `F4Shell` (top bar + content column), `.f4-card`, `.f4-btn`, `.f4-badge`, `.f4-list`, `.f4-stack`, `.f4-page-head`, `.f4-actions`.

To build, roughly in this order:

| Component | Job | Notes |
|---|---|---|
| `F4Shell` (extend) | Left nav + context bar + page header; bottom nav on mobile | Replaces the current placeholder top bar once there's a second screen |
| `PersonIdentity` | Photo/initials, name, room, DOB — the identity block | Never abbreviated on an administration screen |
| `SafetyStrip` | Allergies, alerts, capacity flags | Persistent, risk tint, always with words |
| `StatusBadge` | Given / Refused / Missed / Withheld / Not available / Overdue | Word + tint, never tint alone |
| `Stat` | One number with a label, for the dashboard | Big number, quiet label, no sparkline decoration |
| `MedicineRow` | Medicine, strength, form, dose, route, time | The most-repeated unit in the product — get it right once |
| `OutcomeControl` | Records the outcome, forces a reason where a reason is mandatory | Reason field appears *before* confirm, not after |
| `ConfirmBar` | Sticky primary action; separated destructive actions | Mobile thumb zone |
| `Drawer` / `Sheet` | Desktop right drawer; mobile full-height sheet | Same component, two presentations |
| `Empty` / `ErrorState` / `Skeleton` / `OfflineBanner` | The six global states below | Built early — retrofitting states is how they get skipped |
| `FilterSheet` | Full-height on mobile, inline on desktop; Apply + Reset | |

## 6. The six states, every screen

From the spec, and non-negotiable:

| State | How frontend4 shows it |
|---|---|
| Loading | Skeleton matching the final geometry; the context header stays visible so you don't lose your place |
| Empty | Say *why* it's empty and offer the next safe action — never a bare "No results" |
| Error | Plain-language cause, a retry, a safe fallback, and a reference the office can quote |
| Offline | Persistent banner with the queued-write count and explicit sync status. Not a toast — a toast that's been dismissed is a lie |
| No permission | State the restriction without revealing the content, and give the approved route |
| Conflict | Both versions, timestamps, who changed what; resolution has to be a deliberate choice |

## 7. Responsive and accessibility floor

- **Mobile first.** Design at 390px, then 768, then 1280. If it only works at 1280 it isn't finished.
- **WCAG 2.2 AA** on every critical journey: 4.5:1 text contrast, visible focus on everything reachable, full keyboard operability, focus moved and trapped correctly in drawers and sheets, errors announced and tied to their field.
- **No horizontal body scroll at any width.** Wide things (MAR grid, CD register) scroll inside their own container.
- **Status never colour-only** — stated three times in this document because it's the rule most likely to get quietly broken.

## 8. Keeping the isolation as it grows

- Every new rule goes in `frontend4/f4.css` under `.f4-root`. One flat namespace, `.f4-*`, matching the component name.
- Change `tokens.js` and the custom properties at the top of `f4.css` **together** — they mirror each other by hand.
- No new stylesheet import in `resources/js/f4.jsx`. Ever. That single line is what guarantees the other three front ends can't be touched.
- New controllers extend `F4Controller` and call `useF4Layout()` as the **first line of the action**.

---

## 9. Decided here vs. still your call

**Decided (say if you disagree):** cool/indigo direction and one-accent rule; the six spec areas as the navigation; mobile bottom nav of Today/People/Round/More; five Phase-1 screens in that order; no component library; states built with the first screen rather than retrofitted.

**Settled 2026-08-04:**
- **Scope** — the whole Care One OS system with a fresh design (section 0).
- **Versatility** — setting-agnostic and age-agnostic, terminology from configuration, neutral default wording **"Person"** (section 0).

**Still open:**
1. **Which devices frontend4 is designed around** — this is about screen size and posture, not about who the software is for. Everything in section 0 stays true either way. The question is whether frontend4 is built desktop-and-mobile (the full shell in section 2, all five Phase-1 screens) or phone-first with desktop as a courtesy (drop the left nav and context drawer, Phase 1 shrinks to three screens). It changes how much gets built before there is anything to look at.
2. **Branch** — frontend4 is currently on the `frontend3` branch. Own branch, or stay?
3. **Wireframes** — frontend3 got 12 HTML wireframe screens before code. Does frontend4 want the same (scoped `.f4-*` CSS, no React), or straight to built screens from this document?

---

## 10. Typography (current — supersedes the Figtree/cool-indigo notes above)

**Settled 2026-08-06.** Frontend4 pivoted to the warm cream/navy/teal look and a
two-typeface system. The older mentions of Figtree and the cool/indigo canvas
above are historical — the live values are here and in `frontend4/f4-theme.css`
(colours) + `frontend4/f4.css` (the `--f4-t-*` scale and component rules).

**Two typefaces, no more:**
- **Manrope** — headings, card/section titles, the big numbers (e.g. the "20:00"
  next-dose time). Loaded 500/600/700/800.
- **Inter** — everything else: body, labels, values, buttons, table text. Loaded
  400/500/600/700/800.

Both are loaded in `resources/views/f4.blade.php` (Google Fonts) with Segoe UI /
Arial as the fallback. `body` is set to Inter there too, so nothing can fall
back to the serif default outside `.f4-root`.

**The look is layered, not multi-font.** What can read as "three fonts" in a row
is Manrope on the title and Inter doing three jobs (bold name, muted sub, tiny
uppercase label). One typeface, three treatments.

### The typographic roles (font · size · weight · treatment · colour)

Worked example — a profile tab section header + a medicine row:

| Role | Where | Font | Size | Weight | Case / tracking | Colour |
|---|---|---|---|---|---|---|
| Eyebrow | `CURRENT PRESCRIPTIONS` | Inter | 9px | 800 | UPPERCASE · +0.07em | teal `--colour-teal` |
| Section title | `Medications` | **Manrope** | 16px | 700 | −0.01em | navy `--colour-navy` |
| Description | intro line under a title | Inter | 11px | 400 | normal | muted `--colour-text-muted` |
| Item name | `Pregabalin` | Inter | 12–13px | 700 | normal | ink `--colour-navy` |
| Item sub | `75 mg capsule` | Inter | 10.5px | 400 | normal | muted |
| Micro-label | `DOSE & ROUTE` | Inter | 9px | 800 | UPPERCASE · +0.04em | muted |
| Micro-value | `1 capsule · Oral` | Inter | 11–12px | 650 | normal | ink |
| Big number | the `20:00` time | **Manrope** | 30px | 700 | −0.02em | ink |
| Client name (header) | `Aisha Bello` | **Manrope** | 24–27px | 700 | −0.02em | ink |

Container that holds them: ivory surface, 1px `--colour-border`, 15px radius,
soft lift shadow `--panel-shadow` (`0 10px 30px rgba(23,36,59,.055)`), hairline
`--colour-divider-soft` between rows, ~16–18px header padding.

### Relationship to the owner's reference (`client-profile.html/.css`)

The Overview layout and this visual language come from the owner's reference.
**Two deliberate divergences:** (1) titles use **Manrope** (the reference left
`h2` as Inter); (2) sizes are bumped ~1–3px for readability (the reference runs
8–10px). The reference's "simple-panel" tab stubs were **not** followed — the
tabs are built out to the Overview's density (Rx tiles, status tags, meta grid).
