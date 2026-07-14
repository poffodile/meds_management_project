# Care One OS — colour palette (snapshot before colour experiments)

> **Saved 2026-07-14 as a safety reference** so we can always restore the current
> look. There are **two systems live**: (A) the official brand palette in
> `frontend/tokens.js`, and (B) the premium *muted* set introduced on the
> redesigned pages (Stock / Stock 2 / Controlled drugs). Eventually these should
> be reconciled into `tokens.js` as one source of truth.

---

## A · Brand colours — `frontend/tokens.js` (fixed; identical in light & dark)

| Name | Hex | Use |
|---|---|---|
| Navy | `#13233F` | Header / sidebar / reverse-logo background |
| Teal (core) | `#45C1BF` | Core brand accent |
| Orange (core) | `#F58321` | Core brand accent |
| Purple (core) | `#795076` | Core brand accent |
| Green (core) | `#88B13F` | Core brand accent |
| Light grey | `#F4F6F8` | Surfaces |
| Mid grey | `#DDE4EA` | Borders / dividers |
| Text grey | `#5F6B76` | Secondary text |
| Primary | Mantine `indigo` | `primaryColor` |

Round colours: morning = brandOrange, lunchtime = brandGreen, evening = brandPurple, night = brandTeal.

---

## B · Premium UI tokens (redesigned pages) — light → dark

| Token | Light | Dark |
|---|---|---|
| Ink (primary text) | `#16233B` | `#EAEDF2` |
| Ink-2 (secondary text) | `#5E6878` | `#97A2B3` |
| Faint (captions / units) | `#939DAD` | `#6C7688` |
| Hairline (`LINE`) | `#ECEEF2` | `rgba(255,255,255,.07)` |
| Card surface | `#FFFFFF` | `dark-6 ≈ #25262B` |
| Neutral icon-tile bg | `#F4F6F9` | `rgba(255,255,255,.05)` |
| Segmented / track bg | `#F1F3F7` | `dark-7 ≈ #1A1B1E` |
| Row hover | `#F8FAFC` | `dark-5 ≈ #2C2E33` |
| Progress track | `#F0F2F6` | `dark-5` |

Mantine dark scale referenced: dark-4 `#373A40`, dark-5 `#2C2E33`, dark-6 `#25262B`, dark-7 `#1A1B1E`.

---

## B · Muted semantic / status set

Text + dot are single hexes used in both modes; the chip background is the light → dark pair.

| Meaning | Text | Dot | Chip bg (light → dark) |
|---|---|---|---|
| In stock / positive / received | `#2F7D5B` | `#3E8E77` | `#EAF4EF` → `rgba(62,142,119,.16)` |
| Expiring soon / CD / administered | `#6E5A94` | `#8A6FAE` | `#F0ECF8` → `rgba(138,111,174,.16)` |
| Low stock / warn / correction | `#9C6B22` | `#BF8A3C` | `#FBF2E4` → `rgba(191,138,60,.16)` |
| Out of stock / negative | `#A24A41` | `#B4544A` | `#F8ECEA` → `rgba(180,84,74,.16)` |
| Expired / disposed | `#9A4560` | `#A6506A` | `#F6EAEF` → `rgba(166,80,106,.16)` |
| Returned / slate accent | `#4E6B9A` | — | — |
| "All items" slate | `#3E5170` | — | — |
| Muted gold bar (yellow) | `#C9A94E` | — | — |

Transaction hues (movements): received `#3E8E77`, disposed `#A6506A`, returned `#4E6B9A`, correction `#9C6B22`, administered `#8A6FAE`.

Forecast tones: ≤7 days `#B4544A`, ≤14 days `#BF8A3C`, else Ink-2.

---

## Accent buttons — light → dark

| Use | Light | Dark |
|---|---|---|
| Primary (navy) | `#16233B` | `#1F9E93` (teal) |
| Receive / book-in | `#2F7D5B` | `#1F9E93` |
| Disposal | `#A6506A` | `#B4657E` |

---

## Toasts (branded notifications)
- Background `#13233F` (light) / `#18243D` (dark); success accent `#1F9E93`, error `#B4544A` / `#C0413A`, disposal `#C43D6B` / `#A6506A`.

---

# UNIFIED palette (NEW — applied to the Stock trial page 2026-07-14)

> **Table background:** soft blue wash in light (`#FFFFFF→#ECF0F7→#DCE4F0→…`),
> subtle deep-navy gradient in dark (`#131E33→#1A2842→#223454→…`) — `palette.tableBg`.
>
> Merges A + B into one theme-aware system. Key change vs the snapshot above:
> **status chips now have proper dark-mode colours** (light text on deep tinted
> chips) instead of reusing the dark light-mode hex. Previewing on
> `/frontend2/stock`; promote into `frontend/tokens.js` once approved.

### Core & brand — light → dark
| Token | Light | Dark |
|---|---|---|
| Ink | `#16233B` | `#EAEDF2` |
| Ink-2 | `#5E6878` | `#97A2B3` |
| Faint | `#939DAD` | `#6C7688` |
| Hairline | `#ECEEF2` | `#2C2E33` |
| Card surface | `#FFFFFF` | `#25262B` (dark-6) |
| App background | `#F4F6F9` | `#1A1B1E` (dark-7) |
| Brand Navy | `#13233F` | `#1A2B4C` |
| Brand Teal | `#208280` | `#45C1BF` |
| Brand Orange | `#D96A14` | `#F58321` |
| Brand Purple | `#6F446C` | `#9B6898` |
| Primary button | `#13233F` | `#45C1BF` |

### 5-state status — text/dot (light → dark) · chip bg (light → dark)
| State | Text/dot light | Text/dot dark | Chip light | Chip dark |
|---|---|---|---|---|
| In stock / positive | `#2F7D5B` | `#A2E0C1` | `#EAF4EF` | `#1E2B24` |
| Expiring / CD | `#6E5A94` | `#CBBCE4` | `#F0ECF8` | `#221C2B` |
| Low stock | `#9C6B22` | `#E6C594` | `#FBF2E4` | `#2D2314` |
| Out of stock | `#A24A41` | `#F1B2AC` | `#F8ECEA` | `#2E1917` |
| Expired / disposed | `#9A4560` | `#F5B8CC` | `#F6EAEF` | `#2B171D` |

Slate accents (both modes): Returned `#4E6B9A`, "All items" `#3E5170`.
