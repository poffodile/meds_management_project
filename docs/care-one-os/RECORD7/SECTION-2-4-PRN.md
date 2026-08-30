# Section 2.4 — As-required (PRN) medication

**Status:** implemented, awaiting owner review
**Written:** 30 August 2026

Reasoning and sources live here, not in the code. No regulatory citation appears
in any migration or class comment, because guidance is revised and a comment
cannot be revised with it.

---

## 0. What the fixture values are, and are not

**Every structured PRN value in the Record7 fixtures is fictional test and design
data.** None of it is a migrated prescribing fact, none of it was derived from a
real prescription, and none of it should be read as a clinical instruction for
any actual person.

**Dennis's paracetamol and Aisha's salbutamol limits are deliberate fictional
definitions**, written to exercise the two limit rules the new model has to be
able to tell apart: a maximum number of doses, and a maximum total amount. They
were chosen as the smallest internally consistent reading of fictional data that
already existed, and they are illustrative of the *model*, not of any medicine.

**`prn_max_per_day` was historically ambiguous.** In this fixture it carried a
count in one prescription and an amount in another, which is why it is no longer
the authoritative safety source and why nothing in Section 2.4 decides anything
from it. It is preserved for history and for the legacy front ends that still
read it. Its stored values were deliberately **not** reinterpreted, converted, or
copied into the new columns.

**Integrating real data is a separate, explicit mapping exercise.** Production or
current-system prescribing instructions must be mapped one by one, from the
actual prescription, by somebody qualified to read it. Structured limits must
never be inferred from these fixtures, back-filled from `prn_max_per_day`, or
guessed from a directions string. Where a real instruction cannot be expressed in
the current columns, that is a finding to raise — not a value to approximate.

---

## 1. Why `prn_max_per_day` was not enough

The existing column carried two different rules at once. In the fixture, `4`
against Dennis's two-tablet paracetamol read as four **administrations**, while
`8` against Aisha's two-puff inhaler only made sense as eight **puffs** — with a
four-hour gap, eight administrations is unreachable in a day.

One number cannot mean both. A limit that is sometimes doses and sometimes units
is worse than no limit, because it reads as enforced.

**The column is untouched** — it stays for history and for the legacy front ends
that still read it — and **nothing in Section 2.4 decides anything from it**. A
test asserts the string `prn_max_per_day` does not appear in `PrnAdministration`
at all.

## 2. One rule per column

| Column | Rule |
|---|---|
| `dose_min` / `dose_max` / `dose_unit` | what one dose may be |
| `prn_limit_period` | `rolling_24h` or `calendar_day` |
| `prn_max_administrations` | how many times in that window |
| `prn_max_total_amount` | how much in that window, in `dose_unit` |
| `prn_review_after_minutes` | when to go back and ask |

All nullable. **Where a prescription is silent, nothing is enforced and nothing
is invented.** A limit nobody wrote down is a limit nobody agreed, and a
fabricated maximum that blocks a needed dose is as dangerous as a missing one
that permits too many.

Both limits are independent. Dennis's paracetamol states four doses **and** eight
tablets — the same ceiling from two directions, both stated so neither has to be
inferred. Aisha's inhaler states only eight puffs.

## 3. Rolling, not calendar

Four doses at ten in the evening and four after midnight is eight doses in six
hours, and a calendar-day allowance calls every one permitted.

Fixtures use `rolling_24h`. `calendar_day` remains representable for a
prescription that genuinely says so, but it has to say so. A test freezes the
clock at 06:00 and proves rolling sees four doses where calendar sees two.

**Source:** guidance describes maximum dose in a 24-hour period. The legacy
system used a calendar day (`->where('date', ...)`), which is where the midnight
boundary came from — that behaviour was inspected and deliberately not carried
forward.

## 4. What consumes an allowance

**Only a dose that actually went in** — `given` or `self_administered`.

A refusal, an absence, a medicine that was not there, a missed dose: all real
records, none of them a dose. Somebody who declined at two and asks at three has
had nothing and must not be locked out by their own refusal. Four separate tests
prove each one.

