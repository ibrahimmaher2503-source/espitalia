<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fix #8: Change cascade to restrict on lab_tests.category_id
        Schema::table('lab_tests', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->foreign('category_id')->references('id')->on('lab_test_categories')->onDelete('restrict');
        });

        // Fix #9: Add missing indexes on test_orders
        Schema::table('test_orders', function (Blueprint $table) {
            $table->index('patient_id');
            $table->index('doctor_id');
            $table->index('lab_technician_id');
            $table->index('status');
            $table->index('payment_status');
        });

        // Fix #9b: Add missing indexes on lab_test_categories
        Schema::table('lab_test_categories', function (Blueprint $table) {
            $table->index('status');
            $table->index('display_order');
        });

        // Fix #9c: Add missing indexes on lab_tests
        Schema::table('lab_tests', function (Blueprint $table) {
            $table->index('status');
            $table->index('department');
        });

        // Fix #10: Change created_by/updated_by/deleted_by from integer to unsignedBigInteger
        $tables = ['lab_test_categories', 'lab_tests', 'test_orders', 'test_order_items', 'test_reports'];
        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedBigInteger('created_by')->nullable()->change();
                $table->unsignedBigInteger('updated_by')->nullable()->change();
                $table->unsignedBigInteger('deleted_by')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        // Revert lab_tests FK back to cascade
        Schema::table('lab_tests', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->foreign('category_id')->references('id')->on('lab_test_categories')->onDelete('cascade');
        });

        // Remove indexes from test_orders
        Schema::table('test_orders', function (Blueprint $table) {
            $table->dropIndex(['patient_id']);
            $table->dropIndex(['doctor_id']);
            $table->dropIndex(['lab_technician_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['payment_status']);
        });

        Schema::table('lab_test_categories', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['display_order']);
        });

        Schema::table('lab_tests', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['department']);
        });

        // Revert column types
        $tables = ['lab_test_categories', 'lab_tests', 'test_orders', 'test_order_items', 'test_reports'];
        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->integer('created_by')->unsigned()->nullable()->change();
                $table->integer('updated_by')->unsigned()->nullable()->change();
                $table->integer('deleted_by')->unsigned()->nullable()->change();
            });
        }
    }
};
