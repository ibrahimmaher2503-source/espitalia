<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hospitals', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('hospital_type')->default('private');
            $table->string('address');
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('license_number')->nullable();
            $table->json('accepted_insurance')->nullable();
            $table->decimal('rating', 3, 2)->default(0);
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('vendor_id')->references('id')->on('users')->onDelete('set null');
            $table->index('status');
            $table->index('city');
            $table->index('hospital_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospitals');
    }
};
