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
        Schema::table('home', function (Blueprint $table) {
            if (!Schema::hasColumn('home', 'weekly_changes')) {
                $table->text('weekly_changes')->nullable();
            }
            if (!Schema::hasColumn('home', 'monthly_home_changes')) {
                $table->text('monthly_home_changes')->nullable();
            }
        });

        Schema::table('invoice_products', function (Blueprint $table) {
            if (!Schema::hasColumn('invoice_products', 'is_care_service')) {
                $table->boolean('is_care_service')->default(false);
            }
            if (!Schema::hasColumn('invoice_products', 'is_expense')) {
                $table->boolean('is_expense')->default(false);
            }
            if (!Schema::hasColumn('invoice_products', 'funding_name')) {
                $table->string('funding_name')->nullable();
            }
            if (!Schema::hasColumn('invoice_products', 'funding_value')) {
                $table->decimal('funding_value', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('invoice_products', 'funding_type')) {
                $table->string('funding_type')->nullable(); // Percentage/Fixed
            }
            if (!Schema::hasColumn('invoice_products', 'item_code')) {
                $table->string('item_code')->nullable();
            }
        });

        Schema::table('scheduled_shifts', function (Blueprint $table) {
            if (!Schema::hasColumn('scheduled_shifts', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('home', function (Blueprint $table) {
            $table->dropColumn(['weekly_changes', 'monthly_home_changes']);
        });

        Schema::table('invoice_products', function (Blueprint $table) {
            $table->dropColumn(['is_care_service', 'is_expense', 'funding_name', 'funding_value', 'funding_type', 'item_code']);
        });

        Schema::table('scheduled_shifts', function (Blueprint $table) {
            $table->dropColumn(['end_date']);
        });
    }
};
