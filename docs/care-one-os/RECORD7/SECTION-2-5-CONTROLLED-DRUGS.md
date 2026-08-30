# Section 2.5 — Controlled drugs

**Status:** specification, amended after owner clarification — awaiting approval to build
**Written:** 30 August 2026
**Basis:** the read-only inspection of 30 August 2026, four owner decisions (§1),
and the owner clarification of 30 August 2026 (§2).

Reasoning and sources live here, not in the code. No regulatory citation appears
in any migration or class comment, because guidance is revised and a comment
cannot be revised with it.

---

## 0. What the fixture values are, and are not

**Every controlled-drug value in the Record7 fixtures is fictional test and
design data.** No schedule classification, balance, or witness in this section
is a migrated fact, and none should be read as a clinical or legal instruction
for any actual person.

**Classifying a real medicine's schedule is a separate, explicit exercise** for
somebody qualified to do it. Nothing in Section 2.5 infers a schedule from a
medicine's name, and nothing back-fills one. Where a medicine is flagged
controlled but unclassified, it is treated as controlled and the classification
is shown as missing — not guessed.

---

## 1. Decisions taken by the owner

| # | Decision | Consequence |
|---|---|---|
| 1 | **Add `cd_schedule` alongside `is_controlled`** | `is_controlled` keeps driving the gates. `cd_schedule` is nullable, because a medicine may be flagged controlled before anyone classifies it. |
| 2 | **Witness requirement is fail-safe by setting** | Required unless the service is *positively* identified as supported living or a person's own home. NULL, unrecognised, or missing all require it. |
| 3 | **The register is person-owned** | One running balance per person per *preparation* — see §5, which is stricter than client + medicine. |
| 4 | **2.5 covers receipt, administration, return, waste, correction, discrepancy** | Transfer between services, formal destruction, and reconciliation with general stock are later work. |

## 2. The owner clarification, and what changed

**2.1 Discrepancy is a state, not a universal block.** The previous draft said a
discrepancy "does not block the next administration". That was a clinical rule I
had no business writing. Replaced by §7: Record7 does not decide whether a dose
may proceed. It decides only whether it can *prove* a movement, and **fails
closed** when it cannot.

**2.2 The witness must be authorised at the moment of use**, not merely named.
Expanded in §6.

**2.3 `medicine_id` is not sufficient register identity.** Verified during this
amendment and it is not: see §5. This is the most consequential change.

**2.4 Immutability extends to every newly meaningful field.** Expanded in §11.

**2.5 Boundaries restated verbatim from the owner** in §14.

---

## 3. Decisions taken by me, and why

Each follows an existing Record7 pattern rather than inventing one. **Any can be
overridden before building.**

**3.1 Quantities are `decimal(10,3)`, never `int`.** Legacy stores stock as
`int unsigned` and documented the consequence itself: a fractional dose "leaves
a sub-unit rounding drift each time". A controlled-drug balance that drifts is a
controlled-drug balance that is wrong. Matches `dose_amount` from 2.4.

**3.2 Removed, given, returned and wasted are four separate figures**, not one
signed number. Returned intact stock re-enters the balance; waste leaves it and
is a different act. Collapsing them loses which one happened — the exact
distinction the register exists to make.

**3.3 The balance is computed server-side and never accepted from the client.**
A browser-supplied `balance_after` is ignored if present and rejected by the
insert trigger.

**3.4 A negative balance is recorded, never floored.** Legacy's own comment is
right: flooring to a tidy zero hid the thing the register exists to catch.

