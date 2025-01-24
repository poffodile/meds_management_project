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
        Schema::table('purchase_order_emails', function (Blueprint $table) {
            $table->json('to')->change();
            $table->json('cc')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_order_emails', function (Blueprint $table) {
            $table->string('to')->change();
            $table->string('cc')->nullable()->change();
        });
    }
};
