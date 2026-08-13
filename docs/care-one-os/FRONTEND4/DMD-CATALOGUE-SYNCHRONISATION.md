# Requirement 9a: NHS dm+d catalogue import and synchronisation

Last updated: 13 August 2026

Requirement 9a adds a controlled, repeatable import for an approved NHS dictionary of medicines and devices (dm+d) release. It does not contain, download or fabricate a dm+d release.

## Scope

The importer reads the extracted NHS dm+d XML release and synchronises:

- all five core concept levels: VTM, VMP, AMP, VMPP and AMPP;
- official numeric dm+d/SNOMED identifiers and descriptions;
- explicit current/invalid status;
- VTM/VMP/AMP/VMPP/AMPP hierarchy;
- explicit historic identifier replacement links;
- VMP controlled-drug category, mapped to UK Schedule 1–5;
- the separate official AMPP-to-GTIN mapping file, including multiple GTINs per AMPP;
- release version, date, source filename, file count, row counts and SHA-256 provenance.

It deliberately does not guess:

- a legacy free-text medicine-to-dm+d match;
- that a concept or GTIN is retired merely because it is absent from one supplied file;
- dose rules, interactions, indications or BNF/BNFc guidance;
- form, route or ingredient details by parsing words from a medicine description.

Existing mar_sheets.medicine_id values are preserved. An explicit replacement makes the old catalogue item unavailable for future selection and links it to the current item; it never rewrites a historical prescription.

## Credentials and data acquisition

The NHSBSA publishes dm+d weekly through TRUD. A company-controlled TRUD account, dm+d subscription and TRUD API key are separate from the Omega Care NICE Syndication test key.

- Do not use the NICE key for TRUD.
- Do not commit either key, a release archive or licensed NICE content.
- Do not put keys in browser-side code, logs, screenshots, ordinary email or WhatsApp.
- Requirement 9a does not include an automatic downloader. Download the approved release manually from the official TRUD account and retain its official release metadata/checksum.
- Extract the archive into a private directory outside the web root. The command accepts extracted XML only, rejects symbolic links and does not execute archive content.

The NICE Syndication integration remains a later, separately feature-flagged requirement. Its available six-month test paperwork was still marked draft when reviewed, so it must remain internal-test-only until final signed terms are confirmed.

## Migration

Confirm the environment and database name, take and verify a restorable backup, then preview and apply only:

~~~bash
php artisan migrate --pretend --path=database/migrations/2026_08_13_000007_create_frontend4_dmd_synchronisation.php
php artisan migrate --path=database/migrations/2026_08_13_000007_create_frontend4_dmd_synchronisation.php
~~~

The additive migration creates:

- frontend4_terminology_imports — append-only release/dry-run/failure ledger;
- medicine_catalogue_relationships — hierarchy and explicit replacement links;
- medicine_gtin_mappings — AMPP-to-GTIN mappings.

Do not roll this migration back after a live import without a specific reviewed recovery plan: down() drops imported synchronisation data.

## Dry run

Use the official release version and release date from TRUD:

~~~bash
php artisan frontend4:dmd-import "D:\private\dmd\extracted-release" \
  --version="OFFICIAL_RELEASE_VERSION" \
  --release-date="YYYY-MM-DD" \
  --dry-run
~~~

The dry run parses every XML record, validates identifiers and XML structure, calculates an aggregate SHA-256 checksum and writes only an append-only dry_run ledger record. It does not change medicine_catalogue.

Before applying, compare:

- the release/version/date with the official TRUD metadata;
- the discovered files with the expected dm+d and AMPP/GTIN files;
- the concept and mapping counts with the supplier release summary;
- the recorded SHA-256 with the approved local release directory.

The importer rejects fewer than 10,000 concepts by default. --allow-small exists solely for controlled automated fixtures; never use it to force an incomplete live release.

## Apply

After the dry run is reviewed:

~~~bash
php artisan frontend4:dmd-import "D:\private\dmd\extracted-release" \
  --version="OFFICIAL_RELEASE_VERSION" \
  --release-date="YYYY-MM-DD"
~~~

The catalogue synchronisation is transactional and batched. A failure rolls catalogue changes back and adds a sanitised failed ledger event. Applying the exact same successfully applied checksum again is refused.

Do not run two imports concurrently. Schedule the weekly update in a controlled maintenance process after the release has passed the dry-run/count review.

## Verification

Run:

~~~bash
php artisan test --filter=Frontend4DmdSynchronisationTest
php artisan test --filter=Frontend4PrescriptionLifecycleTest
php artisan test --filter=Frontend4HandoverIncidentTest
php artisan test --filter=Frontend4AssuranceReportingTest
php artisan test --filter=Frontend4ClientLifecycleTest
php artisan test --filter=Frontend4AuthenticationIsolationTest
php artisan test --filter=Frontend4AccessScopeTest
php artisan test --filter=Frontend4PermissionTest
npm test
npm run build
~~~

Then inspect:

- the latest frontend4_terminology_imports applied event and summary;
- counts per dmd_concept_level;
- a known current and invalid product;
- a controlled-drug VMP in each relevant schedule;
- a known AMPP barcode with more than one GTIN where present;
- an explicit historic replacement;
- a pre-existing Frontend 4 prescription to confirm its medicine_id and displayed written label remain unchanged.

The release data itself still needs to be supplied from the approved company TRUD account and loaded locally/staging. Publishing this code does not claim that real catalogue data has already been imported.
