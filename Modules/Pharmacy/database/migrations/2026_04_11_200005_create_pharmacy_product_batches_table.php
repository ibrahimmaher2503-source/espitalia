<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacy_product_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inventory_id');
            $table->string('batch_number', 50);
            $table->unsignedInteger('quantity')->default(0);
            $table->date('manufacturing_date')->nullable();
            $table->date('expiry_date');
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('inventory_id')->references('id')->on('pharmacy_inventory')->onDelete('cascade');
            $table->unique(['inventory_id', 'batch_number']);
            $table->index('expiry_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacy_product_batches');
    }
};
