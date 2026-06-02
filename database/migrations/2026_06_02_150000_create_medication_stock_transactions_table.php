<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medication_stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('home_id');
            $table->unsignedBigInteger('mar_sheet_id');
            $table->unsignedBigInteger('client_id')->nullable();
            $table->string('client_name')->nullable();
            $table->string('medication_name');
            $table->enum('transaction_type', ['received', 'administered', 'disposed', 'returned', 'correction']);
            $table->decimal('quantity', 10, 2)->nullable();
            $table->decimal('balance_before', 10, 2)->nullable();
            $table->decimal('balance_after', 10, 2)->nullable();
            $table->string('unit')->nullable();
            $table->string('reason')->nullable();          // disposal reason / correction reason
            $table->string('disposal_method')->nullable();
            $table->string('witness_name')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('performed_by_user_id');
            $table->dateTime('transaction_date');
            $table->timestamps();

            $table->index('home_id');
            $table->index('mar_sheet_id');
            $table->index('transaction_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_stock_transactions');
    }
};
