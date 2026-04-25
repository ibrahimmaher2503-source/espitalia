<?php

namespace Modules\HomeHealthcare\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HomeHealthcare\Models\HomeHealthcare;
use Tests\TestCase;

class HomeHealthcareSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_endpoint_returns_paginated_json_structure()
    {
        HomeHealthcare::create([
            'name_ar' => 'رعاية منزلية',
            'name_en' => 'Home Care',
            'service_type' => 'nursing',
            'status' => 1,
        ]);

        $response = $this->getJson('/api/home-healthcare/search');

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

    public function test_search_returns_only_active_records()
    {
        HomeHealthcare::create([
            'name_ar' => 'نشط',
            'name_en' => 'Active',
            'service_type' => 'nursing',
            'status' => 1,
        ]);

        HomeHealthcare::create([
            'name_ar' => 'غير نشط',
            'name_en' => 'Inactive',
            'service_type' => 'nursing',
            'status' => 0,
        ]);

        $response = $this->getJson('/api/home-healthcare/search');

        $response->assertStatus(200);

        $total = $response->json('data.pagination.total');
        $this->assertEquals(1, $total);
    }

    public function test_search_filters_by_service_type()
    {
        HomeHealthcare::create([
            'name_ar' => 'تمريض',
            'name_en' => 'Nursing',
            'service_type' => 'nursing',
            'status' => 1,
        ]);

        HomeHealthcare::create([
            'name_ar' => 'علاج طبيعي',
            'name_en' => 'Physiotherapy',
            'service_type' => 'physiotherapy',
            'status' => 1,
        ]);

        $response = $this->getJson('/api/home-healthcare/search?service_type=nursing');

        $response->assertStatus(200);

        $total = $response->json('data.pagination.total');
        $this->assertEquals(1, $total);
    }
}
