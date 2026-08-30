<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Section 2.5, part one — what a medicine is, and where the rules come from.
 *
 * TWO SEPARATE IDEAS, DELIBERATELY NOT MERGED.
 *
 * `is_controlled` stays exactly as it is and keeps driving every gate. It
 * answers "does this need the controlled pathway". `cd_schedule` answers "which
 * schedule is it", which is a different question with different consequences,
 * and it is nullable because a medicine can be flagged controlled before
 * anybody qualified has classified it. Nothing infers a schedule from a name.
 *
 * `service_type` is left completely alone. It is free text holding things like
 * "Supported Living", it is read once to be printed on a screen, and it cannot
 * carry a safety rule. `care_setting` is the rule. A label and a rule are not
 * the same thing and should not share a column.
 *
 * THE DEFAULT IS DELIBERATE. care_setting is nullable, and NULL means "witness
 * required" — see the service class. The harm of a missing witness where one
 * was needed outweighs the friction of an unnecessary one, and a NULL that
 * defaulted to lenient would silently drop the requirement everywhere at once.
 */
return new class extends Migration
{
    protected $connection = 'record7';

    public function up(): void
    {
        Schema::connection('record7')->table('record7_medicines', function (Blueprint $table) {
            // Schedules 2 to 5. Nullable: unclassified is an honest state, and
            // an unclassified controlled medicine is still controlled.
            $table->enum('cd_schedule', ['2', '3', '4', '5'])->nullable()->after('is_controlled');
        });

        Schema::connection('record7')->table('record7_services', function (Blueprint $table) {
            // A registered care home is not a person's own home, and the
            // witness rule that applies to one does not automatically apply to
            // the other. Constrained, because a free-text setting is how you
            // end up with "supported living" and "Supported Living" meaning
            // different things to a rule.
            $table->enum('care_setting', [
                'care_home',
                'childrens_home',
                'supported_living',
                'persons_own_home',
                'other',
            ])->nullable()->after('service_type');

            // A provider may add a control above the minimum. There is
            // deliberately no value here that removes one.
            $table->enum('cd_witness_policy', ['by_setting', 'always'])
                ->default('by_setting')->after('care_setting');
        });

        // Fictional design data, per section 0 of the specification. Three of
        // the four houses are supported living, which is exactly the case the
        // fail-safe rule exists to get right — so the fixture keeps it.
        DB::connection('record7')->table('record7_services')
            ->where('service_type', 'Residential Care')->update(['care_setting' => 'care_home']);
        DB::connection('record7')->table('record7_services')
            ->where('service_type', 'Supported Living')->update(['care_setting' => 'supported_living']);
    }

    public function down(): void
    {
        Schema::connection('record7')->table('record7_medicines', function (Blueprint $table) {
            $table->dropColumn('cd_schedule');
        });

        Schema::connection('record7')->table('record7_services', function (Blueprint $table) {
            $table->dropColumn(['care_setting', 'cd_witness_policy']);
        });
    }
};