**3.5 The witness is a user, never a typed name.** Legacy's `witness_name
varchar(255) NOT NULL` is unverifiable, and its `override_reason` lets the
requirement be waived silently. **`override_reason` is not copied.**

**3.6 A new finding: controlled prescriptions currently carry no unit.** All
three fictional controlled prescriptions have `dose_unit`, `dose_min` and
`dose_max` NULL — 2.4 deliberately left controlled drugs without structured
limits. A register cannot hold a quantity without a unit, so **2.5 must supply
`dose_unit` for controlled prescriptions in the fixture.** It is fictional
design data, per §0.

---

## 4. The setting model

`record7_services.service_type` is free text today. It holds "Supported Living"
and "Residential Care", and is consulted by nothing: read once, in
`HouseController`, to be printed on a screen. It cannot carry a safety rule.

A new `care_setting` column is constrained and actually used:

| `care_setting` | Witness required by setting |
|---|---|
| `care_home` | yes |
| `childrens_home` | yes — position unresolved, see §15 |
| `supported_living` | no; a service may still require it |
| `persons_own_home` | no; a service may still require it |
| `other` | yes |
| `NULL` | **yes** |

**Fail-safe in the direction legacy chose, for the reason legacy recorded:** the
harm of a missing witness where one was needed outweighs the friction of an
unnecessary one. Legacy noted 12 live homes with a NULL setting, and that
defaulting NULL to lenient would drop the requirement across all at once.

`service_type` is left exactly as it is. It is a label; `care_setting` is a rule.

**A service may tighten but never loosen.** `cd_witness_policy` takes
`by_setting` (default) or `always`. There is deliberately **no `never`**: a
provider may add a control above the minimum, never remove one. No configuration
value, anywhere, can weaken a witness that the setting requires.

## 5. Register identity — why `medicine_id` is not enough

**Verified during this amendment:**

- `record7_medicines.form` and `.strength` are plain `varchar`, freely mutable.
- The `Medicine` model has **no** `FROZEN` list and **no** guard.
- There is **no trigger** on `record7_medicines`.
- The only index besides the primary key is `dmd_code`, which is **non-unique**
  and **0 of 18 rows populated**.
- One row per preparation holds today by convention only — nothing enforces it.

So an UPDATE changing "Morphine sulfate MR 10mg" to "20mg" would silently
reinterpret every historical balance ever recorded against it. A register keyed
on `medicine_id` alone would be quietly falsified by an ordinary edit.

**The register therefore snapshots the preparation at entry time** and keys the
balance on the snapshot:

```
medicine_id            FK           NOT NULL  -- identity/reference, for joining
medicine_name_at_time  varchar(255) NOT NULL
form_at_time           varchar(80)  NULL
strength_at_time       varchar(80)  NULL
unit                   varchar(30)  NOT NULL
cd_schedule_at_time    varchar(4)   NULL

