<div
    class="flex-1 min-h-0 flex flex-col overflow-hidden"
    x-data="{
        historyMobileOpen: false,
        activeMode: @entangle('activeMode'),
        copiedId: null,
        showModePresets: false,
        copyText(text, id) {
            if (text) {
                navigator.clipboard.writeText(text);
                this.copiedId = id;
                setTimeout(() => this.copiedId = null, 2000);
            }
        },
        scrollToBottom() {
            this.$nextTick(() => {
                const el = this.$refs.chatContainer;
                if (el) {
                    el.scrollTop = el.scrollHeight;
                }
            });
        }
    }"
    x-init="scrollToBottom()"
    x-on:scroll-chat-to-bottom.window="scrollToBottom()"
>
    {{-- =========================================================================
         Top Header / Status Bar (Compact 1-line flex)
    ========================================================================= --}}
    <div class="flex-shrink-0 flex items-center justify-between gap-3 bg-white dark:bg-slate-900 px-3 sm:px-5 py-2.5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs mb-2 transition-colors">
        <div class="flex items-center gap-2.5">
            {{-- Mobile toggle for history sidebar --}}
            <button
                @click="historyMobileOpen = !historyMobileOpen"
                class="lg:hidden p-1.5 rounded-xl text-slate-500 hover:text-slate-800 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                title="Toggle Conversation History"
                aria-label="Toggle Conversation History"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                </svg>
            </button>

            <div class="w-8 h-8 rounded-xl bg-primary-600 text-white flex items-center justify-center shadow-xs shadow-primary-600/30 flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3l1.5 4.5L11 9l-4.5 1.5L5 15l-1.5-4.5L-1 9l4.5-1.5L5 3zM19 3l1 3 3 1-3 1-1 3-1-3-3-1 3-1 1-3z"/>
                </svg>
            </div>
            <div class="flex items-baseline gap-2">
                <h1 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white leading-tight">AI Assistant</h1>
                <span class="text-[10px] px-1.5 py-0.2 rounded font-semibold bg-primary-100 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300">
                    Chat
                </span>
                <span class="text-xs text-slate-400 hidden sm:inline">— Speech Club Intelligence</span>
            </div>
        </div>

        {{-- Right Controls: New Chat + Engine Badge --}}
        <div class="flex items-center gap-2">
            <button
                wire:click="startNewChat(activeMode)"
                class="flex items-center gap-1 px-3 py-1.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold shadow-xs transition active:scale-95"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>New Chat</span>
            </button>

            <button
                wire:click="refreshEngineStatus"
                title="Refresh engine status"
                class="p-1.5 rounded-xl text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-950/40 transition-colors"
                aria-label="Refresh engine status"
            >
                <svg wire:loading.class="animate-spin text-primary-600" wire:target="refreshEngineStatus" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </button>

            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-semibold
                {{ $ollamaOnline
                    ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50'
                    : 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/50'
                }}"
            >
                <span class="w-2 h-2 rounded-full {{ $ollamaOnline ? 'bg-emerald-500 animate-pulse' : 'bg-amber-500' }}"></span>
                <span class="hidden md:inline">{{ $engineLabel }}</span>
                <span class="md:hidden">{{ $ollamaOnline ? 'Gemma 4' : 'Built-in' }}</span>
            </span>
        </div>
    </div>

    {{-- =========================================================================
         Main Workspace: Fits 100% Screen Height (Zero Window Scroll)
    ========================================================================= --}}
    <div class="flex-1 min-h-0 grid grid-cols-1 lg:grid-cols-4 gap-3 items-stretch overflow-hidden relative">

        {{-- =====================================================================
             LEFT: History Sidebar
        ====================================================================== --}}
        <div
            class="lg:col-span-1 h-full bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs flex flex-col overflow-hidden transition-all"
            :class="historyMobileOpen ? 'fixed inset-y-0 left-0 z-50 w-72 m-2 shadow-2xl flex' : 'hidden lg:flex'"
        >
            {{-- History Header --}}
            <div class="flex-shrink-0 p-3 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider">Chat History</span>
                </div>
                <button
                    @click="historyMobileOpen = false"
                    class="lg:hidden p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-300"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- New Chat Button --}}
            <div class="flex-shrink-0 px-2.5 pt-2.5 pb-2">
                <button
                    wire:click="startNewChat(activeMode)"
                    class="w-full flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-primary-50 dark:bg-primary-950/40 text-primary-700 dark:text-primary-300 hover:bg-primary-100 dark:hover:bg-primary-900/60 border border-primary-200 dark:border-primary-800/50 text-xs font-bold transition"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>+ New Conversation</span>
                </button>
            </div>

            {{-- Search Filter --}}
            <div class="flex-shrink-0 px-2.5 pb-2">
                <div class="relative">
                    <input
                        wire:model.live.debounce.250ms="searchHistory"
                        type="text"
                        placeholder="Search chats..."
                        class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60 text-slate-700 dark:text-slate-200 pl-7 pr-2.5 py-1.5 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 outline-none transition placeholder:text-slate-400"
                    />
                    <svg class="w-3 h-3 absolute left-2.5 top-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            {{-- Grouped Scrollable Conversation List --}}
            <div class="flex-1 min-h-0 overflow-y-auto px-2 pb-3 space-y-3">
                @forelse($groupedConversations as $groupLabel => $items)
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider px-2 py-0.5">
                            {{ $groupLabel }}
                        </p>
                        <div class="space-y-0.5">
                            @foreach($items as $conv)
                                <div
                                    class="group relative flex items-center justify-between px-2.5 py-1.5 rounded-xl text-xs font-medium transition-all cursor-pointer
                                        {{ $activeConversationId === $conv->id
                                            ? 'bg-primary-50 dark:bg-primary-950/50 text-primary-900 dark:text-primary-200 border border-primary-300 dark:border-primary-800/70 font-semibold'
                                            : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60'
                                        }}"
                                    wire:click="selectConversation({{ $conv->id }})"
                                >
                                    <div class="flex items-center gap-1.5 min-w-0 flex-1 pr-1.5">
                                        <span class="text-xs flex-shrink-0">{{ $conv->mode_emoji }}</span>
                                        <span class="truncate block text-xs">{{ $conv->title }}</span>
                                    </div>

                                    <button
                                        type="button"
                                        wire:click.stop="deleteConversation({{ $conv->id }})"
                                        wire:confirm="Delete this conversation?"
                                        title="Delete chat"
                                        class="opacity-0 group-hover:opacity-100 p-1 rounded text-slate-400 hover:text-red-500 transition"
                                    >
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 px-3">
                        <p class="text-xs text-slate-400">No conversations yet</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Mobile backdrop --}}
        <div
            x-show="historyMobileOpen"
            x-transition:enter="transition-opacity ease-out duration-150"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-100"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="historyMobileOpen = false"
            class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs z-40 lg:hidden"
            style="display:none;"
        ></div>

        {{-- =====================================================================
             RIGHT: ChatGPT Canvas (Fits 100% Height, Fixed Input at Bottom)
        ====================================================================== --}}
        <div class="lg:col-span-3 h-full bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs flex flex-col overflow-hidden relative">

            {{-- Chat Header --}}
            <div class="flex-shrink-0 px-4 py-2 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="text-sm">
                        {{ $activeConversation ? $activeConversation->mode_emoji : '💬' }}
                    </span>
                    <div class="min-w-0">
                        <h2 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white truncate max-w-sm sm:max-w-md">
                            {{ $activeConversation ? $activeConversation->title : 'New Chat Session' }}
                        </h2>
                    </div>
                </div>

                {{-- Presets toggle --}}
                <button
                    @click="showModePresets = !showModePresets"
                    class="flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-semibold transition"
                    :class="showModePresets
                        ? 'bg-primary-600 text-white shadow-xs'
                        : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
                >
                    <span>⚡ Presets</span>
                    <svg class="w-3 h-3 transition-transform" :class="showModePresets ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
            </div>

            {{-- Collapsible Presets Drawer --}}
            <div
                x-show="showModePresets"
                x-cloak
                x-collapse
                class="flex-shrink-0 px-4 py-3 bg-slate-50/90 dark:bg-slate-950/50 border-b border-slate-100 dark:border-slate-800"
            >
                <div class="flex gap-1.5 pb-2 overflow-x-auto">
                    @foreach([
                        ['id' => 'general',     'label' => '💬 Q&A'],
                        ['id' => 'outline',     'label' => '🎤 Speech Outline'],
                        ['id' => 'tabletopics', 'label' => '🎯 Table Topics'],
                        ['id' => 'role',        'label' => '📋 Role Scripts'],
                        ['id' => 'coaching',    'label' => '✨ Member Coach'],
                    ] as $m)
                        <button
                            @click="activeMode = '{{ $m['id'] }}'"
                            class="px-2.5 py-1 rounded-xl text-xs font-semibold whitespace-nowrap transition"
                            :class="activeMode === '{{ $m['id'] }}'
                                ? 'bg-primary-600 text-white shadow-xs'
                                : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700'"
                        >
                            {{ $m['label'] }}
                        </button>
                    @endforeach
                </div>

                {{-- Outline preset --}}
                <div x-show="activeMode === 'outline'" x-cloak class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                    <input wire:model="speechTopic" type="text" placeholder="Speech Topic (e.g. Habits)" class="text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 px-3 py-1.5 outline-none focus:ring-1 focus:ring-primary-500"/>
                    <input wire:model="speechProject" type="text" placeholder="Project (e.g. Ice Breaker)" class="text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 px-3 py-1.5 outline-none focus:ring-1 focus:ring-primary-500"/>
                    <button wire:click="generateSpeechOutline" wire:loading.attr="disabled" class="px-3 py-1.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold transition flex items-center justify-center gap-1 shadow-xs">
                        <svg wire:loading wire:target="generateSpeechOutline" class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        <span wire:loading.remove wire:target="generateSpeechOutline">🎤 Outline</span>
                        <span wire:loading wire:target="generateSpeechOutline">Generating...</span>
                    </button>
                </div>

                {{-- Table Topics preset --}}
                <div x-show="activeMode === 'tabletopics'" x-cloak class="flex gap-2">
                    <input wire:model="tableTopicsTheme" type="text" placeholder="Theme (optional, e.g. Resilience)" class="flex-1 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 px-3 py-1.5 outline-none focus:ring-1 focus:ring-primary-500"/>
                    <button wire:click="generateTableTopics" wire:loading.attr="disabled" class="px-3 py-1.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold transition flex items-center gap-1 shadow-xs">
                        <svg wire:loading wire:target="generateTableTopics" class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        <span wire:loading.remove wire:target="generateTableTopics">🎯 Generate</span>
                        <span wire:loading wire:target="generateTableTopics">Generating...</span>
                    </button>
                </div>

                {{-- Role preset --}}
                <div x-show="activeMode === 'role'" x-cloak class="flex gap-2">
                    <select wire:model="selectedRole" class="flex-1 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 px-3 py-1.5 outline-none focus:ring-1 focus:ring-primary-500">
                        @foreach($rolOptions as $r)
                            <option value="{{ $r }}">{{ $r }}</option>
                        @endforeach
                    </select>
                    <button wire:click="generateRoleScript" wire:loading.attr="disabled" class="px-3 py-1.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold transition flex items-center gap-1 shadow-xs">
                        <svg wire:loading wire:target="generateRoleScript" class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        <span wire:loading.remove wire:target="generateRoleScript">📋 Script</span>
                        <span wire:loading wire:target="generateRoleScript">Generating...</span>
                    </button>
                </div>

                {{-- Coaching preset --}}
                <div x-show="activeMode === 'coaching'" x-cloak class="flex gap-2">
                    <select wire:model="selectedUserId" class="flex-1 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 px-3 py-1.5 outline-none focus:ring-1 focus:ring-primary-500">
                        <option value="">— Choose a club member —</option>
                        @foreach($clubMembers as $m)
                            <option value="{{ $m['id'] }}">{{ $m['name'] }}</option>
                        @endforeach
                    </select>
                    <button wire:click="generateCoaching" wire:loading.attr="disabled" class="px-3 py-1.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold transition flex items-center gap-1 shadow-xs">
                        <svg wire:loading wire:target="generateCoaching" class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        <span wire:loading.remove wire:target="generateCoaching">✨ Coaching</span>
                        <span wire:loading wire:target="generateCoaching">Analysing...</span>
                    </button>
                </div>
            </div>

            {{-- =================================================================
                 CHAT TIMELINE (Independent Scrollable Area)
            ================================================================== --}}
            <div
                x-ref="chatContainer"
                class="flex-1 min-h-0 overflow-y-auto px-3 sm:px-6 py-4 space-y-3"
            >
                @if(! $activeConversation || $activeConversation->messages->isEmpty())
                    {{-- Empty State / Welcome Screen --}}
                    <div class="flex flex-col items-center justify-center py-8 px-4 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-primary-50 dark:bg-primary-950/60 text-primary-600 dark:text-primary-400 flex items-center justify-center mb-3 shadow-2xs">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 3l1.5 4.5L11 9l-4.5 1.5L5 15l-1.5-4.5L-1 9l4.5-1.5L5 3zM19 3l1 3 3 1-3 1-1 3-1-3-3-1 3-1 1-3z"/>
                            </svg>
                        </div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-800 dark:text-slate-100 mb-1">How can I help you today?</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mb-6 leading-relaxed">
                            Ask me to outline speeches, generate Table Topics, explain Toastmasters meeting roles, or coach members.
                        </p>

                        {{-- Quick Prompt Suggestions --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 max-w-lg w-full text-left">
                            <button
                                wire:click="$set('prompt', 'Generate a speech outline on the topic: The Power of Stepping Outside Your Comfort Zone'); sendMessage();"
                                class="p-3 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40 hover:border-primary-400 dark:hover:border-primary-500 hover:bg-white dark:hover:bg-slate-800 transition text-left"
                            >
                                <p class="text-xs font-bold text-slate-800 dark:text-slate-200">🎤 Speech Outline</p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 truncate">The Power of Stepping Outside Your Comfort Zone</p>
                            </button>

                            <button
                                wire:click="$set('prompt', 'Create a Table Topics session on Resilience with 5 questions and Word of the Day'); sendMessage();"
                                class="p-3 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40 hover:border-primary-400 dark:hover:border-primary-500 hover:bg-white dark:hover:bg-slate-800 transition text-left"
                            >
                                <p class="text-xs font-bold text-slate-800 dark:text-slate-200">🎯 Table Topics</p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 truncate">Theme: Resilience with Word of the Day</p>
                            </button>

                            <button
                                wire:click="$set('prompt', 'What is the standardized opening script and timing guidelines for the Ah-Counter role?'); sendMessage();"
                                class="p-3 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40 hover:border-primary-400 dark:hover:border-primary-500 hover:bg-white dark:hover:bg-slate-800 transition text-left"
                            >
                                <p class="text-xs font-bold text-slate-800 dark:text-slate-200">📋 Ah-Counter Script</p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 truncate">Opening script and timing rules</p>
                            </button>

                            <button
                                wire:click="$set('prompt', 'How can I make my speech opening more captivating in the first 30 seconds?'); sendMessage();"
                                class="p-3 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40 hover:border-primary-400 dark:hover:border-primary-500 hover:bg-white dark:hover:bg-slate-800 transition text-left"
                            >
                                <p class="text-xs font-bold text-slate-800 dark:text-slate-200">💬 Vocal Variety & Hooks</p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 truncate">Captivate your audience immediately</p>
                            </button>
                        </div>
                    </div>
                @else
                    {{-- Chronological Messages --}}
                    @foreach($activeConversation->messages as $msg)
                        @if($msg->isUser())
                            {{-- =================================================
                                 User Speech Bubble (Compact, Exact Text Size, Right Aligned)
                            ================================================== --}}
                            <div class="flex justify-end items-end gap-2 my-1 pl-8 sm:pl-16">
                                <div class="w-fit max-w-[85%] sm:max-w-[70%] rounded-2xl rounded-br-xs bg-primary-600 text-white px-3.5 py-2 text-xs sm:text-sm leading-relaxed shadow-xs break-words select-text">
                                    {{ trim($msg->content) }}
                                </div>
                                <div class="w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 flex items-center justify-center text-[10px] font-bold flex-shrink-0 select-none mb-0.5">
                                    {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                                </div>
                            </div>
                        @else
                            {{-- =================================================
                                 AI Assistant Speech Bubble (Left Aligned)
                            ================================================== --}}
                            <div class="flex items-start gap-2.5 my-1.5 pr-2 sm:pr-8">
                                <div class="w-7 h-7 rounded-xl bg-primary-50 dark:bg-primary-950/60 border border-primary-200 dark:border-primary-800 text-primary-600 dark:text-primary-400 flex items-center justify-center flex-shrink-0 mt-0.5 shadow-2xs">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3l1.5 4.5L11 9l-4.5 1.5L5 15l-1.5-4.5L-1 9l4.5-1.5L5 3zM19 3l1 3 3 1-3 1-1 3-1-3-3-1 3-1 1-3z"/>
                                    </svg>
                                </div>

                                <div class="w-fit max-w-[95%] sm:max-w-[88%] bg-slate-50/80 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-800 rounded-2xl rounded-tl-xs p-3.5 sm:p-4 shadow-2xs">
                                    {{-- Content formatted via Markdown --}}
                                    <div class="prose prose-slate dark:prose-invert max-w-none text-xs sm:text-sm leading-relaxed prose-p:my-1 prose-headings:my-1.5 prose-ul:my-1 prose-li:my-0.2 prose-blockquote:my-1.5 prose-code:text-primary-600 dark:prose-code:text-primary-400 prose-code:bg-slate-100 dark:prose-code:bg-slate-800 prose-code:px-1 prose-code:py-0.2 prose-code:rounded">
                                        {!! \Illuminate\Support\Str::markdown($msg->content) !!}
                                    </div>

                                    {{-- Footer: Copy button & Engine tag --}}
                                    <div class="mt-2.5 pt-2 border-t border-slate-200/60 dark:border-slate-800 flex items-center justify-between">
                                        <span class="text-[10px] text-slate-400 flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $ollamaOnline ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                            {{ $msg->meta['engine'] ?? $engineLabel }}
                                        </span>

                                        <button
                                            @click="copyText(@js($msg->content), {{ $msg->id }})"
                                            class="flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold text-slate-500 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-white dark:hover:bg-slate-700 transition shadow-2xs border border-slate-200/60 dark:border-slate-700/60"
                                            title="Copy response"
                                        >
                                            <span x-show="copiedId !== {{ $msg->id }}" class="flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                                <span>Copy</span>
                                            </span>
                                            <span x-show="copiedId === {{ $msg->id }}" x-cloak class="flex items-center gap-1 text-emerald-600 dark:text-emerald-400">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                <span>Copied!</span>
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endif

                {{-- Loading Indicator --}}
                @if($loading)
                    <div class="flex items-start gap-2.5 my-1.5 pr-8">
                        <div class="w-7 h-7 rounded-xl bg-primary-50 dark:bg-primary-950/60 border border-primary-200 dark:border-primary-800 text-primary-600 dark:text-primary-400 flex items-center justify-center flex-shrink-0 animate-pulse">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3l1.5 4.5L11 9l-4.5 1.5L5 15l-1.5-4.5L-1 9l4.5-1.5L5 3zM19 3l1 3 3 1-3 1-1 3-1-3-3-1 3-1 1-3z"/>
                            </svg>
                        </div>
                        <div class="bg-slate-50/80 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-800 rounded-2xl px-3.5 py-2 flex items-center gap-2">
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $ollamaOnline ? 'Gemma 4 is thinking' : 'AI is processing' }}</span>
                            <div class="flex gap-1">
                                <div class="w-1.5 h-1.5 rounded-full bg-primary-500 animate-bounce" style="animation-delay: 0ms"></div>
                                <div class="w-1.5 h-1.5 rounded-full bg-primary-400 animate-bounce" style="animation-delay: 150ms"></div>
                                <div class="w-1.5 h-1.5 rounded-full bg-primary-300 animate-bounce" style="animation-delay: 300ms"></div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- =================================================================
                 BOTTOM: Fixed Prompt Input Bar (ALWAYS Visible, Never Scrolls Off)
            ================================================================== --}}
            <div class="flex-shrink-0 p-2.5 sm:p-3 border-t border-slate-100 dark:border-slate-800 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md">
                <form wire:submit="sendMessage" class="relative">
                    <div class="flex items-end gap-2 p-1.5 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-800/60 focus-within:border-primary-500 focus-within:ring-1 focus-within:ring-primary-500/20 focus-within:bg-white dark:focus-within:bg-slate-800 transition-all shadow-2xs">
                        {{-- Expanding textarea --}}
                        <textarea
                            wire:model="prompt"
                            x-on:keydown.enter="if (!$event.shiftKey) { $event.preventDefault(); $wire.sendMessage(); }"
                            x-on:input="$el.style.height = 'auto'; $el.style.height = Math.min($el.scrollHeight, 100) + 'px'"
                            rows="1"
                            placeholder="Message AI Assistant... [Enter to send, Shift+Enter for new line]"
                            class="flex-1 text-xs sm:text-sm bg-transparent text-slate-800 dark:text-slate-100 px-2.5 py-1.5 outline-none resize-none placeholder:text-slate-400 min-h-[34px] max-h-[100px]"
                        ></textarea>

                        {{-- Send button --}}
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="flex-shrink-0 w-8 h-8 rounded-xl bg-primary-600 hover:bg-primary-700 text-white flex items-center justify-center shadow-xs transition active:scale-95 disabled:opacity-50"
                            title="Send Message (Enter)"
                        >
                            <svg wire:loading.remove wire:target="sendMessage" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            <svg wire:loading wire:target="sendMessage" class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="flex items-center justify-between mt-1.5 px-1">
                        <span class="text-[10px] text-slate-400">
                            Press <kbd class="px-1 py-0.2 rounded bg-slate-100 dark:bg-slate-800 font-mono text-[9px]">Enter</kbd> to send, <kbd class="px-1 py-0.2 rounded bg-slate-100 dark:bg-slate-800 font-mono text-[9px]">Shift+Enter</kbd> for newline
                        </span>
                        <span class="text-[10px] text-slate-400 hidden sm:inline">
                            Powered by {{ $engineLabel }}
                        </span>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
