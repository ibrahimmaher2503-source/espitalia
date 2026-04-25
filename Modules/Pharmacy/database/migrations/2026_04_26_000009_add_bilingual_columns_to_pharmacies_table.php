<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pharmacies', function (Blueprint $table) {
            $table->string('name_ar')->nullable()->after('name');
            $table->string('name_en')->nullable()->after('name_ar');
            $table->text('description')->nullable()->after('name_en');
            $table->text('description_ar')->nullable()->after('description');
            $table->text('description_en')->nullable()->after('description_ar');
        });

        // Backfill: copy existing name into name_en
        DB::statement('UPDATE pharmacies SET name_en = name WHERE name IS NOT NULL AND name_en IS NULL');

        // Backfill: copy existing description into description_en
        DB::statement('UPDATE pharmacies SET description_en = description WHERE description IS NOT NULL AND description_en IS NULL');
    }

    public function down(): void
    {
        Schema::table('pharmacies', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'name_en', 'description', 'description_ar', 'description_en']);
        });
    }
};
