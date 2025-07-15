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
        Schema::create('pre_subs_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('loggedUserId');
            $table->string('home_id');
            $table->string('child_id');
            $table->string('current_id')->comment('Pre-Invoice id for foreign');
            $table->date('subs_start_date');
            $table->date('subs_end_date')->nullable();
            $table->string('subs_no_of_days')->nullable();
            $table->decimal('subs_rate', 10, 2);
            $table->decimal('subs_total_cost', 10, 2);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pre_subs_invoices');
    }
};
