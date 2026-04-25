<?php

namespace Modules\RadiologyLab\Tests\Unit;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Tests\CreatesApplication;

abstract class BaseTest extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        $this->runMigrations();
    }

    /**
     * Run only core + RadiologyLab migrations, then clean tables
     * to ensure test isolation. Avoids other module migrations
     * that contain MySQL-specific SQL.
     */
    protected function runMigrations(): void
    {
        // Ensure we're using SQLite for tests
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => ':memory:']);

        $this->disableForeignKeys();

        $this->artisan('migrate', [
            '--path' => 'database/migrations',
            '--force' => true,
        ]);

        $this->artisan('migrate', [
            '--path' => 'Modules/RadiologyLab/database/migrations',
            '--force' => true,
        ]);

        // Reset Spatie Permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Clean data for test isolation
        DB::table('facility_bookings')->delete();
        DB::table('facility_sessions')->delete();
        DB::table('test_reports')->delete();
        DB::table('test_order_items')->delete();
        DB::table('test_orders')->delete();
        DB::table('lab_tests')->delete();
        DB::table('lab_test_categories')->delete();
        DB::table('model_has_roles')->delete();
        DB::table('role_has_permissions')->delete();
        DB::table('permissions')->delete();
        DB::table('roles')->delete();
        DB::table('users')->delete();
    }

    protected function disableForeignKeys(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF');
    }

    protected function enableForeignKeys(): void
    {
        DB::statement('PRAGMA foreign_keys = ON');
    }

    protected function tearDown(): void
    {
        $this->enableForeignKeys();

        parent::tearDown();
    }
}
