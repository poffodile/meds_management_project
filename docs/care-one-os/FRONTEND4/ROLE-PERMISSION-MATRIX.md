# Frontend 4 role and permission matrix

Last updated: 11 August 2026

This matrix applies only to Care One OS / Frontend 4. Legacy frontends keep their existing authorisation paths. The server is authoritative; navigation and button visibility only mirror these rules.

## Role mapping

| Frontend 4 role | Typical existing access-level names |
|---|---|
| Support worker | Staff, RSW, residential/support worker, bank or agency worker, carer |
| Shift lead | Senior staff, Senior RSW, senior support worker/carer, team leader, nurse |
| Manager | Manager, deputy/home/line manager |
| Administrator | Admin, system/home/main admin, owner |
| No medication access | Finance/account manager and known test levels |

An explicit access-level name that is not mapped is denied by default. An account with no access-level row may use its legacy `user_type` only as a compatibility fallback. Unmapped levels must be reviewed and added deliberately.

## Permission matrix

Permissions inherit upward from Support worker to Shift lead to Manager to Administrator, except for the administrator clinical exclusions shown below.

| Capability | Support worker | Shift lead | Manager | Administrator |
|---|:---:|:---:|:---:|:---:|
| View Today | Yes | Yes | Yes | Yes |
| View medication round | Yes | Yes | Yes | Yes |
| View clients and medications | Yes | Yes | Yes | Yes |
| View MAR | Yes | Yes | Yes | Yes |
| Record an administration | Yes | Yes | Yes | **No** |
| Read stock remaining | Yes | Yes | Yes | Yes |
| Witness a controlled drug | No | Yes | Yes | **No** |
| Correct a MAR record | No | Yes | Yes | **No** |
| Reopen a round | No | Yes | Yes | Yes |
| Receive a delivery | No | Yes | Yes | Yes |
| View controlled-drug register | No | Yes | Yes | Yes |
| Approve stock adjustment | No | No | Yes | Yes |
| View/export reports | No | No | Yes | Yes |
| Manage staff in assigned services | No | No | Yes | Yes |
| Pause, stop or amend a prescription | No | No | Yes | **No** |
| Define roles / manage settings | No | No | No | Yes |

Administrators manage access and configuration but cannot administer medicines, witness controlled drugs, correct MAR records or change prescriptions. This separation prevents an access administrator from granting themselves a route into clinical record changes.

## Implemented route enforcement

| Route | Required permission |
|---|---|
| `GET /frontend4` | `view_today` |
| `GET /frontend4/round` | `view_round` |
| `POST /frontend4/round/record` | `record_administration` |
| `GET /frontend4/clients` | `view_clients` |
| `GET /frontend4/clients/{client}` | `view_clients` |
| `GET /frontend4/clients/{client}/mar` | `view_mar` |
| `POST .../medications/{sheet}/status` | `manage_prescription` |
| `POST .../mar/{sheet}/correct` | `correct_record` |
| `GET /frontend4/start` | `manage_settings` |

Every route remains behind `frontend4.auth`. Permission middleware runs before the controller and records a `permission_denied` event in the append-only Frontend 4 authentication audit. Controllers repeat the permission check as defence in depth. Client, MAR and medication queries continue to require the client and record to belong to the currently selected permitted service; an out-of-service ID returns 404.

## Deliberately deferred

GP, pharmacist, client/family and read-only auditor roles are not inferred from ordinary staff accounts. They require identifiable account types, an approved data scope and an owner-confirmed permission matrix before implementation.

Sidebar entries whose Frontend 4 route has not yet been implemented are hidden rather than linked to a 404 page. Add them back only when both their route and server permission are implemented.
