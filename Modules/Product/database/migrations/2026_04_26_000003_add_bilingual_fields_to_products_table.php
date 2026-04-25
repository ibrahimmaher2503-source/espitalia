<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('name_ar')->nullable()->after('name');
            $table->string('name_en')->nullable()->after('name_ar');
            $table->longText('short_description_ar')->nullable()->after('short_description');
            $table->longText('short_description_en')->nullable()->after('short_description_ar');
            $table->longText('description_ar')->nullable()->after('description');
            $table->longText('description_en')->nullable()->after('description_ar');
        });

        DB::statement('UPDATE products SET name_en = name WHERE name IS NOT NULL');
        DB::statement('UPDATE products SET short_description_en = short_description WHERE short_description IS NOT NULL');
        DB::statement('UPDATE products SET description_en = description WHERE description IS NOT NULL');
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'name_en', 'short_description_ar', 'short_description_en', 'description_ar', 'description_en']);
        });
    }
};
