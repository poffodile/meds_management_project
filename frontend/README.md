# frontend/ — shared React component library

All reusable UI components for the web app live here. Import them anywhere using the
**`@frontend`** alias (configured in `vite.config.js`):

```jsx
import StatCard from '@frontend/components/StatCard';
```

## Structure
```
frontend/
└── components/   ← reusable Mantine-based components (StatCard, DataTable, PageHeader, modals…)
   (later)        hooks/, lib/, theme/  — add as the design system grows
```

## What lives here vs. the standard Laravel/Inertia spots
- **Here (`frontend/`)** → shared components (and later hooks/theme) — the stuff you'll reuse everywhere.
- **`resources/js/app.jsx`** → the Inertia entry point (left in the Laravel-standard place).
- **`resources/js/Pages/`** → one file per screen, auto-loaded by Inertia (Laravel-standard).

So: *pages* stay standard; *components* live in this clearly-named folder.
