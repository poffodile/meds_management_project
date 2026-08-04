# Care One OS — merged plan

**The two specifications, joined into one.** Where they disagree, the disagreement is
recorded as a numbered conflict rather than silently resolved.

**Visual version of this plan:** https://claude.ai/code/artifact/8d49b4f3-b17a-439c-9a92-a5645f0d5a39

**Sources merged here**
- [CARE-ONE-OS-UX-SPECIFICATION.md](../FRONTEND3/CARE-ONE-OS-UX-SPECIFICATION.md) — the `.docx`, v1.0. Referred to below as **Spec A**.
- [CARE-ONE-OS-VISUAL-AND-PAGE-SPEC.md](CARE-ONE-OS-VISUAL-AND-PAGE-SPEC.md) — the visual direction, roles and 11 pages. **Spec B**.

The `.docx` supplied on 2026-08-04 was byte-identical to the copy already in the repo
(same md5), so there is no third document. This is the complete set.

Written 2026-08-04.

---

## 1. The resolution that makes the navigation question go away

Three apparently different answers — Spec A's six navigation areas, Spec B's eleven
pages, and "I'd rather have four or five" — are answers to **different questions**.

**A page is not a menu item.** Eleven pages exist. A support worker *navigates* to five
of them. The rest are reached in context: you tap a person to open their profile, and
you open the MAR from inside that profile. Reaching the MAR from a menu means losing the
person you meant.

So the sidebar is not a list of every page. It is the small set of places someone starts
from, and it changes by role — which is the rule Spec B already sets for the Home page:
*same structure, change the information shown, the order, the buttons and the permissions.*
That rule applies to navigation as much as to a dashboard.

### The sidebar, by role

| Role | Sidebar |
|---|---|
| **Support worker** *(first to build)* | Today · Medication round · Missed doses · People · Handover |
| **Shift lead** | the above **+** Controlled drugs · Stock |
| **Care manager** | the above **+** Reports & audit |
| **Administrator** | everything **+** Administration |
| **Pharmacist** | **a different set entirely** — supply, queries, reconciliation; no medication round |

The pharmacist is the one role that is *not* "carer plus extras". Modelling them as an
expansion of the carer's navigation would be wrong.

Nothing is deleted. Today's long menu of pages is not useless — it belongs to the
administrator, and to us as the people building this.

---

## 2. Where the eleven pages live

Sidebar destinations are marked ●.

| Page | Route | How you reach it |
|---|---|---|
| ● Today | `/frontend4` | Sidebar. Role decides what leads — the round, or compliance |
| ● Medication round | `/medication-round` | Sidebar |
| Administering to one person | `…/person/:id` | From the round queue. Never a menu item |
| ● Missed doses | `/missed-doses` | Sidebar, with a count — it is work that ages |
| ● People | `/people` | Sidebar |
| Person profile | `/people/:id` | By tapping a person, from anywhere they appear |
| MAR chart | `/people/:id/mar` | A tab inside the profile |
| ● Handover | `/handover` | Sidebar — start and end of every shift |
| ● Controlled drugs | `/controlled-drugs` | Sidebar for lead and above; a carer meets it inside the round |
| ● Stock | `/stock` | Sidebar for lead and above; a carer sees only "stock remaining" |
| ● Reports & audit | `/reports` | Sidebar for manager and above |
| ● Administration | `/admin` | Sidebar for administrators |
| Sign in & location | `/login` | Not navigation — it is how you arrive |

---

## 3. Standing alone in any setting

The requirement — usable in any healthcare setting or company — is a build constraint,
not a positioning line. Four things must never be hard-coded:

1. **Nouns come from configuration.** Person / Resident / Young person / Client /
   Patient / Service user. The screen asks configuration what to call someone; the
   database keeps one stable concept underneath, so audit records never drift with the
   label.
2. **Nothing assumes a building.** No assumption of a room, a unit or a care home. In
   domiciliary care a person has a visit window; in supported living, a flat. Location is
   a field that may be empty, not a fact.
3. **Modules switch off cleanly.** A service with no controlled drugs, no pharmacy link
   and no witnessing gets a composed screen, not a broken one.
4. **Regulation is a swappable layer.** CQC, NICE and the Children's Homes Regulations are
   the *England* configuration. Keep them in a policy layer so another jurisdiction swaps
   the rules without touching the workflow.

**What configuration must never weaken:** identity checks, outcome coding, audit capture
and witnessing rules. Spec A says this outright, and it is what stops "configurable"
becoming "unsafe".

---

## 4. The eight conflicts

### C1 — What can happen to a dose 🔴 *the one that matters most*

Three vocabularies for the single most important thing the product records.

| Source | Outcomes |
|---|---|
| **Spec A** | Declined · Unavailable · Omitted (clinical) · Omitted (operational) · Late/delayed · Part administered · Spat out/vomited · Not required |
| **Spec B** | Given · Refused · Asleep · Away · Unavailable · Vomited · Clinical instruction to omit · Other |
| **Database today** | `A` given · `S` asleep · `R` refused · `W` withheld · `N` not available · `O` other |

