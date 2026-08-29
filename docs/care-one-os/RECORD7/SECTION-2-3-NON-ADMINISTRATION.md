# Section 2.3 — Recording why a medicine was not given

**Status:** implemented, awaiting owner review
**Written:** 29 August 2026

This document holds the reasoning and the sources. The code holds no regulatory
citations at all — deliberately, because guidance is revised and a comment in a
migration cannot be revised with it.

---

## 1. Why four outcomes and not one

The single most damaging thing this section could have done is offer one button
saying "not given".

Four genuinely different things can have happened to a planned dose:

| What happened | Stored outcome | Why it is its own fact |
|---|---|---|
| Offered properly, and they said no | `refused` | A refusal is a person exercising a choice. It may need a re-offer, and repeated refusals are a clinical signal — not an administrative gap. |
| They were not there to be offered it | `person_unavailable` | Nothing was wrong with the medicine and nobody declined anything. The service simply could not offer it. |
| The medicine could not be given | `not_available` | About supply, not about the person. It needs pharmacy or stock action, and it may affect everybody on that medicine. |
| Nobody got to them, and nobody recorded why at the time | `missed` | This is a medication error. It needs an explanation, an action and an escalation, because somebody will have to answer for it. |

Collapsing these produces a record that cannot answer the question anybody
actually asks afterwards: *what happened, and what was done about it?*

**Source for the distinction:** NICE NG5 (*Medicines optimisation*) and NICE SC1
(*Managing medicines in care homes*) both require that the reason a medicine was
not administered is recorded, not merely that it was not administered. CQC's
medicines guidance for adult social care treats an unexplained gap in a MAR
record as a different and more serious finding than a documented refusal.

---

## 2. Refusal, and re-offer

A refusal is not the end of the matter. A person may decline at eight and accept
at nine, and the record has to be able to say both.

**The model.** A re-offer is a **new, append-only row** that points at the
refusal it follows, through `reoffer_of_administration_id`. It is *not* a
correction. Nothing about the refusal changes — not its outcome, not its actor,
not its time. Corrections (Section 2.7) answer "we recorded the wrong thing".
Re-offers answer "she said no, and later she said yes". Conflating them would
turn a person changing their mind into a staff mistake.

**What closes a refusal.** Only an accepted re-offer of the *same planned dose*.

This corrected a real defect found in Section 1.2 during this work: the previous
rule closed a refusal as soon as *any* later dose of the same prescription was
given. For a medicine taken four times a day, the teatime tablet silently
answered for the morning refusal — so a person nobody went back to dropped off
the chase list because somebody gave them a different dose later. That is
recorded in the development log as a Section 1.2 regression fix.

**Source:** NICE SC1 recommends that care home residents' refusals are recorded
and that repeated refusal is escalated for review rather than normalised. The
"same obligation" rule is Record7's own engineering decision, not a regulatory
requirement.

---

## 3. Missed doses, and why lateness never becomes one automatically

A late dose is still an outstanding obligation. It becomes `missed` only when a
human says so.

**Nothing in Record7 converts lateness to a missed outcome.** There is no
scheduled job, no threshold, no background process. A dose can be nine hours late
and it stays outstanding until a person records what happened. This is tested
explicitly.

The reason is simple: a system that manufactures clinical outcomes creates
records nobody made and nobody can account for. "Missed" is an admission with
consequences, and only a person can make it.

Recording `missed` therefore requires four things together:

- a structured reason (`round_not_completed`, `overlooked`, `staffing_shortfall`, `discovered_later`)
- a written explanation in the worker's own words
- what they did about it
- who they told (`manager_notified` through to `emergency_services_contacted`)

**Not every missed dose is a safeguarding matter,** and Record7 does not label
them as such. `no_escalation_required_under_policy` is a legitimate answer where
the organisation's own policy says so.

**Source:** NHS Specialist Pharmacy Service guidance on medication incidents, and
CQC's expectation that providers record, investigate and learn from medicines
errors. Whether a given missed dose is reportable is a matter for the provider's
policy and the clinical circumstances — Record7 records the facts and does not
make that judgement.

