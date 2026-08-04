<!--
PROVENANCE
Source: Care-One-OS-UX-Specification.docx (supplied 2026-08-04, kept alongside this file
in docs/care-one-os/FRONTEND3/ as the original).
This markdown is a faithful text conversion of that .docx for reading and diffing in the repo.
The .docx is the master. If the .docx is updated, re-convert rather than hand-editing this file.
-->

CARE ONE OS  •  PRODUCT & UX BLUEPRINT

# Medication Management,
Designed Around Care

A mobile-first, safety-led experience specification for supported living, care homes, children’s services, domiciliary care and pharmacy collaboration.

| DESIGN DIRECTION Quiet Clinical Luxury — calm, exact, human and visibly trustworthy. Safety-critical information is prominent without making the product feel alarmist. |
|---|

| Field | Value |
|---|---|
| Document | UX specification and product blueprint |
| Version | 1.0 — planning baseline |
| Prepared for | Care One OS product, design and engineering |
| Primary stack | Laravel • React • Inertia • Mantine • SQL |
| Status | For prototyping, validation and technical scoping |

Clinical and regulatory note: this specification is a product-design blueprint, not legal, clinical-safety or regulatory approval. A qualified clinical safety officer, data-protection lead and relevant UK assurance bodies must validate the implemented service before deployment.

00  •  PRODUCT SPECIFICATION

# How to Use This Specification

| OUTCOME Give designers, developers, clinical leads and founders one shared definition of the Care One OS experience. |
|---|

## Document map

| Section | Subject |
|---|---|
| 01 | Vision, principles and success measures |
| 02 | Users, roles and configurable service modes |
| 03 | Information architecture and page inventory |
| 04 | Global shell, responsive model and navigation |
| 05 | Dashboard and operational command centre |
| 06 | Medication round and administration workflow |
| 07 | MAR chart and medicines history |
| 08 | Person record and medication profile |
| 09 | PRN workflow and outcome review |
| 10 | Exceptions, missed doses and escalation |
| 11 | Stock, ordering and pharmacy collaboration |
| 12 | Controlled drugs |
| 13 | Shift handover and clinical communication |
| 14 | Manager, audit, compliance and reporting |
| 15 | Administration, permissions and integrations |
| 16 | AI workspace and governance |
| 17 | Design system and interaction language |
| 18 | Accessibility, responsive and offline behaviour |
| 19 | Data, interoperability and technical guardrails |
| 20 | MVP, build sequence and acceptance criteria |
| A | Wireframe inventory |
| B | Regulatory and standards reference map |

| DECISION HIERARCHY Safety first; authoritative medicines data over generated text; server-enforced permissions and immutable audit; complete mobile journeys; configuration never weakens core controls. |
|---|

01  •  PRODUCT SPECIFICATION

# Vision, Principles and Success Measures

| OUTCOME Make every medication action understandable, attributable and recoverable — wherever care is delivered. |
|---|

## Product promise

Care One OS should feel like a composed clinical co-pilot: it shows the next safe action, preserves the reasoning behind every decision and turns complex handovers into a reliable shared record. It is not an elderly-care template. The same foundation must adapt to a young person’s home, supported living, a nursing service or a pharmacy-linked organisation.

## Experience principles

| Principle | UX implication |
|---|---|
| Safety before speed | Never compress away identity, medicine, dose, route, time, reason or confirmation. |
| Calm under pressure | Use hierarchy, whitespace and plain language; reserve urgency for genuine risk. |
| Evidence at the point of action | Show prescription source, instructions, recent administrations and relevant notes in context. |
| One record, many perspectives | Frontline, manager, prescriber and pharmacy views share the same underlying events. |
| Configuration without fragmentation | Terminology and enabled modules vary by service type while core interaction patterns remain consistent. |
| Human-accountable automation | AI may retrieve, summarise and draft; people approve, administer, witness and escalate. |

## North-star measures

Zero ambiguous administration records.
Reduced late and omitted-dose investigation time.
Faster, clearer shift handover.
High completion of PRN outcome reviews.
Reconciled stock and controlled-drug balances.
WCAG 2.2 AA conformance for critical journeys.

02  •  PRODUCT SPECIFICATION

# Users, Roles and Configurable Service Modes

| OUTCOME Let one platform fit different care contexts without exposing users to irrelevant complexity. |
|---|

## Primary personas

