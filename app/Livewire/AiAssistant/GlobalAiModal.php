<?php

namespace App\Livewire\AiAssistant;

use App\Models\User;
use App\Services\LocalAiService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * GlobalAiModal
 *
 * A slide-over AI drawer accessible from every page of the application.
 * Auto-detects page context (member profile or meeting page) to pre-load relevant data.
 * Dispatched open/close events: 'open-ai-modal'.
 */
class GlobalAiModal extends Component
{
    public bool   $isOpen         = false;
    public string $prompt         = '';
    public string $response       = '';
    public bool   $loading        = false;
    public string $activeMode     = 'general';   // general | coaching | outline | tabletopics | role
    public string $engineLabel    = '';
    public bool   $ollamaOnline   = false;

    // Context (pre-loaded when on a member page)
    public ?int   $contextUserId  = null;
    public string $contextName    = '';

    // Role script selector
    public string $selectedRole   = 'TMOD';

    public array $rolOptions = ['TMOD', 'Timer', 'Ah-Counter', 'Grammarian', 'General Evaluator'];

    protected LocalAiService $ai;

    public function boot(LocalAiService $ai): void
    {
        $this->ai = $ai;
    }

    public function mount(): void
    {
        $this->engineLabel  = $this->ai->engineLabel();
        $this->ollamaOnline = $this->ai->isOllamaAvailable();
    }

    // =========================================================================
    // Listeners
    // =========================================================================

    #[\Livewire\Attributes\On('open-ai-modal')]
    public function openModal(?int $userId = null, string $mode = 'general'): void
    {
        if (! config('services.ai_assistant.enabled', true)) {
            $this->isOpen = false;
            return;
        }

        $this->isOpen     = true;
        $this->activeMode = $mode;
        $this->response   = '';
        $this->prompt     = '';

        if ($userId) {
            $user = User::find($userId);
            if ($user) {
                $this->contextUserId = $userId;
                $this->contextName   = $user->name;
            }
        }

        // Refresh engine status each time drawer opens
        $this->engineLabel  = $this->ai->engineLabel();
        $this->ollamaOnline = $this->ai->isOllamaAvailable();
    }

    public function closeModal(): void
    {
        $this->isOpen = false;
    }

    // =========================================================================
    // Actions
    // =========================================================================

    public function setMode(string $mode): void
    {
        $this->activeMode = $mode;
        $this->response   = '';
        $this->prompt     = '';
    }

    public function sendPrompt(): void
    {
        abort_unless(config('services.ai_assistant.enabled', true), 403, 'The AI Assistant feature is currently disabled.');

        $this->validate(['prompt' => 'required|string|min:3|max:1000']);
        $this->loading = true;

        try {
            $this->response = $this->ai->ask($this->prompt);
        } finally {
            $this->loading = false;
        }
    }

    public function generateTableTopics(): void
    {
        $this->loading = true;
        try {
            $this->response = $this->ai->generateTableTopics($this->prompt);
        } finally {
            $this->loading = false;
        }
    }

    public function generateSpeechOutline(): void
    {
        $this->loading = true;
        try {
            $this->response = $this->ai->generateSpeechOutline($this->prompt);
        } finally {
            $this->loading = false;
        }
    }

    public function generateRoleScript(): void
    {
        $this->loading = true;
        try {
            $this->response = $this->ai->generateRoleScript($this->selectedRole);
        } finally {
            $this->loading = false;
        }
    }

    public function generateMemberCoaching(): void
    {
        if (! $this->contextUserId) {
            $this->response = '> ⚠️ No member selected. Open this assistant from a member profile page to get personalized coaching.';
            return;
        }

        $this->loading = true;
        $user = User::findOrFail($this->contextUserId);
        $clubId = Auth::user()->primaryClub()?->id ?? 0;

        try {
            $this->response = $this->ai->generateMemberCoaching($user, $clubId);
        } finally {
            $this->loading = false;
        }
    }

    public function clearResponse(): void
    {
        $this->response = '';
        $this->prompt   = '';
    }

    public function render()
    {
        if (! config('services.ai_assistant.enabled', true)) {
            return <<<'HTML'
            <div></div>
            HTML;
        }

        return view('livewire.ai-assistant.global-ai-modal');
    }
}
