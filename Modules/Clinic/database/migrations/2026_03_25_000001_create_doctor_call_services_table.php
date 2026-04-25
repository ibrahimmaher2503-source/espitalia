<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_call_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doctor_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('call_type', ['video', 'phone']);
            $table->integer('duration_min')->default(15);
            $table->integer('time_slot')->default(15);
            $table->double('charges')->default(0);
            $table->tinyInteger('discount')->default(0);
            $table->string('discount_type')->nullable();
            $table->double('discount_value')->default(0);
            $table->tinyInteger('is_inclusive_tax')->default(0);
            $table->text('inclusive_tax')->nullable();
            $table->double('inclusive_tax_price')->default(0);
            $table->tinyInteger('status')->default(1);

            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_call_services');
    }
};
