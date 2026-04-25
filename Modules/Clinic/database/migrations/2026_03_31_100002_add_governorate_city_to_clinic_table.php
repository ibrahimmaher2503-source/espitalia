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
        Schema::table('clinic', function (Blueprint $table) {
            $table->unsignedBigInteger('governorate_id')->nullable()->after('country');
            $table->unsignedBigInteger('city_id')->nullable()->after('governorate_id');

            $table->foreign('governorate_id')
                  ->references('id')
                  ->on('governorates')
                  ->onDelete('set null');

            $table->foreign('city_id')
                  ->references('id')
                  ->on('governorate_cities')
                  ->onDelete('set null');

            $table->index('governorate_id', 'clinic_governorate_id_index');
            $table->index('city_id', 'clinic_city_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clinic', function (Blueprint $table) {
            $table->dropForeign(['governorate_id']);
            $table->dropForeign(['city_id']);
            $table->dropIndex('clinic_governorate_id_index');
            $table->dropIndex('clinic_city_id_index');
            $table->dropColumn(['governorate_id', 'city_id']);
        });
    }
};