preparation_key  CHAR(64) GENERATED ALWAYS AS (
    SHA2(CONCAT_WS('|', medicine_id,
                        IFNULL(form_at_time,''),
                        IFNULL(strength_at_time,''),
                        unit,
                        IFNULL(cd_schedule_at_time,'')), 256)
) STORED
```

**The key is a generated column, not a value the application supplies.** It is
computed by MySQL from the snapshot columns on insert, so it is deterministic,
cannot be browser-controlled, and cannot drift from the snapshot it describes.
The same generated-column pattern already secures `dose_claim`.

`medicine_name_at_time` is deliberately **outside** the key: a corrected spelling
is a display fix and must not split a balance. Form, strength, unit and schedule
**are** in the key, because a change to any of them means a different thing is
being counted.

A balance is `(client_id, preparation_key)`. If a medicine's strength is edited,
subsequent movements land on a **new** key rather than corrupting the old one,
and the divergence is visible instead of silent.

This also means the register does not depend on the dm+d catalogue arriving
later. When `dmd_code` is populated it becomes a better *display* identity; the
balance key does not change.

**Future catalogue correction, without rewriting history.** Historical entries
are never touched. Three cases:

| Change | Effect |
|---|---|
| Name spelling corrected | nothing — name is outside the key; old entries keep the old name as evidence of what was read at the time |
| Form / strength / unit / schedule corrected | later movements land on a **new** `preparation_key`; the old balance closes where it stood and the divergence is visible |
| The old balance needs carrying across | a `correction` entry on the old key taking it to zero, and a `receipt` on the new key — both append-only, both witnessed under the same rules, and the pair is the audit trail |

Record7 never migrates a balance silently. Moving stock between preparation keys
is an explicit, witnessed, two-entry act performed by a person.

## 6. Witness rules

A witness is valid only if **all** hold, evaluated at request time:

1. a real, active Record7 user;
2. holding `witness_medication` in the same organisation and service;
3. **not** the administering user;
4. passing the same `RoundAuthority` scope checks as the administrator.

Failing any of these is a **blocked movement**, audited, with nothing written.

`witnessed_by_user_id` is an FK, and the entry also snapshots
`witness_name_at_time` and `witness_role_at_time` — so the record still reads
correctly if the person later leaves, exactly as `AccessAuditEvent` already does
with `staff_name_at_time`. The snapshot is evidence, never the identity: it can
never be supplied instead of a real user.

**Where a witness is genuinely not required** (`supported_living` or
`persons_own_home`, policy `by_setting`), the entry records
`witness_was_required = false` and a structured `unwitnessed_basis` —
`setting_does_not_require` — never free text, and never a placeholder name.

`witness_was_required` is **stored, not recomputed.** A service's setting can
change; what the rule demanded on the night is a fact about that entry.

## 7. Discrepancy — a fail-safe state

Record7 does not decide whether a dose may proceed. That is a clinical judgement
made by a person under their service's procedure. Record7 decides only whether
it can **prove** a movement, and fails closed when it cannot.

**When expected and verified quantities do not reconcile:**

- the discrepancy is recorded append-only, as its own entry;
- the **verified observed quantity is preserved** and becomes the balance — the
  expected figure is stored alongside it, never substituted for it;
- a negative balance is written as it is, never floored or repaired;
- `is_discrepancy = true`, and a `controlled_drug_discrepancy` issue is raised;
- it stays visible until resolved by **structured evidence**. Acknowledgement or
  free text alone cannot resolve it — `IssueState::SAFETY_CRITICAL` already
  enforces this, and `IssueRegistry::requiresEvidence()` already returns true
  for any controlled medicine.

**Where it surfaces**, so "prominently" is a fact about the code rather than an
intention:

| Surface | Behaviour |
|---|---|
| Manager board | `ManagerBoard` already ranks `controlled_drug_discrepancy` at `rank 1` with `severity: high` — the top of the concerns list |
| The person's medicine view | the affected preparation shows the unreconciled balance, not a tidied one |
| The CD pathway itself | the fail-closed check names the discrepancy as the reason a movement was refused |
| Handover | the open concern is carried, because a discrepancy that ends at shift change is a discrepancy nobody owns |

None of these can be dismissed. Closing runs through the existing
`IssueRegistry` evidence path, and `conditionActive()` keeps the concern live
while the register still shows it — a derived clinical condition that workflow
state cannot override, the pattern 2.3 established for welfare checks.

### 7.4 Negative balance semantics

**An ordinary administration must never intentionally create a negative
balance.** If the verified available quantity is insufficient for the proposed
removal, it **fails closed** (§12.6 step 3). There is no path by which giving a
medicine drives a balance below zero.

A negative or unreconciled balance may arise **only** through an explicit
`stock_check` or `correction` — a pathway where a verified physical observation
proves that the ledger and the actual stock disagree. All three figures are kept:

| Stored | Meaning |
|---|---|
| `expected_quantity` | what the ledger said should be there |
| `counted_quantity` | what was physically verified |
| `balance_after` | the verified count — the truth going forward |

Never floored to zero. Never repaired by rewriting earlier entries. The
discrepancy remains a **derived unresolved condition** — `conditionActive()`
reads the register, so no workflow state can clear it — until structured
reconciliation or correction evidence resolves it.

**Fail-closed rule.** Record7 records a *successful* controlled administration
only when it can prove **both**:

1. a derivable balance for this `(client, preparation)` that is at least the
   quantity to be removed; **and**
2. the witness/policy conditions of §6 are satisfied.

If either is unprovable — no opening balance, an unresolved discrepancy, an
invalid witness — the movement is **refused, audited, and nothing is written**.
The refusal states which condition failed.

This is neither "a discrepancy always blocks" nor "never blocks". Record7 is not
claiming the dose must not be given; it is declining to record a proven movement
it cannot prove. What happens clinically is decided and recorded by people, and
Record7 says plainly that it could not verify the stock.

## 8. Event vocabulary and balance arithmetic

| `action` | Meaning | `balance_after` |
|---|---|---|
| `receipt` | stock arrives | `before + quantity_received` (opening: `before` NULL, treated as 0) |
| `administration` | episode where some was given | `before − (given + wasted)` |
| `non_administration` | removed, none given | `before − wasted` |
| `return_to_storage` | standalone return, no episode | `before + quantity_returned` |
| `waste` | standalone disposal, no episode | `before − quantity_wasted` |
| `stock_check` | verified physical count | `= counted_quantity` (the verified figure) |
| `correction` | corrects an earlier entry | `before + net delta of the correction` |

**`non_administration` is approved (owner ruling, 30 August 2026)** with a
strict invariant:

```
action = 'non_administration'  REQUIRES
    quantity_given    = 0
    quantity_removed  = quantity_returned + quantity_wasted
    quantity_removed  > 0
