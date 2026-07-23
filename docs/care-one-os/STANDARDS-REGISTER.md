# Care One OS — UK standards & regulations register

Every standard we review against, **classified by force**. Agents must state which class a requirement falls into and must not blur them. This register names the standards; the specific documents/versions cited go in [SOURCE-REGISTER.md](SOURCE-REGISTER.md), which the researcher keeps current.

**Force classes**
- **LEGAL** — statute/regulations with legal force in England.
- **NHS-STD** — mandatory NHS/DCB standards (e.g. under the Health & Social Care Act information-standards notices) or NHS onboarding requirements where applicable.
- **ASSURANCE** — contractual / assurance frameworks required to sell into or integrate with the NHS (e.g. DTAC, DSPT, NHS onboarding).
- **GOOD-PRACTICE** — recommended guidance; strongly expected by inspectors but not law.

> ⚠️ Applicability is context-dependent. Whether a given standard is mandatory for Care One OS depends on the specific service, contract and integration. Confirm applicability with a qualified human before treating anything as satisfied.

> ⚠️ **TWO REGULATORY REGIMES.** Care One OS serves both **children's/young people's residential services** (regulated by **Ofsted**, not CQC, under the **Care Standards Act 2000** and the **Children's Homes (England) Regulations 2015**) and **adult social care / supported living** (regulated by **CQC** under the Health and Social Care Act 2008 (Regulated Activities) Regulations 2014). These are **separate legal regimes with separate registers, inspection frameworks and force classes** — see the "Health & social care — adult (CQC)" section (STD-01 to STD-13) versus the new "Health & social care — children & young people (Ofsted)" section (STD-60 onward). **Agents citing a standard must first establish the tenant's care setting (adult / children's / dual-registered) and cite only the regime that matches it.** A small number of children's homes are **dual-registered** with both Ofsted and CQC where they also deliver a CQC-regulated healthcare activity (e.g. nursing care) at the same location — in that scenario BOTH sections may apply to the same home for different aspects of care; treat this as a flag for human review, not something an agent resolves alone. Never apply adult-social-care CQC guidance (e.g. NICE SC1, NG67) to a children's home without independent verification — see STD-63 below.

## Health & social care — adult (CQC)
| ID | Standard | Class | Relevance |
|---|---|---|---|
| STD-01 | Health and Social Care Act 2008 | LEGAL | Overarching framework for regulated activities. |
| STD-02 | Health and Social Care Act 2008 (Regulated Activities) Regulations 2014 | LEGAL | The regulations CQC enforces. |
| STD-03 | CQC Regulation 12 — Safe care and treatment | LEGAL | Safe medicines management; a core inspection focus. |
| STD-04 | CQC Regulation 17 — Good governance | LEGAL | Accurate, complete, contemporaneous records; audit. |
| STD-05 | Human Medicines Regulations 2012 | LEGAL | Supply/administration of medicines. |
| STD-06 | Misuse of Drugs Act 1971 | LEGAL | Controlled drugs. |
| STD-07 | Misuse of Drugs Regulations 2001 | LEGAL | CD **register** (reg 19) and **destruction** (reg 27) are legal duties; safe custody. ⚠️ **Do not over-cite:** the **witness/second-signatory at administration** and the **running balance** are CQC/NICE **GOOD-PRACTICE** (STD-09/STD-10), *not* MDR 2001 clauses. Cite both, with the correct force each. |
| STD-08 | Mental Capacity Act 2005 | LEGAL | Consent, best interests, covert administration. |
| STD-09 | CQC medicines guidance (current) | GOOD-PRACTICE | How CQC expects medicines to be managed. |
| STD-10 | NICE — managing medicines in care homes (SC1) | GOOD-PRACTICE | Primary medicines-in-care-homes reference. **⚠️ CORRECTED 2026-07-23:** the earlier "ADULT SETTING ONLY" label was WRONG — it was an inference, not verified. Fresh verification against SC1's own recommendation text (1.12.1, 1.13.5, 1.14.6, 1.14.8, 1.17.4) confirms SC1 **explicitly addresses children's homes**, with a deliberately *softer* controlled-drug **storage** standard for them than for adult care homes (1.12.1). So SC1 applies to BOTH regimes for record/administration content. It does **not** settle the children's-home CD **administration-witness** question (no source does — see STD-65). Consent still differs by age regardless of SC1 (see STD-63). |
| STD-11 | NICE **NG67** — managing medicines for adults receiving social care in the community | GOOD-PRACTICE | Community/supported-living equivalent. Models "medicines support" levels (prompting → full administration) — relevant to Care One OS's supported-living scope. **CONFIRMED ADULTS 18+ ONLY** — NG67's scope explicitly covers adults aged 18 and over; it does **not** cover children/young people (see STD-63). |
| STD-12 | NICE NG5 — medicines optimisation | GOOD-PRACTICE | Safe/effective medicines use. |
| STD-13 | RPS — professional guidance on handling medicines in social care | GOOD-PRACTICE | Practical handling standard. |

## Health & social care — children & young people (Ofsted)
Regulator: **Ofsted** (Office for Standards in Education, Children's Services and Skills), not CQC, for the residential-care aspects of a children's home. CQC has a role **only** where the same location also delivers a CQC-regulated healthcare activity (e.g. nursing care), requiring **dual registration** — see STD-61.

| ID | Standard | Class | Relevance |
|---|---|---|---|
| STD-60 | Care Standards Act 2000 | LEGAL | Primary Act establishing the "children's home" registration category and Ofsted's statutory role as regulator (functions transferred from the former National Care Standards Commission). Enabling Act for STD-62. |
| STD-61 | Ofsted & CQC joint registration guidance — "Children's homes and health care: registration with Ofsted or CQC" | ASSURANCE / GOOD-PRACTICE (guidance, not itself a statutory instrument) | Confirms: a children's home registers with Ofsted; it must **additionally** register the same location with CQC only if it delivers a distinct CQC-regulated activity there (commonly nursing care); the *same* regulated activity cannot be double-registered with both regulators ("no double accountability"); CQC and Ofsted operate under an MOU. **Flag dual-registration scenarios for human review** — do not assume single-regulator status for any given home. |
| STD-62 | Children's Homes (England) Regulations 2015 (SI 2015/541, as amended) | LEGAL | Made under the Care Standards Act 2000. Sets 9 "quality standards" (regs 4–15) plus related-matters regs (16–25). **Regulation 23 ("Medicines")** is the specific medicines duty: medicines stored securely to prevent unsupervised child access; medicine prescribed for a child administered only to that child; a record kept of administration to each child; provision for supported self-administration where safe/appropriate. **Regulation 10 ("Health and well-being standard")** requires each child be registered with a GP and dentist and have access to medical/dental/nursing/psychiatric/psychological advice and treatment — the wider context medicines sit within, not itself a medicines-administration clause. **Regulation 6 ("Quality and purpose of care")** covers externally arranged/provided health-related care (approval by placing authority, delivery by appropriately skilled/supervised persons, keeping the child's GP informed) — relevant if Care One OS tenants involve external clinicians. **Regulation 40** requires notification to Ofsted (and placing authority) of serious incidents including certain serious illness/accident/hospital admission — relevant to any incident/adverse-event logging feature. Do not conflate reg numbers across the adult CQC regime (STD-01–04) and this regime; they are different instruments with different numbering. |
| STD-63 | Consent for medicines in children — Gillick competence, Fraser guidelines, parental responsibility | GOOD-PRACTICE / COMMON LAW (judge-made law, not a statute or NICE guideline) | **This is a genuine gap in the existing register, which cited MCA 2005 only.** MCA 2005 capacity provisions apply from **age 16**; they do **not** apply to the under-16s who are the core of a children's-home population (e.g. Neptune House, ages 7–14). For under-16s the applicable framework is the common-law **Gillick competence** test (does the child have sufficient intelligence/maturity/understanding to consent to the specific treatment?) — if not Gillick-competent, consent is given by a person with **parental responsibility** (only one such person's consent is legally required). **Fraser guidelines** are a narrower, related test that applies only to contraception/sexual-health treatment — NOT general medicines administration; do not use "Fraser" as a general synonym for "Gillick" in the product. For 16–17-year-olds the **primary statutory basis is the Family Law Reform Act 1969 s.8** (added 2026-07-23; verified against legislation.gov.uk) — a 16/17-year-old's consent to medical/surgical/dental treatment "shall be as effective as it would be if he were of full age," with no parental consent needed. This was **missing from the register entirely** and matters: for 16–17s consent rests first on FLRA 1969 s.8, with MCA 2005 ss.2–3 supplying the *capacity test* (MCA s.2(5): the Act does not apply under 16). The precedence when a capacitous 16/17-year-old **refuses** treatment (case law *Re W*, *Bell v Tavistock*) is **UNVERIFIED — mark for legal review** rather than asserting either applies exclusively. Gillick is generally understood to "fall away" at 16 in favour of s.8, but this too needs case-law-literate legal review. Implication: Care One OS's consent/authorisation model for a children's-home tenant must capture (a) parental-responsibility holder(s) and their consent, separate from (b) any Gillick-competence assessment recorded for the child, and must not reuse the adult MCA "best interests / covert administration" workflow as-is for under-16s. |
| STD-64 | NICE NG205 — Looked-after children and young people | GOOD-PRACTICE | Published Oct 2021; replaces the older PH28 (2010); underpins quality standard QS31. Broad guidance on care/placements for looked-after children — **not** a medicines-specific guideline equivalent to SC1/NG67. Its applicability to Care One OS's medicines workflows is limited/indirect; do not treat as filling the "no children's NG67/SC1 equivalent" gap. |
| STD-65 | Misuse of Drugs Regulations 2001 in children's homes | LEGAL (register/destruction duties are legal only for the classes of person the Regulations actually name; a children's-home CD register is otherwise GOOD-PRACTICE, mirroring STD-07's adult caveat) | Same caveat as STD-07 applies here: don't over-cite. Additionally — **the Misuse of Drugs (Safe Custody) Regulations 1973 do not apply to children's homes** (per practitioner/professional-guidance sources reviewed; **UNVERIFIED against the primary 1973 SI text itself** — the SI's schedule of applicable premises was not independently confirmed clause-by-clause). Practical children's-home CD storage (locked cabinet/room, witnessed administration) is therefore driven by **local policy and inspection expectation**, not the same statutory safe-custody duty that binds registered pharmacies. Treat any "CD safe custody is a legal duty in a children's home" claim as unverified pending direct confirmation from the 1973 SI. |

