<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('appointment_transactions')) {
            return;
        }

        Schema::table('appointment_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('appointment_transactions', 'payment_method')) {
                $table->string('payment_method', 30)->nullable()->after('transaction_type');
            }
            if (! Schema::hasColumn('appointment_transactions', 'payment_gateway')) {
                $table->string('payment_gateway', 30)->nullable()->after('payment_method');
            }
            if (! Schema::hasColumn('appointment_transactions', 'currency')) {
                $table->string('currency', 3)->default('SAR')->after('total_amount');
            }
            if (! Schema::hasColumn('appointment_transactions', 'refund_amount')) {
                $table->decimal('refund_amount', 10, 2)->default(0)->after('currency');
            }
            if (! Schema::hasColumn('appointment_transactions', 'refund_status')) {
                $table->string('refund_status', 20)->nullable()->after('refund_amount');
            }
            if (! Schema::hasColumn('appointment_transactions', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('refund_status');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('appointment_transactions')) {
            return;
        }

        Schema::table('appointment_transactions', function (Blueprint $table) {
            $table->dropColumn(array_filter(
                ['payment_method', 'payment_gateway', 'currency', 'refund_amount', 'refund_status', 'paid_at'],
                fn ($col) => Schema::hasColumn('appointment_transactions', $col)
            ));
        });
    }
};
