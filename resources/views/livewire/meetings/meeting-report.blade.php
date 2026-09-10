<div class="p-6 lg:p-8 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('meetings.show', $meeting) }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-xl transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Meeting Report</h1>
                <p class="text-gray-500 text-sm mt-1">Meeting #{{ $meeting->meeting_number }} · {{ $meeting->club->name }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-4 py-2 border border-gray-200 text-gray-700 text-sm font-medium rounded-xl hover:bg-gray-50 transition-colors">
                🖨️ Print Report
            </button>
        </div>
    </div>

    {{-- Meeting Header Info --}}
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl p-6 text-white">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-6">
            <div>
                <p class="text-indigo-200 text-xs font-medium uppercase tracking-wider">Meeting Number</p>
                <p class="text-2xl font-bold mt-1">#{{ $meeting->meeting_number }}</p>
            </div>
            <div>
                <p class="text-indigo-200 text-xs font-medium uppercase tracking-wider">Date</p>
                <p class="text-lg font-semibold mt-1">{{ $meeting->meeting_date->format('d M Y') }}</p>
            </div>
            <div>
                <p class="text-indigo-200 text-xs font-medium uppercase tracking-wider">Club</p>
                <p class="text-lg font-semibold mt-1">{{ $meeting->club->name }}</p>
            </div>
            <div>
                <p class="text-indigo-200 text-xs font-medium uppercase tracking-wider">Status</p>
                <p class="text-lg font-semibold mt-1">{{ ucfirst($meeting->status) }}</p>
            </div>
        </div>
        @if($meeting->theme)
        <div class="mt-4 pt-4 border-t border-indigo-500/30">
            <p class="text-indigo-200 text-xs font-medium uppercase tracking-wider">Theme</p>
            <p class="text-xl font-medium mt-1">{{ $meeting->theme }}</p>
        </div>
        @endif
    </div>

    {{-- Attendance Stats --}}
    @if(!$meeting->attendance->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h2 class="text-base font-semibold text-gray-900 mb-4">Attendance Summary</h2>
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
            <div class="bg-green-50 rounded-xl p-4 text-center">
                <p class="text-2xl font-bold text-green-700">{{ $stats['present'] }}</p>
                <p class="text-xs text-green-600 font-medium mt-1">Present</p>
            </div>
            <div class="bg-red-50 rounded-xl p-4 text-center">
                <p class="text-2xl font-bold text-red-700">{{ $stats['absent'] }}</p>
                <p class="text-xs text-red-600 font-medium mt-1">Absent</p>
            </div>
            <div class="bg-yellow-50 rounded-xl p-4 text-center">
                <p class="text-2xl font-bold text-yellow-700">{{ $stats['late'] }}</p>
                <p class="text-xs text-yellow-600 font-medium mt-1">Late</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-4 text-center">
                <p class="text-2xl font-bold text-gray-700">{{ $stats['excused'] }}</p>
                <p class="text-xs text-gray-600 font-medium mt-1">Excused</p>
            </div>
            <div class="bg-indigo-50 rounded-xl p-4 text-center">
                <p class="text-2xl font-bold text-indigo-700">
                    {{ $stats['total'] > 0 ? round($stats['present'] / $stats['total'] * 100) : 0 }}%
                </p>
                <p class="text-xs text-indigo-600 font-medium mt-1">Rate</p>
            </div>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Meeting Roles --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-4">Meeting Roles</h2>
            @if($meeting->roles->isEmpty())
                <p class="text-gray-400 text-sm">No roles assigned.</p>
            @else
            <div class="space-y-2">
                @foreach($meeting->roles->sortBy('roleType.sort_order') as $role)
                <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                    <span class="text-sm font-medium text-gray-500 w-36">{{ $role->roleType->name }}</span>
                    <span class="text-sm text-gray-900 font-medium">{{ $role->user->name }}</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Prepared Speakers --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-4">Prepared Speakers</h2>
            @if($meeting->speakers->isEmpty())
                <p class="text-gray-400 text-sm">No prepared speakers.</p>
            @else
            <div class="space-y-3">
                @foreach($meeting->speakers as $speaker)
                <div class="p-3 bg-gray-50 rounded-xl">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-medium text-gray-900 text-sm">{{ $speaker->user->name }}</p>
                            @if($speaker->topic) <p class="text-xs text-gray-500 mt-0.5">{{ $speaker->topic }}</p> @endif
                            @if($speaker->speech_type) <p class="text-xs text-indigo-600 mt-0.5">{{ $speaker->speech_type }}</p> @endif
                        </div>
                        @if($speaker->evaluation)
                        <div class="text-right">
                            <p class="text-xs text-gray-400">Evaluated by</p>
                            <p class="text-xs font-medium text-gray-700">{{ $speaker->evaluation->evaluator->name }}</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- TTM Speakers --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-4">TTM Speakers</h2>
            @if($meeting->ttmSpeakers->isEmpty())
                <p class="text-gray-400 text-sm">No TTM speakers.</p>
            @else
            <div class="space-y-2">
                @foreach($meeting->ttmSpeakers as $ttm)
                <div class="flex items-center gap-3 py-2 border-b border-gray-50 last:border-0">
                    <span class="text-xs font-bold text-purple-600 bg-purple-100 px-2 py-0.5 rounded-full">#{{ $ttm->slot }}</span>
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $ttm->user->name }}</p>
                        @if($ttm->topic) <p class="text-xs text-gray-400">{{ $ttm->topic }}</p> @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Attendance List --}}
        @if(!$meeting->attendance->isEmpty())
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-4">Attendance Details</h2>
            <div class="space-y-2">
                @foreach($meeting->attendance->sortBy('user.name') as $att)
                <div class="flex items-center justify-between py-1.5 border-b border-gray-50 last:border-0">
                    <p class="text-sm text-gray-800">{{ $att->user->name }}</p>
                    <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $att->statusColor() }}">
                        {{ ucfirst($att->status) }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>

    @if($meeting->notes)
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h2 class="text-base font-semibold text-gray-900 mb-3">Meeting Notes</h2>
        <p class="text-gray-600 text-sm whitespace-pre-wrap">{{ $meeting->notes }}</p>
    </div>
    @endif

</div>
