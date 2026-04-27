<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Performance-only indexes; skip on SQLite (used for testing) to avoid
        // SQLite's limitation where named indexes cause issues with subsequent
        // dropColumn operations on other tables.
        if (\Illuminate\Support\Facades\DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('pharmacies', function (Blueprint $table) {
            if (! $this->hasIndex('pharmacies', 'pharmacies_is_active_created_at_index')) {
                $table->index(['is_active', 'created_at'], 'pharmacies_is_active_created_at_index');
            }
            if (! $this->hasIndex('pharmacies', 'pharmacies_vendor_id_index')) {
                $table->index('vendor_id', 'pharmacies_vendor_id_index');
            }
        });

        Schema::table('pharmacy_staff', function (Blueprint $table) {
            if (Schema::hasTable('pharmacy_staff') && ! $this->hasIndex('pharmacy_staff', 'pharmacy_staff_pharmacy_id_index')) {
                $table->index('pharmacy_id', 'pharmacy_staff_pharmacy_id_index');
            }
        });
    }

    public function down(): void
    {
        if (\Illuminate\Support\Facades\DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('pharmacies', function (Blueprint $table) {
            $table->dropIndexIfExists('pharmacies_is_active_created_at_index');
            $table->dropIndexIfExists('pharmacies_vendor_id_index');
        });

        if (Schema::hasTable('pharmacy_staff')) {
            Schema::table('pharmacy_staff', function (Blueprint $table) {
                $table->dropIndexIfExists('pharmacy_staff_pharmacy_id_index');
            });
        }
    }

    private function hasIndex(string $table, string $index): bool
    {
        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            $indexes = \Illuminate\Support\Facades\DB::select(
                "SELECT name FROM sqlite_master WHERE type = 'index' AND tbl_name = ? AND name = ?",
                [$table, $index]
            );
        } else {
            $indexes = \Illuminate\Support\Facades\DB::select(
                "SHOW INDEX FROM `{$table}` WHERE Key_name = ?",
                [$index]
            );
        }

        return ! empty($indexes);
    }
};
