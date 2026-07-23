# Care One OS — design-system rules

Extends the existing `docs/design-system.md`, `docs/brand-guidelines.md`, `docs/COLOUR-PALETTE.md` and the code tokens in `frontend/tokens.js` (`palette`, `statusPalette`, `brand`) and atoms in `frontend/components/*`. **Reuse those first**; only add a token/atom when nothing fits, and add it to the shared source, not inline.

## Visual character
Professional, calm, "expensive" — like a credible modern UK care product, not a generic AI dashboard. Restrained palette, muted hues, hairline borders, soft shadows, tabular numerals, lighter type weights. Theme-aware (light + dark) via `light-dark()`. NHS-adjacent clarity, not NHS branding.

## Hard rules (from the product owner)
- **No** excessive floating cards; don't put everything in separate boxes.
- **No** large empty space, oversized headings or oversized statistics.
- **Don't** stretch a small amount of information across the whole screen.
- **Don't** hide safety-critical information behind unnecessary clicks — use progressive disclosure only for *secondary* detail.
- **Don't** make mobile users interact with desktop-sized tables — collapse to cards.
- **Never** communicate a medication/clinical status by **colour alone** — always pair colour with a **text label + icon + screen-reader text**.
- Keep **resident identity visible** during safety-critical medication actions.
- Keep the **primary action** obvious without burying necessary secondary actions.
- Information-rich but **organised**; clean without hiding what matters.

## Required component set (build/reuse as shared)
Colour palette · typography · spacing scale · page layouts · navigation · buttons · forms · tables · mobile cards · **status badges** (label+icon+colour) · **resident identifier** (photo + name + DOB/NHS no.) · **medication card** (name, strength, form, dose, route, time, instructions) · **allergy / clinical-warning** banner · modals & confirmation screens · side panels/drawers · empty states · loading skeletons · error states · notifications/toasts · offline/sync indicator.

## Responsive behaviour
Mobile-first. Every element must reflow phone → tablet → laptop → desktop. Wide content scrolls inside its own container — the page body must never scroll horizontally. Touch targets ≥ ~44px for primary actions (≥24px minimum). No `zoom:` hacks (they break assistive zoom and desync breakpoints).

## States every page must handle
Loading (skeleton) · empty · error · **offline / sync pending** · success/confirmation. Safety-critical actions require explicit validation + confirmation, and must resist double submission.

## Accessibility baseline (see responsive-accessibility-reviewer)
WCAG 2.2 AA: keyboard-operable, visible focus, labelled forms, announced errors/toasts (`aria-live`), sufficient contrast in **both** themes, status not by colour alone, respects `prefers-reduced-motion`.
