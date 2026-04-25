<?php

namespace Modules\Specialization\Tests\Feature;

use App\Models\User;
use Modules\Specialization\Models\Specialization;
use Spatie\Permission\Models\Role;

class SpecializationCrudTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->runCoreMigrations();
        $this->runSpecializationMigrations();
        $this->seedRolesAndPermissions();
    }

    protected function seedRolesAndPermissions(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'web'],
            ['title' => 'Admin']
        );
        Role::firstOrCreate(
            ['name' => 'demo_admin', 'guard_name' => 'web'],
            ['title' => 'Demo Admin']
        );

        $this->artisan('module:seed', [
            'module' => 'Specialization',
            '--force' => true,
        ]);
    }

    protected function createAdminUser(): User
    {
        $user = User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'user_type' => 'admin',
        ]);
        $user->assignRole('admin');

        return $user;
    }

    public function test_index_redirects_unauthenticated_user(): void
    {
        $response = $this->get(route('backend.specializations.index'));

        $response->assertRedirect();
    }

    public function test_store_creates_specialization(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->post(route('backend.specializations.store'), [
            'name_ar' => 'طب عام',
            'name_en' => 'General Medicine',
            'description_ar' => 'وصف',
            'description_en' => 'Description',
            'status' => 1,
        ]);

        $response->assertRedirect(route('backend.specializations.index'));
        $this->assertDatabaseHas('specializations', ['name_en' => 'General Medicine']);
    }

    public function test_update_modifies_specialization(): void
    {
        $admin = $this->createAdminUser();
        $spec = Specialization::create(['name_ar' => 'قديم', 'name_en' => 'Old Name', 'status' => 1]);

        $response = $this->actingAs($admin)->put(route('backend.specializations.update', $spec->id), [
            'name_ar' => 'جديد',
            'name_en' => 'New Name',
            'status' => 1,
        ]);

        $response->assertRedirect(route('backend.specializations.index'));
        $this->assertEquals('New Name', $spec->fresh()->name_en);
    }

    public function test_destroy_soft_deletes_specialization(): void
    {
        $admin = $this->createAdminUser();
        $spec = Specialization::create(['name_en' => 'To Delete', 'status' => 1]);

        $response = $this->actingAs($admin)->delete(route('backend.specializations.destroy', $spec->id));

        $response->assertJson(['status' => true]);
        $this->assertSoftDeleted('specializations', ['id' => $spec->id]);
    }

    public function test_destroy_blocked_when_active_children_exist(): void
    {
        $admin = $this->createAdminUser();
        $parent = Specialization::create(['name_en' => 'Parent', 'status' => 1]);
        Specialization::create(['name_en' => 'Child', 'parent_id' => $parent->id, 'status' => 1]);

        $response = $this->actingAs($admin)->delete(route('backend.specializations.destroy', $parent->id));

        $response->assertJson(['status' => false]);
        $this->assertDatabaseHas('specializations', ['id' => $parent->id, 'deleted_at' => null]);
    }

    public function test_update_status_toggles(): void
    {
        $admin = $this->createAdminUser();
        $spec = Specialization::create(['name_en' => 'Toggle', 'status' => 1]);

        $response = $this->actingAs($admin)->post(route('backend.specializations.update_status', $spec->id), [
            'status' => 0,
        ]);

        $response->assertJson(['status' => true]);
        $this->assertEquals(0, $spec->fresh()->status);
    }

    public function test_bulk_action_delete(): void
    {
        $admin = $this->createAdminUser();
        $spec1 = Specialization::create(['name_en' => 'Bulk1', 'status' => 1]);
        $spec2 = Specialization::create(['name_en' => 'Bulk2', 'status' => 1]);

        $response = $this->actingAs($admin)->post(route('backend.specializations.bulk_action'), [
            'rowIds' => $spec1->id.','.$spec2->id,
            'action_type' => 'delete',
        ]);

        $response->assertJson(['status' => true]);
        $this->assertSoftDeleted('specializations', ['id' => $spec1->id]);
        $this->assertSoftDeleted('specializations', ['id' => $spec2->id]);
    }
}
