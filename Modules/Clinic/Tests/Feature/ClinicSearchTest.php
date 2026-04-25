<?php

namespace Modules\Clinic\Tests\Feature;

use Modules\Clinic\Tests\Unit\BaseTest;
use Modules\Clinic\Models\Clinics;
use Modules\Location\Models\Governorate;
use Modules\Location\Models\GovernorateCity;
use App\Models\User;

class ClinicSearchTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->runSearchMigrations();
    }

    /**
     * Run migrations needed for clinic search tests.
     */
    protected function runSearchMigrations(): void
    {
        $this->artisan('migrate', [
            '--path' => 'database/migrations',
            '--force' => true,
        ]);

        $this->artisan('migrate', [
            '--path' => 'Modules/Location/database/migrations',
            '--force' => true,
        ]);

        $this->artisan('migrate', [
            '--path' => 'Modules/Appointment/database/migrations',
            '--force' => true,
        ]);

        $this->artisan('migrate', [
            '--path' => 'Modules/Clinic/database/migrations',
            '--force' => true,
        ]);
    }

    public function test_unfiltered_search_returns_paginated_results()
    {
        $vendor = User::factory()->create();

        Clinics::create([
            'name'           => 'Test Clinic',
            'email'          => 'test@clinic.com',
            'slug'           => 'test-clinic',
            'contact_number' => '12345678',
            'address'        => '123 Main St',
            'vendor_id'      => $vendor->id,
            'status'         => 1,
        ]);

        $response = $this->getJson('/api/clinics/search');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'data' => [
                         'items',
                         'pagination',
                     ],
                 ]);
    }

    public function test_filter_by_governorate_returns_matching_clinics()
    {
        $governorate = Governorate::create([
            'name_ar' => 'العاصمة',
            'name_en' => 'Capital',
        ]);

        $otherGovernorate = Governorate::create([
            'name_ar' => 'حولي',
            'name_en' => 'Hawalli',
        ]);

        $vendor = User::factory()->create();

        Clinics::create([
            'name'           => 'Capital Clinic',
            'email'          => 'capital@clinic.com',
            'slug'           => 'capital-clinic',
            'contact_number' => '11111111',
            'address'        => 'Capital Address',
            'vendor_id'      => $vendor->id,
            'governorate_id' => $governorate->id,
            'status'         => 1,
        ]);

        Clinics::create([
            'name'           => 'Hawalli Clinic',
            'email'          => 'hawalli@clinic.com',
            'slug'           => 'hawalli-clinic',
            'contact_number' => '22222222',
            'address'        => 'Hawalli Address',
            'vendor_id'      => $vendor->id,
            'governorate_id' => $otherGovernorate->id,
            'status'         => 1,
        ]);

        $response = $this->getJson('/api/clinics/search?governorate_id=' . $governorate->id);

        $response->assertStatus(200);

        $data = $response->json('data.items');
        $this->assertCount(1, $data);
        $this->assertEquals('Capital Clinic', $data[0]['name']);
    }

    public function test_empty_results_return_valid_structure()
    {
        // governorate_id=99999 won't pass exists validation
        $response = $this->getJson('/api/clinics/search?governorate_id=99999');
        $response->assertStatus(422);

        // Search with no filters and empty database returns empty data
        $response = $this->getJson('/api/clinics/search');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'data' => [
                         'items',
                         'pagination',
                     ],
                 ])
                 ->assertJson([
                     'status' => true,
                     'data' => [
                         'items' => [],
                     ],
                 ]);
    }
}
