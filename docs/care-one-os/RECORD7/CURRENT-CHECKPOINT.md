# Record7 Current Checkpoint

**Checkpoint date:** 8 September 2026  
**Branch:** `record7-section0-access`  
**Remote HEAD at checkpoint:** `c8c6dcb4407a115ce2c91259b6ea3003db7dce29`

This file is the current Record7 continuation point. It exists because implementation has moved materially beyond several older handover, `RESUME-HERE`, section-status and development-log notes.

When this checkpoint conflicts with an older Record7 handover or historical planning note, inspect the current branch code, schema and tests first. Do not treat an older document as proof that implemented work is absent.

## 1. Current implementation position

The current Record7 branch contains implementation for:

- Section 0 — access/login foundation
- Section 1.1 — Support Worker Today
- Section 1.2 — Manager Today
- Section 2.0 — medication-round foundation
- Section 2.1 — person and medicine safety view
- Section 2.2 — scheduled administration
- Section 2.3 — non-administration, refusal and same-dose re-offer
- Section 2.4 — PRN
- Section 2.5 — controlled drugs
- Section 2.6 — round close/reopen lifecycle
- Section 2.7 — stock, reconciliation, audit and corrections
- post-2.7 medication navigation/handoffs
- worker-facing administration correction requests

This means Section 2.3 is no longer a design-only section and Sections 2.5-2.7 are no longer awaiting implementation.

## 2. Clinical-record rules that remain non-negotiable

- Clinical facts and history are preserved.
- Administration records are append-only; a later correction does not rewrite or delete the original.
- Tenant/service/organisation isolation must be enforced from trusted server-side context.
- A later unrelated `given` for the same prescription must never close an earlier refusal.
- A refusal is resolved by an accepted re-offer linked to the same scheduled dose, not by an unrelated later dose.
- Review/workflow status must not by itself erase an underlying clinical or safety condition.
- Ordinary medicine stock effects follow physical medicine movement; a clinical record must not be suppressed merely to protect a stock balance.
- No production care data is to be modified during the current numbered development build.

## 3. Known current code gap — `person_unavailable` corrections

Section 2.3 added `person_unavailable` as a valid administration outcome.

At this checkpoint, the correction workflow has not been fully extended for that new outcome:

- `app/Http/Controllers/Record7/CorrectionController.php` does not offer `person_unavailable` as a correction target.
- `app/Services/Record7/ManagerActions.php` does not accept `person_unavailable` in its correction allowlist.

Therefore a truthful correction from another recorded outcome to `person_unavailable` cannot currently complete through the normal request/approval path.

**Required correction:** extend both halves together and add regression evidence. Do not expose the option in the request UI unless the approval path accepts it.

## 4. Open design/safety decision — correction to `withheld`

The current correction request and approval paths allow `withheld` as a correction target.

However, the wider Record7 design deliberately defers ordinary `withheld` recording until the product has a defined clinical authority/instruction/evidence model. Manager access alone is not clinical authority.

This creates an unresolved design question: whether a correction may assert that a medicine was withheld without the same structured authority/evidence that would be required for an ordinary withheld record.

Do not silently treat this as settled. Before changing the behaviour, make an explicit owner/clinical-safety decision either to:

1. remove/block `withheld` as a correction target until the authority/evidence model exists; or
2. define and enforce the evidence/authority needed for a correction to `withheld`.

## 5. Controlled-drug wording requiring documentation reconciliation

Older Section 2.3 wording is broader than the evidence later captured in the Record7 source register and Section 2.5 design.

The current product design is care-setting aware. It must not state or imply that a second witness/signature or controlled-drug register is a universal legal requirement in every supported-living or person's-own-home setting.

A provider may impose a stricter policy, and Record7 may fail safe where policy/configuration is unknown, but regulatory statements in documentation must distinguish legal requirements from provider policy and good practice.

## 6. Verification status

The repository contains substantial Record7 feature tests, including later manager/correction journey coverage.

At this checkpoint there is **no attached GitHub CI/status evidence proving that the current HEAD has passed the complete PHP, JavaScript and production-build suite**.

Remote repository inspection also cannot prove:

- whether a developer workstation has uncommitted/unpushed changes;
- whether its local branch and HEAD match this checkpoint;
- which migrations are currently applied to its local Record7 database;
- whether the full suite passes on that local environment.

Do not describe the current HEAD as fully green, release-ready, clinically approved or production-ready until those checks are completed and recorded.

## 7. Production sign-in blocker

Record7 production MFA is still outstanding.

Policy/challenge logic exists, but a real production verification method such as TOTP, passkey/WebAuthn or another approved method has not yet been integrated. Development/prototype verification is not sufficient for production sign-in.

MFA therefore remains an explicit production blocker.

## 8. Documentation currently known to be stale

Older documents must be read as historical where they conflict with this checkpoint/current branch. In particular:

- `docs/care-one-os/RECORD7/RESUME-HERE.md`
- Section 2.5 status text that says implementation is awaiting approval
- Section 2.6 status text that says implementation is awaiting approval
- Section 2.7 text that says nothing is implemented
- `docs/DEVELOPMENT-LOG.md`, which does not yet describe all later 2.5-2.7 and post-2.7 work
- root branch instructions that still present `care-one-integration` as the only/current development branch without a Record7-specific override

Do not delete historical documentation solely because it is stale; update status wording or clearly mark it superseded so useful design rationale is retained.

## 9. Definition-of-done evidence still to reconcile

Implementation alone is not enough to mark Record7 release-ready.

Before a formal Ready/production claim, bring the evidence set in line with the implemented 2.x system, including:

- Record7 traceability coverage for the implemented 2.x requirements;
- the Record7 clinical hazard/safety record;
- source-register citations and regulatory wording;
- accessibility/mobile/light-dark UI verification where applicable;
- current full test/build evidence;
- security/MFA completion and review.

## 10. Immediate work order

Do not invent a new Section 3 yet.

Proceed in this order:

1. Confirm local working-tree/branch/HEAD and migration status when a repository workspace is available.
2. Run the complete current test/build baseline and record exact results.
3. Correct `person_unavailable` support across the whole administration-correction workflow and add regressions.
4. Resolve the `withheld` correction authority/evidence decision explicitly.
5. Re-run relevant Section 1.2 and Sections 2.3-2.7 regressions.
6. Browser-walk the connected medication journeys on desktop/mobile and light/dark modes.
7. Reconcile stale section-status, resume and development-log documentation.
8. Update traceability and clinical-safety/hazard evidence to the actual Record7 implementation.
9. Keep MFA visible as a production blocker.
10. Only after the verified 2.x baseline is established, approve the next numbered scope.

---

**Checkpoint principle:** current code/schema/tests establish what exists; documentation establishes intended rules and evidence; neither may silently override a clinical-safety decision that remains unresolved.
