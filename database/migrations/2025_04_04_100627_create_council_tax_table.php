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
        Schema::create('council_tax', function (Blueprint $table) {
            $table->id();
            $table->string('flat_number')->nullable();
            $table->text('address')->nullable();
            $table->string('post_code')->nullable();
            $table->string('council')->nullable();
            $table->string('no_of_bedrooms')->nullable();
            $table->boolean('owned_by_omega')->comment('1-yes, 0-no')->default('0');
            $table->string('occupancy')->nullable();
            $table->boolean('exempt')->comment('1-yes, 0-no')->default('1');
            $table->string('account_number')->nullable();
            $table->date('last_bill_date')->nullable();
            $table->date('bill_period_start_date')->nullable();
            $table->date('bill_period_end_date')->nullable();
            $table->integer('amount_paid')->nullable();
            $table->text('additional')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('council_tax');
    }
};
