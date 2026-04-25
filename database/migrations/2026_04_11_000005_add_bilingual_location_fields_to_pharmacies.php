<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pharmacies')) {
            return;
        }

        Schema::table('pharmacies', function (Blueprint $table) {
            $table->string('name_ar')->nullable()->after('name');
            $table->string('name_en')->nullable()->after('name_ar');
            $table->text('description_ar')->nullable()->after('name_en');
            $table->text('description_en')->nullable()->after('description_ar');
            $table->unsignedBigInteger('vendor_id')->nullable()->after('is_active');
            $table->unsignedBigInteger('governorate_id')->nullable()->after('vendor_id');
            $table->unsignedBigInteger('city_id')->nullable()->after('governorate_id');
            $table->string('license_number', 50)->nullable()->after('city_id');
            $table->json('operating_hours')->nullable()->after('license_number');

            $table->foreign('vendor_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('governorate_id')->references('id')->on('governorates')->onDelete('set null');
            $table->foreign('city_id')->references('id')->on('governorate_cities')->onDelete('set null');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('pharmacies')) {
            return;
        }

        Schema::table('pharmacies', function (Blueprint $table) {
            $table->dropForeign(['vendor_id']);
            $table->dropForeign(['governorate_id']);
            $table->dropForeign(['city_id']);
            $table->dropColumn(['name_ar', 'name_en', 'description_ar', 'description_en', 'vendor_id', 'governorate_id', 'city_id', 'license_number', 'operating_hours']);
        });
    }
};
