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
        Schema::table('purchase_day_books', function (Blueprint $table) {
            $table->decimal('reclaim', 8, 2)->after('grossAmount')->nullable();
            $table->decimal('not_reclaim', 8, 2)->after('reclaim')->nullable();
            $table->integer('expense_type')->after('not_reclaim')->nullable();
            $table->decimal('expense_amount', 8, 2)->after('expense_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_day_books', function (Blueprint $table) {
            //
        });
    }
};
