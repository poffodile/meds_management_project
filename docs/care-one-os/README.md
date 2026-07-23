# Care One OS — development & review system

This folder is the **single source of truth** for how Care One OS pages are designed, built and reviewed. The multi-agent workflow in `.claude/agents/` and the slash commands in `.claude/commands/` all read from these documents. Keep them current — an agent is only as good as the registry it cites.

## Contents

| Doc | Purpose |
|---|---|
| [PRODUCT-CONTEXT.md](PRODUCT-CONTEXT.md) | What Care One OS is, who uses it, the tech stack, and the non-negotiable rules. |
| [STANDARDS-REGISTER.md](STANDARDS-REGISTER.md) | Every UK legal / NHS / assurance / good-practice standard we review against, classified by force. |
| [SOURCE-REGISTER.md](SOURCE-REGISTER.md) | The actual official documents cited, with **version and access date**. Researchers append here. |
| [DESIGN-SYSTEM.md](DESIGN-SYSTEM.md) | Visual + interaction rules for every page (extends `docs/design-system.md` and `docs/brand-guidelines.md`). |
| [MEDICATION-WORKFLOW.md](MEDICATION-WORKFLOW.md) | Functional requirements for medication rounds, outcomes, PRN, controlled drugs, reconciliation and scanning. |
| [CLINICAL-HAZARD-LOG.md](CLINICAL-HAZARD-LOG.md) | DCB0129-style hazard log: hazards, risk ratings, controls, residual risk, owners. |
| [TRACEABILITY-MATRIX.md](TRACEABILITY-MATRIX.md) | Requirement → source → page/feature → implementation → test → status. |
| [DEFINITION-OF-DONE.md](DEFINITION-OF-DONE.md) | The gate a page must pass before it can be called "ready". |
| [REVIEW-WORKFLOW.md](REVIEW-WORKFLOW.md) | The orchestrated per-page process and how findings are combined and graded. |

## The one rule that overrides everything

> An AI review **does not** make Care One OS compliant, certified, clinically safe or NHS-approved. Every agent identifies the *exact* requirement and its *official source*, provides evidence, and flags anything that needs a qualified human (Clinical Safety Officer, Data Protection Officer, pharmacist, medication lead, care professional, security specialist, or an NHS assurance body).

## How to use it

- Build/review a page: run `/care-one-build-page <page>` or `/care-one-review-page <page>` — these invoke the orchestrator, which drives the agents in `.claude/agents/`.
- Research a requirement: `/care-one-research <topic>` (updates the Source Register).
- Never mark a page done while any **Critical** finding is open (see Definition of Done).
