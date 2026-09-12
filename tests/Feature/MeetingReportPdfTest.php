<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MeetingReportPdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_download_meeting_report_pdf(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $this->seed(\Database\Seeders\ClubSeeder::class);
        $this->seed(\Database\Seeders\MeetingRoleTypeSeeder::class);

        $club = Club::first();
        $user = User::factory()->create(['email' => 'member@example.com']);
        $user->assignRole('Member');
        $user->clubs()->attach($club->id);

        $meeting = Meeting::create([
            'club_id' => $club->id,
            'meeting_number' => 101,
            'meeting_date' => now()->toDateString(),
            'theme' => 'Leadership Through Action',
            'venue' => 'Main Auditorium',
            'status' => 'completed',
        ]);

        $this->actingAs($user);

        Livewire::test(\App\Livewire\Meetings\MeetingReport::class, ['meeting' => $meeting])
            ->assertStatus(200)
            ->call('downloadPdf')
            ->assertFileDownloaded("Meeting-101-Report-" . now()->toDateString() . ".pdf");
    }

    public function test_report_page_and_pdf_display_timing_data(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $this->seed(\Database\Seeders\ClubSeeder::class);
        $this->seed(\Database\Seeders\MeetingRoleTypeSeeder::class);

        $club = Club::first();
        $user = User::factory()->create(['email' => 'speaker@example.com']);
        $user->assignRole('Member');
        $user->clubs()->attach($club->id);

        $project = \App\Models\Project::create([
            'name' => 'Ice Breaker',
            'slug' => 'ice-breaker-test-timing',
            'track' => 'Pathways Core',
            'level' => 1,
            'min_minutes' => 4,
            'max_minutes' => 6,
            'default_duration' => '4-6 mins',
        ]);

        $meeting = Meeting::create([
            'club_id' => $club->id,
            'meeting_number' => 102,
            'meeting_date' => now()->toDateString(),
            'start_time' => '07:00 PM',
            'end_time' => '09:00 PM',
            'theme' => 'Timing Mastery',
            'status' => 'completed',
        ]);

        $speaker = \App\Models\MeetingSpeaker::create([
            'meeting_id' => $meeting->id,
            'user_id' => $user->id,
            'project_id' => $project->id,
            'slot' => 1,
            'speech_type' => 'Prepared',
            'topic' => 'My Journey',
            'duration' => '4-6 mins',
        ]);

        $ttm = \App\Models\MeetingTtmSpeaker::create([
            'meeting_id' => $meeting->id,
            'user_id' => $user->id,
            'slot' => 1,
            'topic' => 'Favorite Season',
            'duration' => '1-2 mins',
        ]);

        $this->actingAs($user);

        // Test Livewire Report page renders timing data
        Livewire::test(\App\Livewire\Meetings\MeetingReport::class, ['meeting' => $meeting])
            ->assertStatus(200)
            ->assertSee('07:00 PM - 09:00 PM')
            ->assertSee('4-6 mins')
            ->assertSee('1-2 mins');

        // Test PDF generation view contains timing
        $meeting->load(['speakers.projectModel', 'ttmSpeakers']);
        $this->assertEquals('4-6 mins', $speaker->formattedTiming());
        $this->assertEquals('1-2 mins', $ttm->formattedTiming());
        $this->assertEquals('07:00 PM - 09:00 PM', $meeting->formattedTime());
    }

    public function test_report_page_and_pdf_render_official_timer_report(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $this->seed(\Database\Seeders\ClubSeeder::class);
        $this->seed(\Database\Seeders\MeetingRoleTypeSeeder::class);

        $club = Club::first();
        $speakerUser = User::factory()->create(['name' => 'John Speaker']);
        $speakerUser->assignRole('Member');
        $speakerUser->clubs()->attach($club->id);

        $evaluatorUser = User::factory()->create(['name' => 'Sarah Evaluator']);
        $evaluatorUser->assignRole('Member');
        $evaluatorUser->clubs()->attach($club->id);

        $ttmUser = User::factory()->create(['name' => 'Bob Topics']);
        $ttmUser->assignRole('Member');
        $ttmUser->clubs()->attach($club->id);

        $meeting = Meeting::create([
            'club_id' => $club->id,
            'meeting_number' => 103,
            'meeting_date' => now()->toDateString(),
            'status' => 'completed',
            'notes' => '<p>Scheduled weekly chapter session. Role confirmations in progress.</p>',
        ]);

        $speaker = \App\Models\MeetingSpeaker::create([
            'meeting_id' => $meeting->id,
            'user_id' => $speakerUser->id,
            'slot' => 1,
            'topic' => 'Overcoming Fear',
            'duration' => '5-7 mins',
        ]);

        $evaluation = \App\Models\MeetingEvaluation::create([
            'meeting_id' => $meeting->id,
            'speaker_id' => $speaker->id,
            'evaluator_user_id' => $evaluatorUser->id,
        ]);

        $ttm = \App\Models\MeetingTtmSpeaker::create([
            'meeting_id' => $meeting->id,
            'user_id' => $ttmUser->id,
            'slot' => 1,
            'topic' => 'Morning Routine',
            'duration' => '1-2 mins',
        ]);

        \App\Models\MeetingTimerLog::create([
            'meeting_id' => $meeting->id,
            'speaker_type' => 'prepared_speaker',
            'reference_id' => $speaker->id,
            'user_id' => $speakerUser->id,
            'allotted_time' => '5-7 mins',
            'time_taken' => '06:30',
            'status' => 'within_time',
        ]);

        \App\Models\MeetingTimerLog::create([
            'meeting_id' => $meeting->id,
            'speaker_type' => 'evaluator',
            'reference_id' => $evaluation->id,
            'user_id' => $evaluatorUser->id,
            'allotted_time' => '2-3 mins',
            'time_taken' => '03:15',
            'status' => 'over_time',
        ]);

        \App\Models\MeetingTimerLog::create([
            'meeting_id' => $meeting->id,
            'speaker_type' => 'ttm_speaker',
            'reference_id' => $ttm->id,
            'user_id' => $ttmUser->id,
            'allotted_time' => '1-2 mins',
            'time_taken' => '01:50',
            'status' => 'within_time',
        ]);

        $this->actingAs($speakerUser);

        // Verify Livewire Report view renders official timer report
        Livewire::test(\App\Livewire\Meetings\MeetingReport::class, ['meeting' => $meeting])
            ->assertStatus(200)
            ->assertSee('Official Timer Report')
            ->assertSee('06:30')
            ->assertSee('03:15')
            ->assertSee('01:50')
            ->assertSee('Within Time')
            ->assertSee('Over Time')
            ->call('downloadPdf')
            ->assertFileDownloaded("Meeting-103-Report-" . now()->toDateString() . ".pdf");
    }
}
