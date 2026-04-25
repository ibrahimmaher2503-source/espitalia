<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nurses', function (Blueprint $table) {
            $table->string('specialization_ar', 255)->nullable()->after('city_id');
            $table->string('specialization_en', 255)->nullable()->after('specialization_ar');
            $table->string('experience_ar', 255)->nullable()->after('specialization_en');
            $table->string('experience_en', 255)->nullable()->after('experience_ar');
            $table->text('about_ar')->nullable()->after('experience_en');
            $table->text('about_en')->nullable()->after('about_ar');
            $table->string('service_area_ar', 255)->nullable()->after('about_en');
            $table->string('service_area_en', 255)->nullable()->after('service_area_ar');
        });

        // Copy existing data to both language columns
        DB::statement('UPDATE nurses SET specialization_ar = specialization, specialization_en = specialization WHERE specialization IS NOT NULL');
        DB::statement('UPDATE nurses SET experience_ar = experience, experience_en = experience WHERE experience IS NOT NULL');
        DB::statement('UPDATE nurses SET about_ar = about, about_en = about WHERE about IS NOT NULL');
        DB::statement('UPDATE nurses SET service_area_ar = service_area, service_area_en = service_area WHERE service_area IS NOT NULL');

        // Drop old columns
        Schema::table('nurses', function (Blueprint $table) {
            $table->dropColumn(['specialization', 'experience', 'about', 'service_area']);
        });
    }

    public function down(): void
    {
        Schema::table('nurses', function (Blueprint $table) {
            $table->string('specialization', 255)->nullable()->after('city_id');
            $table->string('experience', 255)->nullable()->after('specialization');
            $table->text('about')->nullable()->after('experience');
            $table->string('service_area', 255)->nullable()->after('about');
        });

        // Copy _en back to restored columns
        DB::statement('UPDATE nurses SET specialization = specialization_en WHERE specialization_en IS NOT NULL');
        DB::statement('UPDATE nurses SET experience = experience_en WHERE experience_en IS NOT NULL');
        DB::statement('UPDATE nurses SET about = about_en WHERE about_en IS NOT NULL');
        DB::statement('UPDATE nurses SET service_area = service_area_en WHERE service_area_en IS NOT NULL');

        Schema::table('nurses', function (Blueprint $table) {
            $table->dropColumn([
                'specialization_ar', 'specialization_en',
                'experience_ar', 'experience_en',
                'about_ar', 'about_en',
                'service_area_ar', 'service_area_en',
            ]);
        });
    }
};
