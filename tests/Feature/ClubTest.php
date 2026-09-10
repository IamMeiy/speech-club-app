<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\User;
use Database\Seeders\ClubSeeder;
use Database\Seeders\MeetingRoleTypeSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ClubTest extends TestCase
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

    public function test_super_admin_can_create_club(): void
    {
        $this->actingAs($this->superAdmin);

        Livewire::test(\App\Livewire\Clubs\ClubCreate::class)
            ->set('name', 'Mughal Speech Club')
            ->set('code', 'mughal')
            ->set('description', 'A vibrant club in the north.')
            ->set('location', 'Main Campus Auditorium')
            ->set('status', 'active')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('clubs', [
            'name' => 'Mughal Speech Club',
            'code' => 'mughal',
        ]);
    }

    public function test_super_admin_can_edit_club(): void
    {
        $this->actingAs($this->superAdmin);
        $club = Club::where('code', 'chola')->first();

        Livewire::test(\App\Livewire\Clubs\ClubEdit::class, ['club' => $club])
            ->set('name', 'Chola Imperial Speech Club')
            ->set('description', 'Updated description.')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('clubs', [
            'id'   => $club->id,
            'name' => 'Chola Imperial Speech Club',
        ]);
    }
}
