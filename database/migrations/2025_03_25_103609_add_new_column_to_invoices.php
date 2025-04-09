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
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('contact_id')->after('project_id')->nullable();
            $table->string('name')->after('project_id')->nullable();
            $table->string('address')->after('project_id')->nullable();
            $table->string('city')->after('project_id')->nullable();
            $table->string('county')->after('project_id')->nullable();
            $table->string('postcode')->after('project_id')->nullable();
            $table->string('telephone_code')->after('project_id')->nullable();
            $table->string('telephone')->after('project_id')->nullable();
            $table->string('invoice_mobile_code')->after('project_id')->nullable();
            $table->string('mobile')->after('project_id')->nullable();
            $table->string('email')->after('project_id')->nullable();
            $table->string('region')->after('project_id')->nullable();
            $table->string('site_name')->after('project_id')->nullable();
            $table->string('company_name')->after('project_id')->nullable();
            $table->string('site_address')->after('project_id')->nullable();
            $table->string('site_city')->after('project_id')->nullable();
            $table->string('site_county')->after('project_id')->nullable();
            $table->string('site_postcode')->after('project_id')->nullable();
            $table->string('site_telephone_code')->after('project_id')->nullable();
            $table->string('site_telephone')->after('project_id')->nullable();
            $table->string('site_mobile_code')->after('project_id')->nullable();
            $table->string('site_mobile')->after('project_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('contact_id');
            $table->dropColumn('name');
            $table->dropColumn('address');
            $table->dropColumn('city');
            $table->dropColumn('county');
            $table->dropColumn('postcode');
            $table->dropColumn('telephone_code');
            $table->dropColumn('telephone');
            $table->dropColumn('invoice_mobile_code');
            $table->dropColumn('mobile');
            $table->dropColumn('email');
            $table->dropColumn('region');
            $table->dropColumn('site_name');
            $table->dropColumn('company_name');
            $table->dropColumn('site_address');
            $table->dropColumn('site_city');
            $table->dropColumn('site_county');
            $table->dropColumn('site_postcode');
            $table->dropColumn('site_telephone_code');
            $table->dropColumn('site_telephone');
            $table->dropColumn('site_mobile_code');
            $table->dropColumn('site_mobile');
        });
    }
};
