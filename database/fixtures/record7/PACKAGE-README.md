# Record7 Section 0 test-data package

This package provides fictional data for building and testing Record7's access and sign-in journey.

It covers Section 0 only:

1. Organisation name
2. Username and password
3. Security verification
4. House selection
5. Role, permission and competency checks
6. First-time activation
7. Password recovery
8. Restricted accounts
9. House switching, locking and sign-out
10. Access audit records

## Files

- `record7-section0-test.sqlite` — portable SQLite database for a working prototype.
- `record7-section0-mock-data.json` — matching data for a design-only or frontend-only preview.
- `schema.sql` — database schema, constraints, indexes and append-only audit triggers.
- `build_test_database.py` — reproducible database and JSON generator.
- `CLAUDE-HANDOFF.md` — exact implementation instructions for Claude.

## Test organisation

Enter `Omega Care Group` on the first screen. Matching should ignore capitalisation and repeated spaces, but the system must not provide a public organisation list.

## Test accounts

| Scenario | Username | Password |
| --- | --- | --- |
| Support worker with two houses | `olivia.carter` | `Record7-Test-Staff-2026!` |
| Service manager with two houses | `daniel.evans` | `Record7-Test-Manager-2026!` |
| Organisation administrator | `priya.nair` | `Record7-Test-Admin-2026!` |
| Medication lead | `sarah.ahmed` | `Record7-Test-MedLead-2026!` |
| Medication administrator with witness/CD access | `noah.williams` | `Record7-Test-MedAdmin-2026!` |
| Read-only reviewer | `maya.thompson` | `Record7-Test-Reviewer-2026!` |
| Agency worker without medication competency | `grace.taylor` | `Record7-Test-Agency-2026!` |
| Suspended account | `ethan.cole` | `Record7-Test-Suspended-2026!` |

The prototype-only verification code is `246810`.

## Safety boundary

Everything in this package is fictional. Test credentials and the fixed verification code are deliberately included for local preview testing and must never be deployed to production.

The SQLite file stores PBKDF2 password hashes rather than plaintext passwords. The matching JSON exposes fake test credentials only so a frontend-only design environment can simulate the journey.

## Rebuild

Run:

```bash
python3 build_test_database.py
```

This recreates both the SQLite database and the matching JSON file.

## Extending later sections

Do not add residents, medicines or MAR data until Section 1 and the relevant clinical sections are planned. Extend this package in versioned steps so design data and working-code data remain consistent.
