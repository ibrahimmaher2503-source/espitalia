<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacy_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('pharmacy_id');
            $table->unsignedBigInteger('address_id')->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('delivery_fee', 8, 2);
            $table->decimal('total', 10, 2);
            $table->string('payment_method', 50)->default('cash_on_delivery');
            $table->string('status', 20)->default('pending');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('user_id');
            $table->index('pharmacy_id');
            $table->index('status');
            $table->index(['user_id', 'status']);

            // Foreign keys
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('restrict');
            $table->foreign('pharmacy_id')
                ->references('id')
                ->on('pharmacies')
                ->onDelete('restrict');
            $table->foreign('address_id')
                ->references('id')
                ->on('addresses')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacy_orders');
    }
};
