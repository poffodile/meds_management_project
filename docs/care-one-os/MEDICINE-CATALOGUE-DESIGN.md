# Medicine Catalogue & Prescription Entry — Design
**Date:** 2026-07-16 · Expands REQ-MED-50/51, REQ-MED-100/101/102/103/107 · Owner conversation 2026-07-16.

**Claim boundary:** an engineering design. It does not make Care One OS compliant, certified, clinically safe or NHS-approved, and it makes no claim that using dm+d makes a medicine record correct.

---

## 1. The core principle (owner, 2026-07-16)

> *"the meds are not manually written but taken from the dm+d … each med collected should have the dose to be given as well"*

Right, and it is the whole design:

**A medicine is CHOSEN from coded reference data. A dose is ENTERED from the prescription. Everything else is DERIVED.**

The product never invents a dose and never asks a carer to do arithmetic.

### Where each fact comes from — this distinction is load-bearing

| Source | Gives you | Does NOT give you |
|---|---|---|
| **dm+d** | Which medicine exists: name, strength, form, unit, pack, CD schedule, discontinued/replaced status | **The dose.** dm+d is a dictionary of *products*, not dosing guidance |
| **The prescription** (prescriber / GP / pharmacy label) | The dose, the frequency, the PRN limits, the route | Which coded product it is (a label says "Paracetamol 250mg/5ml" — matching that to a dm+d concept is the system's job) |
| **BNF / BNFc** | Whether a dose is *appropriate* for this person | Anything we have today — separate licensed dataset, see §7 |
| **The system** | Volume, stock movement, limit enforcement, the audit trail | Clinical judgement of any kind |

**Correction recorded, because it caused real confusion during the build:** "paediatric dosing" is **not** something the product needs to know. A prescriber already worked out that a 22 kg seven-year-old gets 200 mg of ibuprofen; that arrives on the prescription and the product records it. The paediatric doses in `NeptuneHouseDemoSeeder` are flagged unverified **only because the seeder has no prescriber** — the assistant invented them for demo screens. That is a fake-data caveat, not a design gate, and it was wrongly allowed to look like one.

---

## 2. Prescription entry — the model

```
  [ dm+d product picker ]        <- CHOSEN, never typed
        │  gives: name, strength (250 mg / 5 ml), form (suspension),
        │         unit (ml), is_controlled + cd_schedule, dm+d status
        ▼
  [ dose        500  ] [ mg ▾ ]  <- ENTERED, from the prescription
  [ frequency / slots        ]  <- ENTERED
  [ PRN? max/day, min interval] <- ENTERED (if PRN)
        │
        ▼
  DERIVED, shown back for confirmation, never typed:
     volume per dose = 500 ÷ 250 × 5 = 10 ml
     stock deducts in ml
     round displays "Give 10 ml (500 mg)"
```

**Free-text `medication_name` is retired as an identity.** It is preserved as `medication_name_as_written` — the original typed/label text — because the record of what the prescription actually said has value (REQ-MED-50) and destroying it during migration would lose information. It is no longer what the system *means* by "this medicine".

### Why the derived volume matters beyond tidiness

`500 mg of a 250 mg/5 ml suspension = 10 ml` is arithmetic a carer currently does in their head, at 8am, under time pressure, on a child. Paediatric liquid-dose calculation is a recognised error source. Deriving it from the catalogue strength removes the sum from the human — and it is the same fact the stock ledger needs, so they cannot disagree.

### What this kills

The audit (`ROUND-AUDIT-2026-07-16.md`) found `Melatonin 2mg modified-release tablets` in `mar_sheets` and `Melatonin 2mg Tablet` in the stock ledger. **Modified-release and immediate-release are different medicines.** Free text let one prescription be three different medicines across three tables. A coded id makes that impossible rather than merely discouraged.

---

## 3. Schema

### `medicine_catalogue` — shared reference data

**No `home_id`. No `admin_id`.** That absence *is* REQ-MED-103, enforced structurally rather than by discipline: a new customer must find the medicines already there, and must not be able to fork them.

```sql
medicine_catalogue
  id                    BIGINT UNSIGNED PK
  dmd_code              VARCHAR(18) NULL      -- SNOMED CT concept id
  dmd_concept_level     ENUM('VTM','VMP','AMP','VMPP','AMPP') NULL
  name                  VARCHAR(255) NOT NULL -- dm+d preferred term
  form                  VARCHAR(100) NULL     -- Tablet | Oral suspension | Inhaler | ...
  route                 VARCHAR(100) NULL
  countable_unit        VARCHAR(20) NULL      -- tablet | ml | puff | patch  <- stock is held in THIS
  strength_amount       DECIMAL(10,3) NULL    -- 250
  strength_unit         VARCHAR(20) NULL      -- mg
  strength_volume       DECIMAL(10,3) NULL    -- 5      (null for a tablet)
  strength_volume_unit  VARCHAR(20) NULL      -- ml
  is_controlled         TINYINT(1) NOT NULL DEFAULT 0
  cd_schedule           ENUM('1','2','3','4_1','4_2','5') NULL
  dmd_status            ENUM('current','discontinued','invalid') NOT NULL DEFAULT 'current'
  replaced_by_id        BIGINT UNSIGNED NULL  -- REQ-MED-51
  is_local              TINYINT(1) NOT NULL DEFAULT 0   -- un-coded fallback, still catalogued
  valid_from / valid_to DATE NULL
  UNIQUE (dmd_code)
  KEY (name), KEY (is_controlled)
  CHECK ((is_controlled = 0 AND cd_schedule IS NULL)
      OR (is_controlled = 1 AND cd_schedule IS NOT NULL))
```

That `CHECK` makes **"Warfarin as a Schedule 2 controlled drug" structurally impossible** — the owner's literal reason for wanting coded data, enforced by the database instead of by hope. `cd_schedule` as an ENUM kills format drift (`'2'` vs `'schedule_2'`) before it can start.

`is_local` matters: a customer will eventually have something with no dm+d match (a special, an unlicensed import). The answer is a catalogue row flagged un-coded — **not** a free-text escape hatch, which would rebuild the problem.

### `mar_sheets` — the prescription

```sql
  medicine_id              BIGINT UNSIGNED NULL   -- FK -> medicine_catalogue   (identity)
  medication_name_as_written VARCHAR(255) NULL    -- preserved original text     (provenance)
  dose_amount              DECIMAL(10,3) NULL     -- 500                          (prescribed)
  dose_unit                VARCHAR(20) NULL       -- mg
  dose_quantity            DECIMAL(10,3) NULL     -- 10  <- consumed, in countable_unit  ✅ BUILT
  -- is_controlled / cd_schedule become DERIVED from the catalogue (REQ-MED-107),
  -- the local columns demoted to legacy rather than dropped.
```

`medicine_id` is **nullable** and additive, so existing flows keep working (REQ-MED-102). This is a staged decoupling, not a rewrite.

### Standalone (REQ-MED-100)

A pharmacy tenant gets `medicine_catalogue` + stock + CD register with **zero** residents and **zero** MAR sheets. The catalogue has no dependency on `service_user` or `mar_sheets` — that is what makes medication deployable without the care layer.

The audit found the live evidence for why this matters: **all 19 stock transactions are orphaned**, pointing at deleted `mar_sheets`, with no foreign keys anywhere. A medicine currently has no independent existence, so it dies with the prescription and its stock history becomes unreadable free text. That is REQ-MED-100 failing in production data today.

---

## 4. Migration — the part that cannot be automated

**String-matching backfill is unsafe and must not be attempted.** `Melatonin MR` vs `Melatonin Tablet` and `Amoxicillin suspension` vs `Amoxicillin capsule` are *different products*, not typos. A fuzzy matcher would silently map a modified-release prescription onto an immediate-release product.

Staged:
1. Ship the catalogue, seeded from dm+d.
2. **New** prescriptions use the picker. `medicine_id` required going forward.
3. **Existing** prescriptions: a review screen — one row per distinct legacy string, a suggested match, and a human confirms. Nothing auto-maps.
4. The 19 orphaned stock rows may be **unmappable**. Leave them NULL and say so, rather than guess.
5. Only once `medicine_id` is populated does `is_controlled` switch to derived.

Who does step 3 is the **customer's** call, not ours (see §6) — for most it will be whoever manages their medicines.

---

## 5. GP Connect and barcode — how they attach (owner raised both)

The catalogue is the **anchor both of these need**. Neither works against free text. This is the strongest argument for building the catalogue before either.

### GP Connect — a SOURCE of prescriptions
- Supplies the structured medication list + allergies from the GP record. It arrives **dm+d-coded**, which is precisely why the local model must be dm+d-coded to receive it. Against free-text `medication_name` there is nothing to reconcile *to*.
- **Never silently overwrites the local record.** Incoming data lands as a *proposal* with provenance (source, retrieved-at, who reconciled) and a human accepts/rejects per line. A GP record and a MAR chart legitimately disagree — that disagreement is clinical information, not a merge conflict.
- Behind a feature flag, against a mock provider with synthetic data, until real assurance exists. **We must never imply a live NHS integration that is not there.**
- REQ-MED-70/71. Prerequisite: `medicine_id` populated.

### Barcode — a VERIFICATION aid at administration
- Scans a **GS1 GTIN** off the pack. A GTIN is *not* a dm+d code: it needs a governed **GTIN → dm+d** mapping table, maintained as reference data. That mapping is its own piece of work and should not be hand-waved.
- **A scan assists; it never proves.** It can confirm *this pack is that medicine*. It cannot confirm the right resident, dose, route or time. It must not auto-administer, and a manual fallback must always exist — a damaged label at 8am cannot block a dose.
- Handle explicitly: unknown GTIN, unmapped GTIN, duplicate scan, wrong medicine scanned.
- REQ-MED-60/61. Prerequisite: the catalogue + the mapping table.

```
        GP Connect ──(dm+d-coded proposal)──┐
                                            ▼
   dm+d ──seeds──►  medicine_catalogue  ◄──(GTIN→dm+d map)── barcode scan
                            │
                            ▼  medicine_id
                        mar_sheets ──► rounds · stock · CD register
```

Everything hangs off the catalogue. Build it first or build both of these twice.

---

## 6. Who decides what — the SaaS rule (owner, 2026-07-16)

Care One OS is sold to pharmacies, care homes, supported living, children's and adult services. **A role cannot be a build-time dependency.** A pharmacy tenant *is* pharmacists; a supported-living house may have none.

| Kind of question | Resolution |
|---|---|
| **Fact** — did the resident receive it? | Make the code true. No sign-off. |
| **Policy** — block or warn at zero stock? staleness threshold? | **Tenant setting, fail-safe default.** Ships working; each customer tightens it. |
| **Clinical content** — the dose for *this* person; the legacy→dm+d mapping | The **customer's** prescriber/medicines lead, at the point of data entry. Not a gate on our build. |
| **Claim** — compliant / safe / approved | Never made. |

---

## 7. Dose *checking* is a different product (and a licensing question)

Recording a prescribed dose needs dm+d. **Checking** a dose — "that looks high for 22 kg" — needs BNF/BNFc, a separate dataset.

**BNF/BNFc licensing — VERIFIED 2026-07-23** (Pharmaceutical Press licensing page, fetched directly). Quoted: *"The right to make an adaptation of BNF or BNFC content for the purposes of merging it into a computer system is not included in the NICE NHS User licence"* and *"Derivative or transformative use is not permitted."* A **separate commercial licence from the BNF Partners** (via rpharms.com) is required to embed BNF/BNFc in any third-party product. So a dose-checking feature is gated on a commercial licence, not just engineering. Confirmed: dm+d ≠ dosing guidance; BNF/BNFc = dosing guidance.

**dm+d TRUD licensing — STILL UNVERIFIED.** dm+d is distributed via NHS TRUD under registration; it is generally OGL-framed, but the *specific* licence text attached to dm+d could not be read without an authenticated TRUD account. **Do not treat "OGL = free commercial redistribution in a multi-tenant SaaS" as settled** until someone with a TRUD login reads the actual licence.

Not in scope now. Recording what was prescribed, in the right unit, and enforcing the prescriber's own limits is what a MAR does.

---

## 8. Build order

1. `medicine_catalogue` + dm+d seed
2. Picker on prescription entry; `medicine_id` on new prescriptions
3. `dose_amount`/`dose_unit`; derive volume; show "Give 10 ml (500 mg)" on the round
4. Legacy mapping review screen (human-confirmed)
5. `is_controlled`/`cd_schedule` switch to derived; local columns demoted
6. Point stock + CD register at `medicine_id`
7. *then* GTIN→dm+d map → barcode · *then* GP Connect behind a flag

Steps 1–3 are additive and independently deployable. Nothing removes a column a live read depends on.
