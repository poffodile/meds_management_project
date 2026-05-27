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
            $table->string('billing_frequency')->nullable()->after('suFundingType');
            $table->string('billing_rate')->nullable()->after('billing_frequency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_user', function (Blueprint $table) {
            $table->dropColumn(['billing_frequency', 'billing_rate']);
        });
    }
};
