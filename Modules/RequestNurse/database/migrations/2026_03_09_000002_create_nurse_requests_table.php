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
        Schema::create('nurse_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id')->comment('FK to users.id');
            $table->unsignedBigInteger('nurse_id')->nullable()->comment('FK to users.id - assigned nurse');
            $table->text('service_description');
            $table->dateTime('request_date')->useCurrent();
            $table->date('preferred_date');
            $table->time('preferred_time')->nullable();
            $table->double('duration_hours')->default(1);
            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('contact_number');
            $table->string('status')->default('pending')->comment('pending, confirmed, in_progress, completed, cancelled');
            $table->tinyInteger('payment_status')->default(0)->comment('0=unpaid, 1=paid');
            $table->double('total_amount')->default(0);
            $table->text('patient_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->string('cancelled_by')->nullable()->comment('patient, admin, nurse');
            $table->text('cancellation_reason')->nullable();
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('patient_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('nurse_requests');
    }
};
