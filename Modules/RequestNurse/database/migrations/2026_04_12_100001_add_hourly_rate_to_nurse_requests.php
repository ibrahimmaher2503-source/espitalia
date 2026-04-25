<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nurse_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('nurse_requests', 'hourly_rate')) {
                $table->double('hourly_rate')->default(0)->after('total_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('nurse_requests', function (Blueprint $table) {
            if (Schema::hasColumn('nurse_requests', 'hourly_rate')) {
                $table->dropColumn('hourly_rate');
            }
        });
    }
};
