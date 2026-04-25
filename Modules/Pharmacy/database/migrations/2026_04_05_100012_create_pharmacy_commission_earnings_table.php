<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacy_commission_earnings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('pharmacy_id');
            $table->unsignedBigInteger('commission_id')->nullable();
            $table->decimal('order_subtotal', 10, 2);
            $table->string('commission_type', 20);
            $table->decimal('commission_value', 8, 2);
            $table->decimal('commission_amount', 10, 2);
            $table->decimal('pharmacy_receivable', 10, 2);
            $table->string('status', 20)->default('unpaid');
            $table->unsignedBigInteger('payout_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('order_id')->references('id')->on('pharmacy_orders')->onDelete('cascade');
            $table->foreign('pharmacy_id')->references('id')->on('pharmacies')->onDelete('cascade');
            $table->foreign('commission_id')->references('id')->on('pharmacy_commissions')->onDelete('set null');
            $table->foreign('payout_id')->references('id')->on('pharmacy_payouts')->onDelete('set null');

            // Indexes
            $table->unique('order_id');
            $table->index('pharmacy_id');
            $table->index('status');
            $table->index(['pharmacy_id', 'status']);
            $table->index('payout_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacy_commission_earnings');
    }
};
