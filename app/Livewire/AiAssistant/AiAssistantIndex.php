<?php

namespace App\Livewire\AiAssistant;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\User;
use App\Services\LocalAiService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('AI Assistant — Speech Club')]
class AiAssistantIndex extends Component
{
    // Mode & Active Conversation
    public string $activeMode             = 'general'; // general | outline | tabletopics | role | coaching
    public ?int   $activeConversationId   = null;
    public string $searchHistory          = '';
    public bool   $historyOpen            = false; // mobile history toggle

    // Inputs & Generation
    public string $prompt                 = '';
    public string $response               = ''; // for backward compatibility & direct outputs
    public bool   $loading                = false;
    public string $engineLabel            = '';
    public bool   $ollamaOnline           = false;

    // Speech outline fields
    public string $speechTopic            = '';
    public string $speechProject          = '';
    public string $speechTrack            = '';

    // Table Topics
    public string $tableTopicsTheme       = '';

    // Role Script
    public string $selectedRole           = 'TMOD';

    // Member coaching
    public ?int   $selectedUserId         = null;
    public array  $clubMembers            = [];

    public array  $rolOptions = ['TMOD', 'Timer', 'Ah-Counter', 'Grammarian', 'General Evaluator'];

    protected LocalAiService $ai;

    public function boot(LocalAiService $ai): void
    {
        $this->ai = $ai;
    }

    public function mount(): void
    {
        abort_unless(config('services.ai_assistant.enabled', true), 403, 'The AI Assistant feature is currently disabled.');

        $this->engineLabel  = $this->ai->engineLabel();
        $this->ollamaOnline = $this->ai->isOllamaAvailable();

        // Load club members for coaching selector
        $clubId = Auth::user()?->primaryClub()?->id;
        if ($clubId) {
            $this->clubMembers = User::whereHas('clubs', fn($q) => $q->where('clubs.id', $clubId))
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn($u) => ['id' => $u->id, 'name' => $u->name])
                ->toArray();
        }

        // Open the most recent conversation if available
        $latest = AiConversation::forUser(Auth::id())
            ->latest('updated_at')
            ->first();

