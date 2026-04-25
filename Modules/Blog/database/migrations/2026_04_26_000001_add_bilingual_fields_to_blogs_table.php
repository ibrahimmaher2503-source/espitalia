<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->string('title_ar')->nullable()->after('title');
            $table->string('title_en')->nullable()->after('title_ar');
            $table->longText('description_ar')->nullable()->after('description');
            $table->longText('description_en')->nullable()->after('description_ar');
        });

        DB::statement('UPDATE blogs SET title_en = title WHERE title IS NOT NULL');
        DB::statement('UPDATE blogs SET description_en = description WHERE description IS NOT NULL');
    }

    public function down(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->dropColumn(['title_ar', 'title_en', 'description_ar', 'description_en']);
        });
    }
};
