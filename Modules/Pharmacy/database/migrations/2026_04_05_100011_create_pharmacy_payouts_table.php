<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacy_payouts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pharmacy_id');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('total_commission', 10, 2);
            $table->string('payment_method', 50);
            $table->date('payment_date');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('pharmacy_id')->references('id')->on('pharmacies')->onDelete('cascade');

            // Indexes
            $table->index('pharmacy_id');
            $table->index('payment_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacy_payouts');
    }
};
