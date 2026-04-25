<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('nurse_requests')) {
            return;
        }

        Schema::table('nurse_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('governorate_id')->nullable()->after('longitude');
            $table->unsignedBigInteger('city_id')->nullable()->after('governorate_id');
            $table->decimal('hourly_rate', 10, 2)->nullable()->after('total_amount');

            $table->foreign('governorate_id')->references('id')->on('governorates')->onDelete('set null');
            $table->foreign('city_id')->references('id')->on('governorate_cities')->onDelete('set null');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('nurse_requests')) {
            return;
        }

        Schema::table('nurse_requests', function (Blueprint $table) {
            $table->dropForeign(['governorate_id']);
            $table->dropForeign(['city_id']);
            $table->dropColumn(['governorate_id', 'city_id', 'hourly_rate']);
        });
    }
};
