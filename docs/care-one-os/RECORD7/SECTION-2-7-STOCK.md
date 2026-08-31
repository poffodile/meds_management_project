# Section 2.7 — Stock effects, reconciliation, audit and corrections

**Status: DESIGN ONLY. Nothing here has been implemented.** No migration, no
application code, no test. Every schema object, trigger, arithmetic rule and
file below is a proposal awaiting the owner's build authorisation.

Baseline: Section 2.6, commit `512722808d004a166d9c9f095e524cc39d979c78`.

This is the single authoritative specification for Section 2.7. All owner
rulings are incorporated; superseded proposals have been removed rather than
annotated.

---

## 1. Scope and principles

Record7 has had a stock *display* since Section 1.2 and no stock *ledger*.
`record7_stock_levels` and `record7_stock_events` exist, and nothing in the
application has ever written to them — every row was placed by
`Record7Section12Seeder`. Sections 2.2 to 2.6 recorded what happened clinically
and left the cupboard alone, with tests asserting that they did.

Section 2.7 gives ordinary medicines an append-only ledger with a derived,
lockable balance head. Four principles govern everything below.

**Stock movement follows the physical medicine, not the MAR outcome.** A
clinical record never implies a debit unless medicine physically left
accountable stock.

**The clinical record is never suppressed to protect a balance.** Where the two
disagree, both are recorded and the disagreement becomes visible and
unresolved.

**A count observes; only an approved correction moves the ledger.** A physical
count never silently overwrites what the system expected.

**Every entry that independently proves inconsistency keeps its own evidence.**
Aggregation happens in the presentation layer and nowhere else.

### 1.1 Evidence this rests on

Three findings from inspection are load-bearing and are cited where used:

- **The mutable-resolution defect.** `IssueRegistry::conditionActive('stock_event:…')`
  resolves to `whereNull('resolved_at')` and `ManagerActions::close()` sets that
  column. `record7_stock_events` id 90 is a Senna discrepancy — expected 30,
  counted 28 — closed with *"Found recorded on the wrong chart. Balance
  corrected at the next count."* No balance was corrected, no corrective record
  exists, and `stock_levels` holds no Senna row at all. Two tablets are
  unaccounted for and the system reports nothing.

- **Ownership is person-level in every real record.** `stock_levels` 90 holds
  one Macrogol figure for a house where **two** people are prescribed it
  (Margaret 294 and Joyce 298), so it cannot say whose ran out. Legacy Care One
  OS agrees: `medication_stock_transactions` and `medication_stock_batches` are
  keyed on `mar_sheet_id` **and** `client_id`, with `home_id` as tenant scope
  only. No record anywhere in the repository represents service or provider
  stock; a search for `homely`, `home_remedy` and equivalents returns nothing.

- **Free text must never be parsed.** `record7_prescriptions.dose` is
  `VARCHAR(120)`. Legacy audit finding CR-02, recorded in
  `BuildsMedicationRound::deductionQuantity()`: regex-stripping `'10 ml (500mg)'`
  produced 10500 and floored a real 56-unit balance to zero on one tap.

---

## 2. Final schema

All additive. No previously applied migration is edited.

### 2.1 `record7_stock_movements` — the ledger

```
id                            bigint unsigned PK
reference                     varchar(64) UNIQUE                 'R7SM-…'

organisation_id               FK record7_organisations
service_id                    FK record7_services

owner_type                    enum('client','service') NOT NULL
client_id                     FK record7_clients NULL
owner_ref                     bigint unsigned
                                GENERATED ALWAYS AS (IFNULL(client_id,0)) STORED

medicine_id                   FK record7_medicines
prescription_id               FK record7_prescriptions NULL

medicine_name_at_time         varchar(255) NOT NULL
form_at_time                  varchar(80)  NULL
strength_at_time              varchar(80)  NULL
unit                          varchar(30)  NOT NULL

preparation_key               char(64) GENERATED ALWAYS AS (
                                SHA2(CONCAT_WS('|', medicine_id,
                                                    IFNULL(form_at_time,''),
                                                    IFNULL(strength_at_time,''),
                                                    unit), 256)) STORED

action                        enum('opening_balance','receipt','administration',
                                   'non_administration','return_to_stock',
                                   'waste','stock_check','correction')

quantity_received             decimal(10,3) NULL
quantity_removed              decimal(10,3) NULL
quantity_given                decimal(10,3) NULL
quantity_returned             decimal(10,3) NULL
quantity_wasted               decimal(10,3) NULL
quantity_delta                decimal(10,3) NULL     -- signed; correction only

expected_quantity             decimal(10,3) NULL     -- stock_check only
counted_quantity              decimal(10,3) NULL     -- stock_check only

balance_before                decimal(10,3) NULL     -- NULL only on opening_balance
balance_after                 decimal(10,3) NOT NULL
is_discrepancy                boolean DEFAULT false

-- Physical verification, where the ledger said there was not enough and the
-- medicine was physically present anyway. §7.
shortfall_verified_by_user_id FK record7_users NULL
shortfall_verified_at         timestamp NULL
shortfall_basis               enum('physically_counted_sufficient',
                                   'unrecorded_stock_present','other') NULL
shortfall_statement           varchar(190) NULL
shortfall_observed_quantity   decimal(10,3) NULL     -- informal, NOT a count

recorded_by_user_id           FK record7_users
witnessed_by_user_id          FK record7_users NULL
witness_name_at_time          varchar(255) NULL
witness_role_at_time          varchar(120) NULL

occurred_at                   timestamp
corrects_movement_id          FK record7_stock_movements UNIQUE NULL
review_item_id                FK record7_review_items    UNIQUE NULL
notes                         varchar(500) NULL
sequence_no                   int unsigned

imported                      boolean DEFAULT false
import_note                   varchar(500) NULL

timestamps

UNIQUE (service_id, owner_ref, preparation_key, sequence_no)
UNIQUE (corrects_movement_id)
UNIQUE (review_item_id)
INDEX  (service_id, occurred_at)
INDEX  (client_id, occurred_at)
INDEX  (is_discrepancy, service_id)
```

**Ownership key.** `owner_type` is explicit, never inferred from a null
`client_id`. `owner_ref` exists because MySQL treats NULLs as distinct in a
unique index: `UNIQUE (service_id, client_id, preparation_key, sequence_no)`
would permit two service-owned movements at the same sequence number. The
generated `IFNULL(client_id,0)` closes that without a hash. Section 2.7 writes
only `owner_type = 'client'` (§14.1).

**Preparation key.** `record7_medicines.form` and `.strength` are ordinary
mutable columns, so a ledger keyed on `medicine_id` alone would be quietly
falsified the first time somebody corrected a strength. The snapshot is what
was counted; the key hashes the snapshot; correcting a strength lands later
movements on a **new** key where the divergence is visible rather than
corrupting the old balance. `medicine_name_at_time` is deliberately outside the
hash — a corrected spelling is a display fix and must not split a balance.

The key omits `cd_schedule_at_time`, which Section 2.5's includes. Controlled
medicines are excluded from this ledger entirely, so the column would always be
null, and a deliberately different formula means the two keys can never be
accidentally compared or joined.

**`imported` / `import_note`.** Section 2.7 writes no imported rows. They exist
for the production migration contract (§14.4), which is the only writer that may
set them. A test asserts the fixture contains none.

### 2.2 `record7_stock_balances` — the lockable head

Everything here is **rebuildable from the ledger**. Configuration lives
elsewhere (§3).

```
id                    bigint unsigned PK
organisation_id       FK record7_organisations
service_id            FK record7_services
owner_type            enum('client','service') NOT NULL
client_id             FK record7_clients NULL
owner_ref             bigint unsigned GENERATED ALWAYS AS (IFNULL(client_id,0)) STORED
medicine_id           FK record7_medicines
preparation_key       char(64)
unit                  varchar(30)

current_balance       decimal(10,3) DEFAULT 0
last_sequence_no      int unsigned DEFAULT 0
last_movement_id      FK record7_stock_movements NULL
last_counted_at       timestamp NULL      -- MAX(occurred_at) of stock_check

timestamps

UNIQUE (service_id, owner_ref, preparation_key)
INDEX  (service_id, current_balance)
```

It exists for one reason, the same reason `record7_cd_balances` exists: you
cannot take a row lock on a row that does not exist, and "is there enough" must
be decided while holding a lock or it is worthless. A test rebuilds every head
from its ledger and asserts equality, including `last_counted_at`.

### 2.3 Additions to `record7_administrations`

```
stock_movement_id            FK record7_stock_movements UNIQUE NULL
stock_no_quantity_removed    boolean NULL
```

The FK points administration → movement, not the reverse, for the reason
Section 2.5 recorded: the other direction would require writing the ledger row
first with a null link and **updating** it, and updating an append-only ledger
is what must never happen.

Both are added to `record7_administrations_no_rewrite` and to
`Administration::FROZEN`.

### 2.4 Additions to `record7_review_items`

`subject_type` is already a short string (`'administration'`), so the
discriminator needs no class names.

```
correction_shape          enum('administration_outcome','stock_delta') NULL
requested_quantity_delta  decimal(10,3) NULL   -- stock_delta only
requested_dose_amount     decimal(10,3) NULL   -- administration_outcome only
requested_dose_unit       varchar(30)   NULL   -- administration_outcome only
```

`requested_outcome` (from `2026_08_28_000003`) continues to serve
administration corrections. `requested_dose_amount`/`unit` carry the **actual
historical amount** for the two correction directions that need one (§9). No
new review kind, no new table, no new approval permission.

