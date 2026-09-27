<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'firebase_uid')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('firebase_uid', 191)->nullable()->unique()->after('login_type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'firebase_uid')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropUnique(['firebase_uid']);
                $table->dropColumn('firebase_uid');
            });
        }
    }
};
