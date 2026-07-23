---
description: Run the full orchestrated Care One OS cycle to build/improve a page — inspect, research, define requirements & risks, design, implement, review, and gate.
argument-hint: <page name, e.g. "Medication Round">
---

Use the **care-one-orchestrator** agent to build/improve the **$ARGUMENTS** page, following `docs/care-one-os/REVIEW-WORKFLOW.md` and enforcing `docs/care-one-os/DEFINITION-OF-DONE.md`.

Steps (orchestrator drives):
1. **Understand** — users, goals, safety-critical actions.
2. **Inspect** existing frontend, backend, routes, DB, permissions, reusable components; record preserve/reuse/improve/replace.
3. **Research** via healthcare-researcher (current official requirements + inspiration; Source Register updated).
4. **Requirements & risks** via medication-workflow-specialist + clinical-safety-reviewer (+ dm+d, GP Connect, barcode, IG, security specialists as relevant).
5. **Design** via healthcare-ui-designer (checked by responsive-accessibility-reviewer).
6. **Implement** via backend-implementer and/or frontend-implementer — **assign disjoint file sets; no two implementers edit the same file at once**; agree the data contract first; preserve existing functionality.
7. **Review** via security, accessibility, compliance, dm+d/interoperability, IG and QA agents.
8. **Combine** into ONE report: Critical / Important / Optional, each traced to a requirement/hazard/standard with evidence (file:line) and human-review role.
9. **Gate** — update the Traceability Matrix; record standards/versions checked; **do NOT mark the page complete while any Critical remains**.

End with the claim boundary: this workflow reviews and improves the page; it does not make Care One OS compliant, certified, clinically safe or NHS-approved.