### 2.5 `record7_stock_attempts` — replay protection

Modelled directly on `record7_prn_attempts`: server-allocated token, one-way
status, identity fields frozen on insert, no DELETE, insert-validated scope.
Covers `receipt`, `stock_check`, `waste` and `return_to_stock` (§12.3).

### 2.6 CHECK constraints on `record7_stock_movements`

```
stock_movements_ownership_shape
  (owner_type='client'   AND client_id IS NOT NULL)
  OR (owner_type='service' AND client_id IS NULL)

stock_movements_quantities_sane
  each of quantity_received, quantity_removed, quantity_given,
  quantity_returned, quantity_wasted, counted_quantity,
  expected_quantity, shortfall_observed_quantity
  IS NULL OR >= 0

stock_movements_action_shape
      (action <> 'opening_balance' OR quantity_received IS NOT NULL)
  AND (action <> 'receipt'         OR quantity_received IS NOT NULL)
  AND (action <> 'stock_check'     OR (counted_quantity  IS NOT NULL
                                       AND expected_quantity IS NOT NULL))
  AND (action =  'stock_check'     OR (counted_quantity  IS NULL
                                       AND expected_quantity IS NULL))
  AND (action <> 'correction'      OR (corrects_movement_id IS NOT NULL
                                       AND quantity_delta    IS NOT NULL
                                       AND review_item_id    IS NOT NULL))
  AND (action =  'correction'      OR quantity_delta IS NULL)
  -- An approved correction may also ESTABLISH a debit that never existed
  -- (§9.3, historical amount known). That movement is an `administration`
  -- and carries the approval it consumed, so review_item_id is permitted on
  -- exactly two verbs and nowhere else.
  AND (review_item_id IS NULL OR action IN ('correction','administration'))

-- Any negative balance is a disagreement, whatever produced it. This holds
-- for a point-of-care shortfall and for a retrospective correction alike.
stock_movements_negative_is_discrepancy
  balance_after >= 0 OR is_discrepancy = 1

-- Point-of-care evidence is required only where there WAS a point of care:
-- a contemporaneous administration recorded by the person giving the dose.
-- A retrospective movement written under an approved correction has no
-- cupboard to stand at; it raises a discrepancy instead (§9.6).
stock_movements_shortfall_evidence
  action <> 'administration'
  OR review_item_id IS NOT NULL
  OR balance_after >= 0
  OR (shortfall_verified_by_user_id IS NOT NULL
      AND shortfall_verified_at     IS NOT NULL
      AND shortfall_basis           IS NOT NULL
      AND shortfall_statement       IS NOT NULL)

stock_movements_shortfall_scope
  shortfall_basis IS NULL
  OR (action = 'administration' AND review_item_id IS NULL)

stock_movements_witness_is_another_person
  witnessed_by_user_id IS NULL OR witnessed_by_user_id <> recorded_by_user_id

stock_movements_import_note
  imported = 0 OR import_note IS NOT NULL
```

### 2.7 CHECK constraints on `record7_review_items`

```
review_items_correction_shape
      (correction_shape IS NULL
        AND requested_quantity_delta IS NULL
        AND requested_dose_amount    IS NULL
        AND requested_dose_unit      IS NULL)
  OR  (correction_shape = 'administration_outcome'
        AND requested_quantity_delta IS NULL
        AND subject_type = 'administration')
  OR  (correction_shape = 'stock_delta'
        AND requested_quantity_delta IS NOT NULL
        AND requested_outcome        IS NULL
        AND requested_dose_amount    IS NULL
        AND requested_dose_unit      IS NULL
        AND subject_type = 'stock_movement')

review_items_dose_pair
  (requested_dose_amount IS NULL AND requested_dose_unit IS NULL)
  OR (requested_dose_amount IS NOT NULL AND requested_dose_unit IS NOT NULL)
```

The two shapes are unambiguous at the database level, and `correction_shape`
being NULL preserves every existing row exactly.

---

## 3. Threshold configuration

A balance quantity is **derived from the ledger**. A reorder level is
**configuration**. Mixing them would mean a rebuild-from-ledger operation had to
carefully preserve three unrelated columns, which is how configuration gets lost
in a repair.

They are therefore separate tables.

```
record7_stock_thresholds
  id                    bigint unsigned PK
  stock_balance_id      FK record7_stock_balances UNIQUE
  low_threshold         decimal(10,3) NOT NULL
  set_by_user_id        FK record7_users
  set_at                timestamp
  note                  varchar(190) NULL
  timestamps
```

- Set or changed only by a person holding `stock_management`, through an
  explicit act (§17), audited as `stock_threshold_set`.
- Cleared by deleting the row, audited as `stock_threshold_cleared`. The
  **history lives in the audit trail**, which is append-only and cannot be
  rewritten; this table holds only the current rule.
- **No row means no threshold is recorded.** `stock_low` is then *unavailable*,
  not false. The stock screen shows *"No reorder level recorded"* — a blank
  must never render as healthy.
- Nothing is ever inferred from dispensing frequency, schedule or pack size.
  The `low_threshold` values in `record7_stock_levels` (8, 5, 6) were written by
  a seeder, have no provenance, and are not carried over.
- `stock_out` is unaffected: it derives from `current_balance <= 0` and needs no
  configuration.

Two alternatives were considered and are not built: days-of-supply derived from
the schedule (requires structured quantities on every prescription, which do not
exist), and an organisation-level default (an invented number wearing a policy
label). Either can be added later without a data migration.

---

## 4. The eight movement types and their arithmetic

`balance_before` is the locked head's `current_balance` at the moment of
writing, NULL only on `opening_balance`. The trigger recomputes `balance_after`
and refuses any figure that disagrees, so a browser-supplied balance is not
ignored — it is rejected.

| Action | `balance_after` | Required | Authorised by |
|---|---|---|---|
| `opening_balance` | `quantity_received` | `quantity_received >= 0`; `balance_before` NULL; only where `last_sequence_no = 0` | `stock_management` |
| `receipt` | `balance_before + quantity_received` | `quantity_received > 0` | `stock_management` |
| `administration` | `balance_before − (quantity_given + quantity_wasted)` | `quantity_removed = quantity_given + quantity_returned + quantity_wasted`; `quantity_removed > 0` | `administer_medication` |
| `non_administration` | `balance_before − quantity_wasted` | `quantity_given = 0`; `quantity_removed > 0`; `quantity_removed = quantity_returned + quantity_wasted` | `administer_medication` |
| `return_to_stock` | `balance_before + quantity_returned` | `quantity_returned > 0` | `stock_management` |
| `waste` | `balance_before − quantity_wasted` | `quantity_wasted > 0` | `stock_management` |
| **`stock_check`** | **`balance_before` — unchanged** | `counted_quantity` present; `expected_quantity` set to `balance_before` by the server | `stock_management` |
| `correction` | `balance_before + quantity_delta` | `corrects_movement_id`, `quantity_delta`, `review_item_id` all present | `reconciliation` |

### 4.1 `stock_check` is an observation, not a correction

This is the ruling that gives the correction workflow its meaning, and it is a
**deliberate divergence from Section 2.5**, where a controlled `stock_check`
sets the balance to the counted figure. Section 2.5 is unchanged; the ordinary
ledger behaves differently and the two are never compared.

```
expected_quantity := balance_before        (server-read, under lock)
counted_quantity  := what the person counted
balance_after     := balance_before        (ALWAYS, match or not)
is_discrepancy    := ABS(counted − expected) > 0.0005
```

When the count matches, the check is recorded, no discrepancy opens, and the
balance is unchanged. When it differs, both figures are preserved side by side
for ever, a discrepancy opens, and **the balance still does not move**. Only the
later approved `correction` applies the reconciled delta.

The consequence is the point: a count can never be used to make an
inconvenient number go away. Somebody has to state what they believe the true
position is, a manager has to approve that number, and the movement that
applies it is itself part of the permanent record.

The head's `last_counted_at` is updated to `occurred_at` — still ledger-derived,
being `MAX(occurred_at)` over `stock_check` movements.

### 4.2 Negative balances

```
balance_after < 0  REFUSED for   opening_balance, receipt, return_to_stock,
                                 waste, non_administration
balance_after < 0  PERMITTED for administration  (a verified shortfall, §7,
                                                  or an approved §9.3 debit)
                                 correction      (reconciling to the truth,
                                                  or an approved §9.2 increase)
                                 stock_check     (inherits balance_before; a
                                                  check cannot itself move it)

ANY balance_after < 0  =>  is_discrepancy = 1, whatever the verb.
```

Every negative balance is a disagreement and gets its own entry in §5.1. The
point-of-care verification of §7 applies only where there was a point of care;
a retrospective correction that drives the balance negative raises the
discrepancy without it (§9.6).

Never floored. The legacy defect this avoids is in
`MedicationStockTransaction::apply()`, where a balance of 3 minus a dose of 5
was written as `balance_after 0.00` — a ledger whose own three numbers
contradict each other and which can therefore never be investigated.

### 4.3 No `adjustment`

Every real cause has a home: a miscount is a `stock_check`, a wrong entry is a
`correction`, a disposal is `waste`, a medicine that came back is
`return_to_stock`. An `adjustment` with no antecedent is the escape hatch that
destroys accountability. If a future event cannot fit one of the eight
truthfully, the design stops rather than inventing a ninth.

