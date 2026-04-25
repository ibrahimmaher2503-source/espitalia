<?php

namespace Modules\RadiologyLab\Tests\Feature;

use App\Models\User;
use Modules\Location\Models\Governorate;
use Modules\Location\Models\GovernorateCity;
use Modules\RadiologyLab\Models\RadiologyCenter;
use Modules\RadiologyLab\Tests\Unit\BaseTest;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RadiologyCentersCrudTest extends BaseTest
{

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->runCrudMigrations();
        $this->seedPermissions();

        $this->admin = $this->createAdminUser();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    protected function runCrudMigrations(): void
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

    protected function seedPermissions(): void
    {
        $permissions = [
            'view_radiology_centers', 'add_radiology_centers', 'edit_radiology_centers', 'delete_radiology_centers',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web'], ['title' => 'Admin']);
        $role->givePermissionTo($permissions);
    }

    protected function createAdminUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        // Pre-load roles via direct query so loadMissing() skips the broken eager-load path
        $user->setRelation('roles', $user->roles()->get());

        return $user;
    }

    public function test_admin_can_view_radiology_centers_index()
    {
        $response = $this->actingAs($this->admin)->get(route('backend.radiology-centers.index'));

        $response->assertStatus(200);
    }

    public function test_admin_can_create_center_with_bilingual_names()
    {
        $data = [
            'name_ar' => 'مركز أشعة النيل',
            'name_en' => 'Nile Radiology Center',
            'scan_type' => 'MRI',
            'status' => 1,
        ];

        $response = $this->actingAs($this->admin)->post(route('backend.radiology-centers.store'), $data);

        $response->assertRedirect(route('backend.radiology-centers.index'));

        $this->assertDatabaseHas('radiology_centers', [
            'name_ar' => 'مركز أشعة النيل',
            'name_en' => 'Nile Radiology Center',
            'scan_type' => 'MRI',
        ]);
    }

    public function test_validation_rejects_missing_name_ar()
    {
        $data = [
            'name_en' => 'Test Center',
            'status' => 1,
        ];

        $response = $this->actingAs($this->admin)->post(route('backend.radiology-centers.store'), $data);

        $response->assertSessionHasErrors('name_ar');
    }

    public function test_validation_rejects_missing_name_en()
    {
        $data = [
            'name_ar' => 'مركز تجريبي',
            'status' => 1,
        ];

        $response = $this->actingAs($this->admin)->post(route('backend.radiology-centers.store'), $data);

        $response->assertSessionHasErrors('name_en');
    }

    public function test_admin_can_edit_existing_center()
    {
        $center = RadiologyCenter::create([
            'name_ar' => 'مركز قديم',
            'name_en' => 'Old Center',
            'scan_type' => 'CT',
            'status' => 1,
        ]);

        $data = [
            'id' => $center->id,
            'name_ar' => 'مركز جديد',
            'name_en' => 'New Center',
            'scan_type' => 'MRI',
            'status' => 1,
        ];

        $response = $this->actingAs($this->admin)->post(route('backend.radiology-centers.store'), $data);

        $response->assertRedirect(route('backend.radiology-centers.index'));

        $this->assertDatabaseHas('radiology_centers', [
            'id' => $center->id,
            'name_en' => 'New Center',
            'scan_type' => 'MRI',
        ]);
    }

    public function test_admin_can_soft_delete_center()
    {
        $center = RadiologyCenter::create([
            'name_ar' => 'مركز للحذف',
            'name_en' => 'Center To Delete',
            'status' => 1,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('backend.radiology-centers.destroy', $center->id));

        $response->assertJson(['status' => true]);
        $this->assertSoftDeleted('radiology_centers', ['id' => $center->id]);
    }

    public function test_admin_can_toggle_center_status()
    {
        $center = RadiologyCenter::create([
            'name_ar' => 'مركز',
            'name_en' => 'Center',
            'status' => 1,
        ]);

        $response = $this->actingAs($this->admin)->post(
            route('backend.radiology-centers.update_status', $center->id),
            ['status' => 0]
        );

        $response->assertJson(['status' => true]);
        $this->assertDatabaseHas('radiology_centers', ['id' => $center->id, 'status' => 0]);
    }

    public function test_scan_type_is_stored_correctly()
    {
        $data = [
            'name_ar' => 'مركز أشعة',
            'name_en' => 'X-Ray Center',
            'scan_type' => 'X-Ray',
            'status' => 1,
        ];

        $this->actingAs($this->admin)->post(route('backend.radiology-centers.store'), $data);

        $this->assertDatabaseHas('radiology_centers', [
            'name_en' => 'X-Ray Center',
            'scan_type' => 'X-Ray',
        ]);
    }

    public function test_scan_type_is_nullable()
    {
        $data = [
            'name_ar' => 'مركز بدون نوع',
            'name_en' => 'Center Without Type',
            'status' => 1,
        ];

        $response = $this->actingAs($this->admin)->post(route('backend.radiology-centers.store'), $data);

        $response->assertRedirect(route('backend.radiology-centers.index'));

        $this->assertDatabaseHas('radiology_centers', [
            'name_en' => 'Center Without Type',
            'scan_type' => null,
        ]);
    }

    public function test_center_name_accessor_returns_correct_locale()
    {
        $center = RadiologyCenter::create([
            'name_ar' => 'مركز عربي',
            'name_en' => 'English Center',
            'status' => 1,
        ]);

        app()->setLocale('ar');
        $this->assertEquals('مركز عربي', $center->name);

        app()->setLocale('en');
        $this->assertEquals('English Center', $center->name);
    }

    public function test_admin_can_create_center_with_location()
    {
        $governorate = Governorate::create([
            'name_ar' => 'الإسكندرية',
            'name_en' => 'Alexandria',
        ]);

        $city = GovernorateCity::create([
            'governorate_id' => $governorate->id,
            'name_ar' => 'سيدي جابر',
            'name_en' => 'Sidi Gaber',
        ]);

        $data = [
            'name_ar' => 'مركز الإسكندرية',
            'name_en' => 'Alexandria Center',
            'scan_type' => 'CT',
            'governorate_id' => $governorate->id,
            'city_id' => $city->id,
            'status' => 1,
        ];

        $response = $this->actingAs($this->admin)->post(route('backend.radiology-centers.store'), $data);

        $response->assertRedirect(route('backend.radiology-centers.index'));

        $this->assertDatabaseHas('radiology_centers', [
            'name_en' => 'Alexandria Center',
            'governorate_id' => $governorate->id,
            'city_id' => $city->id,
        ]);
    }

    public function test_non_admin_cannot_access_radiology_centers_index()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('backend.radiology-centers.index'));

        $response->assertStatus(403);
    }
}
