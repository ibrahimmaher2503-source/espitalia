<?php

namespace Modules\Specialization\Tests\Feature;

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Modules\Clinic\Models\Doctor;
use Modules\Specialization\Models\Specialization;

class SpecializationApiTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->runApiMigrations();
    }

    protected function runApiMigrations(): void
    {
        $this->runCoreMigrations();
        $this->runSpecializationMigrations();
        $this->createMinimalDoctorsTable();
        $this->createMinimalNursesTable();
    }

    protected function authenticatedUser(): User
    {
        $user = User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'api@test.com',
            'password' => bcrypt('password'),
            'user_type' => 'admin',
        ]);

        Sanctum::actingAs($user);

        return $user;
    }

    public function test_index_returns_paginated_list(): void
    {
        $this->authenticatedUser();

        Specialization::create(['name_en' => 'Cardiology', 'status' => 1]);
        Specialization::create(['name_en' => 'Neurology', 'status' => 1]);

        $response = $this->getJson(route('api.specializations.index'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data',
                'meta',
            ]);
    }

    public function test_index_search_filters_by_name(): void
    {
        $this->authenticatedUser();

        Specialization::create(['name_en' => 'Cardiology', 'status' => 1]);
        Specialization::create(['name_en' => 'Neurology', 'status' => 1]);

        $response = $this->getJson(route('api.specializations.index', ['search' => 'Cardio']));

        $response->assertStatus(200);
        $data = $response->json('data');
        $items = is_array($data) && isset($data[0]) ? $data : ($data['data'] ?? $data);
        $this->assertCount(1, $items);
        $this->assertEquals('Cardiology', $items[0]['name_en']);
    }

    public function test_show_returns_single_specialization(): void
    {
        $this->authenticatedUser();

        $spec = Specialization::create(['name_en' => 'Cardiology', 'status' => 1]);

        $response = $this->getJson(route('api.specializations.show', $spec->id));

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $spec->id)
            ->assertJsonPath('data.name_en', 'Cardiology');
    }

    public function test_show_returns_404_for_nonexistent(): void
    {
        $this->authenticatedUser();

        $response = $this->getJson(route('api.specializations.show', 99999));

        $response->assertStatus(404);
    }

    public function test_unauthenticated_returns_401(): void
    {
        $response = $this->getJson(route('api.specializations.index'));

        $response->assertStatus(401);
    }

    public function test_doctors_endpoint_returns_doctors(): void
    {
        $user = $this->authenticatedUser();

        $spec = Specialization::create(['name_en' => 'Cardiology', 'status' => 1]);

        $doctorUser = User::create([
            'first_name' => 'Dr',
            'last_name' => 'Heart',
            'email' => 'dr@test.com',
            'password' => bcrypt('password'),
            'user_type' => 'doctor',
        ]);

        $doctor = Doctor::create([
            'doctor_id' => $doctorUser->id,
            'status' => 1,
        ]);

        $doctor->specializations()->sync([$spec->id]);

        $response = $this->getJson(route('api.specializations.doctors', $spec->id));

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertNotEmpty($data);
    }
}