`non_administration` is in the list because removing a dose and returning it
intact is one physical episode; splitting it into `waste` plus `return_to_stock`
would let a crash between them leave a removal with nothing accounting for it.
`return_to_stock` survives alongside it for the standalone case — a dose
prepared for somebody who then went out, put back an hour later — which has no
other home.

---

## 5. Discrepancy identity and resolution

### 5.1 Every entry keeps its own evidence

Each movement that independently proves inconsistency carries its own
`is_discrepancy = 1` and requires its own structured correction. There is no
suppression of later entries because an earlier one is open.

```
unresolvedDiscrepancies(balance) =
    SELECT m FROM record7_stock_movements m
     WHERE m.service_id      = :service
       AND m.owner_ref       = :ownerRef
       AND m.preparation_key = :preparationKey
       AND m.is_discrepancy  = 1
       AND NOT EXISTS (SELECT 1 FROM record7_stock_movements fix
                        WHERE fix.corrects_movement_id = m.id)
     ORDER BY m.sequence_no
```

Three things create a discrepancy entry, and only three:

1. a `stock_check` whose `counted_quantity` differs from `expected_quantity`;
2. an `administration` whose `balance_after` falls below zero — either a
   point-of-care shortfall (§7) or an approved §9.3 debit;
3. a `correction` whose `balance_after` falls below zero (§9.2, an approved
   increase larger than the balance can carry).

One thing ends **one** entry: an approved `correction` naming it.
`UNIQUE (corrects_movement_id)` makes one correction per entry a database fact.
Correcting the earliest can never hide a later unresolved shortfall, because
the later entry is a separate row with a separate requirement.

### 5.2 The derived condition

Issue keys stay at **movement level**: `stock_discrepancy:<movement_id>`.

```
conditionActive('stock_discrepancy:<id>') =
    that movement exists in this service, is_discrepancy = 1,
    and no movement names it in corrects_movement_id
```

`resolved_at`, an `IssueState` closure, ownership, escalation, acknowledgement
and a resolution note change nothing. This replaces the mutable
`whereNull('resolved_at')` path described in §1.1.

Movement-level keys are deliberate. A balance-level key would let a *new*
discrepancy inherit the `IssueState` of an old, closed one — appearing on a
manager's board already acknowledged and already closed. Movement ids are never
reused, so that cannot happen.

---

## 6. Manager aggregation

Aggregation is presentation only. The ledger and the issue keys keep full
resolution identity; `ManagerBoard::stockConcerns()` renders **one card per
balance**.

```
card = {
  kind:            'stock_discrepancy',
  balanceId:       <id>,
  person:          <client display name>,
  medicine:        <name, form, strength>,
  key:             <issue key of the OLDEST unresolved entry>,
  unresolvedCount: <n>,
  severity:        from the oldest unresolved entry,
  at:              occurred_at of the oldest unresolved entry,
  entries: [
    { key, movementId, cause: 'count'|'shortfall',
      expected, counted, difference, balanceAfter, at, recordedBy,
      conditionActive: true }
    … one per unresolved entry, oldest first …
  ]
}
```

Rules:

- The card exists while **any** entry for that balance is unresolved.
- `key` is the oldest unresolved entry's key, so the existing own / escalate /
  acknowledge / close machinery keeps working unchanged and the single-entry
  case — the overwhelmingly common one — behaves exactly as it does today.
- Every entry carries its **own** key and is individually actionable and
  individually correctable. Nothing is collapsed away.
- Correcting one entry removes it from `entries` and, if it was the oldest,
  promotes the next. The card and its count persist while others remain.
- `unresolvedCount > 1` is stated in words on the card — *"3 unresolved
  discrepancies on this balance"* — so aggregation is visible rather than
  disguised as a single problem.
- **Acknowledging, owning, escalating or closing one `IssueState` never makes
  the card disappear** while any underlying entry is unresolved. Those acts
  attach to one entry's key; the card's existence is derived from the ledger.
- **No persistent balance-level issue key is introduced.** A key that outlived
  the entries beneath it would let a new discrepancy inherit the
  acknowledgement and closure of an old one and arrive on the board looking
  already handled. Movement ids are never reused, so entry-level keys cannot
  do that.
- `RoundLifecycle::unresolvedCategories()` reads `ManagerBoard::attention()`
  and plucks `kind`, so it picks up `stock_discrepancy` once per balance with
  **no change to that method**.

### 6.1 Ordinary and controlled cards must differ without colour

A client can hold an ordinary discrepancy that does not block administration
and a controlled-drug discrepancy that does. The two consequences are opposite,
so the distinction must survive a greyscale screen, a colour-blind reader and a
phone in daylight. **Colour may reinforce it and must never carry it alone.**

Four independent signals, each sufficient on its own:

| | Ordinary stock | Controlled drug |
|---|---|---|
| **Wording** | "Stock discrepancy" | "Controlled-drug discrepancy" |
| **Status / icon** | attention marker | blocked marker — visually distinct shape, not a recoloured twin |
| **Consequence text** | *"Medicine may still be administered if physical availability is verified. Reconciliation required."* | *"Further CD movement is blocked until reconciled."* |
| **Available action** | "Record a count" / "Request a correction" — the give path stays open | no give path is offered; only "Recount with a witness" |

The consequence sentences are fixed strings, tested verbatim, so a future
redesign cannot quietly soften either. Section 2.5 behaviour is unchanged;
this is presentation only.

---

## 7. Shortfall physical verification

Two different moments, two different answers.

### 7.1 Before the medicine is given

Where the locked balance cannot cover the dose, the confirm screen states the
shortage — the expected figure, the dose, and the shortfall — and the **"Given"
control is unavailable** until structured verification is recorded. This is a
gate, not a banner, and not a bare checkbox.

Captured, at minimum:

| Evidence | Source |
|---|---|
| Authenticated actor | `shortfall_verified_by_user_id`, from the session. Never an id from the request. |
| Server timestamp | `shortfall_verified_at`, from the server clock. A browser can say anything. |
| Structured basis | `shortfall_basis` — `physically_counted_sufficient`, `unrecorded_stock_present`, or `other` |
| Statement | `shortfall_statement` (≤190 chars), always required; the only permitted content for `other` is a description of what was verified |
| Dose quantity and unit | already on the movement as `quantity_given` and `unit` |

Enforced by `stock_movements_shortfall_evidence` (§2.6), so a contemporaneous
administration cannot drive the balance negative without all four, even from
raw SQL.

**Scope.** This gate exists because somebody is standing at the cupboard and
can look. It therefore applies only to a contemporaneous `administration`
movement — one with `review_item_id IS NULL`, written by the clinical recording
path. A retrospective movement established by an approved correction (§9.3) has
no point of care and no cupboard to check; it raises a discrepancy instead, and
a `stock_check` is what establishes the real position afterwards.

### 7.2 The optional observed quantity

`shortfall_observed_quantity` lets the worker record the full physical figure
they saw, which is often the most useful thing a reconciler will have.

It is **not a count**, and the distinction is enforced:

- it does not set `counted_quantity` and does not create a `stock_check`;
- it does not change `balance_after` or `last_counted_at`;
- it does not open its own discrepancy — the administration movement already
  carries one;
- the screen labels it *"what you saw, for the person reconciling this — not a
  formal stock count"*.

A formal `stock_check` by somebody holding `stock_management` remains the only
verified count and the only thing that can populate `counted_quantity`.

### 7.3 After the medicine has been given

Record7 must not refuse to record a clinical event that happened. Where the
worker verified and administered:

- the administration is recorded;
- the `administration` movement is recorded, append-only;
- `balance_after` goes negative and **stays** negative — never floored, never
  silently repaired;
- the movement is automatically marked `is_discrepancy = 1` and becomes its own
  entry in §5.1.

Deliberately looser than Section 2.5, where the stricter controlled-drug
process remains authoritative and an unprovable movement is refused.

### 7.4 An open discrepancy does not block administration

High-visibility, requiring reconciliation, but it does not prevent a person
receiving an otherwise available prescribed medicine. Where staff can
physically verify the medicine exists, Record7 permits the truthful
administration and carries the discrepancy. The governance and evidential
requirements for controlled drugs are different, and Section 2.5's blocking
rule stands there unchanged.

---

## 8. Administration + stock atomic transaction sequence

```
BEGIN

 1. INSERT IGNORE INTO record7_stock_balances (...)  -- head must exist
 2. SELECT ... FROM record7_stock_balances
     WHERE service_id=? AND owner_ref=? AND preparation_key=?
     FOR UPDATE                                      -- lock held to commit

 3. Decide from the locked figure and nothing else:
      balance_before := current_balance
      sequence_no    := last_sequence_no + 1
      shortfall?     := (given + wasted) > balance_before
      -> if shortfall and no verification evidence supplied: ABORT

 4. INSERT INTO record7_stock_movements (...)        -- ledger first
      trigger recomputes balance_after and refuses disagreement

 5. INSERT INTO record7_administrations
      (..., stock_movement_id = <new movement id>)   -- clinical record second

 6. UPDATE record7_stock_balances
      SET current_balance=?, last_sequence_no=?, last_movement_id=?,
          last_counted_at=?                          -- head last

COMMIT
```

Order matters in both directions. The movement is written **before** the
administration because the FK points administration → movement; the reverse
would require writing the ledger row with a null link and updating it. The head
moves **last**, inside the same lock, so it can never describe a movement that
was not written.

If step 5 fails, step 4 rolls back with it. There is no state in which a debit
exists for a dose that was not recorded, or a dose is recorded claiming a
movement that does not exist.

