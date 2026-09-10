<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\User;
use Database\Seeders\ClubSeeder;
use Database\Seeders\MeetingRoleTypeSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(ClubSeeder::class);
        $this->seed(MeetingRoleTypeSeeder::class);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $club = Club::first();
        $user = User::factory()->create(['status' => 'active']);
        $user->clubs()->attach($club->id);
        $user->assignRole('Member');

        $response = $this->actingAs($user)->get('/');
        $response->assertStatus(200);
    }

    public function test_user_can_login_via_livewire(): void
    {
        $user = User::factory()->create([
            'email'    => 'test@speechclub.local',
            'password' => bcrypt('password123'),
            'status'   => 'active',
        ]);
        $user->assignRole('Super Admin');

        \Livewire\Livewire::test(\App\Livewire\Auth\Login::class)
            ->set('email', 'test@speechclub.local')
            ->set('password', 'password123')
            ->call('login')
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_wrong_password(): void
    {
        $user = User::factory()->create([
            'email'    => 'test@speechclub.local',
            'password' => bcrypt('password123'),
        ]);

        \Livewire\Livewire::test(\App\Livewire\Auth\Login::class)
            ->set('email', 'test@speechclub.local')
            ->set('password', 'wrongpassword')
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }
}
