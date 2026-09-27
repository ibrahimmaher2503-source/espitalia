<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The core migration 2026_04_11_000006 already adds these fields for
        // current installs. Keep this migration compatible with older schemas
        // without attempting to add the same columns twice.
        $hasDescriptionAr = Schema::hasColumn('pharmacy_products', 'description_ar');
        $hasDescriptionEn = Schema::hasColumn('pharmacy_products', 'description_en');

        if (! $hasDescriptionAr || ! $hasDescriptionEn) {
            Schema::table('pharmacy_products', function (Blueprint $table) use ($hasDescriptionAr, $hasDescriptionEn) {
                if (! $hasDescriptionAr) {
                    $table->text('description_ar')->nullable()->after('description');
                }

                if (! $hasDescriptionEn) {
                    $table->text('description_en')->nullable()->after('description_ar');
                }
            });
        }

        // Backfill: copy existing description into description_en
        DB::statement('UPDATE pharmacy_products SET description_en = description WHERE description IS NOT NULL AND description_en IS NULL');
    }

    public function down(): void
    {
        // The fields are owned by the earlier core migration.
    }
};
