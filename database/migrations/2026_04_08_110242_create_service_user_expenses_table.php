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
        Schema::create('service_user_expenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('home_id');
            $table->unsignedBigInteger('service_user_id');
            $table->date('expense_date');
            $table->string('title');
            $table->decimal('amount', 10, 2);
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index('service_user_id');
            $table->index('home_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_user_expenses');
    }
};
