<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->unsignedBigInteger('governorate_id')->nullable()->after('country');
            $table->unsignedBigInteger('city_id')->nullable()->after('governorate_id');
            $table->string('label', 20)->nullable()->after('is_primary');
            $table->string('phone', 20)->nullable()->after('last_name');

            $table->foreign('governorate_id')->references('id')->on('governorates')->onDelete('set null');
            $table->foreign('city_id')->references('id')->on('governorate_cities')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropForeign(['governorate_id']);
            $table->dropForeign(['city_id']);
            $table->dropColumn(['governorate_id', 'city_id', 'label', 'phone']);
        });
    }
};
