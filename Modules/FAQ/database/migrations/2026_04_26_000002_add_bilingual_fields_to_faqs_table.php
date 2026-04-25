<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('faqs', function (Blueprint $table) {
            $table->string('question_ar')->nullable()->after('question');
            $table->string('question_en')->nullable()->after('question_ar');
            $table->longText('answer_ar')->nullable()->after('answer');
            $table->longText('answer_en')->nullable()->after('answer_ar');
        });

        DB::statement('UPDATE faqs SET question_en = question WHERE question IS NOT NULL');
        DB::statement('UPDATE faqs SET answer_en = answer WHERE answer IS NOT NULL');
    }

    public function down(): void
    {
        Schema::table('faqs', function (Blueprint $table) {
            $table->dropColumn(['question_ar', 'question_en', 'answer_ar', 'answer_en']);
        });
    }
};
