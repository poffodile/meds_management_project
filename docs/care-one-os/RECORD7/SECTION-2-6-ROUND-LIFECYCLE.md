# Section 2.6 — Round completion, closing and reopening

**Status:** design, awaiting owner approval to build
**Written:** 30 August 2026
**Basis:** the read-only inspection of 30 August 2026, and the owner rulings of
the same date.

Reasoning and sources live here, not in the code. No regulatory citation appears
in any migration or class comment, because guidance is revised and a comment
cannot be revised with it.

---

## 0. The one distinction this section rests on

**Dose accountability is not clinical resolution.**

A refusal is a complete answer to "what happened to this dose". It is *not* an
answer to "is this person all right, and does somebody need to go back". The
first closes a round; the second does not, and closing must never pretend
otherwise.

Every rule below follows from keeping those two ideas apart.

---

## 1. What exists today

`record7_rounds` already carries `completed_at`, `closed_at`,
`closed_by_user_id`, `reopened_at`, `reopened_by_user_id`. A unique key on
`(organisation_id, service_id, round_date, slot)` means there is exactly one
round row per house per date per slot, forever. There are **no triggers** on the
table — it is the only clinical-adjacent table in Record7 with no append-only
protection.

`Round::status()` derives from those timestamps. **Nothing writes
`completed_at`**, so `completed` is unreachable and "round complete" currently
means nothing.

`ManagerActions::closeRound()` works and deliberately checks no clinical
conditions. `test_closing_a_round_does_not_close_its_unexplained_gaps` already
proves the separation holds.

The reopen path sets `closed_at = null`. That erases the closure, and
`reopened_at` holds only the most recent reopen. **The schema as it stands
cannot represent reopen history**, which is the conflict this section resolves.

## 2. Completeness — derived, never asserted

A round is **complete** when every planned staff-recorded scheduled dose
belonging to it has an administration record. Any terminal outcome counts:
`given`, `self_administered`, `refused`, `withheld`, `not_available`, `missed`,
`person_unavailable`.

```
outstanding(round) =
    ScheduledDose
      where service_id = round.service_id
        and date(due_at) = round.round_date
        and slot         = round.slot
        and NOT EXISTS (administration for this dose)
        and NOT prescription.isFullySelfManaged()

complete(round) = outstanding(round) == 0
```

Three things follow, each already true in `RoundQueue` and carried across
unchanged so the round screen and the completeness rule cannot disagree:

- **A fully self-managed medicine is not outstanding work.** There is no staff
  record to make, so counting it would leave every round permanently incomplete
  and teach people that "3 remaining" means nothing.
- **PRN never participates.** It answers a need, not a plan. It is absent from
  `RoundQueue` today and stays absent.
- **Existence, not outcome.** `ScheduledDose::isRecorded()` already asks
  `administration !== null`. That is the accountability primitive and it is
  reused rather than reimplemented.

**`completed_at` is not written.** Deriving it costs one indexed count and
cannot disagree with the records; storing it creates a second copy that can.
The column stays (additive-only migrations never drop) and is documented as
**deprecated and unwritten**, so nobody later mistakes it for truth.

## 3. The append-only lifecycle

```sql
record7_round_lifecycle_events
  id                        BIGINT UNSIGNED PK
  reference                 VARCHAR(64) UNIQUE
  organisation_id           FK record7_organisations
  service_id                FK record7_services
  round_id                  FK record7_rounds
  event                     ENUM('closed','reopened')
  sequence_no               INT UNSIGNED
  occurred_at               TIMESTAMP
  actor_user_id             FK record7_users
  actor_name_at_time        VARCHAR(255)
  actor_role_at_time        VARCHAR(120) NULL
  review_item_id            FK record7_review_items NULL   -- the approval, for a reopen
  reason                    VARCHAR(500) NULL              -- required for a reopen
  planned_doses             SMALLINT UNSIGNED              -- close-time snapshot
  accounted_doses           SMALLINT UNSIGNED
  unrecorded_doses          SMALLINT UNSIGNED
  unresolved_categories     JSON NULL                      -- category names only
  created_at, updated_at

  UNIQUE (round_id, sequence_no)
  UNIQUE (review_item_id)          -- one approval authorises one reopen
  INDEX  (service_id, occurred_at)
```

