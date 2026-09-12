<div class="space-y-6 max-w-7xl mx-auto"
     x-data="{
         activeTab: 'speeches',
         expandedSpeechId: null,
         filterProject: '',
         toggleSpeech(id) {
             this.expandedSpeechId = this.expandedSpeechId === id ? null : id;
         }
     }">

    {{-- Hero Header --}}
    <div class="bg-gradient-to-br from-primary-600 via-primary-700 to-indigo-900 text-white rounded-3xl p-6 sm:p-8 shadow-xl shadow-primary-900/20 relative overflow-hidden">
        {{-- Background decorative shapes --}}
        <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-white/5 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute right-1/3 -top-10 w-60 h-60 bg-amber-400/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4 sm:gap-5">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-white text-xl sm:text-2xl font-black shadow-inner flex-shrink-0">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <span class="text-xs font-bold uppercase tracking-wider bg-white/15 px-3 py-1 rounded-full backdrop-blur-sm">
                            Speech Progress Portfolio
                        </span>
                        @if($currentClub)
                            <span class="text-xs font-semibold bg-white/10 text-white/90 px-2.5 py-1 rounded-full border border-white/10">
                                {{ $currentClub->name }}
                            </span>
                        @endif
                    </div>
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black tracking-tight text-white mt-1.5">
                        {{ $user->name }}
                    </h1>
                    <p class="text-xs sm:text-sm text-primary-100 font-medium mt-1">
                        Track your speaking journey, unlock Pathways milestone achievements, and review peer evaluation feedback.
                    </p>
                </div>
            </div>

            {{-- Quick Progress Pill --}}
            <div class="flex sm:flex-col items-center sm:items-end justify-between sm:justify-center gap-2 bg-white/10 backdrop-blur-md border border-white/15 p-4 rounded-2xl flex-shrink-0">
                <span class="text-xs font-semibold text-primary-100">Milestone Badges</span>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-black text-amber-300">{{ $stats['unlocked_badges'] }}</span>
                    <span class="text-xs text-primary-200 font-bold">/ {{ $stats['total_badges'] }}</span>
                </div>
                <div class="w-28 sm:w-32 bg-white/20 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-amber-300 h-1.5 rounded-full transition-all duration-500"
                         style="width: {{ $stats['total_badges'] > 0 ? ($stats['unlocked_badges'] / $stats['total_badges'] * 100) : 0 }}%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Cards Grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Speeches --}}
        <div @click="activeTab = 'speeches'"
             class="p-5 rounded-3xl border transition-all cursor-pointer select-none group"
             :class="activeTab === 'speeches' ? 'bg-primary-50 dark:bg-primary-950/40 border-primary-300 dark:border-primary-800 shadow-sm ring-2 ring-primary-500/20' : 'bg-white dark:bg-slate-900 border-slate-200/80 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700'">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-primary-100 dark:bg-primary-900/50 text-primary-600 dark:text-primary-400 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z"/></svg>
                </div>
                <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-md text-primary-700 dark:text-primary-300 bg-primary-100/60 dark:bg-primary-900/60">Delivered</span>
            </div>
            <p class="text-3xl font-black text-slate-900 dark:text-white">{{ $stats['speeches'] }}</p>
            <p class="text-xs font-bold text-slate-600 dark:text-slate-300 mt-1">Prepared Speeches</p>
            <p class="text-[11px] text-slate-400 dark:text-slate-500">With written peer evaluations</p>
        </div>

        {{-- Table Topics --}}
        <div @click="activeTab = 'table_topics'"
             class="p-5 rounded-3xl border transition-all cursor-pointer select-none group"
             :class="activeTab === 'table_topics' ? 'bg-purple-50 dark:bg-purple-950/40 border-purple-300 dark:border-purple-800 shadow-sm ring-2 ring-purple-500/20' : 'bg-white dark:bg-slate-900 border-slate-200/80 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700'">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-900/50 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
                <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-md text-purple-700 dark:text-purple-300 bg-purple-100/60 dark:bg-purple-900/60">Impromptu</span>
            </div>
            <p class="text-3xl font-black text-slate-900 dark:text-white">{{ $stats['table_topics'] }}</p>
            <p class="text-xs font-bold text-slate-600 dark:text-slate-300 mt-1">Table Topics</p>
            <p class="text-[11px] text-slate-400 dark:text-slate-500">1-2 min speaking prompts</p>
        </div>

        {{-- Evaluations Given --}}
        <div @click="activeTab = 'evaluations_given'"
             class="p-5 rounded-3xl border transition-all cursor-pointer select-none group"
             :class="activeTab === 'evaluations_given' ? 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-300 dark:border-emerald-800 shadow-sm ring-2 ring-emerald-500/20' : 'bg-white dark:bg-slate-900 border-slate-200/80 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700'">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-md text-emerald-700 dark:text-emerald-300 bg-emerald-100/60 dark:bg-emerald-900/60">Evaluator</span>
            </div>
            <p class="text-3xl font-black text-slate-900 dark:text-white">{{ $stats['evaluations_given'] }}</p>
            <p class="text-xs font-bold text-slate-600 dark:text-slate-300 mt-1">Evaluations Given</p>
            <p class="text-[11px] text-slate-400 dark:text-slate-500">Constructive feedback delivered</p>
        </div>

        {{-- Meeting Roles --}}
        <div @click="activeTab = 'roles'"
             class="p-5 rounded-3xl border transition-all cursor-pointer select-none group"
             :class="activeTab === 'roles' ? 'bg-blue-50 dark:bg-blue-950/40 border-blue-300 dark:border-blue-800 shadow-sm ring-2 ring-blue-500/20' : 'bg-white dark:bg-slate-900 border-slate-200/80 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700'">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-md text-blue-700 dark:text-blue-300 bg-blue-100/60 dark:bg-blue-900/60">Leadership</span>
            </div>
            <p class="text-3xl font-black text-slate-900 dark:text-white">{{ $stats['roles_served'] }}</p>
            <p class="text-xs font-bold text-slate-600 dark:text-slate-300 mt-1">Meeting Roles</p>
            <p class="text-[11px] text-slate-400 dark:text-slate-500">Toastmaster, Timer, Hark Master…</p>
        </div>
    </div>

    {{-- Milestone Badges Carousel / Showcase --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 sm:p-7 transition-colors">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-2.5">
                <span class="text-lg">🏆</span>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Milestone & Achievement Badges</h3>
            </div>
            <span class="text-xs font-semibold text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-950/60 px-3 py-1 rounded-full border border-primary-200/60 dark:border-primary-800/60">
                {{ $stats['unlocked_badges'] }} Unlocked
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($badges as $b)
            <div class="p-4 rounded-2xl border transition-all flex flex-col justify-between {{ $b['unlocked'] ? 'bg-gradient-to-br from-amber-50/50 via-white to-amber-50/20 dark:from-amber-950/20 dark:via-slate-900 dark:to-slate-900 border-amber-300/80 dark:border-amber-700/60 shadow-sm' : 'bg-slate-50/60 dark:bg-slate-950/40 border-slate-200/60 dark:border-slate-800 opacity-65' }}">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md {{ $b['unlocked'] ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300' : 'bg-slate-200/80 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                            {{ $b['category'] }}
                        </span>
                        @if($b['unlocked'])
                            <span class="text-xs text-amber-500 font-bold flex items-center gap-1">
                                ⭐ <span>Unlocked</span>
                            </span>
                        @else
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        @endif
                    </div>
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">{{ $b['name'] }}</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">{{ $b['description'] }}</p>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <div class="flex items-center justify-between text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1.5">
                        <span>Status</span>
                        <span class="font-bold {{ $b['unlocked'] ? 'text-amber-600 dark:text-amber-400' : '' }}">{{ $b['progress'] }} / {{ $b['target'] }}</span>
                    </div>
                    <div class="w-full bg-slate-200/80 dark:bg-slate-800 rounded-full h-1.5 overflow-hidden">
                        <div class="{{ $b['unlocked'] ? 'bg-amber-500' : 'bg-slate-400 dark:bg-slate-600' }} h-1.5 rounded-full transition-all duration-500"
                             style="width: {{ min(100, ($b['progress'] / $b['target']) * 100) }}%"></div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Main Tabs Navigation --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden transition-colors">
        <div class="border-b border-slate-100 dark:border-slate-800 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            {{-- Tabs --}}
            <div class="inline-flex items-center gap-1.5 bg-slate-100 dark:bg-slate-800 p-1.5 rounded-2xl flex-wrap">
                <button @click="activeTab = 'speeches'" type="button"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all"
                        :class="activeTab === 'speeches' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white'">
                    Speeches & Feedback ({{ $speeches->count() }})
                </button>
                <button @click="activeTab = 'evaluations_given'" type="button"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all"
                        :class="activeTab === 'evaluations_given' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white'">
                    Evaluations Given ({{ $evaluationsGiven->count() }})
                </button>
                <button @click="activeTab = 'table_topics'" type="button"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all"
                        :class="activeTab === 'table_topics' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white'">
                    Table Topics ({{ $tableTopics->count() }})
                </button>
                <button @click="activeTab = 'roles'" type="button"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all"
                        :class="activeTab === 'roles' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white'">
                    Roles Served ({{ $rolesServed->count() }})
                </button>
            </div>
        </div>

        <div class="p-6 sm:p-8">

            {{-- ========================================================== --}}
            {{-- Tab 1: Speeches & Feedback Received --}}
            {{-- ========================================================== --}}
            <div x-show="activeTab === 'speeches'" class="space-y-6">
                @if($speeches->isEmpty())
                    <div class="text-center py-12 max-w-md mx-auto space-y-3">
                        <div class="w-14 h-14 rounded-2xl bg-primary-50 dark:bg-primary-950/60 text-primary-600 dark:text-primary-400 flex items-center justify-center mx-auto text-2xl font-bold">
                            🎙️
                        </div>
                        <h4 class="text-base font-bold text-slate-900 dark:text-white">No speeches delivered yet</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                            Sign up as a prepared speaker in an upcoming meeting to deliver your first project speech and receive peer feedback!
                        </p>
                        <a href="{{ route('meetings.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-semibold shadow-sm transition-all">
                            View Upcoming Meetings →
                        </a>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($speeches as $speech)
                        @php
                            $eval = $speech->evaluation;
                            $hasFeedback = $eval && ! empty(trim((string) $eval->notes));
                            $ah = $speech->ah_log;
                            $grammar = $speech->grammar_log;
                        @endphp
                        <div class="p-6 bg-slate-50/70 dark:bg-slate-800/40 rounded-3xl border border-slate-200/80 dark:border-slate-800 transition-all hover:border-primary-200 dark:hover:border-primary-900/50 space-y-4">
                            {{-- Header: Project badge, Meeting number, Duration --}}
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-slate-200/60 dark:border-slate-800/80">
                                <div class="flex items-center gap-2.5 flex-wrap">
                                    <span class="text-xs font-extrabold px-3 py-1 rounded-full bg-primary-100 dark:bg-primary-950 text-primary-700 dark:text-primary-300 border border-primary-200 dark:border-primary-800/60">
                                        Slot #{{ $speech->slot }}
                                    </span>
                                    @if($speech->projectModel)
                                        <span class="text-xs font-bold px-2.5 py-1 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700 shadow-2xs">
                                            {{ $speech->projectModel->levelBadge() }}: {{ $speech->projectModel->name }}
                                        </span>
                                    @elseif($speech->project)
                                        <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                            {{ $speech->project }}
                                        </span>
                                    @endif
                                </div>

                                <div class="flex items-center gap-3">
                                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        {{ $speech->duration ?: '5-7 mins' }}
                                    </span>
                                    <a href="{{ route('meetings.show', $speech->meeting) }}" class="text-xs font-bold text-primary-600 dark:text-primary-400 hover:underline flex items-center gap-1">
                                        Meeting #{{ $speech->meeting->meeting_number }} ({{ $speech->meeting->meeting_date->format('d M Y') }}) →
                                    </a>
                                </div>
                            </div>

                            {{-- Title / Topic --}}
                            <div>
                                <h4 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">
                                    @if($speech->topic)
                                        &ldquo;{{ $speech->topic }}&rdquo;
                                    @else
                                        <span class="text-slate-400 dark:text-slate-500 italic">No topic title recorded</span>
                                    @endif
                                </h4>
                                @if($speech->speech_type)
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $speech->speech_type }}</p>
                                @endif
                            </div>

                            {{-- Peer Evaluation Feedback Card --}}
                            <div class="bg-white dark:bg-slate-900 rounded-2xl border {{ $hasFeedback ? 'border-emerald-200 dark:border-emerald-900/50 bg-gradient-to-br from-emerald-50/30 to-transparent dark:from-emerald-950/20' : 'border-slate-200 dark:border-slate-800' }} p-4 sm:p-5">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 font-bold flex items-center justify-center text-xs">
                                            {{ $eval?->evaluator ? strtoupper(substr($eval->evaluator->name, 0, 1)) : '?' }}
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Speech Evaluator</span>
                                                @if($hasFeedback)
                                                    <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-200">
                                                        Feedback Recorded
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">
                                                {{ $eval?->evaluator?->name ?? 'Evaluator unassigned' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                @if($hasFeedback)
                                    <div class="mt-2 text-xs sm:text-sm text-slate-700 dark:text-slate-200 bg-emerald-50/50 dark:bg-emerald-950/30 p-4 rounded-xl border border-emerald-100 dark:border-emerald-900/40 whitespace-pre-line leading-relaxed font-medium">
                                        {{ $eval->notes }}
                                    </div>
                                @else
                                    <p class="text-xs text-slate-400 dark:text-slate-500 italic mt-1">
                                        Written evaluation feedback has not been recorded yet for this speech.
                                    </p>
                                @endif
                            </div>

                            {{-- Facilitator Feedback Metrics (Ah-Counter & Grammarian logs for this speech) --}}
                            @if($ah || $grammar)
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                                {{-- Ah-Counter Metrics --}}
                                @if($ah)
                                <div class="bg-white dark:bg-slate-900 p-3.5 rounded-2xl border border-slate-200/80 dark:border-slate-800 flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-xs">
                                            🎙️
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900 dark:text-white">Ah-Counter Report</p>
                                            <p class="text-[10px] text-slate-400">Ah: {{ $ah->ah_count }}, Um: {{ $ah->um_count }}, Like: {{ $ah->like_count }}, Repeats: {{ $ah->repeats_count }}</p>
                                        </div>
                                    </div>
                                    <span class="text-xs font-black px-2.5 py-1 rounded-xl {{ $ah->totalFillers() > 0 ? 'bg-rose-50 dark:bg-rose-950/50 text-rose-600' : 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600' }}">
                                        {{ $ah->totalFillers() }} Fillers
                                    </span>
                                </div>
                                @endif

                                {{-- Grammarian Metrics --}}
                                @if($grammar)
                                <div class="bg-white dark:bg-slate-900 p-3.5 rounded-2xl border border-slate-200/80 dark:border-slate-800 flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xs">
                                            ✨
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900 dark:text-white">Grammarian Vocabulary</p>
                                            <p class="text-[10px] text-slate-400">Word of the Day usage: <strong class="text-emerald-600">{{ $grammar->word_of_day_count }}x</strong></p>
                                        </div>
                                    </div>
                                    @if($grammar->good_phrases)
                                        <span class="text-[11px] font-semibold text-slate-600 dark:text-slate-300 italic truncate max-w-[160px]" title="{{ $grammar->good_phrases }}">
                                            &ldquo;{{ $grammar->good_phrases }}&rdquo;
                                        </span>
                                    @endif
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- ========================================================== --}}
            {{-- Tab 2: Evaluations Given (As Evaluator) --}}
            {{-- ========================================================== --}}
            <div x-show="activeTab === 'evaluations_given'" class="space-y-4">
                @if($evaluationsGiven->isEmpty())
                    <div class="text-center py-12 max-w-md mx-auto space-y-3">
                        <div class="w-14 h-14 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto text-2xl font-bold">
                            ✍️
                        </div>
                        <h4 class="text-base font-bold text-slate-900 dark:text-white">No evaluations given yet</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                            Volunteering as a Speech Evaluator is one of the best ways to sharpen your listening and analytical leadership skills.
                        </p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($evaluationsGiven as $evaluation)
                        <div class="p-5 bg-slate-50/70 dark:bg-slate-800/40 rounded-3xl border border-slate-200/80 dark:border-slate-800 space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pb-3 border-b border-slate-200/60 dark:border-slate-800/80">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-primary-100 dark:bg-primary-950 text-primary-700 dark:text-primary-300 font-bold flex items-center justify-center text-xs">
                                        {{ strtoupper(substr($evaluation->speaker->user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold text-slate-900 dark:text-white">{{ $evaluation->speaker->user->name }}</span>
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300">
                                                Evaluated by You
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-slate-400">
                                            @if($evaluation->speaker->projectModel)
                                                [{{ $evaluation->speaker->projectModel->levelBadge() }}] {{ $evaluation->speaker->projectModel->name }}
                                            @else
                                                {{ $evaluation->speaker->project ?: 'Prepared Speech' }}
                                            @endif
                                            @if($evaluation->speaker->topic) &bull; &ldquo;{{ $evaluation->speaker->topic }}&rdquo; @endif
                                        </p>
                                    </div>
                                </div>
                                <a href="{{ route('meetings.show', $evaluation->meeting) }}" class="text-xs font-semibold text-primary-600 dark:text-primary-400 hover:underline">
                                    Meeting #{{ $evaluation->meeting->meeting_number }} ({{ $evaluation->meeting->meeting_date->format('d M Y') }}) →
                                </a>
                            </div>

                            @if($evaluation->notes)
                                <div class="text-xs sm:text-sm text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 whitespace-pre-line leading-relaxed font-medium">
                                    {{ $evaluation->notes }}
                                </div>
                            @else
                                <p class="text-xs text-slate-400 italic">No written feedback notes recorded.</p>
                            @endif
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- ========================================================== --}}
            {{-- Tab 3: Table Topics Journey --}}
            {{-- ========================================================== --}}
            <div x-show="activeTab === 'table_topics'" class="space-y-4">
                @if($tableTopics->isEmpty())
                    <div class="text-center py-12 max-w-md mx-auto space-y-3">
                        <div class="w-14 h-14 rounded-2xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center mx-auto text-2xl font-bold">
                            💡
                        </div>
                        <h4 class="text-base font-bold text-slate-900 dark:text-white">No Table Topics speeches yet</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                            Jump in for 1-to-2 minute impromptu speaking challenges at any meeting to sharpen your on-the-spot thinking!
                        </p>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($tableTopics as $ttm)
                        <div class="p-5 bg-slate-50/70 dark:bg-slate-800/40 rounded-3xl border border-slate-200/80 dark:border-slate-800 space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-purple-600 dark:text-purple-400 bg-purple-100 dark:bg-purple-950/60 px-2.5 py-0.5 rounded-full">
                                    Slot #{{ $ttm->slot }}
                                </span>
                                <a href="{{ route('meetings.show', $ttm->meeting) }}" class="font-semibold text-slate-500 hover:text-primary-600 dark:text-slate-400">
                                    Meeting #{{ $ttm->meeting->meeting_number }} &bull; {{ $ttm->meeting->meeting_date->format('d M Y') }} →
                                </a>
                            </div>
                            <p class="text-sm font-bold text-slate-900 dark:text-white mt-1">
                                @if($ttm->topic)
                                    &ldquo;{{ $ttm->topic }}&rdquo;
                                @else
                                    <span class="text-slate-400 italic">Impromptu speaking challenge</span>
                                @endif
                            </p>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- ========================================================== --}}
            {{-- Tab 4: Meeting Roles Served --}}
            {{-- ========================================================== --}}
            <div x-show="activeTab === 'roles'" class="space-y-4">
                @if($rolesServed->isEmpty())
                    <div class="text-center py-12 max-w-md mx-auto space-y-3">
                        <div class="w-14 h-14 rounded-2xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center mx-auto text-2xl font-bold">
                            🤝
                        </div>
                        <h4 class="text-base font-bold text-slate-900 dark:text-white">No meeting roles served yet</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                            Sign up for roles like Timer, Ah-Counter, Grammarian, or Toastmaster of the Day to develop leadership skills.
                        </p>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($rolesServed as $role)
                        <div class="p-4 bg-slate-50/70 dark:bg-slate-800/40 rounded-2xl border border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
                            <div>
                                <h5 class="text-sm font-bold text-slate-900 dark:text-white">{{ $role->roleType->name }}</h5>
                                <a href="{{ route('meetings.show', $role->meeting) }}" class="text-xs text-slate-500 hover:text-primary-600 dark:text-slate-400 mt-0.5 block">
                                    Meeting #{{ $role->meeting->meeting_number }} &bull; {{ $role->meeting->meeting_date->format('d M Y') }}
                                </a>
                            </div>
                            <span class="text-lg">⭐</span>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>

</div>
