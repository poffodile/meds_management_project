# Care One OS — medication workflow requirements

Functional + safety requirements for medication features. Each requirement has an ID (`REQ-MED-xx`) used in the [TRACEABILITY-MATRIX.md](TRACEABILITY-MATRIX.md). Requirements cite the governing standard from the [STANDARDS-REGISTER.md](STANDARDS-REGISTER.md); confirm the source in [SOURCE-REGISTER.md](SOURCE-REGISTER.md).

## Medication round

> ⚠️ **Citation scope (added 2026-07-23; corrected same day after source verification).** Care One OS runs these round requirements on **children's homes** (Neptune, `home.id=101`, ages 7–17, Ofsted regime) as well as adult settings, so regime matters when reading a citation:
> - **STD-10 (NICE SC1)** — **applies to BOTH regimes.** Verified 2026-07-23 against SC1's own text: it explicitly addresses children's homes (a first draft of this banner wrongly said it was adult-only, echoing a since-corrected register label). Good-practice tier.
> - **STD-11 (NICE NG67)** — **adults 18+ ONLY** (confirmed). Do **not** rely on it for a children's-home resident.
> - **STD-03/STD-04 (CQC Reg 12/17)** and **STD-62 (Children's Homes Regs 2015)** are LEGAL and apply per the matching regime.
> - The children's-home **CD administration-witness** position is still genuinely unresolved (no statute or NICE answer found; SC1 softens only *storage*) — see STD-65. The product's fail-safe default is a product choice, not a regulatory determination.

- REQ-MED-01 Round selection by time-band/home; show **due** and **overdue** medications distinctly (not by colour alone). [STD-03, STD-10]
- REQ-MED-02 Resident identification before administration: photo + name + DOB and/or NHS number visible throughout the action. [STD-03]
- REQ-MED-03 Allergies and clinical warnings surfaced prominently before outcome selection. [STD-03, STD-12]
- REQ-MED-04 Medication detail shows name, **strength, form, dose, route, scheduled time, instructions**. [STD-05, STD-10]
- REQ-MED-05 Outcomes: **Given, Refused, Missed/Omitted, Withheld, Not available** (MAR-code aligned). [STD-10]
- REQ-MED-06 **Mandatory structured reason** for any non-administration (refused/omitted/withheld/not available); free-text notes optional but reason coded. [STD-04, STD-10]
- REQ-MED-07 Late/overdue handling with time captured; re-offer workflow for refused doses. [STD-10]
- REQ-MED-08 Round progress + sign-off/round completion; contemporaneous who/when on every action. [STD-04]

## PRN (as-required)
- REQ-MED-10 PRN requires reason for administration, dose within stated limits, max frequency/24h enforced, and **effectiveness follow-up**. [STD-10, STD-12]

## Controlled drugs
- REQ-MED-20 CD administration requires a **second signatory/witness**; capture both identities. [STD-07]
- REQ-MED-21 Link to CD register with **running balance**; reconcile stock on administration/destruction. [STD-07]
- REQ-MED-22 A missed/refused CD is **not** treated like an ordinary medicine — apply CD controls. [STD-07]
- REQ-MED-23 **CD control strength is driven by the house's registration, not hard-coded** (owner, 2026-07-16). A house must be able to operate as **either** a registered care home **or** supported living:
  - **⚠️ Force class of the witness (clarified 2026-07-23):** the second-signatory-at-administration and the running balance are **GOOD PRACTICE** strongly expected by CQC/NICE/RPS — **NOT** a Misuse of Drugs Regulations 2001 duty. Verified 2026-07-23 against MDR 2001 regs 19/20/27 directly: those cover the CD *register* and *destruction*, and name no witness at administration; CQC's own care-homes page recommends but does not attribute the witness to statute. So "COMPULSORY" below means "the product enforces it as expected good practice," not "the law mandates it." (Open, verified as unresolved: whether reg 27's *destruction*-witness duty binds ordinary care-home staff vs only the supplying pharmacy — legal question, do not assume.)
  - `adult_care_home` (registered care home) → CD **register + witness are enforced** by the product as expected good practice (not skippable in-app).
  - `adult_supported_living` → CD register + witness are **OPTIONAL** (offered and recordable, but not enforced). Rationale: CQC's confirmed position is that a person's **own home** — including a supported-living tenancy — needs **no** CD cupboard/register and **no** legal second-signature witness; the strict convention attaches to registered care homes (STD-07/STD-09).
  - `childrens_home` → **UNRESOLVED — do not assume.** Ofsted regime; CD handling detail is largely local policy/good practice rather than a named statutory clause (STD-62). Needs a human answer before enforcement is chosen.
  - **NULL / unrecognised / missing home → FAIL SAFE = enforce the witness** (owner decision, 2026-07-16). 12 of the active homes have `care_setting` NULL; defaulting NULL to lenient would silently drop the witness requirement across all of them at once. The harm of a missing witness in a registered care home outweighs the friction of an unnecessary one in supported living. This unblocks the NULL houses without forcing 12 decisions up front — but they still need setting properly.
  - **Implemented** 2026-07-16 as `cdWitnessRequired(int $homeId)` in `Concerns/BuildsMedicationRound.php`. The rule is a single positive test: a witness is required **unless** the home is explicitly `adult_supported_living`. Verified against Neptune (101, childrens_home → required), Aries (8, supported living → optional), Home1 (92, NULL → required) and a nonexistent home (→ required). **Not a compliance determination.** [owner; STD-07, STD-09]

