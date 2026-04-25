<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('clinic')) {
            return;
        }

        Schema::table('clinic', function (Blueprint $table) {
            if (! Schema::hasColumn('clinic', 'branch_id')) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('city_id');
                $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
                $table->index('branch_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('clinic') || ! Schema::hasColumn('clinic', 'branch_id')) {
            return;
        }

        Schema::table('clinic', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropIndex(['branch_id']);
            $table->dropColumn('branch_id');
        });
    }
};
