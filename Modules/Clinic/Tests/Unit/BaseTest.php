<?php

namespace Modules\Clinic\Tests\Unit;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
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
     * Run only core + Clinic migrations to avoid MySQL-specific SQL in other modules.
     */
    protected function runMigrations(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            $this->artisan('migrate', [
                '--path' => 'database/migrations',
                '--force' => true,
            ]);

            // Appointment module must run first as Clinic migrations depend on its tables
            $this->artisan('migrate', [
                '--path' => 'Modules/Appointment/database/migrations',
                '--force' => true,
            ]);

            $this->artisan('migrate', [
                '--path' => 'Modules/Clinic/database/migrations',
                '--force' => true,
            ]);
        } else {
            $this->artisan('migrate', ['--force' => true]);
        }

        // Clean data for test isolation
        DB::table('doctor_independent_sessions')->delete();
        DB::table('doctor_independent_services')->delete();
        DB::table('users')->delete();
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
