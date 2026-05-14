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
        Schema::table('staff_report_incidents', function (Blueprint $table) {
            $table->unsignedBigInteger('dynamic_form_builder_id')->nullable()->after('client_id');
            $table->unsignedBigInteger('dynamic_form_id')->nullable()->after('dynamic_form_builder_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff_report_incidents', function (Blueprint $table) {
            //
        });
    }
};
