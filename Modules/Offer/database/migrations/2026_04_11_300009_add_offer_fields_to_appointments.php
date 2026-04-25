<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->unsignedBigInteger('offer_id')->nullable()->after('cancellation_charge_amount');
            $table->decimal('offer_discount', 12, 2)->default(0)->after('offer_id');
            $table->string('offer_code', 50)->nullable()->after('offer_discount');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['offer_id', 'offer_discount', 'offer_code']);
        });
    }
};
