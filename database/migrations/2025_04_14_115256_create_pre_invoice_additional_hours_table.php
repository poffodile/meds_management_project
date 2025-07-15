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
        Schema::create('pre_invoice_additional_hours', function (Blueprint $table) {
            $table->id();
            $table->string('loggedUserId');
            $table->string('home_id');
            $table->string('child_id');
            $table->string('current_id')->comment('Pre-Invoice id for foreign');
            $table->date('addHour_start_date');
            $table->integer('additional_per_week');
            $table->date('addHour_end_date')->nullable();
            $table->string('addHour_no_of_days')->nullable();
            $table->decimal('addHour_rate', 10, 2);
            $table->decimal('addHour_total_cost', 10, 2);
            $table->decimal('addHour_vat',10,2)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pre_invoice_additional_hours');
    }
};
