# Care One OS — Definition of Done (per page)

A page may be marked **Ready** only when **all** gates below pass and **no Critical finding is open**. The `care-one-orchestrator` enforces this gate and will refuse to mark a page complete while any Critical remains.

## Finding severity
- **Critical** — unsafe, unlawful, insecure, data-losing, or breaks existing functionality. **Blocks Ready.**
- **Important** — real gap or defect; fix before broad rollout; may be time-boxed with owner + date.
- **Optional** — enhancement / polish.

## Gates (each owned by an agent)
1. **Care workflow & human factors** — realistic round conditions handled; no wrong-resident/double-submit traps. → `medication-workflow-specialist`
2. **Medication safety (clinical risk)** — hazards logged, controls in place, residual risk acceptable *provisionally* (CSO sign-off flagged). → `clinical-safety-reviewer`
3. **Regulatory (UK health & social care)** — requirements identified with source + force; evidence recorded; human-review points flagged. → `uk-compliance-reviewer`
4. **dm+d & interoperability** — medication data dm+d-ready; GP Connect designed behind mock/flag with reconciliation + provenance. → `dm-d-terminology-specialist` (+ `gp-connect-integration-specialist` when relevant)
5. **Information governance** — lawful basis, minimisation, access logging, retention, no unlogged exports. → `information-governance-reviewer`
6. **Security & permissions** — server-side authz, least privilege, tenant separation, dup-request protection, audit. → `security-and-permissions-reviewer`
7. **UI & mobile** — design-system compliant, responsive phone→desktop, no colour-only status, resident identity persistent. → `healthcare-ui-designer` + `responsive-accessibility-reviewer`
8. **Accessibility** — WCAG 2.2 AA: keyboard, focus, labels, contrast (both themes), announced errors. → `responsive-accessibility-reviewer`
9. **Testing & evidence** — success/validation/role/duplicate/empty/loading/error/offline/mobile/keyboard/edge cases; each test traces to a requirement or hazard. → `healthcare-qa-reviewer`
10. **Traceability** — [TRACEABILITY-MATRIX.md](TRACEABILITY-MATRIX.md) rows complete; [SOURCE-REGISTER.md](SOURCE-REGISTER.md) sources verified with version + access date; [CLINICAL-HAZARD-LOG.md](CLINICAL-HAZARD-LOG.md) updated. → `care-one-orchestrator`

## Preservation gate
Existing functionality preserved (nothing removed without explicit approval); only related files changed; existing components/styles reused where appropriate. Regression-checked.

## The claim boundary (always)
Passing these gates means the page has been **reviewed and improved by this workflow**. It does **not** mean Care One OS is compliant, certified, clinically safe, or NHS-approved. Those determinations require the named qualified humans and, where applicable, formal NHS assurance.
