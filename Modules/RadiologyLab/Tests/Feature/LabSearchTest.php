<?php

namespace Modules\RadiologyLab\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Location\Models\Governorate;
use Modules\Location\Models\GovernorateCity;
use Modules\RadiologyLab\Models\Lab;
use Modules\RadiologyLab\Tests\Unit\BaseTest;

class LabSearchTest extends BaseTest
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
        Lab::create([
            'name_ar' => 'مختبر الكويت',
            'name_en' => 'Kuwait Lab',
            'status'  => 1,
        ]);

        $response = $this->getJson('/api/labs/search');

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
            ])
            ->assertJsonPath('data.items.0.name_ar', 'مختبر الكويت')
            ->assertJsonPath('data.items.0.name_en', 'Kuwait Lab');
    }

    public function test_search_returns_only_active_labs()
    {
        Lab::create([
            'name_ar' => 'مختبر نشط',
            'name_en' => 'Active Lab',
            'status'  => 1,
        ]);

        Lab::create([
            'name_ar' => 'مختبر غير نشط',
            'name_en' => 'Inactive Lab',
            'status'  => 0,
        ]);

        $response = $this->getJson('/api/labs/search');

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

        Lab::create([
            'name_ar'        => 'مختبر العاصمة',
            'name_en'        => 'Capital Lab',
            'governorate_id' => $governorate->id,
            'status'         => 1,
        ]);

        Lab::create([
            'name_ar'        => 'مختبر حولي',
            'name_en'        => 'Hawalli Lab',
            'governorate_id' => $otherGovernorate->id,
            'status'         => 1,
        ]);

        $response = $this->getJson('/api/labs/search?governorate_id=' . $governorate->id);

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
        $this->assertEquals('Capital Lab', $items[0]['name']);
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

        Lab::create([
            'name_ar'        => 'مختبر الشرق',
            'name_en'        => 'Sharq Lab',
            'governorate_id' => $governorate->id,
            'city_id'        => $city->id,
            'status'         => 1,
        ]);

        Lab::create([
            'name_ar'        => 'مختبر المرقاب',
            'name_en'        => 'Mirqab Lab',
            'governorate_id' => $governorate->id,
            'city_id'        => $otherCity->id,
            'status'         => 1,
        ]);

        $response = $this->getJson('/api/labs/search?city_id=' . $city->id);

        $response->assertStatus(200);

        $total = $response->json('data.pagination.total');
        $this->assertEquals(1, $total);
    }

    public function test_search_filters_by_test_name()
    {
        Lab::create([
            'name_ar' => 'مختبر الدم',
            'name_en' => 'Blood Lab',
            'status'  => 1,
        ]);

        Lab::create([
            'name_ar' => 'مختبر الأشعة',
            'name_en' => 'Radiology Lab',
            'status'  => 1,
        ]);

        $response = $this->getJson('/api/labs/search?test_name=Blood');

        $response->assertStatus(200);

        $total = $response->json('data.pagination.total');
        $this->assertEquals(1, $total);
    }

    public function test_empty_results_return_valid_structure()
    {
        $response = $this->getJson('/api/labs/search');

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
        $response = $this->getJson('/api/labs/search?governorate_id=99999');

        $response->assertStatus(422);
    }
}
