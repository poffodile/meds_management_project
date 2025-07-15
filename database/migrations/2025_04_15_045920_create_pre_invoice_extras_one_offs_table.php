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
        Schema::create('pre_invoice_extras_one_offs', function (Blueprint $table) {
            $table->id();
            $table->string('loggedUserId');
            $table->string('home_id');
            $table->string('child_id');
            $table->string('current_id')->comment('Pre-Invoice id for foreign');
            $table->string('extras_oneoff_expenditure_type')->nullable();
            $table->date('extras_oneoff_start_date');
            $table->decimal('extras_oneoff_amount', 10, 2);
            $table->decimal('extras_oneoff_total_cost', 10, 2);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pre_invoice_extras_one_offs');
    }
};
