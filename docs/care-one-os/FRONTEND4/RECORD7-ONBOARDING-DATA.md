# RECORD7 — onboarding data form

**What a new company fills in to stand up their own RECORD7 / Care One OS.**

This is the intake form. When an organisation wants to start using the product, they provide
the information below and it becomes the **seed data** for their instance — their homes, their
staff, their clients, their medicines and their settings. Nothing here is invented by us; it
mirrors the actual data the running system uses.

**How to read it:** each section is a "form". **Req** = required to go live · **Opt** = optional,
can be added later · **Later** = a field a not-yet-built page will need (listed now so the shape
is known). This file **grows as we build** — each new page (stock, controlled drugs, handover,
reports) adds its own section.

Written 2026-08-06. Living document.

---

## 1. Organisation

The company itself — the top of the tree.

| Field | Req/Opt | Notes |
|---|---|---|
| Organisation name | **Req** | e.g. "Omega Care Group Ltd" |
| Country / jurisdiction | **Req** | Sets the regulation layer. Default: England (CQC/NICE). |
| Main contact name + email | **Req** | The person we set the account up with |
| What people in care are called | **Req** | The display word: Client · Resident · Young person · Person · Patient. Default **Client**. The system stores "client" underneath either way. |
| Logo | Opt | For their instance branding |

---

## 2. Homes / services / locations

One row per home, unit, supported-living property, or domiciliary service.

| Field | Req/Opt | Notes |
|---|---|---|
| Home / service name | **Req** | e.g. "Neptune House" |
| Type of setting | **Req** | Care home · Children's home · Supported living · Domiciliary · Individual. Affects which modules show. |
| Address | Opt | Not every service is a building (domiciliary) |
| Uses room / unit numbers? | **Req** | Yes/No. If No, the "room" field stays empty and nothing assumes one. |
| Controlled-drug witness required? | **Req** | Drives whether a second signatory is demanded at administration. Set per care setting/policy. |

---

## 3. Staff / users

One row per person who logs in. **Role decides what they can do** — see section 8.

| Field | Req/Opt | Notes |
|---|---|---|
| Full name | **Req** | |
| Email / username | **Req** | Used to sign in |
| Role | **Req** | **Support worker · Shift lead · Manager · Administrator** (see §8) |
| Home(s) they work in | **Req** | A person can be scoped to one or more homes |
| Start date | Opt | |
| Medication-trained? | Later | Competency gating isn't built yet; captured for when it is |
| Witness-authorised? | Later | For controlled-drug witnessing, when competency lands |

> Accounts that hold historical medication records are **deactivated, never deleted** — so this
> list only grows; people are switched off, not removed.

---

## 4. Clients / people in care

One row per person receiving care. (These are the fields the profile and the round already use.)

| Field | Req/Opt | Notes |
|---|---|---|
| Full name | **Req** | |
| Date of birth | **Req** | Drives age; used for age/weight-banded dosing |
| Home / service | **Req** | Which home they belong to |
| Room / location | Opt | Only if the setting uses rooms (§2) |
| NHS number | Opt | |
| Status | **Req** | Active / Inactive |
| Photo | Opt | A file; **if given, the file must actually be uploaded** — a name with no file shows as blank (initials are used as the fallback) |
| Allergies | **Req if any** | Currently a list of allergens (e.g. "Penicillin, latex"). Reaction/severity/source become structured later (planned). |
| Main warnings / risks | Opt | e.g. swallowing difficulty, choking risk |
| **Key contacts — Next of kin** (name, relationship, phone) | Opt | Already captured; shows on the profile's **Key contacts** card |
| **Key contacts — GP** | Later | Goes on the same card; no client field yet (arrives with care-plan / GP Connect) |
| **Key contacts — Pharmacy** | Later | Same card; no field yet |
| **Key contacts — Social worker** | Later | Same card; a new field to add |
| Care needs / medical notes | Opt | Free text on the profile Overview |

> **How the "Key contacts" card grows:** the profile Overview builds each card from a
> list of fields. Next of kin shows today; the moment GP / pharmacy / social-worker
> fields exist, their rows are added in one place and appear on the card automatically —
> no layout change. This is the pattern for adding *any* new Overview section (e.g. a
> future "Appointments" or "Diagnoses" card).

---

## 5. Each client's medicines (prescriptions)

