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
        Schema::table('service_user_expenses', function (Blueprint $table) {
            $table->unsignedBigInteger('invoice_id')->nullable()->after('home_id');
            $table->index('invoice_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_user_expenses', function (Blueprint $table) {
            $table->dropColumn('invoice_id');
        });
    }
};