```

It exists **only** for an episode where controlled stock was actually removed
from storage but no dose was given. It is one action, not a family: refusal,
person-unavailable and not-available are **clinical outcomes on the MAR record**,
never distinct register movements.

**The two records answer different questions and must not be conflated:**

| Record | Question |
|---|---|
| `record7_administrations` | what happened clinically to this person and this dose |
| `record7_cd_register` | what happened physically to this controlled stock |

A refusal where nothing left the cupboard is a complete clinical record with no
physical movement, and creating a zero-quantity register entry for it would
pollute the ledger with non-events.

**Invariant, enforced in the insert trigger for episode actions:**

```
quantity_removed = quantity_given + quantity_returned + quantity_wasted
```

Returned intact stock re-enters the balance. Waste does not. They are never
interchangeable, and an unknown outcome is **not** recorded as a return — the
movement is refused until the quantities are known.

`stock_check` stores `expected_quantity` (derived) **and** `counted_quantity`
(verified). The balance becomes the counted figure; the expected figure is
preserved as evidence of the divergence, never overwritten by it.

## 9. Transaction sequences

**Scheduled controlled administration** — one database transaction:

1. `RoundAuthority::check` — account, organisation, house, access window,
   permission, competency, open round.
2. Medicine is controlled → enter the CD pathway (not the ordinary give path).
3. Resolve `witness_was_required` from `care_setting` + `cd_witness_policy`.
4. Validate the witness against all four rules of §6.
5. Derive `balance_before` from the last entry for `(client, preparation_key)`.
6. **Fail-closed check** (§7). Refuse and audit if unprovable.
7. Insert the `record7_administrations` row (`witnessed_by_user_id` populated).
8. Insert the `record7_cd_register` row linked to it, balance computed in SQL.
9. Audit `controlled_medication_administered`.

Steps 7–8 are one transaction: neither can exist without the other.

**Controlled PRN** — identical, except step 1 is person-scoped (no round), and
**every Section 2.4 guard runs, in 2.4's existing order, before any CD work**:
`kind === 'prn'` → prescription status → support type → competency →
`checkDose()` range → `nextAllowedAt()` minimum interval → `windowUsage()`
against `prn_max_administrations` and `prn_max_total_amount`. Only after all of
them pass does the CD pathway begin. The current `witness_required` refusal at
`PrnAdministration.php:211` is *replaced* by the pathway, not bypassed — and a
test asserts each 2.4 guard still refuses a controlled PRN independently.

**Refusal or unavailable after removal:**

1. Authority, as above.
2. The 2.3 declaration path stays for the case where **nothing was removed**.
3. Where quantity *was* removed, the CD pathway takes over and requires
   `quantity_returned` and `quantity_wasted` such that
   `removed = 0 + returned + wasted`.
4. Insert the non-administration `record7_administrations` row (outcome
   `refused` / `person_unavailable` / `not_available`).
5. Insert the `non_administration` register entry.
6. Waste requires a witness whenever the movement did.

## 10. Corrections

`corrects_register_id`, a self-FK, exactly as `corrects_administration_id` works
today. The original entry is never updated or deleted and remains visible. A
correction states the corrected quantities; the balance moves by the **delta**,
so the running balance stays continuous and the error stays on the record.

A correction may not correct a correction more than one link deep without
pointing at the original — the same shape as the 2.3 re-offer chain.

## 11. Immutability

**Existing gap this section must close first.** `witnessed_by_user_id`,
`dose_amount`, `dose_unit` and `controlled_drug_no_quantity_removed` on
`record7_administrations` are protected by **neither** the model `FROZEN` list
**nor** the `no_rewrite` trigger — both guard the same seven other columns.
Nothing exploits this today because nothing writes a witness. 2.5 writes one.

**Additive migration** adds to both guards, on `record7_administrations`:
`witnessed_by_user_id`, `dose_amount`, `dose_unit`,
`controlled_drug_no_quantity_removed`, and the new register linkage.

**No applied migration is edited.** Triggers are dropped and recreated in a new
migration, the pattern already used by `2026_08_31_000001_record7_reoffer_chain`.

**Verified: freezing these columns cannot break an existing write path.**
Nothing in Record7 updates an administration after insert — the one `->save()`
in `PrnAdministration` updates a `PrnFollowUp`. The one test that writes
`dose_unit` after the fact (`Record7PrnTest:186`) edits the **prescription**, to
prove the **administration's** snapshot survives; freezing the administration
column strengthens that assertion rather than threatening it.
`corrects_administration_id` and `reoffer_of_administration_id` are already
frozen, so correction and re-offer behaviour is untouched.

**Where the quantities live, and one point for your ruling.** Your brief lists
quantity removed / administered / returned / wasted among the *administration*
fields to freeze. In this design they live on the **register**, which is
append-only at both levels from birth — so they are immutable, and the
requirement is met, but by a different route than the brief implies.

The same applies to the linkage: the FK is `record7_cd_register.administration_id`
(UNIQUE), i.e. stored on the append-only side, where it cannot be rewritten at
all — rather than a frozen column on the mutable side.

I recommend against duplicating the quantities onto `record7_administrations`:
two copies of a controlled-drug quantity can disagree, and a register whose
figures are contradicted by the record it links to is worse than either alone.
**If you would rather have them on both, say so and I will mirror them and
freeze both copies.**

The register itself is append-only at both levels from birth: a `FROZEN` list in
the model plus `BEFORE UPDATE` and `BEFORE DELETE` triggers, matching
`record7_welfare_checks`.

## 12. Database constraints and triggers

Every rule below is enforced **in the database**, not only in PHP. A service can
be bypassed by a forged request, a console command, or a future controller that
forgets. A trigger cannot.

### 12.1 Atomicity — and a reversal of my earlier recommendation

I previously recommended the linkage FK live on the register
(`administration_id`). **That was wrong once atomicity is a requirement**, and
the reason is circularity: if the register points at the administration, the
register row must be written first with a NULL link and updated afterwards — and
updating an append-only ledger is precisely what must never happen.

So the FK goes the other way:

```
record7_administrations.cd_register_id  BIGINT UNSIGNED NULL,
    FOREIGN KEY -> record7_cd_register(id),
    UNIQUE KEY  -- one administration per movement, and one movement per administration
