<?php

namespace Tests\Feature;

use App\Livewire\AiAssistant\AiAssistantIndex;
use App\Livewire\AiAssistant\GlobalAiModal;
use App\Livewire\Users\UserShow;
use App\Models\Club;
use App\Models\User;
use App\Services\LocalAiService;
use Database\Seeders\ClubSeeder;
use Database\Seeders\MeetingRoleTypeSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AiAssistantTest extends TestCase
{
    use RefreshDatabase;

    protected Club $club;
    protected User $member;
    protected User $president;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.ollama.url'           => 'http://127.0.0.1:99999',
            'services.ai_assistant.enabled' => true,
        ]);
        Livewire::withoutLazyLoading();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(ClubSeeder::class);
        $this->seed(MeetingRoleTypeSeeder::class);

        $this->club = Club::first();

        // Regular Member
        $this->member = User::factory()->create(['status' => 'active']);
        $this->member->clubs()->attach($this->club->id);
        $this->member->assignRole('Member');

        // Club President with users.view permission
        $this->president = User::factory()->create(['status' => 'active']);
        $this->president->clubs()->attach($this->club->id);
        $this->president->assignRole('President');
    }

    public function test_ai_assistant_page_requires_authentication(): void
    {
        $this->get('/ai-assistant')
            ->assertRedirect('/login');
    }

    public function test_ai_assistant_page_renders_for_authenticated_user(): void
    {
        $this->actingAs($this->member)
            ->get('/ai-assistant')
            ->assertSuccessful()
            ->assertSeeLivewire(AiAssistantIndex::class)
            ->assertSee('AI Assistant');
    }

    public function test_ai_assistant_component_can_switch_modes_and_generate_outline(): void
    {
        $this->actingAs($this->member);

        Livewire::test(AiAssistantIndex::class)
            ->assertSet('activeMode', 'general')
            ->call('setMode', 'outline')
            ->assertSet('activeMode', 'outline')
            ->set('speechTopic', 'The Power of Habit')
            ->set('speechProject', 'Ice Breaker')
            ->call('generateSpeechOutline')
            ->assertSee('Speech Outline')
            ->assertSee('The Power of Habit');
    }

    public function test_ai_assistant_component_can_generate_table_topics(): void
    {
        $this->actingAs($this->member);

        Livewire::test(AiAssistantIndex::class)
            ->call('setMode', 'topics')
            ->set('tableTopicsTheme', 'Courage and Resilience')
            ->call('generateTableTopics')
            ->assertSee('Table Topics')
            ->assertSee('Courage and Resilience');
    }

    public function test_ai_assistant_component_can_generate_role_script(): void
    {
        $this->actingAs($this->member);

        Livewire::test(AiAssistantIndex::class)
            ->call('setMode', 'roles')
            ->set('selectedRole', 'Timer')
            ->call('generateRoleScript')
            ->assertSee('Timer');
    }

    public function test_global_ai_modal_component_renders_and_handles_prompt(): void
    {
        $this->actingAs($this->member);

        Livewire::test(GlobalAiModal::class)
            ->assertSet('isOpen', false)
            ->call('openModal')
            ->assertSet('isOpen', true)
            ->set('prompt', 'Give me tips for vocal variety')
            ->call('sendPrompt')
            ->assertSee('Speech Club Assistant')
            ->call('closeModal')
            ->assertSet('isOpen', false);
    }

    public function test_local_ai_service_offline_fallback_methods(): void
    {
        config(['services.ollama.url' => 'http://127.0.0.1:99999']);
        $ai = new LocalAiService();

        $this->assertFalse($ai->isOllamaAvailable());
        $this->assertStringContainsString('Built-in', $ai->engineLabel());

        $outline = $ai->generateSpeechOutline('Mindfulness at Work', 'Ice Breaker');
        $this->assertStringContainsString('Mindfulness at Work', $outline);
        $this->assertStringContainsString('Hook', $outline);

        $topics = $ai->generateTableTopics('Leadership Under Pressure');
        $this->assertStringContainsString('Leadership Under Pressure', $topics);
        $this->assertStringContainsString('Word of the Day', $topics);

        $timerScript = $ai->generateRoleScript('Timer', null);
        $this->assertStringContainsString('Timer', $timerScript);
        $this->assertStringContainsString('Green', $timerScript);

        $coaching = $ai->generateMemberCoaching($this->member, $this->club->id);
        $this->assertStringContainsString($this->member->name, $coaching);
        $this->assertStringContainsString('Speech Coaching Report', $coaching);

        $tmodIntro = $ai->generateTmodIntroduction($this->member);
        $this->assertStringContainsString($this->member->name, $tmodIntro);
    }

    public function test_user_show_ai_speech_coach_tab_and_generation(): void
    {
        config(['services.ollama.url' => 'http://127.0.0.1:99999']);
        $this->actingAs($this->president);

        Livewire::test(UserShow::class, ['user' => $this->member])
            ->assertSee('AI Speech Coach')
            ->set('activeTab', 'ai_coach')
            ->call('generateAiCoaching')
            ->assertSee('Speech Coaching Report')
            ->assertSee($this->member->name)
            ->call('generateTmodIntro')
            ->assertSee('Speaker Introduction')
            ->call('clearAiCoaching')
            ->assertSet('aiCoachingReport', '');
    }

    public function test_ai_assistant_creates_conversation_and_records_multi_turn_messages(): void
    {
        config(['services.ollama.url' => 'http://127.0.0.1:99999']);
        $this->actingAs($this->member);

        $component = Livewire::test(AiAssistantIndex::class)
            ->set('prompt', 'How can I become a better impromptu speaker?')
            ->call('sendMessage');

        $this->assertDatabaseCount('ai_conversations', 1);
        $this->assertDatabaseCount('ai_messages', 2);

        $conv = \App\Models\AiConversation::first();
        $this->assertEquals($this->member->id, $conv->user_id);
        $this->assertNotEmpty($conv->title);

        // Second turn in same conversation
        $component->set('prompt', 'Give me 3 practice exercises')
            ->call('sendMessage');

        $this->assertDatabaseCount('ai_conversations', 1);
        $this->assertDatabaseCount('ai_messages', 4);
    }

    public function test_ai_assistant_history_management_and_deletion(): void
    {
        config(['services.ollama.url' => 'http://127.0.0.1:99999']);
        $this->actingAs($this->member);

        $conv = \App\Models\AiConversation::create([
            'user_id' => $this->member->id,
            'title'   => 'Sample Test Conversation',
            'mode'    => 'general',
        ]);

        Livewire::test(AiAssistantIndex::class)
            ->assertSee('Sample Test Conversation')
            ->call('deleteConversation', $conv->id)
            ->assertDontSee('Sample Test Conversation');

        $this->assertDatabaseMissing('ai_conversations', ['id' => $conv->id]);
    }

    public function test_ai_assistant_hidden_and_forbidden_when_disabled(): void
    {
        config(['services.ai_assistant.enabled' => false]);
        $this->assertFalse(LocalAiService::isEnabled());

        // Sidebar link & header button should NOT be visible
        $this->actingAs($this->member)
            ->get('/')
            ->assertSuccessful()
            ->assertDontSee('id="global-ai-btn"', false);

        // Direct HTTP GET access to /ai-assistant should return 403 Forbidden
        $this->actingAs($this->member)
            ->get('/ai-assistant')
            ->assertForbidden();

        // Direct Livewire component instantiation should abort 403
        Livewire::actingAs($this->member)
            ->test(AiAssistantIndex::class)
            ->assertForbidden();
    }

    public function test_ai_assistant_visible_and_accessible_when_enabled(): void
    {
        config(['services.ai_assistant.enabled' => true]);
        $this->assertTrue(LocalAiService::isEnabled());

        $this->actingAs($this->member)
            ->get('/')
            ->assertSuccessful()
            ->assertSee('AI Assistant')
            ->assertSee('id="global-ai-btn"', false);

        $this->actingAs($this->member)
            ->get('/ai-assistant')
            ->assertSuccessful();
    }
}

