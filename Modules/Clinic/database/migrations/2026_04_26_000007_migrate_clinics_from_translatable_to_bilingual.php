<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clinic', function (Blueprint $table) {
            $table->string('name_ar')->nullable()->after('name');
            $table->string('name_en')->nullable()->after('name');
            $table->text('description_ar')->nullable()->after('description');
            $table->text('description_en')->nullable()->after('description');
        });

        // Backfill from existing JSON columns
        DB::table('clinic')->orderBy('id')->each(function ($clinic) {
            $nameEn = null;
            $nameAr = null;
            $descEn = null;
            $descAr = null;

            // Parse name
            if (!empty($clinic->name)) {
                $decoded = json_decode($clinic->name, true);
                if (is_array($decoded)) {
                    $nameEn = $decoded['en'] ?? null;
                    $nameAr = $decoded['ar'] ?? null;
                } else {
                    // Plain string — put it in _en
                    $nameEn = $clinic->name;
                }
            }

            // Parse description
            if (!empty($clinic->description)) {
                $decoded = json_decode($clinic->description, true);
                if (is_array($decoded)) {
                    $descEn = $decoded['en'] ?? null;
                    $descAr = $decoded['ar'] ?? null;
                } else {
                    // Plain string — put it in _en
                    $descEn = $clinic->description;
                }
            }

            DB::table('clinic')->where('id', $clinic->id)->update([
                'name_en' => $nameEn,
                'name_ar' => $nameAr,
                'description_en' => $descEn,
                'description_ar' => $descAr,
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clinic', function (Blueprint $table) {
            $table->dropColumn(['name_en', 'name_ar', 'description_en', 'description_ar']);
        });
    }
};
