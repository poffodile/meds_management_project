---
description: QA-verify a Care One OS page end-to-end and produce traceable test cases (success, validation, roles, duplicates, states, mobile, keyboard, safety edges).
argument-hint: <page name, e.g. "Medication Round">
---

Use the **healthcare-qa-reviewer** agent to verify and test the **$ARGUMENTS** page.

1. Read `docs/care-one-os/MEDICATION-WORKFLOW.md`, `CLINICAL-HAZARD-LOG.md`, `TRACEABILITY-MATRIX.md`, `DEFINITION-OF-DONE.md`.
2. **Inventory every control** and trace each end-to-end (onClick/onSubmit → handler → Inertia route → controller → model/DB). Flag dead controls, silent failures, missing validation, missing loading/disabled states, stale-state bugs, client/server mismatches.
3. **Design tests** covering: successful workflow; validation failures; **different roles/permissions**; **duplicate/double submission**; empty/loading/error/**offline** states; **mobile/tablet** layout; **keyboard** use; long content; and **safety-critical edge cases** (wrong resident, overdue, CD without witness, unreconciled GP data, missed-dose escalation). Each test cites the **Req/Hazard ID** it covers.
4. **Verify the backend** with a **rolled-back** smoke test where it meaningfully proves a handler (Laragon PHP; temp script at project root; `DB::beginTransaction()`/`rollBack()`; no `users` table — use an existing row's id; delete the script after). Run the build to confirm it compiles.

Return: control inventory with verdicts, defects graded **Critical/Important/Optional** with evidence + repro + fix, the numbered test set (traced to requirements/hazards, marking code-traced vs smoke-tested vs needs-device), coverage gaps, and human-review items. Do not claim the page is verified-compliant/safe. Do not modify application files (temp rolled-back smoke script excepted; delete it).
