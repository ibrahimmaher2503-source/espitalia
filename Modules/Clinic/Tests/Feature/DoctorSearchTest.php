<?php

namespace Modules\Clinic\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Clinic\Tests\Unit\BaseTest;
use Modules\Clinic\Models\Doctor;
use Modules\Location\Models\Governorate;
use Modules\Location\Models\GovernorateCity;
use App\Models\User;

class DoctorSearchTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->runSearchMigrations();
    }

    /**
     * Run migrations needed for doctor search tests.
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
        $user = User::factory()->create([
            'first_name' => 'Ahmad',
            'last_name'  => 'Ali',
        ]);

        Doctor::create([
            'doctor_id'  => $user->id,
            'experience' => '5 years',
            'vendor_id'  => $user->id,
            'status'     => 1,
        ]);

        $response = $this->getJson('/api/doctors/search');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'data' => [
                         'items',
                         'pagination' => [
                             'current_page',
                             'last_page',
                             'per_page',
                             'total',
                         ],
                     ],
                 ])
                 ->assertJson([
                     'status' => true,
                     'data' => [
                         'pagination' => [
                             'current_page' => 1,
                         ],
                     ],
                 ]);
    }

    public function test_filter_by_governorate_returns_matching_doctors()
    {
        $governorate = Governorate::create([
            'name_ar' => 'العاصمة',
            'name_en' => 'Capital',
        ]);

        $otherGovernorate = Governorate::create([
            'name_ar' => 'حولي',
            'name_en' => 'Hawalli',
        ]);

        $user1 = User::factory()->create([
            'first_name' => 'Doctor',
            'last_name'  => 'One',
        ]);

        $user2 = User::factory()->create([
            'first_name' => 'Doctor',
            'last_name'  => 'Two',
        ]);

        Doctor::create([
            'doctor_id'      => $user1->id,
            'experience'     => '3 years',
            'vendor_id'      => $user1->id,
            'governorate_id' => $governorate->id,
            'status'         => 1,
        ]);

        Doctor::create([
            'doctor_id'      => $user2->id,
            'experience'     => '5 years',
            'vendor_id'      => $user2->id,
            'governorate_id' => $otherGovernorate->id,
            'status'         => 1,
        ]);

        $response = $this->getJson('/api/doctors/search?governorate_id=' . $governorate->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'data' => [
                         'pagination' => [
                             'total' => 1,
                         ],
                     ],
                 ]);

        $items = $response->json('data.items');
        $this->assertCount(1, $items);
        $this->assertEquals('Doctor One', $items[0]['name']);
    }

    public function test_empty_results_return_valid_structure()
    {
        $response = $this->getJson('/api/doctors/search?governorate_id=99999');

        // governorate_id=99999 won't pass exists validation
        $response->assertStatus(422);

        // Search with no filters but empty database
        $response = $this->getJson('/api/doctors/search');

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => '',
                     'data' => [
                         'items' => [],
                         'pagination' => [
                             'current_page' => 1,
                             'total' => 0,
                         ],
                     ],
                 ]);
    }
}
