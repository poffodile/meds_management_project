# Care One OS — product context

## What it is
Care One OS is a **UK medication-management and care-management system** for supported living and adult social care (residential/care homes and community settings). It supports medication administration records (MAR/eMAR), medication rounds, missed/refused-dose handling, controlled-drug registers, stock control, and resident/care records. It is being developed as **health and care software**, which means it is in scope for clinical-safety and NHS assurance expectations — not merely a CRUD web app.

## Who uses it
Support workers, senior support workers, team leaders, registered managers, medication-trained staff, administrators, and authorised witnesses. Design for real conditions: busy medication rounds, interruptions, agency staff, gloves, mobile devices, low light, poor connectivity, long medicine names, large resident lists, and safe recovery from incomplete tasks.

## Technology stack
- **Backend:** Laravel 10, PHP 8.3.
- **Frontend:** React + Inertia.js + Mantine v7 (`@mantine/core`, `@mantine/hooks`, `@mantine/notifications`). Vite for assets.
- **Database:** MySQL 8.4. NOTE: the schema is loaded **from a dump**, not from a clean migration history — there is **no `users` table** in the usual Laravel shape; user ids come from existing rows (e.g. `medication_stock_transactions.performed_by_user_id`). Guard migrations with `Schema::hasTable`/`hasColumn`.
- **Frontend2** pages live in `resources/js/Pages/Frontend2/`; the shared design tokens/atoms live in `frontend/tokens.js` and `frontend/components/*`. See `docs/design-system.md` and `docs/brand-guidelines.md`.
- **Roles:** two-tier — `user_type` (N/A/M/CM/O) plus per-home `access_levels`; the React layer collapses this to manager/carer. Permissions **must** be enforced server-side in Laravel; hiding a button in React is not security.

## Current state (2026-07)
Frontend2 revamp in progress. Stock 2 is at a "done" tier. Missed Doses, Controlled Drugs and Medication Round are being finished/rebuilt. The Medication Round page is safety-critical and is the reference page for the new design system.

## SaaS tenancy & the two-tier data model (owner, 2026-07-16)
Care One OS is a **SaaS product**: it must work for any customer "as long as the right information is in the database." That splits all data into two tiers, and the distinction drives most medication architecture:

1. **System reference data — ships WITH the product, identical for every customer.** The industry-standard information a company should find *already there* on day one: the **dm+d medicines catalogue** (coded), MAR outcome codes, CD schedules, route/form vocabularies, standard risk taxonomies. **This resolves the dm+d specialist's open question:** `medicine_catalogue` is **system-level/shared, NOT per-company** — a company must not have to type in the British National Formulary to start.
2. **Tenant data — per company/home.** Residents, prescriptions, administrations, stock, staff. Onboarding a company = importing *their* tenant data (they complete a **defined import format/template**) and mapping it onto the shared reference tier. Never require a customer to supply reference data.

**Tenancy shape (verified in the DB, 2026-07-16):** `home.admin_id` = the **company/tenant**; each `home` row = a house within it. Company `admin_id=1` owns **16 houses**, including **Neptune House (`home.id=101`)** and Aries House (`id=8`). `Home1` (`id=92`) belongs to a **different** company (`admin_id=131`) — keep it as a **tenant-separation negative test** (it must never appear for company 1).

**Manager multi-home switching is a requirement — and is currently broken.** Managers must switch between their company's houses and see each house's data, plus the care view. Today `getHomeId()` does `explode(',', $user->home_id)[0]` (`MedicationRoundController.php:47`) — it silently takes only the **first** house for org-level users. This must be an explicit, visible house selection.

**Audit is an explicit owner requirement, not just compliance:** there must be a way to see **any change made and who made it**. This aligns with REQ-MED-30/31 (append-only, no destructive edits) and the IG findings (corrections currently overwrite in place; round-closure rows are hard-deleted on reopen).

**Market:** the owner's own company serves **children & young people** (Neptune House is a children's/young people's home — note `home.number_of_child`, and the legacy `risk` taxonomy). The product must serve **both** children's/young people's services **and** adult social care / supported living. Setting must be a property of the tenant's data, not baked into the app.

**House type is TENANT DATA, not an application fork (owner, 2026-07-16).** One company can run a mix of houses — supported living, children's, residential — under any names they choose. On onboarding, a company adds **their** houses, sets each house's **type**, and records who lives where. The application does **not** branch per care setting; the same medication data model serves all of them, populated according to the regulations that apply to that house. **Do not** build setting-specific code paths, and do not ask the owner to choose a setting for the product — ask what type a *house* is.

