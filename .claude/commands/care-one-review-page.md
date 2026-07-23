---
description: Review an existing Care One OS page across all dimensions and return one combined Critical/Important/Optional report (no code changes).
argument-hint: <page name, e.g. "Medication Round">
---

Use the **care-one-orchestrator** agent to review (not rebuild) the **$ARGUMENTS** page.

1. **Inspect** the real frontend + backend + routes + DB + permissions for this page.
2. Report **what currently works** and **what's missing** against `docs/care-one-os/MEDICATION-WORKFLOW.md` and `DEFINITION-OF-DONE.md`.
3. Run the review agents and combine their findings:
   - **uk-compliance-reviewer** — regulatory gaps (source + force + evidence).
   - **clinical-safety-reviewer** — hazards, controls, residual risk (update the hazard log).
   - **medication-workflow-specialist** — workflow/human-factors issues.
   - **dm-d-terminology-specialist** (+ **gp-connect-integration-specialist** / **barcode-and-medication-identification-specialist** if relevant) — interoperability.
   - **information-governance-reviewer** — privacy/IG.
   - **security-and-permissions-reviewer** — authz/tenant/OWASP.
   - **responsive-accessibility-reviewer** (+ **healthcare-ui-designer** notes) — mobile + WCAG 2.2 AA.
   - **healthcare-qa-reviewer** — functional defects + test gaps.
4. Return ONE report grouped **Critical / Important / Optional**, each finding traced to a requirement/hazard/standard with evidence (file:line) and any human-review role. Update the Traceability Matrix. List open Criticals that block "Ready".

Do not change code in this command. End with the claim boundary.