| Role | Core jobs |
|---|---|
| Support worker / carer | Prepare and record administrations, capture outcomes, escalate exceptions, complete handover. |
| Senior / shift lead | Monitor rounds, resolve exceptions, witness controlled-drug events, coordinate handover. |
| Registered nurse | Assess clinical context, record richer observations, verify and escalate within scope. |
| Manager / provider lead | Review compliance, investigate events, manage access, monitor trends and assurance. |
| Pharmacy team | Review orders, supply status, prescription queries and reconciliation within agreed access. |
| Family / advocate | Optional, tightly scoped read-only information or approved communications. |
| Administrator | Configure organisations, sites, terminology, policies, roles and integrations. |
| Clinical safety / auditor | Trace hazards, changes, evidence, access and event histories without altering records. |

## Service-mode configuration

| Mode | Default language | Typical emphasis |
|---|---|---|
| Supported living | Person / home / support team | Consent, independence, prompts, leave and self-administration. |
| Residential care | Resident / unit / care team | Rounds, stock, pharmacy cycles, manager oversight. |
| Nursing care | Resident / nurse / unit | Clinical observations, complex regimes, escalation. |
| Children’s home | Young person / home / staff | Consent and capacity context, safeguarding, guardian/prescriber communication. |
| Domiciliary care | Person / visit / care worker | Visit windows, route planning context, offline resilience. |
| Pharmacy | Patient / prescription / supply | Ordering, prescription validation, dispensing and query exchange. |

| CONFIGURATION BOUNDARY The organisation may rename Person, Site and Team, enable optional modules and define escalation routes. It must not remove identity checks, outcome coding, audit capture or controlled-drug witnessing rules. |
|---|

03  •  PRODUCT SPECIFICATION

# Information Architecture and Page Inventory

| OUTCOME Use a stable mental model: Today, People, Medicines, Operations, Assurance and Settings. |
|---|

## Primary navigation

| Area | Purpose | Representative pages |
|---|---|---|
| Today | Immediate work and risk | Dashboard, medication round, tasks, alerts, handover. |
| People | Person-centred record | People list, profile, medicines, allergies, documents, contacts. |
| Medicines | Medication record and workflow | MAR, PRN, prescriptions, stock, orders, returns, disposal. |
| Operations | Cross-shift control | Exceptions, controlled drugs, handover, incidents, pharmacy messages. |
| Assurance | Management and evidence | Reports, audits, compliance, trends, clinical-safety log. |
| Settings | Organisation configuration | Sites, roles, permissions, terminology, integrations, AI policy. |

## 52 primary page templates

| Domain | Templates |
|---|---|
| Access | Sign in; MFA; forgot password; device/session check; organisation/site selection |
| Today | Dashboard; medication round list; round workspace; tasks; alerts; shift handover |
| People | People directory; person overview; medication profile; allergies; documents; contacts; leave/self-administration |
| MAR & PRN | MAR chart; administration detail; correction/addendum; PRN dashboard; PRN administration; outcome review |
| Exceptions | Exceptions queue; missed/late dose detail; escalation; incident linkage; resolution review |
| Stock | Stock overview; medicine stock ledger; ordering; deliveries; returns; disposal; reconciliation |
| Controlled drugs | CD dashboard; register; receipt; administration; transfer; return/destruction; balance discrepancy |
| Pharmacy | Pharmacy workspace; prescription query; order status; message thread; cycle management |
| Assurance | Manager dashboard; reports library; audit pack; KPI drill-down; access audit; clinical-safety log |
| Administration | Organisation; sites; teams; roles; permission matrix; terminology; policy rules; integrations; AI settings |

| MVP TARGET Build 26 core templates first. Secondary detail and configuration views can share patterns and expand after frontline safety workflows are validated. |
|---|

04  •  PRODUCT SPECIFICATION

# Global Shell, Responsive Model and Navigation

| OUTCOME Keep place, person, shift and urgent work visible while preserving generous working space. |
|---|

## Desktop shell

Slim left navigation with labelled icons; collapses to icon rail but never hides the current area.
Top context bar: organisation, site/home, shift, global search, connectivity, notifications and profile.
Page header: title, concise state summary, last sync and one primary action.
Optional right-side context drawer for details, evidence and communication without losing the list.
Command palette for search and navigation; no hidden clinical action shortcuts.

## Mobile shell

Bottom navigation: Today, People, Round, Messages and More.
Sticky identity/status header on administration journeys.
Primary action remains within comfortable thumb reach; destructive actions are separated.
Filters open as full-height sheets with clear Apply and Reset controls.
Drawers become full-screen pages; back navigation preserves filters and scroll position.

## Global states

