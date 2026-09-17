<div
    x-data="{
        drawerOpen: false,
        activeMode: @entangle('activeMode'),
        copied: false,
        selectMode(mode) {
            this.activeMode = mode;
        },
        copyResponse() {
            const text = this.$refs.aiResponse ? this.$refs.aiResponse.innerText.trim() : '';
            if (text) {
                navigator.clipboard.writeText(text);
                this.copied = true;
                setTimeout(() => this.copied = false, 2000);
            }
        }
    }"
    x-on:open-ai-modal.window="drawerOpen = true"
    x-on:close-ai-modal.window="drawerOpen = false; $wire.closeModal()"
    x-on:keydown.escape.window="if (drawerOpen) { drawerOpen = false; $wire.closeModal(); }"
    class="relative z-50"
>
    {{-- =====================================================================
         Overlay Backdrop
    ====================================================================== --}}
    <div
        x-show="drawerOpen"
        x-transition:enter="transition-opacity ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm"
        @click="drawerOpen = false; $wire.closeModal()"
        style="display:none;"
    ></div>

    {{-- =====================================================================
         Slide-Over Drawer
    ====================================================================== --}}
    <div
        x-show="drawerOpen"
        x-transition:enter="transform transition ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transform transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed inset-y-0 right-0 w-full max-w-lg flex flex-col shadow-2xl"
        style="display:none;"
    >
        {{-- Glass background matching app theme & dark mode --}}
        <div class="flex flex-col h-full bg-white/95 dark:bg-slate-900/95 backdrop-blur-2xl border-l border-slate-200 dark:border-slate-800">

            {{-- ============================================================
                 Header
            ============================================================= --}}
            <div class="flex-shrink-0 px-5 py-4 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        {{-- Sparkle Icon using theme primary color --}}
                        <div class="w-9 h-9 rounded-xl bg-primary-600 text-white flex items-center justify-center shadow-md shadow-primary-600/30 flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3l1.5 4.5L11 9l-4.5 1.5L5 15l-1.5-4.5L-1 9l4.5-1.5L5 3zM19 3l1 3 3 1-3 1-1 3-1-3-3-1 3-1 1-3z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900 dark:text-white">AI Assistant</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 truncate max-w-[220px]">{{ $engineLabel }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1">
                        <a
                            href="{{ route('ai-assistant.index') }}"
                            class="p-2 rounded-lg text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-950/40 transition-colors"
                            title="Open Full AI Chat & History"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                        <button
                            @click="drawerOpen = false; $wire.closeModal()"
                            class="p-2 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                            aria-label="Close AI Assistant"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Ollama status banner --}}
                @if(! $ollamaOnline)
                    <div class="mt-3 flex items-start gap-2 p-2.5 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700/40 text-xs text-amber-700 dark:text-amber-300">
                        <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Run <code class="font-mono bg-amber-100 dark:bg-amber-900/40 px-1 rounded">ollama run gemma4:e4b</code> in CMD for Gemma 4 AI. Using built-in engine now.</span>
                    </div>
                @endif

                {{-- Context badge --}}
                @if($contextName)
                    <div class="mt-2 flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-primary-50 dark:bg-primary-950/40 border border-primary-200 dark:border-primary-800/40">
                        <svg class="w-3.5 h-3.5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span class="text-xs font-medium text-primary-700 dark:text-primary-300">Context: {{ $contextName }}</span>
                    </div>
                @endif
            </div>

            {{-- ============================================================
                 Mode Tabs (Instantaneous Alpine Switching - 0ms Delay)
            ============================================================= --}}
            <div class="flex-shrink-0 px-5 pt-3 pb-2">
                <div class="flex gap-1.5 flex-wrap">
                    @foreach([
                        ['id' => 'general',     'label' => '💬 General',       'title' => 'General Club Q&A'],
                        ['id' => 'outline',     'label' => '🎤 Speech Outline', 'title' => 'Speech Topic & Outline'],
                        ['id' => 'tabletopics', 'label' => '🎯 Table Topics',   'title' => 'Table Topics Generator'],
                        ['id' => 'role',        'label' => '📋 Role Scripts',   'title' => 'Meeting Role Scripts'],
                        ['id' => 'coaching',    'label' => '✨ Coaching',       'title' => 'Member Speech Coaching'],
                    ] as $tab)
                        <button
                            @click="selectMode('{{ $tab['id'] }}')"
                            title="{{ $tab['title'] }}"
                            class="px-2.5 py-1 rounded-xl text-xs font-medium transition-all"
                            :class="activeMode === '{{ $tab['id'] }}' ? 'bg-primary-600 text-white shadow-md shadow-primary-600/25' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
                        >
                            {{ $tab['label'] }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- ============================================================
                 Input Area (Instantaneous Alpine Panels - 0ms Delay)
            ============================================================= --}}
            <div class="flex-shrink-0 px-5 pb-3">

                {{-- Role Script: role selector --}}
                <div x-show="activeMode === 'role'" x-cloak>
                    <div class="mb-2">
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Select Role</label>
                        <select wire:model="selectedRole" class="w-full text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition">
                            @foreach($rolOptions as $role)
                                <option value="{{ $role }}">{{ $role }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button
                        wire:click="generateRoleScript"
                        wire:loading.attr="disabled"
                        class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold shadow-md shadow-primary-600/25 transition-all disabled:opacity-60"
                    >
                        <svg wire:loading wire:target="generateRoleScript" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        <span wire:loading.remove wire:target="generateRoleScript">📋 Generate {{ $selectedRole }} Script</span>
                        <span wire:loading wire:target="generateRoleScript">Generating...</span>
                    </button>
                </div>

                {{-- Coaching: one-click --}}
                <div x-show="activeMode === 'coaching'" x-cloak>
                    <button
                        wire:click="generateMemberCoaching"
                        wire:loading.attr="disabled"
                        class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold shadow-md shadow-primary-600/25 transition-all disabled:opacity-60"
                    >
                        <svg wire:loading wire:target="generateMemberCoaching" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        <span wire:loading.remove wire:target="generateMemberCoaching">✨ {{ $contextName ? "Generate Coaching for $contextName" : 'Generate Coaching Report' }}</span>
                        <span wire:loading wire:target="generateMemberCoaching">Analysing...</span>
                    </button>
                </div>

                {{-- Table Topics: optional theme input --}}
                <div x-show="activeMode === 'tabletopics'" x-cloak>
                    <div class="flex gap-2">
                        <input
                            wire:model="prompt"
                            wire:keydown.enter="generateTableTopics"
                            type="text"
                            placeholder="Theme (optional) e.g. 'Growth Mindset'"
                            class="flex-1 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition placeholder:text-slate-400"
                        />
                        <button
                            wire:click="generateTableTopics"
                            wire:loading.attr="disabled"
                            class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold shadow-md shadow-primary-600/25 transition-all disabled:opacity-60"
                        >
                            <svg wire:loading wire:target="generateTableTopics" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            <span wire:loading.remove wire:target="generateTableTopics">Generate</span>
                            <span wire:loading wire:target="generateTableTopics">...</span>
                        </button>
                    </div>
                </div>

                {{-- Speech Outline --}}
                <div x-show="activeMode === 'outline'" x-cloak>
                    <div class="flex gap-2">
                        <input
                            wire:model="prompt"
                            wire:keydown.enter="generateSpeechOutline"
                            type="text"
                            placeholder="Topic or Project (e.g. 'Ice Breaker')"
                            class="flex-1 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition placeholder:text-slate-400"
                        />
                        <button
                            wire:click="generateSpeechOutline"
                            wire:loading.attr="disabled"
                            class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold shadow-md shadow-primary-600/25 transition-all disabled:opacity-60"
                        >
                            <svg wire:loading wire:target="generateSpeechOutline" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            <span wire:loading.remove wire:target="generateSpeechOutline">Generate</span>
                            <span wire:loading wire:target="generateSpeechOutline">...</span>
                        </button>
                    </div>
                </div>

                {{-- General: free-form chat --}}
                <div x-show="activeMode === 'general'" x-cloak>
                    <div class="flex gap-2">
                        <input
                            wire:model="prompt"
                            wire:keydown.enter="sendPrompt"
                            type="text"
                            placeholder="Ask anything about Toastmasters, speeches, roles..."
                            class="flex-1 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition placeholder:text-slate-400"
                        />
                        <button
                            wire:click="sendPrompt"
                            wire:loading.attr="disabled"
                            class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold shadow-md shadow-primary-600/25 transition-all disabled:opacity-60"
                        >
                            <svg wire:loading wire:target="sendPrompt" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            <svg wire:loading.remove wire:target="sendPrompt" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        </button>
                    </div>
                </div>

                @error('prompt')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- ============================================================
                 Response Area
            ============================================================= --}}
            <div class="flex-1 overflow-y-auto px-5 pb-5">
                @if($loading)
                    <div class="flex flex-col items-center justify-center gap-3 py-16 text-slate-400 dark:text-slate-500">
                        <div class="w-10 h-10 rounded-full bg-primary-600 text-white flex items-center justify-center animate-pulse shadow-md shadow-primary-600/30">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3l1.5 4.5L11 9l-4.5 1.5L5 15l-1.5-4.5L-1 9l4.5-1.5L5 3z"/></svg>
                        </div>
                        <p class="text-sm font-medium">Thinking<span class="animate-pulse">...</span></p>
                    </div>

                @elseif($response)
                    <div class="relative group">
                        {{-- Alpine-powered Copy button (no page refresh) --}}
                        <button
                            @click="copyResponse()"
                            class="absolute top-2 right-2 px-2.5 py-1 rounded-lg bg-white dark:bg-slate-700 text-slate-600 dark:text-slate-200 border border-slate-200 dark:border-slate-600 hover:border-primary-400 hover:text-primary-600 dark:hover:text-primary-400 transition-all text-xs flex items-center gap-1 shadow-sm z-10"
                            title="Copy to clipboard"
                        >
                            <template x-if="!copied">
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    <span>Copy</span>
                                </span>
                            </template>
                            <template x-if="copied">
                                <span class="flex items-center gap-1 text-emerald-600 dark:text-emerald-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span>Copied!</span>
                                </span>
                            </template>
                        </button>

                        <div x-ref="aiResponse" class="ai-response-content prose prose-sm dark:prose-invert max-w-none p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700/50 text-slate-700 dark:text-slate-300 leading-relaxed prose-code:text-primary-600 dark:prose-code:text-primary-400 prose-blockquote:border-primary-500 prose-blockquote:bg-primary-50/50 dark:prose-blockquote:bg-primary-950/30">
                            {!! \Illuminate\Support\Str::markdown($response) !!}
                        </div>

                        <div class="mt-2 flex justify-end">
                            <button wire:click="clearResponse" class="text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                                ✕ Clear
                            </button>
                        </div>
                    </div>

                @else
                    {{-- Empty state with quick prompts --}}
                    <div class="py-6">
                        <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-3">Quick Prompts</p>
                        <div class="flex flex-col gap-2">
                            @foreach([
                                ['label' => '🎤 Generate a speech outline for my Ice Breaker', 'mode' => 'outline', 'prompt' => 'Ice Breaker'],
                                ['label' => '🎯 Table Topics on a Growth Mindset theme',        'mode' => 'tabletopics', 'prompt' => 'Growth Mindset'],
                                ['label' => '📋 Give me the TMOD script',                       'mode' => 'role',        'prompt' => ''],
                                ['label' => '⏱ Timer role guide and timing rules',              'mode' => 'role',        'prompt' => ''],
                                ['label' => '💬 How do I structure a speech evaluation?',       'mode' => 'general',     'prompt' => 'How do I structure a speech evaluation effectively?'],
                            ] as $qp)
                                <button
                                    @click="selectMode('{{ $qp['mode'] }}')"
                                    class="text-left text-sm px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/50 text-slate-600 dark:text-slate-300 hover:border-primary-400 dark:hover:border-primary-500 hover:text-primary-700 dark:hover:text-primary-300 hover:bg-primary-50/50 dark:hover:bg-primary-950/20 transition-all"
                                >
                                    {{ $qp['label'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Footer --}}
            <div class="flex-shrink-0 px-5 py-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <a href="{{ route('ai-assistant.index') }}" wire:navigate class="text-xs text-primary-600 dark:text-primary-400 hover:underline font-medium flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    Open Full AI Hub
                </a>
                <span class="text-xs text-slate-400">100% Free · Local</span>
            </div>
        </div>
    </div>
</div>
