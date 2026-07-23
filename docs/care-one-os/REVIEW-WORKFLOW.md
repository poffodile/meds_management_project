# Care One OS — agent review workflow

How a page moves from request to Ready. Driven by `care-one-orchestrator`, which invokes the specialist agents in `.claude/agents/` and records outputs in the shared registry.

## Roles at a glance
- **Orchestrator** — `care-one-orchestrator`: runs the process, combines findings, resolves conflicts, maintains traceability, enforces the completion gate, prevents file-edit collisions.
- **Research** — `healthcare-researcher`: finds current official requirements + professional design inspiration; updates the Source Register.
- **Requirements & risk** — `medication-workflow-specialist`, `clinical-safety-reviewer`.
- **Domain specialists** — `uk-compliance-reviewer`, `dm-d-terminology-specialist`, `gp-connect-integration-specialist`, `barcode-and-medication-identification-specialist`, `information-governance-reviewer`, `security-and-permissions-reviewer`.
- **Design** — `healthcare-ui-designer`, `responsive-accessibility-reviewer`.
- **Build** — `frontend-implementer`, `backend-implementer`.
- **Verify** — `healthcare-qa-reviewer` (+ the reviewers re-check).

## The process (orchestrator)
1. **Understand** the page request — who uses it, what they must accomplish, safety-critical actions.
2. **Inspect** existing code — frontend, backend, routes, DB structure, permissions, reusable components. Record what to preserve/reuse/improve/replace. **Never** assume; read the code.
3. **Research** — ask `healthcare-researcher` for current official requirements (→ Source Register) and appropriate, non-infringing professional design inspiration.
4. **Define requirements & risks** — ask `medication-workflow-specialist` (workflow/human-factors requirements) and `clinical-safety-reviewer` (hazards → Clinical Hazard Log). Pull in domain specialists as the page warrants.
5. **Design** — ask `healthcare-ui-designer` for an original, responsive, design-system-compliant layout; `responsive-accessibility-reviewer` sanity-checks the direction.
6. **Build** — ask `backend-implementer` and/or `frontend-implementer` to implement, preserving existing functionality. **File-lock rule:** the orchestrator assigns disjoint file sets; **no two implementers edit the same file at the same time**. Backend contract is agreed before frontend consumes it. Prefer git worktree isolation if parallel edits are unavoidable.
7. **Review** — ask security, accessibility, compliance, dm+d/interoperability, IG and QA agents to review the built page.
8. **Combine** — produce **one** report grouped into **Critical / Important / Optional**, each finding traced to a requirement/hazard/standard with evidence (file:line) and the responsible human-review role where relevant.
9. **Gate** — update the Traceability Matrix. **Refuse to mark the page complete while any Critical finding is open.** Record which standards/versions were checked.

## Conflict resolution
When agents disagree, the orchestrator prefers, in order: (1) safety/legal requirement over convenience, (2) the more specific current official source over general guidance, (3) preserving existing functionality over speculative redesign. It records the decision and rationale, and flags genuinely clinical/legal trade-offs for a qualified human rather than deciding them.

## Output contract
Every agent returns findings in its defined format. The orchestrator's combined report always ends with the **claim boundary**: this workflow reviews and improves pages; it does not make the product compliant, certified, clinically safe or NHS-approved.
