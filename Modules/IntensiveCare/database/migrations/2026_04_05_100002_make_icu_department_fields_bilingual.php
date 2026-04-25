<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('icu_departments', function (Blueprint $table) {
            $table->string('name_ar', 255)->nullable()->after('hospital_id');
            $table->string('name_en', 255)->nullable()->after('name_ar');
            $table->text('description_ar')->nullable()->after('name_en');
            $table->text('description_en')->nullable()->after('description_ar');
        });

        // Copy existing data to both language columns
        DB::statement('UPDATE icu_departments SET name_ar = name, name_en = name WHERE name IS NOT NULL');
        DB::statement('UPDATE icu_departments SET description_ar = description, description_en = description WHERE description IS NOT NULL');

        // Drop old columns
        Schema::table('icu_departments', function (Blueprint $table) {
            $table->dropColumn(['name', 'description']);
        });
    }

    public function down(): void
    {
        Schema::table('icu_departments', function (Blueprint $table) {
            $table->string('name')->nullable()->after('hospital_id');
            $table->text('description')->nullable()->after('daily_price');
        });

        DB::statement('UPDATE icu_departments SET name = name_en WHERE name_en IS NOT NULL');
        DB::statement('UPDATE icu_departments SET description = description_en WHERE description_en IS NOT NULL');

        Schema::table('icu_departments', function (Blueprint $table) {
            $table->dropColumn([
                'name_ar', 'name_en',
                'description_ar', 'description_en',
            ]);
        });
    }
};