`AdministrationRecorder::recordGiven()` and `recordNonAdministration()`
currently have **no transaction at all** — they rely on the `dose_claim` unique
index for single-write safety. Both need wrapping.
`PrnAdministration::record()` already runs inside a transaction holding a lock;
the movement goes inside it. `ManagerActions::correct()` already runs inside the
`decideReview()` transaction; the compensating movement goes inside that.

### 8.1 What each situation does

| Situation | Movement | Arithmetic |
|---|---|---|
| Staff-administered dose, tracked and quantified | `administration` | `removed = given`; balance − given |
| `self_administered` + `check_and_record`, where the medicine came from Record7-accounted stock **and** the record confirms stock was used | `administration` | balance − given |
| `self_administered` + `check_and_record`, where either is not established | **none** | staff watched; Record7 does not claim to know what left a supply it does not hold |
| Self-managed (`self_administration_monitoring = 'none'`) | **none, ever** | the person holds their own supply; no consumption is invented from absent staff records |
| PRN | `administration`, quantity from `administrations.dose_amount` | balance − dose_amount |
| Refusal before removal | none | nothing left the cupboard |
| Refusal after removal, returned intact | `non_administration` | `removed = returned`; balance unchanged |
| Refusal after removal, cannot be returned | `non_administration` | `removed = wasted`; balance − wasted |
| `person_unavailable` | none | |
| `not_available` | none | there was nothing to remove |
| `missed` | none | |
| `withheld` | **none — deferred entirely, §8.3** | |
| Re-offer | a movement only on the attempt where medicine was actually taken | the earlier attempt's record is untouched |
| Standalone waste / return | `waste` / `return_to_stock` | balance ∓ quantity |

### 8.2 Tracked, untracked, unquantified

A preparation is **tracked** for a person when a `record7_stock_balances` row
exists. Three states, all named on screen so silence never looks like success:

| State | Stock effect |
|---|---|
| **Untracked** — no balance row | none. The dose records exactly as today. Without this, switching 2.7 on would require every medicine in every house to be counted before any dose could be given. |
| **Tracked, unquantified** — balance exists, no structured quantity available | none. Screen: *"counted, but no dose quantity is recorded, so doses do not move the balance."* |
| **Tracked and quantified** | §8.1 applies |

Quantity comes from, in order: `record7_administrations.dose_amount` (what was
actually given), then `record7_prescriptions.dose_min` **only where
`dose_min = dose_max`**. From nowhere else. `record7_prescriptions.dose` is
display text — never parsed, never regex-matched. A test asserts no stock-aware
service references `->dose` on a prescription.

For every non-administration outcome on a tracked, quantified preparation the
recorder declares whether stock physically left storage — the ordinary
equivalent of `controlled_drug_no_quantity_removed`:

```
stock_no_quantity_removed = 1  ->  no movement may exist
stock_no_quantity_removed = 0  ->  a non_administration movement must exist
NULL where tracked and quantified  ->  refused
NULL otherwise  ->  permitted; there is nothing to declare
```

Enforced in `record7_administrations_validate_insert` alongside the existing
Section 2.5 clauses, not instead of them.

### 8.3 `withheld` is deferred

Section 2.7 adds **no** ordinary-stock removal or waste pathway for `withheld`.

`AdministrationRecorder::recordNonAdministration()` line 403 already refuses it
outright — *"Withheld recording is not part of Section 2.3"* — because no
verified clinical-authority model exists. Its stock effect is deferred with that
future workflow. When one arrives, the eight-verb vocabulary above can already
record a removal and a waste; nothing further is needed here.

One nuance worth stating, because it is easy to miss:
`ManagerActions::correct()` line 376 **does** accept `withheld` as a *correction
target*. A `given` administration can therefore be corrected to `withheld`
today. That path is a correction, not a withheld recording, and §9.1 covers it:
`withheld` is a non-consuming outcome, so the attributable quantity is returned
per §9.0 — `quantity_given` only, with any waste on the original episode
preserved. No new pathway is created.

### 8.4 Structured scheduled-dose fixture data

No schema change is required — `dose_min`, `dose_max`, `dose_unit` already exist
on prescriptions, and `dose_amount`/`dose_unit` on administrations, all
nullable decimals with pair CHECKs.

What is required is data. Today **19 of 20 ordinary scheduled prescriptions
have no structured quantity**; the one that does is the controlled Morphine
populated by Section 2.5. `Record7Section12Seeder` will populate the scheduled
prescriptions that Section 2.7 exercises.

These are **fictional design values chosen to make the section testable**. They
are not prescribing inference, are not derived from the `dose` string, and are
marked as fixture design data in a comment beside every value, in the terms
Section 2.5 used for its own fixtures. **No NOT NULL constraint is added** —
that would force fiction onto every row where a prescription genuinely carries
no structured quantity, and the unquantified path in §8.2 exists precisely for
those.

---

## 9. Corrections — every direction change

Section 2.7 adds no approval workflow. Both paths raise a `correction_request`
review item decided by `ManagerActions::decideReview()` under the existing
`correction_approval` permission.

A correction **never** rewrites an administration or a movement. It appends
linked compensating records, atomically.

### 9.0 The attributable-quantity rule

A clinical administration correction reverses **only the physical stock
consequence attributable to the clinical fact being corrected**, while
preserving independently recorded return and waste dispositions.

It is therefore wrong to say "reverse the original debit". The original debit
on an `administration` movement is `quantity_given + quantity_wasted`, and the
wasted portion was destroyed as a separate physical act that no clinical
correction has touched.

```
attributable(original administration movement) := quantity_given
```

Worked, on `removed 2 / given 1 / returned 1` (balance −1):

- correcting `given` → `refused` compensates **+1**;
- the returned unit never left the balance, so nothing is done for it.

Worked, on `removed 2 / given 1 / wasted 1` (balance −2):

- correcting `given` → `refused` compensates **+1, not +2**;
- the wasted unit stays wasted. Its physical disposition has not been
  corrected, and restoring it because an outcome changed would put a destroyed
  tablet back in a cupboard.

Worked, on `removed 3 / given 1 / returned 1 / wasted 1` (balance −2):

- correcting `given` → `refused` compensates **+1**.

If the waste record itself was wrong, that is a separate correction with its
own approved evidence, naming that movement. Nothing about a MAR outcome can
un-waste a medicine.

### 9.1 Original `given` → a non-consuming outcome

Non-consuming outcomes: `refused`, `missed`, `not_available`,
`person_unavailable`, `withheld`, `self_administered` where no accounted stock
was used.

```
append   corrective administration (corrects_administration_id = original)
append   correction movement
           corrects_movement_id = the original administration's movement
           quantity_delta       = + attributable(original)   -- §9.0
           review_item_id       = the approved request
```

The figure comes from the original movement's own `quantity_given` — not the
prescription's current dose, not a recomputation from the corrected outcome.
Both the original administration and the original movement stand untouched, in
order, for ever.

### 9.2 Original `given` → `given` with a different actual amount

The request must carry the corrected **actual** dose:
`requested_dose_amount` and `requested_dose_unit`, both required, and the unit
must equal the original movement's `unit`.

```
q  = attributable(original movement) = original.quantity_given
q′ = the approved corrected actual historical amount
append   corrective administration with dose_amount = q′, dose_unit = unit
append   correction movement, quantity_delta = (q − q′)
```

Only the difference moves, and only the *given* portion participates: a
correction from 2 tablets to 1 returns 1; from 1 to 2 debits 1 further. Any
returned or wasted quantity on the original episode is untouched, per §9.0.

**Unit.** `requested_dose_unit` must equal the original movement's `unit`
exactly. No conversion is performed — a mismatch fails closed and the request
must be re-raised with the right unit.

If the further debit takes the balance below zero the correction still
proceeds: `balance_after` goes negative, `is_discrepancy = 1` is set, and no
point-of-care verification is demanded because there is no point of care
(§7.1 scope). A `stock_check` is what establishes the real position.

### 9.3 Original non-consuming outcome → `given`

**The dose is never inferred from the current prescription.** Historical MAR
data must not acquire newer prescribing details retrospectively; a prescription
can change between the event and the correction, and reading today's figure into
last month's dose would silently rewrite history.

```
if the request carries requested_dose_amount AND requested_dose_unit
   (the actual historical amount, stated by the requester and approved):
      append  corrective administration with that amount and unit
      append  correction movement, quantity_delta = −<that amount>
              corrects_movement_id = … see below

else:
      append  corrective administration (clinical correction stands)
      append  NO stock movement
      raise   a stock reconciliation requirement
```

**No zero movement is fabricated.** Where the original outcome moved nothing
there is no movement to correct, `corrects_movement_id` cannot be satisfied,
and the debit therefore cannot use the `correction` verb. Neither is the
correction constraint weakened, nor a ninth verb added.

The newly established debit is an **`administration` movement**, linked through
the corrective administration's `stock_movement_id`, carrying `review_item_id`
= the approved request. `corrects_administration_id` on the corrective
administration is what explains why that debit exists; the `correction` verb
stays reserved for movements that correct an existing movement.

**Unit.** With no original movement to compare against, compatibility is
checked against the **authoritative balance / preparation unit** — the `unit`
on the balance head, which the preparation key already binds. Exact match is
required. No automatic conversion. A mismatch fails closed.

**The reconciliation requirement** is a new derived condition,
`stock_verification_due:<corrective_administration_id>`, active until a
**qualifying stock check** exists. A qualifying stock check is one that:

