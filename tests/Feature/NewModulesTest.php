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

        $ahRoleType = MeetingRoleType::where('slug', 'ah-counter')->first();
        MeetingRole::create([
            'meeting_id' => $meeting->id,
            'meeting_role_type_id' => $ahRoleType->id,
            'user_id' => $user->id,
        ]);

        $grammarianRoleType = MeetingRoleType::where('slug', 'grammarian')->first();
        MeetingRole::create([
            'meeting_id' => $meeting->id,
            'meeting_role_type_id' => $grammarianRoleType->id,
            'user_id' => $user->id,
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
            ->assertSee('Organize Your Speech')
            ->set('search', 'Storytelling')
            ->assertSee('The Folk Tale')
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

        $ahRoleType = MeetingRoleType::where('slug', 'ah-counter')->first();
        MeetingRole::create([
            'meeting_id' => $meeting->id,
            'meeting_role_type_id' => $ahRoleType->id,
            'user_id' => $user->id,
        ]);

        $grammarianRoleType = MeetingRoleType::where('slug', 'grammarian')->first();
        MeetingRole::create([
            'meeting_id' => $meeting->id,
            'meeting_role_type_id' => $grammarianRoleType->id,
            'user_id' => $user->id,
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

    public function test_ah_counter_and_grammarian_do_not_overwrite_each_other(): void
    {
        $club = Club::first();
        $user = User::factory()->create();
        $user->assignRole('Member');
        $user->clubs()->attach($club->id);

        $ahUser = User::factory()->create();
        $ahUser->assignRole('Member');
        $ahUser->clubs()->attach($club->id);

        $grammarianUser = User::factory()->create();
        $grammarianUser->assignRole('Member');
        $grammarianUser->clubs()->attach($club->id);

        $meeting = Meeting::create([
            'club_id' => $club->id,
            'meeting_number' => 205,
            'meeting_date' => now()->toDateString(),
            'status' => 'scheduled',
        ]);

        $ahRoleType = MeetingRoleType::where('slug', 'ah-counter')->first();
        MeetingRole::create([
            'meeting_id' => $meeting->id,
            'meeting_role_type_id' => $ahRoleType->id,
            'user_id' => $ahUser->id,
        ]);

        $grammarianRoleType = MeetingRoleType::where('slug', 'grammarian')->first();
        MeetingRole::create([
            'meeting_id' => $meeting->id,
            'meeting_role_type_id' => $grammarianRoleType->id,
            'user_id' => $grammarianUser->id,
        ]);

        // Step 1: Grammarian enters and submits data
        $this->actingAs($grammarianUser);
        Livewire::test(MeetingShow::class, ['meeting' => $meeting])
            ->call('saveAllCounts', null, [
                $user->id => [
                    'word_of_day_count' => 4,
                    'good_phrases'      => 'Outstanding rhetoric',
                    'awkward_phrases'   => null,
                    'notes'             => null,
                ]
            ], 'grammarian')
            ->assertDispatched('counts-saved');

        $this->assertDatabaseHas('meeting_grammarian_logs', [
            'meeting_id'        => $meeting->id,
            'user_id'           => $user->id,
            'word_of_day_count' => 4,
            'good_phrases'      => 'Outstanding rhetoric',
        ]);

        // Step 2: Ah-Counter later enters and submits their data
        $this->actingAs($ahUser);
        Livewire::test(MeetingShow::class, ['meeting' => $meeting])
            ->call('saveAllCounts', [
                $user->id => [
                    'ah_count' => 7,
                    'um_count' => 2,
                ]
            ], null, 'ah_counter')
            ->assertDispatched('counts-saved');

        // Verify Ah-Counter data is saved
        $this->assertDatabaseHas('meeting_ah_counter_logs', [
            'meeting_id' => $meeting->id,
            'user_id'    => $user->id,
            'ah_count'   => 7,
            'um_count'   => 2,
        ]);

        // Crucially: verify previously saved Grammarian data is STILL INTACT and NOT overwritten
        $this->assertDatabaseHas('meeting_grammarian_logs', [
            'meeting_id'        => $meeting->id,
            'user_id'           => $user->id,
            'word_of_day_count' => 4,
            'good_phrases'      => 'Outstanding rhetoric',
        ]);

        // Step 3: Grammarian updates their data again
        $this->actingAs($grammarianUser);
        Livewire::test(MeetingShow::class, ['meeting' => $meeting])
            ->call('saveGrammarianCounts', [
                $user->id => [
                    'word_of_day_count' => 5,
                    'good_phrases'      => 'Brilliant phrasing',
                ]
            ]);

        $this->assertDatabaseHas('meeting_grammarian_logs', [
            'meeting_id'        => $meeting->id,
            'user_id'           => $user->id,
            'word_of_day_count' => 5,
            'good_phrases'      => 'Brilliant phrasing',
        ]);

        // Verify Ah-Counter data is still preserved
        $this->assertDatabaseHas('meeting_ah_counter_logs', [
            'meeting_id' => $meeting->id,
            'user_id'    => $user->id,
            'ah_count'   => 7,
            'um_count'   => 2,
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

    public function test_timer_sheet_can_record_timings_for_speakers_evaluators_and_ttm(): void
    {
        $club = Club::first();
        $speakerUser = User::factory()->create();
        $speakerUser->assignRole('Member');
        $speakerUser->clubs()->attach($club->id);

        $evaluatorUser = User::factory()->create();
        $evaluatorUser->assignRole('Member');
        $evaluatorUser->clubs()->attach($club->id);

        $ttmUser = User::factory()->create();
        $ttmUser->assignRole('Member');
        $ttmUser->clubs()->attach($club->id);

        $timerUser = User::factory()->create();
        $timerUser->assignRole('Member');
        $timerUser->clubs()->attach($club->id);

        $meeting = Meeting::create([
            'club_id' => $club->id,
            'meeting_number' => 209,
            'meeting_date' => now()->toDateString(),
            'status' => 'in_progress',
        ]);

        $timerRoleType = MeetingRoleType::where('slug', 'timer')->first();
        MeetingRole::create([
            'meeting_id' => $meeting->id,
            'meeting_role_type_id' => $timerRoleType->id,
            'user_id' => $timerUser->id,
        ]);

        $speaker = MeetingSpeaker::create([
            'meeting_id' => $meeting->id,
            'user_id' => $speakerUser->id,
            'slot' => 1,
            'topic' => 'The Art of Persuasion',
            'duration' => '5-7 mins',
        ]);

        $evaluation = MeetingEvaluation::create([
            'meeting_id' => $meeting->id,
            'speaker_id' => $speaker->id,
            'evaluator_user_id' => $evaluatorUser->id,
        ]);

        $ttm = MeetingTtmSpeaker::create([
            'meeting_id' => $meeting->id,
            'user_id' => $ttmUser->id,
            'slot' => 1,
            'topic' => 'Spontaneous Speaking',
            'duration' => '1-2 mins',
        ]);

        $this->actingAs($timerUser);

        Livewire::test(MeetingShow::class, ['meeting' => $meeting])
            ->assertStatus(200)
            ->call('saveTimerLogs', [
                'prepared_speaker_' . $speaker->id => [
                    'speaker_type' => 'prepared_speaker',
                    'reference_id' => $speaker->id,
                    'user_id' => $speakerUser->id,
                    'allotted_time' => '5-7 mins',
                    'time_taken' => '06:15',
                    'status' => 'within_time',
                    'notes' => 'Paced well',
                ],
                'evaluator_' . $evaluation->id => [
                    'speaker_type' => 'evaluator',
                    'reference_id' => $evaluation->id,
                    'user_id' => $evaluatorUser->id,
                    'allotted_time' => '2-3 mins',
                    'time_taken' => '03:15',
                    'status' => 'over_time',
                    'notes' => 'Red flag at 3:00',
                ],
                'ttm_speaker_' . $ttm->id => [
                    'speaker_type' => 'ttm_speaker',
                    'reference_id' => $ttm->id,
                    'user_id' => $ttmUser->id,
                    'allotted_time' => '1-2 mins',
                    'time_taken' => '01:45',
                    'status' => 'within_time',
                    'notes' => 'Green at 1:00',
                ],
            ])
            ->assertDispatched('timer-logs-saved');

        $this->assertDatabaseHas('meeting_timer_logs', [
            'meeting_id' => $meeting->id,
            'speaker_type' => 'prepared_speaker',
            'reference_id' => $speaker->id,
            'user_id' => $speakerUser->id,
            'time_taken' => '06:15',
            'status' => 'within_time',
        ]);

        $this->assertDatabaseHas('meeting_timer_logs', [
            'meeting_id' => $meeting->id,
            'speaker_type' => 'evaluator',
            'reference_id' => $evaluation->id,
            'user_id' => $evaluatorUser->id,
            'time_taken' => '03:15',
            'status' => 'over_time',
        ]);

        $this->assertDatabaseHas('meeting_timer_logs', [
            'meeting_id' => $meeting->id,
            'speaker_type' => 'ttm_speaker',
            'reference_id' => $ttm->id,
            'user_id' => $ttmUser->id,
            'time_taken' => '01:45',
            'status' => 'within_time',
        ]);
    }

    public function test_unauthorized_user_cannot_modify_timer_logs(): void
    {
        $club = Club::first();
        $user = User::factory()->create();
        $user->assignRole('Member');
        $user->clubs()->attach($club->id);

        $timerUser = User::factory()->create();
        $timerUser->assignRole('Member');
        $timerUser->clubs()->attach($club->id);

        $meeting = Meeting::create([
            'club_id' => $club->id,
            'meeting_number' => 210,
            'meeting_date' => now()->toDateString(),
            'status' => 'in_progress',
        ]);

        $timerRoleType = MeetingRoleType::where('slug', 'timer')->first();
        MeetingRole::create([
            'meeting_id' => $meeting->id,
            'meeting_role_type_id' => $timerRoleType->id,
            'user_id' => $timerUser->id,
        ]);

        $this->actingAs($user);

        Livewire::test(MeetingShow::class, ['meeting' => $meeting])
            ->call('saveTimerLogs', [
                'prepared_speaker_1' => [
                    'speaker_type' => 'prepared_speaker',
                    'reference_id' => 1,
                    'user_id' => $user->id,
                    'time_taken' => '05:00',
                    'status' => 'within_time',
                ],
            ])
            ->assertForbidden();
    }

    public function test_unauthorized_user_cannot_modify_live_counters(): void
    {
        $club = Club::first();
        $user = User::factory()->create();
        $user->assignRole('Member');
        $user->clubs()->attach($club->id);

        $ahUser = User::factory()->create();
        $ahUser->assignRole('Member');
        $ahUser->clubs()->attach($club->id);

        $meeting = Meeting::create([
            'club_id' => $club->id,
            'meeting_number' => 211,
            'meeting_date' => now()->toDateString(),
            'status' => 'in_progress',
        ]);

        $ahRoleType = MeetingRoleType::where('slug', 'ah-counter')->first();
        MeetingRole::create([
            'meeting_id' => $meeting->id,
            'meeting_role_type_id' => $ahRoleType->id,
            'user_id' => $ahUser->id,
        ]);

        $this->actingAs($user);

        Livewire::test(MeetingShow::class, ['meeting' => $meeting])
            ->call('incrementFiller', $user->id, 'ah_count')
            ->assertForbidden();

        Livewire::test(MeetingShow::class, ['meeting' => $meeting])
            ->call('incrementWordOfDay', $user->id)
            ->assertForbidden();

        Livewire::test(MeetingShow::class, ['meeting' => $meeting])
            ->call('saveAllCounts', [
                $user->id => ['ah_count' => 5],
            ], null, 'ah_counter')
            ->assertForbidden();
    }

    public function test_meeting_facilitator_can_modify_timer_and_counters_when_unassigned(): void
    {
        $club = Club::first();
        $tmodUser = User::factory()->create();
        $tmodUser->assignRole('Member');
        $tmodUser->clubs()->attach($club->id);

        $meeting = Meeting::create([
            'club_id' => $club->id,
            'meeting_number' => 212,
            'meeting_date' => now()->toDateString(),
            'status' => 'in_progress',
        ]);

        $tmodRoleType = MeetingRoleType::where('slug', 'tmod')->first();
        MeetingRole::create([
            'meeting_id' => $meeting->id,
            'meeting_role_type_id' => $tmodRoleType->id,
            'user_id' => $tmodUser->id,
        ]);

        $this->actingAs($tmodUser);

        // Can modify filler count
        Livewire::test(MeetingShow::class, ['meeting' => $meeting])
            ->call('incrementFiller', $tmodUser->id, 'ah_count')
            ->assertStatus(200);

        $this->assertDatabaseHas('meeting_ah_counter_logs', [
            'meeting_id' => $meeting->id,
            'user_id' => $tmodUser->id,
            'ah_count' => 1,
        ]);

        // Can modify timer
        Livewire::test(MeetingShow::class, ['meeting' => $meeting])
            ->call('saveTimerLogs', [
                'prepared_speaker_99' => [
                    'speaker_type' => 'prepared_speaker',
                    'reference_id' => 99,
                    'user_id' => $tmodUser->id,
                    'time_taken' => '06:00',
                    'status' => 'within_time',
                ],
            ])
            ->assertDispatched('timer-logs-saved');
    }

    public function test_club_admin_can_modify_timer_and_counters(): void
    {
        $club = Club::first();
        $adminUser = User::factory()->create();
        $adminUser->assignRole('Admin');
        $adminUser->clubs()->attach($club->id);

        $meeting = Meeting::create([
            'club_id' => $club->id,
            'meeting_number' => 213,
            'meeting_date' => now()->toDateString(),
            'status' => 'in_progress',
        ]);

        $this->actingAs($adminUser);

        Livewire::test(MeetingShow::class, ['meeting' => $meeting])
            ->call('incrementWordOfDay', $adminUser->id)
            ->assertStatus(200);

        $this->assertDatabaseHas('meeting_grammarian_logs', [
            'meeting_id' => $meeting->id,
            'user_id' => $adminUser->id,
            'word_of_day_count' => 1,
        ]);
    }

    public function test_completed_meeting_cannot_be_modified_by_anyone(): void
    {
        $club = Club::first();
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');
        $admin->clubs()->attach($club->id);

        $member = User::factory()->create();
        $member->assignRole('Member');
        $member->clubs()->attach($club->id);

        $meeting = Meeting::create([
            'club_id' => $club->id,
            'meeting_number' => 214,
            'meeting_date' => now()->toDateString(),
            'status' => 'completed',
        ]);

        $roleType = MeetingRoleType::first();
        $existingRole = MeetingRole::create([
            'meeting_id' => $meeting->id,
            'meeting_role_type_id' => $roleType->id,
            'user_id' => $member->id,
        ]);

        $speaker = MeetingSpeaker::create([
            'meeting_id' => $meeting->id,
            'user_id' => $member->id,
            'speech_type' => 'Standard',
            'topic' => 'My Great Speech',
        ]);

        $ttm = MeetingTtmSpeaker::create([
            'meeting_id' => $meeting->id,
            'user_id' => $member->id,
            'slot' => 1,
            'topic' => 'My TT Topic',
        ]);

        $eval = MeetingEvaluation::create([
            'meeting_id' => $meeting->id,
            'speaker_id' => $speaker->id,
            'evaluator_user_id' => $admin->id,
            'notes' => 'Original evaluation',
        ]);

        // 1. Even Super Admin / Admin cannot save timer logs on completed meeting
        $this->actingAs($admin);
        Livewire::test(MeetingShow::class, ['meeting' => $meeting])
            ->call('saveTimerLogs', [
                'prepared_speaker_' . $speaker->id => [
                    'speaker_type' => 'prepared_speaker',
                    'reference_id' => $speaker->id,
                    'user_id' => $member->id,
                    'time_taken' => '05:30',
                    'status' => 'within_time',
                ],
            ])
            ->assertStatus(403);

        // 2. Cannot save live counter counts
        Livewire::test(MeetingShow::class, ['meeting' => $meeting])
            ->call('saveAllCounts', [$member->id => ['ah_count' => 5]], null, 'ah_counter')
            ->assertStatus(403);

        // 3. Cannot increment/decrement counters
        Livewire::test(MeetingShow::class, ['meeting' => $meeting])
            ->call('incrementFiller', $member->id, 'ah_count')
            ->assertStatus(403);

        // 4. Cannot save evaluation notes or listening master report
        Livewire::test(MeetingShow::class, ['meeting' => $meeting])
            ->call('saveEvaluationNotes', $eval->id, 'Hacked notes')
            ->assertStatus(403);

        Livewire::test(MeetingShow::class, ['meeting' => $meeting])
            ->set('listeningMasterReport', '<p>Hacked Listening Quiz</p>')
            ->call('saveListeningMasterReport')
            ->assertStatus(403);

        // 5. Cannot volunteer for roles, speakers, or TTM
        $this->actingAs($member);
        Livewire::test(MeetingShow::class, ['meeting' => $meeting])
            ->call('signUpForRole', $roleType->id)
            ->assertStatus(403);

        Livewire::test(MeetingShow::class, ['meeting' => $meeting])
            ->call('signUpAsSpeaker')
            ->assertStatus(403);

        Livewire::test(MeetingShow::class, ['meeting' => $meeting])
            ->call('signUpForTtm')
            ->assertStatus(403);

        // 6. Cannot relinquish existing roles
        Livewire::test(MeetingShow::class, ['meeting' => $meeting])
            ->call('relinquishRole', $existingRole->id)
            ->assertStatus(403);

        Livewire::test(MeetingShow::class, ['meeting' => $meeting])
            ->call('relinquishSpeaker', $speaker->id)
            ->assertStatus(403);

        Livewire::test(MeetingShow::class, ['meeting' => $meeting])
            ->call('relinquishTtm', $ttm->id)
            ->assertStatus(403);
    }

    public function test_assigned_listening_master_and_admin_can_save_report(): void
    {
        $club = Club::first();
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        $admin->clubs()->attach($club->id);

        $listeningUser = User::factory()->create();
        $listeningUser->assignRole('Member');
        $listeningUser->clubs()->attach($club->id);

        $otherUser = User::factory()->create();
        $otherUser->assignRole('Member');
        $otherUser->clubs()->attach($club->id);

        $meeting = Meeting::create([
            'club_id' => $club->id,
            'meeting_number' => 215,
            'meeting_date' => now()->toDateString(),
            'status' => 'in_progress',
        ]);

        $listeningRoleType = MeetingRoleType::where('slug', 'listening-master')->first();
        MeetingRole::create([
            'meeting_id' => $meeting->id,
            'meeting_role_type_id' => $listeningRoleType->id,
            'user_id' => $listeningUser->id,
        ]);

        // 1. Assigned Listening Master can save report
        $this->actingAs($listeningUser);
        Livewire::test(MeetingShow::class, ['meeting' => $meeting])
            ->set('listeningMasterReport', '<p><strong>Quiz:</strong> What was the main takeaway?</p>')
            ->call('saveListeningMasterReport')
            ->assertDispatched('listening-master-report-saved');

        $this->assertDatabaseHas('meetings', [
            'id' => $meeting->id,
            'listening_master_report' => '<p><strong>Quiz:</strong> What was the main takeaway?</p>',
        ]);

        // 2. Admin can also save/update report
        $this->actingAs($admin);
        Livewire::test(MeetingShow::class, ['meeting' => $meeting])
            ->set('listeningMasterReport', '<p>Updated by Admin</p>')
            ->call('saveListeningMasterReport')
            ->assertDispatched('listening-master-report-saved');

        $this->assertDatabaseHas('meetings', [
            'id' => $meeting->id,
            'listening_master_report' => '<p>Updated by Admin</p>',
        ]);

        // 3. Unauthorized member cannot save report
        $this->actingAs($otherUser);
        Livewire::test(MeetingShow::class, ['meeting' => $meeting])
            ->set('listeningMasterReport', '<p>Hacked by unauthorized user</p>')
            ->call('saveListeningMasterReport')
            ->assertStatus(403);
    }
}

