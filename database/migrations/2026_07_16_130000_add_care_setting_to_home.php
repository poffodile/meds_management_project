<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds `care_setting` (+ `is_dual_registered`) to `home`.
 *
 * WHY
 * ---
 * Care One OS is a SaaS product: one company can run a MIX of houses — children's,
 * supported living, residential — under any names they choose (REQ-MED-108). Which
 * REGULATORY REGIME applies is a property of the HOUSE, and nothing in the schema
 * currently records it:
 *
 *   - children's home        -> Ofsted; Care Standards Act 2000 + Children's Homes
 *                               (England) Regulations 2015 (reg 23 medicines,
 *                               reg 10 GP/dentist, reg 40 notifications)
 *   - adult social care      -> CQC; Health & Social Care Act 2008 (Regulated
 *                               Activities) Regulations 2014 (reg 12, reg 17)
 *
 * Without this flag, nothing can decide which fields to ask for or which standards
 * apply, so REQ-MED-109 (one resident record, common core + CONDITIONAL
 * setting-specific fields) has no input. This column is that input.
 *
 * DO NOT infer the setting from `home.number_of_child` — that is a CAPACITY COUNT
 * left over from the app's children's-home origins, not a type flag. It would be
 * wrong the moment a company adds an adult house.
 *
 * REGULATOR IS DERIVED, NOT STORED. `care_setting` -> regulator is a pure function.
 * Storing the regulator separately would let the two drift apart — the same class of
 * bug that let `is_controlled` contradict `cd_schedule` and flag Warfarin as a
 * Schedule 2 controlled drug (REQ-MED-107).
 *
 * NOTE ON AGE: setting alone does NOT resolve consent. MCA 2005 applies from age 16;
 * Gillick applies under 16 — so a 16-17 year old in a children's home falls under both.
 * Conditional requirements must be evaluated on HOUSE TYPE **+ RESIDENT AGE**
 * (REQ-MED-109). This column supplies only the first half.
 *
 * Left NULL = "not yet declared". NULL is honest and must be handled explicitly by
 * callers; it must NOT be silently treated as either setting. A company declares the
 * setting for each house at onboarding.
 *
 * Schema is loaded from a dump rather than a clean migration history, so every change
 * is guarded and safe to re-run.
 */
return new class extends Migration
{
    /**
     * Controlled vocabulary. Deliberately varchar, not enum — MySQL enum changes are
     * painful migrations, and this list will grow (e.g. domiciliary/community).
     */
    public const SETTINGS = [
        'childrens_home',         // Ofsted — Children's Homes (England) Regulations 2015
        'adult_supported_living', // CQC
        'adult_care_home',        // CQC — residential
        'adult_community',        // CQC — domiciliary / community social care (NICE NG67)
    ];

    public function up(): void
    {
        if (! Schema::hasTable('home')) {
            return;
        }

        Schema::table('home', function (Blueprint $table) {
            if (! Schema::hasColumn('home', 'care_setting')) {
                $table->string('care_setting', 50)->nullable()->after('title')
                    ->comment('Regulatory setting of this house. One of: childrens_home, adult_supported_living, adult_care_home, adult_community. NULL = not yet declared. Regulator is DERIVED from this, never stored.');
            }
            if (! Schema::hasColumn('home', 'is_dual_registered')) {
                // A children's home that ALSO delivers a distinct CQC-regulated activity
                // (e.g. nursing) at the same location is registered with BOTH regulators.
                // Confirmed via joint Ofsted/CQC guidance (STD-61). Per-tenant fact — a human
                // must confirm it; never infer it.
                $table->boolean('is_dual_registered')->default(false)->after('care_setting')
                    ->comment('Also registered with CQC for a distinct regulated activity (e.g. nursing) at this location. Human-confirmed only — never inferred.');
            }
        });

        // Seed the two houses whose setting we have actually established with the owner.
        // Every other house stays NULL until its company declares it — guessing would be
        // worse than an explicit unknown.
        $known = [
            101 => 'childrens_home',         // Neptune House — children & young people
            8 => 'adult_supported_living',   // Aries House — adult supported living
        ];

        foreach ($known as $homeId => $setting) {
            DB::table('home')->where('id', $homeId)->update(['care_setting' => $setting]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('home')) {
            return;
        }

        Schema::table('home', function (Blueprint $table) {
            foreach (['care_setting', 'is_dual_registered'] as $col) {
                if (Schema::hasColumn('home', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
