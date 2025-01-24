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
        Schema::table('purchase_order_products', function (Blueprint $table) {
            $table->string('receive_more')->after('quantity_available')->nullable();
            $table->string('outstanding_amount')->after('quantity_available')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_order_products', function (Blueprint $table) {
            $table->dropColumn('receive_more');
            $table->dropColumn('outstanding_amount');
        });
    }
};
