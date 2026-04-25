<?php

namespace Modules\Location\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Location\database\seeders\GovernorateSeeder;
use Modules\Location\Models\Governorate;
use Tests\TestCase;

class LocationApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(GovernorateSeeder::class);
    }

    public function test_governorates_returns_all_27()
    {
        $response = $this->getJson('/api/governorates');

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonCount(27, 'data');
    }

    public function test_governorates_in_arabic()
    {
        $response = $this->getJson('/api/governorates', [
            'Accept-Language' => 'ar',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true);

        $data = $response->json('data');
        $names = collect($data)->pluck('name')->toArray();

        $this->assertContains('القاهرة', $names);
        $this->assertContains('الإسكندرية', $names);
    }

    public function test_cities_require_governorate_id()
    {
        $response = $this->getJson('/api/cities');

        $response->assertStatus(422);
    }

    public function test_cities_filter_by_governorate()
    {
        $governorate = Governorate::where('name_en', 'Cairo')->first();

        $response = $this->getJson('/api/cities?governorate_id='.$governorate->id);

        $response->assertStatus(200)
            ->assertJsonPath('status', true);

        $data = $response->json('data');
        $this->assertCount(7, $data);

        $names = collect($data)->pluck('name')->toArray();
        $this->assertContains('Nasr City', $names);
        $this->assertContains('Maadi', $names);
    }

    public function test_invalid_governorate_returns_422()
    {
        $response = $this->getJson('/api/cities?governorate_id=9999');

        $response->assertStatus(422);
    }

    public function test_default_language_is_english()
    {
        $response = $this->getJson('/api/governorates');

        $response->assertStatus(200);

        $data = $response->json('data');
        $names = collect($data)->pluck('name')->toArray();

        $this->assertContains('Cairo', $names);
        $this->assertContains('Alexandria', $names);
        $this->assertNotContains('القاهرة', $names);
    }
}
