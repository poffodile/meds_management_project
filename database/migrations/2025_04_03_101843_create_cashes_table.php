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
        Schema::create('cashes', function (Blueprint $table) {
            $table->id();
            $table->string('home_id');
            $table->string('loginUserId');
            $table->date('cash_date');
            $table->decimal('balance_bfwd', 8, 2)->nullable();
            $table->decimal('petty_cashIn', 8, 2)->nullable();
            $table->decimal('cash_out', 8, 2)->nullable();
            $table->string('card_details')->nullable();
            $table->string('receipt');
            $table->string('fileName');
            $table->boolean('dext');
            $table->boolean('invoice_la');
            $table->string('initial');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashes');
    }
};
