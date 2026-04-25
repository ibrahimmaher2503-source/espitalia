<?php

namespace Modules\RequestNurse\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\RequestNurse\Models\Nurse;
use Modules\RequestNurse\Tests\Unit\BaseTest;

class NurseSearchTest extends BaseTest
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_search_endpoint_returns_paginated_json_structure()
    {
        $user = User::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
        ]);

        Nurse::factory()->create([
            'nurse_id' => $user->id,
            'status' => 1,
            'availability_status' => 'available',
            'specialization_en' => 'Pediatric', 'specialization_ar' => 'أطفال',
        ]);

        $response = $this->getJson('/api/nurses/search');

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

    public function test_search_returns_only_active_nurses()
    {
        Nurse::factory()->create(['status' => 1]);
        Nurse::factory()->create(['status' => 0]);

        $response = $this->getJson('/api/nurses/search');

        $response->assertStatus(200);

        $total = $response->json('data.pagination.total');
        $this->assertEquals(1, $total);
    }

    public function test_search_filters_by_availability()
    {
        Nurse::factory()->create([
            'status' => 1,
            'availability_status' => 'available',
        ]);
        Nurse::factory()->create([
            'status' => 1,
            'availability_status' => 'busy',
        ]);

        $response = $this->getJson('/api/nurses/search?availability=available');

        $response->assertStatus(200);

        $total = $response->json('data.pagination.total');
        $this->assertEquals(1, $total);
    }

    public function test_search_filters_by_specialty()
    {
        Nurse::factory()->create([
            'status' => 1,
            'specialization_en' => 'Cardiac', 'specialization_ar' => 'قلب',
        ]);
        Nurse::factory()->create([
            'status' => 1,
            'specialization_en' => 'Pediatric', 'specialization_ar' => 'أطفال',
        ]);

        $response = $this->getJson('/api/nurses/search?specialty=Cardiac');

        $response->assertStatus(200);

        $total = $response->json('data.pagination.total');
        $this->assertEquals(1, $total);
    }

    public function test_search_validates_invalid_availability()
    {
        $response = $this->getJson('/api/nurses/search?availability=invalid_status');

        $response->assertStatus(422);
    }

    public function test_search_validates_invalid_gender()
    {
        $response = $this->getJson('/api/nurses/search?gender=unknown');

        $response->assertStatus(422);
    }
}
