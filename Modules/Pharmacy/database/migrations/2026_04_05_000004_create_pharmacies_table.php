<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 20);
            $table->text('address');
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->decimal('delivery_fee', 8, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacies');
    }
};
