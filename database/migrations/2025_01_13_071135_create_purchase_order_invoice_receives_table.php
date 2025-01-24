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
        Schema::create('purchase_order_invoice_receives', function (Blueprint $table) {
            $table->id();
            $table->string('home_id');
            $table->string('loginUserId');
            $table->string('po_id');
            $table->string('supplier_id');
            $table->string('inv_ref');
            $table->string('net_amount');
            $table->string('vat_id');
            $table->string('vat_amount')->nullable();
            $table->string('gross_amount');
            $table->string('invoice_date');
            $table->string('due_date')->nullable();
            $table->string('notes')->nullable();
            $table->string('file')->nullable();
            $table->string('original_file_name')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_invoice_receives');
    }
};
