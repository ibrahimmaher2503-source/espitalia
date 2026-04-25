<?php

namespace Modules\DoctorVisit\Tests\Unit;

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

    /**
     * Run core + DoctorVisit migrations only.
     * Creates a stub doctors table for tests needing doctor records.
     */
    protected function runMigrations(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            $this->artisan('migrate', [
                '--path' => 'database/migrations',
                '--force' => true,
            ]);

            $this->artisan('migrate', [
                '--path' => 'Modules/DoctorVisit/database/migrations',
                '--force' => true,
            ]);

            // Create a stub doctors table so exists:doctors,id validation works.
            // The Clinic module migration may contain MySQL-specific SQL, so we
            // create a minimal schema for test isolation.
            if (! Schema::hasTable('doctors')) {
                Schema::create('doctors', function ($table) {
                    $table->bigIncrements('id');
                    $table->unsignedBigInteger('doctor_id')->nullable()->comment('FK to users.id');
                    $table->integer('status')->default(1);
                    $table->timestamps();
                    $table->softDeletes();
                });
            }
        } else {
            $this->artisan('migrate', ['--force' => true]);
        }

        // Clean data for test isolation
        DB::table('doctor_visit_status_histories')->delete();
        DB::table('doctor_visit_requests')->delete();
        DB::table('doctors')->delete();
        DB::table('model_has_roles')->delete();
        DB::table('role_has_permissions')->delete();
        DB::table('permissions')->delete();
        DB::table('roles')->delete();
        DB::table('users')->delete();

        // Flush Spatie's in-memory permission cache so stale role/permission
        // data doesn't persist across test methods.
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
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