```

Write order becomes: register entry first, then the administration referencing
it. Nothing is ever updated, and the link is frozen on insert.

**An administration of a controlled drug cannot exist without its movement**,
enforced by trigger rather than convention:

**The link is required when, and only when, the episode moved controlled
stock** — not merely because the medicine is controlled (owner ruling).

| Episode | MAR row | CD movement | `cd_register_id` |
|---|---|---|---|
| Successful controlled administration | yes | `administration` | **required** |
| Refused **before** removal | yes | none | **NULL is correct** |
| Refused **after** removal, all returned intact | yes | `non_administration` | **required** |
| Refused after removal, some wasted | yes | `non_administration` | **required** |

Whether stock was removed is a fact only the worker knows, so it is asked
explicitly. The existing 2.3 field `controlled_drug_no_quantity_removed` already
carries exactly this declaration, and 2.5 reuses it rather than inventing a
second flag:

```
record7_administrations_validate_insert  (extended, BEFORE INSERT)
  -- existing correction/re-offer rules unchanged, then:
  IF the prescription's medicine is_controlled
     AND NEW.corrects_administration_id IS NULL
  THEN
     IF NEW.outcome IN ('given','self_administered') THEN
        -- a dose that went in always moved stock
        IF NEW.cd_register_id IS NULL THEN
           SIGNAL 45000 'a controlled administration must carry its register movement'
        END IF
     ELSE
        -- a non-administration: the declaration decides
        IF NEW.controlled_drug_no_quantity_removed IS NOT TRUE
           AND NEW.cd_register_id IS NULL THEN
           SIGNAL 45000 'controlled stock was removed; account for it in the register'
        END IF
        IF NEW.controlled_drug_no_quantity_removed IS TRUE
           AND NEW.cd_register_id IS NOT NULL THEN
           SIGNAL 45000 'nothing was removed, so there is no movement to record'
        END IF
     END IF
  END IF
```

The lookup is a `SELECT` inside the trigger, the same shape the 2.3 re-offer
trigger already uses to check organisation and house. The rule is symmetric: a
movement without a removal is refused just as a removal without a movement is.

### 12.2 Append-only history

| Trigger | Rule |
|---|---|
| `record7_cd_register_no_rewrite` (BEFORE UPDATE) | refuses **every** update, unconditionally — no column list, because no field of a ledger entry may ever change |
| `record7_cd_register_no_delete` (BEFORE DELETE) | refuses every delete |

Stricter than `record7_administrations`, deliberately: an administration has
non-clinical fields that may legitimately settle, a register entry has none.

Mirrored at model level by a `FROZEN` list plus `deleting()` guard, so an
Eloquent write fails with a readable message before it ever reaches the trigger.

### 12.3 Witness and quantity fields, after recording

The additive migration adds to **both** the `Administration::FROZEN` list and
the `record7_administrations_no_rewrite` trigger:
`witnessed_by_user_id`, `dose_amount`, `dose_unit`,
`controlled_drug_no_quantity_removed`, `cd_register_id`.

The quantities themselves live on the register, which refuses all updates — so
they are immutable by construction rather than by enumeration.

### 12.4 Administrator ≠ witness

Three independent layers, because this is the rule most worth forging:

```
CHECK (witnessed_by_user_id IS NULL
       OR witnessed_by_user_id <> recorded_by_user_id)
