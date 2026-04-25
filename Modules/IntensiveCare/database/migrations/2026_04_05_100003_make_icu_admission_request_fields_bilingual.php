<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('icu_admission_requests', function (Blueprint $table) {
            // Bilingual medical_condition
            $table->text('medical_condition_ar')->nullable()->after('insurance_number');
            $table->text('medical_condition_en')->nullable()->after('medical_condition_ar');

            // Bilingual diagnosis
            $table->string('diagnosis_ar', 255)->nullable()->after('medical_condition_en');
            $table->string('diagnosis_en', 255)->nullable()->after('diagnosis_ar');

            // Bilingual admin_notes
            $table->text('admin_notes_ar')->nullable()->after('status');
            $table->text('admin_notes_en')->nullable()->after('admin_notes_ar');

            // Bilingual rejection_reason
            $table->text('rejection_reason_ar')->nullable()->after('admin_notes_en');
            $table->text('rejection_reason_en')->nullable()->after('rejection_reason_ar');
        });

        // Copy existing data to both language columns
        DB::statement('UPDATE icu_admission_requests SET medical_condition_ar = medical_condition, medical_condition_en = medical_condition WHERE medical_condition IS NOT NULL');
        DB::statement('UPDATE icu_admission_requests SET diagnosis_ar = diagnosis, diagnosis_en = diagnosis WHERE diagnosis IS NOT NULL');
        DB::statement('UPDATE icu_admission_requests SET admin_notes_ar = admin_notes, admin_notes_en = admin_notes WHERE admin_notes IS NOT NULL');
        DB::statement('UPDATE icu_admission_requests SET rejection_reason_ar = rejection_reason, rejection_reason_en = rejection_reason WHERE rejection_reason IS NOT NULL');

        // Drop old columns
        Schema::table('icu_admission_requests', function (Blueprint $table) {
            $table->dropColumn(['medical_condition', 'diagnosis', 'admin_notes', 'rejection_reason']);
        });
    }

    public function down(): void
    {
        Schema::table('icu_admission_requests', function (Blueprint $table) {
            $table->text('medical_condition')->nullable()->after('insurance_number');
            $table->string('diagnosis')->nullable()->after('medical_condition');
            $table->text('admin_notes')->nullable()->after('status');
            $table->text('rejection_reason')->nullable()->after('admin_notes');
        });

        DB::statement('UPDATE icu_admission_requests SET medical_condition = medical_condition_en WHERE medical_condition_en IS NOT NULL');
        DB::statement('UPDATE icu_admission_requests SET diagnosis = diagnosis_en WHERE diagnosis_en IS NOT NULL');
        DB::statement('UPDATE icu_admission_requests SET admin_notes = admin_notes_en WHERE admin_notes_en IS NOT NULL');
        DB::statement('UPDATE icu_admission_requests SET rejection_reason = rejection_reason_en WHERE rejection_reason_en IS NOT NULL');

        Schema::table('icu_admission_requests', function (Blueprint $table) {
            $table->dropColumn([
                'medical_condition_ar', 'medical_condition_en',
                'diagnosis_ar', 'diagnosis_en',
                'admin_notes_ar', 'admin_notes_en',
                'rejection_reason_ar', 'rejection_reason_en',
            ]);
        });
    }
};