**`completed` is deliberately not an event.** Completeness is a fact about the
doses, true or false at any moment, and it can go from true back to false if a
dose is added. An event would freeze a claim that the records could later
contradict.

### 3.1 What `unresolved_categories` is, and is not (owner ruling)

**It is a close-time manager safety snapshot of the conditions visible for the
HOUSE at the moment somebody signed.** It answers one question and only one:
*what was this manager looking at when they closed this round?*

It is **not**:

- a claim that every category listed belongs exclusively to that round or slot.
  The list is service-wide, because that is what the manager's screen shows and
  what they were actually looking at. A concern from the lunchtime round appears
  on the morning round's snapshot, and that is correct — they saw it.
- the source of truth for whether any condition exists. The refusal, the welfare
  check, the controlled drug register, the stock event and the review item are
  each authoritative for their own lifecycle, exactly as they were before.
- a lifecycle. Nothing resolves a category by removing it from this list, and
  nothing here is consulted to decide whether a condition is still open. A
  category that has since been dealt with simply stays in the snapshot, because
  the snapshot records the past, not the present.

Category **names** only — never copies of clinical records. Reading this field
tells you what a manager saw; to know what is true now, read the records.

**The snapshot is evidence, not authority.** `planned_doses`,
`accounted_doses`, `unrecorded_doses` and `unresolved_categories` record what
the manager was looking at when they signed. `unresolved_categories` holds
category *names* only — `['refusal', 'welfare_check', 'controlled_drug_discrepancy']`
— never copies of the underlying records. The refusal, the welfare check and
the CD register remain authoritative, and a category listed here that has since
been resolved is simply history: it says what stood at the time, which is the
only question this field exists to answer.

## 4. Current state, and what the head columns now mean

```
lifecycleState(round) =
    latest lifecycle event by sequence_no
      → 'closed'   when that event is 'closed'
      → 'reopened' when that event is 'reopened'
      → 'open'     when there are no events
```

`Round::status()` combines that with completeness:

| Lifecycle | Complete | Status |
|---|---|---|
| open / reopened | no | `in_progress` |
| open / reopened | yes | `complete` |
| closed | either | `closed` |

**A new head column, `last_lifecycle_event_id`**, points at the newest event so
the common query costs one join rather than an aggregate. It is a cache,
rebuildable from the events, and a test asserts it agrees with them.

**The four existing columns are redefined and never nulled again:**

| Column | New meaning |
|---|---|
| `closed_at`, `closed_by_user_id` | the **most recent** closure. Never cleared. |
| `reopened_at`, `reopened_by_user_id` | the **most recent** reopen. Never cleared. |
| `completed_at` | deprecated, unwritten |

They become a convenience for display, not a state machine. **Nothing may infer
"is this round closed" from `closed_at` any more** — that question is answered
by `last_lifecycle_event_id`. Existing callers (`RoundEntry::openRoundFor`,
`RoundAuthority`, `ManagerBoard`) move to the derived state in the same change,
because leaving two sources of the same answer is how they drift apart.

I considered dropping the four columns instead. Additive-only forbids it, and
keeping them as "most recent" is honest as long as nothing decides state from
them.

### 4.1 Projections are projections (owner ruling)

**The immutable event chain is the authoritative source of round lifecycle
state and history. Everything else is a convenience projection.**

That applies to `last_lifecycle_event_id` as much as to the old timestamps: the
head pointer is a cache of "which event is newest", rebuildable by reading the
chain, and it is never the thing that *decides* anything on its own.

Every state-sensitive caller derives from the chain:

| Caller | Before | After |
|---|---|---|
| `Round::status()` | `closed_at` / `completed_at` | latest event + derived completeness |
| `Round::isClosed()` | `closed_at !== null` | latest event is `closed` |
| `RoundAuthority` | `$round->isClosed()` | unchanged call, now derived underneath |
| `RoundEntry::openRoundFor` / `openRoundsInOtherHouses` | `whereNull('closed_at')` | rounds whose latest event is not `closed` |
| `ManagerBoard` round card | `closed_at` | derived state |
| close / reopen | read and write `closed_at` | read the chain, append an event, then project |