| State | Required presentation |
|---|---|
| Loading | Skeleton matching final geometry; keep context header visible. |
| Empty | Explain why it is empty and offer the next safe action. |
| Error | Plain-language cause, retry, safe fallback and support reference. |
| Offline | Persistent banner, queued-write count and explicit sync status. |
| No permission | State restriction without revealing sensitive content; give approved route. |
| Conflict | Show both versions, timestamps and who changed what; require deliberate resolution. |

05  •  PRODUCT SPECIFICATION

# Dashboard and Operational Command Centre

| OUTCOME Answer three questions instantly: what is due, what is at risk and what must I do next? |
|---|

## Frontline dashboard anatomy

| Zone | Contents | Interaction |
|---|---|---|
| Shift context | Site, shift, team, last sync, staffing note | Switch only within permissions; confirm context change. |
| Due now | People and medicines due in current window | Start or continue round. |
| Needs attention | Late, omitted, unavailable, declined, observation or stock concerns | Open prioritised exception. |
| Upcoming | Next 2–4 hours grouped by time | Preview; no premature administration unless policy permits. |
| Handover | Unread notes, unresolved risks, acknowledged items | Review and acknowledge. |
| Quick actions | Person search, stock check, incident, pharmacy message | Open focused workflow. |

## Manager variant

Completion by site and shift.
Exception ageing and ownership.
PRN outcome-review compliance.
Low/out-of-stock and order bottlenecks.
Controlled-drug discrepancy status.
Access anomalies and overdue audits.

| DESIGN RULE Avoid “dashboard wallpaper”. Every number must open the records behind it, show its date range and explain whether it is informational or actionable. |
|---|

## Mobile priority

The first viewport shows shift context, due-now count, the highest-priority exception and a single Start round button. Charts and secondary summaries move below operational cards.

06  •  PRODUCT SPECIFICATION

# Medication Round and Administration Workflow

| OUTCOME Make the safe path the shortest path while documenting every deviation. |
|---|

## Round list

Group by time window, not alphabet alone.
Show person photo/approved identifier, room/location only where appropriate, due count and highest risk.
Display In progress across devices to reduce duplicate work.
Provide accessible status text: Due, Early window, Late, Completed, Needs review.

## Administration workspace

| Region | Must show |
|---|---|
| Identity | Photo, full name, date of birth or local identifier, location, alerts. |
| Safety strip | Allergies, intolerances, swallowing needs, relevant observation requirements. |
| Medicine card | Name, form, strength, dose, route, time/window, indication, instructions, source/version. |
| Evidence | Last administration, recent outcomes, current stock, supporting document/link. |
| Action | Administered, not administered, part administered, delayed, self-administered or not required. |
| Confirmation | Selected outcome, quantity used, stock impact, note, time and accountable user. |

## Safe sequence

- Confirm person and care context.
- Review allergies, alerts and the active prescription.
- Check the medicine, dose, route, time and relevant observations.
- Record the administration outcome and required detail.
- Review the confirmation summary.
- Commit once; receive an event receipt and next action.

| HARD STOP Identity mismatch, expired/inactive prescription, unresolved allergy conflict or server validation failure prevents completion and opens an escalation path. AI cannot override a hard stop. |
|---|

07  •  PRODUCT SPECIFICATION

# MAR Chart and Medicines History

| OUTCOME Provide a legible legal record without forcing users through a spreadsheet-sized interface. |
|---|

## Default MAR view

Person header is sticky and includes allergies and active safety alerts.
Date range defaults to current cycle with day/week controls on mobile.
Rows represent medicines and instructions; cells represent scheduled opportunities and recorded outcomes.
Outcome codes always have text labels and an accessible legend.
Selecting a cell opens the complete event record, not an editable inline cell.

## Event detail drawer/page

| Field group | Content |
|---|---|
| Prescription | Medicine, strength, form, dose, route, schedule, indication and version. |
| Event | Scheduled window, recorded time, outcome, quantity, stock movement. |
| Accountability | Recorded by, witnessed by where applicable, device/session and site. |
| Reasoning | Reason code, free-text note, observations, escalation and linked incident. |
| History | Corrections/addenda, previous values, author, timestamp and reason. |

## Corrections

Never overwrite an administration event. A permitted user creates an addendum or correction linked to the original, gives a reason and leaves both records visible in the audit history.

| MOBILE BEHAVIOUR The MAR becomes a medicine-by-medicine timeline with date chips. Horizontal scrolling may support comparison, but the primary reading path must not depend on a wide grid. |
|---|

08  •  PRODUCT SPECIFICATION

# Person Record and Medication Profile

| OUTCOME Keep medication work person-centred, current and connected to consent, preferences and risk. |
|---|

## Profile information architecture

