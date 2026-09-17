<?php

namespace App\Services;

use App\Models\Meeting;
use App\Models\MeetingAhCounterLog;
use App\Models\MeetingAttendance;
use App\Models\MeetingEvaluation;
use App\Models\MeetingSpeaker;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * LocalAiService
 *
 * Provides AI-powered speech coaching, topic brainstorming, Table Topics generation,
 * meeting role scripts, and general club assistance.
 *
 * Priority 1: Local Ollama (Gemma 4) running in CMD — configured via OLLAMA_URL and OLLAMA_MODEL.
 * Priority 2: Built-in Speech Club Intelligence Engine (offline PHP fallback, always works).
 */
class LocalAiService
{
    protected string $ollamaUrl;
    protected string $model;
    protected int $timeout;

    public function __construct()
    {
        $this->ollamaUrl = config('services.ollama.url', 'http://127.0.0.1:11434');
        $this->model     = config('services.ollama.model', 'gemma4:e4b');
        $this->timeout   = config('services.ollama.timeout', 20);
    }

    // =========================================================================
    // Public API
    // =========================================================================

    public function getOllamaUrl(): string
    {
        return config('services.ollama.url', $this->ollamaUrl ?: 'http://127.0.0.1:11434');
    }

    public function getTimeout(): int
    {
        return (int) config('services.ollama.timeout', $this->timeout ?: 180);
    }

    public function getModel(): string
    {
        return config('services.ollama.model', $this->model ?: 'gemma4:e4b');
    }

