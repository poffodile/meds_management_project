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
        Schema::create('sales_day_books', function (Blueprint $table) {
            $table->id();
            $table->string('home_id');
            $table->integer('customer_id');
            $table->date('date');
            $table->string('invoice_no');
            $table->decimal('netAmount', 10,2);
            $table->integer('Vat');
            $table->decimal('vatAmount', 10,2);
            $table->decimal('grossAmount', 10,2);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_day_books');
    }
};
