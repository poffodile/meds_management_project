# Frontend4 — feature test log

Manual test cases for everything built in frontend4, plus a test for every issue in
[FRONTEND4-ISSUES.md](FRONTEND4-ISSUES.md) — so a fixed issue can be proved fixed, and an
open one has a step that demonstrates it rather than a paragraph describing it.

**Status key:** ☐ not yet run · ✅ pass · ❌ fail (note what broke) · ⚠️ known gap, expected to fail

> **"Auto-verified"** means I confirmed it myself — by build, by HTTP response, or by
> reading the database afterwards. It is not a click-through. Anything marked ☐ still needs
> a human in a browser.
>
> **Precondition for everything:** MySQL running, `php -S 127.0.0.1:8000 serve-local.php`
> from the project root, and signed in via `/dev-login`. Hard refresh (Ctrl+F5) after a
> rebuild.

---

## ✅ Acceptance checklist — tick when you're happy each works

- [x] **Isolation** — frontend4 loads only its own CSS/JS; frontends 1–3 unchanged — ✅ auto-verified 2026-08-04
- [x] **Roles resolve** — the signed-in user gets one of four roles from their access level — ✅ auto-verified (demo user → manager, 11 permissions)
- [x] **Medication access is refused** to anyone without it — ✅ auto-verified (replaced a check that admitted every user type)
- [x] **Short sidebar** — five items for a carer, more by role — ✅ auto-verified
- [x] **Nine outcomes** offered, older screens can still name them — ✅ auto-verified
- [x] **One definition of "given"** — all ten sites use the shared helper — ✅ auto-verified (tests identical before/after)
- [x] **Round records a dose** — writes, attributes, supersedes rather than overwrites — ✅ auto-verified
- [x] **Mandatory reason enforced** server-side — ✅ auto-verified (422, nothing written)
- [x] **Stock ledger reconciles** — ✅ auto-verified ([I16](FRONTEND4-ISSUES.md#i16))
- [ ] **The round, in a browser** — the thing that still needs your eyes
- [ ] **Mobile** — the round at 390px wide
- [ ] **Keyboard only** — record a dose without touching the mouse

---

## M0 — Isolation

- **Auto-verified:** `/frontend4` serves only `f4-*.css` and `f4-*.js`; `/frontend3`, `/frontend2` and `/medication/medication-round` return 200 with zero frontend4 references.
- ☐ **T1** Open `/frontend4`. It is styled — cool grey background, indigo accent.
- ☐ **T2** Open `/frontend3`. Still warm ivory and teal, visually unchanged.
- ☐ **T3** Open `/frontend2` and `/medication/medication-round`. Unchanged.
- ☐ **T4** View source on `/frontend4`: exactly one stylesheet, `f4-*.css`. No `app-*.css`, no `styles-*.css`, no `f3-*.css`.

## M1 — Design system

- **Auto-verified:** builds; Today and the round render from the shared atoms.
- ☐ **T1** Every status shows a **word**, not just a colour — "Overdue", "Given", "Due now".
- ☐ **T2** Squint, or set the display to greyscale. You can still tell overdue from given.
- ☐ **T3** No filled colour blocks — statuses are a dot plus a word plus a thin edge.
- ☐ **T4** Headings are Manrope, body text is Inter. Nothing smaller than 12px.
- ☐ **T5** Cards have a thin border and an almost-invisible shadow. No nested cards.

## M1.5 — Roles and permissions

- **Auto-verified:** demo user resolves to `manager` with 11 permissions; `define_roles` and `manage_settings` correctly absent.
- ☐ **T1** The context bar top-right shows your name **and** your role.
- ☐ **T2** Sign in as a support-worker account. Role reads "Support worker".
- ✅ **T3** A user whose access level maps to no medication access gets **403** — auto-verified by code path; ☐ still worth confirming with a real finance account.
- ☐ **T4** **Server-side, not just hidden.** As a carer, POST directly to `/frontend4/round/record` with a permission you do not hold. It must be refused, not merely absent from the screen.
- ☐ **T5** A finance ("Account Manager") account is refused **even though** its account type is `A`. The access level beats the account type.

## M1.6 — The sidebar

- ☐ **T1** As a carer: exactly five items — Today, Medication round, Missed doses, Clients, Handover.
- ☐ **T2** As a shift lead: the five plus Controlled drugs and Stock, under an "Oversight" heading.
- ☐ **T3** As a manager: the above plus Reports & audit.
- ☐ **T4** As an admin: everything, plus Administration.
- ☐ **T5** The current item is marked **three ways** — a thin left edge, a lifted background and heavier text. Not colour alone.
- ☐ **T6** At phone width the sidebar is replaced by a bottom bar of four fixed items. It does **not** change length by role — the target stays where a carer's thumb expects it.

## M1.7 — The nine outcomes

- **Auto-verified:** the outcome list reaches the page; the server accepts `A,S,R,W,N,O,AW,OP,VO,NR`.
- ☐ **T1** The outcome dropdown offers nine: Given, Declined, Asleep, Away, Not available, Omitted — clinical, Omitted — operational, Vomited or spat out, Not required.
- ☐ **T2** Choose **Declined**. A "Why?" field appears **before** the Record button, with a hint.
- ☐ **T3** Choose **Asleep**. No reason is demanded — the code already says why.
- ☐ **T4** Choose **Away** or **Not required**. Also no reason demanded.
- ☐ **T5** Record something as **Omitted — operational** in frontend4, then open the same dose in frontend2's round. It reads "Omitted — operational", **not** a blank.
- ☐ **T6** frontend1 and frontend2 dropdowns still offer their original six. Nothing new appeared there.

## M1.8 — One definition of "was it given"

- **Auto-verified:** all ten call sites use `DoseOutcome::isGiven()`; medication suites returned 14 errors / 3 failures **before and after** — identical.
- ☐ **T1** Record a dose as **Given** on a medicine with tracked stock. Stock goes down.
- ☐ **T2** Record one as **Asleep**. Stock does **not** move, and it does not count toward a PRN daily maximum.
- ☐ **T3** Record a **controlled drug** as Given with no witness. It is refused, with the server's message.
- ☐ **T4** Search the code for `code === 'A'`. There should be no functional match left — only comments explaining why.

## M2 — The medication round

- **Auto-verified:** page loads on real data; recording writes and attributes; a decline with no reason returns 422 and writes nothing.
- ☐ **T1** Open `/frontend4/round`. The queue shows clients for the current round.
- ☐ **T2** The queue is ordered by **urgency**, not alphabetically — overdue first, then due, then later, then finished.
- ☐ **T3** Finished clients are still listed, at the bottom, visibly quieter. They are not hidden.
- ☐ **T4** Allergies appear on the client in the queue **and** at the top of their medicine list.
- ☐ **T5** Search by part of a name filters the queue.
- ☐ **T6** The round buttons across the top switch between Morning / Lunchtime / Evening / Night.
- ☐ **T7** Click a client. Their medicines appear with name strongest, then strength/form, then dose · route · due.
- ☐ **T8** "Take with water. Do not crush." style instructions appear as an instruction — separate from *why* the medicine is prescribed.
- ☐ **T9** Progress reads both doses and people: "N of M doses recorded · X of Y clients complete".
- ✅ **T10** Record a **Declined** with no reason → refused, server's own wording, **nothing written**. Auto-verified: HTTP 422, zero rows.
- ✅ **T11** Record a Declined **with** a reason → saved, attributed to you, timestamped. Auto-verified.
- ✅ **T12** Re-record the same dose as **Given** → a new row is written and the previous one is marked superseded (`is_current` 1 → 0). **The original is never overwritten.** Auto-verified.
- ☐ **T13** A recorded medicine shows the outcome, the time and who recorded it — and no longer offers a Record button.
- ☐ **T14** A **closed** round shows the closed banner and offers no Record buttons at all.
- ☐ **T15** A client with a medicine low or out of stock shows it on the medicine, with the amount left.

### M2 — states
- ☐ **T16** A round with nobody scheduled shows an empty state that says *why* and what to check — not "No results".
- ☐ **T17** Search for a name that does not exist → an empty state offering to clear the search.
- ☐ **T18** Before choosing anyone, the right-hand panel invites you to pick a client.

### M2 — mobile and keyboard
- ☐ **T19** At 390px the two panes stack; the queue is on top, the chosen client's medicines below.
- ☐ **T20** No horizontal scrolling of the page at any width.
- ☐ **T21** Tab through and record a dose using only the keyboard. Focus is always visible.
- ☐ **T22** With a screen reader, the reason error is announced when recording is refused — not just shown in red.

---

# Tests for the issue log

One test per issue. A ✅ proves a fix; a ⚠️ demonstrates a gap that is still open.

## ✅ I1 — one definition of "given" *(fixed)*
- ✅ **T1** All ten sites call `DoseOutcome::isGiven()`. Auto-verified.
- ✅ **T2** Tests identical before and after the change — 14 errors / 3 failures both times. Auto-verified.
- ☐ **T3** Add a code to `DoseOutcome::GIVEN_CODES`, record a dose with it, and confirm stock moves and a controlled drug demands a witness. Then remove it. *(This is the proof the ten sites really are one.)*

## ✅ I16 — the stock ledger reconciles *(fixed)*
- ✅ **T1** Set a medicine's stock to 3, administer a 5ml dose. The ledger shows `administered 5.00: 3.00 → −2.00` then `correction 2.00: −2.00 → 0.00` with a reason naming the shortfall. Every row adds up. Auto-verified.
- ✅ **T2** The dose is still recorded (`given = 1`). Recording is never refused because of a stock shortfall. Auto-verified.
- ☐ **T3** Normal case: administer 5ml against 60ml. One row, `60.00 → 55.00`, and **no** correction entry.
- ☐ **T4** Repeat T1 on a **controlled drug** and confirm the CD register tells the same story.
- ⚠️ **T5** *Owed:* an automated regression test for T1. Verified against live data instead, which is weaker.

## ⚠️ I2 — medication pages in frontends 1–3 are still open
- ✅ **T1** In frontend4, a user without medication access gets 403. Auto-verified.
- ⚠️ **T2** Sign in as **any** user type and open `/medication/medication-round`. **It loads.** That is the gap — the same check has not been applied there.

## ⚠️ I3 — allergies cannot be checked
- ☐ **T1** A client's allergies display on the round. *(Works.)*
- ⚠️ **T2** Give that client a medicine they are allergic to. **Nothing warns you.** Allergies are free text, so nothing can compare them to a medicine.

## ⚠️ I4 — no medicine codes
- ⚠️ **T1** Search the medicines for a client. Two spellings of the same medicine appear as two different medicines, because the name is free text and there is no code behind it.

## ⚠️ I5 — MAR sheet delete
- ⚠️ **T1** Confirm what `/roster/client/mar-sheet-delete` actually does — a soft `is_deleted` flag, or a real delete. Do not carry the capability into frontend4 either way.

## ⚠️ I6 — PRN duplicate guard is a time window
- ⚠️ **T1** Record a PRN dose twice within 90 seconds → treated as one event. *(Intended.)*
- ⚠️ **T2** Record a genuine second PRN dose 80 seconds after the first → **swallowed**. That is the flaw: the event is inferred from the clock rather than identified.

## ⚠️ I7 — no general audit log
- ⚠️ **T1** Change a permission or a setting, then try to find out who changed it and what it was before. There is nowhere that records it.

## ⚠️ I8 — handover items cannot be assigned
- ⚠️ **T1** Open a handover. Try to assign one item to a person and tick it off. You cannot — it is a block of text, not a list of items.

## ⚠️ I9 — no missed-dose follow-up
- ⚠️ **T1** Record a decline, then try to set a re-offer time, record pharmacy advice, or close the case. There is nowhere to do it.

## ⚠️ I10 — no competency gating
- ⚠️ **T1** Have a shift lead witness a controlled drug. **It is allowed by role alone** — nothing checks that they are trained or authorised to witness, or that their competency has not expired.

## ⚠️ I11 — "Staff" is near-admin in the old system
- ⚠️ **T1** Inspect the access level named "Staff" at Station Road (home 1). It carries roughly **330** rights — nearly everything System Admin has. The name does not describe the permissions.

## ⚠️ I12 — the test suite is red
- ⚠️ **T1** Run the four medication suites. **14 errors, 3 failures**, including `MARSheetTest::test_full_prescription_lifecycle` returning 500. Confirmed pre-existing.
- ⚠️ **T2** Consequence: nobody can distinguish a new regression from the existing noise. Every "before and after are identical" check in this log is weaker than it should be because of it.

## ⚠️ I13 — unmapped access levels fall back
- ☐ **T1** Create an access level with a name nobody has mapped — "Support Worker Level 2". The user still gets in, at the role their account type implies.
- ☐ **T2** `RoleResolver::unmappedLevels()` lists it, so it can be mapped properly. *(Not yet surfaced on any screen — that is the open part.)*

## ⚠️ I14 — junk access levels
- ☐ **T1** `azure`, `acc`, `aa`, `AccessTest`, `Jesse Daniels Level` and the rest are mapped to **no access**, so they are safe. They should still be removed rather than left looking like roles.

## ⚠️ I15 — dated duplicate tables
- ☐ **T1** Confirm nothing reads `access_right@nov08old` or `24_oct_access_right` before the schema grows further.

## ⚠️ I17 — nobody is told about a stock shortfall
- ✅ **T1** The shortfall is recorded in the ledger with a reason. Auto-verified.
- ⚠️ **T2** Cause a shortfall, then check the Today page, the shift lead's screen, and any notification. **Nothing tells anyone.** It sits in a ledger until someone opens it.

---

## How to run the automated checks

```
# build (needed after any React change)
npm run build

# the medication suites — current baseline is 14 errors / 3 failures
php vendor/bin/phpunit tests/Feature/MedicationRoundSafetyTest.php \
  tests/Feature/MedicationRoundReactTest.php \
  tests/Feature/ControlledDrugRegisterTest.php \
  tests/Feature/MARSheetTest.php \
  tests/Feature/MedicationStockReactTest.php
```

Any run that differs from **14 errors / 3 failures** means something changed — in either
direction. That baseline is the only regression signal available until [I12](FRONTEND4-ISSUES.md#i12)
is dealt with.
