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
        Schema::create('invoice_products', function (Blueprint $table) {
            $table->id();
            $table->string('home_id');
            $table->string('invoice_id');
            $table->string('customer_id');
            $table->string('product_id');
            $table->string('code')->nullable();
            $table->string('accountcode')->nullable();
            $table->text('description')->nullable();
            $table->string('qty');
            $table->decimal('price', 10,2);
            $table->string('discount');
            $table->string('discount_type');
            $table->string('vat_id');
            $table->string('vat');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_products');
    }
};