```

plus the same test in `record7_cd_register_validate_insert`, plus the service
check that also verifies authority. The CHECK alone would be enough to stop the
write; the trigger message explains it, and the service refuses it politely.

### 12.5 Tenant, service and person scope

In `record7_cd_register_validate_insert`, following the 2.3 pattern that already
validates a re-offer across organisation and house:

- the client belongs to `NEW.service_id`;
- the service belongs to `NEW.organisation_id`;
- the recorder and, when present, the witness belong to the same organisation
  and hold access to that service;
- `NEW.prescription_id`, when present, belongs to `NEW.client_id`;
- `NEW.corrects_register_id`, when present, points at an **earlier** entry for
  the **same** `(client_id, preparation_key)`.

A cross-house or cross-organisation identifier fails at the database even if
every layer above it has been bypassed.

### 12.6 Concurrency — pessimistic locking on a balance head row

**Verified engine facts:** MySQL 8.4.3, InnoDB throughout, isolation
`REPEATABLE-READ`, `innodb_deadlock_detect=ON`, `innodb_lock_wait_timeout=50`.

A unique key on `(client_id, preparation_key, sequence_no)` is necessary but
**not sufficient**, and the owner is right to press on it. It makes a duplicate
*write* impossible, but it is optimistic: two workers can still both read a
balance of one tablet, both conclude there is enough, and only discover the
conflict at insert. That is a race decided after the clinical decision was made.

So sufficiency is evaluated **under a lock**, not before one.

**A balance head row per person per preparation:**

```
record7_cd_balances
  id
  organisation_id, service_id, client_id
  preparation_key    CHAR(64)
  current_balance    DECIMAL(10,3) NOT NULL
  last_sequence_no   INT UNSIGNED  NOT NULL
  last_register_id   BIGINT UNSIGNED NULL
  UNIQUE KEY (client_id, preparation_key)
```

This row is a **derived head pointer, not history**. The register remains the
sole record of what happened; the head is rebuildable from it at any time, and a
test asserts `current_balance` equals the ledger's own arithmetic. It exists to
be lockable, because you cannot take a row lock on a row that does not exist.

**The algorithm, in one transaction:**

| Step | Action |
|---|---|
| 1 | `INSERT IGNORE INTO record7_cd_balances (…, current_balance=0, last_sequence_no=0)` — creates the head if this is the first ever movement |
| 2 | `SELECT … FROM record7_cd_balances WHERE client_id=? AND preparation_key=? FOR UPDATE` — **the lock is taken here and held to COMMIT** |
| 3 | **Sufficiency is evaluated now**, against `current_balance` read under that lock |
| 4 | `sequence_no := last_sequence_no + 1`, allocated server-side |
| 5 | `INSERT` the register row; the insert trigger re-derives `balance_after` and refuses any submitted figure that disagrees |
| 6 | `UPDATE` the head: new balance, new sequence, `last_register_id` |
| 7 | `INSERT` the administration carrying `cd_register_id` |
| 8 | `COMMIT` |

**Step 1 before step 2, deliberately.** Taking `FOR UPDATE` on a row that does
not exist would set a gap lock under REPEATABLE READ, and two workers opening
the same person's first-ever movement would deadlock on the gap. `INSERT IGNORE`
resolves the race on the unique key instead: one insert wins, the other is a
no-op, and both then lock the same real row.

**The opening receipt** is not a special case. It runs the same algorithm with
`current_balance = 0`; only a `receipt` may increase a balance, so a first
movement of any other action fails sufficiency at step 3 and is refused.

**What the second worker sees.** It blocks at step 2 until the first commits,
then reads the *committed* new balance — not the stale one. `FOR UPDATE` is a
locking read, so it sees the latest committed row rather than the transaction's
REPEATABLE-READ snapshot; this is exactly why sufficiency must be evaluated at
step 3 and not from an earlier plain `SELECT`. With one tablet in stock, the
second worker now reads zero and **fails closed**. It never reaches step 5.

**Deadlock and lock-wait.** Errors 1213 and 1205 both roll the transaction back
entirely, so nothing partial can survive. The service retries the whole
transaction up to three times with short backoff, re-reading the head each time.

**Why a retry cannot duplicate the clinical administration.** Because the
administration and the movement are one transaction, a rolled-back attempt wrote
nothing, so re-running it is a first attempt rather than a second. And a retry
that races again cannot both succeed: `(client_id, preparation_key,
sequence_no)` is unique, `record7_administrations.cd_register_id` is unique, and
for a scheduled dose `dose_claim` is unique as well.

**Retries are bounded to deadlock and lock-wait only.** These are the two errors
that guarantee a rollback. A connection loss after `COMMIT` is *not* retried,
because the outcome is genuinely unknown and re-running could double-record a
controlled drug; it surfaces for a person to check the register.

### 12.7 Quantity sanity

```
CHECK (quantity_removed   IS NULL OR quantity_removed   >= 0)
CHECK (quantity_given     IS NULL OR quantity_given     >= 0)
CHECK (quantity_returned  IS NULL OR quantity_returned  >= 0)
CHECK (quantity_wasted    IS NULL OR quantity_wasted    >= 0)
CHECK (witness_was_required = 0 OR witnessed_by_user_id IS NOT NULL)
CHECK (witness_was_required = 1 OR unwitnessed_basis    IS NOT NULL)
CHECK (action <> 'stock_check' OR counted_quantity      IS NOT NULL)
CHECK (action <> 'correction'  OR corrects_register_id  IS NOT NULL)
```

and, in the trigger for episode actions,
`quantity_removed = quantity_given + quantity_returned + quantity_wasted`.

**Negative balances are constrained by pathway, not by CHECK** — see §7.4. An
ordinary administration can never create one, because sufficiency is checked
under lock at step 3. Only `stock_check` and `correction` may write one, and the
insert trigger enforces exactly that:

```
IF NEW.balance_after < 0
   AND NEW.action NOT IN ('stock_check','correction') THEN
   SIGNAL 45000 'an ordinary movement cannot drive the balance negative'
