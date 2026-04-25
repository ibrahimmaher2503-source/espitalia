<?php

namespace Modules\RequestNurse\Tests\Unit;

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
     * Run only core + RequestNurse migrations, then clean tables
     * to ensure test isolation. Avoids other module migrations
     * that contain MySQL-specific SQL.
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
                '--path' => 'Modules/RequestNurse/database/migrations',
                '--force' => true,
            ]);
        } else {
            $this->artisan('migrate', ['--force' => true]);
        }

        // Clean data for test isolation
        DB::table('nurse_requests')->delete();
        DB::table('nurses')->delete();
        DB::table('model_has_roles')->delete();
        DB::table('role_has_permissions')->delete();
        DB::table('permissions')->delete();
        DB::table('roles')->delete();
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
