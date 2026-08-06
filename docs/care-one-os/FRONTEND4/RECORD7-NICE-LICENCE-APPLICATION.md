# RECORD7 — NICE content licence application (governance record)

**Saved verbatim in substance, supplied by the owner 2026-08-05.** This is the licence
application for using NICE content in RECORD7 / Care One OS via the **NICE Syndication API**.
It is a governance record — it defines what we are permitted to do with NICE content, and the
constraints the product must honour. Where the product design touches NICE content, it must
match this application.

Related: [RECORD7-BUILD-PLAN.md](RECORD7-BUILD-PLAN.md) · [FRONTEND4-NICE-AND-BNF.md](FRONTEND4-NICE-AND-BNF.md).

---

## Security certification

- **Cyber Essentials Plus.** Certificate on file: `Cyber Essentials Plus - Certificate.pdf`.

## Licence

- **Type:** Full (NICE content to be made available to users in a product or service).
- **Product / service name:** Record7 Medication Management, integrated with Care One OS.

## Product description (as declared to NICE)

Record7 is a digital medication management and electronic medication administration record
(eMAR) system developed by **Omega Care Group Ltd** for use within **children's residential
care and related care services**. It supports authorised staff to manage medication records,
administration schedules, stock, omissions, refusals, PRN medication, checks, witnessing and
audit trails. Built around the six recognised rights of medicines administration and
strengthened by Omega's additional safeguard, the **"Right Record"**. It can operate
**standalone** or as an **integrated module within Care One OS**.

Intent: integrate relevant NICE **guidance, quality standards and information for the public**
so authorised users can access current, authoritative guidance from within appropriate
medication and care workflows. This complements, but remains separate from, the planned **GP
Connect** integration (authorised patient information from GP records).

## How NICE content is processed

- Obtained through the **official NICE Syndication API**; stored/indexed securely; searched
  and displayed within Record7 / Care One OS.
- Automated search/matching may identify guidance relevant to the subject being viewed (a
  medication topic, health condition or care activity).
- Where AI is used, it may help find relevant content or produce a **short plain-language
  summary**. AI will **not** train/fine-tune a model on NICE content, and will **not**
  diagnose, prescribe, alter medication, or make an autonomous clinical decision.
- Any AI output is clearly identified as a summary, linked back to the NICE source, with the
  original reference. Users are instructed to consult the complete NICE guidance and apply
  professional judgement.

## How NICE content is displayed

Within clearly-labelled guidance panels: title + reference number; short extracts/snippets
from the API; a concise summary of the relevant section; publication/update date; clear
attribution to NICE; a direct link to the complete current guidance on the NICE website; and a
notice when content has been summarised/selected using automated or AI-assisted processing.
**NICE content is never presented as Omega-authored clinical advice** and never replaces the
full guidance, professional judgement or individual clinical assessment.

## Intended users

Authorised internal Omega users: registered managers and responsible individuals; trained
residential care staff; medication-trained staff; clinical/healthcare professionals; QA,
safeguarding and compliance staff; system administrators. **Information for the public** may
be made available in an appropriate format to children, young people, parents or carers where
relevant — but they get **no** access to staff-only or confidential clinical records. Access
is controlled by role. Future: RECORD7 may be offered to other UK care providers under a
controlled software subscription.

## Other content sources (and their status)

Patient information via authorised **GP Connect**; NHS terminology and coding — **SNOMED CT**
and **dm+d** — "where appropriately licensed"; medication/prescription info entered/verified
by authorised professionals; eMAR records + histories; pharmacy labels/dispensing info;
manufacturer PILs; local policies/procedures/risk assessments; care plans, health plans,
allergy info, individual clinical instructions; relevant NHS England + CQC information; info
entered by authorised staff/managers.

> **Important scope limit:** **BNF, BNF for Children and Clinical Knowledge Summaries (CKS)
> content will NOT be obtained through the NICE Syndication API.** Any future use of those
> sources is subject to **separate** permission, licensing and technical arrangements. →
> Implication for the build: **D2 (coded medicines) and any dosing/interaction data are not
> solved by this NICE licence** — they need dm+d/SNOMED (separate) and, for BNF, a separate
> licence later.

## Countries

Initial implementation within Omega services in **England and Wales**; any expansion stays in
the **UK** unless separate written permission / international licensing is obtained from NICE.

## Quality assurance

NICE content only via the official Syndication API; version/update info used to keep content
current. Controls: clear attribution + direct links; automated checks for updated/replaced/
withdrawn guidance; retention of title/reference/publication info; testing that content maps
to the correct subject/context; clinical/qualified human review of templates, summaries and
higher-risk use cases before release; clear identification of AI/automated summaries;
instructions to consult full guidance before a clinical decision; role-based access + full
audit trails; documented incident reporting + correction procedures; periodic review by
clinical, medication-governance, QA and IG leads; change control, UAT and staff training
before material updates. **The system will not autonomously prescribe, change a medication
instruction, or replace a qualified professional's judgement.**

## Translation

None planned; content initially in English. Any future translation seeks NICE permission
first, with human review and a link back to the original English guidance.

## Commercial model

Developed by Omega initially for its own services. During initial deployment staff do not pay
separately for NICE content, and NICE content is **not sold, sublicensed or offered as a
standalone information product**. RECORD7 may later be offered to other regulated UK care
providers via SaaS subscription / organisational licence (covering platform, implementation,
hosting, security, support, training, workflows, development). NICE content remains an
**attributed supporting resource**, not separately charged and not represented as Omega-owned.
Any external commercial deployment involving NICE content stays subject to NICE approval and
the applicable licence terms.

---

**Constraints this places on the product (the short version for builders):**
1. NICE content always shown in a labelled panel with attribution + a link to the full
   guidance + publication date.
2. AI summaries clearly marked as summaries, sourced, never autonomous, never model-training.
3. NICE never presented as Omega advice; never replaces professional judgement.
4. Role-based access + audit on NICE content, like everything else.
5. **No BNF/BNFc/CKS via this licence.** dm+d/SNOMED are a separate arrangement.
6. Automated checks for withdrawn/updated guidance; keep title/reference/date.
