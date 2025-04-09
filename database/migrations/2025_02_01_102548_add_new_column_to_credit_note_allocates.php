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
        Schema::table('credit_note_allocates', function (Blueprint $table) {
            $table->string('product_id')->after('po_id')->nullable();
            $table->string('supplier_id')->after('po_id')->nullable();
            $table->string('date')->after('amount_paid')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('credit_note_allocates', function (Blueprint $table) {
            $table->dropColumn('product_id');
            $table->dropColumn('supplier_id');
            $table->dropColumn('date');
        });
    }
};
