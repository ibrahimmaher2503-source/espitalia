<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $translatableColumns = [
        'clinic' => ['name', 'description'],
        'services' => ['name', 'description'],
        'lab_tests' => ['name', 'description', 'preparation_instructions'],
        'lab_test_categories' => ['name', 'description'],
        'system_service_category' => ['name'],
    ];

    public function up(): void
    {
        foreach ($this->translatableColumns as $table => $columns) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as $column) {
                if (!Schema::hasColumn($table, $column)) {
                    continue;
                }

                // STEP 1: Change column type to TEXT first (prevents truncation)
                Schema::table($table, function (Blueprint $blueprint) use ($column) {
                    $blueprint->text($column)->nullable()->change();
                });

                // STEP 2: Convert existing plain text values to JSON {"en": "value"}
                DB::table($table)
                    ->whereNotNull($column)
                    ->where($column, '!=', '')
                    ->orderBy('id')
                    ->chunk(100, function ($rows) use ($table, $column) {
                        foreach ($rows as $row) {
                            $value = $row->$column;
                            // Skip if already valid JSON with locale keys
                            $decoded = json_decode($value, true);
                            if (is_array($decoded) && (isset($decoded['en']) || isset($decoded['ar']))) {
                                continue;
                            }
                            DB::table($table)->where('id', $row->id)->update([
                                $column => json_encode(['en' => $value], JSON_UNESCAPED_UNICODE),
                            ]);
                        }
                    });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->translatableColumns as $table => $columns) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as $column) {
                if (!Schema::hasColumn($table, $column)) {
                    continue;
                }

                // Convert JSON back to plain text (take 'en' value)
                DB::table($table)
                    ->whereNotNull($column)
                    ->where($column, '!=', '')
                    ->orderBy('id')
                    ->chunk(100, function ($rows) use ($table, $column) {
                        foreach ($rows as $row) {
                            $decoded = json_decode($row->$column, true);
                            if (is_array($decoded) && isset($decoded['en'])) {
                                DB::table($table)->where('id', $row->id)->update([
                                    $column => $decoded['en'],
                                ]);
                            }
                        }
                    });

                Schema::table($table, function (Blueprint $blueprint) use ($column) {
                    $blueprint->string($column)->nullable()->change();
                });
            }
        }
    }
};
