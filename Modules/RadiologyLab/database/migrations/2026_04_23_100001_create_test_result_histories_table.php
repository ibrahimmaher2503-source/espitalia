<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_result_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('test_order_item_id');
            $table->unsignedBigInteger('test_order_id');

            $table->string('old_result_value')->nullable();
            $table->string('old_result_unit', 50)->nullable();
            $table->string('old_reference_range', 100)->nullable();
            $table->string('old_result_status', 20)->nullable();
            $table->text('old_result_notes')->nullable();
            $table->dateTime('old_result_date')->nullable();
            $table->unsignedBigInteger('old_result_by')->nullable();

            $table->string('new_result_value')->nullable();
            $table->string('new_result_unit', 50)->nullable();
            $table->string('new_reference_range', 100)->nullable();
            $table->string('new_result_status', 20)->nullable();
            $table->text('new_result_notes')->nullable();

            $table->unsignedBigInteger('changed_by');
            $table->text('change_reason')->nullable();
            $table->timestamp('created_at');

            $table->foreign('test_order_item_id')->references('id')->on('test_order_items')->cascadeOnDelete();
            $table->foreign('test_order_id')->references('id')->on('test_orders')->cascadeOnDelete();
            $table->foreign('changed_by')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('old_result_by')->references('id')->on('users')->nullOnDelete();

            $table->index('test_order_item_id');
            $table->index('test_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_result_histories');
    }
};