        if ($latest) {
            $this->activeConversationId = $latest->id;
            $this->activeMode           = $latest->mode;
        }
    }

    // =========================================================================
    // History & Conversation Management (ChatGPT / Gemini Module)
    // =========================================================================

    public function getGroupedConversationsProperty()
    {
        $userId = Auth::id();
        if (! $userId) {
            return collect();
        }

        $query = AiConversation::forUser($userId)
            ->withCount('messages')
            ->orderBy('updated_at', 'desc');

        if (! empty(trim($this->searchHistory))) {
            $query->where('title', 'like', '%' . trim($this->searchHistory) . '%');
        }

        return $query->get()->groupBy(function ($conv) {
            if ($conv->updated_at->isToday()) {
                return 'Today';
            }
            if ($conv->updated_at->isYesterday()) {
                return 'Yesterday';
            }
            if ($conv->updated_at->greaterThanOrEqualTo(now()->subDays(7))) {
                return 'Previous 7 Days';
            }
            return 'Older';
        });
    }

    public function getActiveConversationProperty(): ?AiConversation
    {
        if (! $this->activeConversationId) {
            return null;
        }

        return AiConversation::where('id', $this->activeConversationId)
            ->where('user_id', Auth::id())
            ->with('messages')
            ->first();
    }

    public function startNewChat(string $mode = 'general'): void
    {
        $this->activeConversationId = null;
        $this->activeMode           = $mode;
        $this->prompt               = '';
        $this->response             = '';
        $this->historyOpen          = false;
    }

    public function selectConversation(int $id): void
    {
        $conv = AiConversation::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if ($conv) {
            $this->activeConversationId = $conv->id;
            $this->activeMode           = $conv->mode;
            $this->response             = '';
            $this->prompt               = '';
            $this->historyOpen          = false;
            $this->dispatch('scroll-chat-to-bottom');
        }
    }

    public function deleteConversation(int $id): void
    {
        $conv = AiConversation::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if ($conv) {
            $conv->delete();

            if ($this->activeConversationId === $id) {
                $latest = AiConversation::forUser(Auth::id())->latest('updated_at')->first();
                $this->activeConversationId = $latest?->id;
                $this->activeMode           = $latest?->mode ?? 'general';
                $this->response             = '';
            }
        }
    }

    public function setMode(string $mode): void
    {
        $this->activeMode = $mode;
        $this->response   = '';
    }

    // =========================================================================
    // Conversational Messaging (ChatGPT / Gemini Multi-Turn Engine)
    // =========================================================================

    public function sendMessage(): void
    {
        abort_unless(config('services.ai_assistant.enabled', true), 403, 'The AI Assistant feature is currently disabled.');

        $this->validate([
            'prompt' => 'required|string|min:2|max:3000',
        ]);

        $userPrompt = trim($this->prompt);
        $this->prompt = '';
        $this->loading = true;

        try {
            $conv = $this->ensureActiveConversation($userPrompt);

            // 1. Record User Message in Conversation History
            $userMsg = $conv->messages()->create([
                'role'    => 'user',
                'content' => $userPrompt,
            ]);

            // 2. Load Multi-Turn Context (up to last 10 messages)
            $history = $conv->messages()
                ->orderBy('created_at', 'asc')
                ->get(['role', 'content'])
                ->map(fn($m) => ['role' => $m->role, 'content' => $m->content])
                ->toArray();

            // 3. Request Multi-Turn Completion from LocalAiService
            $aiResponse = $this->ai->chat($history);

            // 4. Record Assistant Response in Conversation History
            $conv->messages()->create([
                'role'    => 'assistant',
                'content' => $aiResponse,
                'meta'    => [
                    'engine' => $this->engineLabel,
                ],
            ]);

            $conv->touch(); // updates updated_at for history sorting

            $this->response = $aiResponse; // backward compatibility
            $this->dispatch('scroll-chat-to-bottom');
        } finally {
            $this->loading = false;
        }
    }

    // Backward-compatible alias for sendPrompt
    public function sendPrompt(): void
    {
        $this->sendMessage();
    }

    // =========================================================================
    // Specialized Toastmasters Generators (Creates a Conversational Turn)
    // =========================================================================

    public function generateSpeechOutline(): void
    {
        $topic = trim($this->speechTopic) ?: 'Compelling Toastmasters Speech';
        $summary = "Speech Outline: \"{$topic}\"";
        if ($this->speechProject) {
            $summary .= " ({$this->speechProject})";
        }

        $promptText = "Please generate a complete Toastmasters speech outline with title, opening hook, 3 key points with transitions, and memorable closing call-to-action.\n- Topic: {$topic}";
        if ($this->speechProject) {
            $promptText .= "\n- Pathways Project: {$this->speechProject}";
        }
        if ($this->speechTrack) {
            $promptText .= "\n- Pathways Track: {$this->speechTrack}";
        }

        $this->executePresetTurn($promptText, $summary, 'outline');
    }

    public function generateTableTopics(): void
    {
        $theme = trim($this->tableTopicsTheme) ?: 'Personal Growth and Impromptu Speaking';
        $promptText = "Please generate a dynamic Toastmasters Table Topics session on the theme: \"{$theme}\". Provide a Word of the Day (with definition and example) and 5 engaging, thought-provoking questions.";
        $this->executePresetTurn($promptText, "Table Topics: {$theme}", 'tabletopics');
    }

    public function generateRoleScript(): void
    {
        $role = $this->selectedRole ?: 'TMOD';
        $promptText = "Please provide the standardized Toastmasters meeting role guide, opening script, and report instructions for the role of: {$role}.";
        $this->executePresetTurn($promptText, "Role Script: {$role}", 'role');
    }

    public function generateCoaching(): void
    {
        if (! $this->selectedUserId) {
            $this->response = '> ⚠️ Please select a member to generate their coaching report.';
            return;
        }

        $user   = User::findOrFail($this->selectedUserId);
        $clubId = Auth::user()?->primaryClub()?->id ?? 0;

        $this->loading = true;
        try {
            $report = $this->ai->generateMemberCoaching($user, $clubId);

            $conv = $this->ensureActiveConversation("Coaching Report for {$user->name}", 'coaching');
            $conv->messages()->create([
                'role'    => 'user',
                'content' => "Generate AI Speech Coaching Report for {$user->name}",
            ]);
            $conv->messages()->create([
                'role'    => 'assistant',
                'content' => $report,
                'meta'    => ['engine' => $this->engineLabel],
            ]);
            $conv->touch();

            $this->response = $report;
            $this->dispatch('scroll-chat-to-bottom');
        } finally {
            $this->loading = false;
        }
    }

    public function clearResponse(): void
    {
        $this->response = '';
    }

    public function refreshEngineStatus(): void
    {
        $this->engineLabel  = $this->ai->engineLabel();
        $this->ollamaOnline = $this->ai->isOllamaAvailable();
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    protected function ensureActiveConversation(string $initialPrompt, string $mode = 'general'): AiConversation
    {
        if ($this->activeConversationId) {
            $conv = AiConversation::where('id', $this->activeConversationId)
                ->where('user_id', Auth::id())
                ->first();

            if ($conv) {
                return $conv;
            }
        }

        // Create a new conversation record
        $title = Str::limit(preg_replace('/[\r\n]+/', ' ', $initialPrompt), 40);
        $conv  = AiConversation::create([
            'user_id' => Auth::id(),
            'club_id' => Auth::user()?->primaryClub()?->id,
            'title'   => $title ?: 'New Conversation',
            'mode'    => $mode,
        ]);

        $this->activeConversationId = $conv->id;
        $this->activeMode           = $mode;

        return $conv;
    }

    protected function executePresetTurn(string $promptText, string $title, string $mode): void
    {
        $this->loading = true;
        try {
            $conv = $this->ensureActiveConversation($title, $mode);

            // Record User prompt
            $conv->messages()->create([
                'role'    => 'user',
                'content' => $promptText,
            ]);

            // Request from AI
            $history = $conv->messages()
                ->orderBy('created_at', 'asc')
                ->get(['role', 'content'])
                ->map(fn($m) => ['role' => $m->role, 'content' => $m->content])
                ->toArray();

            $aiResponse = $this->ai->chat($history);

            // Record Assistant response
            $conv->messages()->create([
                'role'    => 'assistant',
                'content' => $aiResponse,
                'meta'    => ['engine' => $this->engineLabel],
            ]);

            $conv->touch();
            $this->response = $aiResponse;
            $this->dispatch('scroll-chat-to-bottom');
        } finally {
            $this->loading = false;
        }
    }

    public function render()
    {
        return view('livewire.ai-assistant.ai-assistant-index', [
            'groupedConversations' => $this->groupedConversations,
            'activeConversation'   => $this->activeConversation,
        ]);
    }
}
