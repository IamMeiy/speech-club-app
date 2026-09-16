<?php

namespace Tests\Feature;

use App\Livewire\Users\UserShow;
use App\Models\Club;
use App\Models\Meeting;
use App\Models\MeetingAttendance;
use App\Models\MeetingRole;
use App\Models\MeetingRoleType;
use App\Models\MeetingSpeaker;
use App\Models\MeetingTtmSpeaker;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\ClubSeeder;
use Database\Seeders\MeetingRoleTypeSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserShowTest extends TestCase
{
    use RefreshDatabase;

    protected Club $chola;
    protected Club $chera;
    protected User $cholaPresident;
    protected User $cheraPresident;
    protected User $cholaMember;
    protected User $cheraMember;
    protected User $superAdmin;
    protected User $regularMember;

    protected function setUp(): void
    {
        parent::setUp();
        Livewire::withoutLazyLoading();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(ClubSeeder::class);
        $this->seed(MeetingRoleTypeSeeder::class);

        $this->chola = Club::where('code', 'chola')->first();
        $this->chera = Club::where('code', 'chera')->first();

        // Chola President (has users.view permission for Chola)
        $this->cholaPresident = User::factory()->create(['name' => 'Chola Pres', 'status' => 'active']);
        $this->cholaPresident->clubs()->attach($this->chola->id);
        $this->cholaPresident->assignRole('President');

        // Chera President
        $this->cheraPresident = User::factory()->create(['name' => 'Chera Pres', 'status' => 'active']);
        $this->cheraPresident->clubs()->attach($this->chera->id);
        $this->cheraPresident->assignRole('President');

        // Chola Member
        $this->cholaMember = User::factory()->create(['name' => 'Kavitha Raman', 'phone' => '+91 98765 43210', 'status' => 'active']);
        $this->cholaMember->clubs()->attach($this->chola->id);
        $this->cholaMember->assignRole('Member');

        // Chera Member
        $this->cheraMember = User::factory()->create(['name' => 'Manoj Chera', 'status' => 'active']);
        $this->cheraMember->clubs()->attach($this->chera->id);
        $this->cheraMember->assignRole('Member');

        // Super Admin
        $this->superAdmin = User::factory()->create(['name' => 'Root Admin', 'status' => 'active']);
        $this->superAdmin->assignRole('Super Admin');

        // Regular Member without users.view permission
        $this->regularMember = User::factory()->create(['name' => 'Regular Person', 'status' => 'active']);
        $this->regularMember->clubs()->attach($this->chola->id);
        $this->regularMember->assignRole('Member');
    }

    public function test_club_officer_can_view_member_of_same_club(): void
    {
        $this->actingAs($this->cholaPresident);

        $response = $this->get(route('members.show', $this->cholaMember));
        $response->assertOk();
        $response->assertSee('Kavitha Raman');
        $response->assertSee('+91 98765 43210');
        $response->assertSee('Speeches Delivered');
        $response->assertSee('Evaluations Given');
        $response->assertSee('Milestone Badges');
    }

    public function test_club_officer_cannot_view_member_of_different_club(): void
    {
        $this->actingAs($this->cholaPresident);

        $response = $this->get(route('members.show', $this->cheraMember));
        $response->assertForbidden();
    }

    public function test_user_without_users_view_permission_is_forbidden(): void
    {
        $this->actingAs($this->regularMember);

        $response = $this->get(route('members.show', $this->cholaMember));
        $response->assertForbidden();
    }

    public function test_super_admin_can_view_any_club_member(): void
    {
        $this->actingAs($this->superAdmin);

        $response = $this->get(route('members.show', $this->cheraMember));
        $response->assertOk();
        $response->assertSee('Manoj Chera');
    }

    public function test_member_view_renders_speeches_roles_and_attendance(): void
    {
        $meeting = Meeting::create([
            'club_id' => $this->chola->id,
            'meeting_number' => 101,
            'meeting_date' => now()->subDays(5)->format('Y-m-d'),
            'start_time' => '18:00',
            'end_time' => '20:00',
            'status' => 'completed',
            'created_by' => $this->cholaPresident->id,
        ]);

        $project = Project::create([
            'name' => 'The Icebreaker Project',
            'slug' => 'the-icebreaker-project',
            'track' => 'Presentation Mastery',
            'level' => 1,
            'min_minutes' => 4,
            'max_minutes' => 6,
        ]);

        // Speeches
        MeetingSpeaker::create([
            'meeting_id' => $meeting->id,
            'user_id' => $this->cholaMember->id,
            'project_id' => $project->id,
            'slot' => 1,
            'topic' => 'My Journey to Tech',
            'speech_type' => 'Pathways',
            'duration' => '4-6 mins',
        ]);

        // Table Topics
        MeetingTtmSpeaker::create([
            'meeting_id' => $meeting->id,
            'user_id' => $this->cholaMember->id,
            'slot' => 1,
            'topic' => 'The Power of Perseverance',
            'duration' => '1-2 mins',
        ]);

        // Meeting Role
        $timerRole = MeetingRoleType::where('name', 'Timer')->first();
        MeetingRole::create([
            'meeting_id' => $meeting->id,
            'user_id' => $this->cholaMember->id,
            'meeting_role_type_id' => $timerRole->id,
        ]);

        // Attendance
        MeetingAttendance::create([
            'meeting_id' => $meeting->id,
            'user_id' => $this->cholaMember->id,
            'status' => 'present',
            'notes' => 'Arrived on time',
        ]);

        $this->actingAs($this->cholaPresident);

        Livewire::test(UserShow::class, ['user' => $this->cholaMember])
            ->assertSee('Kavitha Raman')
            ->assertSee('My Journey to Tech')
            ->assertSee('The Icebreaker Project')
            ->assertSee('The Power of Perseverance')
            ->assertSee('Timer')
            ->assertSee('Present');
    }

    public function test_members_index_page_contains_view_link(): void
    {
        $this->actingAs($this->cholaPresident);

        Livewire::test(\App\Livewire\Users\UserIndex::class)
            ->assertSee(route('members.show', $this->cholaMember));
    }

    public function test_super_admin_can_delete_member_from_show_page(): void
    {
        $this->actingAs($this->superAdmin);

        Livewire::test(UserShow::class, ['user' => $this->cholaMember])
            ->call('deleteUser')
            ->assertRedirect(route('members.index'));

        $this->assertSoftDeleted('users', ['id' => $this->cholaMember->id]);
    }

    public function test_large_meeting_attendance_is_paginated_and_loads_efficiently(): void
    {
        $this->actingAs($this->cholaPresident);

        // Create 60 meetings and attendance records
        $records = [];
        for ($i = 1; $i <= 60; $i++) {
            $meeting = Meeting::create([
                'club_id' => $this->chola->id,
                'meeting_number' => 200 + $i,
                'meeting_date' => now()->subDays(60 - $i)->format('Y-m-d'),
                'start_time' => '18:00',
                'end_time' => '20:00',
                'status' => 'completed',
                'created_by' => $this->cholaPresident->id,
            ]);

            MeetingAttendance::create([
                'meeting_id' => $meeting->id,
                'user_id' => $this->cholaMember->id,
                'status' => $i % 5 === 0 ? 'late' : 'present',
                'notes' => 'Session ' . $i,
            ]);
        }

        // Livewire component should calculate total stats accurately via SQL without hydrating 60 models,
        // and only paginate 15 records per page.
        $component = Livewire::test(UserShow::class, ['user' => $this->cholaMember]);

        $component->assertSee('60 sessions');
        $component->assertSee('100%'); // 60 attended out of 60
        $component->assertViewHas('attendanceRecords', function ($records) {
            return $records->total() === 60 && $records->count() === 15 && $records->hasPages();
        });
    }
}
