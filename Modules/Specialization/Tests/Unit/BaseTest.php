<?php

namespace Modules\Specialization\Tests\Unit;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\CreatesApplication;

abstract class BaseTest extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
        $this->disableForeignKeys();
    }

    protected function runCoreMigrations(): void
    {
        $this->artisan('migrate', [
            '--path' => 'database/migrations',
            '--force' => true,
        ]);
    }

    protected function runSpecializationMigrations(): void
    {
        $this->artisan('migrate', [
            '--path' => 'Modules/Specialization/database/migrations',
            '--force' => true,
        ]);
    }

    protected function createMinimalDoctorsTable(): void
    {
        if (! Schema::hasTable('doctors')) {
            Schema::create('doctors', function ($table) {
                $table->id();
                $table->unsignedBigInteger('doctor_id');
                $table->unsignedBigInteger('vendor_id')->nullable();
                $table->string('experience')->nullable();
                $table->string('experience_ar')->nullable();
                $table->string('experience_en')->nullable();
                $table->text('description')->nullable();
                $table->string('description_ar')->nullable();
                $table->string('description_en')->nullable();
                $table->text('Signature')->nullable();
                $table->tinyInteger('status')->default(1);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->unsignedBigInteger('deleted_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    protected function createMinimalNursesTable(): void
    {
        if (! Schema::hasTable('nurses')) {
            Schema::create('nurses', function ($table) {
                $table->id();
                $table->unsignedBigInteger('nurse_id');
                $table->string('specialization')->nullable();
                $table->string('specialization_ar')->nullable();
                $table->string('specialization_en')->nullable();
                $table->decimal('hourly_rate', 10, 2)->default(0);
                $table->string('availability_status')->default('available');
                $table->string('experience')->nullable();
                $table->string('service_area')->nullable();
                $table->text('about')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->unsignedBigInteger('deleted_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    protected function disableForeignKeys(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        }
    }

    protected function enableForeignKeys(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    protected function tearDown(): void
    {
        $this->enableForeignKeys();
        parent::tearDown();
    }
}
