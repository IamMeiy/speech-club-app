<div x-data="{
    search: '',
    selectedTrack: '',
    selectedLevel: '',
    showModal: {{ $viewingProject ? 'true' : 'false' }},
    selectedProject: {{ $viewingProject ? Js::from([
        'id'              => $viewingProject->id,
        'name'            => $viewingProject->name,
        'track'           => $viewingProject->track,
        'level'           => $viewingProject->level,
        'level_badge'     => $viewingProject->levelBadge(),
        'min_minutes'     => $viewingProject->min_minutes,
        'max_minutes'     => $viewingProject->max_minutes,
        'duration'        => $viewingProject->formattedTiming(),
        'overview'        => $viewingProject->overview,
        'objectives'      => $viewingProject->objectives,
        'evaluator_notes' => $viewingProject->evaluator_notes,
    ]) : 'null' }},
    projects: {{ Js::from($allProjects) }},
    get projectMap() {
        const map = {};
        this.projects.forEach(p => { map[p.id] = p; });
        return map;
    },
    isMatch(id) {
        const p = this.projectMap[id];
        if (!p) return false;
        const q = this.search ? this.search.toLowerCase().trim() : '';
        const matchesSearch = !q ||
            (p.name && p.name.toLowerCase().includes(q)) ||
            (p.overview && p.overview.toLowerCase().includes(q)) ||
            (p.objectives && p.objectives.toLowerCase().includes(q)) ||
            (p.track && p.track.toLowerCase().includes(q));
        const matchesTrack = !this.selectedTrack || p.track === this.selectedTrack;
        const matchesLevel = this.selectedLevel === '' || String(p.level) === String(this.selectedLevel);
        return matchesSearch && matchesTrack && matchesLevel;
    },
    get matchCount() {
        return this.projects.filter(p => this.isMatch(p.id)).length;
    },
    openProject(id) {
        this.selectedProject = this.projectMap[id] || null;
        this.showModal = true;
    },
    closeProject() {
        this.showModal = false;
        this.selectedProject = null;
        if (typeof $wire !== 'undefined' && $wire.closeProject) {
            $wire.closeProject();
        }
    },
    clearFilters() {
        this.search = '';
        this.selectedTrack = '';
        this.selectedLevel = '';
    }
}" @keydown.escape.window="closeProject()" class="max-w-7xl mx-auto space-y-6">

    {{-- Header Banner --}}
    <div class="bg-gradient-to-r from-primary-600 via-primary-700 to-primary-800 rounded-3xl p-6 sm:p-8 text-white shadow-lg shadow-primary-600/20 transition-all flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5 mb-2">
                <div class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center backdrop-blur-xs">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <span class="text-primary-200 text-xs font-bold uppercase tracking-wider">Traditional Curriculum & Manuals</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Speech Projects Catalog</h1>
            <p class="text-primary-100 text-xs sm:text-sm mt-1 max-w-xl">
                Explore speech project requirements, learning objectives, standard durations, and evaluator guidelines from the Competent Communication & Advanced Series manuals.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <div class="bg-white/10 backdrop-blur-md px-4 py-2.5 rounded-2xl text-center border border-white/10">
                <span class="block text-2xl font-black" x-text="matchCount">{{ count($allProjects) }}</span>
                <span class="block text-[11px] font-medium text-primary-200">Matching Projects</span>
            </div>
        </div>
    </div>

    {{-- Filter Bar (Driven 100% via Alpine.js client-side) --}}
    <div class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm flex flex-col sm:flex-row items-center gap-3.5 transition-colors">
        {{-- Search (Instant Alpine filtering) --}}
        <div class="relative w-full sm:flex-1">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <input x-model="search" type="search" placeholder="Search project name, objectives, or keywords…"
                   class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
        </div>

        {{-- Track Filter (Instant Alpine filtering) --}}
        <div class="w-full sm:w-56">
            <select x-model="selectedTrack"
                    class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 font-medium">
                <option value="">All Manuals & Tracks</option>
                @foreach($tracks as $track)
                    <option value="{{ $track }}">{{ $track }}</option>
                @endforeach
            </select>
        </div>

        {{-- Level Filter (Instant Alpine filtering) --}}
        <div class="w-full sm:w-40">
            <select x-model="selectedLevel"
                    class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 font-medium">
                <option value="">All Projects</option>
                @foreach($levels as $lvl)
                    <option value="{{ $lvl }}">Project {{ $lvl }}</option>
                @endforeach
            </select>
        </div>

        {{-- Clear Button --}}
        <button x-show="search || selectedTrack || selectedLevel !== ''" x-cloak
                @click="clearFilters()"
                class="px-3 py-2 text-xs text-rose-600 hover:text-rose-700 dark:text-rose-400 font-semibold whitespace-nowrap transition-colors">
            Clear Filters
        </button>
    </div>

    {{-- Projects Grid --}}
    <div x-show="matchCount === 0" x-cloak class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 p-12 text-center">
        <p class="text-slate-400 dark:text-slate-500 font-medium text-sm">No speech projects match your search criteria.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($allProjects as $project)
        <div x-show="isMatch({{ $project['id'] }})"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm hover:border-primary-300 dark:hover:border-primary-700/60 transition-all p-6 flex flex-col justify-between group">
            <div class="space-y-3">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full {{ $project['level'] ? 'bg-primary-50 text-primary-700 dark:bg-primary-950/60 dark:text-primary-300 border border-primary-100 dark:border-primary-900/50' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' }}">
                        {{ $project['level_badge'] }}
                    </span>
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border border-amber-200/70 dark:border-amber-900/50 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ $project['duration'] }}
                    </span>
                </div>

                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                        {{ $project['name'] }}
                    </h3>
                    <p class="text-[11px] font-semibold text-slate-400 dark:text-slate-500 mt-0.5">
                        {{ $project['track'] }}
                    </p>
                </div>

                @if(!empty($project['overview']))
                <p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-3 leading-relaxed">
                    {{ $project['overview'] }}
                </p>
                @endif
            </div>

            <div class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <span class="text-[11px] text-slate-400">Standard Project</span>
                <button type="button"
                        @click="openProject({{ $project['id'] }})"
                        class="text-xs font-bold text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 flex items-center gap-1 transition-colors">
                    <span>View Objectives</span>
                    <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ================================================================ --}}
    {{-- Project Details Modal (100% Client-Side Alpine.js) --}}
    {{-- ================================================================ --}}
    <div x-show="showModal" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div @click.outside="closeProject()"
             class="bg-white dark:bg-slate-900 rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-6"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            
            <div class="flex items-start justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-primary-50 text-primary-700 dark:bg-primary-950/60 dark:text-primary-300 border border-primary-200/80 dark:border-primary-900/50"
                              x-text="selectedProject ? selectedProject.level_badge : ''">
                            {{ $viewingProject ? $viewingProject->levelBadge() : '' }}
                        </span>
                        <span class="text-[11px] font-semibold text-slate-400"
                              x-text="selectedProject ? selectedProject.track : ''">
                            {{ $viewingProject ? $viewingProject->track : '' }}
                        </span>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white"
                        x-text="selectedProject ? selectedProject.name : ''">
                        {{ $viewingProject ? $viewingProject->name : '' }}
                    </h2>
                </div>
                <button @click="closeProject()" type="button" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Timing pill bar --}}
            <div class="bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200/70 dark:border-amber-800/50 rounded-2xl p-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-500/20 text-amber-700 dark:text-amber-300 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-amber-800 dark:text-amber-400">Allotted Speech Time</span>
                        <p class="text-base font-extrabold text-amber-950 dark:text-amber-200"
                           x-text="selectedProject ? selectedProject.duration : ''">
                            {{ $viewingProject ? $viewingProject->formattedTiming() : '' }}
                        </p>
                    </div>
                </div>
                <div class="text-right text-xs text-amber-800/80 dark:text-amber-300"
                     x-text="selectedProject ? ('Green: ' + selectedProject.min_minutes + 'm • Amber: ' + (selectedProject.min_minutes + 1) + 'm • Red: ' + selectedProject.max_minutes + 'm') : ''">
                    @if($viewingProject)
                        Green: {{ $viewingProject->min_minutes }}m • Amber: {{ $viewingProject->min_minutes + 1 }}m • Red: {{ $viewingProject->max_minutes }}m
                    @endif
                </div>
            </div>

            {{-- Overview --}}
            <template x-if="selectedProject && selectedProject.overview">
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1.5">Overview</h4>
                    <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed" x-text="selectedProject.overview"></p>
                </div>
            </template>
            @if($viewingProject && $viewingProject->overview)
                <div class="sr-only">
                    <p>{{ $viewingProject->overview }}</p>
                </div>
            @endif

            {{-- Objectives --}}
            <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1.5">Speech Purpose and Objectives</h4>
                <div class="bg-slate-50 dark:bg-slate-800/50 rounded-2xl p-4 border border-slate-200/60 dark:border-slate-800 text-xs sm:text-sm text-slate-800 dark:text-slate-200 leading-relaxed font-medium"
                     x-text="selectedProject ? selectedProject.objectives : ''">
                    {{ $viewingProject ? $viewingProject->objectives : '' }}
                </div>
            </div>

            {{-- Evaluator Notes --}}
            <template x-if="selectedProject && selectedProject.evaluator_notes">
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1.5">Evaluator Guidelines and Notes</h4>
                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed italic" x-text="selectedProject.evaluator_notes"></p>
                </div>
            </template>
            @if($viewingProject && $viewingProject->evaluator_notes)
                <div class="sr-only">
                    <p>{{ $viewingProject->evaluator_notes }}</p>
                </div>
            @endif

            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                <button @click="closeProject()" type="button"
                        class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 text-white text-xs font-semibold rounded-xl transition-colors">
                    Close Details
                </button>
            </div>
        </div>
    </div>

</div>
