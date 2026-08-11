<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin', function (Blueprint $table) {
            $table->string('frontend4_slug')->nullable()->after('company')->unique();
        });

        Schema::create('frontend4_user_service_access', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->index();
            $table->integer('organisation_id')->index();
            $table->integer('service_id')->index();
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
            $table->unique(['user_id', 'organisation_id', 'service_id'], 'f4_user_org_service_unique');
            $table->foreign('user_id', 'f4_service_access_user_fk')->references('id')->on('user')->restrictOnDelete();
            $table->foreign('organisation_id', 'f4_service_access_org_fk')->references('id')->on('admin')->restrictOnDelete();
            $table->foreign('service_id', 'f4_service_access_service_fk')->references('id')->on('home')->restrictOnDelete();
        });

        Schema::create('frontend4_user_location_access', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->index();
            $table->integer('organisation_id')->index();
            $table->integer('service_id')->index();
            $table->unsignedBigInteger('location_id')->index();
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
            $table->unique(['user_id', 'organisation_id', 'service_id', 'location_id'], 'f4_user_org_service_location_unique');
            $table->foreign('user_id', 'f4_location_access_user_fk')->references('id')->on('user')->restrictOnDelete();
            $table->foreign('organisation_id', 'f4_location_access_org_fk')->references('id')->on('admin')->restrictOnDelete();
            $table->foreign('service_id', 'f4_location_access_service_fk')->references('id')->on('home')->restrictOnDelete();
            $table->foreign('location_id', 'f4_location_access_location_fk')->references('id')->on('home_areas')->restrictOnDelete();
        });

        Schema::table('service_user', function (Blueprint $table) {
            $table->unsignedBigInteger('home_area_id')->nullable()->after('home_id')->index();
            $table->foreign('home_area_id', 'service_user_home_area_fk')->references('id')->on('home_areas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('service_user', function (Blueprint $table) {
            $table->dropForeign('service_user_home_area_fk');
            $table->dropColumn('home_area_id');
        });
        Schema::dropIfExists('frontend4_user_location_access');
        Schema::dropIfExists('frontend4_user_service_access');
        Schema::table('admin', function (Blueprint $table) {
            $table->dropUnique(['frontend4_slug']);
            $table->dropColumn('frontend4_slug');
        });
    }
};
