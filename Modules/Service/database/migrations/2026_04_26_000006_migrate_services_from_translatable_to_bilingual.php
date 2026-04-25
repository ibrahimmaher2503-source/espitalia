<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('name_ar')->nullable()->after('name');
            $table->string('name_en')->nullable()->after('name_ar');
            $table->longText('description_ar')->nullable()->after('description');
            $table->longText('description_en')->nullable()->after('description_ar');
        });

        // Extract JSON translatable data into new columns
        $services = DB::table('services')->get();
        foreach ($services as $service) {
            $nameData = json_decode($service->name, true);
            $descData = json_decode($service->description, true);

            $updates = [];
            if (is_array($nameData)) {
                $updates['name_en'] = $nameData['en'] ?? null;
                $updates['name_ar'] = $nameData['ar'] ?? null;
            } else {
                $updates['name_en'] = $service->name;
            }

            if (is_array($descData)) {
                $updates['description_en'] = $descData['en'] ?? null;
                $updates['description_ar'] = $descData['ar'] ?? null;
            } else {
                $updates['description_en'] = $service->description;
            }

            DB::table('services')->where('id', $service->id)->update($updates);
        }
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'name_en', 'description_ar', 'description_en']);
        });
    }
};
