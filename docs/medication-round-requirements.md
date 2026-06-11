# Medication Round — Requirements & Gap Analysis

> Source: owner's requirements writeup (Obsidian: "Meds Round Page Requirements"). Captured 2026-06-11 with current build status mapped against each item.
> Principle (owner): this page should feel like a **safe guided checklist, not just a table.**

## Purpose
Medication Setup says what a resident *should* take; **Medication Round records whether they actually took it** — updating the MAR, stock, CD register, missed doses, and handover.

## Data links
- **Reads from:** Service Users (name, photo, DOB, room, allergies), Medication Setup (medicine, dose, route, schedule, PRN rules), Stock (current/low/expiry), Controlled Drugs (CD flag, witness), MAR (previous admin, last-given).
- **Writes to:** MAR (given/refused/missed), Stock (deduct on given), CD register (entry if CD), Missed Doses (alert if refused/missed), Shift Handover (important med issue).

## Gap analysis — status as of 2026-06-11
| Feature | Owner's note | Status | Notes |
|---|---|---|---|
| Round tabs (Morning–Night) | ✅ | ✅ done | |
| Date selector | ✅ | ✅ done | |
| Search resident/medication | ✅ | ✅ done (resident search) | medication search not yet |
| Resident list (due only) | ✅ | ✅ done | |
| Resident safety info (name/room/DOB/photo) | ❌ Add | ✅ mostly | photo+DOB+age+name done; **room = placeholder** (no room lookup table in DB) |
| Allergies shown before recording | ❌ Add | ✅ done | in ResidentCard + risk strip |
| Medication details (name/strength/dose/route/time) | ⚠️ | ✅ done | |
| Medication timing (Due/Late/Overdue) | ⚠️ Improve | ✅ done | derived `overdue/due_now/upcoming` |
| Status types (Due/Late/Overdue/Given/Refused/Omitted) | ⚠️ | ✅ done | "Late" not distinguished from Overdue yet |
| Stock display + low-stock flag | ⚠️ Improve | ✅ done | shows stock+unit+Low; never "Unknown" |
| Stock auto-deduct on Given | ✅ | ✅ done | |
| CD witness flow | ❌ Add | ✅ done | enforced UI + server (2026-06-11) |
| Record Given (one-tap) | ✅ | ✅ done | non-CD scheduled = one tap |
| Round progress (x of y) | ✅ | ✅ done | donut |
| Special instructions prominent | ⚠️ Improve | ⚠️ partial | shown inline; **not warning-styled / prominent** |
| Double-dose prevention | ❌ Add | ⚠️ partial | server guards double-deduct; recorded badge shown. **Missing: "Already administered HH:MM by X"** |
| Refusal reason (structured) | ❌ Add | ❌ TODO | currently free-text notes; needs **reason dropdown** |
| Omitted reason (structured) | ❌ Add | ❌ TODO | same |
| PRN guidance (reason / last-given / next-available / max dose) | ❌ Add | ❌ TODO | PRN meds listed but no timing/limits |
| Create Missed Dose alert on refused/missed | (data link) | ❌ TODO | not yet flowing to Missed Doses page |
| Add Handover flag from a med issue | (data link) | ❌ TODO | not yet |
| Audit trail (who/when) | ⚠️ Improve | ⚠️ partial | recorded; richer display TODO |
| Filter by status | ✅ list | ❌ TODO | no status filter on the round yet |
| Real-time updates (other staff) | NFR | ❌ TODO | needs polling/websockets |
| End Round (lock + summary) | (workflow) | ❌ TODO | button is a stub |

## Signature approach (owner's call — agreed)
Do **NOT** make carers draw a signature each dose. Use **logged-in user** + optional **PIN / electronic confirmation**. (Current build already attributes the record to the logged-in user — no drawn signature.)

## Owner's "mini workflow" idea for the record modal
Confirm Resident → Review Medication → Choose Outcome → (Reason if not given) → Notes → Confirm. Goal: reduce medication errors, not just looks. (Current modal is single-step; this is a candidate redesign.)

## Prioritised build backlog (functional — after the UI cleanup)
1. **Refusal/Omitted reason dropdown** (Resident refused / Asleep / Hospital / Not available / Other) — required for audit.
2. **PRN flow** — last-given, next-available, max dose, "given in last 24h".
3. **Prominent special instructions** — warning chips ("Take with water", "Do not crush").
4. **Double-dose detail** — show "Already administered at HH:MM by {name}".
5. **Missed Dose alert** on refused/missed (data link to Missed Doses page).
6. **Handover flag** from a med issue → Shift Handover.
7. **Status filter** + distinguish **Late vs Overdue**.
8. **End Round** — lock + summary/sign-off.
9. (NFR) **Real-time updates** for concurrent staff.

> Immediate priority (owner, 2026-06-11): **fix the frontend UI polish first** (layout feels messy), then work this backlog and answer the open workflow questions (round start event, concurrency, grace window, refusal follow-up, end-of-round sign-off).
