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
        Schema::create('pre_invoice_extras_weeklies', function (Blueprint $table) {
            $table->id();
            $table->string('loggedUserId');
            $table->string('home_id');
            $table->string('child_id');
            $table->string('current_id')->comment('Pre-Invoice id for foreign');
            $table->date('extras_weekly_start_date');
            $table->date('extras_weekly_end_date');
            $table->string('extras_weekly_expenditure_type')->nullable();
            $table->string('extras_weekly_no_of_days')->nullable();
            $table->decimal('extras_weekly_amount', 10, 2);
            $table->decimal('extras_weekly_total_cost', 10, 2);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pre_invoice_extras_weeklies');
    }
};
