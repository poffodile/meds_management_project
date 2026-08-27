# Section 1.1 — Support worker Today dashboard

**The agreed numbering, so it does not drift again:**

| | |
|---|---|
| **1.1** | Support Worker Today — *this screen* |
| **1.2** | Manager Today — not built |
| **Section 2** | Medication Round — not built |

The Start/Resume Round button's current limitation belongs to Section 2.

Built 27 August 2026 for **Noah Williams** at **Oakwood House**. Nothing
committed, nothing pushed.

**Preview:** `! C:\OmegaLife\record7-start.ps1` then
http://127.0.0.1:8333/record7/login — organisation `omega care group`,
`noah.williams` / `Record7-Test-MedAdmin-2026!`. He holds one house, so Section 0
takes him straight to Today.

---

## Read this first — what did not exist

Section 0 built **who may do what**. There was nothing at all for **what they
do**: no clients, no medicines, no prescriptions, no doses, no administrations,
no handovers, no allergies, no PRN follow-ups. The Record7 database held sixteen
tables and every one of them was about access.

So Section 1.1 is not a screen built on existing data. Ten new tables carry it:

| Table | What it holds |
|---|---|
| `record7_clients` | the people, and which house they are in |
| `record7_client_allergies` | substance, reaction, severity as a word |
| `record7_medicines` | name, form, strength, controlled-drug flag |
| `record7_prescriptions` | dose, route, frequency, scheduled or PRN, time-critical, change history |
| `record7_scheduled_doses` | the plan — one row per dose per moment, with its own grace period |
| `record7_administrations` | what actually happened; **append-only** |
| `record7_prn_follow_ups` | the ask-back after an as-required medicine |
| `record7_handovers` / `record7_handover_notes` | what the last shift left, with priority |
| `record7_rounds` | one round per house per slot per day |

**The plan and the outcome are separate records.** A dose that was never given
still has to exist, or nothing can tell the difference between a missed dose and
a dose that was never due.

**Administrations cannot be rewritten or deleted.** Two database triggers refuse
it, and the Eloquent model refuses it as well so the mistake is caught where it
is made. A correction is a new row pointing at the original
(`corrects_administration_id`). Three tests cover this, including one that goes
straight through the query builder past every Eloquent guard.

---

## The six bands, and why that order

| | Band | The question it answers |
|---|---|---|
| 1 | Shift handover | what did the last shift leave me |
| 2 | Needs attention | what is wrong right now |
| 3 | Shift overview | where has the day got to, and the round action |
| 4 | People due | who is waiting, and what for |
| 5 | My tasks and PRN follow-ups | what still needs an answer |
| 6 | Recently completed | what is already done, so I do not repeat it |

That is the order the questions actually arrive in when somebody walks onto a
shift. It is the same order at every width — nothing sits in a column beside
anything else, because a two-column layout on a wide screen silently changes
which band is read first. A test reads the component and fails if the order
changes; another fails if `.r7-board` is ever given `grid-template-columns`.

### What each band does that a list of doses would not

**Handover** sorts urgent, then important, then routine, and collapses the
routine ones. A handover with fourteen routine lines at the top of a phone
screen means the person scrolls past all of it, including the two that mattered.

**Needs attention** is one list, not four counters. A support worker does not
want to know there are two late doses and one outstanding follow-up; they want
to know what to do first, and that is a judgement across all of them. Sort order
is: late time-critical, late, unanswered follow-up or out of stock, refusal —
and within each, longest waiting first.

A **refusal that was later given** drops off the list. A refusal that was
re-offered and accepted is closed, and leaving it there trains people to ignore
the list. Tested.

**People due** is **late, or in the round now open** — not the whole day. A
tablet due at half past nine tonight is not something anybody is due at ten in
the morning, and a card for it buries the ones that are. The rest of the day is
counted in one line per round underneath.

Each person shows **full name and date of birth**, because two people in one
house can share a first name. **Severe and life-threatening allergies appear on
the card you read before you knock**, not two taps further in. Severity is a
word, never a colour alone.

**Recently completed** exists because the commonest double-dose near-miss at a
shift change is repeating a round the last person finished but had not mentioned
yet.

---

## The round

"Next round" is the earliest slot today that still has an unrecorded dose —
which is more honest than reading the clock. If the morning was never finished
it is still the morning round, whatever time it is now.

`POST /record7/round/start` creates or **joins** the round. Two people opening
the morning round within a minute of each other is an ordinary event on a busy
shift; a unique key on `(service, date, slot)` means the second joins the first
rather than opening a rival. Starting it is written to the Section 0
append-only audit trail as `round_started` or `round_joined`.