| Tab | Contents |
|---|---|
| Overview | Identity, communication, key contacts, current alerts, next medication and recent events. |
| Medicines | Active, future, paused and ended prescriptions; PRN protocols; review dates. |
| MAR | Current chart and historical cycles. |
| Health & safety | Allergies, intolerances, diagnoses/context, swallowing, observations and escalation plans. |
| Documents | Prescriptions, protocols, letters, consent/capacity and attachments with provenance. |
| Contacts | GP, pharmacy, prescribers, family/guardian and approved communication preferences. |
| Activity | Chronological, filterable record across medication, messages, changes and access. |

## Medication profile controls

A medicine lifecycle uses Draft, Awaiting verification, Active, Paused, Ended and Superseded.
Every activation records source, author, verifier where required, effective dates and version.
Duplicate and interaction warnings are decision support, not automatic clinical conclusions.
Self-administration captures assessed level, storage arrangement, prompts and review date.
Leave/away status explains responsibility, supply and reconciliation.

| TERMINOLOGY The interface uses the organisation’s approved Person/Resident/Young Person label, while audit and API records retain stable canonical concepts. |
|---|

09  •  PRODUCT SPECIFICATION

# PRN Workflow and Outcome Review

| OUTCOME Ensure “when required” medicines have an indication, safe interval, maximum and measurable follow-up. |
|---|

## PRN protocol card

| Required field | UX treatment |
|---|---|
| Indication | Plain-language reason and observable signs. |
| Dose and route | Exact permitted dose/range with unambiguous units. |
| Minimum interval | Show earliest next eligible time based on administrations. |
| Maximum | Rolling and/or calendar-period maximum with current total. |
| Non-drug measures | Prompt what should be tried or considered first, if specified. |
| Outcome plan | Expected effect, review interval, measurement and escalation threshold. |

## Administration journey

- Select the prescribed indication.
- Review last dose, interval and maximum-dose calculation.
- Record relevant symptoms/observations and non-drug measures.
- Administer or record an alternative outcome.
- Create a timed outcome-review task.
- At review, record effectiveness, adverse effects and escalation.

## PRN dashboard

Due outcome reviews first.
Repeated-use pattern by person/medicine.
Ineffective administrations requiring review.
Near-maximum or interval-blocked situations.
Overdue protocol or prescription review.

| HARD RULE A missing or ambiguous PRN protocol is not repaired by generated advice. The system blocks administration where policy requires and routes a query to the responsible clinician/pharmacy. |
|---|

10  •  PRODUCT SPECIFICATION

# Exceptions, Missed Doses and Escalation

| OUTCOME Turn every non-standard outcome into an owned, time-bound and auditable response. |
|---|

## Outcome taxonomy

| Outcome | Minimum capture | Typical follow-up |
|---|---|---|
| Declined | Reason offered, capacity/consent context, attempt, advice | Monitor or escalate per medicine/policy. |
| Unavailable | Stock/source issue, last available dose, order status | Urgent supply/pharmacy contact. |
| Omitted — clinical | Clinical reason, decision maker, instructions | Review/observation. |
| Omitted — operational | Reason and responsible owner | Incident/escalation where threshold met. |
| Late / delayed | Actual time, reason and risk assessment reference | Recalculate later windows where authorised. |
| Part administered | Quantity given/not given and reason | Clinical advice and stock reconciliation. |
| Spat out / vomited | Timing, observed amount if known, symptoms | Seek authorised advice; never suggest redosing. |
| Not required | Scheduled rule/condition not met | No exception if prescription supports it. |

## Exception detail

Risk summary with source rules.
Chronology of attempts, messages and decisions.
Named owner and response deadline.
Escalation pathway and contact method.
Linked incident/safeguarding record where relevant.
Resolution, reviewer and learning note.

| LANGUAGE RULE Never label every non-administration as “missed”. Distinguish clinical, consent, supply and operational outcomes so reporting does not erase context. |
|---|

11  •  PRODUCT SPECIFICATION

# Stock, Ordering and Pharmacy Collaboration

| OUTCOME Keep an explainable balance from receipt to administration, transfer, return and disposal. |
|---|

## Stock workspace

| View | Key information |
|---|---|
| Overview | Low, out, expiring, quarantined, unreconciled and awaiting-delivery items. |
| Medicine ledger | Opening balance plus immutable receipts, administrations, adjustments, returns and disposal. |
| Ordering | Suggested needs, approved quantities, supplier/pharmacy, status and expected delivery. |
| Delivery | Items expected vs received, quantity, batch/expiry where used, discrepancy and receiver. |
| Returns/disposal | Reason, quantity, destination/method, witness where required and evidence. |
| Reconciliation | System balance, physical count, variance, investigation and sign-off. |

