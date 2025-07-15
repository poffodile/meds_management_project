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
        Schema::create('pre_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('loggedUserId');
            $table->string('home_id');
            $table->string('child_id');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('no_of_days')->nullable();
            $table->decimal('rate', 10, 2)->comment('type 1,2 then weekly rate, type 3 then hourly rate, type 4 then weekly amount, type 5 then amount');
            $table->decimal('total_cost', 10, 2);
            $table->integer('hours_per_week')->nullable();
            $table->string('expenditure_type')->nullable();
            $table->string('vat')->nullable();
            $table->integer('type')->comment('1 Current Rate (per week), 2 Subs (per week), 3 Additional Hours, 4 Additional Extras - weekly, 5 Additional Extras - one off');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pre_invoices');
    }
};
