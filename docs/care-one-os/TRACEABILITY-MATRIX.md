# Care One OS — requirements traceability matrix

Links every requirement to its source, the page/feature that implements it, the code, the test that proves it, and its status. The `care-one-orchestrator` maintains this during each page cycle; no page is "done" until its rows are complete and status is **Verified** (or explicitly deferred with sign-off).

**Status values:** `Not started` · `In design` · `Implemented` · `Verified` · `Deferred (approved)` · `Blocked (human review)`.

| Req ID | Requirement (short) | Source (Std ID) | Force | Page / feature | Implementation (file:area) | Test / evidence | Status | Notes / human review |
|---|---|---|---|---|---|---|---|---|
| REQ-MED-02 | Resident identity visible during administration | STD-03 | LEGAL | Medication Round | _to fill_ | _to fill_ | Not started | HAZ-02 |
| REQ-MED-05 | Given/Refused/Missed/Withheld/Not available outcomes | STD-10 | GOOD-PRACTICE | Medication Round | _to fill_ | _to fill_ | Not started | |
| REQ-MED-06 | Mandatory structured reason for non-administration | STD-10 | GOOD-PRACTICE | Medication Round / Missed Doses | _to fill_ | _to fill_ | Not started | |
| REQ-MED-20 | CD witness / second signatory | STD-07 | LEGAL | Controlled Drugs / Round | _to fill_ | _to fill_ | Not started | HAZ-05; pharmacist review |
| REQ-MED-30 | Append-only log; no destructive edits | STD-04 | LEGAL | All medication pages | _to fill_ | _to fill_ | Not started | HAZ-07 |
| REQ-MED-31 | No hard-delete of clinical records | STD-03/04 | LEGAL | Missed Doses (unresolve) | _to fill_ | _to fill_ | Not started | Known issue in fallback page |
| REQ-MED-50 | dm+d stable identifier stored | STD-30 | NHS-STD | Medication data model | _to fill_ | _to fill_ | Not started | interoperability |
| REQ-MED-70 | GP data never silently overwrites; reconciliation | STD-32 | ASSURANCE | Future GP Connect | _to fill_ | _to fill_ | Not started | HAZ-01; mock only |

*One row per requirement per page. Extend as reviews add requirements. Keep IDs stable.*
