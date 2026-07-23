---
description: Produce an original, responsive, design-system-compliant layout + component plan for a Care One OS page (no code yet).
argument-hint: <page name, e.g. "Medication Round">
---

Design the **$ARGUMENTS** page. Do not implement code in this step.

1. Read `docs/care-one-os/DESIGN-SYSTEM.md`, `docs/design-system.md`, `docs/brand-guidelines.md`, `frontend/tokens.js`, and inspect the existing page + a finished sibling (e.g. Stock 2).
2. If requirements/risks aren't yet defined, first run the **medication-workflow-specialist** and **clinical-safety-reviewer** (and **healthcare-researcher** for inspiration).
3. Use the **healthcare-ui-designer** agent to produce: a mobile layout and a desktop layout (wireframe + reasoning), the component list (reused vs new), the interaction flow, and the built-in safety controls (persistent resident identity; status via label+icon+colour+SR text; prominent allergies/warnings; confirmation on safety-critical actions; loading/empty/error/offline/success states).
4. Have the **responsive-accessibility-reviewer** sanity-check the direction.

Return the design direction, both layouts, the component plan, safety/accessibility notes, and any question that would materially change the medication workflow (flag for human decision). Preserve existing functionality — redesign presentation, not capability.