## Pharmacy workspace

Shared prescription query queue with status and ownership.
Order lifecycle: Draft, Approved, Sent, Acknowledged, Part supplied, Supplied, Cancelled.
Structured messages linked to person, medicine or order.
Cycle-management view for expected changes, discontinued items and outstanding prescriptions.
Role-scoped access: pharmacy users see only contracted organisations and required data.

| SAFETY CALCULATION Suggested order quantities must expose their inputs: current balance, scheduled demand, PRN assumption, expected delivery and configured buffer. A person approves the order. |
|---|

12  •  PRODUCT SPECIFICATION

# Controlled Drugs

| OUTCOME Provide a register-grade workflow with two-person accountability and immediate balance visibility. |
|---|

## CD register

Separate register by site/location and medicine as policy requires.
Chronological, immutable entries with running balance.
Receipts, administrations, transfers, returns and destruction use dedicated workflows.
Two distinct authenticated users for witness-required actions; self-witnessing is impossible.
Late entries and corrections are explicit linked records, never silent edits.

## CD transaction pattern

- Select person and controlled medicine.
- Review current balance and active instruction.
- Enter transaction quantity and supporting detail.
- Second user independently authenticates and reviews.
- Both confirm the same summary.
- System commits one atomic transaction and displays the new balance.

## Discrepancy workflow

| Stage | Required action |
|---|---|
| Detect | Display expected vs physical balance and freeze casual adjustment. |
| Contain | Follow local policy; restrict transactions if configured and identify responsible lead. |
| Investigate | Review ledger, administration events, receipts, transfers, returns and access. |
| Escalate | Record notifications, external reference and deadlines. |
| Resolve | Document conclusion, authorised correction/addendum and sign-off. |
| Learn | Link incident, corrective action and audit evidence. |

| NON-NEGOTIABLE Controlled-drug entries, witnesses and balances are enforced by the server in a single transaction. Offline completion is disabled unless a clinically assured, conflict-safe design is approved. |
|---|

13  •  PRODUCT SPECIFICATION

# Shift Handover and Clinical Communication

| OUTCOME Replace memory and scattered messages with a structured transfer of unresolved medication responsibility. |
|---|

## Handover board

| Section | Examples |
|---|---|
| Outstanding actions | Outcome reviews, pharmacy calls, observations, stock checks, prescription queries. |
| Exceptions | Unresolved late/omitted doses, repeated refusals, unavailable medicine. |
| Changes | New, amended, paused or ended medicines; changed instructions. |
| Supply | Low stock, partial delivery, fridge/cold-chain issue, return pending. |
| Controlled drugs | Count status, discrepancy, witness-required follow-up. |
| People away / returning | Responsibility, supplied medicines and reconciliation due. |

## Handover workflow

- Outgoing lead reviews auto-assembled candidate items.
- They add context, assign owners and set urgency/deadline.
- Incoming lead reviews by section and asks clarifying questions.
- Incoming lead acknowledges specific items, not a blanket screen.
- Unresolved items remain active after handover and appear on Today.

## Messaging rules

Clinical threads link to a person, medicine, order or event.
Urgent does not equal emergency; show the approved emergency route.
Read receipts do not replace acknowledgement of assigned actions.
Messages cannot silently change prescription or MAR data.
Retention and access follow the underlying record’s policy.

14  •  PRODUCT SPECIFICATION

# Manager, Audit, Compliance and Reporting

| OUTCOME Turn operational records into traceable assurance without hiding risk behind a score. |
|---|

## Manager dashboard

| Metric family | Drill-down questions |
|---|---|
| Administration | Which doses are late/omitted, why, where and who owns follow-up? |
| PRN | Are protocols complete? Are outcomes reviewed? Is use increasing? |
| Stock | Which items are low, out, expiring or unreconciled? |
| Controlled drugs | Are counts complete? Which discrepancies remain open? |
| Prescriptions | Which reviews, verifications or renewals are overdue? |
| Workforce | Which competency, access or training controls require action? |
| System | Are integrations failing, data stale or queued writes unresolved? |

## Audit pack

Filter by organisation, site, person, medicine, event, user and period.
Show source records behind every summary.
Export includes filters, generation time, requesting user and integrity metadata.
Access audit records viewing and exporting of sensitive records.
Corrective actions have owner, due date, evidence and closure approval.

## Reporting principles

Separate activity volume from safety performance.
Show numerator, denominator, exclusions and data-quality caveats.
Suppress or aggregate small cohorts where privacy requires.
Do not rank staff without context and validated methodology.
Use trend and control context before declaring improvement or deterioration.

