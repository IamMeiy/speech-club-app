<?php

namespace Tests\Feature;

use App\Livewire\Meetings\MeetingEdit;
use App\Livewire\Meetings\MeetingShow;
use App\Models\Club;
use App\Models\Meeting;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\ClubSeeder;
use Database\Seeders\MeetingRoleTypeSeeder;
use Database\Seeders\ProjectSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MeetingMinutesAndStatusTest extends TestCase
{
    use RefreshDatabase;

    protected Club $club;
    protected User $president;
    protected User $secretary;
    protected User $member;
    protected Meeting $meeting;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(ClubSeeder::class);
        $this->seed(MeetingRoleTypeSeeder::class);
        $this->seed(ProjectSeeder::class);

        $this->club = Club::first();

        $this->president = User::factory()->create(['status' => 'active']);
        $this->president->clubs()->attach($this->club->id);
        $this->president->assignRole('President');

        $this->secretary = User::factory()->create(['status' => 'active']);
        $this->secretary->clubs()->attach($this->club->id);
        $this->secretary->assignRole('Secretary');

        $this->member = User::factory()->create(['status' => 'active']);
        $this->member->clubs()->attach($this->club->id);
        $this->member->assignRole('Member');

        $this->meeting = Meeting::create([
            'club_id' => $this->club->id,
            'meeting_number' => 101,
            'meeting_date' => now()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'status' => 'scheduled',
            'notes' => '<p>Planned Agenda Items</p>',
            'created_by' => $this->president->id,
        ]);
    }

    public function test_authorized_user_can_update_meeting_status(): void
    {
        Livewire::actingAs($this->president)
            ->test(MeetingShow::class, ['meeting' => $this->meeting])
            ->call('updateStatus', 'completed')
            ->assertDispatched('flash');

        $this->assertEquals('completed', $this->meeting->fresh()->status);
    }

    public function test_unauthorized_user_cannot_update_meeting_status(): void
    {
        Livewire::actingAs($this->member)
            ->test(MeetingShow::class, ['meeting' => $this->meeting])
            ->call('updateStatus', 'completed')
            ->assertForbidden();

        $this->assertEquals('scheduled', $this->meeting->fresh()->status);
    }

    public function test_authorized_user_can_save_minutes_of_meeting(): void
    {
        $minutesHtml = '<p>Meeting was called to order at 10:00 AM. Motion to approve reports passed unanimously.</p>';

        Livewire::actingAs($this->secretary)
            ->test(MeetingShow::class, ['meeting' => $this->meeting])
            ->call('saveMinutesOfMeeting', $minutesHtml)
            ->assertDispatched('flash');

        $this->assertEquals($minutesHtml, $this->meeting->fresh()->minutes_of_meeting);
    }

    public function test_unauthorized_user_cannot_save_minutes_of_meeting(): void
    {
        Livewire::actingAs($this->member)
            ->test(MeetingShow::class, ['meeting' => $this->meeting])
            ->call('saveMinutesOfMeeting', '<p>Unauthorized edits</p>')
            ->assertForbidden();

        $this->assertNull($this->meeting->fresh()->minutes_of_meeting);
    }

    public function test_speech_projects_are_properly_sorted(): void
    {
        $projects = Project::active()
            ->orderBy('sort_order')
            ->orderBy('level')
            ->orderBy('name')
            ->get();

        $this->assertNotEmpty($projects);

        // First project should be CC Project 1: Ice Breaker (sort_order = 10, level = 1)
        $first = $projects->first();
        $this->assertEquals(10, $first->sort_order);
        $this->assertEquals(1, $first->level);
        $this->assertEquals('Ice Breaker', $first->name);
        $this->assertEquals('Competent Communication', $first->track);

        // Subsequent CC projects should follow sequentially
        $second = $projects->get(1);
        $this->assertEquals(20, $second->sort_order);
        $this->assertEquals(2, $second->level);
        $this->assertEquals('Organize Your Speech', $second->name);
    }

    public function test_meeting_edit_saves_minutes_of_meeting_and_agenda(): void
    {
        Livewire::actingAs($this->president)
            ->test(MeetingEdit::class, ['meeting' => $this->meeting])
            ->set('notes', '<p>Updated Agenda</p>')
            ->set('minutes_of_meeting', '<p>Official Minutes Recorded</p>')
            ->call('save')
            ->assertRedirect(route('meetings.show', $this->meeting));

        $this->meeting->refresh();
        $this->assertEquals('<p>Updated Agenda</p>', $this->meeting->notes);
        $this->assertEquals('<p>Official Minutes Recorded</p>', $this->meeting->minutes_of_meeting);
    }

    public function test_meeting_report_view_renders_minutes_of_meeting_and_not_agenda(): void
    {
        $this->meeting->update([
            'notes' => '<p>Secret Pre-meeting Agenda Notes</p>',
            'minutes_of_meeting' => '<p>Official Minutes: Secretary presented club treasury report.</p>',
            'status' => 'completed',
        ]);

        Livewire::actingAs($this->member)
            ->test(\App\Livewire\Meetings\MeetingReport::class, ['meeting' => $this->meeting])
            ->assertStatus(200)
            ->assertSee('Minutes of Meeting (MoM)')
            ->assertSee('Secretary presented club treasury report.')
            ->assertDontSee('Secret Pre-meeting Agenda Notes');
    }
}
