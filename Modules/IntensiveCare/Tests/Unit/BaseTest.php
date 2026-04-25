<?php

namespace Modules\IntensiveCare\Tests\Unit;

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
     * Run only core + IntensiveCare migrations, then clean tables
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
            '--path' => 'Modules/Location/database/migrations',
            '--force' => true,
        ]);

        $this->artisan('migrate', [
            '--path' => 'Modules/IntensiveCare/database/migrations',
            '--force' => true,
        ]);

        // Reset Spatie Permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Clean data for test isolation
        DB::table('icu_commission_earnings')->delete();
        DB::table('icu_payouts')->delete();
        DB::table('icu_commissions')->delete();
        DB::table('icu_admission_requests')->delete();
        DB::table('icu_departments')->delete();
        DB::table('hospitals')->delete();
        DB::table('model_has_roles')->delete();
        DB::table('role_has_permissions')->delete();
        DB::table('permissions')->delete();
        DB::table('roles')->delete();
        DB::table('users')->delete();
        DB::table('media')->delete();
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
