<div class="space-y-8 max-w-7xl mx-auto">

    @if($currentClub)
    {{-- ================================================================== --}}
    {{-- Club-Scoped Dashboard --}}
    {{-- ================================================================== --}}

    {{-- Header Banner --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-900 p-6 sm:p-8 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm transition-colors">
        <div class="space-y-1">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-primary-100 dark:bg-primary-950/60 text-primary-600 dark:text-primary-400 flex items-center justify-center font-bold text-base shadow-sm">
                    {{ strtoupper(substr($currentClub->code, 0, 2)) }}
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-white">{{ $currentClub->name }}</h1>
                    <p class="text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400">Club Overview & Activity Portal</p>
                </div>
            </div>
        </div>
        @can('meetings.create')
        <a href="{{ route('meetings.create') }}"
           class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-2xl transition-all shadow-md shadow-primary-600/20 active:scale-[0.98]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            Create Meeting
        </a>
        @endcan
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">

        {{-- Members --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-md transition-all group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-primary-50 dark:bg-primary-950/50 rounded-2xl flex items-center justify-center text-primary-600 dark:text-primary-400 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-semibold text-primary-700 dark:text-primary-300 bg-primary-50 dark:bg-primary-950/60 px-2.5 py-1 rounded-full">Active</span>
            </div>
            <p class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $memberCount }}</p>
            <p class="text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400 mt-1">Club Members</p>
        </div>

        {{-- Upcoming Meetings --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-md transition-all group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-sky-50 dark:bg-sky-950/50 rounded-2xl flex items-center justify-center text-sky-600 dark:text-sky-400 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $upcomingMeetings->count() }}</p>
            <p class="text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400 mt-1">Upcoming Meetings</p>
            @if($upcomingMeetings->first())
                <p class="text-xs text-sky-600 dark:text-sky-400 mt-2 font-semibold">
                    Next: {{ $upcomingMeetings->first()->meeting_date->format('d M Y') }}
                </p>
            @endif
        </div>

        {{-- Total Meetings --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-md transition-all group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-purple-50 dark:bg-purple-950/50 rounded-2xl flex items-center justify-center text-purple-600 dark:text-purple-400 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $totalMeetings }}</p>
            <p class="text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400 mt-1">Total Meetings Held</p>
        </div>

        {{-- Last Attendance --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-md transition-all group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-950/50 rounded-2xl flex items-center justify-center text-emerald-600 dark:text-emerald-400 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                @if($lastAttendance !== null)
                    {{ $lastAttendance }}
                @else
                    —
                @endif
            </p>
            <p class="text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400 mt-1">Last Meeting Attendance</p>
            @if($lastMeeting)
                <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-2 font-semibold">Meeting #{{ $lastMeeting->meeting_number }}</p>
            @endif
        </div>

    </div>

    {{-- Main content grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Upcoming Meetings --}}
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden transition-colors">
            <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-slate-900 dark:text-white text-base">Upcoming Meetings</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Scheduled sessions and agenda</p>
                </div>
                <a href="{{ route('meetings.index') }}" class="text-xs sm:text-sm text-primary-600 dark:text-primary-400 hover:underline font-semibold flex items-center gap-1">
                    View all <span>→</span>
                </a>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800/60">
                @forelse($upcomingMeetings as $meeting)
                <a href="{{ route('meetings.show', $meeting) }}" class="flex items-center gap-4 px-6 py-4 hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors group">
                    <div class="w-12 h-12 rounded-2xl bg-primary-50 dark:bg-primary-950/60 border border-primary-100 dark:border-primary-900/50 flex flex-col items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                        <span class="text-primary-600 dark:text-primary-400 text-[11px] font-bold uppercase">{{ $meeting->meeting_date->format('M') }}</span>
                        <span class="text-primary-800 dark:text-primary-200 text-lg font-extrabold leading-none">{{ $meeting->meeting_date->format('d') }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-slate-900 dark:text-white text-sm">Meeting #{{ $meeting->meeting_number }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 truncate mt-0.5">{{ $meeting->theme ?: 'No theme set' }}</p>
                    </div>
                    <span class="flex-shrink-0 text-xs font-semibold px-3 py-1 rounded-full {{ $meeting->statusColor() }}">
                        {{ ucfirst($meeting->status) }}
                    </span>
                </a>
                @empty
                <div class="px-6 py-12 text-center">
                    <div class="w-12 h-12 bg-slate-100 dark:bg-slate-800 rounded-2xl flex items-center justify-center mx-auto mb-3 text-slate-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">No upcoming meetings scheduled</p>
                    @can('meetings.create')
                    <a href="{{ route('meetings.create') }}" class="mt-3 inline-flex items-center text-xs sm:text-sm text-primary-600 dark:text-primary-400 font-semibold hover:underline">
                        Schedule a meeting →
                    </a>
                    @endcan
                </div>
                @endforelse
            </div>
        </div>

        {{-- Recent Meetings --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden transition-colors">
            <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800">
                <h2 class="font-bold text-slate-900 dark:text-white text-base">Recent Meetings</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Past minutes & attendance</p>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800/60">
                @forelse($recentMeetings as $meeting)
                <a href="{{ route('meetings.show', $meeting) }}" class="flex items-center gap-3 px-6 py-4 hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors group">
                    <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center flex-shrink-0 text-slate-600 dark:text-slate-300 font-bold text-xs">
                        #{{ $meeting->meeting_number }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs sm:text-sm font-semibold text-slate-800 dark:text-slate-200 truncate">{{ $meeting->meeting_date->format('d M Y') }}</p>
                        <p class="text-xs text-slate-400 dark:text-slate-500 truncate">{{ $meeting->theme ?: 'No theme' }}</p>
                    </div>
                </a>
                @empty
                <div class="px-6 py-10 text-center">
                    <p class="text-slate-400 dark:text-slate-500 text-xs sm:text-sm">No completed meetings yet</p>
                </div>
                @endforelse
            </div>
        </div>

    </div>

    @else
    {{-- ================================================================== --}}
    {{-- Global Dashboard (All Clubs) --}}
    {{-- ================================================================== --}}

    {{-- Header Banner --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-900 p-6 sm:p-8 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm transition-colors">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Global Dashboard</h1>
            <p class="text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400 mt-1">Enterprise overview across all assigned speech clubs</p>
        </div>
        @can('meetings.create')
        <a href="{{ route('meetings.create') }}"
           class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-2xl transition-all shadow-md shadow-primary-600/20 active:scale-[0.98]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            Create Meeting
        </a>
        @endcan
    </div>

    {{-- Global Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-md transition-all">
            <div class="w-12 h-12 bg-primary-50 dark:bg-primary-950/50 rounded-2xl flex items-center justify-center mb-4 text-primary-600 dark:text-primary-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/>
                </svg>
            </div>
            <p class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $totalClubs }}</p>
            <p class="text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400 mt-1">Total Active Clubs</p>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-md transition-all">
            <div class="w-12 h-12 bg-sky-50 dark:bg-sky-950/50 rounded-2xl flex items-center justify-center mb-4 text-sky-600 dark:text-sky-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <p class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $totalUsers }}</p>
            <p class="text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400 mt-1">Enrolled Members</p>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-md transition-all">
            <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-950/50 rounded-2xl flex items-center justify-center mb-4 text-emerald-600 dark:text-emerald-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <p class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $upcomingCount }}</p>
            <p class="text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400 mt-1">Upcoming Meetings</p>
        </div>
    </div>

    {{-- Club summaries --}}
    <div class="space-y-4">
        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Clubs Overview</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @foreach($clubSummaries as $summary)
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 hover:shadow-md transition-all">
                <div class="flex items-center gap-3.5 mb-5">
                    <div class="w-11 h-11 bg-gradient-to-br from-primary-500 to-primary-700 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-sm shadow-primary-500/20">
                        <span class="text-white text-sm font-extrabold">{{ strtoupper(substr($summary['club']->code, 0, 2)) }}</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="font-bold text-slate-900 dark:text-white text-sm truncate">{{ $summary['club']->name }}</h3>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $summary['club']->status === 'active' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                            {{ ucfirst($summary['club']->status) }}
                        </span>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2 text-center">
                    <div class="bg-slate-50 dark:bg-slate-800/60 rounded-2xl p-3">
                        <p class="text-lg font-bold text-slate-900 dark:text-white">{{ $summary['member_count'] }}</p>
                        <p class="text-[11px] font-medium text-slate-400 dark:text-slate-500">Members</p>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-800/60 rounded-2xl p-3">
                        <p class="text-lg font-bold text-slate-900 dark:text-white">{{ $summary['upcoming_count'] }}</p>
                        <p class="text-[11px] font-medium text-slate-400 dark:text-slate-500">Upcoming</p>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-800/60 rounded-2xl p-3">
                        <p class="text-lg font-bold text-slate-900 dark:text-white">{{ $summary['total_meetings'] }}</p>
                        <p class="text-[11px] font-medium text-slate-400 dark:text-slate-500">Total</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Recent meetings across all clubs --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden transition-colors">
        <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h2 class="font-bold text-slate-900 dark:text-white text-base">Recent Meetings</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Cross-club activities log</p>
            </div>
            <a href="{{ route('meetings.index') }}" class="text-xs sm:text-sm text-primary-600 dark:text-primary-400 hover:underline font-semibold flex items-center gap-1">
                View all <span>→</span>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40">
                        <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Meeting</th>
                        <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Club</th>
                        <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($recentMeetings as $meeting)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors">
                        <td class="px-6 py-4">
                            <a href="{{ route('meetings.show', $meeting) }}" class="font-semibold text-slate-900 dark:text-white hover:text-primary-600 dark:hover:text-primary-400 text-sm">
                                Meeting #{{ $meeting->meeting_number }}
                            </a>
                            @if($meeting->theme)
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 truncate">{{ $meeting->theme }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-xs sm:text-sm text-slate-700 dark:text-slate-300 font-medium">{{ $meeting->club->name }}</td>
                        <td class="px-6 py-4 text-xs sm:text-sm text-slate-500 dark:text-slate-400">{{ $meeting->meeting_date->format('d M Y') }}</td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $meeting->statusColor() }}">
                                {{ ucfirst($meeting->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-slate-400 text-sm">No meetings recorded yet</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @endif

</div>
