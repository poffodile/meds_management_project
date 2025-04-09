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
        Schema::table('purchase_order_record_payments', function (Blueprint $table) {
            $table->boolean('record_type')->after('record_description')->nullable()->comment('1 purchaseOrder,2 Invoice');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_order_record_payments', function (Blueprint $table) {
            $table->dropColumn('record_type');
        });
    }
};