- occurred **after** that corrective administration;
- belongs to the **same organisation**;
- belongs to the **same service**;
- belongs to the **same client**;
- belongs to the **same preparation / balance** (`preparation_key` and
  `owner_ref` both matching);
- records a genuine physical count through the Section 2.7 `stock_check`
  pathway.

An older count, another person's count, another preparation's count, free text,
a review status and an `IssueState` closure each resolve nothing. It clears on
a **fact**, following the welfare-check design. It is added to
`IssueState::SAFETY_CRITICAL`, so closing the board item requires evidence and
closing it does not clear the condition.

A qualifying count resolves the requirement because the current physical
position is then known. If that count disagrees with the ledger, the mismatch
becomes its own ordinary-stock discrepancy (§5.1) and follows the normal
correction lifecycle — one requirement is answered and, where warranted, a new
and separate one opens.

### 9.4 Path B — reconciling a stock discrepancy

Three acts, three people, by design:

1. **Raise** — `stock_management` or `reconciliation` creates the request from
   the stock screen, naming the exact discrepancy movement and the signed delta
   they believe is correct.
2. **Approve** — `correction_approval`, via `decideReview()`. Approval only.
3. **Carry out** — `reconciliation` executes it from the stock screen, under
   the balance lock, writing the `correction` movement.

### 9.5 The correction matrix

`orig` = the original administration's movement. `attributable(orig)` =
`orig.quantity_given` (§9.0). Every row writes a corrective administration
carrying `corrects_administration_id`, and every row leaves the original
administration and the original movement immutable.

| # | Direction | Original movement | New stock movement | Quantity arithmetic | Unit source | Discrepancy / verification raised | Approval and evidence |
|---|---|---|---|---|---|---|---|
| 1 | `given` → `refused` | `administration`, untouched | `correction`, `corrects_movement_id = orig` | `quantity_delta = + attributable(orig)` | `orig.unit`, exact match | no | `correction_request`, shape `administration_outcome`, `requested_outcome='refused'`; `correction_approval` |
| 2 | `given` → `missed` | untouched | `correction` → `orig` | `+ attributable(orig)` | `orig.unit`, exact | no | as row 1, `requested_outcome='missed'` |
| 3 | `given` → `person_unavailable` | untouched | `correction` → `orig` | `+ attributable(orig)` | `orig.unit`, exact | no | as row 1 |
| 4 | `given` → `not_available` | untouched | `correction` → `orig` | `+ attributable(orig)` | `orig.unit`, exact | no | as row 1 |
| 5 | `given` → `given`, **smaller** amount `q′` | untouched | `correction` → `orig` | `quantity_delta = + (attributable(orig) − q′)` | `orig.unit`; `requested_dose_unit` must equal it exactly, no conversion | no | as row 1 plus `requested_dose_amount`/`requested_dose_unit`, both required |
| 6 | `given` → `given`, **larger** amount `q′` | untouched | `correction` → `orig` | `quantity_delta = − (q′ − attributable(orig))` | `orig.unit`, exact, no conversion | **yes if `balance_after < 0`** → `is_discrepancy = 1`. No point-of-care evidence demanded (§7.1 scope) | as row 5 |
| 7 | non-consuming → `given`, **historical amount known** | none exists | **`administration`** (not `correction`), linked via corrective administration's `stock_movement_id`, carrying `review_item_id` | `balance − q′` where `q′` is the approved historical amount | **balance-head `unit`**, exact match, no conversion, mismatch fails closed | **yes if `balance_after < 0`** → `is_discrepancy = 1`, no point-of-care evidence | as row 5. The current prescription is never read. |
| 8 | non-consuming → `given`, **historical amount unknown** | none exists | **none — nothing is fabricated** | none | n/a | **`stock_verification_due:<corrective administration id>`**, cleared only by a qualifying stock check (§9.3) | `correction_request` with `requested_outcome='given'` and no dose amount; `correction_approval` |
| 9 | original episode contained a **returned** quantity | untouched | as rows 1–6 for the direction taken | the returned quantity **never left the balance**, so it contributes nothing; only `quantity_given` is attributable | as the direction taken | as the direction taken | as the direction taken |
| 10 | original episode contained a **wasted** quantity | untouched | as rows 1–6 for the direction taken | the wasted quantity is **not restored**; only `quantity_given` is attributable | as the direction taken | as the direction taken | correcting the waste itself requires its own separate approved correction naming that movement |

Rows 9 and 10 are not separate directions — they are the modifier that applies
to every row above, and they exist in this table because "reverse the original
debit" is the wrong instruction and would silently restore destroyed medicine.

### 9.6 Who authorises a compensating movement

`reconciliation` governs **Path B only** — a person investigating a
disagreement and declaring what the true balance is. That is a judgement.

Rows 1–7 above exercise no judgement about a balance: the figure is derived
arithmetically from an immutable prior movement, or from an amount a manager
has already approved. Those movements are authorised **by the approved
correction request itself**, written inside the `decideReview()` transaction,
and consume it through `review_item_id`.

This is the principle already settled for the clinical path — a carer needs no
`stock_management` to move stock by giving a tablet, because the movement is a
consequence of the clinical record rather than a separate stock act. The same
holds here. Requiring `reconciliation` as well would mean the approving manager
(Daniel, `correction_approval`) could not carry out the correction they had
just approved, and collapsing the two permissions onto one person would undo
the separation of duties the fixture exists to demonstrate.

`UNIQUE (review_item_id)` still means one approval buys exactly one movement,
whichever path wrote it.

---

## 10. Stock-check → correction sequence

The full lifecycle of one discrepancy, end to end.

```
t0  balance head: current_balance = 30, last_sequence_no = 7

t1  Sarah (stock_management) counts and finds 28.
    -> movement seq 8: action=stock_check
                       expected_quantity = 30
                       counted_quantity  = 28
                       balance_before    = 30
                       balance_after     = 30      <-- UNCHANGED
                       is_discrepancy    = 1
    -> head: current_balance = 30, last_counted_at = t1
    -> board: card for this balance, unresolvedCount = 1,
              key = stock_discrepancy:<movement 8>
    -> audit: stock_counted, stock_discrepancy_found

t2  Sarah raises a correction request.
    -> review_item: kind=correction_request
                    subject_type='stock_movement', subject_id=<movement 8>
                    correction_shape='stock_delta'
                    requested_quantity_delta = -2
    -> audit: stock_correction_requested

t3  Daniel (correction_approval) approves. NOTHING EXECUTES.
    -> review_item.status = 'approved'
    -> the discrepancy is still active; the card still shows it

t4  Sarah (reconciliation) carries it out, under the balance lock.
    -> movement seq 9: action=correction
                       corrects_movement_id = <movement 8>
                       review_item_id       = <the approved item>
                       quantity_delta       = -2
                       balance_before       = 30
                       balance_after        = 28
    -> head: current_balance = 28
    -> conditionActive(stock_discrepancy:<8>) = false
    -> card disappears if no other entry is unresolved
    -> audit: stock_corrected
```

Three properties this sequence guarantees:

- **the expected figure survives** — movement 8 holds 30 for ever, and the
  count that disproved it never overwrites it;
- **approval and execution are separate acts by separate people**, and the
  balance does not move at approval;
- **the delta that moved the ledger is itself a permanent, immutable row**
  naming the entry it resolved and the approval it consumed.

---

## 11. Approval request and consumption model

| Property | Mechanism |
|---|---|
| Bound to the exact discrepancy | `subject_type='stock_movement'`, `subject_id` = the movement id; the trigger verifies the target has `is_discrepancy = 1` |
| Bound to the exact balance | the executing `correction` must share `service_id`, `owner_ref` and `preparation_key` with the target; enforced in the insert trigger |
| Bound to the exact delta | executed `quantity_delta` must equal the approved `requested_quantity_delta`; refused otherwise |
| Consumed exactly once | `UNIQUE (review_item_id)` on `record7_stock_movements` |
| One correction per discrepancy | `UNIQUE (corrects_movement_id)` |
| Approval does not execute | `decideReview()` branches on `subject_type` (below) |
| Request-time authority | `AccessPolicy` re-checked inside the service on every write, not only at route middleware |

**`decideReview()` must branch — owner-approved change.** It currently calls
`carryOut()` for every approved `correction_request`, writing a corrective
Administration. For `subject_type = 'stock_movement'` it must approve and stop:
execution requires `reconciliation`, the current balance lock, request-time
authority and the exact approved delta, none of which the approver holds or
takes. `subject_type = 'administration'` continues its current Section 2.3
behaviour unchanged, extended only by the compensating movement in §9.1–9.3
written inside the same transaction.

A blocked attempt is audited **after** the transaction unwinds, using a
`ControlledDrugRefusal`-style exception carrying its own code — the pattern that
exists because an audit row written inside a rolled-back transaction disappears
with it.

---

## 12. Locking and idempotency

**Row locked.** `record7_stock_balances` for
`(service_id, owner_ref, preparation_key)`.

**Lock order.** The balance head first, always, before any other row. A
transaction never holds two balance locks at once — a round touching several
people takes one balance at a time, each in its own transaction. A deadlock
cycle cannot form by construction.

**Opening a balance.** `insertOrIgnore` then `SELECT … FOR UPDATE`. Taking
`FOR UPDATE` on a row that does not exist sets a gap lock under REPEATABLE READ,
and two workers opening the same first movement would deadlock on the gap rather
than queue. `insertOrIgnore` resolves the race on the unique key instead. This
is Section 2.5's proven pattern.

