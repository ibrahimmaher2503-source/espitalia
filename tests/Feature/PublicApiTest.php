<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicApiTest extends TestCase
{
    // No database interactions needed — just route resolution.

    // --- Group A: simple route moves ---

    public function test_get_categories_is_public(): void
    {
        $this->getJson('/api/get-categories')->assertStatus(200);
    }

    public function test_get_services_is_public(): void
    {
        $this->getJson('/api/get-services')->assertStatus(200);
    }

    public function test_service_detail_is_public(): void
    {
        $this->getJson('/api/service-detail')->assertStatus(200);
    }

    public function test_get_clinics_is_public(): void
    {
        $this->getJson('/api/get-clinics')->assertStatus(200);
    }

    public function test_clinic_session_is_public(): void
    {
        $this->getJson('/api/clinic-session')->assertStatus(200);
    }

    public function test_get_clinic_session_list_is_public(): void
    {
        $this->getJson('/api/get-clinic-session-list')->assertStatus(200);
    }

    public function test_get_doctors_is_public(): void
    {
        $this->getJson('/api/get-doctors')->assertStatus(200);
    }

    public function test_doctor_details_is_public(): void
    {
        $this->getJson('/api/doctor-details')->assertStatus(200);
    }

    public function test_get_doctors_session_list_is_public(): void
    {
        $this->getJson('/api/get-doctors-session_list')->assertStatus(200);
    }

    public function test_doctor_session_is_public(): void
    {
        $this->getJson('/api/doctor-session')->assertStatus(200);
    }

    public function test_specialization_list_is_public(): void
    {
        $this->getJson('/api/specialization-list')->assertStatus(200);
    }

    public function test_country_list_is_public(): void
    {
        $this->getJson('/api/country-list')->assertStatus(200);
    }

    public function test_state_list_is_public(): void
    {
        $this->getJson('/api/state-list')->assertStatus(200);
    }

    public function test_city_list_is_public(): void
    {
        $this->getJson('/api/city-list')->assertStatus(200);
    }

    public function test_branches_list_is_public(): void
    {
        $this->getJson('/api/v1/branches')->assertStatus(200);
    }

    public function test_branch_show_is_public(): void
    {
        $this->getJson('/api/v1/branches/1')->assertStatus(200);
    }

    public function test_pharmacy_categories_is_public(): void
    {
        $this->getJson('/api/v1/pharmacy/categories')->assertStatus(200);
    }

    public function test_pharmacy_products_is_public(): void
    {
        $this->getJson('/api/v1/pharmacy/products')->assertStatus(200);
    }

    public function test_pharmacy_brands_filter_is_public(): void
    {
        $this->getJson('/api/v1/pharmacy/filters/brands')->assertStatus(200);
    }

    public function test_pharmacy_product_types_filter_is_public(): void
    {
        $this->getJson('/api/v1/pharmacy/filters/product-types')->assertStatus(200);
    }

    public function test_hospitals_list_is_public(): void
    {
        $this->getJson('/api/v1/hospitals')->assertStatus(200);
    }

    public function test_hospital_show_is_public(): void
    {
        $this->getJson('/api/v1/hospitals/1')->assertStatus(200);
    }

    public function test_icu_departments_is_public(): void
    {
        $this->getJson('/api/v1/icu-departments')->assertStatus(200);
    }

    public function test_service_list_resource_is_public(): void
    {
        $this->getJson('/api/service')->assertStatus(200);
    }

    // --- Group B: new controllers ---

    public function test_blog_list_is_public(): void
    {
        $response = $this->getJson('/api/v1/blog');
        $response->assertStatus(200)
                 ->assertJsonStructure(['data', 'meta']);
    }

    public function test_faq_list_is_public(): void
    {
        $response = $this->getJson('/api/v1/faq');
        $response->assertStatus(200)
                 ->assertJsonStructure(['data']);
    }

    public function test_frontendsetting_is_public(): void
    {
        $response = $this->getJson('/api/v1/frontendsetting');
        $response->assertStatus(200)
                 ->assertJsonStructure(['data']);
    }

    public function test_frontend_is_public(): void
    {
        $response = $this->getJson('/api/v1/frontend');
        $response->assertStatus(200)
                 ->assertJsonStructure(['data']);
    }

    public function test_constants_is_public(): void
    {
        $response = $this->getJson('/api/constants');
        $response->assertStatus(200)
                 ->assertJsonStructure(['data']);
    }

    // --- Verify write ops still require auth ---

    public function test_create_branch_still_requires_auth(): void
    {
        $this->postJson('/api/v1/branches', [])->assertStatus(401);
    }

    public function test_pharmacy_cart_still_requires_auth(): void
    {
        $this->getJson('/api/v1/pharmacy/cart')->assertStatus(401);
    }

    public function test_update_branch_still_requires_auth(): void
    {
        $this->putJson('/api/v1/branches/1', [])->assertStatus(401);
    }
}
