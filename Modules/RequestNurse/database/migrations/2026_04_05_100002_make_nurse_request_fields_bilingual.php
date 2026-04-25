<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nurse_requests', function (Blueprint $table) {
            $table->text('service_description_ar')->nullable()->after('nurse_id');
            $table->text('service_description_en')->nullable()->after('service_description_ar');
        });

        // Copy existing data to both language columns
        DB::statement('UPDATE nurse_requests SET service_description_ar = service_description, service_description_en = service_description WHERE service_description IS NOT NULL');

        Schema::table('nurse_requests', function (Blueprint $table) {
            $table->dropColumn('service_description');
        });
    }

    public function down(): void
    {
        Schema::table('nurse_requests', function (Blueprint $table) {
            $table->text('service_description')->nullable()->after('nurse_id');
        });

        DB::statement('UPDATE nurse_requests SET service_description = service_description_en WHERE service_description_en IS NOT NULL');

        Schema::table('nurse_requests', function (Blueprint $table) {
            $table->dropColumn(['service_description_ar', 'service_description_en']);
        });
    }
};
