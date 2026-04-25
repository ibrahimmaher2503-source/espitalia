<?php

namespace Modules\RadiologyLab\Tests\Feature;

use App\Models\User;
use Modules\Location\Models\Governorate;
use Modules\Location\Models\GovernorateCity;
use Modules\RadiologyLab\Models\Lab;
use Modules\RadiologyLab\Tests\Unit\BaseTest;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class LabsCrudTest extends BaseTest
{

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->runCrudMigrations();
        $this->seedPermissions();

        $this->admin = $this->createAdminUser();
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
            'view_labs', 'add_labs', 'edit_labs', 'delete_labs',
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

    public function test_admin_can_view_labs_index()
    {
        $response = $this->actingAs($this->admin)->get(route('backend.labs.index'));

        $response->assertStatus(200);
    }

    public function test_admin_can_create_lab_with_bilingual_names()
    {
        $data = [
            'name_ar' => 'مختبر البرج',
            'name_en' => 'Al Borg Lab',
            'status' => 1,
        ];

        $response = $this->actingAs($this->admin)->post(route('backend.labs.store'), $data);

        $response->assertRedirect(route('backend.labs.index'));

        $this->assertDatabaseHas('labs', [
            'name_ar' => 'مختبر البرج',
            'name_en' => 'Al Borg Lab',
            'status' => 1,
        ]);
    }

    public function test_validation_rejects_missing_name_ar()
    {
        $data = [
            'name_en' => 'Test Lab',
            'status' => 1,
        ];

        $response = $this->actingAs($this->admin)->post(route('backend.labs.store'), $data);

        $response->assertSessionHasErrors('name_ar');
    }

    public function test_validation_rejects_missing_name_en()
    {
        $data = [
            'name_ar' => 'مختبر تجريبي',
            'status' => 1,
        ];

        $response = $this->actingAs($this->admin)->post(route('backend.labs.store'), $data);

        $response->assertSessionHasErrors('name_en');
    }

    public function test_admin_can_edit_existing_lab()
    {
        $lab = Lab::create([
            'name_ar' => 'مختبر قديم',
            'name_en' => 'Old Lab',
            'status' => 1,
        ]);

        $data = [
            'id' => $lab->id,
            'name_ar' => 'مختبر جديد',
            'name_en' => 'New Lab',
            'status' => 1,
        ];

        $response = $this->actingAs($this->admin)->post(route('backend.labs.store'), $data);

        $response->assertRedirect(route('backend.labs.index'));

        $this->assertDatabaseHas('labs', [
            'id' => $lab->id,
            'name_ar' => 'مختبر جديد',
            'name_en' => 'New Lab',
        ]);
    }

    public function test_admin_can_soft_delete_lab()
    {
        $lab = Lab::create([
            'name_ar' => 'مختبر للحذف',
            'name_en' => 'Lab To Delete',
            'status' => 1,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('backend.labs.destroy', $lab->id));

        $response->assertJson(['status' => true]);
        $this->assertSoftDeleted('labs', ['id' => $lab->id]);
    }

    public function test_admin_can_toggle_lab_status()
    {
        $lab = Lab::create([
            'name_ar' => 'مختبر',
            'name_en' => 'Lab',
            'status' => 1,
        ]);

        $response = $this->actingAs($this->admin)->post(
            route('backend.labs.update_status', $lab->id),
            ['status' => 0]
        );

        $response->assertJson(['status' => true]);
        $this->assertDatabaseHas('labs', ['id' => $lab->id, 'status' => 0]);
    }

    public function test_lab_name_accessor_returns_correct_locale()
    {
        $lab = Lab::create([
            'name_ar' => 'مختبر عربي',
            'name_en' => 'English Lab',
            'status' => 1,
        ]);

        app()->setLocale('ar');
        $this->assertEquals('مختبر عربي', $lab->name);

        app()->setLocale('en');
        $this->assertEquals('English Lab', $lab->name);
    }

    public function test_admin_can_create_lab_with_location()
    {
        $governorate = Governorate::create([
            'name_ar' => 'القاهرة',
            'name_en' => 'Cairo',
        ]);

        $city = GovernorateCity::create([
            'governorate_id' => $governorate->id,
            'name_ar' => 'مدينة نصر',
            'name_en' => 'Nasr City',
        ]);

        $data = [
            'name_ar' => 'مختبر القاهرة',
            'name_en' => 'Cairo Lab',
            'governorate_id' => $governorate->id,
            'city_id' => $city->id,
            'status' => 1,
        ];

        $response = $this->actingAs($this->admin)->post(route('backend.labs.store'), $data);

        $response->assertRedirect(route('backend.labs.index'));

        $this->assertDatabaseHas('labs', [
            'name_en' => 'Cairo Lab',
            'governorate_id' => $governorate->id,
            'city_id' => $city->id,
        ]);
    }

    public function test_non_admin_cannot_access_labs_index()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('backend.labs.index'));

        $response->assertStatus(403);
    }
}
