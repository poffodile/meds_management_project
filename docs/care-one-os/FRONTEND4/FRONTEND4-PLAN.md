# Frontend4 — plan & isolation rules

The rules for the fourth parallel front end. Read this before touching anything
under `frontend4/`, `resources/js/F4Pages/` or `app/Http/Controllers/Frontend4/`.

## What frontend4 is

A fourth front end running **beside** frontends 1, 2 and 3 inside the same
Laravel application. It is standalone in every way that matters visually, and
shared in the one way that matters functionally:

- **Same database.** Same `.env`, same connection, same tables, same Eloquent
  models. A medication recorded in frontend4 is the same row frontend1 sees.
  There is no second database, no copy, no sync.
- **Same auth.** Same login, same session, same `user_type` / `access_levels`
  role model. Frontend4 does not get its own user table or its own login page.
- **Its own everything else.** Own root Blade view, own Vite entry, own page
  directory, own tokens, own stylesheet, own look.

## How you reach it

The "Frontend 4" button in the old Blade header
(`resources/views/frontEnd/common/header.blade.php`, indigo `#3F4FD6`), or
`/frontend4` directly. Login is unchanged; the post-login redirect is untouched.

## Where it lives

| Piece | Path |
| --- | --- |
| Route | `routes/web.php` → `/frontend4` (`frontend4.home`) |
| Controllers | `app/Http/Controllers/Frontend4/` (all extend `F4Controller`) |
| Root view | `resources/views/f4.blade.php` |
| Vite entry | `resources/js/f4.jsx` (registered in `vite.config.js`) |
| Pages | `resources/js/F4Pages/` |
| Components | `frontend4/components/` (alias `@frontend4/...`) |
| Tokens | `frontend4/tokens.js` |
| Styles | `frontend4/f4.css` — **the only stylesheet frontend4 loads** |

## THE ISOLATION RULE (the important one) — CSS must not leak

**Nothing frontend4 does may change how any other page looks.** Frontends 1, 2
and 3 must render byte-identically whether frontend4 exists or not.

That is enforced by four things, all of which must stay true:

1. **Every CSS rule is scoped under `.f4-root`.** No bare `body`, `h1`, `a`,
   `.btn`, `*`, `:root`. Even the reset is written as `.f4-root *`. The only
   top-level construct allowed is an `@media` block whose inner rules are all
   `.f4-root`-scoped. If a rule cannot be written under `.f4-root`, stop — that
   is the signal you are about to leak into a global stylesheet.
2. **Frontend4 loads no global CSS at all** — not `resources/css/app.css`, not a
   Mantine/component-library stylesheet, not `frontend3/f3.css`. Its own file is
   the whole of it. This also means nothing can reach *in*.
3. **Separate bundle.** `resources/js/f4.jsx` is its own Vite entry and resolves
   pages only from `./F4Pages/**`. It cannot pull in another front end's screens
   or styles, and they cannot pull in its.
4. **Separate root view.** Every frontend4 action calls `$this->useF4Layout()`
   as its first line (see `F4Controller` for why it must be in the action and
   not the constructor). That swaps Inertia's root view to `f4`.

**Do not edit** `resources/css/app.css`, `resources/views/app.blade.php`,
`frontend/`, `frontend2/`, `frontend3/`, `resources/js/app.jsx`,
`resources/js/f3.jsx`, or `app/Http/Kernel.php` in the course of frontend4 work.
Adding the header button and the route/entry lines listed above is the entire
footprint frontend4 has outside its own folders.

Shared files frontend4 *may* append to, never restructure: `routes/web.php`
(its own block), `vite.config.js` (its entry + alias).

## The "same database" rule

Frontend4 controllers read and write the **existing** tables through the
**existing** models. Do not create frontend4-only tables, do not duplicate
clinical data, and do not add a migration to reshape a table just to suit a
frontend4 screen. If a screen needs data the schema does not hold, raise it
before building — a schema change affects all four front ends and the clinical
record is append-only.

## Where the record is kept

- `FRONTEND4-PLAN.md` (this file) — the rules.
- `FRONTEND4.md` — the running work log **and** the verbatim-substance record of
  each session's conversation. Both parts get updated every session.
