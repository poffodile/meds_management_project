<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('service_user', function (Blueprint $table) {
            $table->string('suMobility')->after('allergies')->nullable();
            $table->string('suFundingType')->after('allergies')->nullable();
            $table->string('street')->after('allergies')->nullable();
            $table->string('city')->after('allergies')->nullable();
            $table->string('postcode')->after('allergies')->nullable();
            $table->string('relationship')->after('allergies')->nullable();
            $table->string('care_needs')->after('allergies')->nullable();
            $table->string('medical_notes')->after('allergies')->nullable();
            $table->string('em_name')->after('allergies')->nullable();
            $table->string('em_phone')->after('allergies')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_user', function (Blueprint $table) {
            $table->dropColumn('suMobility');
            $table->dropColumn('suFundingType');
            $table->dropColumn('street');
            $table->dropColumn('city');
            $table->dropColumn('postcode');
            $table->dropColumn('relationship');
            $table->dropColumn('care_needs');
            $table->dropColumn('medical_notes');
            $table->dropColumn('em_name');
            $table->dropColumn('em_phone');
        });
    }
};
