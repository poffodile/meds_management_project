<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reorder → ordering (#32) — a purchase line raised against a low medicine.
 * Flow: ordered → received (books stock in, creates a batch) / cancelled.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medication_stock_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('home_id');
            $table->unsignedBigInteger('mar_sheet_id');
            $table->unsignedBigInteger('client_id')->nullable();
            $table->string('medication_name');
            $table->decimal('quantity', 10, 2);              // amount ordered
            $table->decimal('received_quantity', 10, 2)->nullable();
            $table->string('supplier')->nullable();
            $table->enum('status', ['ordered', 'received', 'cancelled'])->default('ordered');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by_user_id');
            $table->dateTime('ordered_at')->nullable();
            $table->dateTime('received_at')->nullable();
            $table->timestamps();

            $table->index('home_id');
            $table->index('mar_sheet_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_stock_orders');
    }
};