One row per prescribed medicine, per client. This is the **most important and most detailed**
part — a wrong prescription is a safety issue.

| Field | Req/Opt | Notes |
|---|---|---|
| Client | **Req** | Who it's for |
| Medicine name | **Req** | Free text today; a coded medicine (dm+d) is planned |
| Strength | **Req** | e.g. "250mg/5ml" |
| Form | **Req** | Tablet, oral suspension, inhaler… |
| Dose (as written) | **Req** | e.g. "10 ml (500mg)", "2 puffs" |
| **Dose quantity** (a number) | **Req** | The amount **one dose consumes from stock** — a plain number. Stock deduction and the CD register depend on this being a real number, not text. |
| Unit | **Req** | tablet / ml / puff… |
| Route | **Req** | Oral, inhaled, topical… |
| Frequency | **Req** | e.g. "Twice daily", or PRN |
| Times of day (slots) | **Req for scheduled** | e.g. 08:00, 20:00. PRN has none. |
| When required (PRN)? | **Req** | Yes/No |
| — PRN max in 24h | Req if PRN | The daily maximum |
| — PRN min interval (hours) | Req if PRN | Minimum gap between doses |
| — PRN protocol / when to offer | Opt | Symptoms, what to try first |
| Reason for the medicine (indication) | Opt | Why it's prescribed — shown separately from "how to give it" |
| Administration instructions | Opt | e.g. "Take with water. Do not crush." |
| Prescriber | Opt | |
| Pharmacy | Opt | |
| Start date / end date | Opt | |
| Controlled drug? | **Req** | Yes/No |
| — CD schedule | Req if CD | 2–5 |
| Barcode | Opt | For scan-to-verify later |
| Current stock level | **Req if tracked** | Starting balance |
| Reorder level | Opt | When to warn "low" |
| Allergy warning on this medicine | Opt | Free-text caution |

---

## 6. Settings / policies (per organisation or per home)

Defaults the system ships with, that a company can tune.

| Setting | Req/Opt | Notes |
|---|---|---|
| Round times | Opt | Morning / lunch / evening / night windows |
| Late / overdue thresholds | Opt | How late before a dose is flagged |
| Reorder levels / expiry warnings | Opt | Stock alerts |
| Missed-dose escalation rules | Opt | When to escalate a not-given dose |
| Witness requirements | **Req** | Per §2 — when a second person is needed |
| MAR outcome reasons | Opt | The reasons offered when a dose isn't given |
| Terminology overrides | Opt | Per §1 — the word for a client, and for a home/unit/team |

---

## 7. Coming as those pages are built

Listed so the company knows what's on the way (not needed to go live):

- **Stock** — deliveries (quantity, batch, expiry, supplier), disposals, cabinet counts.
- **Controlled drugs** — opening register balances, authorised witnesses.
- **Shift handover** — handover categories, priority levels.
- **Reports & audit** — nothing to provide; generated from the data above.
- **Administration** — incident types, task types, notification templates, competency records.

---

## 8. Roles — who can do what (reference for §3)

The four roles, least to most privileged. Each inherits the one below it. Enforced on the
server, not by hiding buttons.

| Can… | Support worker | Shift lead | Manager | Administrator |
|---|:--:|:--:|:--:|:--:|
| See their home's clients & records | ✓ | ✓ | ✓ | ✓ |
| Record a dose on the round | ✓ | ✓ | ✓ | — |
| Witness a controlled drug | if authorised | ✓ | ✓ | — |
| Correct a MAR record (by addendum) | — | ✓ | ✓ | — |
| Pause / stop / change a prescription | — | — | ✓ | — |
| Export / print reports | — | — | ✓ | — |
| Manage staff (add/deactivate, in own homes) | — | — | ✓ | ✓ |
| Define what a role may do | — | — | — | ✓ |
| Change settings / integrations | — | — | — | ✓ |
| **Overwrite or delete a clinical record** | — | — | — | **nobody** |

> The administrator manages **access**, not the **clinical record** — they can't record,
> witness, correct or change a prescription. That separation is deliberate: whoever can rewrite
> the permission model must not also be able to act clinically.

**Status note (2026-08-06):** the role model and these permissions are **built and enforced**
on every page we've built. The **screens to *manage* roles and accounts** (assign a role,
activate/deactivate a person) are the **Administration page**, which is the last page on the
build plan and **not built yet**. So the rules exist; the admin UI to change them comes later.