**Sequence allocation.** Under the lock, `last_sequence_no + 1`.
`UNIQUE (service_id, owner_ref, preparation_key, sequence_no)` is the backstop:
two workers reading the same tail cannot both write; the second loses at the
index rather than recording a balance derived from state that has already moved.

**Concurrent administrations.** Serialised by the head lock. The second waits,
re-reads under the lock, and either succeeds or takes the §7 shortfall path.

**Administration racing a count.** Whichever takes the lock first wins. The
count's `expected_quantity` is read under the lock, so it can never be stale.

**Correction concurrency.** Executed under the same lock; both
`UNIQUE (review_item_id)` and `UNIQUE (corrects_movement_id)` are checked by the
database. Two managers acting on one approval, or on one discrepancy, resolve to
one winner and one plain refusal.

**Deadlock and retry.** No retry loop anywhere. A retried movement is a
duplicate movement. A deadlock or lock-wait timeout fails the transaction and
the person is told what happened.

### 12.1 Replay and double submission

| Path | Protection |
|---|---|
| Scheduled administration | existing `dose_claim` generated column + unique index; plus `UNIQUE (stock_movement_id)` on administrations |
| PRN | existing Section 2.4 server-issued attempt token |
| `receipt`, `stock_check`, `waste`, `return_to_stock` | **new** `record7_stock_attempts` token |
| `correction` | none needed — `UNIQUE (review_item_id)` |
| threshold set | none needed — idempotent |

Receipt, count, waste and return each create or destroy quantity from a plain
form post, and a double submit doubles a delivery.

---

## 13. Ordinary and controlled boundary

Enforced in the database, not by convention.

1. `record7_stock_movements_validate_insert` refuses any row whose medicine has
   `is_controlled = 1`, **before any other check** — *"a controlled medicine is
   accounted for in the controlled drug register"*.
2. `record7_stock_balances` carries the same guard. No balance, no movement, no
   head.
3. Ordinary correction approval cannot resolve a Section 2.5 discrepancy.
   Different tables, different correction chains, different permissions
   (`manage_controlled_drugs` versus `reconciliation`), different approval
   subjects.
4. The stock screen and manager board **may display** a controlled balance,
   read from `record7_cd_balances` / `record7_cd_register` and labelled as the
   register's figure. Read only. No write path, no second copy, no
   reconciliation between the two.
5. `ManagerBoard::stockConcerns()` stops deriving `controlled_drug_discrepancy`
   from `record7_stock_events` and reads the register instead.
6. Section 2.5 history is never touched, rewritten, imported from or migrated.

The current fixture's Oxycodone 20-versus-22 conflict is the reason for all six.
`cd_register` 28/29 give receipt 23 → one given at 04:40 → 22.000, while
`stock_levels` 88 holds 20 and `stock_events` 88 says *"Book says 22 after the
4:40 dose. Cupboard holds 20."* One fictional incident, written twice. It
disappears when the ordinary rows are retired rather than imported.

---

## 14. Fixtures, permissions and legacy data

### 14.1 Ownership: client only

The schema represents `owner_type ENUM('client','service')` so a later section
needs no data migration. Section 2.7 is **client-owned only**:

- `StockLedger` refuses `owner_type = 'service'` with a plain message and fails
  closed;
- no fixture, no route and no screen offers it;
- no test implies service-stock workflow support exists — the only tests are
  that it cannot be used (§16.2).

The `owner_ref` design and its unique index remain, because they are cheap and
because a nullable `client_id` inside a unique index is a latent concurrency
hole whether or not the arm is enabled.

### 14.2 Permissions

| Permission | Authorises | Competency gate |
|---|---|---|
| `administer_medication` | `administration`, `non_administration` — the consequence of a clinical act | `general_medication` (existing) |
| `stock_management` | `opening_balance`, `receipt`, `stock_check`, `waste`, `return_to_stock`, setting a threshold, raising a correction request | `stock_management` (existing, type 101) |
| `reconciliation` | `correction` only — declaring what is true when the ledger and the cupboard disagree | none |
| `correction_approval` | approving a correction request, both paths | none |

A stock manager cannot erase or reconcile a discrepancy. A carer does not need
`stock_management` to give a tablet.

### 14.3 Fixture changes

**The current fixture cannot exercise any stock workflow.** Verified by calling
`AccessPolicy::decide()` for every user against both live houses: every user is
denied `stock_management`. Sarah Ahmed holds it by role (R5) but has no
`stock_management` competency; Daniel Evans holds explicit per-house grants
written by `Record7Section12Seeder::managerGrants()` but has neither the role
grant nor the competency, so his grant can never take effect.

Two competency grants, in `Record7Section1Seeder` beside `secondSignatory()`
and `reopenAuthority()` — **not** in a migration, because
`Record7Section0Seeder::clearExisting()` deletes `record7_user_competencies` on
every reseed, which is the failure that occurred twice before:

1. Sarah Ahmed — a current `stock_management` competency.
2. Daniel Evans — a current `stock_management` competency, making his
   per-house grants effective for the first time.

Resulting matrix:

| Person | `stock_management` | `reconciliation` | `correction_approval` |
|---|---|---|---|
| Sarah Ahmed (R5 Medication Lead) | ✔ | ✔ | ✘ |
| Daniel Evans (R4 Service Manager) | ✔ | ✘ | ✔ |
| Olivia Carter / Noah Williams (R7) | ✘ | ✘ | ✘ |

Daniel is the exact negative required: a stock manager who cannot reconcile. He
is also the approver, so approval and execution are separated between two real
fixture people without inventing a user. No manual database edits; a test
asserts every positive workflow is reachable immediately after a clean seed.

### 14.4 Legacy data — no import

**Section 2.7 imports nothing** from `record7_stock_levels` or
`record7_stock_events`.

15 of 18 rows in each are reseed artefacts. The cause is exact:
`Record7Section0Seeder::clearExisting()` issues `SET FOREIGN_KEY_CHECKS=0` and
deletes `record7_services`, and the stock tables are not in its list;
`Record7Section12Seeder` clears stock by `service_id` alone. The comment eight
lines below its own stock delete explains why that fails — *"Section 0 rebuilds
the houses with new ids, so rows from the previous run are orphaned rather than
matched"*. `ReviewItem` got a `reference`-based fallback; the stock tables have
no `reference` column, so none was possible, and orphans accumulated across six
reseeds. No other Record7 table is affected: clients, administrations,
cd_register and review_items are all clean.

Every orphan is a byte-for-byte duplicate of the live trio. The three live rows
are not imported either: two are the controlled Oxycodone duplicate Section 2.5
already holds properly, and the third pair carries no ownership, no unit and no
history. **The orphaned rows are left exactly where they are.** Nothing is
deleted or cleaned up by this section.

The new tables carry a `reference`, and the seeder clears by it as well as by
`service_id`, so this cannot recur.

### 14.5 The fictional ledger, built explicitly

`Record7Section12Seeder` builds the ledger **through the `StockLedger` service**,
not by direct inserts, so the fixture cannot contain a state the application
could not have produced.

| Person | Medicine | Ledger | Exercises |
|---|---|---|---|
| Sylvia (Rosewood) | Insulin glargine | `opening_balance`, `receipt` | an ordinary tracked balance with a threshold |
| Margaret (Oakwood) | Macrogol | `opening_balance` drawn to 0 by administrations | `stock_out`, and the per-person split the old service-level row could not express |
| Joyce (Oakwood) | Macrogol | separate balance, healthy | proves two people's supplies of one medicine are separate |
| Sylvia (Rosewood) | Senna | `opening_balance`, then a `stock_check` short by 2 | a live unreconciled discrepancy — the replacement for the free-text-resolved `stock_events` 90 |
| Sylvia (Rosewood) | Senna | a second `stock_check`, also short | **two** unresolved entries on one balance, for the aggregation rule in §6 |
| Dennis (Oakwood) | any ordinary scheduled medicine | balance, **no threshold row** | `stock_low` unavailable |
| — | one tracked medicine whose prescription has no structured dose | balance only | the tracked-but-unquantified path |

Thresholds are configured explicitly on **selected ordinary client-owned
balances**, and **at least one balance is deliberately left with no threshold
row**, so both the low-stock derivation and the threshold-unavailable behaviour
are exercised. Every threshold value carries a comment marking it as fictional
design and test data — it is not inherited policy, not a clinical reorder level,
and never a runtime default. Nothing invents a threshold at runtime.

Plus the structured scheduled-dose values of §8.4, each marked as fixture design
data.

### 14.6 Production migration — contract only

Section 2.7 ships **no** production data migration. Fixture structure must not
be assumed to match production. What a real deployment must do, stated so it can
be planned separately:

1. Map every real `stock_levels` row to a **named owner**, explicitly, from that
   deployment's own records. Ownership is never inferred from a null
   `client_id`.
2. Establish a `unit` for every balance explicitly. The old table has none.
3. Exclude every controlled medicine; those balances belong to Section 2.5.
4. Write each opening position as one `opening_balance` movement with
   `imported = 1` and an `import_note` naming the source and stating that no
   movement history existed before that point.
5. Map unresolved `discrepancy` events explicitly, or carry them forward outside
   the ledger. A bare count with no ledger behind it cannot produce a truthful
   `balance_before`.
6. Never fabricate a movement, actor, time or quantity the source did not hold —
   the rule Section 2.6's backfill established.

### 14.7 Retiring the old tables

Neither is dropped.