**ONE resident model: common core + setting-specific fields (owner, 2026-07-16).** Some companies run **both** children's and adult services. They must **never enter the same information twice**. So the data model is:
- **Common core — entered once, applies to every setting:** identity (name, DOB, NHS number, photo, room), allergies/intolerances, GP, medicines, MAR/administration records, stock, controlled drugs, risks.
- **Setting-specific extensions — stored on the same record, only *asked for / required* when they apply:** e.g. children's → parental-responsibility holder + consent, Gillick-competence assessment, Ofsted reg 40 notifications, supported self-administration (reg 23); adult → MCA 2005 capacity/best-interests, covert-administration authorisation.

**Do NOT fork the resident model per setting.** What varies is **which fields are required and asked**, driven by the house's type — not which table the person lives in. Two reasons this is load-bearing, not preference:
1. **The regimes overlap on real people.** MCA 2005 applies from **age 16**; Gillick applies **under 16**. Neptune House has residents aged **10–17**, so a 16–17-year-old in a children's home sits in **both** frames simultaneously. Consent cannot be resolved by house type alone — it depends on the **person's age**, so a forked model has no correct home for that resident. (The MCA-vs-Gillick precedence for 16–17s is flagged **UNVERIFIED** — needs qualified legal review; see SOURCE-REGISTER STD-63.)
2. **Transition.** A young person turning 18 moves from parental-responsibility/Gillick to MCA. With one record the consent frame moves with them and medication history is continuous. A forked model would force re-creating the person exactly when continuity matters most.

Conditional requirement should be driven by **house type + resident age**, evaluated at the point of asking — not baked into separate schemas or code paths.

**Age range (owner, 2026-07-16):** the company takes children from **~6 years old up to their late teens**, and the product must also serve **adults**. So the live population spans the whole consent spectrum: **6–15** (Gillick / parental responsibility), **16–17** (MCA and Gillick overlap — precedence **UNVERIFIED**, needs legal review), **18+** (MCA). This is not theoretical: Neptune House already has a **17-year-old** (`service_user.id=177`). Age-driven conditional logic is therefore load-bearing from day one, and `service_user.date_of_birth` is now a real `DATE` (converted 2026-07-16) so age is reliably computable.

### Adaptable behaviour, STATIC structure — do not make the schema "dynamic" (owner requirement, 2026-07-16)
The owner's requirement: the database must adapt across settings **without making data hard to extract** — *"the order or the type of data shouldn't make it hard to extract."* These two goals pull against each other, and the obvious solution is the wrong one:

❌ **Do NOT** implement per-setting flexibility with **EAV/key-value tables, `custom_fields`, or JSON blob columns.** They feel adaptable but destroy exactly what the owner asked to protect: you cannot write a plain query, cannot index, cannot report, and every extraction becomes bespoke. This is a common way care systems become unreportable.

✅ **DO** use one **common core of typed, nullable, indexed columns**. A children's house leaves the MCA/covert columns NULL; an adult house leaves the Gillick/parental-responsibility columns NULL. The **schema is static and queryable**; the **behaviour is dynamic** — only *which fields are required/asked* varies, resolved at the point of asking from house type + resident age. Extraction stays a plain `SELECT` in every setting, forever.

Corollary: prefer extending existing structured tables over inventing parallel ones. `client_consents`, `care_plan_pharmacies` (`self_administers`, `administration_support_level`, `gp_details`) and `su_care_team` (job titles incl. Doctor/Dentist) already exist — several are disconnected from the Medication Round, the same way `care_plan_risks` is. Wire them up; do not duplicate them.

⚠️ **The one thing that DOES vary by setting is the regulator, not the app.** Adult social care → **CQC** (Health & Social Care Act 2008 + 2014 Regulations). Children's homes in England → believed to be **Ofsted** under the Children's Homes (England) Regulations 2015 — **being verified**; see the children's-services section of [STANDARDS-REGISTER.md](STANDARDS-REGISTER.md). Compliance agents must cite the regime matching the **house's type**, and must not assume CQC. Setting-independent regimes (apply to both): Misuse of Drugs Regulations 2001, Human Medicines Regulations 2012, UK GDPR/DPA 2018, WCAG 2.2, DCB0129/0160, dm+d.

## Product direction: medication must be able to stand alone (owner, 2026-07-16)
Care One OS's medication module must be usable **without** the care-package / MAR-sheet layer. Not every customer administers medicines to residents — e.g. **a pharmacy** needs medicines, stock and controlled-drug handling but has **no MAR sheets and no residents to administer to**. Treat "medication" as a component that can be deployed standalone, with the resident/MAR/administration layer as an *optional* tier on top.

