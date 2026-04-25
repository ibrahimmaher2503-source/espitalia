<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacy_refunds', function (Blueprint $table) {
            $table->id();
            $table->string('refund_number', 20)->unique();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('user_id');
            $table->text('reason');
            $table->string('refund_type', 20);
            $table->decimal('refund_amount', 10, 2);
            $table->string('refund_method', 30);
            $table->string('status', 20)->default('pending');
            $table->text('admin_notes')->nullable();
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('order_id')->references('id')->on('pharmacy_orders');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('processed_by')->references('id')->on('users')->onDelete('set null');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacy_refunds');
    }
};