END IF
```

## 13. Test plan

### 13.1 Positive

Witnessed scheduled administration end to end; witnessed controlled PRN;
unwitnessed movement in supported living recording its structured basis; receipt
establishing an opening balance; balance arithmetic for each of the seven
actions; a refusal after removal split across return and waste; a combination of
return **and** waste in one episode; correction preserving the original and
moving the balance by the delta; discrepancy raised, surfaced, and closed only
with structured evidence.

### 13.2 Adversarial — every one a forged server-side request, not a UI click

| Attack | Must |
|---|---|
| Forged witness ID (nonexistent user) | refuse, audit, write nothing |
| Witness without `witness_medication` | refuse |
| Witness from another organisation | refuse |
| Witness from another house in the same organisation | refuse |
| **Administrator submits their own ID as witness** | refuse at service, trigger **and** CHECK |
| Cross-house `client_id` | refuse |
| Cross-organisation `client_id` | refuse |
| `prescription_id` belonging to a different person | refuse |
| Browser-supplied `balance_after` that flatters the balance | refuse — recomputed |
| Browser-supplied `balance_before` not matching the tail | refuse |
| Negative quantity | refuse |
| Quantity exceeding the available balance | refuse, fail-closed |
| Administration with **no** opening balance | refuse, fail-closed |
| Administration while a discrepancy is unresolved | refuse, fail-closed, reason named |
| Arithmetic that does not balance (`removed ≠ given + returned + wasted`) | refuse |
| Unknown outcome submitted as a return | refuse |
| Duplicate register movement (replayed request) | one entry only |
| Two concurrent movements on one preparation | one wins, one retries; no lost update |
| Controlled PRN bypassing **each** 2.4 guard in turn | refuse — one test per guard |
| Controlled drug down the ordinary 2.2 give path | refuse |
| Controlled drug down the 2.3 simplified path after removal | refuse |
| Administration inserted with `cd_register_id` NULL | refuse at trigger |
| Two administrations claiming one register movement | refuse at unique key |
| **Direct SQL `UPDATE`** on a register row | refuse at trigger |
| **Direct SQL `DELETE`** on a register row | refuse at trigger |
| Direct `UPDATE` of `witnessed_by_user_id` on an administration | refuse at trigger |
| Direct `UPDATE` of `dose_amount` on an administration | refuse at trigger |
| Eloquent `->save()` on a register entry | refuse at model |
| Closing a discrepancy with acknowledgement only | refuse |
| Closing a discrepancy with free text only | refuse |

The `UPDATE`/`DELETE` cases run raw SQL on the `record7` connection, deliberately
bypassing Eloquent, because the point is that the **database** refuses.

### 13.3 Mutation — proving each guard bites

Each mutation is applied, the suite run, and the specific test confirmed red;
then reverted. A mutation that changes nothing means the guard is decorative and
the test is passing for the wrong reason — the method that found two false
passes in 2.2.

1. Remove the administrator-≠-witness service check → the forged-self-witness
   test must fail.
2. Remove the witness authority check → the unauthorised-witness test must fail.
3. Remove the fail-closed balance check → the insufficient-quantity test must
   fail.
4. Remove the arithmetic constraint → the unbalanced-episode test must fail.
5. Default an unrecognised `care_setting` to lenient → the NULL-setting witness
   test must fail.
6. Remove the `cd_register_id` requirement → the atomicity test must fail.
7. Remove any single 2.4 PRN guard → that guard's controlled-PRN test must fail.
8. Accept the submitted `balance_after` → the forged-balance test must fail.

## 13A. The seven existing controlled-drug tests

**Updated, never deleted.** Each asserted a Section 2.5 stop condition that 2.5
now removes; each becomes the positive case plus an adversarial one, so the
safety boundary it guarded stays covered.

| Test | Proved (old) | Replaced by (2.5) | New assertion keeping the boundary |
|---|---|---|---|
| `test_a_controlled_drug_cannot_bypass_its_witness_requirement` | scheduled CD returns `witness_required` | scheduled CD proceeds **with** a valid witness | same request **without** a witness is still refused; and with an invalid one |
| `test_a_controlled_prn_cannot_be_given_here_even_by_a_forged_request` | controlled PRN refused at screen and server | controlled PRN proceeds with witness + movement | forged request lacking a witness still refused server-side |
| `test_a_controlled_prn_is_still_visible_to_staff` | CD visible with its reason, not hidden | unchanged — still visible | wording changes from "cannot" to the witness requirement; visibility assertion kept as-is |
| `test_a_controlled_drug_needs_the_storage_declaration` | declaration required before a non-administration records | unchanged, and now **load-bearing**: it decides whether a movement is required | declaration false ⇒ `cd_register_id` required; true ⇒ must be NULL |
| `test_the_ordinary_given_path_is_still_closed_to_controlled_drugs` | ordinary 2.2 give path writes nothing | unchanged — the ordinary path stays shut | kept verbatim; the CD pathway is the only route |
| `test_a_re_offer_cannot_bypass_the_controlled_drug_gate` | re-offer cannot reach the ordinary give path | unchanged | kept verbatim, plus a re-offer through the CD pathway still needing its witness |
| `test_closing_a_controlled_drug_discrepancy_needs_evidence` | evidence-free close refused | unchanged and extended | discrepancy from a real `stock_check` also refuses an evidence-free close |

Four are kept essentially as they are. Only the three asserting
`witness_required` change meaning, and each keeps its boundary by asserting the
refusal still happens when the *witness* is missing rather than when the
*feature* is.

## 14. Section boundary

**IN:** classification needed for this workflow; constrained setting/witness
rule; witnessed scheduled administration; witnessed controlled PRN retaining
every 2.4 guard; person-owned append-only register; opening/receipt movement;
administration movement; return-to-storage; waste arising from the
administration/non-administration workflow; balance calculation; discrepancy
detection and escalation needed by these movements; append-only correction;
audit of successful **and blocked** sensitive actions; non-administration after
removal.

**OUT unless strictly required by the above:** general stock implementation and
reconciliation (2.7); inter-service transfers; general destruction management
unrelated to an administration episode; incident/safeguarding module; catalogue
or BNF integration; final UI redesign.

## 15. Known boundary: children's homes

The regulatory position is **unresolved**. Record7 fails safe — witness required
— flagged for the owner and a qualified regulatory reviewer. Not a compliance
determination, and recorded here so it is not silently reversed later.

## 16. Sources

Consult the current published versions; these were applicable at the time of
writing and guidance is revised.

- Misuse of Drugs Act 1971; Misuse of Drugs Regulations 2001
- CQC — medicines guidance for adult social care, including controlled drugs
- NICE SC1 — *Managing medicines in care homes*
- NICE NG67 — *Managing medicines for adults receiving social care in the community*
- Royal Pharmaceutical Society — *The safe and secure handling of medicines*

**None are cited in executable code.** Record7's enum values are technical
identifiers, not regulatory wording, and must not be quoted as such.
