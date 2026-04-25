<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Column was already 'longitude' in the database.
        // The typo 'logitude' only existed in the PHP model's fillable array (now fixed).
    }

    public function down(): void
    {
        // No-op
    }
};
