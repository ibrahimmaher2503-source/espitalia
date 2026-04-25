<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pharmacy_products', function (Blueprint $table) {
            $table->text('description_ar')->nullable()->after('description');
            $table->text('description_en')->nullable()->after('description_ar');
        });

        // Backfill: copy existing description into description_en
        DB::statement('UPDATE pharmacy_products SET description_en = description WHERE description IS NOT NULL AND description_en IS NULL');
    }

    public function down(): void
    {
        Schema::table('pharmacy_products', function (Blueprint $table) {
            $table->dropColumn(['description_ar', 'description_en']);
        });
    }
};
