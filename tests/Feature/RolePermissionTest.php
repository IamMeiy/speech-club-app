<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\ClubSeeder;
use Database\Seeders\MeetingRoleTypeSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(ClubSeeder::class);
        $this->seed(MeetingRoleTypeSeeder::class);

        $this->superAdmin = User::factory()->create(['name' => 'Root Admin', 'status' => 'active']);
        $this->superAdmin->assignRole('Super Admin');
    }

    public function test_super_admin_can_create_role_with_permissions(): void
    {
        $this->actingAs($this->superAdmin);

        Livewire::test(\App\Livewire\Roles\RoleCreate::class)
            ->set('name', 'Event Coordinator')
            ->set('selectedPermissions', ['meetings.view', 'meetings.create'])
            ->call('save')
            ->assertHasNoErrors();

        $role = Role::where('name', 'Event Coordinator')->first();
        $this->assertNotNull($role);
        $this->assertTrue($role->hasPermissionTo('meetings.view'));
        $this->assertTrue($role->hasPermissionTo('meetings.create'));
    }

    public function test_system_critical_roles_cannot_be_deleted(): void
    {
        $this->actingAs($this->superAdmin);
        $superAdminRole = Role::where('name', 'Super Admin')->first();

        Livewire::test(\App\Livewire\Roles\RoleIndex::class)
            ->call('deleteRole', $superAdminRole->id)
            ->assertDispatched('flash');

        $this->assertDatabaseHas('roles', ['name' => 'Super Admin']);
    }
}