**`record7_stock_levels` — retired.** `BEFORE INSERT` and `BEFORE UPDATE`
triggers refuse all writes. `IssueRegistry` and `ManagerBoard` stop reading it;
`stock_out` and `stock_low` move to the derived balance and the threshold table.
It stays readable so the pre-2.7 record survives.

**`record7_stock_events` — partially retained.** `count` and `discrepancy` take
no new rows and no updates; read-only history. `delivery_overdue` stays live as
an operational workflow and keeps its mutable `resolved_at`.

`delivery_overdue` is **explicitly not part of the stock ledger or discrepancy
truth**, and the model, the migration and the manager card all say so. It
asserts no quantity: the condition it describes is "the pharmacy has not
delivered" and the fact that ends it is "it arrived". Nothing about it can make
a missing quantity cease to exist, because it never claimed one.

`ManagerActions::close()` stops writing `resolved_at` for `discrepancy` rows,
and the trigger refuses it even if a future caller forgets.

After this migration there is exactly one source of truth for an ordinary
balance.

---

## 15. Manager integration and audit

### 15.1 Derived conditions

| Key | Derivation |
|---|---|
| `stock_out:<balance_id>` | `current_balance <= 0` |
| `stock_low:<balance_id>` | a threshold row exists and `0 < current_balance <= low_threshold` |
| `stock_discrepancy:<movement_id>` | `is_discrepancy = 1` with no correction naming it (§5.2) |
| `stock_verification_due:<corrective_administration_id>` | active until a qualifying stock check exists — after it, same organisation, service, client and preparation/balance, recorded through the §4 `stock_check` pathway (§9.3) |
| `stock_event:<event_id>` | `delivery_overdue` only; condition unchanged |
| controlled-drug discrepancies | unchanged — Section 2.5 only |

`IssueState::SAFETY_CRITICAL` already lists `stock_discrepancy` and `stock_out`;
`stock_verification_due` is added. `IssueRegistry::requiresEvidence()`'s special
case loading a stock event to check `is_controlled` becomes unreachable once
controlled medicines cannot appear there — kept as belt and braces, with a test
asserting it can never fire.

### 15.2 Audit events

```
stock_opening_balance_recorded
stock_received
stock_counted
stock_discrepancy_found
stock_correction_requested
stock_corrected
stock_wasted
stock_returned
stock_threshold_set
stock_threshold_cleared
stock_shortfall_verified
stock_movement_blocked          (written after the transaction unwinds)
```

Administration-driven movements are **not** audited separately. The
administration already emits `medication_administered`,
`prn_medication_administered` or `medication_non_administration_recorded`;
those events gain the movement reference and, where §7 applied, the shortfall
basis. A second event per dose would double the trail and make it harder to
read, not safer.

---

## 16. Test plan

### 16.1 Positive

**Ledger and head** — each of the eight actions writes the arithmetic in §4;
the head agrees after every write; a head rebuilt from its ledger from scratch
equals the live head including `last_counted_at`; `opening_balance` only at
sequence 0.

**`stock_check` semantics** — a matching count changes no balance and opens no
discrepancy; a differing count changes no balance, preserves both figures, and
opens one; `last_counted_at` moves in both cases; the balance moves only when
the approved correction lands.

**Preparation identity** — two strengths hold two balances; two units hold two
balances; a corrected medicine **name** does not split a balance; a corrected
**strength** lands later movements on a new key and leaves the old intact.

**Ownership** — two clients prescribed one medicine in one house hold two
separate balances (the Macrogol case).

**Administration effects** — every row of §8.1; tracked-and-quantified debits;
untracked moves nothing; tracked-but-unquantified moves nothing and says so;
PRN debits `dose_amount`; `check_and_record` debits only where stock was
accounted; `none` never debits; `withheld` is refused at recording and moves
nothing.

**Corrections** — **every one of the ten rows of the §9.5 matrix**, each
asserting the corrective administration, the new movement's verb, the exact
delta, the unit source and whether a discrepancy or verification requirement
was raised. Path B end to end as §10. The historical amount is used and the
current prescription is never read.

**The attributable-quantity rule** — `removed 2 / given 1 / wasted 1` corrected
to `refused` compensates **+1**; the wasted unit is still wasted afterwards;
`removed 2 / given 1 / returned 1` compensates **+1**;
`removed 3 / given 1 / returned 1 / wasted 1` compensates **+1**.

**Qualifying stock check** — `stock_verification_due` clears on a count that is
later, same organisation, same service, same client and same preparation; and
does **not** clear on an earlier count, another client's count, another
preparation's count, another service's count, a resolution note, a review
decision or an `IssueState` closure.

**Card distinction** — the ordinary and controlled consequence sentences are
asserted verbatim, and a test confirms the two cards differ in wording, status
marker and available action, not only in colour.

**Discrepancy identity** — two unresolved entries on one balance; correcting the
earlier leaves the later active; the card persists with a decremented count.

**Permissions** — Sarah receipts, counts, sets a threshold, raises and carries
out a reconciliation; Daniel receipts, counts and **approves** but cannot carry
out; Olivia gives a dose and moves stock through the clinical path but cannot
receipt, count or correct — all on a clean seed with no manual grants.

**Thresholds** — `stock_low` fires where a row exists; is unavailable where none
does; `stock_out` fires regardless; setting and clearing are audited.

### 16.2 Adversarial

- an ordinary movement or balance for a controlled medicine — refused by
  trigger, from raw SQL, bypassing every service;
- an application write with `owner_type = 'service'` — fails closed with a plain
  message (the only ownership test; nothing implies the workflow exists);
- a browser-supplied `balance_after` that disagrees with the arithmetic —
  refused;
- a browser-supplied `expected_quantity` on a count — ignored; the server figure
  is used;
- a `stock_check` that tries to move the balance to the counted figure —
  refused;
- `UPDATE` and `DELETE` on `record7_stock_movements` — refused at model and
  database level, including on a correction;
- `UPDATE` on `record7_stock_levels`, and on a `discrepancy` row's
  `resolved_at` — refused;
- setting `resolved_at`, closing the `IssueState`, taking ownership, escalating
  and writing a resolution note — the discrepancy stays active after each and
  after all five together;
- correcting the earlier of two discrepancies — the later stays active and the
  card stays visible;
- a `correction` with no approved request, a declined one, an already-consumed
  one, one from another house, one whose target is not a discrepancy, one whose
  target belongs to a different balance, or a delta differing from the approved
  figure — each refused;
- an administration driving the balance negative with no verification evidence,
  or with a partial set — refused by CHECK;
- `shortfall_basis` on any action other than `administration` — refused;
- a §9.3 correction with no historical amount — the clinical correction stands,
  no movement exists, no zero-quantity movement is fabricated, and
  `stock_verification_due` is active;
- a correction whose `requested_dose_unit` differs from the original movement's
  unit, or from the balance head's unit where there is no original — refused,
  with no conversion attempted;
- a `correction` driven below zero that is not marked `is_discrepancy` —
  refused by `stock_movements_negative_is_discrepancy`;
- `review_item_id` set on a `receipt`, `waste`, `stock_check`,
  `return_to_stock`, `non_administration` or `opening_balance` — refused;
- `shortfall_basis` on a movement that carries a `review_item_id` — refused;
- a negative quantity, a zero-quantity receipt, a `stock_check` with no count —
  each refused;
- an administration whose movement insert fails — no administration row
  survives.

### 16.3 Concurrency

In-suite, structural: `FOR UPDATE` is issued before any read that decides;
`insertOrIgnore` precedes the lock; the sequence comes from the locked head.

Out of suite, behavioural — a two-connection PDO probe in the scratchpad,
following the Section 2.5 and 2.6 pattern, inside `START TRANSACTION … ROLLBACK`
so it leaves nothing behind:

1. two workers administering the same person's tracked medicine — the second
   blocks, then computes from the moved balance;
2. an administration racing a `stock_check` — serialised; the count's expected
   figure is never stale;
3. two corrections consuming one approval — the second refused on
   `UNIQUE (review_item_id)`;
4. two corrections of one discrepancy — the second refused on
   `UNIQUE (corrects_movement_id)`;
5. two movements claiming one sequence number — the second refused on the
   composite unique;
6. two workers opening the same first balance — both queue, neither deadlocks on
   a gap.

The distinction between structural and behavioural evidence is stated in the
test file, as in Sections 2.5 and 2.6. Structural evidence is never reported as
proof of serialisation.

### 16.4 Mutation plan

| # | Mutation | Must be killed by |
|---|---|---|
| M1 | remove the controlled-medicine exclusion from the insert trigger | the ordinary-movement-for-Oxycodone tests |
| M2 | change the derived discrepancy condition to `whereNull('resolved_at')` | the five acknowledgement tests |
| M3 | make `stock_check` set `balance_after = counted_quantity` | the count-does-not-move-the-ledger tests |
| M4 | floor `balance_after` at zero | the ledger-self-consistency tests |
| M5 | drop `is_discrepancy` from a negative-balance administration | the shortfall-surfaces tests |
| M6 | suppress a second discrepancy while one is open on the balance | the two-entries and correct-the-earlier tests |
| M7 | drop `UNIQUE (service_id, owner_ref, preparation_key, sequence_no)` | the concurrent double-write tests |
| M8 | let `stock_management` authorise a `correction` | the Daniel-cannot-reconcile tests |
| M9 | let a correction proceed with a delta differing from the approved figure | the approval-consumption tests |
| M10 | remove the transaction from `recordGiven()` | the atomicity tests |
| M11 | let the trigger accept a browser-supplied `balance_after` | the derived-balance tests |
| M12 | make `stock_low` fire when no threshold row exists | the unavailable-threshold tests |
| M13 | drop the `shortfall_evidence` CHECK | the negative-without-verification tests |
| M14 | make `decideReview()` auto-execute a stock correction request | the approval-does-not-execute tests |
| M15 | read the current prescription's dose in a §9.3 correction | the historical-amount tests |
| M16 | remove the service-ownership refusal from `StockLedger` | the fails-closed test |
| M17 | compensate `given + wasted` instead of `quantity_given` | the attributable-quantity tests (§9.0) |
| M18 | accept a mismatched unit, or convert between units | the unit-fails-closed tests, both sources |
| M19 | let an earlier count, or another preparation's count, resolve `stock_verification_due` | the qualifying-stock-check tests |
| M20 | drop `stock_movements_negative_is_discrepancy` | the negative-correction and negative-administration tests |
| M21 | render the ordinary and controlled cards with the same wording and action, differing only in colour | the card-distinction tests |