**The round SCREEN is Section 2 and is not built.** The button records that
the round is under way and returns to Today, where the card then reads "Resume
round — 1 of 8 left — started 10:11". That is a real state change, not a
placeholder, but it is the honest limit of 1.1.

---

## Authorisation

Every query in `ShiftBoard` is bound to one service id — the house the session
is in, which the person has already proved they may enter. There is no
cross-house query in the class and no organisation-level aggregate.

- Page requires `view_dashboard`.
- The round action requires `administer_medication`, enforced by route
  middleware **and** re-checked in the controller.
- Somebody with review-only access still sees the whole board — knowing what is
  outstanding is part of reviewing — and is told in words that they cannot
  record, rather than being given a button that silently does nothing.

Tested: a client at Rosewood never appears; `ShiftBoard` returns empty for a
house with no data; Maya (review only) gets 403 from the round action and no
round row is created; and the rendered page contains no manager or
organisation-wide wording.

---

## Assumptions I made, all changeable

These were not specified and I picked something defensible. Each is a one-line
change in `Record7Section1Seeder` or the prescription.

| Assumption | Value | Why |
|---|---|---|
| Round times | 08:00, 12:30, 17:30, 21:30 | ordinary supported-living pattern |
| Grace before "late" | 90 / 60 / 60 / 90 minutes | a morning round has hours; a Parkinson's dose has minutes |
| Time-critical grace | 30 minutes (per prescription) | set on the prescription, not the medicine class |
| PRN follow-up due | 1 hour after giving | long enough for it to have worked |
| "Recently completed" | last 12 hours | covers a shift and the handover before it |
| "Changed recently" | last 7 days | somebody working two on, four off can miss a change |

**Time-critical is a property of the prescription, not the medicine.** The same
drug can be time-critical for one person and not another.

---

## The fictional data

Six clients at Oakwood House, all invented. Real medicines used in ordinary
ways. The situations are the ones that actually fill a shift:

- **Terence Boyle** — Parkinson's, co-careldopa four times a day, time-critical.
  His morning dose is deliberately left unrecorded, so there is always a late
  time-critical dose to see.
- **Joyce Hartley** — lorazepam (controlled) given at 03:10 for agitation, with
  the follow-up still unanswered. Her macrogol is out of stock. Peanut allergy,
  life threatening.
- **Dennis Okafor** — refused his morning metformin, not offered again since.
- **Aisha Rahman** — levetiracetam increased on Monday; the change is surfaced
  whether or not anything is due. Self-administers her inhaler.
- **Margaret Whitfield** — penicillin allergy, severe.
- **Callum Fraser** — in hospital, prescription suspended, **no doses planned
  and absent from the round**. Tested.

**The fixture is anchored to now, not to a date.** Doses are generated around
the current time and everything before the open round is recorded, so the
dashboard is meaningful whenever it is opened rather than showing an empty day.
Re-running the seeder regenerates today.

Guarded exactly like Section 0: never in production, never without
`RECORD7_ALLOW_FIXTURE_SEED=true`, and never against a database whose name looks
like the legacy one.

```
RECORD7_ALLOW_FIXTURE_SEED=true php artisan db:seed --class=Record7Section1Seeder
```

---

## Two bugs found in the shared shell, both fixed

Neither was in the new code; both broke this page.

1. **The page scrolled sideways on a tablet.** Above 600px the top bar becomes a
   row, and neither half was allowed to shrink — so the longest role name in the
   organisation ("Medication Administrator") decided how wide the page was.
2. **At 360px the context labels collided** — "ORGANISATION" overlapped "HOUSE",
   because each cell sized to its own longest word.

Both fixed with `min-width: 0`, equal flex shares and truncation. Checked at
360, 390, 768 and 1024: no horizontal scroll at any of them.

---

## Not built, deliberately

- **Recording an outcome.** That is the Medication Round, **Section 2**.
- **Manager Today, Section 1.2.** Explicitly out of scope.
- **Stock counts, witness countersigning, offline.** Later sections.
- **dm+d codes.** The column exists on `record7_medicines` and is unused, so
  adding a catalogue later is a backfill rather than a migration of live
  prescriptions.

## One thing to know about the fixture

**Noah's role in the supplied Section 0 fixture is "Medication Administrator"
(R7), not "Support Worker".** I did not change the fixture. His permissions are
exactly the support-worker set — `view_dashboard`, `view_people`,
`administer_medication` — so the dashboard is the support worker dashboard; only
the role label differs. Say if you want the label changed.

---

**Record7 198 tests, Frontend 4 64, JS 22 — all passing.** Frontend 4,
Frontend 3 and the legacy pages untouched.
