# Record7 — resume here

**Stopped:** 25 August 2026, mid-session, at Michael's request.
**Branch:** `record7-section0-access`. **Nothing committed, nothing pushed.**
**Preview:** `! C:\OmegaLife\record7-start.ps1` then http://127.0.0.1:8333/record7/login

Sign in with `testmanager` / `precious` or `teststaff` / `precious`, organisation
`omega care group`.

---

## Two things to change first — BOTH NOW DONE (26 August)

Kept below for the record. See "What changed on 26 August" at the foot.

### 1. The back arrow looks bad

The "← Back" control at the top left of the sign-in card. Michael's words: *"it's
too bad, it needs to be rounded and nice."*

Where it lives:

- `resources/css/record7/r7.css` — `.r7-root .r7-link.r7-back` (around line 851)
- `resources/js/record7/components/AuthShell.jsx` — the `back` prop renders it

Right now it is a text link with an arrow glyph rotated 180 degrees and no shape
of its own until hover. It should be a proper rounded control — most likely a
circle matching the theme toggle that sits opposite it on the same row, since
those two are the only things on that row and they should look like a pair.

Worth deciding at the same time: **icon only, or icon and the word "Back"?** The
theme toggle opposite it is icon-only, so a circular icon-only back would balance
it exactly.

### 2. The arrangement inside a house row

Michael's words: *"I don't like the arrangement of the name, the Manager access,
the Supported Living and all that."*

Where it lives:

- `resources/js/record7/components/HouseRow.jsx`
- `resources/css/record7/r7.css` — `.r7-house`, `.r7-house__name`,
  `.r7-house__meta`, `.r7-house__body > .r7-status`, `.r7-house__go`

It currently stacks three things down the left:

```
Oakwood House
Manager access                    →
Supported Living   Liverpool   Currently open
```

**Do not simply reshuffle it and ask.** Three arrangements have already been
tried this session and each was rejected, so the next attempt should be a real
alternative, not a fourth variation:

| Tried | Outcome |
|---|---|
| Badge and "Open →" in a column on the far right | Rejected — stretched the row to both edges, hole down the middle |
| Badge beside the name, pushed right | Rejected — fitted on desktop, wrapped on a phone, so the row changed shape between them |
| Badge under the name, small (what is there now) | Rejected — the arrangement itself |

Things worth putting to him as genuinely different options: dropping the access
badge from this screen altogether and stating it once on entry; putting the town
and type on one quiet line under the name with the access as plain text rather
than a pill; or a two-column tile per house rather than a full-width row.

---

## What is actually built and working

Section 0 — the login and access section — is complete and passing.

The journey: **organisation → username and password → house selection → Today.**
Walked end to end on this machine as `testmanager`.

Covered: organisation verification with case and space collapsing; credentials
with the password checked *before* account state, so a suspended account never
leaks that its password was right; MFA policy (off for testing, `246810` in test
mode, fails closed in production because no real provider is integrated); house
selection; five-layer authorisation; session and lock; credential lifecycle;
account states with the non-revealing message; append-only access audit; and the
manager audit screen.

**Tests: Record7 177, Frontend 4 64, JS 22 — all passing.**

---

## What changed today, in order

1. **Applied Michael's supplied `record7-login.css`** as guidance. Its best idea
   was a second accent: teal for brand and state, blue for interaction. Four of
   its colours failed WCAG AA and were substituted (`#367399`, `#656F7B`,
   `#748EA2`, `#8E897E`). Kept Sora and Outfit rather than its Plus Jakarta Sans.

2. **Three faults that change exposed**, all fixed and all now covered by tests:
   the primary button's arrow was invisible on the dark theme (teal on teal, now
   its own `--r7-icon-on-solid` token); one token was carrying both hairlines and
   the smallest text on navy (split into `-rule` and `-dim`); and on switch-house
   the current house and the hovered house were the same teal (hover is blue now).

3. **The form became a raised white card**, reversing an earlier decision to
   flatten it into the cream.

4. **Position and size taken from Frontend 4**, at Michael's instruction —
   geometry only, no colour or type. Four numbers out of `frontend4/f4.css`:
   card column `minmax(360px, 480px)`, page inset `clamp(24px, 6vw, 88px)`, card
   padding `clamp(24px, 4vw, 40px)`, vertically centred. Frontend 4 itself was
   read and **not touched** — `git status` on `frontend4/` and
   `resources/js/F4Pages/` is clean and its 64 tests still pass.

5. **The house step stopped being wider than the rest.** It was 620px against
   480px for the two steps before it, so the journey changed shape at the end.

6. **The house row lost its right-hand column**, its "Open" label (the whole row
   is a button) and its building icon (identical on every row, so it carried no
   information and cost about seventy pixels).

7. **A back control** was added to the sign-in card, top left, opposite the theme
   toggle. On the switch-house screen it returns to Today. `AuthShell` takes a
   `back` prop; `HouseController` supplies `backUrl` only when a house is already
   open, since otherwise there is nothing behind that screen.

8. **`TextLink` was overwriting any className passed to it**, so a caller's
   styling silently vanished. It merges now.

---

## Still open, separate from the two above

- The unexpected commit `efc28181` on `record7-section0-access`, authored
  `poffodile` and not by me. Michael's call what to do with it.
- The Frontend 4 role-cache issue — found, reproduced, deliberately not fixed.
- Port 8000 is held by the Omega Life Django app; Record7 stays on 8333.
- MFA is paused at Michael's instruction. Prototype only. See
  [MFA-OUTSTANDING.md](MFA-OUTSTANDING.md) and [MFA-INVENTORY.md](MFA-INVENTORY.md).

**Next section after those two fixes: Section 1, the Today dashboard.**


---

## What changed on 26 August

Both queued items are done.

**The back arrow** is now the theme control's twin — same diameter, same
hairline, same pill radius, same hover. They are the only two things on that row
and they sit at its two ends, so anything other than a matched pair looked like
an accident. It is icon only, because the control opposite it is too; the word
"Back" is still announced to assistive technology, just not drawn.

**The house row** was rebuilt on a different principle rather than shuffled a
fourth time. Michael chose it from three options: **the badge appears only when
the access carries a catch.** Full, manager and oversight access all mean "you
can do your job", which is what anybody opening the screen already assumes — so
they say nothing, and the row is two clean lines. Review-only and temporary get a
warning badge, which is then the only badge on the screen and is actually
noticed.

Two tests cover it, and both were checked to genuinely fail when the rule is
broken:

- Every access type the *policy* treats as read-only must be flagged by the
  *screen*. It asks `UserServiceAccess::isReadOnly()` rather than asserting a
  hard-coded list, so adding a new non-writing access type and forgetting to
  badge it fails the build.
- The badge is conditional and not rendered on every row.

Verified in the browser both ways, by temporarily setting one house to
review-only in `record7_local` and putting it straight back.

**Record7 179, Frontend 4 64, JS 22 — all passing. Nothing committed.**

**Next: Section 1 — the Today dashboard.**
