<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nurses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nurse_id')->comment('FK to users.id');
            $table->unsignedBigInteger('vendor_id')->nullable()->comment('FK to users.id - clinic admin');
            $table->string('specialization')->nullable();
            $table->string('experience')->nullable();
            $table->text('about')->nullable();
            $table->enum('availability_status', ['available', 'busy', 'off_duty'])->default('available');
            $table->string('service_area')->nullable();
            $table->double('hourly_rate')->default(0);
            $table->tinyInteger('status')->default(1)->comment('1=active, 0=inactive');
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('nurse_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('nurses');
    }
};
