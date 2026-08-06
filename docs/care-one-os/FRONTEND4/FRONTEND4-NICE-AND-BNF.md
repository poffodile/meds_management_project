# Frontend4 — where NICE / BNF data plugs in (and what we do without it)

**Purpose.** The owner holds a **Full NICE content licence via the NICE Syndication API**.
This note records **where NICE content is used**, and **what we build regardless**, so the
front end is never blocked and nothing is overlooked.

Written 2026-08-05. **Scope confirmed 2026-08-05 from the licence application**
([RECORD7-NICE-LICENCE-APPLICATION.md](RECORD7-NICE-LICENCE-APPLICATION.md)):

> ⚠️ **The NICE Syndication licence covers NICE _guidance, quality standards and information
> for the public_ ONLY. It does NOT include BNF, BNF for Children or CKS, and it is NOT
> dm+d.** So NICE is a **guidance/reference-panel** layer, not a source of medicine
> dosing/interaction data. **D2 (coded medicines / I4) is therefore NOT solved by this
> licence** — it still needs dm+d/SNOMED (a separate arrangement "where appropriately
> licensed"), and any BNF use needs a separate licence later. The plug-in table below is
> corrected accordingly: points 1–3 do **not** come from the NICE key; points 4–5 do.

---

## The governing principle

**NICE/BNF data is an enhancement layer, never a dependency.**

- Every page below works with a **fallback** that needs no external content.
- **No safety-critical behaviour depends on NICE.** Identity check, mandatory reason,
  witnessing, stock reconciliation and audit all work without it. This is the same rule the
  merged plan sets in **C8**: an unavailable external service must never block administering a
  medicine.
- Where NICE data exists, it **adds a reference or a warning** — it never generates clinical
  advice on its own, and never auto-acts. (Merged plan C8, and the Missed-doses rule: "must
  not give clinical advice automatically — direct staff to policy and to authorised
  professionals.")

**So:** build every page on its fallback first. Wire NICE in at the marked points once we
know what the key unlocks. Nothing waits.

---

## The plug-in points

| # | Where | If we HAVE NICE/BNF data | If we DON'T (the fallback we build now) | Tracks |
|---|---|---|---|---|
| 1 | **Coded medicines** — MAR sheet, Client profile → Medications | Store a stable coded identifier (dm+d VTM/VMP/AMP, or BNF code) against each medicine; pull authoritative name / strength / form. Two spellings become one medicine. | Keep the current free-text `mar_sheets.medication_name`, plus a light in-house normalisation so obvious duplicates don't split. Adequate, not authoritative. | **D2 / I4** |
| 2 | **Allergy & interaction checking** — Administration workspace | Warn when the medicine being given clashes with a recorded allergy *or* interacts with another of the person's medicines, using BNF interaction/allergy data. | Build the structured allergies table (**D1**) regardless — it lets us match on the allergen name/class we hold and *display* a clear warning. We just can't do full drug–drug interaction checking without the data. **D1 is worth doing either way.** | **D1 / I3** |
| 3 | **PRN limits** — Client profile → PRN protocols, Administration workspace | Pre-fill and sanity-check max daily dose / minimum interval against BNF reference values. | Use the prescription's own values — `mar_sheets.prn_max_daily` and `prn_min_interval_hours` already exist and are already enforced inside a row lock. **Fallback already built.** | — |
| 4 | **Escalation → policy** — Missed doses & follow-ups | Link the relevant **NICE guidance / pathway** when a dose is missed, refused, or a high-risk/CD medicine is involved. | Link the **organisation's own policy** (from configuration). Either way the system points to policy and never writes clinical advice itself. | **D3 / I9** |
| 5 | **Medicine reference on screen** — anywhere a medicine is shown | Show authoritative cautions / "what it's for" from BNF alongside the prescription's own instruction. | Show only what the prescription holds (dose, route, instruction, indication) — already the plan, and instruction/indication are kept separate because an indication shown as a directive is a hazard. | — |

Points **not** touched by NICE: stock ledger, controlled-drug witnessing mechanics, roles &
permissions, audit log, handover structure. Those are ours regardless.

---

## What this means for the build order

- **Start now, unblocked.** The easiest pages (Clients, Client profile) don't need NICE at
  all. Build them on their fallbacks.
- **D1 (structured allergies) is worth doing whether or not NICE arrives** — it's the
  foundation of point 2, and even without interaction data it upgrades allergies from
  "displayed" to "checked against what we hold".
- **D2 (coded medicines) is the one decision NICE could reshape** — so before building the
  MAR sheet / Medications tab to their full depth, confirm whether the key gives us dm+d or
  BNF codes. If yes, we store codes from the start rather than retrofitting them later.

## Owner action (no rush)

Check what the licence key actually unlocks — BNF/BNFc, dm+d, or NICE guidance syndication —
and tell me. Until then the plan holds and nothing is waiting on it.