*Sources for STD-60–STD-65 are in [SOURCE-REGISTER.md](SOURCE-REGISTER.md).*

## Clinical safety & assurance
| ID | Standard | Class | Relevance |
|---|---|---|---|
| STD-20 | DCB0129 — clinical risk management for manufacturers of health IT | NHS-STD | We manufacture health IT: hazard log, safety case, CSO oversight. |
| STD-21 | DCB0160 — clinical risk management for deploying/using health IT | NHS-STD | Applies to deploying organisations; we support their obligations. |
| STD-22 | NHS DTAC (Digital Technology Assessment Criteria) | ASSURANCE | Clinical safety, data protection, tech security, interoperability, usability/accessibility. Confirm current form/version in Source Register. |
| STD-23 | Data Security and Protection Toolkit (DSPT) | ASSURANCE | Annual data-security assurance. |

## Interoperability & terminology
| ID | Standard | Class | Relevance |
|---|---|---|---|
| STD-30 | dm+d (NHS dictionary of medicines and devices) | NHS-STD | Standard for electronic exchange of medicines info (VTM/VMP/AMP/VMPP/AMPP). |
| STD-31 | SNOMED CT | NHS-STD | Clinical terminology; dm+d concepts are SNOMED CT. |
| STD-32 | GP Connect (Access Record, Structured, Send Document) | ASSURANCE | Direct-care access to GP data; requires NHS onboarding/assurance. |
| STD-33 | NHS FHIR / UK Core | NHS-STD | Interoperability payload format where applicable. |
| STD-34 | GS1 standards (GTIN) | GOOD-PRACTICE | Barcode product identification; needs separate mapping to dm+d. |
| STD-35 | Organisation Data Service (ODS) codes | NHS-STD | Identifying organisations in exchanges. |

## Information governance & security
| ID | Standard | Class | Relevance |
|---|---|---|---|
| STD-40 | UK GDPR | LEGAL | Lawful basis, special-category data, rights. |
| STD-41 | Data Protection Act 2018 | LEGAL | UK data-protection law. |
| STD-42 | Common Law Duty of Confidentiality | LEGAL | Confidential patient information. |
| STD-43 | Caldicott Principles | GOOD-PRACTICE | Justify use of confidential info; minimise. |
| STD-44 | OWASP (ASVS / Top 10) | GOOD-PRACTICE | Application security baseline. |

## Accessibility & usability
| ID | Standard | Class | Relevance |
|---|---|---|---|
| STD-50 | WCAG 2.2 AA | ASSURANCE/GOOD-PRACTICE | Accessibility target (and DTAC criterion). |
| STD-51 | NHS service-design principles / content guidance | GOOD-PRACTICE | Clear, accessible clinical UI where appropriate. |

*Add rows as new requirements are found. Do not delete rows — mark superseded ones.*
