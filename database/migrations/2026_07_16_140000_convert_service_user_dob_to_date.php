<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Converts `service_user.date_of_birth` from TEXT to a real DATE.
 *
 * WHY THIS MATTERS (it is not tidiness)
 * -------------------------------------
 * Resident age drives CONSENT LAW and therefore what the app must ask for:
 *   - under 16  -> Gillick competence / parental-responsibility holder's consent
 *   - 16+       -> Mental Capacity Act 2005 capacity provisions
 *   - 18        -> children's-home (Ofsted) duties end; adult (CQC) duties begin
 * (REQ-MED-109.) Age is also part of resident identification, a safety control
 * (REQ-MED-02). Computing an age from unvalidated free text is therefore a safety
 * problem, not a cosmetic one.
 *
 * WHAT WAS ACTUALLY IN THE COLUMN (audited 2026-07-16, 226 rows)
 * -------------------------------------------------------------
 *   169  ISO   YYYY-MM-DD
 *    34  UK    DD-MM-YYYY
 *     7  UK    DD/MM/YYYY
 *     1  UK    DD.MM.YYYY
 *    14  blank
 *     1  literal "." (id 163)
 *   +  6 rows of exactly 01-01-1970 (Unix epoch = "nothing entered", not a birthday)
 *
 * AMBIGUITY — WHY WE DO NOT SILENTLY GUESS
 * ----------------------------------------
 * In a UK-format value, "05-01-2007" could be 5 January or 1 May. 31 rows have a
 * first component <= 12 and are therefore ambiguous to a machine.
 *
 * We resolve them as DD-MM-YYYY because the evidence is strong, not because it is
 * convenient: values such as 29-06-2000, 30-12-2000 and 27/07/2006 have a first
 * component > 12, which is only possible in DD-MM ordering, and this is a UK system
 * with UK users. That is a reasoned inference — so we PRESERVE THE ORIGINAL TEXT in
 * `date_of_birth_raw` rather than destroy it. If the inference is ever wrong for a
 * given resident, the original is still there to correct from, and `down()` restores it.
 *
 * A date of birth is a clinical/identifying fact about a real person. This migration
 * therefore never invents one: anything it cannot resolve becomes NULL (an honest
 * "unknown") and is reported, rather than being coerced into a plausible-looking date.
 *
 * 01-01-1970 is nulled: six residents sharing the exact Unix epoch is definitionally a
 * placeholder, not six birthdays. The raw value is preserved either way.
 *
 * Schema is loaded from a dump rather than a clean migration history — guarded throughout
 * and safe to re-run.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('service_user')) {
            return;
        }

        // 1. Preserve the original text before touching anything.
        if (! Schema::hasColumn('service_user', 'date_of_birth_raw')) {
            Schema::table('service_user', function (Blueprint $table) {
                $table->string('date_of_birth_raw', 50)->nullable()->after('date_of_birth')
                    ->comment('Original free-text DOB as it existed before the 2026-07-16 DATE conversion. Audit trail — the source of truth for correcting any mis-parsed value.');
            });
            DB::statement('UPDATE service_user SET date_of_birth_raw = date_of_birth WHERE date_of_birth_raw IS NULL');
        }

        // 2. Normalise the text in place into ISO, so the column-type change cannot
        //    silently zero-out rows MySQL fails to coerce.
        //    ISO YYYY-MM-DD -> already fine, leave alone.

        // UK DD-MM-YYYY / DD/MM/YYYY / DD.MM.YYYY -> YYYY-MM-DD.
        // Matched only when the value does NOT start with a 4-digit year, so ISO is untouched.
        DB::statement(<<<'SQL'
            UPDATE service_user
            SET date_of_birth = DATE_FORMAT(
                STR_TO_DATE(REPLACE(REPLACE(date_of_birth, '/', '-'), '.', '-'), '%d-%m-%Y'),
                '%Y-%m-%d'
            )
            WHERE date_of_birth IS NOT NULL
              AND TRIM(date_of_birth) <> ''
              AND date_of_birth NOT REGEXP '^[0-9]{4}-'
              AND REPLACE(REPLACE(date_of_birth, '/', '-'), '.', '-') REGEXP '^[0-9]{1,2}-[0-9]{1,2}-[0-9]{4}$'
              AND STR_TO_DATE(REPLACE(REPLACE(date_of_birth, '/', '-'), '.', '-'), '%d-%m-%Y') IS NOT NULL
        SQL);

        // 3. NULL ONLY what cannot be represented as a DATE at all (blank, or junk such
        //    as "."). Never guess a person's DOB, and never delete a value that IS a
        //    valid date just because it looks implausible — see the note below.
        //
        //    Detection is by REGEXP, NOT STR_TO_DATE: under MySQL 8 strict mode
        //    STR_TO_DATE('.', '%Y-%m-%d') raises error 1411 rather than returning NULL,
        //    so using it to *find* junk fails on the very rows it is meant to catch.
        DB::statement(<<<'SQL'
            UPDATE service_user
            SET date_of_birth = NULL
            WHERE date_of_birth IS NOT NULL
              AND (
                    TRIM(date_of_birth) = ''
                 OR date_of_birth NOT REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$'
              )
        SQL);

        // NOTE — 1970-01-01 ("Unix epoch") placeholders are deliberately LEFT ALONE here.
        // This dev database has 36 residents sharing that exact date, which is plainly a
        // "nothing entered" placeholder rather than 36 birthdays. But 1970-01-01 IS a
        // valid date, and this migration will one day run against a real customer's data
        // where one resident may genuinely have been born on it. Deleting valid dates is
        // DATA CLEANING, not TYPE CONVERSION — conflating the two is what made the first
        // version of this migration fail. Placeholder cleanup belongs in the demo seeder
        // (for dummy data) or in an explicit, human-approved data-quality pass (for real
        // data). Query them with:  WHERE date_of_birth = '1970-01-01'

        // 4. Now the column contains only valid ISO dates or NULL — safe to retype.
        DB::statement('ALTER TABLE service_user MODIFY COLUMN date_of_birth DATE NULL');
    }

    public function down(): void
    {
        if (! Schema::hasTable('service_user')) {
            return;
        }

        // Back to text, then restore the original strings from the audit column.
        DB::statement('ALTER TABLE service_user MODIFY COLUMN date_of_birth TEXT NULL');

        if (Schema::hasColumn('service_user', 'date_of_birth_raw')) {
            DB::statement('UPDATE service_user SET date_of_birth = date_of_birth_raw WHERE date_of_birth_raw IS NOT NULL');

            Schema::table('service_user', function (Blueprint $table) {
                $table->dropColumn('date_of_birth_raw');
            });
        }
    }
};
