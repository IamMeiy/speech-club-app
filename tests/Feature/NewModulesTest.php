<?php

namespace Tests\Feature;

use App\Livewire\Meetings\MeetingShow;
use App\Livewire\Progress\ProgressIndex;
use App\Models\Club;
use App\Models\Meeting;
use App\Models\MeetingEvaluation;
use App\Models\MeetingRole;
use App\Models\MeetingRoleType;
use App\Models\MeetingSpeaker;
use App\Models\MeetingTtmSpeaker;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NewModulesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $this->seed(\Database\Seeders\ClubSeeder::class);
        $this->seed(\Database\Seeders\MeetingRoleTypeSeeder::class);
    }

    public function test_user_can_download_meeting_agenda_pdf(): void
    {
        $club = Club::first();
        $user = User::factory()->create(['email' => 'member@example.com']);
        $user->assignRole('Member');
        $user->clubs()->attach($club->id);

        $meeting = Meeting::create([
            'club_id' => $club->id,
            'meeting_number' => 201,
            'meeting_date' => now()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:30',
            'theme' => 'Unlocking Potential',
            'word_of_the_day' => 'Resilience',
            'word_part_of_speech' => 'Noun',
            'word_definition' => 'The capacity to recover quickly from difficulties.',
            'word_example_sentence' => 'The team showed remarkable resilience.',
            'venue' => 'Grand Hall',
            'status' => 'scheduled',
        ]);

        $this->actingAs($user);

        Livewire::test(MeetingShow::class, ['meeting' => $meeting])
            ->assertStatus(200)
            ->call('downloadAgenda', 'indigo')
            ->assertFileDownloaded("Meeting-201-Agenda-" . now()->toDateString() . ".pdf");
    }

    public function test_user_can_self_service_signup_and_relinquish_meeting_role(): void
    {
        $club = Club::first();
        $user = User::factory()->create();
        $user->assignRole('Member');
        $user->clubs()->attach($club->id);

        $roleType = MeetingRoleType::first();

        $meeting = Meeting::create([
            'club_id' => $club->id,
            'meeting_number' => 202,
            'meeting_date' => now()->toDateString(),
            'status' => 'scheduled',
        ]);

        $this->actingAs($user);

        // Volunteer for role
        Livewire::test(MeetingShow::class, ['meeting' => $meeting])
            ->assertStatus(200)
            ->call('signUpForRole', $roleType->id);

        $this->assertDatabaseHas('meeting_roles', [
            'meeting_id' => $meeting->id,
            'meeting_role_type_id' => $roleType->id,
            'user_id' => $user->id,
        ]);

        $assigned = MeetingRole::where('meeting_id', $meeting->id)->first();

        // Relinquish role
        Livewire::test(MeetingShow::class, ['meeting' => $meeting])
            ->call('relinquishRole', $assigned->id);

        $this->assertDatabaseMissing('meeting_roles', [
            'id' => $assigned->id,
        ]);
    }

    public function test_live_counter_tools_increment_fillers_and_word_of_day(): void
    {
        $club = Club::first();
        $user = User::factory()->create();
        $user->assignRole('Member');
        $user->clubs()->attach($club->id);

        $meeting = Meeting::create([
            'club_id' => $club->id,
            'meeting_number' => 203,
            'meeting_date' => now()->toDateString(),
            'word_of_the_day' => 'Eloquence',
            'status' => 'scheduled',
        ]);

        $this->actingAs($user);

        // Ah-counter increment & decrement
        Livewire::test(MeetingShow::class, ['meeting' => $meeting])
            ->call('incrementFiller', $user->id, 'ah_count')
            ->call('incrementFiller', $user->id, 'ah_count')
            ->call('decrementFiller', $user->id, 'ah_count');

        $this->assertDatabaseHas('meeting_ah_counter_logs', [
            'meeting_id' => $meeting->id,
            'user_id' => $user->id,
            'ah_count' => 1,
        ]);

        // Grammarian word of the day increment & notes
        Livewire::test(MeetingShow::class, ['meeting' => $meeting])
            ->call('incrementWordOfDay', $user->id)
            ->set('selectedGrammarUserId', $user->id)
            ->set('grammarGoodPhrases', 'Fabulous opening metaphor')
            ->set('grammarAwkwardPhrases', 'Double negative in conclusion')
            ->call('saveGrammarNotes');

        $this->assertDatabaseHas('meeting_grammarian_logs', [
            'meeting_id' => $meeting->id,
            'user_id' => $user->id,
            'word_of_day_count' => 1,
            'good_phrases' => 'Fabulous opening metaphor',
            'awkward_phrases' => 'Double negative in conclusion',
        ]);
    }

    public function test_user_speech_progress_and_milestone_badges(): void
    {
        $club = Club::first();
        $user = User::factory()->create();
        $user->assignRole('Member');
        $user->clubs()->attach($club->id);

        $meeting = Meeting::create([
            'club_id' => $club->id,
            'meeting_number' => 204,
            'meeting_date' => now()->toDateString(),
            'status' => 'completed',
        ]);

        // Add 1 speech
        MeetingSpeaker::create([
            'meeting_id' => $meeting->id,
            'user_id' => $user->id,
            'slot' => 1,
            'topic' => 'Icebreaker: My Journey',
        ]);

        $this->assertEquals(1, $user->speechesCount());

        $badges = $user->getMilestoneBadges();
        $icebreaker = collect($badges)->firstWhere('id', 'icebreaker');

        $this->assertNotNull($icebreaker);
        $this->assertTrue($icebreaker['unlocked']);
        $this->assertEquals(1, $icebreaker['progress']);
    }

    public function test_projects_catalog_can_be_viewed_by_authenticated_members(): void
    {
        $this->seed(\Database\Seeders\ProjectSeeder::class);

        $club = Club::first();
        $user = User::factory()->create();
        $user->assignRole('Member');
        $user->clubs()->attach($club->id);

        $this->actingAs($user);

        Livewire::test(\App\Livewire\Projects\ProjectIndex::class)
            ->assertStatus(200)
            ->assertSee('Ice Breaker')
            ->assertSee('Writing a Speech with Purpose')
            ->set('search', 'Storytelling')
            ->assertSee('Connect with Storytelling')
            ->call('openProject', \App\Models\Project::where('name', 'Ice Breaker')->first()->id)
            ->assertSee('Speech Purpose and Objectives');
    }

    public function test_meeting_speaker_can_select_project_and_autofills_duration(): void
    {
        $this->seed(\Database\Seeders\ProjectSeeder::class);

        $club = Club::first();
        $user = User::factory()->create();
        $user->assignRole('Member');
        $user->clubs()->attach($club->id);

        $project = \App\Models\Project::where('name', 'Ice Breaker')->first();

        $meeting = Meeting::create([
            'club_id' => $club->id,
            'meeting_number' => 205,
            'meeting_date' => now()->toDateString(),
            'status' => 'scheduled',
        ]);

        $this->actingAs($user);

        // Test volunteer speaker modal with project selection
        Livewire::test(MeetingShow::class, ['meeting' => $meeting])
            ->set('speakerProjectId', $project->id)
            ->assertSet('speakerProject', 'Ice Breaker')
            ->assertSet('speakerDuration', '4-6 mins')
            ->set('speakerTopic', 'Hello World: My First Step')
            ->call('signUpAsSpeaker');

        $this->assertDatabaseHas('meeting_speakers', [
            'meeting_id' => $meeting->id,
            'user_id' => $user->id,
            'project_id' => $project->id,
            'topic' => 'Hello World: My First Step',
            'duration' => '4-6 mins',
        ]);
    }

    public function test_live_counter_sync_ah_and_grammar_counts(): void
    {
        $club = Club::first();
        $user = User::factory()->create();
        $user->assignRole('Member');
        $user->clubs()->attach($club->id);

        $meeting = Meeting::create([
            'club_id' => $club->id,
            'meeting_number' => 206,
            'meeting_date' => now()->toDateString(),
            'status' => 'scheduled',
        ]);

        $this->actingAs($user);

        // Test Alpine debounced sync method
        Livewire::test(MeetingShow::class, ['meeting' => $meeting])
            ->call('syncAhCounts', $user->id, [
                'ah_count' => 3,
                'um_count' => 2,
                'er_count' => 1,
            ])
            ->call('syncGrammarCount', $user->id, 4)
            ->call('saveAllCounts', [
                $user->id => [
                    'ah_count' => 5,
                    'um_count' => 3,
                    'er_count' => 2,
                    'like_count' => 4,
                ]
            ], [
                $user->id => [
                    'word_of_day_count' => 6,
                ]
            ])
            ->assertDispatched('counts-saved');

        $this->assertDatabaseHas('meeting_ah_counter_logs', [
            'meeting_id' => $meeting->id,
            'user_id' => $user->id,
            'ah_count' => 5,
            'um_count' => 3,
            'er_count' => 2,
            'like_count' => 4,
        ]);

        $this->assertDatabaseHas('meeting_grammarian_logs', [
            'meeting_id' => $meeting->id,
            'user_id' => $user->id,
            'word_of_day_count' => 6,
        ]);
    }

    public function test_user_can_view_my_progress_and_feedback(): void
    {
        $club = Club::first();
        $speakerUser = User::factory()->create(['name' => 'Speech Prodigy']);
        $speakerUser->assignRole('Member');
        $speakerUser->clubs()->attach($club->id);

        $evaluatorUser = User::factory()->create(['name' => 'Wise Evaluator']);
        $evaluatorUser->assignRole('Member');
        $evaluatorUser->clubs()->attach($club->id);

        $meeting = Meeting::create([
            'club_id' => $club->id,
            'meeting_number' => 207,
            'meeting_date' => now()->toDateString(),
            'status' => 'completed',
        ]);

        $speaker = MeetingSpeaker::create([
            'meeting_id' => $meeting->id,
            'user_id' => $speakerUser->id,
            'slot' => 1,
            'topic' => 'The Power of Vulnerability',
            'project' => 'Ice Breaker',
            'speech_type' => 'Pathways',
            'duration' => '4-6 mins',
        ]);

        $evaluation = MeetingEvaluation::create([
            'meeting_id' => $meeting->id,
            'speaker_id' => $speaker->id,
            'evaluator_user_id' => $evaluatorUser->id,
            'notes' => 'Tremendous vocal variety and natural body language. Work on structuring your conclusion with a clear call-to-action.',
        ]);

        $this->actingAs($speakerUser);

        // Can access the route and see speech, badges, and feedback
        $response = $this->get(route('progress.index'));
        $response->assertStatus(200);

        Livewire::test(ProgressIndex::class)
            ->assertStatus(200)
            ->assertSee('Speech Progress Portfolio')
            ->assertSee('The Power of Vulnerability')
            ->assertSee('Wise Evaluator')
            ->assertSee('Tremendous vocal variety');
    }

    public function test_evaluator_can_save_evaluation_feedback(): void
    {
        $club = Club::first();
        $speakerUser = User::factory()->create();
        $speakerUser->assignRole('Member');
        $speakerUser->clubs()->attach($club->id);

        $evaluatorUser = User::factory()->create();
        $evaluatorUser->assignRole('Member');
        $evaluatorUser->clubs()->attach($club->id);

        $meeting = Meeting::create([
            'club_id' => $club->id,
            'meeting_number' => 208,
            'meeting_date' => now()->toDateString(),
            'status' => 'in_progress',
        ]);

        $speaker = MeetingSpeaker::create([
            'meeting_id' => $meeting->id,
            'user_id' => $speakerUser->id,
            'slot' => 1,
            'topic' => 'Leading with Empathy',
        ]);

        $evaluation = MeetingEvaluation::create([
            'meeting_id' => $meeting->id,
            'speaker_id' => $speaker->id,
            'evaluator_user_id' => $evaluatorUser->id,
            'notes' => null,
        ]);

        // Evaluator saves notes
        $this->actingAs($evaluatorUser);

        Livewire::test(MeetingShow::class, ['meeting' => $meeting])
            ->assertStatus(200)
            ->call('saveEvaluationNotes', $evaluation->id, 'Exceptional storytelling and eye contact throughout!')
            ->assertDispatched('flash');

        $this->assertDatabaseHas('meeting_evaluations', [
            'id' => $evaluation->id,
            'notes' => 'Exceptional storytelling and eye contact throughout!',
        ]);

        // Unauthorized member cannot edit someone else's evaluation
        $unrelatedUser = User::factory()->create();
        $unrelatedUser->assignRole('Member');
        $unrelatedUser->clubs()->attach($club->id);

        $this->actingAs($unrelatedUser);

        Livewire::test(MeetingShow::class, ['meeting' => $meeting])
            ->call('saveEvaluationNotes', $evaluation->id, 'Hacked notes')
            ->assertForbidden();
    }
}

