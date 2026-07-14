<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Batch / lot tracking (#30) — each medicine can hold several batches, every one
 * with its own quantity + expiry. Stock rolls up from the batches, and they're
 * consumed First-Expiry-First-Out (FEFO).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medication_stock_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('home_id');
            $table->unsignedBigInteger('mar_sheet_id');
            $table->unsignedBigInteger('client_id')->nullable();
            $table->string('batch_number')->nullable();     // lot / batch number on the pack
            $table->decimal('quantity', 10, 2)->default(0);  // remaining in this batch
            $table->decimal('received_quantity', 10, 2)->nullable(); // what was booked in
            $table->date('expiry_date')->nullable();
            $table->string('supplier')->nullable();
            $table->boolean('is_depleted')->default(false);  // quantity reached 0
            $table->unsignedBigInteger('performed_by_user_id')->nullable();
            $table->dateTime('received_at')->nullable();
            $table->timestamps();

            $table->index('home_id');
            $table->index('mar_sheet_id');
            $table->index('expiry_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_stock_batches');
    }
};