## Records, audit & amendment
- REQ-MED-30 **Append-only** medication event log; corrections are recorded as new entries with **reason for amendment**, never destructive edits. [STD-04 (= CQC Reg 17); STD-62 reg 23(2)(c) for children's homes] <!-- was "STD-17→CQC 17": STD-17 is not a registered standard; CQC Reg 17 is STD-04. Corrected 2026-07-23 per compliance review. -->
- REQ-MED-31 Do **not hard-delete** clinical records; use void/supersede (soft-delete) with retention. [STD-03, STD-04]
- REQ-MED-32 Every record shows source/provenance and effective date. [STD-04]
- REQ-MED-33 Restrict undo/amend and audit-export to appropriate roles; log exports of resident health data. [STD-40, STD-04]

## Clinical escalation
- REQ-MED-40 For high-risk/time-critical meds (insulin, anticoagulants, Parkinson's, epilepsy), flag and capture escalation: who was contacted, when, advice received, monitoring required. [STD-10 (SC1), STD-12]

## dm+d & identification (see dm-d-terminology-specialist)
- REQ-MED-50 Store a stable **dm+d identifier** (appropriate VTM/VMP/AMP/VMPP/AMPP + SNOMED CT id) as the primary interoperable value; preserve the original typed description + source. [STD-30, STD-31]
- REQ-MED-51 Handle current/discontinued/replaced dm+d concepts; flag duplicates. [STD-30]

## Barcode scanning (see barcode-and-medication-identification-specialist)
- REQ-MED-60 Support GS1 GTIN scanning mapped (via a separately governed mapping) to a dm+d product/pack; capture batch/expiry/serial where present. [STD-34, STD-30]
- REQ-MED-61 A scan **assists** verification but does not prove correct resident/medicine/dose/route/time; require confirmation and provide a safe manual fallback; handle unknown/damaged/duplicate/wrong barcodes. [STD-03]

## GP Connect & reconciliation (see gp-connect-integration-specialist)
- REQ-MED-70 GP data must **never silently overwrite** the local record; run a **reconciliation** flow (new / changed dose / discontinued / duplicate / conflict) with reviewer + decision recorded. [STD-32, STD-04]
- REQ-MED-71 Show provenance (GP Connect / pharmacy / prescription / hospital discharge / manual / prior record), source organisation, effective date, last sync time. Keep the real integration behind a **feature flag** with mock provider + synthetic data until NHS onboarding/assurance is complete. [STD-32]

## SaaS reference data, onboarding & audit (owner requirements, 2026-07-16)
- REQ-MED-103 The **medicine catalogue is system reference data shipped with the product** (dm+d-coded), **shared across tenants — not per-company**. A new customer must find industry-standard medicines, MAR codes, CD schedules and route/form vocabularies **already present**. (Resolves the dm+d specialist's open question.) [owner; STD-30]
- REQ-MED-104 **Onboarding import**: a company supplies only its **tenant** data (residents, prescriptions, stock) via a **defined import format/template**, mapped onto the shared reference tier. Never require a customer to supply reference data. [owner]
- REQ-MED-105 **Change history must be visible**: any change to a medication record shows **what changed and who changed it**. Owner requirement, and the mechanism for REQ-MED-30/31 (append-only; corrections superseded, never silently overwritten). [owner; STD-04]
- REQ-MED-106 **Manager multi-home switching**: a manager must explicitly select which of their company's houses they are viewing, see each house's data, and access the care view. Silent "first home in the list" resolution is a defect (`getHomeId()` → `explode(',', ...)[0]`). Tenant separation between companies must hold. [owner; STD-40]
- REQ-MED-107 **Coded data prevents clinical nonsense**: the owner's stated reason for wanting dm+d + regulations is to stop entries like "Warfarin as a Schedule 2 controlled drug." A coded catalogue should carry the CD schedule and legal classification **from reference data**, so `is_controlled`/`cd_schedule` are derived, not free-typed per prescription. [owner; STD-07, STD-30]
- REQ-MED-108 The product must serve **both** children's/young people's services **and** adult social care / supported living. Care setting is a property of tenant data, not of the application. [owner]
- REQ-MED-110 **Adaptable behaviour, static structure.** The schema must adapt across care settings **without making data hard to extract**. Implement per-setting variation as **typed, nullable, indexed columns on a common core** — **never** EAV/key-value tables, `custom_fields`, or JSON blobs, which destroy queryability, indexing and reporting (the exact problem the owner asked to avoid). Only *which fields are required/asked* is dynamic, resolved at the point of asking from house type + resident age. Extraction must remain a plain `SELECT` in every setting. Prefer extending existing structured tables (`client_consents`, `care_plan_pharmacies`, `su_care_team`) over inventing parallel ones. [owner]
- REQ-MED-112 **Recorded weight is a dated series, not a field.** Paediatric doses are weight-based (mg/kg), and children grow — so weight must be **re-recorded periodically** and every record must carry **when it was measured and who recorded it**. A single `service_user.weight` column is unsafe: a stale weight silently justifies a wrong dose while looking authoritative. Implement as an **append-only dated series**; "current weight" is **derived** as the latest record and never stored twice (same failure mode as `is_controlled` drifting from `cd_schedule`). Wherever a weight-based dose is shown, the **age of the weight must be shown with it**, and a stale weight must be flagged. **UNRESOLVED — needs a qualified clinical reviewer:** the staleness threshold (likely age-dependent — an infant outgrows a weight far faster than a 16-year-old), and whether a stale weight should *block* or merely *warn*. Also unresolved: whether this applies to adults (Aries, home 8) or children only. Do not invent a parallel table if an existing observation/vitals structure can be extended (REQ-MED-110). [owner; STD-63]
- REQ-MED-111 **Population spans the whole consent spectrum.** Children from ~6 to late teens **and** adults: 6–15 Gillick/parental responsibility · 16–17 MCA/Gillick overlap (**UNVERIFIED** — legal review) · 18+ MCA. Neptune already has a 17-year-old. Age-driven conditional logic is required from day one; `service_user.date_of_birth` is now a real `DATE`. [owner; STD-08, STD-63]
- REQ-MED-109 **One resident model — common core + conditional setting-specific fields.** A company running both children's and adult services must never enter the same information twice. Store the common core once (identity, allergies, GP, medicines, MAR, stock, CDs, risks); hold setting-specific fields (parental responsibility/Gillick consent, reg 23 self-administration, reg 40 notifications | MCA capacity/best-interests, covert administration) **on the same record**, asked/required conditionally by **house type + resident age**. **Do not fork the resident model or create setting-specific code paths.** Rationale: (a) MCA applies 16+ and Gillick under-16, so a **16–17-year-old in a children's home falls under both** — house type alone cannot resolve consent; (b) a resident turning 18 must **transition** without re-creating the person or losing medication history. [owner; STD-08, STD-62, STD-63]

## Standalone medication module (owner requirement, 2026-07-16)
- REQ-MED-100 Medication must be deployable **standalone**, without the resident/MAR-sheet/care-package layer (e.g. a **pharmacy** customer: medicines, stock, controlled drugs, scanning — but no MAR sheets and no administration). The resident/MAR/administration tier is an **optional** layer on top, not a dependency. [owner]
- REQ-MED-101 Introduce a **medicine catalogue** entity as the anchor for medicine identity (dm+d-coded where possible — see REQ-MED-50). MAR sheets, CD register entries and stock transactions must **reference** the catalogue rather than each re-typing `medication_name` as free text. Today a medicine only exists as a `mar_sheets` row, which is what blocks REQ-MED-100. [owner; aligns with STD-30]
- REQ-MED-102 New medication features must depend on the **catalogue**, not on `MARSheet`. Existing MAR-based flows must keep working — this is an **additive, staged decoupling**, not a rewrite. [owner]

> Every requirement above is subject to confirmation against the current official source and, where clinical, to review by a qualified human (pharmacist / medication lead / Clinical Safety Officer).

---

## Medication Round — page element checklist (Definition of Done for the Round page)

The Round page is the first page through the workflow. It records **whether a resident actually took** their medicine — writing to MAR, Stock, CD register, Missed Doses and Handover. The owner's principle: it should feel like a **safe guided checklist, not a table**. Element list is from the owner's design brief; the "already built (original page)" column is reconciled with `docs/medication-round-requirements.md` (status as of 2026-06-11) so the fresh Medication 2 rebuild **reuses existing business logic** rather than reinventing it. Backend reads: Service Users, Medication Setup, Stock, Controlled Drugs, MAR. Backend writes: MAR, Stock (deduct on given), CD register, Missed Doses, Handover.

| # | Element | Req / source | Already built (original page) | For the Medication 2 rebuild |
|---|---|---|---|---|
| 1 | Round selection (Morning–Night) + date | REQ-MED-01 | ✅ tabs + date | Reuse; distinguish **Late vs Overdue** (not colour-only). |
| 2 | Due residents (due-only list) | REQ-MED-01 | ✅ | Reuse; add **status filter**; handle large lists. |
| 3 | Resident identification (persistent) | REQ-MED-02 · STD-03 | ✅ name/DOB/age | Keep identity banner **visible during administration** (HAZ-02). |
| 4 | Resident photo + key details | REQ-MED-02 | ✅ photo; room = placeholder (no room table) | Reuse; flag room-data gap. |
| 5 | Allergies & warnings (before recording) | REQ-MED-03 · STD-03/12 | ✅ risk strip | Make **prominent**, label+icon+SR text. |
| 6 | Medication name, strength, form, dose | REQ-MED-04 · STD-05 | ✅ | Reuse; **dm+d-ready** identifiers (REQ-MED-50). |
| 7 | Route | REQ-MED-04 | ✅ | Reuse. |
| 8 | Scheduled time | REQ-MED-04 | ✅ | Reuse. |
| 9 | Instructions | REQ-MED-04 | ⚠️ shown inline, not warning-styled | **Prominent** warning chips ("Take with water", "Do not crush"). |
| 10 | Outcomes: Given / Refused / Missed(Omitted) / Withheld / Not available | REQ-MED-05 · STD-10 | ✅ given/refused/omitted | Add Withheld + Not available; one-tap Given for non-CD scheduled. |
| 11 | Mandatory outcome reasons (structured) | REQ-MED-06 · STD-10 | ❌ free-text only | **Reason dropdown** required (Refused / Asleep / Hospital / Not available / Other) (HAZ-04). |
| 12 | PRN reason, limits, effectiveness | REQ-MED-10 | ❌ listed, no timing/limits | last-given, next-available, max dose, given-in-24h, effectiveness follow-up. |
| 13 | Controlled-drug witness process | REQ-MED-20/21/22 · STD-07 | ✅ enforced UI+server | Reuse; link register/running-balance; CD ≠ ordinary med (HAZ-05). |
| 14 | Late & overdue medication | REQ-MED-07 | ✅ overdue/due/upcoming | Distinguish **Late vs Overdue**; capture time. |
| 15 | Stock availability + low-stock | REQ-MED-04 | ✅ stock+unit+Low; auto-deduct on Given | Reuse. |
| 16 | Notes | REQ-MED-06 | ✅ | Reuse; keep alongside structured reason. |
| 17 | Handover quick-add (from a med issue) | REQ-MED-40 link | ❌ TODO | Data link → Shift Handover. |
| 18 | Re-offer workflow (refused doses) | REQ-MED-07 | ❌ | Add re-offer path. |
| 19 | Audit information (who/when) | REQ-MED-30 · STD-04 | ⚠️ recorded, thin display | Richer audit display; append-only; "Already administered HH:MM by {name}" (HAZ-03). |
| 20 | Round progress (x of y) | REQ-MED-08 | ✅ donut | Reuse. |
| 21 | Sign-off & round completion | REQ-MED-08 | ❌ End Round is a stub | Lock + summary + sign-off (logged-in user + optional PIN; **no drawn signature** — owner's call). |
| 22 | Offline & sync status | Design-system states | ❌ | Add offline/sync indicator + safe recovery after interruption/poor network. |
| — | Double-dose prevention | HAZ-03 | ⚠️ server guards deduct | Show "Already administered…"; idempotent submit; disabled/loading state. |
| — | Missed Dose alert on refused/missed | data link | ❌ TODO | Flow to Missed Doses page. |

**Record modal flow (owner's mini-workflow):** Confirm Resident → Review Medication → Choose Outcome → (Reason if not Given) → Notes → Confirm. Reduces medication errors, not just looks.

**Open workflow questions (need owner/clinical decision — do not assume):** round-start event, concurrency between staff, grace window for "late", refusal follow-up policy, end-of-round sign-off rules. Flag these rather than deciding them.
