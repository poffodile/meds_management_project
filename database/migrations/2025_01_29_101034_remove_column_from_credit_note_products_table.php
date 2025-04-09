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
        Schema::table('credit_note_products', function (Blueprint $table) {
            $table->dropColumn(['job_id', 'deliverd_qty', 'quantity_available', 'outstanding_amount', 'receive_more']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('credit_note_products', function (Blueprint $table) {
            $table->string('job_id')->nullable();
            $table->string('deliverd_qty')->nullable();
            $table->string('quantity_available')->nullable();
            $table->string('outstanding_amount')->nullable();
            $table->string('receive_more')->nullable();
        });
    }
};
