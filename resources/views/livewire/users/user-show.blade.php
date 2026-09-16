<div class="space-y-6 max-w-7xl mx-auto"
     x-data="{
         activeTab: @entangle('activeTab'),
         expandedSpeechId: null,
         toggleSpeech(id) {
             this.expandedSpeechId = this.expandedSpeechId === id ? null : id;
         }
     }">

    {{-- Breadcrumb / Top Bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
            <a href="{{ route('members.index') }}" class="hover:text-primary-600 dark:hover:text-primary-400 font-medium transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Members
            </a>
            <span>/</span>
            <span class="text-slate-900 dark:text-white font-semibold truncate">{{ $user->name }}</span>
        </div>

        <div class="flex items-center gap-2">
            @can('users.update')
            <a href="{{ route('members.edit', $user) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs sm:text-sm font-semibold rounded-2xl border border-slate-200 dark:border-slate-700 transition-all shadow-sm">
                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Member
            </a>
            @endcan

            @can('users.delete')
            <button
                type="button"
                x-data
                @click="$confirm({
                    title: 'Delete Member',
                    message: 'Are you sure you want to delete {{ addslashes($user->name) }}? All association and activity logs for this member will be permanently impacted.',
                    type: 'danger',
                    confirmText: 'Delete Member',
                    onConfirm: () => $wire.deleteUser()
                })"
                class="inline-flex items-center gap-2 px-4 py-2 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 dark:hover:bg-rose-900/40 text-rose-600 dark:text-rose-400 text-xs sm:text-sm font-semibold rounded-2xl border border-rose-200 dark:border-rose-900/60 transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Delete
            </button>
            @endcan
        </div>
    </div>

    {{-- Hero Profile Banner --}}
    <div class="bg-gradient-to-br from-primary-600 via-primary-700 to-indigo-900 text-white rounded-3xl p-6 sm:p-8 shadow-xl shadow-primary-900/20 relative overflow-hidden">
        {{-- Background decorative glows --}}
        <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-white/5 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute right-1/3 -top-10 w-60 h-60 bg-amber-400/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            {{-- Member Info --}}
            <div class="flex items-start sm:items-center gap-4 sm:gap-6">
                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-3xl bg-white/10 backdrop-blur-md border border-white/25 flex items-center justify-center text-white text-2xl sm:text-3xl font-black shadow-inner flex-shrink-0">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div class="space-y-1.5 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-xs font-bold uppercase tracking-wider px-3 py-1 rounded-full backdrop-blur-sm
                            {{ $user->status === 'active' ? 'bg-emerald-500/20 text-emerald-200 border border-emerald-400/30' : ($user->status === 'inactive' ? 'bg-slate-500/20 text-slate-200 border border-slate-400/30' : 'bg-rose-500/20 text-rose-200 border border-rose-400/30') }}">
                            ● {{ ucfirst($user->status) }}
                        </span>

                        @foreach($user->roles as $role)
                        <span class="text-xs font-semibold bg-white/15 text-white/95 px-3 py-1 rounded-full border border-white/20 backdrop-blur-sm">
                            {{ $role->name }}
                        </span>
                        @endforeach

                        @if($currentClub)
                        <span class="text-xs font-semibold bg-indigo-500/30 text-indigo-100 px-3 py-1 rounded-full border border-indigo-400/30">
                            {{ $currentClub->name }}
                        </span>
                        @endif
                    </div>

                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black tracking-tight text-white truncate">
                        {{ $user->name }}
                    </h1>

                    <div class="flex flex-wrap items-center gap-y-1 gap-x-4 text-xs sm:text-sm text-primary-100 font-medium">
                        {{-- Email --}}
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-primary-200 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <a href="mailto:{{ $user->email }}" class="hover:underline hover:text-white">{{ $user->email }}</a>
                        </div>

                        {{-- Phone --}}
                        @if($user->phone)
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-primary-200 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <a href="tel:{{ $user->phone }}" class="hover:underline hover:text-white">{{ $user->phone }}</a>
                        </div>
                        @endif

                        {{-- Member Since --}}
                        <div class="flex items-center gap-1.5 text-primary-200">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>Joined {{ $user->created_at ? $user->created_at->format('M d, Y') : 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Milestone Mini Widget --}}
            <div class="flex sm:flex-col items-center sm:items-end justify-between sm:justify-center gap-2 bg-white/10 backdrop-blur-md border border-white/15 p-4 rounded-2xl flex-shrink-0">
                <span class="text-xs font-semibold text-primary-100">Milestone Achievements</span>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-black text-amber-300">{{ $stats['unlocked_badges'] }}</span>
                    <span class="text-xs text-primary-200 font-bold">/ {{ $stats['total_badges'] }} Badges</span>
                </div>
                <div class="w-32 bg-white/20 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-amber-300 h-1.5 rounded-full transition-all duration-500"
                         style="width: {{ $stats['total_badges'] > 0 ? ($stats['unlocked_badges'] / $stats['total_badges'] * 100) : 0 }}%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Cards Grid (Clickable quick tabs) --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
        {{-- Speeches --}}
        <div @click="activeTab = 'speeches'"
             class="p-4 rounded-3xl border transition-all cursor-pointer select-none group"
             :class="activeTab === 'speeches' ? 'bg-primary-50 dark:bg-primary-950/40 border-primary-300 dark:border-primary-800 shadow-sm ring-2 ring-primary-500/20' : 'bg-white dark:bg-slate-900 border-slate-200/80 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700'">
            <div class="flex items-center justify-between mb-2">
                <div class="w-8 h-8 rounded-xl bg-primary-100 dark:bg-primary-900/50 text-primary-600 dark:text-primary-400 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z"/></svg>
                </div>
            </div>
            <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $stats['speeches'] }}</p>
            <p class="text-xs font-bold text-slate-600 dark:text-slate-300 mt-0.5">Speeches</p>
            <p class="text-[11px] text-slate-400 dark:text-slate-500">Prepared</p>
        </div>

        {{-- Table Topics --}}
        <div @click="activeTab = 'table_topics'"
             class="p-4 rounded-3xl border transition-all cursor-pointer select-none group"
             :class="activeTab === 'table_topics' ? 'bg-purple-50 dark:bg-purple-950/40 border-purple-300 dark:border-purple-800 shadow-sm ring-2 ring-purple-500/20' : 'bg-white dark:bg-slate-900 border-slate-200/80 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700'">
            <div class="flex items-center justify-between mb-2">
                <div class="w-8 h-8 rounded-xl bg-purple-100 dark:bg-purple-900/50 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
            </div>
            <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $stats['table_topics'] }}</p>
            <p class="text-xs font-bold text-slate-600 dark:text-slate-300 mt-0.5">Table Topics</p>
            <p class="text-[11px] text-slate-400 dark:text-slate-500">Impromptu</p>
        </div>

        {{-- Evaluations Given --}}
        <div @click="activeTab = 'evaluations'"
             class="p-4 rounded-3xl border transition-all cursor-pointer select-none group"
             :class="activeTab === 'evaluations' ? 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-300 dark:border-emerald-800 shadow-sm ring-2 ring-emerald-500/20' : 'bg-white dark:bg-slate-900 border-slate-200/80 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700'">
            <div class="flex items-center justify-between mb-2">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $stats['evaluations_given'] }}</p>
            <p class="text-xs font-bold text-slate-600 dark:text-slate-300 mt-0.5">Evaluations</p>
            <p class="text-[11px] text-slate-400 dark:text-slate-500">Given to peers</p>
        </div>

        {{-- Meeting Roles --}}
        <div @click="activeTab = 'roles'"
             class="p-4 rounded-3xl border transition-all cursor-pointer select-none group"
             :class="activeTab === 'roles' ? 'bg-blue-50 dark:bg-blue-950/40 border-blue-300 dark:border-blue-800 shadow-sm ring-2 ring-blue-500/20' : 'bg-white dark:bg-slate-900 border-slate-200/80 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700'">
            <div class="flex items-center justify-between mb-2">
                <div class="w-8 h-8 rounded-xl bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
            </div>
            <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $stats['roles_served'] }}</p>
            <p class="text-xs font-bold text-slate-600 dark:text-slate-300 mt-0.5">Meeting Roles</p>
            <p class="text-[11px] text-slate-400 dark:text-slate-500">Facilitation</p>
        </div>

        {{-- Attendance Rate --}}
        <div @click="activeTab = 'attendance'"
             class="p-4 rounded-3xl border transition-all cursor-pointer select-none group"
             :class="activeTab === 'attendance' ? 'bg-amber-50 dark:bg-amber-950/40 border-amber-300 dark:border-amber-800 shadow-sm ring-2 ring-amber-500/20' : 'bg-white dark:bg-slate-900 border-slate-200/80 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700'">
            <div class="flex items-center justify-between mb-2">
                <div class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                </div>
            </div>
            <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $stats['attendance_rate'] }}%</p>
            <p class="text-xs font-bold text-slate-600 dark:text-slate-300 mt-0.5">Attendance</p>
            <p class="text-[11px] text-slate-400 dark:text-slate-500">{{ $stats['total_attended'] }}/{{ $stats['total_records'] }} sessions</p>
        </div>

        {{-- Badges --}}
        <div @click="activeTab = 'badges'"
             class="p-4 rounded-3xl border transition-all cursor-pointer select-none group"
             :class="activeTab === 'badges' ? 'bg-rose-50 dark:bg-rose-950/40 border-rose-300 dark:border-rose-800 shadow-sm ring-2 ring-rose-500/20' : 'bg-white dark:bg-slate-900 border-slate-200/80 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700'">
            <div class="flex items-center justify-between mb-2">
                <div class="w-8 h-8 rounded-xl bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                </div>
            </div>
            <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $stats['unlocked_badges'] }}</p>
            <p class="text-xs font-bold text-slate-600 dark:text-slate-300 mt-0.5">Milestones</p>
            <p class="text-[11px] text-slate-400 dark:text-slate-500">Badges earned</p>
        </div>
    </div>

    {{-- Tabs Navigation --}}
    <div class="relative flex items-center border-b border-slate-200 dark:border-slate-800"
         x-data="{
             canScrollLeft: false,
             canScrollRight: false,
             checkScroll() {
                 const el = this.$refs.tabNav;
                 if (!el) return;
                 this.canScrollLeft = el.scrollLeft > 10;
                 this.canScrollRight = el.scrollLeft + el.clientWidth < el.scrollWidth - 10;
             },
             scroll(direction) {
                 const el = this.$refs.tabNav;
                 if (!el) return;
                 el.scrollBy({ left: direction * 240, behavior: 'smooth' });
                 setTimeout(() => this.checkScroll(), 300);
             }
         }"
         x-init="checkScroll(); $nextTick(() => { checkScroll(); setTimeout(() => checkScroll(), 250); }); window.addEventListener('resize', () => checkScroll());">

        {{-- Left Scroll Arrow (placed on the LEFT) --}}
        <button
            x-show="canScrollLeft"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-90"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-90"
            @click="scroll(-1)"
            type="button"
            class="p-1.5 mr-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition-colors shadow-sm flex-shrink-0"
            title="Scroll left"
            style="display: none;">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
            </svg>
        </button>

        <nav x-ref="tabNav"
             @scroll.passive="checkScroll()"
             class="flex space-x-1 sm:space-x-3 overflow-x-auto pb-px no-scrollbar scroll-smooth flex-1 [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden"
             aria-label="Tabs">
            {{-- Tab: Speeches --}}
            <button @click="activeTab = 'speeches'; setTimeout(() => checkScroll(), 200)"
                    class="py-3 px-3 sm:px-4 text-xs sm:text-sm font-semibold border-b-2 flex items-center gap-2 whitespace-nowrap transition-colors"
                    :class="activeTab === 'speeches' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z"/></svg>
                <span>Speeches Delivered</span>
                <span class="ml-1 px-2 py-0.5 text-[10px] rounded-full"
                      :class="activeTab === 'speeches' ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/60 dark:text-primary-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'">
                    {{ $stats['speeches'] }}
                </span>
            </button>

            {{-- Tab: Evaluations Given --}}
            <button @click="activeTab = 'evaluations'; setTimeout(() => checkScroll(), 200)"
                    class="py-3 px-3 sm:px-4 text-xs sm:text-sm font-semibold border-b-2 flex items-center gap-2 whitespace-nowrap transition-colors"
                    :class="activeTab === 'evaluations' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Evaluations Given</span>
                <span class="ml-1 px-2 py-0.5 text-[10px] rounded-full"
                      :class="activeTab === 'evaluations' ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/60 dark:text-primary-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'">
                    {{ $stats['evaluations_given'] }}
                </span>
            </button>

            {{-- Tab: Table Topics --}}
            <button @click="activeTab = 'table_topics'; setTimeout(() => checkScroll(), 200)"
                    class="py-3 px-3 sm:px-4 text-xs sm:text-sm font-semibold border-b-2 flex items-center gap-2 whitespace-nowrap transition-colors"
                    :class="activeTab === 'table_topics' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                <span>Table Topics</span>
                <span class="ml-1 px-2 py-0.5 text-[10px] rounded-full"
                      :class="activeTab === 'table_topics' ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/60 dark:text-primary-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'">
                    {{ $stats['table_topics'] }}
                </span>
            </button>

            {{-- Tab: Meeting Roles --}}
            <button @click="activeTab = 'roles'; setTimeout(() => checkScroll(), 200)"
                    class="py-3 px-3 sm:px-4 text-xs sm:text-sm font-semibold border-b-2 flex items-center gap-2 whitespace-nowrap transition-colors"
                    :class="activeTab === 'roles' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <span>Meeting Roles</span>
                <span class="ml-1 px-2 py-0.5 text-[10px] rounded-full"
                      :class="activeTab === 'roles' ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/60 dark:text-primary-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'">
                    {{ $stats['roles_served'] }}
                </span>
            </button>

            {{-- Tab: Attendance --}}
            <button @click="activeTab = 'attendance'; setTimeout(() => checkScroll(), 200)"
                    class="py-3 px-3 sm:px-4 text-xs sm:text-sm font-semibold border-b-2 flex items-center gap-2 whitespace-nowrap transition-colors"
                    :class="activeTab === 'attendance' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Attendance History</span>
                <span class="ml-1 px-2 py-0.5 text-[10px] rounded-full"
                      :class="activeTab === 'attendance' ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/60 dark:text-primary-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'">
                    {{ $stats['total_records'] }}
                </span>
            </button>

            {{-- Tab: Milestone Badges --}}
            <button @click="activeTab = 'badges'; setTimeout(() => checkScroll(), 200)"
                    class="py-3 px-3 sm:px-4 text-xs sm:text-sm font-semibold border-b-2 flex items-center gap-2 whitespace-nowrap transition-colors"
                    :class="activeTab === 'badges' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                <span>Milestone Badges</span>
                <span class="ml-1 px-2 py-0.5 text-[10px] rounded-full"
                      :class="activeTab === 'badges' ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/60 dark:text-primary-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'">
                    {{ $stats['unlocked_badges'] }}/{{ $stats['total_badges'] }}
                </span>
            </button>

            {{-- Tab: Club & Roles --}}
            <button @click="activeTab = 'clubs'; setTimeout(() => checkScroll(), 200)"
                    class="py-3 px-3 sm:px-4 text-xs sm:text-sm font-semibold border-b-2 flex items-center gap-2 whitespace-nowrap transition-colors"
                    :class="activeTab === 'clubs' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span>Club & Permissions</span>
            </button>
        </nav>

        {{-- Right Scroll Arrow (placed on the RIGHT) --}}
        <button
            x-show="canScrollRight"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-90"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-90"
            @click="scroll(1)"
            type="button"
            class="p-1.5 ml-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition-colors shadow-sm flex-shrink-0"
            title="Scroll right"
            style="display: none;">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
            </svg>
        </button>
    </div>

    {{-- ========================================================================= --}}
    {{-- TAB 1: SPEECHES DELIVERED --}}
    {{-- ========================================================================= --}}
    <div x-show="activeTab === 'speeches'" x-cloak class="space-y-4">
        @forelse($speeches as $speech)
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden transition-all">
            <div class="p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        @if($speech->projectModel)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-bold bg-primary-50 dark:bg-primary-950/60 text-primary-700 dark:text-primary-300">
                            {{ $speech->projectModel->name }} • {{ $speech->projectModel->track }} (L{{ $speech->projectModel->level }})
                        </span>
                        @endif

                        <span class="text-xs font-semibold px-2.5 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                            Slot #{{ $speech->slot }}
                        </span>

                        <span class="text-xs text-slate-400 dark:text-slate-500">
                            {{ $speech->formattedTiming() }}
                        </span>
                    </div>

                    <h3 class="text-lg font-bold text-slate-900 dark:text-white pt-1">
                        {{ $speech->topic ?: ($speech->projectModel?->name ?? $speech->project ?? 'Prepared Speech') }}
                    </h3>

                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                        Meeting #{{ $speech->meeting?->meeting_number }} on {{ $speech->meeting?->meeting_date?->format('M d, Y') }}
                        @if($speech->meeting?->club)
                        • <span class="font-medium text-slate-600 dark:text-slate-300">{{ $speech->meeting->club->name }}</span>
                        @endif
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    @if($speech->evaluation)
                    <button @click="toggleSpeech({{ $speech->id }})"
                            type="button"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-xl transition-all"
                            :class="expandedSpeechId === {{ $speech->id }} ? 'bg-primary-100 dark:bg-primary-900/60 text-primary-700 dark:text-primary-300' : 'bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300'">
                        <span x-text="expandedSpeechId === {{ $speech->id }} ? 'Hide Details' : 'View Feedback & Stats'"></span>
                        <svg class="w-3.5 h-3.5 transition-transform" :class="expandedSpeechId === {{ $speech->id }} ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    @else
                    <span class="text-xs font-semibold text-slate-400 dark:text-slate-500 italic bg-slate-50 dark:bg-slate-800/40 px-3 py-1.5 rounded-xl">
                        Pending Evaluation
                    </span>
                    @endif

                    <a href="{{ route('meetings.show', $speech->meeting_id) }}"
                       class="p-2 text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-950/40 rounded-xl transition-colors"
                       title="Go to Meeting">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>
            </div>

            {{-- Expandable Evaluation Feedback & Stats Panel --}}
            <div x-show="expandedSpeechId === {{ $speech->id }}"
                 x-collapse
                 class="border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 p-5 sm:p-6 space-y-5">

                {{-- Written Peer Evaluation --}}
                @if($speech->evaluation)
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-4 sm:p-5 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-extrabold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Written Evaluation Feedback</span>
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400">
                            Evaluator: <strong class="text-slate-800 dark:text-slate-200">{{ $speech->evaluation->evaluator?->name ?? 'Assigned Evaluator' }}</strong>
                        </span>
                    </div>
                    <p class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-line leading-relaxed">
                        {{ $speech->evaluation->notes ?: 'No written remarks provided.' }}
                    </p>
                </div>
                @endif

                {{-- Ah-Counter & Grammarian Logs for this meeting --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Ah Counter --}}
                    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-4">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                Ah-Counter Filler Stats
                            </span>
                        </div>
                        @if($speech->ah_log)
                        <div class="grid grid-cols-4 gap-2 text-center">
                            <div class="bg-slate-50 dark:bg-slate-800/60 p-2 rounded-xl">
                                <p class="text-xs text-slate-400">Ah</p>
                                <p class="text-sm font-bold text-slate-800 dark:text-white">{{ $speech->ah_log->ah_count }}</p>
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-800/60 p-2 rounded-xl">
                                <p class="text-xs text-slate-400">Um</p>
                                <p class="text-sm font-bold text-slate-800 dark:text-white">{{ $speech->ah_log->um_count }}</p>
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-800/60 p-2 rounded-xl">
                                <p class="text-xs text-slate-400">Like</p>
                                <p class="text-sm font-bold text-slate-800 dark:text-white">{{ $speech->ah_log->like_count }}</p>
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-800/60 p-2 rounded-xl">
                                <p class="text-xs text-slate-400">Repeats</p>
                                <p class="text-sm font-bold text-slate-800 dark:text-white">{{ $speech->ah_log->repeats_count }}</p>
                            </div>
                        </div>
                        @else
                        <p class="text-xs text-slate-400 dark:text-slate-500 italic">No filler log recorded for this session.</p>
                        @endif
                    </div>

                    {{-- Grammarian --}}
                    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-4">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                Grammarian Insights
                            </span>
                        </div>
                        @if($speech->grammar_log)
                        <div class="space-y-2 text-xs text-slate-600 dark:text-slate-300">
                            <div class="flex items-center justify-between bg-slate-50 dark:bg-slate-800/60 p-2 rounded-xl">
                                <span>Word of the Day usage:</span>
                                <strong class="text-primary-600 dark:text-primary-400 font-bold">{{ $speech->grammar_log->word_of_day_count }} times</strong>
                            </div>
                            @if($speech->grammar_log->good_phrases)
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 italic">
                                "{{ $speech->grammar_log->good_phrases }}"
                            </p>
                            @endif
                        </div>
                        @else
                        <p class="text-xs text-slate-400 dark:text-slate-500 italic">No grammarian log recorded for this session.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 p-12 text-center">
            <div class="w-12 h-12 bg-primary-50 dark:bg-primary-950/50 rounded-2xl flex items-center justify-center mx-auto mb-3 text-primary-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z"/></svg>
            </div>
            <h4 class="text-base font-bold text-slate-900 dark:text-white">No Prepared Speeches Yet</h4>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">This member has not yet delivered a prepared project speech.</p>
        </div>
        @endforelse

        @if($speeches->hasPages())
        <div class="pt-2">
            {{ $speeches->links() }}
        </div>
        @endif
    </div>

    {{-- ========================================================================= --}}
    {{-- TAB 2: EVALUATIONS GIVEN --}}
    {{-- ========================================================================= --}}
    <div x-show="activeTab === 'evaluations'" x-cloak class="space-y-4">
        @forelse($evaluationsGiven as $evaluation)
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 p-5 sm:p-6 shadow-sm space-y-3 transition-colors">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="space-y-0.5">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-extrabold uppercase px-2.5 py-0.5 rounded-lg bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300">
                            Evaluated Speaker
                        </span>
                        <span class="text-sm font-bold text-slate-900 dark:text-white">
                            {{ $evaluation->speaker?->user?->name ?? 'Speaker' }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Meeting #{{ $evaluation->meeting?->meeting_number }} • {{ $evaluation->meeting?->meeting_date?->format('M d, Y') }}
                        @if($evaluation->speaker?->projectModel)
                        • {{ $evaluation->speaker->projectModel->name }}
                        @endif
                    </p>
                </div>

                <a href="{{ route('meetings.show', $evaluation->meeting_id) }}"
                   class="inline-flex items-center gap-1 text-xs font-semibold text-primary-600 dark:text-primary-400 hover:underline">
                    View Meeting
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            <div class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-line leading-relaxed bg-slate-50/70 dark:bg-slate-800/40 p-4 rounded-2xl border border-slate-100 dark:border-slate-800/60">
                {{ $evaluation->notes ?: 'No written notes provided for this evaluation.' }}
            </div>
        </div>
        @empty
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 p-12 text-center">
            <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-950/50 rounded-2xl flex items-center justify-center mx-auto mb-3 text-emerald-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h4 class="text-base font-bold text-slate-900 dark:text-white">No Evaluations Given Yet</h4>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">This member has not yet served as a speech evaluator.</p>
        </div>
        @endforelse

        @if($evaluationsGiven->hasPages())
        <div class="pt-2">
            {{ $evaluationsGiven->links() }}
        </div>
        @endif
    </div>

    {{-- ========================================================================= --}}
    {{-- TAB 3: TABLE TOPICS --}}
    {{-- ========================================================================= --}}
    <div x-show="activeTab === 'table_topics'" x-cloak class="space-y-4">
        @forelse($tableTopics as $ttm)
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 p-5 sm:p-6 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition-colors">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold px-2.5 py-0.5 rounded-lg bg-purple-100 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300">
                        Slot #{{ $ttm->slot }}
                    </span>
                    <span class="text-xs text-slate-400 dark:text-slate-500">
                        {{ $ttm->formattedTiming() }}
                    </span>
                </div>
                <h4 class="text-base font-bold text-slate-900 dark:text-white">
                    {{ $ttm->topic ?: 'Table Topic Challenge' }}
                </h4>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Meeting #{{ $ttm->meeting?->meeting_number }} on {{ $ttm->meeting?->meeting_date?->format('M d, Y') }}
                    @if($ttm->meeting?->club)
                    • {{ $ttm->meeting->club->name }}
                    @endif
                </p>
                @if($ttm->notes)
                <p class="text-xs text-slate-600 dark:text-slate-400 mt-2 bg-slate-50 dark:bg-slate-800/40 p-2.5 rounded-xl">
                    {{ $ttm->notes }}
                </p>
                @endif
            </div>

            <a href="{{ route('meetings.show', $ttm->meeting_id) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 self-start sm:self-auto transition-colors">
                View Meeting
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
        </div>
        @empty
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 p-12 text-center">
            <div class="w-12 h-12 bg-purple-50 dark:bg-purple-950/50 rounded-2xl flex items-center justify-center mx-auto mb-3 text-purple-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            </div>
            <h4 class="text-base font-bold text-slate-900 dark:text-white">No Table Topics Delivered</h4>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">This member has not yet participated in impromptu Table Topics.</p>
        </div>
        @endforelse

        @if($tableTopics->hasPages())
        <div class="pt-2">
            {{ $tableTopics->links() }}
        </div>
        @endif
    </div>

    {{-- ========================================================================= --}}
    {{-- TAB 4: MEETING ROLES --}}
    {{-- ========================================================================= --}}
    <div x-show="activeTab === 'roles'" x-cloak class="space-y-4">
        @forelse($rolesServed as $role)
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 p-5 sm:p-6 shadow-sm flex items-center justify-between gap-4 transition-colors">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-blue-100 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div>
                    <h4 class="text-base font-bold text-slate-900 dark:text-white">
                        {{ $role->roleType?->name ?? 'Meeting Facilitator' }}
                    </h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Meeting #{{ $role->meeting?->meeting_number }} on {{ $role->meeting?->meeting_date?->format('M d, Y') }}
                        @if($role->meeting?->club)
                        • {{ $role->meeting->club->name }}
                        @endif
                    </p>
                </div>
            </div>

            <a href="{{ route('meetings.show', $role->meeting_id) }}"
               class="p-2 text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-950/40 rounded-xl transition-colors"
               title="View Meeting">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
        </div>
        @empty
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 p-12 text-center">
            <div class="w-12 h-12 bg-blue-50 dark:bg-blue-950/50 rounded-2xl flex items-center justify-center mx-auto mb-3 text-blue-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <h4 class="text-base font-bold text-slate-900 dark:text-white">No Meeting Roles Served Yet</h4>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">This member has not yet served as a meeting facilitator (Toastmaster, Timer, Grammarian, etc.).</p>
        </div>
        @endforelse

        @if($rolesServed->hasPages())
        <div class="pt-2">
            {{ $rolesServed->links() }}
        </div>
        @endif
    </div>

    {{-- ========================================================================= --}}
    {{-- TAB 5: ATTENDANCE HISTORY --}}
    {{-- ========================================================================= --}}
    <div x-show="activeTab === 'attendance'" x-cloak class="space-y-4">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden transition-colors">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40">
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Meeting</th>
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Club</th>
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Notes</th>
                            <th class="text-right px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        @forelse($attendanceRecords as $att)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-6 py-4 font-bold text-slate-900 dark:text-white text-xs sm:text-sm">
                                Meeting #{{ $att->meeting?->meeting_number }}
                            </td>
                            <td class="px-6 py-4 text-xs sm:text-sm text-slate-600 dark:text-slate-400">
                                {{ $att->meeting?->meeting_date?->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 text-xs sm:text-sm text-slate-600 dark:text-slate-400">
                                {{ $att->meeting?->club?->name ?? 'Primary Club' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                    {{ $att->status === 'present' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : ($att->status === 'late' ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300' : ($att->status === 'excused' ? 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300')) }}">
                                    ● {{ ucfirst($att->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500 dark:text-slate-400">
                                {{ $att->notes ?: '—' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('meetings.show', $att->meeting_id) }}"
                                   class="p-2 text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 rounded-xl transition-colors inline-block"
                                   title="View Meeting">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500 text-xs sm:text-sm">
                                No attendance records found for this member.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($attendanceRecords->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800">
                {{ $attendanceRecords->links() }}
            </div>
            @endif
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- TAB 6: MILESTONE BADGES --}}
    {{-- ========================================================================= --}}
    <div x-show="activeTab === 'badges'" x-cloak class="space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($badges as $badge)
            <div class="p-5 rounded-3xl border transition-all relative overflow-hidden flex flex-col justify-between
                 {{ $badge['unlocked'] ? 'bg-white dark:bg-slate-900 border-amber-300/80 dark:border-amber-500/40 shadow-md shadow-amber-500/5' : 'bg-slate-50/50 dark:bg-slate-900/40 border-slate-200/80 dark:border-slate-800 opacity-75' }}">

                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[10px] font-extrabold uppercase px-2.5 py-0.5 rounded-full
                              {{ $badge['unlocked'] ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                            {{ $badge['category'] }}
                        </span>

                        @if($badge['unlocked'])
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-600 dark:text-amber-400">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            Unlocked
                        </span>
                        @else
                        <span class="text-[11px] font-semibold text-slate-400">In Progress</span>
                        @endif
                    </div>

                    <h4 class="text-base font-bold text-slate-900 dark:text-white">
                        {{ $badge['name'] }}
                    </h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                        {{ $badge['description'] }}
                    </p>
                </div>

                <div class="mt-5 space-y-1.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <div class="flex items-center justify-between text-xs font-bold">
                        <span class="text-slate-500 dark:text-slate-400">Progress</span>
                        <span class="{{ $badge['unlocked'] ? 'text-amber-600 dark:text-amber-400' : 'text-slate-700 dark:text-slate-300' }}">
                            {{ $badge['progress'] }} / {{ $badge['target'] }}
                        </span>
                    </div>
                    <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
                        <div class="h-2 rounded-full transition-all duration-500 {{ $badge['unlocked'] ? 'bg-amber-400' : 'bg-primary-500' }}"
                             style="width: {{ ($badge['progress'] / $badge['target']) * 100 }}%"></div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- TAB 7: CLUB & PERMISSIONS DETAILS --}}
    {{-- ========================================================================= --}}
    <div x-show="activeTab === 'clubs'" x-cloak class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Club Memberships --}}
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm space-y-4">
                <div class="flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="w-8 h-8 rounded-xl bg-primary-100 dark:bg-primary-900/50 text-primary-600 dark:text-primary-400 flex items-center justify-center font-bold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Assigned Clubs</h3>
                        <p class="text-xs text-slate-400">Clubs this member belongs to</p>
                    </div>
                </div>

                <div class="space-y-2">
                    @forelse($user->clubs as $c)
                    <div class="flex items-center justify-between p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary-500 to-indigo-600 flex items-center justify-center text-white font-black text-xs shadow-sm">
                                {{ strtoupper(substr($c->name, 0, 2)) }}
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $c->name }}</p>
                                @if($c->code)
                                <p class="text-[11px] text-slate-400 uppercase tracking-wider font-semibold">{{ $c->code }}</p>
                                @endif
                            </div>
                        </div>

                        @can('clubs.view')
                        <a href="{{ route('clubs.roles', $c) }}" class="text-xs font-semibold text-primary-600 dark:text-primary-400 hover:underline">
                            Manage Roles
                        </a>
                        @endcan
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 italic">No assigned clubs.</p>
                    @endforelse
                </div>
            </div>

            {{-- System Roles & Permissions --}}
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm space-y-4">
                <div class="flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="w-8 h-8 rounded-xl bg-purple-100 dark:bg-purple-900/50 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Security & Roles</h3>
                        <p class="text-xs text-slate-400">Assigned security permissions</p>
                    </div>
                </div>

                <div class="space-y-3">
                    <div>
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Assigned Role(s):</span>
                        <div class="flex flex-wrap gap-2 mt-1.5">
                            @forelse($user->roles as $r)
                            <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-semibold bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                                {{ $r->name }}
                            </span>
                            @empty
                            <span class="text-xs text-slate-400 italic">None</span>
                            @endforelse
                        </div>
                    </div>

                    <div class="pt-2">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Direct Permissions Granted:</span>
                        <div class="flex flex-wrap gap-1.5 mt-1.5 max-h-48 overflow-y-auto pr-1">
                            @forelse($user->getAllPermissions() as $p)
                            <span class="text-[11px] font-mono px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                {{ $p->name }}
                            </span>
                            @empty
                            <span class="text-xs text-slate-400 italic">No direct permissions.</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
