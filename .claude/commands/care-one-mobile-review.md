---
description: Run a mobile-first responsiveness + WCAG 2.2 AA accessibility review of a Care One OS page.
argument-hint: <page name, e.g. "Medication Round">
---

Run a **mobile + accessibility** review of the **$ARGUMENTS** page using the **responsive-accessibility-reviewer** agent (with **healthcare-ui-designer** design-consistency notes).

1. Read `docs/care-one-os/DESIGN-SYSTEM.md` and inspect the page JSX + a finished sibling (Stock 2).
2. Responsiveness: every element reflows phone (≤576) → tablet → desktop; **tables collapse to cards** on mobile; wide content scrolls in its own container; **body never scrolls horizontally**; touch targets ≥ ~44px; flag `zoom:` hacks / fixed widths / fragile absolute positioning.
3. Accessibility (WCAG 2.2 AA, cite SC): keyboard operability + no traps; focus management for modals/drawers and after Inertia reloads; screen-reader semantics (aria-labels, `<th>`/scope, `aria-live` toasts, tablist/tab/tabpanel, labelled forms); contrast in **both** light and dark; **status never conveyed by colour alone** (label + icon + SR text); accessible error messages; usable at 200%/320px; respect `prefers-reduced-motion`.

Return the verdict (can a keyboard/screen-reader phone user complete the core task?), findings graded **Critical/Important/Optional** with evidence (file:line) and fixes, and the top priorities. Ground every finding in the real JSX. No code changes in this command.