**Recommendation:** Spec A's taxonomy, because it is the only one that separates
*clinical* from *operational* omission — and its own rule says never to label every
non-administration as "missed", since that erases the context reporting depends on. Add
**asleep** and **away** from Spec B; both are real and neither is covered. That gives ten
outcomes and needs three or four new codes in the database **before** the round is built.

**Why it can't wait:** this is written into every dose ever recorded. Changing it later
means migrating live clinical data.

### C2 — What gets built first

- **Spec A:** domain model, terminology, identity, tenancy, permissions and audit first; *then* people and prescriptions; *then* the round.
- **Spec B:** build the Medication Round first, show it, then add gradually.

**Recommendation:** Spec B, with one borrowing. The foundations Spec A wants are largely
already built — permissions, tenancy and an immutable audit trail exist and work. What is
genuinely missing is the **role model**, and that is worth settling before the round
rather than after, because every screen reads from it.

### C3 — What a person is called ✅ *settled 2026-08-04 — the original recommendation was wrong*

My recommendation was "person" as the canonical concept. **That was wrong for this
codebase, and the owner's answer is right.**

Two reasons:

1. **The database already calls them clients.** `client_id` appears in **37 tables**, and
   there are dozens of `client_*` tables — `client_alerts`, `client_care_plans`,
   `client_consents`, `client_goals`, `client_mental_capacities`, and more. Making
   "person" canonical would mean renaming the schema for no gain.
2. **"People" is ambiguous in this system, because staff are also people.** A `people`
   table or a `/people` route would be genuinely unclear about whether it means the people
   receiving care or the people giving it. "Client" has no such ambiguity here.

**Settled:** `client` is the canonical concept — in routes, database and audit. The
**display label stays configurable** with "Client" as this organisation's default, so a
children's service can still show "Young person" without the schema changing. Routes are
`/clients/:id`, exactly as Spec B had them.

### C4 — Which roles exist

- **Spec A:** eight personas, including **registered nurse** and **clinical safety / auditor**.
- **Spec B:** five groups for v1 — support worker, shift lead, care manager, pharmacist, system administrator. Neither of the above appears.

**Recommendation:** build the five, but **reserve the auditor now**. A read-only role that
sees everything and alters nothing is very hard to retrofit once permissions are written
around five roles that can all edit something. The registered nurse can wait — clinically
that is a shift lead with a wider scope.

### C5 — How the person profile splits

- **Spec A:** seven tabs; allergies live inside *Health & safety*.
- **Spec B:** eight tabs; *Allergies* and *PRN protocols* each get their own.

**Recommendation:** Spec B. Allergies deserve their own tab because they are what someone
checks under time pressure. Low stakes either way.

### C6 — How big the first release is

- **Spec A:** a 26-template MVP across seven release slices.
- **Spec B:** eleven pages, one at a time.

**Recommendation:** these count different things — 26 templates includes drawers,
confirmations and detail views living *inside* the 11 pages. Not a real conflict, but pick
one unit so "how far are we" has an answer. **Eleven pages** is the more honest one.

### C7 — Controlled drugs when the network drops

- **Spec A:** offline completion is disabled unless a clinically assured, conflict-safe design is approved; never pretend queued means committed.
- **Spec B:** silent on offline.

**Recommendation:** Spec A, without argument. Two people confirming a controlled-drug
balance against a queue that has not committed is how a register goes wrong. The feature
refuses rather than degrades.

### C8 — How far AI is allowed to go

- **Spec A:** a full governance section — approved use cases, source links, and prohibited autonomy (no prescribing, no automatic administration record, no witnessing, no closing an incident).
- **Spec B:** AI appears only as a handover-summary drafter and a settings entry.

**Recommendation:** Spec A's governance applies product-wide, not just to handover. Its
architecture rule matters most: **an unavailable AI service must never block administering
a medicine.**

---

## 5. The roles that already exist

Checked against the live database on 2026-08-04, not assumed. The model is **two tiers**.

### Tier 1 — `user_type` on the `user` table

| Code | Users | Means |
|---|--:|---|
| `N` | 281 | Staff / support worker — the bulk of the system |
| `A` | 75 | Admin |
| `M` | 45 | Manager |
| `CM` | 11 | Care / company manager |
| `O` | 2 | Owner |

### Tier 2 — `access_level`, defined per home

**82 access levels across 46 homes, using 40 different names** for what is really about
five roles. The names each home invented include:

| Name | Homes using it |
|---|--:|
| Staff | 15 |
| Manager | 9 |
| Deputy Manager | 5 |
| Admin · Home Manager · Main Admin · RSW · Senior RSW | 3 each |
| Senior Staff · Manager Access · Team Leader · Support Worker · Floating Support · Default Staff Access | 2 each |
| Line manager · Owner · Manager Admin · Omega Support Worker · Bank Support Worker · Agency Support Worker | 1 each |

