<div class="max-w-4xl mx-auto space-y-6" x-data="{
    speakers: $wire.entangle('speakers'),
    ttmSpeakers: $wire.entangle('ttmSpeakers'),
    evaluations: $wire.entangle('evaluations'),
    members: $wire.entangle('membersList'),
    projectsList: {{ Js::from($projects->map(fn($p) => [
        'id' => $p->id,
        'name' => $p->name,
        'badge' => $p->levelBadge(),
        'duration' => $p->formattedTiming(),
        'speech_type' => $p->track ?: 'Speech Project',
        'track' => $p->track ?: 'General',
    ])) }},
    onSelectProject(index, projectId) {
        const p = this.projectsList.find(item => String(item.id) === String(projectId));
        if (p) {
            this.speakers[index].project_id = p.id;
            this.speakers[index].project = p.name;
            this.speakers[index].speech_type = p.speech_type;
            this.speakers[index].duration = p.duration;
        } else {
            this.speakers[index].project_id = '';
        }
    },
    addSpeaker() {
        this.speakers.push({ user_id: '', project_id: '', topic: '', speech_type: '', project: '', duration: '' });
    },
    removeSpeaker(index) {
        if (this.speakers.length > 1) {
            this.speakers.splice(index, 1);
        }
    },
    addTtmSpeaker() {
        this.ttmSpeakers.push({ user_id: '', topic: '', duration: '' });
    },
    removeTtmSpeaker(index) {
        if (this.ttmSpeakers.length > 1) {
            this.ttmSpeakers.splice(index, 1);
        }
    },
    addEvaluation() {
        this.evaluations.push({ speaker_index: 0, evaluator_user_id: '' });
    },
    removeEvaluation(index) {
        if (this.evaluations.length > 1) {
            this.evaluations.splice(index, 1);
        }
    }
}">

    {{-- Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('meetings.index') }}" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-white dark:hover:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Create Meeting</h1>
            @if(!$isGlobal && $currentClub)
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                Creating session for <span class="font-semibold text-primary-600 dark:text-primary-400">{{ $currentClub->name }}</span>
            </p>
            @endif
        </div>
    </div>

    <form wire:submit="save" class="space-y-6">

        {{-- Club Selection for Global Users (Admin / Super Admin) --}}
        @if($isGlobal)
        <div class="bg-primary-50/70 dark:bg-primary-950/40 border border-primary-100 dark:border-primary-900/50 rounded-3xl p-6 sm:p-7 shadow-sm transition-colors">
            <label class="block text-sm font-bold text-slate-900 dark:text-white mb-1">Select Club for Meeting <span class="text-rose-500">*</span></label>
            <p class="text-xs text-slate-600 dark:text-slate-400 mb-3">As an administrative user, select the club to conduct and record this meeting for.</p>
            <select wire:model.live="selectedClubId"
                    class="w-full sm:w-80 px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm font-semibold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 shadow-sm">
                @foreach($accessibleClubs as $club)
                    <option value="{{ $club->id }}">{{ $club->name }} ({{ $club->code }})</option>
                @endforeach
            </select>
            @error('selectedClubId') <p class="mt-2 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
        </div>
        @endif

        {{-- Validation errors summary --}}
        @if($errors->any())
        <div class="bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/50 rounded-2xl p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-rose-700 dark:text-rose-400 mb-2">Please fix the following errors:</p>
            <ul class="list-disc list-inside text-xs sm:text-sm text-rose-600 dark:text-rose-300 space-y-1 font-medium">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- ================================================================ --}}
        {{-- Meeting Information --}}
        {{-- ================================================================ --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 sm:p-8 transition-colors">
            <h2 class="text-base font-bold text-slate-900 dark:text-white mb-6 pb-3 border-b border-slate-100 dark:border-slate-800">Meeting Information</h2>
            <div class="space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Meeting Date <span class="text-rose-500">*</span></label>
                        <input wire:model="meeting_date" type="date"
                               class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 @error('meeting_date') border-rose-300 @enderror">
                        @error('meeting_date') <p class="mt-1.5 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Meeting Number <span class="text-rose-500">*</span></label>
                        <input wire:model="meeting_number" type="number" min="1"
                               class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 @error('meeting_number') border-rose-300 @enderror">
                        @error('meeting_number') <p class="mt-1.5 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Theme</label>
                    <input wire:model="theme" type="text" placeholder="e.g. Master the Art of Storytelling"
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Start Time</label>
                        <input wire:model="start_time" type="time"
                               class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        @error('start_time') <p class="mt-1.5 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">End Time</label>
                        <input wire:model="end_time" type="time"
                               class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        @error('end_time') <p class="mt-1.5 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Venue</label>
                        <input wire:model="venue" type="text" placeholder="e.g. Main Auditorium / Zoom"
                               class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Status</label>
                        <select wire:model="status" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 font-medium">
                            <option value="draft">Draft</option>
                            <option value="scheduled">Scheduled</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Meeting Agenda & Announcements</label>
                    <x-rich-text-editor wire:model="notes" placeholder="Planned agenda items, meeting notes, theme details, or announcements for members…" />
                </div>
            </div>
        </div>

        {{-- ================================================================ --}}
        {{-- Word of the Day (Grammarian) --}}
        {{-- ================================================================ --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 sm:p-8 transition-colors">
            <div class="flex items-center gap-3 mb-6 pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="w-9 h-9 rounded-2xl bg-amber-500/10 dark:bg-amber-400/10 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Word of the Day & Vocabulary</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Optional vocabulary challenge for the meeting, introduced by the Grammarian and printed on the agenda.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-5">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Word of the Day</label>
                    <input wire:model="word_of_the_day" type="text" placeholder="e.g. Resilient, Eloquent, Serendipity"
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500 font-semibold">
                    @error('word_of_the_day') <p class="mt-1.5 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Part of Speech</label>
                    <input wire:model="word_part_of_speech" type="text" placeholder="e.g. Adjective, Noun, Verb"
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500">
                    @error('word_part_of_speech') <p class="mt-1.5 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Definition</label>
                    <textarea wire:model="word_definition" rows="2" placeholder="e.g. Able to withstand or recover quickly from difficult conditions."
                              class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500"></textarea>
                    @error('word_definition') <p class="mt-1.5 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Example Sentence</label>
                    <textarea wire:model="word_example_sentence" rows="2" placeholder="e.g. Our community showed a resilient spirit in the face of unexpected changes."
                              class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500"></textarea>
                    @error('word_example_sentence') <p class="mt-1.5 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- ================================================================ --}}
        {{-- Fixed Meeting Roles --}}
        {{-- ================================================================ --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 sm:p-8 transition-colors" wire:key="fixed-roles-card-{{ $selectedClubId }}">
            <h2 class="text-base font-bold text-slate-900 dark:text-white mb-6 pb-3 border-b border-slate-100 dark:border-slate-800">Meeting Roles</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                @foreach($roleTypes as $roleType)
                <div wire:key="role-type-{{ $roleType->id }}-{{ $selectedClubId }}">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ $roleType->name }}</label>
                    
                    {{-- Searchable Select Combobox --}}
                    <div class="relative" x-data="{
                        open: false,
                        search: '',
                        selectedId: @entangle('roleAssignments.' . $roleType->id),
                        get filtered() {
                            if (!this.search.trim()) return members;
                            const q = this.search.toLowerCase();
                            return members.filter(m => m.name.toLowerCase().includes(q));
                        },
                        get selectedName() {
                            const found = members.find(m => String(m.id) === String(this.selectedId));
                            return found ? found.name : '— Select Member —';
                        }
                    }" @click.outside="open = false; search = ''">
                        <button type="button" @click="open = !open; if(open) $nextTick(() => $refs.searchInp?.focus())"
                                class="w-full flex items-center justify-between px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 text-left transition-all">
                            <span :class="selectedId ? 'text-slate-900 dark:text-white font-medium' : 'text-slate-400 dark:text-slate-500'" x-text="selectedName" class="truncate"></span>
                            <div class="flex items-center gap-1.5 ml-2 flex-shrink-0">
                                <span x-show="selectedId" @click.stop="selectedId = ''" class="text-slate-400 hover:text-rose-500 p-0.5 rounded-lg transition-colors cursor-pointer" title="Clear selection">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </span>
                                <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </button>

                        <div x-show="open"
                             x-cloak
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute z-50 mt-1.5 w-full bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xl overflow-hidden max-h-64 flex flex-col"
                             style="display: none;">
                            <div class="p-2.5 border-b border-slate-100 dark:border-slate-700/80 bg-slate-50/80 dark:bg-slate-900/50">
                                <div class="relative">
                                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    <input x-ref="searchInp" x-model="search" type="text" placeholder="Type to search member…"
                                           class="w-full pl-9 pr-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                                </div>
                            </div>
                            <div class="overflow-y-auto p-1.5 space-y-0.5 max-h-48">
                                <button type="button" @click="selectedId = ''; open = false; search = ''"
                                        class="w-full text-left px-3 py-2 rounded-xl text-xs text-slate-400 dark:text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700/60 transition-colors">
                                    — None / Clear Selection —
                                </button>
                                <template x-for="m in filtered" :key="m.id">
                                    <button type="button" @click="selectedId = m.id; open = false; search = ''"
                                            class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium transition-colors"
                                            :class="String(selectedId) === String(m.id) ? 'bg-primary-50 dark:bg-primary-950/60 text-primary-600 dark:text-primary-400 font-semibold' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/60'">
                                        <span x-text="m.name" class="truncate"></span>
                                        <svg x-show="String(selectedId) === String(m.id)" class="w-4 h-4 text-primary-600 dark:text-primary-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                </template>
                                <div x-show="filtered.length === 0" class="py-4 text-center text-xs text-slate-400 dark:text-slate-500">
                                    No matching members
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ================================================================ --}}
        {{-- Prepared Speakers (100% Client-Side Alpine.js) --}}
        {{-- ================================================================ --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 sm:p-8 transition-colors">
            <div class="flex items-center justify-between mb-6 pb-3 border-b border-slate-100 dark:border-slate-800">
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Prepared Speakers</h2>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-primary-50 dark:bg-primary-950/60 text-primary-600 dark:text-primary-400">Dynamic</span>
            </div>
            <div class="space-y-4">
                <template x-for="(speaker, index) in speakers" :key="index">
                    <div class="flex gap-3 items-start p-4 sm:p-5 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-200/60 dark:border-slate-700/50">
                        <div class="w-8 h-8 bg-primary-100 dark:bg-primary-950/80 text-primary-600 dark:text-primary-400 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5 font-bold text-xs">
                            <span x-text="index + 1"></span>
                        </div>
                        <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Speaker</label>
                                
                                {{-- Searchable Member Combobox for Speaker --}}
                                <div class="relative" x-data="{
                                    open: false,
                                    search: '',
                                    get filtered() {
                                        if (!this.search.trim()) return members;
                                        const q = this.search.toLowerCase();
                                        return members.filter(m => m.name.toLowerCase().includes(q));
                                    },
                                    get selectedName() {
                                        const found = members.find(m => String(m.id) === String(speaker.user_id));
                                        return found ? found.name : '— Select Member —';
                                    }
                                }" @click.outside="open = false; search = ''">
                                    <button type="button" @click="open = !open; if(open) $nextTick(() => $refs.searchInp?.focus())"
                                            class="w-full flex items-center justify-between px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 text-left transition-all">
                                        <span :class="speaker.user_id ? 'text-slate-900 dark:text-white font-medium' : 'text-slate-400 dark:text-slate-500'" x-text="selectedName" class="truncate"></span>
                                        <div class="flex items-center gap-1.5 ml-2 flex-shrink-0">
                                            <span x-show="speaker.user_id" @click.stop="speaker.user_id = ''" class="text-slate-400 hover:text-rose-500 p-0.5 rounded-lg transition-colors cursor-pointer" title="Clear selection">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </span>
                                            <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        </div>
                                    </button>
                                    <div x-show="open"
                                         x-cloak
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="opacity-0 scale-95"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="opacity-100 scale-100"
                                         x-transition:leave-end="opacity-0 scale-95"
                                         class="absolute z-50 mt-1.5 w-full bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xl overflow-hidden max-h-64 flex flex-col"
                                         style="display: none;">
                                        <div class="p-2.5 border-b border-slate-100 dark:border-slate-700/80 bg-slate-50/80 dark:bg-slate-900/50">
                                            <div class="relative">
                                                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                                <input x-ref="searchInp" x-model="search" type="text" placeholder="Type to search member…"
                                                       class="w-full pl-9 pr-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                                            </div>
                                        </div>
                                        <div class="overflow-y-auto p-1.5 space-y-0.5 max-h-48">
                                            <button type="button" @click="speaker.user_id = ''; open = false; search = ''"
                                                    class="w-full text-left px-3 py-2 rounded-xl text-xs text-slate-400 dark:text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700/60 transition-colors">
                                                — None / Clear Selection —
                                            </button>
                                            <template x-for="m in filtered" :key="m.id">
                                                <button type="button" @click="speaker.user_id = m.id; open = false; search = ''"
                                                        class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium transition-colors"
                                                        :class="String(speaker.user_id) === String(m.id) ? 'bg-primary-50 dark:bg-primary-950/60 text-primary-600 dark:text-primary-400 font-semibold' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/60'">
                                                    <span x-text="m.name" class="truncate"></span>
                                                    <svg x-show="String(speaker.user_id) === String(m.id)" class="w-4 h-4 text-primary-600 dark:text-primary-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                </button>
                                            </template>
                                            <div x-show="filtered.length === 0" class="py-4 text-center text-xs text-slate-400 dark:text-slate-500">
                                                No matching members
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Topic</label>
                                <input x-model="speaker.topic" type="text" placeholder="Speech topic"
                                       class="w-full px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Speech Project (Auto-fills timing & track)</label>
                                
                                {{-- Searchable Project Combobox --}}
                                <div class="relative" x-data="{
                                    open: false,
                                    search: '',
                                    get filtered() {
                                        if (!this.search.trim()) return projectsList;
                                        const q = this.search.toLowerCase();
                                        return projectsList.filter(p => 
                                            p.name.toLowerCase().includes(q) || 
                                            (p.track && p.track.toLowerCase().includes(q)) || 
                                            (p.badge && p.badge.toLowerCase().includes(q)) ||
                                            (p.duration && p.duration.toLowerCase().includes(q))
                                        );
                                    },
                                    get selectedProject() {
                                        return projectsList.find(p => String(p.id) === String(speaker.project_id));
                                    },
                                    get displayText() {
                                        const p = this.selectedProject;
                                        if (!p) return '— Select from Speech Catalog (Searchable) —';
                                        return `[${p.badge}] ${p.name} (${p.duration})`;
                                    },
                                    choose(id) {
                                        this.open = false;
                                        this.search = '';
                                        onSelectProject(index, id);
                                    }
                                }" @click.outside="open = false; search = ''">
                                    <button type="button" @click="open = !open; if(open) $nextTick(() => $refs.searchProjInp?.focus())"
                                            class="w-full flex items-center justify-between px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 text-left transition-all">
                                        <span :class="speaker.project_id ? 'text-slate-900 dark:text-white font-medium' : 'text-slate-400 dark:text-slate-500'" x-text="displayText" class="truncate"></span>
                                        <div class="flex items-center gap-1.5 ml-2 flex-shrink-0">
                                            <span x-show="speaker.project_id" @click.stop="choose('')" class="text-slate-400 hover:text-rose-500 p-0.5 rounded-lg transition-colors cursor-pointer" title="Clear selection">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </span>
                                            <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        </div>
                                    </button>
                                    <div x-show="open"
                                         x-cloak
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="opacity-0 scale-95"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="opacity-100 scale-100"
                                         x-transition:leave-end="opacity-0 scale-95"
                                         class="absolute z-50 mt-1.5 w-full bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xl overflow-hidden max-h-72 flex flex-col"
                                         style="display: none;">
                                        <div class="p-2.5 border-b border-slate-100 dark:border-slate-700/80 bg-slate-50/80 dark:bg-slate-900/50">
                                            <div class="relative">
                                                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                                <input x-ref="searchProjInp" x-model="search" type="text" placeholder="Type to search project, manual, track, timing…"
                                                       class="w-full pl-9 pr-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                                            </div>
                                        </div>
                                        <div class="overflow-y-auto p-1.5 space-y-0.5 max-h-56">
                                            <button type="button" @click="choose('')"
                                                    class="w-full text-left px-3 py-2 rounded-xl text-xs text-slate-400 dark:text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700/60 transition-colors">
                                                — Custom / None (Clear Selection) —
                                            </button>
                                            <template x-for="p in filtered" :key="p.id">
                                                <button type="button" @click="choose(p.id)"
                                                        class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium transition-colors text-left"
                                                        :class="String(speaker.project_id) === String(p.id) ? 'bg-primary-50 dark:bg-primary-950/60 text-primary-600 dark:text-primary-400 font-semibold' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/60'">
                                                    <div class="truncate mr-2">
                                                        <span class="font-semibold text-slate-900 dark:text-white" x-text="p.name"></span>
                                                        <span class="text-[11px] text-slate-500 dark:text-slate-400 ml-1.5" x-text="'(' + p.badge + ' • ' + p.duration + ')'"></span>
                                                        <span class="block text-[10px] text-primary-600/80 dark:text-primary-400/80 font-medium" x-text="p.track"></span>
                                                    </div>
                                                    <svg x-show="String(speaker.project_id) === String(p.id)" class="w-4 h-4 text-primary-600 dark:text-primary-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                </button>
                                            </template>
                                            <div x-show="filtered.length === 0" class="py-4 text-center text-xs text-slate-400 dark:text-slate-500">
                                                No matching speech projects
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Speech Type / Track</label>
                                <input x-model="speaker.speech_type" type="text" placeholder="e.g. Competent Communication / Ice Breaker"
                                       class="w-full px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400">Duration</label>
                                    <span x-show="speaker.project_id" class="text-[10px] text-primary-600 dark:text-primary-400 font-semibold">Project timing</span>
                                </div>
                                <input x-model="speaker.duration" type="text" placeholder="e.g. 5-7 min"
                                       :readonly="Boolean(speaker.project_id)"
                                       :class="speaker.project_id ? 'bg-slate-100/90 dark:bg-slate-800/80 cursor-not-allowed border-dashed text-slate-600 dark:text-slate-300 select-none' : ''"
                                       class="w-full px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 font-semibold text-primary-600 dark:text-primary-400 transition-colors">
                            </div>
                        </div>
                        <button x-show="speakers.length > 1" type="button" @click="removeSpeaker(index)"
                                class="p-2 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-xl transition-colors flex-shrink-0 mt-0.5" title="Remove speaker">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>
            </div>
            <button type="button" @click="addSpeaker()"
                    class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-sm text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-950/60 hover:bg-primary-100 dark:hover:bg-primary-900/60 font-semibold transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                Add Speaker
            </button>
        </div>

        {{-- ================================================================ --}}
        {{-- TTM Speakers (100% Client-Side Alpine.js) --}}
        {{-- ================================================================ --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 sm:p-8 transition-colors">
            <div class="flex items-center justify-between mb-6 pb-3 border-b border-slate-100 dark:border-slate-800">
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Table Topics (TTM) Speakers</h2>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400">Dynamic</span>
            </div>
            <div class="space-y-4">
                <template x-for="(ttm, index) in ttmSpeakers" :key="index">
                    <div class="flex gap-3 items-start p-4 sm:p-5 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-200/60 dark:border-slate-700/50">
                        <div class="w-8 h-8 bg-purple-100 dark:bg-purple-950/80 text-purple-600 dark:text-purple-400 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5 font-bold text-xs">
                            <span x-text="index + 1"></span>
                        </div>
                        <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">TTM Speaker</label>
                                
                                {{-- Searchable Member Combobox for TTM --}}
                                <div class="relative" x-data="{
                                    open: false,
                                    search: '',
                                    get filtered() {
                                        if (!this.search.trim()) return members;
                                        const q = this.search.toLowerCase();
                                        return members.filter(m => m.name.toLowerCase().includes(q));
                                    },
                                    get selectedName() {
                                        const found = members.find(m => String(m.id) === String(ttm.user_id));
                                        return found ? found.name : '— Select Member —';
                                    }
                                }" @click.outside="open = false; search = ''">
                                    <button type="button" @click="open = !open; if(open) $nextTick(() => $refs.searchInp?.focus())"
                                            class="w-full flex items-center justify-between px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 text-left transition-all">
                                        <span :class="ttm.user_id ? 'text-slate-900 dark:text-white font-medium' : 'text-slate-400 dark:text-slate-500'" x-text="selectedName" class="truncate"></span>
                                        <div class="flex items-center gap-1.5 ml-2 flex-shrink-0">
                                            <span x-show="ttm.user_id" @click.stop="ttm.user_id = ''" class="text-slate-400 hover:text-rose-500 p-0.5 rounded-lg transition-colors cursor-pointer" title="Clear selection">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </span>
                                            <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        </div>
                                    </button>
                                    <div x-show="open"
                                         x-cloak
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="opacity-0 scale-95"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="opacity-100 scale-100"
                                         x-transition:leave-end="opacity-0 scale-95"
                                         class="absolute z-50 mt-1.5 w-full bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xl overflow-hidden max-h-64 flex flex-col"
                                         style="display: none;">
                                        <div class="p-2.5 border-b border-slate-100 dark:border-slate-700/80 bg-slate-50/80 dark:bg-slate-900/50">
                                            <div class="relative">
                                                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                                <input x-ref="searchInp" x-model="search" type="text" placeholder="Type to search member…"
                                                       class="w-full pl-9 pr-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                                            </div>
                                        </div>
                                        <div class="overflow-y-auto p-1.5 space-y-0.5 max-h-48">
                                            <button type="button" @click="ttm.user_id = ''; open = false; search = ''"
                                                    class="w-full text-left px-3 py-2 rounded-xl text-xs text-slate-400 dark:text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700/60 transition-colors">
                                                — None / Clear Selection —
                                            </button>
                                            <template x-for="m in filtered" :key="m.id">
                                                <button type="button" @click="ttm.user_id = m.id; open = false; search = ''"
                                                        class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium transition-colors"
                                                        :class="String(ttm.user_id) === String(m.id) ? 'bg-primary-50 dark:bg-primary-950/60 text-primary-600 dark:text-primary-400 font-semibold' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/60'">
                                                    <span x-text="m.name" class="truncate"></span>
                                                    <svg x-show="String(ttm.user_id) === String(m.id)" class="w-4 h-4 text-primary-600 dark:text-primary-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                </button>
                                            </template>
                                            <div x-show="filtered.length === 0" class="py-4 text-center text-xs text-slate-400 dark:text-slate-500">
                                                No matching members
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Topic</label>
                                <input x-model="ttm.topic" type="text" placeholder="TTM topic"
                                       class="w-full px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                        </div>
                        <button x-show="ttmSpeakers.length > 1" type="button" @click="removeTtmSpeaker(index)"
                                class="p-2 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-xl transition-colors flex-shrink-0 mt-0.5" title="Remove TTM speaker">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>
            </div>
            <button type="button" @click="addTtmSpeaker()"
                    class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-sm text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-950/60 hover:bg-purple-100 dark:hover:bg-purple-900/60 font-semibold transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                Add TTM Speaker
            </button>
        </div>

        {{-- ================================================================ --}}
        {{-- Evaluators (100% Client-Side Alpine.js) --}}
        {{-- ================================================================ --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 sm:p-8 transition-colors">
            <div class="flex items-center justify-between mb-6 pb-3 border-b border-slate-100 dark:border-slate-800">
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Evaluators</h2>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400">Dynamic</span>
            </div>
            <div class="space-y-4">
                <template x-for="(evalItem, index) in evaluations" :key="index">
                    <div class="flex gap-3 items-center p-4 sm:p-5 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-200/60 dark:border-slate-700/50">
                        <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Speaker Being Evaluated</label>
                                <select x-model="evalItem.speaker_index"
                                        class="w-full px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 font-medium">
                                    <template x-for="(sp, si) in speakers" :key="si">
                                        <option :value="si" x-text="'Speaker ' + (si + 1)"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Evaluator</label>
                                
                                {{-- Searchable Member Combobox for Evaluator --}}
                                <div class="relative" x-data="{
                                    open: false,
                                    search: '',
                                    get filtered() {
                                        if (!this.search.trim()) return members;
                                        const q = this.search.toLowerCase();
                                        return members.filter(m => m.name.toLowerCase().includes(q));
                                    },
                                    get selectedName() {
                                        const found = members.find(m => String(m.id) === String(evalItem.evaluator_user_id));
                                        return found ? found.name : '— Select Evaluator —';
                                    }
                                }" @click.outside="open = false; search = ''">
                                    <button type="button" @click="open = !open; if(open) $nextTick(() => $refs.searchInp?.focus())"
                                            class="w-full flex items-center justify-between px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 text-left transition-all">
                                        <span :class="evalItem.evaluator_user_id ? 'text-slate-900 dark:text-white font-medium' : 'text-slate-400 dark:text-slate-500'" x-text="selectedName" class="truncate"></span>
                                        <div class="flex items-center gap-1.5 ml-2 flex-shrink-0">
                                            <span x-show="evalItem.evaluator_user_id" @click.stop="evalItem.evaluator_user_id = ''" class="text-slate-400 hover:text-rose-500 p-0.5 rounded-lg transition-colors cursor-pointer" title="Clear selection">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </span>
                                            <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        </div>
                                    </button>
                                    <div x-show="open"
                                         x-cloak
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="opacity-0 scale-95"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="opacity-100 scale-100"
                                         x-transition:leave-end="opacity-0 scale-95"
                                         class="absolute z-50 mt-1.5 w-full bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xl overflow-hidden max-h-64 flex flex-col"
                                         style="display: none;">
                                        <div class="p-2.5 border-b border-slate-100 dark:border-slate-700/80 bg-slate-50/80 dark:bg-slate-900/50">
                                            <div class="relative">
                                                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                                <input x-ref="searchInp" x-model="search" type="text" placeholder="Type to search evaluator…"
                                                       class="w-full pl-9 pr-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                                            </div>
                                        </div>
                                        <div class="overflow-y-auto p-1.5 space-y-0.5 max-h-48">
                                            <button type="button" @click="evalItem.evaluator_user_id = ''; open = false; search = ''"
                                                    class="w-full text-left px-3 py-2 rounded-xl text-xs text-slate-400 dark:text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700/60 transition-colors">
                                                — None / Clear Selection —
                                            </button>
                                            <template x-for="m in filtered" :key="m.id">
                                                <button type="button" @click="evalItem.evaluator_user_id = m.id; open = false; search = ''"
                                                        class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium transition-colors"
                                                        :class="String(evalItem.evaluator_user_id) === String(m.id) ? 'bg-primary-50 dark:bg-primary-950/60 text-primary-600 dark:text-primary-400 font-semibold' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/60'">
                                                    <span x-text="m.name" class="truncate"></span>
                                                    <svg x-show="String(evalItem.evaluator_user_id) === String(m.id)" class="w-4 h-4 text-primary-600 dark:text-primary-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                </button>
                                            </template>
                                            <div x-show="filtered.length === 0" class="py-4 text-center text-xs text-slate-400 dark:text-slate-500">
                                                No matching members
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button x-show="evaluations.length > 1" type="button" @click="removeEvaluation(index)"
                                class="p-2 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-xl transition-colors flex-shrink-0" title="Remove evaluation">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>
            </div>
            <button type="button" @click="addEvaluation()"
                    class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-sm text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 font-semibold transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                Add Evaluation
            </button>
        </div>

        {{-- ================================================================ --}}
        {{-- Actions --}}
        {{-- ================================================================ --}}
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('meetings.index') }}" class="px-5 py-2.5 text-xs sm:text-sm text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-semibold transition-colors">Cancel</a>
            <button type="submit" wire:loading.attr="disabled"
                    class="px-7 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-xs sm:text-sm font-semibold rounded-2xl transition-all shadow-md shadow-primary-600/20 disabled:opacity-60 active:scale-[0.98]">
                <span wire:loading.remove>Create Meeting</span>
                <span wire:loading class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    Creating…
                </span>
            </button>
        </div>

    </form>
</div>
