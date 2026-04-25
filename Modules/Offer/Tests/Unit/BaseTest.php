<?php

namespace Modules\Offer\Tests\Unit;

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

    protected function runMigrations(): void
    {
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => ':memory:']);

        $this->disableForeignKeys();

        // Core migrations
        $this->artisan('migrate', [
            '--path' => 'database/migrations',
            '--force' => true,
        ]);

        // Offer module migrations (only the create tables, not alter tables)
        $this->artisan('migrate', [
            '--path' => 'Modules/Offer/database/migrations/2026_04_11_300001_create_offers_table.php',
            '--force' => true,
        ]);
        $this->artisan('migrate', [
            '--path' => 'Modules/Offer/database/migrations/2026_04_11_300002_create_offer_services_table.php',
            '--force' => true,
        ]);
        $this->artisan('migrate', [
            '--path' => 'Modules/Offer/database/migrations/2026_04_11_300003_create_offer_branches_table.php',
            '--force' => true,
        ]);
        $this->artisan('migrate', [
            '--path' => 'Modules/Offer/database/migrations/2026_04_11_300006_create_offer_schedules_table.php',
            '--force' => true,
        ]);
        $this->artisan('migrate', [
            '--path' => 'Modules/Offer/database/migrations/2026_04_11_300007_create_offer_usages_table.php',
            '--force' => true,
        ]);
        $this->artisan('migrate', [
            '--path' => 'Modules/Offer/database/migrations/2026_04_11_300008_create_offer_audit_logs_table.php',
            '--force' => true,
        ]);
    }

    protected function disableForeignKeys(): void
    {
        if (config('database.default') === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
        }
    }

    protected function cleanTables(): void
    {
        DB::table('offer_usages')->delete();
        DB::table('offer_audit_logs')->delete();
        DB::table('offer_schedules')->delete();
        DB::table('offer_services')->delete();
        DB::table('offer_branches')->delete();
        DB::table('offers')->delete();
    }
}
