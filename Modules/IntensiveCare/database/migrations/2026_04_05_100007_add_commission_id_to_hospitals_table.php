<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hospitals', function (Blueprint $table) {
            $table->unsignedBigInteger('commission_id')->nullable()->after('rating');
            $table->foreign('commission_id')->references('id')->on('icu_commissions')->onDelete('set null');
            $table->index('commission_id');
        });
    }

    public function down(): void
    {
        Schema::table('hospitals', function (Blueprint $table) {
            $table->dropForeign(['commission_id']);
            $table->dropIndex(['commission_id']);
            $table->dropColumn('commission_id');
        });
    }
};
