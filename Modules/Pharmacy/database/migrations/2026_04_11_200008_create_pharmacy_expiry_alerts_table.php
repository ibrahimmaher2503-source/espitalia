<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacy_expiry_alerts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pharmacy_id');
            $table->unsignedBigInteger('batch_id');
            $table->string('alert_type', 20);
            $table->date('expiry_date');
            $table->integer('days_until_expiry');
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->foreign('pharmacy_id')->references('id')->on('pharmacies')->onDelete('cascade');
            $table->foreign('batch_id')->references('id')->on('pharmacy_product_batches')->onDelete('cascade');
            $table->index(['pharmacy_id', 'is_resolved']);
            $table->index('alert_type');
            $table->index('expiry_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacy_expiry_alerts');
    }
};
