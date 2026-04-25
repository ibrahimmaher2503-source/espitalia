<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_order_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_order_id')->constrained('test_orders')->cascadeOnDelete();
            $table->string('external_transaction_id')->nullable();
            $table->string('transaction_type', 30)->default('cash');
            $table->string('payment_method', 50)->nullable();
            $table->string('payment_gateway', 50)->nullable();
            $table->boolean('payment_status')->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->json('tax_percentage')->nullable();
            $table->string('currency', 3)->default('SAR');
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->string('refund_status', 30)->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('test_order_id');
            $table->index('transaction_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_order_transactions');
    }
};
