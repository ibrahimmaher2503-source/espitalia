<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacy_stock_alerts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pharmacy_id');
            $table->unsignedBigInteger('inventory_id');
            $table->string('alert_type', 20);
            $table->unsignedInteger('threshold');
            $table->unsignedInteger('current_qty');
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->foreign('pharmacy_id')->references('id')->on('pharmacies')->onDelete('cascade');
            $table->foreign('inventory_id')->references('id')->on('pharmacy_inventory')->onDelete('cascade');
            $table->index(['pharmacy_id', 'is_resolved']);
            $table->index('alert_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacy_stock_alerts');
    }
};