**A projection can never create a transition.** Writing `closed_at` by hand does
not close a round; only an event does. This is testable and is tested: three
tests deliberately put the projection and the chain into disagreement —
a stale `closed_at` on a round whose latest event is `reopened`, a cleared
`closed_at` on one whose latest event is `closed`, and a `last_lifecycle_event_id`
pointing at the wrong event — and assert that `status()`, `RoundAuthority` and
round entry all follow the **chain**, not the column.

The projections are then repaired from the chain rather than trusted.

## 5. Closing

Authority: `view_manager_dashboard`, unchanged. Closing is an operational
sign-off and the people who run a shift already hold it.

```
BEGIN
  1. lock the round row              SELECT ... FOR UPDATE
  2. derive lifecycleState           refuse if already 'closed'  (idempotent)
  3. count planned / accounted / unrecorded
  4. collect unresolved category names from IssueRegistry + the 2.5 CD register
  5. INSERT lifecycle event 'closed', sequence_no = last + 1
  6. UPDATE head: last_lifecycle_event_id, closed_at, closed_by_user_id
  7. audit round_closed, carrying the counts
COMMIT
```

**Closing resolves nothing.** It does not touch `record7_issue_states`, the CD
register, welfare checks or review items. Unresolved conditions stay exactly
where they are and stay visible afterwards. A round with unrecorded doses may
still be closed — a manager must be able to sign off a shift, and blocking them
would only produce worse workarounds — but the count is on the event, so what
was signed off is on the record.

**No special rule for controlled drugs.** Section 2.5 already fails closed on
unsafe movements. Closing the operational round neither fixes nor worsens a
ledger discrepancy, so it does not block.

## 6. Reopening

A `record7_review_items` row of kind `round_reopen_request` is the request and
approval workflow. **It is not the round's state.** Approval authorises a
transition; the transition is the lifecycle event.

```
BEGIN
  1. lock the round row              SELECT ... FOR UPDATE
  2. lock the review item            SELECT ... FOR UPDATE
  3. the request must be:
       kind          = 'round_reopen_request'
       status        = 'approved'
       subject_type  = 'round'  and  subject_id = this round
       service_id / organisation_id = this round's
       not already consumed  (no lifecycle event carries this review_item_id)
  4. re-check reopen_medication_round for the actor, NOW
  5. refuse unless lifecycleState = 'closed'
  6. require a reason
  7. INSERT lifecycle event 'reopened', sequence_no = last + 1, review_item_id set
  8. UPDATE head: last_lifecycle_event_id, reopened_at, reopened_by_user_id
     -- closed_at is NOT cleared
  9. audit round_reopened
COMMIT
```

**One approval, one reopen** — the owner's expectation, enforced by
`UNIQUE (review_item_id)` on the lifecycle table. A replayed approval loses on
that index rather than producing a second transition. Reopening again needs a
new request.

**Declining changes nothing.** `carryOut()` is only reached on approval, and
after this change it records a lifecycle event rather than editing the round.

**Reopening resolves nothing either** — same as closing.

## 7. Authority

A new permission, `reopen_medication_round`, granted in the fixtures to the
roles that already carry accountability for a house:

**Three roles, and only three** (owner ruling). The earlier draft said four;
the repository shows no evidence that a fourth needs this authority, so it is
not granted for convenience.

| Role | Gets it | Why |
|---|---|---|
| Service Manager | yes | runs the house and signs off its shifts |
| Medication Lead | yes | owns medication practice; already holds `incident_review` and `reconciliation` |
| Organisation Owner | yes | organisation-wide accountability for the record |
| Organisation Administrator | **no** | administers accounts and structure. Nothing in the repository shows it carrying clinical-record accountability, and `manage_staff` is not a reason to reopen a signed-off period. |
| Medication Administrator | **no** | records doses; does not sign off or reopen |
| Support Worker | **no** | same |
| Quality and Compliance Reviewer | **no** | reviewers observe. Read access is the point of the role. |
| External Healthcare Professional | **no** | outside the organisation's record-keeping line |

In the fixtures that means **daniel.evans** and **testmanager** (Service Manager)
and **sarah.ahmed** (Medication Lead) hold it. Organisation Owner has no fixture
user, which is fine — the grant follows the role, not the person.