    /**
     * Check if AI Assistant feature is enabled via ENV.
     */
    public static function isEnabled(): bool
    {
        return filter_var(config('services.ai_assistant.enabled', true), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Check if local Ollama is running and accessible.
     */
    public function isOllamaAvailable(): bool
    {
        try {
            $response = Http::timeout(2)->get($this->getOllamaUrl());
            return $response->successful() || $response->status() === 200;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Get the active engine name for display in the UI.
     */
    public function engineLabel(): string
    {
        if ($this->isOllamaAvailable()) {
            return '🟢 Local Ollama — ' . $this->model;
        }

        return '⚡ Built-in Speech Club AI Engine';
    }

    /**
     * Generate a free-form response to any prompt.
     * Uses Ollama if available, falls back to built-in engine.
     */
    public function ask(string $prompt, string $systemContext = ''): string
    {
        if ($this->isOllamaAvailable()) {
            return $this->ollamaGenerate($prompt, $systemContext);
        }

        return $this->fallbackAnswer($prompt);
    }

    /**
     * Conduct a multi-turn conversation with memory (ChatGPT / Gemini style).
     * Passes message history to Ollama /api/chat so the model remembers context across turns.
     *
     * @param array<int, array{role: string, content: string}> $messages
     */
    public function chat(array $messages, string $systemContext = ''): string
    {
        if ($this->isOllamaAvailable()) {
            return $this->ollamaChat($messages, $systemContext);
        }

        $lastUserMsg = collect($messages)->where('role', 'user')->last();
        $prompt = $lastUserMsg['content'] ?? '';

        return $this->fallbackAnswer($prompt);
    }

    /**
     * Generate a personalized speech coaching report for a member.
     */
    public function generateMemberCoaching(User $user, int $clubId): string
    {
        $stats = $this->collectMemberStats($user, $clubId);

        if ($this->isOllamaAvailable()) {
            $prompt = $this->buildCoachingPrompt($user, $stats);
            return $this->ollamaGenerate($prompt, $this->toastmastersSystemPrompt());
        }

        return $this->buildBuiltinCoachingReport($user, $stats);
    }

    /**
     * Generate a speech topic, hook, and 3-point outline.
     */
    public function generateSpeechOutline(string $topic = '', string $projectTitle = '', string $pathwaysTrack = ''): string
    {
        if ($this->isOllamaAvailable()) {
            $p = "Generate a Toastmasters speech outline for a member with the following details:\n";
            $p .= $topic       ? "- Speech Topic: $topic\n" : "- Topic: suggest a compelling topic\n";
            $p .= $projectTitle ? "- Project: $projectTitle\n" : '';
            $p .= $pathwaysTrack ? "- Pathways Track: $pathwaysTrack\n" : '';
            $p .= "\nProvide:\n1. A captivating speech title\n2. A strong opening hook (story, rhetorical question, or surprising statistic)\n3. Three main key points with transitions\n4. A memorable closing call-to-action\n\nKeep the tone warm, inspiring, and practical.";
            return $this->ollamaGenerate($p, $this->toastmastersSystemPrompt());
        }

        return $this->buildBuiltinSpeechOutline($topic, $projectTitle, $pathwaysTrack);
    }

    /**
     * Generate Table Topics theme, 5 impromptu questions, and Word of the Day.
     */
    public function generateTableTopics(string $theme = ''): string
    {
        if ($this->isOllamaAvailable()) {
            $t = $theme ?: 'an inspiring life lessons theme';
            $p = "Generate a Toastmasters Table Topics session for the theme: \"$t\".\n\nProvide:\n1. A catchy session title\n2. A brief Table Topics Master introduction (2–3 sentences)\n3. Five diverse impromptu speaking prompts/questions that relate to the theme\n4. A Word of the Day with definition and a sample sentence\n\nMake the prompts varied: mix personal experiences, hypothetical scenarios, and opinion-based questions.";
            return $this->ollamaGenerate($p, $this->toastmastersSystemPrompt());
        }

        return $this->buildBuiltinTableTopics($theme);
    }

    /**
     * Generate a script and guidance for a specific meeting role.
     */
    public function generateRoleScript(string $roleType, ?Meeting $meeting = null): string
    {
        if ($this->isOllamaAvailable()) {
            $meetingContext = $meeting ? "Meeting Date: {$meeting->meeting_date}, Theme: " . ($meeting->theme ?? 'General') . '.' : '';
            $p = "Generate a complete guide and script for the Toastmasters role: **$roleType**.\n$meetingContext\n\nInclude:\n1. Role objective and responsibilities\n2. Opening announcement/introduction script\n3. Timing rules or tracking rules if applicable\n4. Report/summary script to deliver at the end\n5. Tips for doing this role excellently\n\nKeep it practical and beginner-friendly.";
            return $this->ollamaGenerate($p, $this->toastmastersSystemPrompt());
        }

        return $this->buildBuiltinRoleScript($roleType, $meeting);
    }

    /**
     * Generate a TMOD introduction script for a speaker.
     */
    public function generateTmodIntroduction(User $speaker, string $speechTitle = '', string $projectTitle = ''): string
    {
        $name   = $speaker->name;
        $title  = $speechTitle  ?: 'their prepared speech';
        $proj   = $projectTitle ?: 'their Pathways project';

        if ($this->isOllamaAvailable()) {
            $p = "Write a warm, engaging Toastmaster of the Day (TMOD) speaker introduction for:\n- Speaker Name: $name\n- Speech Title: \"$title\"\n- Project: $proj\n\nThe introduction should: build anticipation, briefly mention the speaker's passion or background, and end with 'Please welcome [name]!' Keep it 60–90 seconds long when spoken aloud.";
            return $this->ollamaGenerate($p, $this->toastmastersSystemPrompt());
        }

        return $this->buildBuiltinTmodIntro($name, $title, $proj);
    }

    // =========================================================================
    // Ollama HTTP Integration
    // =========================================================================

    protected function ollamaGenerate(string $prompt, string $system = ''): string
    {
        try {
            $payload = [
                'model'  => $this->getModel(),
                'prompt' => $prompt,
                'stream' => false,
                'options' => [
                    'temperature' => 0.7,
                    'num_predict' => 400,
                ],
            ];

            if ($system) {
                $payload['system'] = $system;
            }

            $response = Http::timeout($this->getTimeout())
                ->post($this->getOllamaUrl() . '/api/generate', $payload);

            if ($response->successful()) {
                return trim($response->json('response') ?? '');
            }

            Log::warning('Ollama API returned non-success status', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Ollama request failed, using fallback', ['error' => $e->getMessage()]);
        }

        return $this->fallbackAnswer($prompt);
    }

    protected function ollamaChat(array $messages, string $system = ''): string
    {
        try {
            $formattedMessages = [];

            $systemPrompt = $system ?: $this->toastmastersSystemPrompt();
            $formattedMessages[] = [
                'role'    => 'system',
                'content' => $systemPrompt,
            ];

            // Send up to last 10 messages for conversation context memory
            $recent = array_slice($messages, -10);
            foreach ($recent as $msg) {
                if (! empty($msg['content'])) {
                    $formattedMessages[] = [
                        'role'    => $msg['role'] ?? 'user',
                        'content' => $msg['content'],
                    ];
                }
            }

            $payload = [
                'model'    => $this->getModel(),
                'messages' => $formattedMessages,
                'stream'   => false,
                'options'  => [
                    'temperature' => 0.7,
                    'num_predict' => 400,
                ],
            ];

            $response = Http::timeout($this->getTimeout())
                ->post($this->getOllamaUrl() . '/api/chat', $payload);

            if ($response->successful()) {
                $content = $response->json('message.content');
                if ($content !== null) {
                    return trim($content);
                }
            }

            Log::warning('Ollama chat API returned non-success status', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Ollama chat request failed, using fallback', ['error' => $e->getMessage()]);
        }

        $lastUserMsg = collect($messages)->where('role', 'user')->last();
        return $this->fallbackAnswer($lastUserMsg['content'] ?? '');
    }

    protected function toastmastersSystemPrompt(): string
    {
        return 'You are an expert Toastmasters International speech coach and club mentor. You specialize in the Pathways educational program, meeting facilitation, evaluations, and helping members grow as confident communicators. Always respond in clear, structured markdown with headers, bullet points, and encouraging language.';
    }

    // =========================================================================
    // Built-in Fallback Engine — Data-Driven Speech Club Intelligence
    // =========================================================================

    protected function collectMemberStats(User $user, int $clubId): array
    {
        $speechCount = MeetingSpeaker::where('user_id', $user->id)
            ->whereHas('meeting', fn($q) => $q->where('club_id', $clubId))
            ->count();

        $ttmCount = \App\Models\MeetingTtmSpeaker::where('user_id', $user->id)
            ->whereHas('meeting', fn($q) => $q->where('club_id', $clubId))
            ->count();

        $evalCount = MeetingEvaluation::where('evaluator_user_id', $user->id)
            ->whereHas('meeting', fn($q) => $q->where('club_id', $clubId))
            ->count();

        $attended = MeetingAttendance::where('user_id', $user->id)
            ->whereHas('meeting', fn($q) => $q->where('club_id', $clubId))
            ->count();

        // Filler word analysis — get the last 5 meetings
        $recentFillers = MeetingAhCounterLog::where('user_id', $user->id)
            ->whereHas('meeting', fn($q) => $q->where('club_id', $clubId))
            ->orderByDesc('id')
            ->limit(5)
            ->get(['filler_word', 'count']);

        $totalFillers  = $recentFillers->sum('count');
        $topFiller     = $recentFillers->sortByDesc('count')->first();

        // Last speech
        $lastSpeech = MeetingSpeaker::where('user_id', $user->id)
            ->whereHas('meeting', fn($q) => $q->where('club_id', $clubId))
            ->with('meeting', 'project')
            ->orderByDesc('id')
            ->first();

        return compact('speechCount', 'ttmCount', 'evalCount', 'attended', 'totalFillers', 'topFiller', 'lastSpeech', 'recentFillers');
    }

    protected function buildCoachingPrompt(User $user, array $stats): string
    {
        $name = $user->name;
        $speechCount = $stats['speechCount'] ?? 0;
        $ttmCount = $stats['ttmCount'] ?? 0;
        $evalCount = $stats['evalCount'] ?? 0;
        $attended = $stats['attended'] ?? 0;
        $totalFillers = $stats['totalFillers'] ?? 0;

        $p = "Please generate an inspiring, highly personalized Toastmasters speech coaching report for member $name.\n\n";
        $p .= "Member Club Record:\n";
        $p .= "- Prepared Speeches Completed: $speechCount\n";
        $p .= "- Table Topics Impromptu Speeches: $ttmCount\n";
        $p .= "- Speech Evaluations Given: $evalCount\n";
        $p .= "- Meetings Attended: $attended\n";
        $p .= "- Recent Filler Word Count: $totalFillers\n\n";
        $p .= "Provide:\n";
        $p .= "1. A warm acknowledgment of their Toastmasters journey and consistency\n";
        $p .= "2. Identified Strengths based on their speech/evaluation experience\n";
        $p .= "3. Areas of Focus & Next Growth Milestones (Pathways goals, vocal variety, impromptu mastery)\n";
        $p .= "4. Practical Speaking Drill of the Week\n\n";
        $p .= "Format in clean markdown with headers and bullet points.";

        return $p;
    }

    protected function buildBuiltinCoachingReport(User $user, array $s): string
    {
        $name = $user->name;
        $last = $s['lastSpeech'];

        $lines   = [];
        $lines[] = "## ✨ AI Speech Coaching Report — $name";
        $lines[] = '';
        $lines[] = '### 📊 Activity Summary';
        $lines[] = "| Metric | Count |";
        $lines[] = "|--------|-------|";
        $lines[] = "| Prepared Speeches | {$s['speechCount']} |";
        $lines[] = "| Table Topics Speeches | {$s['ttmCount']} |";
        $lines[] = "| Evaluations Given | {$s['evalCount']} |";
        $lines[] = "| Meetings Attended | {$s['attended']} |";
        $lines[] = '';

        // Strengths
        $lines[] = '### 💪 Strengths';
        if ($s['speechCount'] >= 5) {
            $lines[] = '- **Consistent Speaker**: You have delivered ' . $s['speechCount'] . ' prepared speeches — excellent commitment to your growth journey!';
        } elseif ($s['speechCount'] > 0) {
            $lines[] = '- **Getting Started**: You have delivered ' . $s['speechCount'] . ' prepared speech(es). Great start — keep going!';
        } else {
            $lines[] = '- **Ready to Begin**: You haven\'t delivered a prepared speech yet. Your first speech will be the most memorable!';
        }

        if ($s['evalCount'] >= 3) {
            $lines[] = '- **Active Evaluator**: Giving ' . $s['evalCount'] . ' evaluations shows strong engagement and leadership.';
        }
        if ($s['ttmCount'] >= 3) {
            $lines[] = '- **Improv Champion**: ' . $s['ttmCount'] . ' Table Topics speeches demonstrate excellent spontaneous speaking ability.';
        }
        $lines[] = '';

        // Filler word analysis
        $lines[] = '### 🗣 Filler Word Trend (Last 5 Meetings)';
        if ($s['totalFillers'] === 0) {
            $lines[] = '- ✅ **No filler words recorded** in recent meetings — outstanding vocal discipline!';
        } else {
            $lines[] = "- **Total filler words logged**: {$s['totalFillers']}";
            if ($s['topFiller']) {
                $lines[] = "- **Most frequent filler**: \"*{$s['topFiller']->filler_word}*\" ({$s['topFiller']->count} times)";
            }
            if ($s['totalFillers'] > 10) {
                $lines[] = '- 💡 **Tip**: Practice the **pause** technique — when you feel a filler word coming, simply pause silently. A pause sounds confident; a filler word does not.';
            } elseif ($s['totalFillers'] > 0) {
                $lines[] = '- 💡 **Tip**: You\'re making good progress on vocal clarity. Record yourself during practice sessions to notice filler patterns early.';
            }
        }
        $lines[] = '';

        // Next steps
        $lines[] = '### 🎯 Recommended Next Steps';
        if ($s['speechCount'] === 0) {
            $lines[] = '1. **Sign up for your Ice Breaker speech** at the next meeting — everyone is rooting for you!';
            $lines[] = '2. **Volunteer for a Table Topics** to warm up your impromptu speaking.';
            $lines[] = '3. **Attend the next 3 meetings** to absorb the club atmosphere and learn from other speakers.';
        } elseif ($s['speechCount'] < 5) {
            $lines[] = '1. **Schedule your next prepared speech** — consistency is the fastest path to growth.';
            $lines[] = '2. **Request a mentor** through your VP Education for personalized project guidance.';
            $lines[] = "3. **Give an evaluation** at the next meeting to sharpen your critical listening skills.";
        } else {
            $lines[] = '1. **Pursue a leadership role** (Table Topics Master, General Evaluator, or TMOD) to challenge yourself.';
            $lines[] = '2. **Focus on advanced projects** that stretch your comfort zone — storytelling, persuasion, or humour.';
            $lines[] = '3. **Mentor a newer member** to cement your knowledge and earn your leadership milestones.';
        }

        if ($last) {
            $lines[] = '';
            $lines[] = '### 🏅 Last Speech';
            $lines[] = "- **Project**: " . ($last->project->name ?? 'Pathways Speech');
            $lines[] = "- **Meeting**: " . ($last->meeting->meeting_date ?? 'Recent');
            $lines[] = '- **Advice**: Review any evaluations you received and focus on one specific improvement for your next speech.';
        }

        $lines[] = '';
        $lines[] = '---';
        $lines[] = '*Generated by the Built-in Speech Club Intelligence Engine. Connect Ollama locally for Gemma 4-powered insights.*';

        return implode("\n", $lines);
    }

    protected function buildBuiltinSpeechOutline(string $topic, string $projectTitle, string $pathwaysTrack): string
    {
        $topics = [
            'The Power of Listening',
            'Embracing Failure as a Stepping Stone',
            'The Art of Saying No',
            'Why Curiosity is a Superpower',
            'Small Habits, Remarkable Results',
            'The Courage to Be a Beginner',
        ];

        $finalTopic = $topic ?: $topics[array_rand($topics)];

        return <<<MD
## 🎤 Speech Outline — "$finalTopic"

**Project**: {$projectTitle} {$pathwaysTrack}

---

### 🪝 Opening Hook (30–60 seconds)
Begin with a brief personal story or a surprising statistic that directly connects to the core theme. Example opener:

> *"Three years ago, I made a decision that most people said was a mistake. And they were right — at first..."*

Pause. Let the audience wonder. Then smoothly transition to your thesis statement.

---

### 🗝 Main Point 1 — Set the Scene
- Introduce the situation or problem your speech addresses.
- Use a concrete example (personal story, news event, or research finding).
- **Transition**: *"But this situation isn't unique — it happens to all of us, and here's why..."*

### 🗝 Main Point 2 — The Core Insight
- Share the key lesson, strategy, or perspective shift.
- Break it into 2–3 actionable sub-points.
- Use descriptive language and vivid imagery.
- **Transition**: *"Knowing this changes everything — let me show you how..."*

### 🗝 Main Point 3 — Application & Evidence
- Demonstrate the insight in action with a specific case study or result.
- Make it relatable to your audience's everyday life.
- **Transition**: *"So what does this mean for you, sitting here today?"*

---

### 🔚 Closing Call to Action (30–45 seconds)
Return to your opening hook — close the loop. Leave the audience with one clear, memorable takeaway.

> *"The next time you face [challenge], remember: [core message]. That decision is yours to make — starting today."*

---

**⏱ Timing Guide**: Ice Breaker (4–6 min), Standard (5–7 min), Advanced (8–10 min)

*Generated by the Built-in Speech Club Intelligence Engine.*
MD;
    }

    protected function buildBuiltinTableTopics(string $theme): string
    {
        $finalTheme = $theme ?: 'Life Lessons & Resilience';

        $words = [
            ['word' => 'Perspicacious', 'def' => 'having a ready insight; shrewd.', 'example' => 'Her perspicacious analysis of the situation impressed everyone in the room.'],
            ['word' => 'Equanimity',    'def' => 'mental calmness and composure, especially in difficult situations.', 'example' => 'He handled the unexpected challenge with remarkable equanimity.'],
            ['word' => 'Tenacious',     'def' => 'holding firmly to a purpose or course of action.', 'example' => 'The tenacious speaker practised every day until her delivery was flawless.'],
            ['word' => 'Eloquent',      'def' => 'fluent, persuasive, and expressive in speech.', 'example' => 'An eloquent speaker can move an audience with just a few well-chosen words.'],
            ['word' => 'Candid',        'def' => 'truthful and straightforward; frank.', 'example' => 'His candid feedback helped the new member improve rapidly.'],
        ];

        $wotd = $words[array_rand($words)];

        return <<<MD
## 🎯 Table Topics Session — "$finalTheme"

---

### 📣 Table Topics Master Introduction
*"Welcome to today's Table Topics session! Our theme is **{$finalTheme}**. Table Topics is your chance to think on your feet and speak with confidence — even without preparation. Each speaker will have 1 to 2 minutes. Ready? Let's dive in!"*

---

### 💬 Question 1 — Personal Experience
**"Tell us about a time you faced an unexpected obstacle. What did you do, and what did you learn from it?"**

### 💬 Question 2 — Hypothetical Scenario
**"If you could give your 15-year-old self one piece of advice about resilience, what would it be and why?"**

### 💬 Question 3 — Opinion-Based
**"Some say failure is the best teacher. Do you agree or disagree? Give us a specific example."**

### 💬 Question 4 — Values & Priorities
**"What is one habit or daily practice that you believe contributes most to your personal growth or resilience?"**

### 💬 Question 5 — Creative / Imaginative
**"You've been asked to give a 5-minute keynote speech to 1,000 young people on the theme of '{$finalTheme}'. What would your opening line be, and why?"**

---

### 📖 Word of the Day — *{$wotd['word']}*
> **Definition**: {$wotd['def']}
> **In a sentence**: *"{$wotd['example']}"*

Challenge all speakers to use today's word naturally in their Table Topic response!

---

*Generated by the Built-in Speech Club Intelligence Engine.*
MD;
    }

    protected function buildBuiltinRoleScript(string $roleType, ?Meeting $meeting): string
    {
        $date  = $meeting?->meeting_date ?? 'today';
        $theme = $meeting?->theme ?? 'our meeting theme';

        $scripts = [
            'TMOD' => <<<MD
## 🎙 Toastmaster of the Day (TMOD) Guide & Script

**Your Role**: You are the host and overall facilitator of the meeting. You set the tone, keep energy high, and ensure the meeting flows smoothly.

---

### 🕐 Opening Script
*"Good [morning/evening] everyone, and welcome to [Club Name]! I'm [Your Name], your Toastmaster of the Day for our $date meeting. Our theme today is **$theme**. I'm excited to guide us through an inspiring session."*

*"For our guests — a warm welcome! Toastmasters is a place where you can practise speaking, leadership, and listening in a supportive environment. Tonight you'll see exactly how it works."*

### 📋 Your Responsibilities
- Arrive 15 minutes early to review the agenda.
- Introduce each segment and speaker with energy.
- Time-keep the overall meeting and manage transitions.
- Handle unexpected gaps or delays gracefully.

### 🔚 Closing Script
*"What a fantastic meeting! Thank you to all our speakers, role players, and evaluators. Special thanks to [highlight a speaker or moment]. Remember — every speech is a step forward. I now return the floor to our President."*

---

### 💡 Pro Tips
- Smile and make eye contact when you speak.
- Prepare 1–2 filler transitions in case a segment ends early.
- Know the names and speech titles of all speakers in advance.
MD,

            'Timer' => <<<MD
## ⏱ Timer Role — Guide & Scripts

**Your Role**: Track speaking times for all prepared speeches, Table Topics, and evaluations. Signal speakers using green, yellow, and red signals.

---

### 📋 Standard Timing Signals
| Signal | Meaning |
|--------|---------|
| 🟢 Green | Minimum time reached — speaker is in the acceptable range |
| 🟡 Yellow | One minute remaining |
| 🔴 Red | Maximum time reached — speaker should begin closing |

### Standard Time Ranges
| Segment | Min | Max |
|---------|-----|-----|
| Table Topics | 1:00 | 2:00 |
| Prepared Speech (standard) | 5:00 | 7:00 |
| Evaluations | 2:00 | 3:00 |

### 🎙 Opening Script
*"Fellow Toastmasters and guests — I'm [Your Name], your Timer for today's meeting. I'll be tracking time for all speakers using green, yellow, and red signals. Green means [time], yellow means one minute remains, and red means time is up. Please wrap up when you see the red signal."*

### 🔚 Timer Report Script
*"Madam/Mr. Toastmaster — here is the timing report:*
*- [Speaker Name]: [time spoken] — [within/over/under time]*
*(Repeat for each speaker)*
*Back to you, [TMOD name]."*
MD,

            'Ah-Counter' => <<<MD
## 🔤 Ah-Counter Role — Guide & Scripts

**Your Role**: Listen carefully to all speakers and count every filler word or crutch phrase used.

---

### 📋 Common Filler Words to Track
- Verbal fillers: "ah", "um", "er", "uh"
- Crutch phrases: "you know", "like", "basically", "actually", "right?", "so"
- Repetitive sounds: extended "and..." or "but..."

### 🎙 Opening Script
*"Fellow Toastmasters and guests — I'm [Your Name], your Ah-Counter. My role is to track filler words and crutch phrases that speakers use as verbal crutches. I'll note them quietly during the meeting and report at the end. Remember — a well-placed pause is far more powerful than 'um'!"*

### 🔚 Report Script
*"Madam/Mr. Toastmaster — here is the Ah-Counter report:*
*- [Speaker Name]: [number] filler words — most common was '[word]'*
*(Repeat for each speaker)*
*Overall, [positive observation]. Back to you."*

### 💡 Tips
- Use a tally chart — one column per speaker.
- Be encouraging in your report, not critical.
- Acknowledge speakers with zero filler words explicitly.
MD,

            'Grammarian' => <<<MD
## 📝 Grammarian Role — Guide & Scripts

**Your Role**: Introduce and reinforce the Word of the Day, note excellent language usage, and gently highlight grammatical slips.

---

### 🎙 Opening Script — Introducing Word of the Day
*"Fellow Toastmasters and guests — I'm [Your Name], your Grammarian. My role is to monitor language usage throughout the meeting. I'll note beautiful phrases, excellent vocabulary choices, and any grammatical slips.*

*Today's Word of the Day is: **[WORD]**. It means [definition]. Here it is in a sentence: '[example sentence]'. I challenge each of you — speakers and role players alike — to use this word naturally during the meeting!"*

### 🔚 Report Script
*"Madam/Mr. Toastmaster — as your Grammarian, here are my observations:*

***Word of the Day Usage**: [list who used it and how]*

***Language Highlights**: [quote excellent phrases — e.g., '[Speaker] used the beautiful phrase...']*

***Grammatical Notes**: [mention slips gently — e.g., 'We heard a few double negatives today...']*

*Back to you, [TMOD]."*
MD,

            'General Evaluator' => <<<MD
## 🔍 General Evaluator Role — Guide & Scripts

**Your Role**: Evaluate the overall meeting — all role players, organisation, time management, and atmosphere.

---

### 📋 What to Observe
- **TMOD**: Did they control the meeting? Were transitions smooth?
- **Role Players**: Did Timer, Ah-Counter, and Grammarian do their roles clearly?
- **Speakers**: Overall quality and engagement.
- **Meeting Flow**: Did it start/end on time? Were there awkward pauses?

### 🎙 Opening Script
*"Fellow Toastmasters and guests — I'm [Your Name], your General Evaluator. I'll be assessing the overall quality of today's meeting. I'll provide feedback on our role players, the flow of the meeting, and the overall club atmosphere. I'll also introduce our individual speech evaluators."*

### 🔚 Report Script
*"Madam/Mr. Toastmaster — today's meeting was [adjective: energetic / thoughtful / well-paced].*

*Highlights I'd like to commend:*
*- [Role player / TMOD]: [specific praise]*
*- [Another observation]*

*Areas where we can grow:*
*- [Constructive observation — e.g., transitions between segments could be smoother]*

*Overall, this was a [rating: excellent / very good / good] meeting. Congratulations to all participants. Back to you, [TMOD]."*

---

### 💡 Tips
- Take notes throughout the entire meeting.
- Be specific — vague praise or criticism helps no one.
- End on an encouraging, forward-looking note.
MD,
        ];

        // Try to match role type (case-insensitive partial match)
        foreach ($scripts as $key => $script) {
            if (stripos($roleType, $key) !== false || stripos($key, $roleType) !== false) {
                return $script . "\n\n*Generated by the Built-in Speech Club Intelligence Engine.*";
            }
        }

        // Generic fallback
        return "## 📋 Role Guide — $roleType\n\nPlease ask your VPE or an experienced member for guidance on this role. Our club mentors are always happy to help!\n\n*Generated by the Built-in Speech Club Intelligence Engine.*";
    }

    protected function buildBuiltinTmodIntro(string $name, string $title, string $project): string
    {
        return <<<MD
## 🎤 TMOD Speaker Introduction — $name

---

*"Our next speaker is someone who brings [energy/warmth/passion] to everything they do — both inside and outside this club.*

*[He/She/They] has been on an incredible Toastmasters journey, most recently tackling **$project**. Today, [he/she/they] steps up to share a speech titled **"$title"**.*

*When you hear [him/her/them] speak, listen for [tip: a compelling story / a surprising insight / a clear call to action]. It's going to be memorable.*

*Fellow Toastmasters and honoured guests — please put your hands together and give a warm welcome to… **$name**!"*

---

*Generated by the Built-in Speech Club Intelligence Engine.*
MD;
    }

    protected function buildBuiltinTimingRules(): string
    {
        return <<<MD
## ⏱ Speech Club Assistant — Toastmasters Timing Rules & Signals

In Toastmasters, the **Timer** uses green, yellow, and red cards (or digital backgrounds) to help speakers practice time management and stay within their designated speaking limits.

---

### 🚦 The Meaning of the Signals

| Signal | Meaning | Action for Speaker |
| :--- | :--- | :--- |
| 🟢 **Green** | **Minimum Qualifying Time** reached | You are now in the qualified speaking window for voting. |
| 🟡 **Yellow** | **Target Midpoint / Warning** reached | Begin transitioning towards your conclusion. |
| 🔴 **Red** | **Maximum Limit** reached | Conclude your speech within the 30-second grace period. |

---

### ⏱ Standard Timing Windows & Grace Periods

| Segment / Role | 🟢 Green | 🟡 Yellow | 🔴 Red | Grace Period |
| :--- | :--- | :--- | :--- | :--- |
| **Table Topics** (1–2 min) | 1:00 min | 1:30 min | 2:00 min | +30 seconds (no under-time grace) |
| **Ice Breaker Speech** (4–6 min) | 4:00 min | 5:00 min | 6:00 min | 30s before & after (3:30 – 6:30) |
| **Standard Speech** (5–7 min) | 5:00 min | 6:00 min | 7:00 min | 30s before & after (4:30 – 7:30) |
| **Advanced Speech** (8–10 min) | 8:00 min | 9:00 min | 10:00 min | 30s before & after (7:30 – 10:30) |
| **Speech Evaluation** (2–3 min) | 2:00 min | 2:30 min | 3:00 min | 30s before & after (1:30 – 3:30) |

> ⚠️ **Disqualification Note**: In official Toastmasters speech contests, speakers who speak less than 30 seconds below the minimum time or more than 30 seconds above the maximum time are disqualified from voting or winning.

---

### 💡 Best Practices for Speakers
1. **Locate the Timer** before you start speaking and ensure they are clearly in your line of sight.
2. When the 🟢 **Green** light turns on, you know you have satisfied the minimum requirement.
3. When the 🟡 **Yellow** light appears, move into your final point or beginning of your summary.
4. When the 🔴 **Red** light appears, deliver your prepared concluding sentence or call-to-action and thank the audience.

*Speech Club Intelligence Engine — Toastmasters Standards*
MD;
    }

    protected function buildBuiltinEvaluationGuide(): string
    {
        return <<<MD
## 📋 Speech Club Assistant — Speech Evaluation Guide (CRC Method)

Effective speech evaluations follow the **CRC (Commend — Recommend — Commend)** sandwich technique to encourage speakers while offering concrete, actionable areas for improvement.

---

### 🥪 The 3-Part Evaluation Structure

1. **Commend (What went well)**:
   - Highlight 2–3 specific strengths you observed (e.g., strong opening hook, vivid storytelling, great vocal projection, confident stage presence).
   - Use specific quotes or moments: *"When you paused after saying [...], it really drew the room in."*

2. **Recommend (Growth opportunity)**:
   - Provide 1–2 actionable, constructive suggestions for their next speech.
   - Frame suggestions positively: *"To elevate this speech even further, consider..."*
   - Demonstrate how to implement it: give an example of an alternative phrasing or gesture.

3. **Commend & Encourage (Closing summary)**:
   - Summarize the main takeaway of the speech.
   - Reiterate your confidence in the speaker: *"Your message resonated deeply, and I look forward to your next speech!"*

---

### ⏱ Evaluation Timing
- **Duration**: 2 to 3 minutes
- 🟢 **Green**: 2:00 min
- 🟡 **Yellow**: 2:30 min
- 🔴 **Red**: 3:00 min (wrap up within 30s grace period)

*Speech Club Intelligence Engine — Toastmasters Standards*
MD;
    }

    protected function buildBuiltinVocalAndDeliveryGuide(): string
    {
        return <<<MD
## 🎙 Speech Club Assistant — Vocal Variety & Stage Presence Guide

Great speakers engage their audience through dynamic vocal variation and confident physical delivery.

---

### 🔊 The 4 Pillars of Vocal Variety
1. **Pace (Speed)**: Vary your speed to match emotions. Speed up to create excitement or urgency; slow down for serious, reflective, or technical moments.
2. **Pitch (Inflection)**: Avoid a monotone delivery. Vary your pitch up and down naturally to emphasize key words. End statements with a descending inflection to project authority.
3. **Power (Volume)**: Speak loudly enough to be heard in the back row. Lower your volume to a whisper for dramatic secrets or intimate moments.
4. **Pause (The Secret Weapon)**: Replace filler words (*"um"*, *"ah"*, *"like"*) with deliberate 2–3 second pauses. Pauses let your points sink in and make you appear in total control.

---

### 🧍 Overcoming Nervousness & Stage Fright
- **The 4-7-8 Breathing Drill**: Inhale for 4 seconds, hold for 7 seconds, exhale slowly for 8 seconds before taking the stage.
- **Power Stance**: Stand with feet shoulder-width apart, shoulders back, chin level.
- **Eye Contact**: Hold eye contact with one person for 3–5 seconds (one complete thought) before moving to someone else across the room.

*Speech Club Intelligence Engine — Toastmasters Standards*
MD;
    }

    protected function fallbackAnswer(string $prompt): string
    {
        $lower = strtolower(trim($prompt));

        // 1. Timing rules, signals, timer role
        if (
            str_contains($lower, 'timer') ||
            str_contains($lower, 'timing') ||
            str_contains($lower, 'time limit') ||
            (str_contains($lower, 'green') && (str_contains($lower, 'red') || str_contains($lower, 'yellow'))) ||
            str_contains($lower, 'timing rule')
        ) {
            return $this->buildBuiltinTimingRules();
        }

        // 2. Evaluation, feedback, critique
        if (
            str_contains($lower, 'evaluat') ||
            str_contains($lower, 'feedback') ||
            str_contains($lower, 'critique') ||
            str_contains($lower, 'sandwich')
        ) {
            return $this->buildBuiltinEvaluationGuide();
        }

        // 3. Table Topics & Impromptu
        if (
            str_contains($lower, 'table topic') ||
            str_contains($lower, 'impromptu') ||
            str_contains($lower, 'ttm')
        ) {
            return $this->buildBuiltinTableTopics('');
        }

        // 4. Speech topics, outline, ice breaker
        if (
            str_contains($lower, 'ice breaker') ||
            str_contains($lower, 'icebreaker') ||
            (str_contains($lower, 'speech') && (str_contains($lower, 'outline') || str_contains($lower, 'topic') || str_contains($lower, 'write') || str_contains($lower, 'idea') || str_contains($lower, 'prepare')))
        ) {
            return $this->buildBuiltinSpeechOutline('', '', '');
        }

        // 5. Vocal variety, nervousness, delivery, stage fright
        if (
            str_contains($lower, 'vocal') ||
            str_contains($lower, 'nervous') ||
            str_contains($lower, 'stage fright') ||
            str_contains($lower, 'body language') ||
            str_contains($lower, 'eye contact') ||
            str_contains($lower, 'fear')
        ) {
            return $this->buildBuiltinVocalAndDeliveryGuide();
        }

        // 6. Ah-Counter & Fillers
        if (
            str_contains($lower, 'ah-counter') ||
            str_contains($lower, 'ah counter') ||
            str_contains($lower, 'filler') ||
            str_contains($lower, 'crutch')
        ) {
            return $this->buildBuiltinRoleScript('Ah-Counter', null);
        }

        // 7. Grammarian & Word of the Day
        if (
            str_contains($lower, 'grammarian') ||
            str_contains($lower, 'word of the day') ||
            str_contains($lower, 'wotd') ||
            str_contains($lower, 'grammar')
        ) {
            return $this->buildBuiltinRoleScript('Grammarian', null);
        }

        // 8. TMOD / Toastmaster of the Day
        if (
            str_contains($lower, 'tmod') ||
            str_contains($lower, 'toastmaster of the day') ||
            str_contains($lower, 'emcee') ||
            str_contains($lower, 'master of ceremonies')
        ) {
            return $this->buildBuiltinRoleScript('TMOD', null);
        }

        // 9. General Evaluator
        if (str_contains($lower, 'general evaluator')) {
            return $this->buildBuiltinRoleScript('General Evaluator', null);
        }

        // If generic speech mentioned
        if (str_contains($lower, 'speech')) {
            return $this->buildBuiltinSpeechOutline('', '', '');
        }

        // Default guidance response
        $tip = $this->isOllamaAvailable()
            ? "💡 **Tip**: Ollama is active with `{$this->getModel()}`. Ask any speech, evaluation, timing, or Toastmasters question!"
            : "💡 **Tip**: For full conversational AI, run `ollama run gemma4:e4b` in your terminal and I'll automatically upgrade to Gemma 4!";

        return <<<MD
## 💬 Speech Club Assistant

I'm your built-in Speech Club AI — here's how I can help:

- **Timing Rules & Signals** — *"Explains green, yellow, and red timing rules for speeches"*
- **Speech Evaluations** — *"How to evaluate a speech using the CRC method"*
- **Speech Topic & Outline** — *"Generate a speech outline for my Ice Breaker"*
- **Table Topics** — *"Create Table Topics for a growth mindset theme"*
- **Role Scripts** — *"Give me the Timer role script"*, *"Grammarian guide"*, *"TMOD script"*
- **Vocal Variety & Delivery** — *"Tips for overcoming stage fright"*
- **Member Coaching** — *Click "Generate Coaching Report" on a member's profile*

> $tip

*Built-in Speech Club Intelligence Engine — always available, zero cost.*
MD;
    }
}
