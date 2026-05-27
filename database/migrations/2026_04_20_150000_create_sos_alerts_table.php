<?php
 
 use Illuminate\Database\Migrations\Migration;
 use Illuminate\Database\Schema\Blueprint;
 use Illuminate\Support\Facades\Schema;
 
 return new class extends Migration
 {
     public function up(): void
     {
         if (!Schema::hasTable('sos_alerts')) {
             Schema::create('sos_alerts', function (Blueprint $table) {
                 $table->id();
                 $table->integer('staff_id');
                 $table->string('location')->nullable();
                 $table->timestamps();
 
                 $table->index('staff_id');
             });
         }
     }
 
     public function down(): void
     {
         Schema::dropIfExists('sos_alerts');
     }
 };
