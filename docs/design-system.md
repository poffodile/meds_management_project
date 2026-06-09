# Care One OS — Frontend Design System

> The reusable foundation for the modern (Inertia + React + Mantine) UI. Build pages from these tokens, components, and hooks — **don't hardcode colours or copy-paste markup.** Add a thing here once and every page gets it.
> Last updated: 2026-06-09

## Where things live
```
frontend/
  tokens.js            # design tokens — the single source of truth (colours, type, spacing)
  theme.js             # Mantine theme, built from tokens (entry: @frontend/theme)
  lib/                 # pure helpers (dateUtils, avatarColor, medicationCodes, role)
  hooks/               # reusable hooks (useFlash, usePageReload)
  components/          # generic, app-wide UI components
  features/
    medications/       # medication-specific composites + modals
  Layouts/AppShell.jsx # the app shell (sidebar + header)
```
Import alias: **`@frontend` → `frontend/`** (Vite + Vitest). Pages live in `resources/js/Pages/`.

---

## 1. Tokens (`frontend/tokens.js`)
The ONE place for visual constants. Consumed by `theme.js` and directly by components.

| Export | What it is |
|---|---|
| `brand` | `{ primary: 'indigo', navy: '#16223a' }` — primary colour + the dark sidebar logo band. |
| `statusColors` | Lowercased status → Mantine colour. Drives `<StatusBadge>`. Priority levels are namespaced `priority_low/medium/high/urgent` so they don't collide with stock `low`. |
| `roundTokens` | Per round (`morning/lunchtime/evening/night`) → `{ label, icon, color }`. |
| `avatarColors` | Palette for deterministic initials avatars. |
| `radius`, `typography` | Layout scale (card/control radius; font family + heading weight). |

**Rule:** need a new status colour? Add it to `statusColors`. Need a brand tweak / per-tenant theme? Change `brand` (white-label ready). Never inline a hex.

---

## 2. Generic components (`frontend/components/`)
| Component | Purpose | Key props |
|---|---|---|
| `StatusBadge` | Coloured status pill from `statusColors`. | `status, label, color?, variant?, size?` |
| `StatCard` | Dashboard metric tile. | `label, value, color, icon, sublabel` |
| `MetricChip` | Compact icon·label·value row (inline metrics). | `icon, label, value, color` |
| `PageHeader` | Title + subtitle + actions strip. | `title, subtitle, actions` |
| `DataTable` | Search + sort + paginate table. | `columns, data, searchable, pageSize, emptyMessage, minWidth` |
| `FormModal` | Modal wrapper with Cancel/Save. | `opened, onClose, title, onSubmit, submitting, submitLabel` |
| `ConfirmDialog` | "Are you sure?" prompt. | `opened, onClose, onConfirm, message, confirmLabel, confirmColor, loading` |
| `FlashAlerts` | Renders Laravel `{success,error}` flash. Drop-in for the old copy-pasted block. | `mb?, radius?` (+ Alert props) |
| `AlertItem` | Tinted alert row (icon + title + desc + chevron). | `severity('danger'|'warning'|'info'|'success'), icon, title, description, href|onClick` |
| `QuickActionItem` | Icon + label + description action row. | `icon, label, description, href|onClick, color, disabled` |
| `RiskFlag` | Risk pill coloured by severity (uses `priority_*`). | `label, level('low'|'medium'|'high'|'urgent')` |
| `RoundProgressDonut` | Segmented ring + legend (Completed/Due Soon/Overdue/Not Started). | `completed, dueSoon, overdue, notStarted` |

Each has a Vitest test alongside it (`*.test.jsx`). Run `npm run test`.

## 3. Medication composites (`frontend/features/medications/`)
| Component | Purpose |
|---|---|
| `ResidentCard` | Selected resident's clinical header (photo, DOB/age/gender, weight, allergies, risk strip, MetricChips). Renders fields only when present. |
| `ResidentListItem` | Compact resident row (avatar, name, room, round-status pill, selected state). |
| `MedicationCard` | One medication: name+strength, tags, dose·route·instruction, time+status, stock, CD flag, and Administer/Refused/Omitted actions (or recorded badge). |
| Modals | `RecordDoseModal` (now accepts `presetCode`), `AdjustStockModal`, `ResolveDoseModal`, `AddCdEntryModal`, `AddHandoverModal`. |

## 4. Hooks & utils
- `lib/dateUtils.js` — `pad`, `formatDate(iso)` → "DD Mon YYYY", `ageFromDob(iso)`, `MONTHS`.
- `lib/avatarColor.js` — `avatarColor(name)`, `initials(name)`.
- `lib/medicationCodes.js` — `MED_CODES` (A/S/R/W/N/O) + `CODE_LABELS`. Must match the controller's `code` validation.
- `lib/role.js` — `RoleContext` / `useRole()` → `'manager' | 'carer'`.
- `hooks/useFlash.js` — read the flash bag (backs `FlashAlerts`).
- `hooks/usePageReload.js` — `usePageReload(endpoint)` → `reload(params)` (preserves scroll/state).

---

## 5. Medication Round — data map (Phase A/B/C)
The page is built layout-first; data is wired in phases. Source: `MedicationRoundController@indexReact`.

**Phase A — wired (real):** resident name, photo, DOB→age, allergies, regular/PRN counts; per-med name, strength, dose, route, instruction, time, stock, low-stock, controlled-drug + schedule, Regular/PRN tag, recorded code.

**Phase B — wired (derived):** per-dose timing bucket (`status`: completed/overdue/due_now/upcoming/later via `doseBucket()`), round-progress buckets, overdue-aware resident status, the Upcoming-next-2h split, overdue/low-stock/CD alerts, gender, weight, generic risk flags (from `care_plan_risks`, surfaced with their `impact` level).

**Phase C — placeholders (not in DB / out of scope):** NHS number (no column), room label (no room lookup table), medication form, therapeutic class ("Pain Relief"), conditions list, PRN last-given/next-available, care-plan link. The Scan / Add-PRN / Temporary-Absence / MAR-report quick actions are stubbed (`disabled`).

When wiring a Phase C item later: add it to the controller payload, then surface it in the relevant component — the slot already exists in the UI.

---

## Conventions
- Components are documented with a header JSDoc comment (see `StatCard.jsx`, `DataTable.jsx`).
- Generic (app-wide / mobile-portable) components go in `components/`; domain composites go in `features/<domain>/`.
- New generic components ship with a Vitest test.
- Prefer extending a token/component over adding a new one; prefer a shared hook over repeating a fetch/flash pattern.