| ASSURANCE A green metric never overrides an open high-risk event. The dashboard keeps serious unresolved issues visible until appropriately closed. |
|---|

15  •  PRODUCT SPECIFICATION

# Administration, Permissions and Integrations

| OUTCOME Make configuration deliberate, reviewable and safe across organisations and sites. |
|---|

## Administration areas

| Area | Capabilities |
|---|---|
| Organisation & sites | Hierarchy, service mode, locations, pharmacy relationships and time zone. |
| Teams & users | Invitations, employment/contract context, site membership, status and MFA. |
| Roles & permissions | Permission bundles plus field/action-level restrictions and break-glass policy. |
| Terminology | Approved UI labels with preview; canonical data concepts remain stable. |
| Medication policy | Windows, escalation thresholds, witness rules, stock buffers and review intervals. |
| Integrations | Connection status, scope, mapping, last sync, errors and retry controls. |
| AI policy | Enabled use cases, source scope, review rules, retention and monitoring. |
| Audit & export | Configuration history, access history and controlled export policy. |

## Permission model

Deny by default.
Scope by organisation, site, team, person relationship and function.
Separate view, create, administer, correct, approve, witness, export and configure.
Require step-up authentication for high-risk actions.
Record policy version and permission decision with the event.
Break-glass access is time-limited, justified, alerted and audited.

## Integration status card

Each connection shows environment, data scope, last successful sync, data freshness, current incident, retry state, owner and audit trail. Users see whether data is live, delayed, manually entered or unavailable.

16  •  PRODUCT SPECIFICATION

# AI Workspace and Governance

| OUTCOME Use AI to find, summarise and draft — never to prescribe, administer or conceal uncertainty. |
|---|

## Approved starting use cases

| Use case | Output | Required control |
|---|---|---|
| Record summarisation | Concise handover or review summary | Source links, time range, omissions warning, human approval. |
| Policy / guideline retrieval | Relevant passage and navigation | Approved corpus, citation, version and jurisdiction. |
| Exception triage support | Suggested grouping/priority for review | Rules remain authoritative; reviewer confirms. |
| Draft communication | Pharmacy/prescriber query draft | No autonomous sending; editable and attributable. |
| Trend explanation | Plain-language description of report data | Show data range and methodology; no causal claim. |
| Data-quality support | Possible duplicate/incomplete record | User verifies before merge/change. |

## AI workspace anatomy

Purpose header and clear “AI-generated” label.
Context chips show person/site, date range and selected sources.
Answer separates sourced facts, inference and suggested next steps.
Every material statement can open its supporting record or approved guidance.
Feedback captures Useful, Incorrect, Missing context and Safety concern.
Copy/draft action preserves provenance and requires human review.

## Prohibited autonomy

No diagnosis, prescribing, dose change or redosing instruction.
No automatic administration record.
No controlled-drug witness or balance adjustment.
No automatic incident closure, safeguarding decision or escalation suppression.
No generated content presented as dm+d, NICE, GP or prescription truth.

| ARCHITECTURE Keep deterministic safety rules separate from generative services. An unavailable AI service must not prevent medication administration or access to authoritative records. |
|---|

17  •  PRODUCT SPECIFICATION

# Design System and Interaction Language

| OUTCOME Create a distinctive premium clinical interface that stays restrained, readable and fast. |
|---|

## Quiet Clinical Luxury palette

| Token | Value | Use |
|---|---|---|
| Warm clinical ivory | #F6F2E9 | App background and calm negative space |
| Soft porcelain | #FFFCF7 | Cards and primary surfaces |
| Warm mist | #EEEAE2 | Subtle grouping and secondary bands |
| Deep navy | #17243B | Primary headings and high-confidence structure |
| Clinical teal | #176B65 | Primary action, focus and positive emphasis |
| Muted eucalyptus | #7E9B90 | Secondary accents and quiet data visualisation |
| Deep ink | #202A35 | Body text |
| Slate | #626D78 | Secondary text and metadata |
| Warm stone | #D9D4CA | Borders and dividers |

## Typography and geometry

Headings: Manrope in product UI; Aptos Display fallback in documents.
Body/UI: Inter in product UI; Aptos fallback in documents.
Base UI text 16px mobile, 14–15px dense desktop; never shrink critical content to fit.
Radius 12–16px; subtle elevation only for overlays and active workspaces.
Eight-point spacing system with generous section rhythm and compact data rows.

## Component language

