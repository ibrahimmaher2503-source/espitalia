<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('icu_commission_earnings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admission_request_id');
            $table->unsignedBigInteger('hospital_id');
            $table->unsignedBigInteger('commission_id')->nullable();
            $table->decimal('order_subtotal', 10, 2);
            $table->string('commission_type', 20);
            $table->decimal('commission_value', 8, 2);
            $table->decimal('commission_amount', 10, 2);
            $table->decimal('hospital_receivable', 10, 2);
            $table->string('status', 20)->default('unpaid');
            $table->unsignedBigInteger('payout_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('admission_request_id')->references('id')->on('icu_admission_requests')->onDelete('cascade');
            $table->foreign('hospital_id')->references('id')->on('hospitals')->onDelete('cascade');
            $table->foreign('commission_id')->references('id')->on('icu_commissions')->onDelete('set null');
            $table->foreign('payout_id')->references('id')->on('icu_payouts')->onDelete('set null');

            // Indexes
            $table->unique('admission_request_id');
            $table->index('hospital_id');
            $table->index('status');
            $table->index(['hospital_id', 'status']);
            $table->index('payout_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('icu_commission_earnings');
    }
};
