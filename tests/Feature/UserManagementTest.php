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

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Club $chola;
    protected Club $chera;
    protected User $cholaPresident;
    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(ClubSeeder::class);
        $this->seed(MeetingRoleTypeSeeder::class);

        $this->chola = Club::where('code', 'chola')->first();
        $this->chera = Club::where('code', 'chera')->first();

        // Chola President
        $this->cholaPresident = User::factory()->create(['name' => 'Arun', 'status' => 'active']);
        $this->cholaPresident->clubs()->attach($this->chola->id);
        $this->cholaPresident->assignRole('President');

        // Super Admin
        $this->superAdmin = User::factory()->create(['name' => 'Root Admin', 'status' => 'active']);
        $this->superAdmin->assignRole('Super Admin');
    }

    public function test_club_user_creates_member_automatically_assigned_to_their_club(): void
    {
        $this->actingAs($this->cholaPresident);

        Livewire::test(\App\Livewire\Users\UserCreate::class)
            ->set('name', 'Deepak')
            ->set('email', 'deepak@speechclub.local')
            ->set('phone', '+91 99999 88888')
            ->set('role', 'Member')
            ->set('password', 'password123')
            ->set('status', 'active')
            ->call('save')
            ->assertHasNoErrors();

        $newUser = User::where('email', 'deepak@speechclub.local')->first();
        $this->assertNotNull($newUser);
        $this->assertTrue($newUser->belongsToClub($this->chola->id));
        $this->assertFalse($newUser->belongsToClub($this->chera->id));
        $this->assertTrue($newUser->hasRole('Member'));
    }

    public function test_global_user_creates_global_user_with_multiple_clubs(): void
    {
        $this->actingAs($this->superAdmin);

        Livewire::test(\App\Livewire\GlobalUsers\GlobalUserCreate::class)
            ->set('name', 'Global Manager')
            ->set('email', 'globalmgr@speechclub.local')
            ->set('phone', '+91 99999 77777')
            ->set('role', 'Admin')
            ->set('password', 'password123')
            ->set('status', 'active')
            ->set('selectedClubs', [$this->chola->id, $this->chera->id])
            ->call('save')
            ->assertHasNoErrors();

        $newUser = User::where('email', 'globalmgr@speechclub.local')->first();
        $this->assertNotNull($newUser);
        $this->assertTrue($newUser->belongsToClub($this->chola->id));
        $this->assertTrue($newUser->belongsToClub($this->chera->id));
        $this->assertTrue($newUser->hasRole('Admin'));
    }

    public function test_user_can_update_profile_and_email_remains_unchanged(): void
    {
        $this->actingAs($this->cholaPresident);

        Livewire::test(\App\Livewire\Profile\ProfileEdit::class)
            ->set('name', 'Arun Updated')
            ->set('phone', '+91 88888 77777')
            ->call('saveProfile')
            ->assertHasNoErrors();

        $fresh = $this->cholaPresident->fresh();
        $this->assertEquals('Arun Updated', $fresh->name);
        $this->assertEquals('+91 88888 77777', $fresh->phone);
        $this->assertEquals($this->cholaPresident->email, $fresh->email);
    }

    public function test_user_can_fetch_roles_via_renderless_action_without_rerendering(): void
    {
        Livewire::withoutLazyLoading();
        $this->actingAs($this->cholaPresident);

        $member = User::factory()->create(['name' => 'Roles Test Member', 'status' => 'active']);
        $member->clubs()->attach($this->chola->id);
        $member->assignRole('Member');

        $meeting = \App\Models\Meeting::create([
            'club_id'        => $this->chola->id,
            'meeting_number' => 101,
            'meeting_date'   => now()->subDays(2)->format('Y-m-d'),
            'start_time'     => '18:00',
            'end_time'       => '20:00',
            'status'         => 'completed',
            'created_by'     => $this->cholaPresident->id,
        ]);

        $timerRole = \App\Models\MeetingRoleType::where('name', 'Timer')->first();
        \App\Models\MeetingRole::create([
            'meeting_id'           => $meeting->id,
            'user_id'              => $member->id,
            'meeting_role_type_id' => $timerRole->id,
        ]);

        $test = Livewire::test(\App\Livewire\Users\UserIndex::class)
            ->assertSee('Roles Test Member');

        $test->call('getMemberRoles', $member->id)
            ->assertReturned(function ($data) {
                return is_array($data)
                    && $data['user']['name'] === 'Roles Test Member'
                    && $data['total_taken_count'] === 1
                    && count($data['taken_roles']) === 1
                    && $data['taken_roles'][0]['name'] === 'Timer'
                    && $data['taken_roles'][0]['count'] === 1
                    && $data['taken_roles'][0]['last_meeting'] === '#101'
                    && count($data['not_taken_roles']) > 0
                    && count($data['recently_taken']) === 1
                    && $data['recently_taken'][0]['role_name'] === 'Timer'
                    && $data['recently_taken'][0]['meeting_number'] === 101;
            });
    }

    public function test_user_cannot_fetch_roles_for_member_from_another_club(): void
    {
        Livewire::withoutLazyLoading();
        $this->actingAs($this->cholaPresident);

        $cheraMember = User::factory()->create(['name' => 'Chera Member', 'status' => 'active']);
        $cheraMember->clubs()->attach($this->chera->id);
        $cheraMember->assignRole('Member');

        Livewire::test(\App\Livewire\Users\UserIndex::class)
            ->call('getMemberRoles', $cheraMember->id)
            ->assertForbidden();
    }
}
