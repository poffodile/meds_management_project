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
        Schema::table('purchase_order_invoice_receives', function (Blueprint $table) {
            $table->string('oustanding_amount')->after('gross_amount')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_order_invoice_receives', function (Blueprint $table) {
            $table->dropColumn('oustanding_amount');
        });
    }
};
