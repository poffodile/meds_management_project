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
        // Rename the column
        Schema::table('pre_invoices', function (Blueprint $table) {
            $table->renameColumn('rate', 'current_rate');
        });

        // Remove the comment by modifying column directly
        DB::statement("ALTER TABLE `pre_invoices` MODIFY `current_rate` DECIMAL(10,2) NULL");

        // Drop unnecessary columns
        Schema::table('pre_invoices', function (Blueprint $table) {
            $table->dropColumn(['hours_per_week', 'expenditure_type', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rename the column back
        Schema::table('pre_invoices', function (Blueprint $table) {
            $table->renameColumn('current_rate', 'rate');
        });

        // Recreate the dropped columns
        Schema::table('pre_invoices', function (Blueprint $table) {
            $table->string('hours_per_week')->nullable();
            $table->string('expenditure_type')->nullable();
            $table->string('type')->nullable();
        });
    }
};
