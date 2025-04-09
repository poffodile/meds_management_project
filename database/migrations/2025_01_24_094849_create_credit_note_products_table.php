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
        Schema::create('credit_note_products', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('credi_note_id')->nullable();
            $table->string('job_id')->nullable();
            $table->string('product_id')->nullable();
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->string('accountCode_id')->nullable();
            $table->integer('qty');
            $table->decimal('price', 8, 2)->nullable();
            $table->string('vat_id')->nullable();
            $table->string('vat')->nullable();
            $table->string('deliverd_qty')->nullable();
            $table->string('quantity_available')->nullable();
            $table->string('outstanding_amount')->nullable();
            $table->string('receive_more')->nullable();
            $table->string('userType');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_note_products');
    }
};