The minimum interval runs from `administered_at` of the last dose actually
given. Legacy made the same choice for the same reason, and its comment is worth
preserving: editing a note must never push the clock forward.

## 5. Dose is a number and a unit

`dose varchar(120)` is for a person to read. Nothing computes from it — §4 of the
brief forbids parsing clinical limits from free text, and "Two puffs" cannot be
validated against a range.

The administration **snapshots** `dose_amount` and `dose_unit`, so a record made
this afternoon stays readable when the prescription changes next month. Tested by
changing the prescription afterwards and asserting the administration is
unmoved.

## 6. Effectiveness, and concern, are two questions

The hardcoded one-hour follow-up is gone. `due_at` now comes from
`prn_review_after_minutes`; **where no interval is stated, no follow-up is
created** and the gap is surfaced on screen rather than filled in.

`concerning_response` is a **separate axis** from `outcome`. A medicine can be
entirely effective and still produce something worth reporting; it can do nothing
at all with no reaction. Folding one into the other loses whichever mattered.

A concern must say what was seen **and** what was done — "something worried me"
with nothing after it is not something the next shift can act on. Record7 does
not attempt to say what the reaction was; that is for a clinician.

**Nothing is automatically classified as safeguarding.** The screen says so
explicitly. `prn_concerning_response` joins `IssueState::SAFETY_CRITICAL`, so
closing it needs evidence rather than a tick.

## 7. Outside the round

A person in pain at three in the morning is not a medication round. Requiring one
would leave a worker choosing between refusing a needed medicine and opening a
fake round to get past the software.

PRN is therefore person-scoped: `/record7/person/{client}/prn`. Every authority
check the round makes still runs — account, organisation, house access, the
access window, permission, competency — through the same `RoundAuthority` the
round uses, so the two cannot diverge. Only the seventh check, the open round, is
omitted, and that is a scheduling fact rather than a safety one.

## 8. Boundaries

| Not built | Owner | How it is enforced |
|---|---|---|
| Controlled-drug PRN | 2.5 | `eligibility()` refuses before any other check; proven against a forged server-side request |
| Stock effects | 2.7 | tested: levels and events unchanged |
| Corrections | 2.7 | untouched |
| `withheld` | pending an authority model | unchanged from 2.3 |

Controlled PRN medicines stay **visible** on the person's list with the reason
they cannot be given here. Hiding them would leave a worker believing there is
nothing for somebody in pain — "there is nothing" and "there is something you
cannot reach from here" lead to completely different next actions.

## 9. Navigation placement is a UI decision, not a gap

The PRN workflow is **functionally complete and reachable**: from a person's
record at any hour, with no medication round open, carrying every authority check
the round performs.

There is deliberately **no Today or dashboard tile** in Section 2.4. Where PRN
sits in Record7's navigation is a question about how the product is laid out, and
it belongs with the broader UI review rather than being settled piecemeal here.

This is a placement decision, not an incomplete clinical workflow. Nothing about
a person's ability to receive an as-required medicine depends on it.

---

## 10. Known clinical-data boundary

**Record7 does not calculate combined regular-plus-PRN maxima.** Where the same
medicine or therapeutic class is prescribed both regularly and as required, no
external maximum is verified unless an explicit structured instruction provides
the combined limit. Record7 will not claim to have checked something it has not.

This is a prescribing and catalogue-integration question, deferred.

## 11. Sources

Consult the current published versions; these were applicable at the time of
writing and guidance is revised.

- NICE NG5 — *Medicines optimisation*
- NICE SC1 — *Managing medicines in care homes*
- NICE NG67 — *Managing medicines for adults receiving social care in the community*
- CQC — medicines guidance for adult social care, including PRN and MAR records
- NHS Specialist Pharmacy Service — PRN and medication incident guidance
- Misuse of Drugs Regulations 2001

**None are cited in executable code.** Record7's enum values are technical
identifiers, not regulatory wording, and must not be quoted as such.
