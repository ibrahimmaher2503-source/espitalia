<?php

namespace Modules\RadiologyLab\Tests\Feature;

use Modules\Location\Models\Governorate;
use Modules\Location\Models\GovernorateCity;
use Modules\RadiologyLab\Models\RadiologyCenter;
use Modules\RadiologyLab\Tests\Unit\BaseTest;

class RadiologySearchTest extends BaseTest
{

    protected function setUp(): void
    {
        parent::setUp();

        $this->runSearchMigrations();
    }

    /**
     * Run additional migrations needed for search tests.
     */
    protected function runSearchMigrations(): void
    {
        $this->artisan('migrate', [
            '--path' => 'Modules/Location/database/migrations',
            '--force' => true,
        ]);

        $this->artisan('migrate', [
            '--path' => 'Modules/RadiologyLab/database/migrations',
            '--force' => true,
        ]);
    }

    public function test_search_endpoint_returns_paginated_json_structure()
    {
        RadiologyCenter::create([
            'name_ar'   => 'مركز أشعة الكويت',
            'name_en'   => 'Kuwait Radiology Center',
            'scan_type' => 'MRI',
            'status'    => 1,
        ]);

        $response = $this->getJson('/api/radiology/search');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'items',
                    'pagination' => [
                        'current_page',
                        'last_page',
                        'per_page',
                        'total',
                    ],
                ],
                'message',
            ]);
    }

    public function test_search_returns_only_active_centers()
    {
        RadiologyCenter::create([
            'name_ar'   => 'مركز نشط',
            'name_en'   => 'Active Center',
            'scan_type' => 'CT',
            'status'    => 1,
        ]);

        RadiologyCenter::create([
            'name_ar'   => 'مركز غير نشط',
            'name_en'   => 'Inactive Center',
            'scan_type' => 'CT',
            'status'    => 0,
        ]);

        $response = $this->getJson('/api/radiology/search');

        $response->assertStatus(200);

        $total = $response->json('data.pagination.total');
        $this->assertEquals(1, $total);
    }

    public function test_search_filters_by_governorate()
    {
        $governorate = Governorate::create([
            'name_ar' => 'العاصمة',
            'name_en' => 'Capital',
        ]);

        $otherGovernorate = Governorate::create([
            'name_ar' => 'حولي',
            'name_en' => 'Hawalli',
        ]);

        RadiologyCenter::create([
            'name_ar'        => 'مركز العاصمة',
            'name_en'        => 'Capital Center',
            'scan_type'      => 'MRI',
            'governorate_id' => $governorate->id,
            'status'         => 1,
        ]);

        RadiologyCenter::create([
            'name_ar'        => 'مركز حولي',
            'name_en'        => 'Hawalli Center',
            'scan_type'      => 'MRI',
            'governorate_id' => $otherGovernorate->id,
            'status'         => 1,
        ]);

        $response = $this->getJson('/api/radiology/search?governorate_id=' . $governorate->id);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'data'   => [
                    'pagination' => [
                        'total' => 1,
                    ],
                ],
            ]);

        $items = $response->json('data.items');
        $this->assertCount(1, $items);
        $this->assertEquals('Capital Center', $items[0]['name']);
    }

    public function test_search_filters_by_city()
    {
        $governorate = Governorate::create([
            'name_ar' => 'العاصمة',
            'name_en' => 'Capital',
        ]);

        $city = GovernorateCity::create([
            'governorate_id' => $governorate->id,
            'name_ar'        => 'الشرق',
            'name_en'        => 'Sharq',
        ]);

        $otherCity = GovernorateCity::create([
            'governorate_id' => $governorate->id,
            'name_ar'        => 'المرقاب',
            'name_en'        => 'Mirqab',
        ]);

        RadiologyCenter::create([
            'name_ar'        => 'مركز الشرق',
            'name_en'        => 'Sharq Center',
            'scan_type'      => 'X-Ray',
            'governorate_id' => $governorate->id,
            'city_id'        => $city->id,
            'status'         => 1,
        ]);

        RadiologyCenter::create([
            'name_ar'        => 'مركز المرقاب',
            'name_en'        => 'Mirqab Center',
            'scan_type'      => 'X-Ray',
            'governorate_id' => $governorate->id,
            'city_id'        => $otherCity->id,
            'status'         => 1,
        ]);

        $response = $this->getJson('/api/radiology/search?city_id=' . $city->id);

        $response->assertStatus(200);

        $total = $response->json('data.pagination.total');
        $this->assertEquals(1, $total);
    }

    public function test_search_filters_by_scan_type()
    {
        RadiologyCenter::create([
            'name_ar'   => 'مركز رنين',
            'name_en'   => 'MRI Center',
            'scan_type' => 'MRI',
            'status'    => 1,
        ]);

        RadiologyCenter::create([
            'name_ar'   => 'مركز أشعة سينية',
            'name_en'   => 'X-Ray Center',
            'scan_type' => 'X-Ray',
            'status'    => 1,
        ]);

        $response = $this->getJson('/api/radiology/search?scan_type=MRI');

        $response->assertStatus(200);

        $total = $response->json('data.pagination.total');
        $this->assertEquals(1, $total);
    }

    public function test_empty_results_return_valid_structure()
    {
        $response = $this->getJson('/api/radiology/search');

        $response->assertStatus(200)
            ->assertJson([
                'status'  => true,
                'message' => '',
                'data'    => [
                    'items'      => [],
                    'pagination' => [
                        'current_page' => 1,
                        'total'        => 0,
                    ],
                ],
            ]);
    }

    public function test_search_validates_invalid_governorate_id()
    {
        $response = $this->getJson('/api/radiology/search?governorate_id=99999');

        $response->assertStatus(422);
    }

    public function test_search_response_includes_scan_type()
    {
        RadiologyCenter::create([
            'name_ar'   => 'مركز أشعة',
            'name_en'   => 'Scan Center',
            'scan_type' => 'Ultrasound',
            'status'    => 1,
        ]);

        $response = $this->getJson('/api/radiology/search');

        $response->assertStatus(200);

        $items = $response->json('data.items');
        $this->assertCount(1, $items);
        $this->assertEquals('Ultrasound', $items[0]['scan_type']);
    }
}