Every mutation must kill at least two tests; one killed by a single test is
treated as evidence the suite is thin there, as in Sections 2.5 and 2.6.

---

## 17. UI and routes

| Route | Permission | Purpose |
|---|---|---|
| `GET  /record7/stock` | `view_dashboard` | tracked balances for the house; out, low and discrepant first; untracked and unquantified medicines named honestly; controlled balances read-only from the register and labelled |
| `GET  /record7/stock/{balance}` | `view_dashboard` | one balance, its movements newest first, its unresolved discrepancies listed individually |
| `POST /record7/stock/{balance}/receipt` | `stock_management` | record a delivery |
| `POST /record7/stock/{balance}/count` | `stock_management` | record a verified count |
| `POST /record7/stock/{balance}/threshold` | `stock_management` | set or clear the reorder level |
| `POST /record7/stock/{movement}/correction-request` | `stock_management` or `reconciliation` | raise a correction request against one discrepancy |
| `POST /record7/stock/{movement}/correct` | `reconciliation` | carry out an approved correction |

Plus one change to the existing confirm screen: where the locked balance cannot
cover the dose, the shortage, the basis selector, the statement field and the
optional observed quantity appear, and the "Given" control is unavailable until
the verification is complete (§7.1).

New pages: `R7Pages/Stock.jsx`, `StockBalance.jsx`. Approval happens on the
existing manager screen; no new approval UI.

Not built: ordering, suppliers, batches, expiry, barcode scanning, transfers,
destruction management, standalone waste and return screens, and anything
service-owned.

---

## 18. Expected files

**New migrations** (additive; no previously applied migration is edited):

1. `2026_09_06_000001_record7_stock_ledger.php` — `record7_stock_movements`,
   `record7_stock_balances`, generated columns, unique keys, CHECKs
2. `2026_09_06_000002_record7_stock_integrity.php` — `no_rewrite`, `no_delete`,
   `validate_insert` on both tables; the two `record7_administrations` columns;
   the extended `record7_administrations_no_rewrite` and
   `record7_administrations_validate_insert`
3. `2026_09_06_000003_record7_stock_thresholds.php` — `record7_stock_thresholds`
4. `2026_09_06_000004_record7_stock_correction_approval.php` — the four
   `record7_review_items` columns and their CHECKs
5. `2026_09_06_000005_record7_stock_attempt_tokens.php` —
   `record7_stock_attempts` and its integrity triggers
6. `2026_09_06_000006_record7_retire_mutable_stock.php` — write guards on
   `record7_stock_levels` and on non-`delivery_overdue` `record7_stock_events`

**New application files**

- `app/Models/Record7/StockMovement.php`, `StockBalance.php`,
  `StockThreshold.php`, `StockAttempt.php`
- `app/Services/Record7/StockLedger.php`, `StockRefusal.php`
- `app/Http/Controllers/Record7/StockController.php`
- `resources/js/R7Pages/Stock.jsx`, `StockBalance.jsx`

**Modified**

- `app/Models/Record7/Administration.php` — `FROZEN` gains two columns
- `app/Models/Record7/ReviewItem.php` — the four new columns
- `app/Models/Record7/StockLevel.php`, `StockEvent.php` — deprecation notes,
  write guards
- `app/Services/Record7/AdministrationRecorder.php` — transaction; stock effect;
  the §8.2 declaration
- `app/Services/Record7/PrnAdministration.php` — stock effect inside its
  existing transaction and lock
- `app/Services/Record7/IssueRegistry.php` — the derived conditions of §15.1
- `app/Services/Record7/ManagerBoard.php` — `stockConcerns()` from the ledger,
  the threshold table, the CD register, and the §6 aggregation
- `app/Services/Record7/ManagerActions.php` — stop writing `resolved_at`;
  branch `decideReview()` on subject type; compensating movements in `correct()`
- `app/Http/Controllers/Record7/RoundController.php` — the §7.1 verification
- `routes/web.php` — seven routes
- `database/seeders/Record7Section1Seeder.php` — two stock competencies
- `database/seeders/Record7Section12Seeder.php` — structured scheduled doses;
  the explicit fictional ledger; reference-based clearing

**Tests**

- New: `Record7StockTest.php`, `Record7StockCorrectionTest.php`
- Updated: `Record7ManagerTest.php`, `Record7IssueLifecycleTest.php`,
  `Record7AdministrationTest.php`, `Record7NonAdministrationTest.php`,
  `Record7PrnTest.php` — per §19
- Scratchpad probe: `stock_concurrency.php`

---

## 19. Existing tests that change

Nothing is silently deleted.

**`Record7ManagerTest::test_a_resolved_stock_discrepancy_is_not_an_active_concern`**
Old: a `StockEvent` with `resolved_at` set is absent from `stockConcerns()`.
Unsafe because the row it selects is the Senna discrepancy closed with a
sentence, while two tablets remain unaccounted for. Replaced by
`test_a_stock_discrepancy_stays_active_until_a_correction_names_it`.

**`Record7IssueLifecycleTest::test_closing_a_controlled_drug_discrepancy_needs_evidence`**
Old: closing a controlled-medicine `stock_event` without evidence is refused.
Obsolete because no controlled medicine may have an ordinary stock record, so
the row ceases to exist; the rule is now carried by Section 2.5's derived
condition, which is stronger. Replaced by
`test_a_controlled_medicine_cannot_have_an_ordinary_stock_record` and
`test_closing_an_ordinary_stock_discrepancy_needs_evidence_and_still_does_not_resolve_it`.

**`Record7IssueLifecycleTest::test_low_stock_stays_until_the_cupboard_is_restocked`**
Old: `$level->update(['quantity' => 500])` clears `stock_low`. Unsafe because it
writes a balance directly, which must be impossible. Replaced by
`test_low_stock_stays_until_stock_is_actually_received` and
`test_low_stock_is_unavailable_where_no_threshold_is_recorded`.

**The four "does not touch stock" tests** —
`test_recording_an_administration_does_not_touch_stock`,
`test_no_outcome_changes_stock`, `test_giving_a_prn_does_not_touch_stock`,
`test_the_attempt_flow_changes_no_stock`. These are **not** unsafe; they were
correct for 2.2–2.6 and are the guard that kept the boundary honest. Each is
kept and narrowed to the untracked case — e.g.
`test_an_administration_of_an_untracked_medicine_moves_no_stock` — with new
positive tests alongside. Nothing is removed; the assertions get tighter.

---

## 20. Settled decisions

All five contradictions raised during design have been ruled on by the owner and
are incorporated. None remains open.

| | Ruling | Where it lives |
|---|---|---|
| **C1** | Ordinary and controlled discrepancy cards must differ in wording, status marker, consequence text and available action. Colour may reinforce, never carry, the distinction. Section 2.5 behaviour unchanged. | §6.1, tested verbatim |
| **C2** | The §9.3 debit is an `administration` movement. No zero movement fabricated, no correction constraint weakened, no ninth verb. Unit checked against the authoritative balance/preparation unit, exact match, no conversion, fails closed. | §9.3, §9.5 row 7 |
| **C3** | `stock_verification_due` added, keyed to the corrective administration, cleared only by a qualifying stock check — later, same organisation, service, client and preparation, through the §4 pathway. Added to `SAFETY_CRITICAL`. | §9.3, §15.1 |
| **C4** | Entry-level identity, keys and correction requirements retained. Card aggregates by balance, uses the oldest unresolved key, advances as entries are corrected. **No persistent balance-level key.** Closing one `IssueState` never hides the others. | §5, §6 |
| **C5** | Fixture configures thresholds on selected client-owned balances, deliberately omits at least one, documents all values as fictional design data, and tests both branches. Never invents one at runtime. | §14.5, §16.1 |

**Waste-inclusive compensation — final ruling.** A clinical administration
correction reverses only the quantity attributable to the corrected consumption
(`quantity_given`), preserving independently recorded return and waste
dispositions. It is never described as "reversing the original debit". Recorded
in full at §9.0 and applied through every row of §9.5.

---

## 21. Out of scope

Service-owned and provider stock workflows, fixtures, routes and screens (the
schema represents the type; nothing else does); a `withheld` stock pathway
(deferred with its clinical-authority workflow); inter-service transfers; formal
destruction and denaturing; supplier ordering and pharmacy integration; batch,
lot and expiry tracking; barcode scanning; the incident module; dm+d catalogue
synchronisation; the production data migration (contract only, §14.6); the final
UI redesign; and any change to Section 2.5 controlled-drug history.