It joins `WRITE_PERMISSIONS`, so a read-only access row cannot exercise it.
`decideReview()` stops falling through to `view_manager_dashboard` for this kind
and maps `round_reopen_request → reopen_medication_round`. Authority is checked
when the request is approved **and again** when the transition is performed, so
a permission removed in between takes effect.

## 8. Controlled-drug discrepancy visibility

The Section 2.5 discrepancy is derived from the immutable register: an entry
with `is_discrepancy = true` and no correction naming it. That derivation is
authoritative and does not change.

`IssueRegistry` gains `controlled_drug_register_discrepancy` as a **derived**
condition — `conditionActive()` runs the same query 2.5 uses. It appears on the
manager board beside the existing stock-sourced concern, ranked with it.

**It is not given mutable workflow state.** No `record7_issue_states` row
decides whether it exists; acknowledgement, a review item's `status` and free
text cannot clear it. Only a correction in the register can, exactly as 2.5
established. This is the `conditionActive()` pattern 2.3 introduced for welfare
checks, applied unchanged.

## 9. Idempotency and concurrency

The round row is the lock for every transition, so all of these serialise:

| Race | Behaviour |
|---|---|
| Two managers close at once | second blocks, then sees `closed`, and is refused as already closed. One event. |
| Double-submitted close | same — one event, no silent overwrite |
| Two managers consume one approval | second loses on `UNIQUE (review_item_id)`; one reopen |
| Double-submitted reopen | same |
| Dose recorded while closing | the recorder does not take the round lock, so it either commits before the close (counted) or after (refused by `RoundAuthority`). Never half. |
| Reopen while a recording attempt is in flight | the attempt was already refused on the closed state; it does not become valid retroactively |
| `sequence_no` collision | `UNIQUE (round_id, sequence_no)` refuses; the service retries against the new tail |

A successful reopen makes `RoundAuthority` permit recording again, because that
check moves to the derived state. A closed round keeps refusing.

## 10. Immutability

| Trigger | Rule |
|---|---|
| `record7_round_lifecycle_events_no_rewrite` | BEFORE UPDATE — refuses **every** update, unconditionally |
| `record7_round_lifecycle_events_no_delete` | BEFORE DELETE — refuses every delete |
| `record7_round_lifecycle_events_validate_insert` | client/service/organisation scope; round belongs to this service; service to this organisation; `reopened` requires a `reason` and a `review_item_id`; `closed` requires neither; the named review item is an approved `round_reopen_request` for this round; `accounted + unrecorded = planned` |

Mirrored by a `FROZEN` list and a `deleting()` guard on the model, so an
Eloquent write fails with a readable message before reaching the trigger.

Rounds themselves gain no delete guard in this section — they are an identity
row, not a clinical record, and the history now lives elsewhere.

## 11. Tests

**Accountability.** Every terminal outcome counts a dose as accounted for
(seven cases). A refusal makes its dose accounted for **while the refusal
condition stays active**. Self-managed medicines do not leave false
incompleteness. PRN does not affect completeness — giving one changes no count.
Completeness flips back to false if a dose is added.

**Closing resolves nothing.** Five separate tests: an earlier refusal, a welfare
concern, a 2.5 CD discrepancy, a pending correction request, and a stock
concern each survive a close unchanged and stay visible on the manager board. A
later unrelated `given` does not close an earlier refusal. Acknowledgement and
free-text review status cannot clear any of them.

**Reopening resolves nothing** — the same five, after a reopen.

**History.** close → reopen → close → reopen leaves four events in order, with
`closed_at` never cleared and every actor preserved.

**Projection disagreement.** Three tests force the head and the chain apart and
prove application state follows the chain (§4.1). One further test proves that
writing a projection column by hand creates no transition.

**Adversarial.** Reopen with no approved request · with a declined request ·
with a request for another round · another house · another organisation ·
consuming one approval twice · without a reason · without
`reopen_medication_round` · with the permission removed between approval and
transition · reopening a round that is not closed · raw SQL `UPDATE` and
`DELETE` on a lifecycle event · forging `accounted + unrecorded ≠ planned` ·
recording into a closed round · recording into a reopened round (allowed).

**Concurrency.** Structural assertions that the round row is selected
`FOR UPDATE` before state is read, plus a two-connection probe outside the
suite, as Sections 2.4 and 2.5 used.