| Component | Rule |
|---|---|
| Status badge | Word + icon + restrained tint; colour is never the sole carrier. |
| Alert | Severity, meaning, affected record and next action; persistent when unresolved. |
| Card | One job, clear heading, minimal chrome and no nested card stacks. |
| Table/list | Sticky headers, intentional columns, row actions in predictable location. |
| Drawer | Contextual detail that preserves the user’s place; full screen on mobile. |
| Confirmation | Summarise committed data and consequences; avoid generic “Are you sure?”. |

18  •  PRODUCT SPECIFICATION

# Accessibility, Responsive and Offline Behaviour

| OUTCOME Make every critical journey operable and understandable across ability, device and connectivity. |
|---|

## WCAG 2.2 AA baseline

Keyboard access and visible focus for every action.
Minimum target size 24×24 CSS px; prefer 44×44 for primary mobile controls.
Text and UI contrast meet applicable thresholds.
Headings, landmarks, labels, descriptions and errors are programmatically connected.
Status never relies on colour alone.
Zoom/reflow to 400% without loss of critical content or two-dimensional scrolling except genuine data grids.
Timeouts, re-authentication and session expiry warn users and preserve safe drafts where possible.
Motion is restrained and respects reduced-motion preference.

## Mobile-first breakpoints

| Range | Layout behaviour |
|---|---|
| < 600px | Single column, bottom navigation, full-screen drawers, sticky primary action. |
| 600–899px | Single/two-column adaptive cards, compact side sheet where space permits. |
| 900–1199px | Expanded navigation, list-detail layouts, denser tables. |
| ≥ 1200px | Three-zone workspace where useful; never stretch content without measure. |

## Offline and degraded mode

Show connection and data-freshness continuously.
Cache only clinically and legally approved minimum data with encryption and device controls.
Queue permitted writes with event time, device and user; never pretend queued means committed.
Resolve conflicts explicitly on reconnect.
Disable high-risk flows such as CD witnessing when safe atomic completion cannot be assured.
Provide printable/downtime procedures configured by the organisation.

19  •  PRODUCT SPECIFICATION

# Data, Interoperability and Technical Guardrails

| OUTCOME Build one trustworthy event model that supports current workflows and future NHS connectivity. |
|---|

## Authoritative data hierarchy

| Domain | Expected authority / standard |
|---|---|
| Medicinal product description | NHS dm+d, represented with stable identifiers and mapped display text. |
| Clinical terminology | SNOMED CT where appropriate and licensed/implemented correctly. |
| Prescription | Verified local record and connected source; versioned with effective dates. |
| Person identity | Local master record; PDS linkage only through assured integration and matching. |
| Primary care exchange | GP Connect capabilities where applicable and approved. |
| Authentication | CIS2 or other approved identity route for relevant NHS integrations. |
| Guidance | NICE and approved organisational policy, versioned and cited. |
| Operational event | Care One OS immutable event and audit model. |

## Core engineering guardrails

Laravel owns authorisation, validation, transaction boundaries and audit persistence.
React/Inertia/Mantine renders state but never becomes the security boundary.
Every prescription and configuration has version and effective period.
Medication events are append-only; corrections are linked events.
Use idempotency keys for administration, stock and witness commits.
Store timestamps with UTC and service-local context.
Encrypt in transit and at rest; minimise cached mobile data.
API-ready service layer isolates dm+d, GP Connect and future integrations.

## UK assurance workstreams

Clinical safety management: DCB0129 for manufacture and DCB0160 for deployment responsibilities.
Digital Technology Assessment Criteria (DTAC) where applicable.
UK GDPR, Data Protection Act 2018, DPIA, records of processing and special-category controls.
Cybersecurity, penetration testing, incident response, backups and recovery.
MHRA assessment of medical-device boundary for software and AI functions.
Supplier, hosting, data-residency and business-continuity assurance.

20  •  PRODUCT SPECIFICATION

# MVP, Build Sequence and Acceptance Criteria

| OUTCOME Deliver the smallest clinically credible product, validate it in real workflows and expand without re-platforming. |
|---|

## 26-template MVP

| Release slice | Included templates |
|---|---|
| Foundation | Sign in/MFA, context selection, dashboard, global search, alerts. |
| People | People list, overview, medication profile, allergies/safety, contacts. |
| Administration | Round list, administration workspace, confirmation, MAR, event detail, correction/addendum. |
| PRN & exceptions | PRN administration, outcome review, exceptions queue, exception detail/escalation. |
| Supply | Stock overview, ledger, ordering, delivery/reconciliation. |
| Accountability | CD register/transaction, handover, manager dashboard, audit export. |
| Control plane | Users/roles, service settings, integrations status, AI policy placeholder. |

## Recommended sequence