---

## 4. Person unavailable

Kept strictly apart from medicine unavailable. Section 2.1 established that a
worker must never read "not available" and have to wonder which was missing.

**A client status never creates an outcome.** Callum's record says he is in
hospital. The screen still will not choose "person unavailable" for the worker,
and nothing preselects it. A status is a fact about where somebody is; an outcome
is a statement about what a worker did, and only a person can make that
statement. This is tested.

`not_found_in_service` is treated differently from routine absence. Somebody who
cannot be found is a welfare concern, and it raises an urgent attention item
through the existing issue lifecycle. **It is not automatically labelled a
safeguarding incident** — that determination belongs to a manager and the
provider's safeguarding policy.

**Limitation, stated plainly:** Record7 holds no care plan, so the suggested
action is generic. It cannot say what *this* person's plan requires.

---

## 5. Self-administration

`self_administration_monitoring` distinguishes two genuinely different
arrangements for a self-administered medicine:

- **`none`** — the person fully manages this medicine themselves. No individual
  staff dose record is required, and the dose does not sit outstanding on a round.
- **`check_and_record`** — staff have a documented responsibility to check and
  record.

**These are Record7 technical values. They are not regulatory terminology and
must not be quoted as such.**

**In Section 2.3 the field is read-only.** It is set by migration and fixture
only. There is no route, no endpoint and no UI that can change it. That is
deliberate: changing whether staff must monitor somebody's medicines is a
care-planning decision, and Record7 has no authority model for it yet. Building
one here would have meant inventing clinical authority, which this section
explicitly must not do.

**This is an interim medication-support-plan field** pending the fuller care-plan
and risk-assessment model.

**Source:** CQC guidance on care homes accepts that individual doses need not be
recorded where a person manages their own medicines, *provided* this is
established through their care plan and risk assessment. Record7 currently has
neither, which is precisely why the field is not editable in-product yet.

---

## 6. Medicine unavailable, and the stock boundary

Recording that a medicine was not available **changes no stock quantity**. Not
one. Stock effects are Section 2.7, and a section that silently adjusted counts
would make the stock record untrustworthy in exactly the situation where it
matters most.

This is tested across refusal, medicine-unavailable and person-unavailable.

---

## 7. Controlled drugs

A controlled drug can be *recorded as not given* here, but only after an explicit
declaration that **no quantity was removed from secure storage**.

If any quantity was removed, the medicine is accountable and this path is closed.
Section 2.5 owns witnessing, register entries and balances. Section 2.3 will not
write a placeholder, a stock event, a witness record or a register entry, and it
does not pretend to route into a workflow that does not exist yet.

The ordinary "Given" path remains closed to controlled drugs entirely.

**Source:** Misuse of Drugs Regulations 2001 and CQC guidance on controlled drugs
in adult social care, both of which require a second signature and a register
entry for administration of Schedule 2 controlled drugs.

---

## 8. What is deliberately not built

| Not built | Owner |
|---|---|
| `withheld` | Blocked. Record7 has no permission expressing clinical authority to withhold, and manager-dashboard access is not clinical authority. |
| PRN administration and effectiveness | Section 2.4 |
| Controlled-drug witnessing, register, balances | Section 2.5 |
| Corrections to a recorded outcome | Section 2.7 |
| Stock effects | Section 2.7 |
| Care plan and risk assessment | Not yet designed |

---

## 9. Sources

Consult the current published versions; these were the applicable documents at
the time of writing and guidance is revised.

- NICE NG5 — *Medicines optimisation: safe and effective use of medicines*
- NICE SC1 — *Managing medicines in care homes*
- NICE NG67 — *Managing medicines for adults receiving social care in the community*
- CQC — medicines guidance for adult social care providers, including MAR record
  and controlled drugs guidance
- NHS Specialist Pharmacy Service — medication incident reporting and learning
- Misuse of Drugs Regulations 2001, and the Human Medicines Regulations 2012

**None of these are cited in executable code.** Where a Record7 enum value
resembles a phrase from guidance, that is convenience of language, not a claim of
regulatory equivalence.
