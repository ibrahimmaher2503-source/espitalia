<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->json('name'); // Spatie translatable {ar, en}
            $table->string('slug')->unique();
            $table->unsignedBigInteger('vendor_id');
            $table->string('type', 30)->default('clinic'); // clinic, pharmacy, lab, radiology_center, hospital, multi
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('phone_secondary', 20)->nullable();
            $table->text('address')->nullable();
            $table->unsignedBigInteger('governorate_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->json('description')->nullable(); // Spatie translatable {ar, en}
            $table->json('operating_hours')->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->string('license_number', 100)->nullable();
            $table->string('tax_number', 100)->nullable();
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('vendor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('manager_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('governorate_id')->references('id')->on('governorates')->onDelete('set null');
            $table->foreign('city_id')->references('id')->on('governorate_cities')->onDelete('set null');

            $table->index('vendor_id');
            $table->index('governorate_id');
            $table->index('city_id');
            $table->index('type');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