**Architectural consequence (important):** today a medicine has **no independent existence** — it exists only as a row on `mar_sheets` (i.e. only as one resident's prescription). `medication_name` is re-typed as free text into `controlled_drug_register` and `medication_stock_transactions` with no shared anchor. So a standalone (pharmacy) deployment has no medicine entity at all to hold stock/CD/scanning against. The fix is the same one the dm+d work needs: a **medicine catalogue** entity (dm+d-coded where possible) that MAR sheets *reference* rather than *are*. Design new medication features so they depend on the catalogue, not on `MARSheet`.

Do not break the existing MAR-based flows to achieve this — it is an additive decoupling, staged.

## Test data (dummy dataset — owner, 2026-07-16)
The MySQL dataset is **dummy data** and is not representative of a real care package. The owner has approved **adding to it** so each client has proper details and realistic use cases. This matters for safety, not just demos: sparse test data actively hides defects — e.g. the `risk_flags` render crash (`MedicationRound.jsx:208`) only fires for residents with an active `care_plan_risks` row, so it stayed invisible.

Seed coverage should include, across clients: active care-plan risks (multiple impact levels), allergies (including a `NKDA`-style "no allergy" string to test false positives), controlled drugs (with schedules), PRN medicines (with `prn_max_daily`/`prn_min_interval_hours` set, incl. one at its daily limit and one inside its interval → `blocked`), low/zero stock, long medicine names, a large resident list, discontinued sheets, and residents with missing photos/room/NHS number. Add via a **repeatable seeder** (not ad-hoc INSERTs) so it can be re-run and reviewed; never seed into a real/production dataset.

## Work split & file ownership (avoid collisions — the owner works in parallel)
The owner edits the **original "Medication"** sidebar group in a separate terminal; the agent workflow builds fresh, agent-driven versions in the **"Medication 2"** group (currently placeholder stubs). These must never edit the same files at once.

- **OWNER owns (agents DO NOT touch):** the original pages `resources/js/Pages/Frontend2/MedicationRound*.jsx`, `Medications.jsx`, `MissedDoses.jsx`, `ControlledDrugs.jsx`, `Stock.jsx`, `Stock2.jsx`, and the **existing methods** of their controllers (`MedicationRoundController`, `MissedDosesController`, `ControlledDrugRegisterController`, `MedicationStockController`, etc.). Routes: `/frontend2/medication-round`, `/medications`, `/missed-doses`, `/controlled-drugs`, `/stock`, `/stock-2`.
- **AGENTS own (build here):** `resources/js/Pages/Frontend2/Medication2/*.jsx`, the Medication 2 route/controller (`Frontend2Controller@medication2` catch-all, or a new dedicated `Medication2Controller`), and **new, clearly-named backend methods** (e.g. `roundMed2()`) — never edit the owner's existing controller methods. Routes: `/frontend2/medication-2/round | medications | missed-doses | controlled-drugs | stock`.
- **Shared, read-mostly:** Eloquent **models**, migrations, `frontend/tokens.js`, `frontend/components/*`. Agents may **read** freely and **add** shared tokens/atoms/columns; if a shared model/migration change is required, the orchestrator flags it for the owner rather than editing it mid-flight (or serialises via a git worktree).

Net: the agents reuse the **business logic** behind the original pages (by calling shared models / adding parallel controller methods) but render it through the new Care One OS design system in the Medication 2 slot.

**Exception — Critical fixes to original pages (owner, 2026-07-16).** The owner has approved fixing **Critical defects in the original pages** as part of each page's exercise, even though those files are owner-owned. The **focus stays on the agent-built Medication 2 page**; original-page work is limited to *Critical* defects only (not redesign, not Important/Optional polish). Because the owner may have these files open in another terminal, the orchestrator must announce each original-page fix, keep it minimal and surgical, and never bundle it with unrelated change. Currently in scope for Medication Round:
- `MedicationRound.jsx:208` — `risk_flags` renders an object → page crashes for residents with an active care-plan risk.
- `MedicationRoundController.php:674` (`marReport`) — unscoped `ServiceUser::find()` → cross-home resident name/DOB disclosure (IDOR).
- `recordFrontend2()` `:845-852` — failure redirects so Inertia fires `onSuccess` → a failed dose record can present as success.
- Role gating on `recordFrontend2()` / `endFrontend2()` (the `ALLOWED_USER_TYPES` list contains every user_type, so it gates nothing).

## Non-negotiable rules
1. **Preserve existing functionality.** Inspect before changing; reuse existing components/styles; do not touch unrelated files. Do not remove functionality without explicit approval.
2. **Do not invent legal/clinical/NHS requirements.** Cite the exact source (see [SOURCE-REGISTER.md](SOURCE-REGISTER.md)).
3. **Separate the force of each requirement:** legal / mandatory NHS standard / contractual-or-assurance / recommended good practice.
4. **No unsupported compliance claims.** AI review ≠ compliant/certified/safe/approved.
5. **Flag human-review points:** Clinical Safety Officer, DPO, pharmacist, medication lead, care professional, security specialist, NHS assurance body.
6. **Safety-critical UX:** keep resident identity visible during administration; never convey medication status by colour alone; validate and confirm safety-critical actions; guard against double submission and wrong-resident selection.
7. **Interoperability-ready:** structure medication data to be **dm+d-ready**, and design integrations (GP Connect) behind mocks/feature-flags with provenance and reconciliation — never silently overwrite the local record.