- Domain model, terminology, identity, tenancy, permissions and audit.
- People, prescriptions and verified medicines data.
- Medication round, MAR and immutable event ledger.
- PRN, exceptions, escalation and handover.
- Stock, ordering and pharmacy collaboration.
- Controlled drugs after dedicated clinical-safety review.
- Manager assurance and exports.
- Integrations and source-grounded AI use cases.

## Definition of done for a critical journey

Happy, exception, error, offline, timeout, conflict and no-permission states designed.
Keyboard, screen-reader, zoom and touch-target testing passed.
Server-side permission and validation tests passed.
Immutable audit record and correction path verified.
Clinical-safety hazards and mitigations reviewed.
Privacy, security and data-retention decisions recorded.
Representative frontline users complete the journey on mobile.
Monitoring, support and downtime behaviour documented.

| GO-LIVE GATE No pilot should rely on prototype assumptions. Complete clinical-safety, information-governance, security, accessibility, regulatory and operational assurance for the intended deployment context. |
|---|

A  •  PRODUCT SPECIFICATION

# Wireframe Inventory and Prototype Coverage

| OUTCOME Maintain a direct bridge from this specification to the approved interactive concept screens. |
|---|

| # | Concept | Prototype file |
|---|---|---|
| 01 | Frontline dashboard | careone-dashboard-wireframe.html |
| 02 | Medication round | careone-medication-round-wireframe.html |
| 03 | MAR chart | careone-mar-wireframe.html |
| 04 | Person profile | careone-person-profile-wireframe.html |
| 05 | PRN workflow | careone-prn-wireframe.html |
| 06 | Missed doses / exceptions | careone-missed-doses-wireframe.html |
| 07 | Stock and pharmacy | careone-stock-pharmacy-wireframe.html |
| 08 | Controlled drugs | careone-controlled-drugs-wireframe.html |
| 09 | Shift handover | careone-handover-wireframe.html |
| 10 | Manager and compliance | careone-manager-compliance-wireframe.html |
| 11 | Administration and integrations | careone-admin-integrations-wireframe.html |
| 12 | AI workspace | careone-ai-workspace-wireframe.html |

## Prototype review checklist

Use fictional data only.
Review at 390px, 768px, 1024px and 1440px widths.
Test normal and long names, instructions and notes.
Exercise every status, validation error and empty state.
Confirm one-handed primary actions and safe destructive-action placement.
Run frontline scenario testing before visual refinement.

B  •  PRODUCT SPECIFICATION

# Regulatory and Standards Reference Map

| OUTCOME Use primary sources to drive formal requirements, hazard analysis and assurance — not interface decoration. |
|---|

| Owner | Relevance | Primary source |
|---|---|---|
| CQC | Medicines administration records and electronic MAR guidance | cqc.org.uk/guidance-providers/adult-social-care |
| CQC | Controlled drugs in care homes | cqc.org.uk/guidance-providers/adult-social-care/controlled-drugs-care-homes |
| NICE SC1 | Managing medicines in care homes | nice.org.uk/guidance/sc1 |
| NICE NG67 | Managing medicines for adults receiving social care in the community | nice.org.uk/guidance/ng67 |
| UK legislation | Children’s Homes Regulations 2015, Regulation 23 | legislation.gov.uk/uksi/2015/541/regulation/23 |
| NHS dm+d | Dictionary of medicines and devices | digital.nhs.uk/services/terminology-and-classifications/dm-d |
| NHS GP Connect | Interoperability capabilities and assurance | digital.nhs.uk/services/gp-connect |
| NHS clinical safety | DCB0129 and DCB0160 standards | digital.nhs.uk/services/clinical-safety |
| DTAC | Digital Technology Assessment Criteria | transform.england.nhs.uk/key-tools-and-info/digital-technology-assessment-criteria-dtac/ |
| ICO | UK GDPR and special-category health data | ico.org.uk/for-organisations/uk-gdpr-guidance-and-resources/ |
| MHRA | Software and AI as a medical device | gov.uk/government/publications/software-and-artificial-intelligence-ai-as-a-medical-device |
| W3C | Web Content Accessibility Guidelines 2.2 | w3.org/TR/WCAG22/ |

## Governance note

Requirements must be confirmed against the latest source, the intended care setting, local policy, contractual obligations and the implemented risk classification. Competent professional review is required; this document deliberately avoids claiming compliance or regulatory approval.

| NEXT DECISION Move from UX planning into a clickable design-system prototype: start with the global shell, frontline dashboard and medication round, then validate the workflow with support workers, nurses, managers and pharmacy partners. |
|---|

