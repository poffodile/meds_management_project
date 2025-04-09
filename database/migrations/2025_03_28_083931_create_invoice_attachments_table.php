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
        Schema::create('invoice_attachments', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_id');
            $table->string('Invoice_ref');
            $table->string('attachment_type');
            $table->string('file');
            $table->string('original_file_name');
            $table->string('mime_type');
            $table->string('size');
            $table->string('title');
            $table->string('description');
            $table->boolean('customer_visible')->default(0);
            $table->boolean('mobile_user_visible')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_attachments');
    }
};
