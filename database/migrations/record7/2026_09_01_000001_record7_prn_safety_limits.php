<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Section 2.4 — the structured facts a PRN decision actually needs.
 *
 * WHY prn_max_per_day IS NOT ENOUGH, AND IS NOT TOUCHED.
 * That column was carrying two different rules at once. In this fixture
 * "4" against Dennis's two-tablet paracetamol reads as four administrations,
 * while "8" against Aisha's two-puff inhaler only makes sense as eight puffs.
 * One number cannot mean both, and a limit that is sometimes doses and
 * sometimes units is worse than no limit at all — it reads as enforced.
 *
 * So the column stays exactly as it is, for history and for the legacy front
 * ends that still read it, and nothing in Section 2.4 uses it to decide
 * anything. The new columns say which rule they are, one rule each.
 *
 * A COUNT LIMIT AND AN AMOUNT LIMIT ARE DIFFERENT RULES.
 * "No more than four doses" and "no more than eight puffs" answer different
 * questions and a prescription may carry either, both, or neither. Where both
 * are present both must pass; where neither is present nothing is invented,
 * because a maximum nobody wrote down is a maximum nobody agreed.
 *
 * ROLLING, NOT MIDNIGHT.
 * The period is explicit on the prescription. Four doses at eight in the
 * evening and four more after midnight is eight doses in eight hours, and
 * every one of them inside a calendar-day allowance. The column can still say
 * calendar_day where a prescription genuinely says so, but it has to say it.
 *
 * DOSE IS A NUMBER AND A UNIT, NOT A SENTENCE.
 * "Two puffs" is for a person to read. Nothing may compute from it.
 *
 * All additive and all nullable: every existing row stays valid and no
 * historical record changes meaning.
 */
return new class extends Migration
{
    protected $connection = 'record7';

    public function up(): void
    {
        Schema::connection('record7')->table('record7_prescriptions', function (Blueprint $table) {
            // What one dose may be. Equal min and max is a fixed dose; a range
            // is a variable one ("one or two tablets"). Decimal because half a
            // tablet and 2.5 ml are both real.
            $table->decimal('dose_min', 10, 3)->nullable()->after('dose');
            $table->decimal('dose_max', 10, 3)->nullable()->after('dose_min');
            $table->string('dose_unit', 30)->nullable()->after('dose_max');

            // Which window the limits below are measured over. Explicit,
            // because rolling and calendar are genuinely different rules and
            // guessing between them is how a midnight double-dose happens.
            $table->enum('prn_limit_period', ['rolling_24h', 'calendar_day'])
                ->nullable()->after('prn_min_gap_minutes');

            // How many times it may be given in that window. Null means the
            // prescription does not say, and Record7 will not pretend it does.
            $table->unsignedSmallInteger('prn_max_administrations')
                ->nullable()->after('prn_limit_period');

            // How much of it may be given in that window, in dose_unit. Also
            // null when unstated. Independent of the count above.
            $table->decimal('prn_max_total_amount', 10, 3)
                ->nullable()->after('prn_max_administrations');

            // When somebody should go back and ask whether it worked. The
            // fixture used to assume an hour in the seeder; an assumption in a
            // seeder is not an instruction.
            $table->unsignedSmallInteger('prn_review_after_minutes')
                ->nullable()->after('prn_indication');
        });

        Schema::connection('record7')->table('record7_administrations', function (Blueprint $table) {
            // WHAT WAS ACTUALLY GIVEN, snapshotted here rather than read back
            // through the prescription. A prescription can change next month;
            // what somebody gave this afternoon cannot.
            $table->decimal('dose_amount', 10, 3)->nullable()->after('outcome');
            $table->string('dose_unit', 30)->nullable()->after('dose_amount');
        });

        Schema::connection('record7')->table('record7_prn_follow_ups', function (Blueprint $table) {
            // A SEPARATE AXIS FROM EFFECTIVENESS.
            // "It did not work" and "something about them worried me" are not
            // the same observation, and a medicine can be entirely effective
            // and still produce something worth reporting. Folding one into the
            // other loses whichever mattered.
            $table->boolean('concerning_response')->default(false)->after('outcome');
            $table->string('concern_observed', 500)->nullable()->after('concerning_response');
            $table->string('concern_action_code', 60)->nullable()->after('concern_observed');
        });

        // A range that runs backwards is not a range.
        DB::connection('record7')->statement(<<<'SQL'
            ALTER TABLE record7_prescriptions
                ADD CONSTRAINT record7_prescriptions_dose_range
                CHECK (dose_min IS NULL OR dose_max IS NULL OR dose_min <= dose_max)
        SQL);

        // A limit needs a window to be measured over, and a window with no
        // limit measures nothing.
        DB::connection('record7')->statement(<<<'SQL'
            ALTER TABLE record7_prescriptions
                ADD CONSTRAINT record7_prescriptions_prn_limit_period
                CHECK (
                    (prn_max_administrations IS NULL AND prn_max_total_amount IS NULL)
                    OR prn_limit_period IS NOT NULL
                )
        SQL);

        // An amount limit is meaningless without the unit it counts.
        DB::connection('record7')->statement(<<<'SQL'
            ALTER TABLE record7_prescriptions
                ADD CONSTRAINT record7_prescriptions_prn_amount_unit
                CHECK (prn_max_total_amount IS NULL OR dose_unit IS NOT NULL)
        SQL);

        // A recorded dose is a number AND a unit, or neither.
        DB::connection('record7')->statement(<<<'SQL'
            ALTER TABLE record7_administrations
                ADD CONSTRAINT record7_administrations_dose_pair
                CHECK (
                    (dose_amount IS NULL AND dose_unit IS NULL)
                    OR (dose_amount IS NOT NULL AND dose_unit IS NOT NULL)
                )
        SQL);
    }

    public function down(): void
    {
        DB::connection('record7')->statement(
            'ALTER TABLE record7_administrations DROP CHECK record7_administrations_dose_pair'
        );
        DB::connection('record7')->statement(
            'ALTER TABLE record7_prescriptions DROP CHECK record7_prescriptions_prn_amount_unit'
        );
        DB::connection('record7')->statement(
            'ALTER TABLE record7_prescriptions DROP CHECK record7_prescriptions_prn_limit_period'
        );
        DB::connection('record7')->statement(
            'ALTER TABLE record7_prescriptions DROP CHECK record7_prescriptions_dose_range'
        );

        Schema::connection('record7')->table('record7_prn_follow_ups', function (Blueprint $table) {
            $table->dropColumn(['concerning_response', 'concern_observed', 'concern_action_code']);
        });

        Schema::connection('record7')->table('record7_administrations', function (Blueprint $table) {
            $table->dropColumn(['dose_amount', 'dose_unit']);
        });

        Schema::connection('record7')->table('record7_prescriptions', function (Blueprint $table) {
            $table->dropColumn([
                'dose_min', 'dose_max', 'dose_unit',
                'prn_limit_period', 'prn_max_administrations', 'prn_max_total_amount',
                'prn_review_after_minutes',
            ]);
        });
    }
};
