<div class="p-6 lg:p-8 space-y-8">

    @if($currentClub)
    {{-- ================================================================== --}}
    {{-- Club-Scoped Dashboard --}}
    {{-- ================================================================== --}}

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $currentClub->name }}</h1>
            <p class="text-gray-500 text-sm mt-1">Club Dashboard</p>
        </div>
        @can('meetings.create')
        <a href="{{ route('meetings.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Create Meeting
        </a>
        @endcan
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">

        {{-- Members --}}
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full">Active</span>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ $memberCount }}</p>
            <p class="text-sm text-gray-500 mt-1">Members</p>
        </div>

        {{-- Upcoming Meetings --}}
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ $upcomingMeetings->count() }}</p>
            <p class="text-sm text-gray-500 mt-1">Upcoming Meetings</p>
            @if($upcomingMeetings->first())
                <p class="text-xs text-blue-600 mt-2 font-medium">
                    Next: {{ $upcomingMeetings->first()->meeting_date->format('d M Y') }}
                </p>
            @endif
        </div>

        {{-- Total Meetings --}}
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 bg-purple-50 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ $totalMeetings }}</p>
            <p class="text-sm text-gray-500 mt-1">Total Meetings</p>
        </div>

        {{-- Last Attendance --}}
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900">
                @if($lastAttendance !== null)
                    {{ $lastAttendance }}
                @else
                    —
                @endif
            </p>
            <p class="text-sm text-gray-500 mt-1">Last Meeting Attendance</p>
            @if($lastMeeting)
                <p class="text-xs text-green-600 mt-2 font-medium">Meeting #{{ $lastMeeting->meeting_number }}</p>
            @endif
        </div>

    </div>

    {{-- Main content grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Upcoming Meetings --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900">Upcoming Meetings</h2>
                <a href="{{ route('meetings.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">View all →</a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($upcomingMeetings as $meeting)
                <a href="{{ route('meetings.show', $meeting) }}" class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50 transition-colors">
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 flex flex-col items-center justify-center flex-shrink-0">
                        <span class="text-indigo-600 text-xs font-semibold">{{ $meeting->meeting_date->format('M') }}</span>
                        <span class="text-indigo-800 text-lg font-bold leading-none">{{ $meeting->meeting_date->format('d') }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-900">Meeting #{{ $meeting->meeting_number }}</p>
                        <p class="text-sm text-gray-500 truncate">{{ $meeting->theme ?: 'No theme set' }}</p>
                    </div>
                    <span class="flex-shrink-0 text-xs font-medium px-2.5 py-1 rounded-full {{ $meeting->statusColor() }}">
                        {{ ucfirst($meeting->status) }}
                    </span>
                </a>
                @empty
                <div class="px-6 py-12 text-center">
                    <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <p class="text-gray-500 text-sm">No upcoming meetings scheduled</p>
                    @can('meetings.create')
                    <a href="{{ route('meetings.create') }}" class="mt-3 inline-flex items-center text-sm text-indigo-600 font-medium hover:text-indigo-800">
                        Create the first meeting →
                    </a>
                    @endcan
                </div>
                @endforelse
            </div>
        </div>

        {{-- Recent Meetings --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900">Recent Meetings</h2>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($recentMeetings as $meeting)
                <a href="{{ route('meetings.show', $meeting) }}" class="flex items-center gap-3 px-6 py-3.5 hover:bg-gray-50 transition-colors">
                    <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                        <span class="text-gray-500 text-xs font-bold">#{{ $meeting->meeting_number }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800 truncate">{{ $meeting->meeting_date->format('d M Y') }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ $meeting->theme ?: 'No theme' }}</p>
                    </div>
                </a>
                @empty
                <div class="px-6 py-8 text-center">
                    <p class="text-gray-400 text-sm">No completed meetings yet</p>
                </div>
                @endforelse
            </div>
        </div>

    </div>

    @else
    {{-- ================================================================== --}}
    {{-- Global Dashboard (All Clubs) --}}
    {{-- ================================================================== --}}

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Global Dashboard</h1>
            <p class="text-gray-500 text-sm mt-1">Overview across all assigned clubs</p>
        </div>
        @can('meetings.create')
        <a href="{{ route('meetings.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Create Meeting
        </a>
        @endcan
    </div>

    {{-- Global Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
            <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center mb-4">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/>
                </svg>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ $totalClubs }}</p>
            <p class="text-sm text-gray-500 mt-1">Total Clubs</p>
        </div>

        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
            <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center mb-4">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ $totalUsers }}</p>
            <p class="text-sm text-gray-500 mt-1">Total Members</p>
        </div>

        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
            <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center mb-4">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ $upcomingCount }}</p>
            <p class="text-sm text-gray-500 mt-1">Upcoming Meetings</p>
        </div>
    </div>

    {{-- Club summaries --}}
    <div>
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Club Overview</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach($clubSummaries as $summary)
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center flex-shrink-0">
                        <span class="text-white text-sm font-bold">{{ strtoupper(substr($summary['club']->code, 0, 2)) }}</span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 text-sm">{{ $summary['club']->name }}</h3>
                        <span class="text-xs text-gray-400">{{ ucfirst($summary['club']->status) }}</span>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3 text-center">
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-lg font-bold text-gray-900">{{ $summary['member_count'] }}</p>
                        <p class="text-xs text-gray-400">Members</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-lg font-bold text-gray-900">{{ $summary['upcoming_count'] }}</p>
                        <p class="text-xs text-gray-400">Upcoming</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-lg font-bold text-gray-900">{{ $summary['total_meetings'] }}</p>
                        <p class="text-xs text-gray-400">Meetings</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Recent meetings across all clubs --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-900">Recent Meetings</h2>
            <a href="{{ route('meetings.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">View all →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Meeting</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Club</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($recentMeetings as $meeting)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <a href="{{ route('meetings.show', $meeting) }}" class="font-medium text-gray-900 hover:text-indigo-600">
                                Meeting #{{ $meeting->meeting_number }}
                            </a>
                            @if($meeting->theme)
                            <p class="text-xs text-gray-400 mt-0.5">{{ $meeting->theme }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $meeting->club->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $meeting->meeting_date->format('d M Y') }}</td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $meeting->statusColor() }}">
                                {{ ucfirst($meeting->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-gray-400 text-sm">No meetings found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @endif

</div>
