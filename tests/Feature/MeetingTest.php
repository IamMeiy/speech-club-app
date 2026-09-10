<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Meeting;
use App\Models\MeetingRoleType;
use App\Models\User;
use Database\Seeders\ClubSeeder;
use Database\Seeders\MeetingRoleTypeSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MeetingTest extends TestCase
{
    use RefreshDatabase;

    protected Club $chola;
    protected Club $chera;
    protected User $cholaPresident;
    protected User $cholaMember1;
    protected User $cholaMember2;
    protected User $cheraMember;
    protected User $superAdmin;
    protected User $globalAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(ClubSeeder::class);
        $this->seed(MeetingRoleTypeSeeder::class);

        $this->chola = Club::where('code', 'chola')->first();
        $this->chera = Club::where('code', 'chera')->first();

        // Chola users
        $this->cholaPresident = User::factory()->create(['name' => 'Arun', 'status' => 'active']);
        $this->cholaPresident->clubs()->attach($this->chola->id);
        $this->cholaPresident->assignRole('President');

        $this->cholaMember1 = User::factory()->create(['name' => 'Kumar', 'status' => 'active']);
        $this->cholaMember1->clubs()->attach($this->chola->id);
        $this->cholaMember1->assignRole('Member');

        $this->cholaMember2 = User::factory()->create(['name' => 'Priya', 'status' => 'active']);
        $this->cholaMember2->clubs()->attach($this->chola->id);
        $this->cholaMember2->assignRole('Member');

        // Chera user
        $this->cheraMember = User::factory()->create(['name' => 'Ravi', 'status' => 'active']);
        $this->cheraMember->clubs()->attach($this->chera->id);
        $this->cheraMember->assignRole('Member');

        // Super Admin
        $this->superAdmin = User::factory()->create(['name' => 'Super Admin', 'status' => 'active']);
        $this->superAdmin->assignRole('Super Admin');

        // Global Admin (assigned to Chola & Chera)
        $this->globalAdmin = User::factory()->create(['name' => 'Global Admin', 'status' => 'active']);
        $this->globalAdmin->assignRole('Admin');
        $this->globalAdmin->clubs()->attach([$this->chola->id, $this->chera->id]);
    }

    public function test_club_user_creates_meeting_for_own_club_automatically(): void
    {
        $this->actingAs($this->cholaPresident);

        $tmodRole = MeetingRoleType::where('slug', 'tmod')->first();

        Livewire::test(\App\Livewire\Meetings\MeetingCreate::class)
            ->set('meeting_number', '1')
            ->set('meeting_date', '2026-10-15')
            ->set('theme', 'Leadership in Action')
            ->set('venue', 'Board Room A')
            ->set('roleAssignments.' . $tmodRole->id, $this->cholaMember1->id)
            ->set('speakers.0.user_id', $this->cholaMember2->id)
            ->set('speakers.0.topic', 'My First Speech')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('meetings', [
            'club_id'        => $this->chola->id,
            'meeting_number' => 1,
            'theme'          => 'Leadership in Action',
        ]);

        $meeting = Meeting::first();
        $this->assertCount(1, $meeting->roles);
        $this->assertCount(1, $meeting->speakers);
        $this->assertEquals($this->cholaMember1->id, $meeting->roles->first()->user_id);
        $this->assertEquals($this->cholaMember2->id, $meeting->speakers->first()->user_id);
    }

    public function test_super_admin_can_create_meeting_for_any_club(): void
    {
        $this->actingAs($this->superAdmin);

        Livewire::test(\App\Livewire\Meetings\MeetingCreate::class)
            ->set('selectedClubId', $this->chera->id)
            ->set('meeting_number', '1')
            ->set('meeting_date', '2026-10-20')
            ->set('theme', 'Global Excellence')
            ->set('speakers.0.user_id', $this->cheraMember->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('meetings', [
            'club_id'        => $this->chera->id,
            'meeting_number' => 1,
            'theme'          => 'Global Excellence',
        ]);
    }

    public function test_global_admin_can_create_meeting_for_assigned_club(): void
    {
        $this->actingAs($this->globalAdmin);

        Livewire::test(\App\Livewire\Meetings\MeetingCreate::class)
            ->set('selectedClubId', $this->chola->id)
            ->set('meeting_number', '1')
            ->set('meeting_date', '2026-10-22')
            ->set('theme', 'Admin Leadership')
            ->set('speakers.0.user_id', $this->cholaMember1->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('meetings', [
            'club_id'        => $this->chola->id,
            'meeting_number' => 1,
            'theme'          => 'Admin Leadership',
        ]);
    }

    public function test_global_admin_can_edit_meetings_across_assigned_clubs(): void
    {
        $cheraMeeting = Meeting::create([
            'club_id'        => $this->chera->id,
            'meeting_number' => 1,
            'meeting_date'   => now()->addDays(5),
            'status'         => 'scheduled',
            'created_by'     => $this->globalAdmin->id,
        ]);

        $this->actingAs($this->globalAdmin);

        Livewire::test(\App\Livewire\Meetings\MeetingEdit::class, ['meeting' => $cheraMeeting])
            ->set('theme', 'Updated Chera Theme')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('meetings', [
            'id'    => $cheraMeeting->id,
            'theme' => 'Updated Chera Theme',
        ]);
    }

    public function test_cannot_assign_cross_club_member_to_meeting(): void
    {
        $this->actingAs($this->cholaPresident);

        $tmodRole = MeetingRoleType::where('slug', 'tmod')->first();

        Livewire::test(\App\Livewire\Meetings\MeetingCreate::class)
            ->set('meeting_number', '1')
            ->set('meeting_date', '2026-10-15')
            ->set('roleAssignments.' . $tmodRole->id, $this->cheraMember->id) // Chera member in Chola meeting
            ->call('save')
            ->assertHasErrors(['meeting_date']);

        $this->assertDatabaseCount('meetings', 0);
    }

    public function test_attendance_can_be_saved_and_role_replaced(): void
    {
        $this->actingAs($this->cholaPresident);

        $meeting = Meeting::create([
            'club_id'        => $this->chola->id,
            'meeting_number' => 1,
            'meeting_date'   => now()->addDays(2),
            'status'         => 'scheduled',
            'created_by'     => $this->cholaPresident->id,
        ]);

        $tmodRoleType = MeetingRoleType::where('slug', 'tmod')->first();
        $meetingRole = $meeting->roles()->create([
            'meeting_role_type_id' => $tmodRoleType->id,
            'user_id'              => $this->cholaMember1->id,
        ]);

        // Save attendance
        Livewire::test(\App\Livewire\Meetings\MeetingAttendance::class, ['meeting' => $meeting])
            ->set('attendance.' . $this->cholaMember1->id, 'absent')
            ->set('attendance.' . $this->cholaMember2->id, 'present')
            ->call('saveAttendance')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('meeting_attendance', [
            'meeting_id' => $meeting->id,
            'user_id'    => $this->cholaMember1->id,
            'status'     => 'absent',
        ]);

        // Replace absent TMOD role holder with cholaMember2
        Livewire::test(\App\Livewire\Meetings\MeetingAttendance::class, ['meeting' => $meeting])
            ->set('roleReplacements.' . $meetingRole->id, $this->cholaMember2->id)
            ->call('replaceRole', $meetingRole->id)
            ->assertHasNoErrors();

        $this->assertEquals($this->cholaMember2->id, $meetingRole->fresh()->user_id);
    }

    public function test_changing_club_in_meeting_create_updates_members_list_and_resets_selections(): void
    {
        $this->actingAs($this->superAdmin);

        $component = Livewire::test(\App\Livewire\Meetings\MeetingCreate::class)
            ->set('selectedClubId', $this->chola->id);

        // Verify Chola members are loaded
        $membersChola = $component->get('membersList');
        $this->assertContains((string) $this->cholaMember1->id, array_column($membersChola, 'id'));
        $this->assertNotContains((string) $this->cheraMember->id, array_column($membersChola, 'id'));

        // Assign a Chola member as speaker
        $component->set('speakers.0.user_id', (string) $this->cholaMember1->id);

        // Now switch club to Chera
        $component->set('selectedClubId', $this->chera->id);

        // Verify Chera members are loaded and Chola members are gone
        $membersChera = $component->get('membersList');
        $this->assertContains((string) $this->cheraMember->id, array_column($membersChera, 'id'));
        $this->assertNotContains((string) $this->cholaMember1->id, array_column($membersChera, 'id'));

        // Verify speaker user_id was reset
        $speakers = $component->get('speakers');
        $this->assertEquals('', $speakers[0]['user_id']);
    }
}