**Mutation.** Reopen no longer clearing `closed_at` → history test fails ·
remove the `UNIQUE (review_item_id)` → double-consume test fails · derive
completeness from `completed_at` instead of the doses → accountability tests
fail · let closing write to `issue_states` → the five resolution tests fail ·
drop `reopen_medication_round` back to `view_manager_dashboard` → authority test
fails · count self-managed doses as outstanding → false-incompleteness test
fails.

## 12. Fixtures

`reopen_medication_round` is created and attached to Service Manager, Medication
Lead, Organisation Administrator and Organisation Owner. Daniel Evans (Service
Manager) therefore holds it, so the approval journey is performable from a clean
seed — the lesson from Section 2.5's witness gap.

The existing pre-approved `ROSE-R-004` reopen request stays as history. A second
**open** request is added so the approval journey can actually be exercised, and
Rosewood's closed round gains its lifecycle event so the fixture is consistent
with the new source of truth.

## 13. UI, kept to what function requires

- **Manager board:** the round card shows derived state, the accountability
  count ("11 of 12 doses accounted for") and, on the close control, what will
  remain unresolved. Closing asks for confirmation naming those categories.
- **Round history:** a plain list of lifecycle events on the round — what, who,
  when, why — so a reopen is visible rather than inferred.
- **Review queue:** a `round_reopen_request` shows which round, and approval
  states plainly that it authorises a reopen rather than performing one.
- **CD discrepancy** appears in the existing concerns list. No new surface.

No final UI redesign. No Section 2.7 work.

### 13.1 Deferred UI verification

**Section 2.6 mobile presentation remains to be re-verified during the final
Record7 responsive UI pass.**

Desktop light and desktop dark were verified during Section 2.6. Mobile-width
light and dark were **not** verified, because the available browser tooling
could not provide a genuine mobile viewport — window resizing was refused by the
environment, and forcing a width on the document does not emulate one, so any
result would have measured nothing.

They are recorded as unverified rather than assumed to pass. This does not
change the approved functional or clinical behaviour of Section 2.6.

Two things for whoever picks this up: the manager round table is wide and has no
`overflow-x` container of its own, and there is no design-system test guarding
against body overflow — so the narrow-width behaviour of that table is genuinely
unknown, not merely unphotographed.

## 14. Open questions

None blocking. Two worth recording:

**Backfill — nothing is invented.** The earlier draft proposed manufacturing a
`closed` event for rounds whose closure detail the old `closed_at = null`
behaviour had already destroyed. That would have written a clinical history
Record7 does not know, which is worse than an incomplete record.

Two extra columns carry the honesty:

```
imported     TINYINT(1) NOT NULL DEFAULT 0
import_note  VARCHAR(500) NULL
```

`actor_user_id` and `reason` become nullable **for imported events only** — the
insert trigger requires both on any event that is not imported, so a live
transition can never omit them.

| Legacy shape | What is written |
|---|---|
| `closed_at` set, `reopened_at` null | one imported `closed` event carrying the **known** time and actor, `import_note` = *"Reconstructed during the round lifecycle migration from the closed_at and closed_by_user_id columns."* |
| `closed_at` set **and** `reopened_at` set | imported `closed` then imported `reopened`, both from known values |
| `closed_at` null, `reopened_at` set — the shape the old reopen produced | **only** the imported `reopened` event, from its known time and actor. No closure is fabricated. `import_note` = *"A closure preceded this reopen, but the legacy mutable schema did not retain its time or actor."* |
| no timestamps | nothing; the round was never closed |

The third row is the important one: the round is currently open, which the
single `reopened` event states correctly, and the missing closure is recorded as
missing rather than guessed at.

**`completed_at`.** Left in place, unwritten, documented as deprecated. Removing
it would be a destructive migration; writing it would create a second truth.

## 15. Sources

Consult the current published versions; these were applicable at the time of
writing and guidance is revised.

- CQC — medicines guidance for adult social care, including MAR records
- NICE SC1 — *Managing medicines in care homes*
- NICE NG67 — *Managing medicines for adults receiving social care in the community*

**None are cited in executable code.** Record7's enum values are technical
identifiers, not regulatory wording, and must not be quoted as such.
