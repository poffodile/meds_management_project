<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
          // Clean NULL data first
          DB::table('council_tax')->whereNull('address')->update(['address' => '']);
          DB::table('council_tax')->whereNull('post_code')->update(['post_code' => '']);
          DB::table('council_tax')->whereNull('council')->update(['council' => '']);
          DB::table('council_tax')->whereNull('account_number')->update(['account_number' => '']);
  
          // Now modify the columns to be NOT NULL
          DB::statement("ALTER TABLE council_tax MODIFY address VARCHAR(255) NOT NULL");
          DB::statement("ALTER TABLE council_tax MODIFY post_code VARCHAR(255) NOT NULL");
          DB::statement("ALTER TABLE council_tax MODIFY council VARCHAR(20) NOT NULL");
          DB::statement("ALTER TABLE council_tax MODIFY account_number VARCHAR(20) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('council_tax', function (Blueprint $table) {
            //
        });
    }
};
