# Frontend — where the new UI lives

The modern web UI is React + Inertia + Mantine. **Shared components live in the top-level `frontend/` folder**; the Inertia entry + pages stay in the Laravel-standard `resources/js/`.

```
frontend/                       ← 🟢 shared component library (import via '@frontend/...')
└── components/
    └── StatCard.jsx            ← example shared component (used by the pilot page)
   (later) Layouts/, hooks/, theme/ — design system grows here

resources/js/
├── app.jsx                     ← entry point: Inertia + Mantine setup, theme (standard)
└── Pages/                      ← ONE file per screen, Inertia auto-loads these (standard)
    └── Medication/
        └── Stock.jsx           ← the pilot page (live at /medication/stock-react)

resources/views/app.blade.php   ← the HTML shell Inertia mounts React into
vite.config.js                  ← build config (React + Laravel + '@frontend' alias)
postcss.config.cjs              ← Mantine PostCSS config
```

**Import shared components with the `@frontend` alias**, e.g. `import StatCard from '@frontend/components/StatCard';`

## Why it's here and not a top-level `/frontend` folder
We chose **Inertia**, which means **Laravel serves the React app** — so by convention the frontend lives *inside* the Laravel project at `resources/js/`. Keeping it there is what every Laravel/Inertia developer will expect, and it keeps the web frontend and backend in one repo, deploying together.

The **truly separate** frontends get their own home when we build them:
- the **React Native** carers' app (Phase 4) → its own project/repo
- any future fully-standalone web SPA → its own project/repo

If you'd rather the web frontend physically sit in a top-level `frontend/` folder anyway (purely for your own findability), it's a small config change — just say the word and I'll move it.

## How to run it (dev)
1. Start the app: `start-local.bat` (or `php -S 127.0.0.1:8000 serve-local.php` from the project root).
2. Start Vite: `npm run dev`.
3. Open a migrated page, e.g. **http://127.0.0.1:8000/medication/stock-react** (after login).

See [`docs/ui-modernization-plan.md`](./docs/ui-modernization-plan.md) for the full step-by-step plan.