Plus some that are clearly not roles at all: `azure`, `AccessTest`, `acc`,
`Test Access Level`, `Jesse Daniels Level`.

**So the system already has** support worker (in several flavours — permanent, bank,
agency, floating), senior / team leader, deputy manager, manager, admin and owner. That
is four of Spec B's five role groups already present in some form.

**Missing entirely:** pharmacist, registered nurse, and the read-only auditor.

### The real gap: permissions gate pages, not actions — and not the new ones

There are **814 access rights**, each tied to a route. The **old** MAR module is covered
and reasonably finely — `MAR Administer` is a separate right from `MAR Sheet List`, which
is separate again from `MAR Sheet Delete`.

But **the new `/medication/*` React pages have no access-right rows at all.** They are
gated only by `user_type`, and the check in the controllers admits `N, A, M, CM, O` —
which is every user type there is. In other words, **anyone who can log in today can reach
medication management.**

That is what M1.5 has to close, and it is the strongest argument for settling the role
model before building the round rather than after.

> ⚠️ Also noted: the old system has a `MAR Sheet Delete` right. Deleting a MAR sheet is
> destructive. That capability should not be carried into frontend4.

---

## 6. Permissions — the starting position

Enforced on the server. Hiding a button is a courtesy; the check is the feature.

| Action | Worker | Lead | Manager | Pharmacist | Admin |
|---|:--:|:--:|:--:|:--:|:--:|
| Record an administration | ✓ | ✓ | — | — | — |
| Witness a controlled drug | if authorised | ✓ | ✓ | — | — |
| **Correct a clinical record (addendum)** | — | ✓ | **✓** | — | — |
| Reopen a closed round | — | ✓ | ✓ | — | — |
| Receive a delivery | — | ✓ | ✓ | ✓ | — |
| Approve a stock adjustment | — | — | ✓ | — | — |
| Export a report | — | — | ✓ | scoped | — |
| Change a prescription | — | — | request | ✓ | — |
| **Manage staff in own homes** | — | — | **✓** | — | ✓ |
| Define what a role may do | — | — | — | — | ✓ |
| **Destructively overwrite or delete a clinical record** | — | — | — | — | **nobody** |

### On managers editing clinical records — we agree, I labelled it badly

The owner's position: *"we cannot edit — however a manager should be able to edit it, but
there would be a log of what was changed, and the log would always be there."*

**That is exactly the correction model, and it is already built.** `mar_administrations`
carries `is_current`, `supersedes_id`, `superseded_at` and `amendment_reason`. A manager
"edits"; the system writes a new record, links it to the original, keeps the original
visible forever, and records who changed it, when and why. Nothing is lost and nothing is
overwritten.

So the row that previously read *"Alter a clinical record — never"* was badly worded. It
meant **destructive overwrite or deletion**, which stays impossible for everyone. Managers
correcting records through an addendum is allowed, expected, and supported by the schema
today.

### On managers managing staff — yes, with one boundary

Managers can **add, deactivate and assign staff within their own homes**. That is normal
and the two-tier model already scopes by home.

The boundary: **defining what a role is permitted to do stays with the administrator.** If
a manager can both manage staff *and* edit permission bundles, a manager can grant
themselves anything — including witnessing their own controlled-drug entries. Managing
your team and rewriting the permission model are different powers, and only the second one
is dangerous.

Accounts holding historical medication records are **deactivated, never deleted** — Spec B
says this and the audit trail depends on it.

---

## 7. The order

| | Milestone | Note |
|---|---|---|
| ✅ | **M0** Isolated front end | Own bundle and stylesheet, same database, other front ends provably untouched |
| ✅ | **M1** Design system | Ten statuses at three intensities, type scale, cards, buttons, forms, tables, icons |
| ☐ | **M1.5** Role model + outcome vocabulary | C1 and C4 turned into a permission map and outcome codes. Small, and it stops the round being rebuilt |
| ☐ | **M1.6** Role-gated sidebar | The five-item carer navigation, replacing six areas shown to everyone |
| ☐ | **M2** Medication round | Queue, person summary, medicine list, recording — then stop and show it |
| ☐ | **M3** Round, gradually | PRN → witnessing → stock deduction → sign-off, one at a time |
| ☐ | **M4+** | Person profile → MAR → missed doses → stock → controlled drugs → handover → reports → administration |

M1.5 and M1.6 are new, and they are the change this merge produced: two small pieces that
belong *before* the round rather than after it.

---

## 8. Superseded

[FRONTEND4-MILESTONES.md](FRONTEND4-MILESTONES.md) still holds the database track (D1–D6)
and the detail of M0/M1. Its page plan and build order are superseded by this file where
the two differ.
