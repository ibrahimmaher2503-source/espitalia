<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const COLUMNS = [
        'otp',
        'otp_hash',
        'otp_expires_at',
        'otp_attempts',
        'otp_last_sent_at',
        'is_google_authentication',
        'google_authentication_type',
        'google2fa_secret',
    ];

    public function up(): void
    {
        $columns = array_values(array_filter(
            self::COLUMNS,
            fn (string $column) => Schema::hasColumn('users', $column),
        ));

        if ($columns !== []) {
            Schema::table('users', fn (Blueprint $table) => $table->dropColumn($columns));
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('otp')->nullable();
            $table->string('otp_hash')->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->unsignedTinyInteger('otp_attempts')->default(0);
            $table->timestamp('otp_last_sent_at')->nullable();
            $table->integer('is_google_authentication')->default(0);
            $table->string('google_authentication_type')->nullable();
            $table->text('google2fa_secret')->nullable();
        });
    }
};
