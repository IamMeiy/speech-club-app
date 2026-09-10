<div class="p-6 lg:p-8 space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('meetings.index') }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-xl transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Meeting #{{ $meeting->meeting_number }}</h1>
                <p class="text-gray-500 text-sm mt-1">{{ $meeting->club->name }} · {{ $meeting->meeting_date->format('d F Y') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs font-medium px-3 py-1.5 rounded-full {{ $meeting->statusColor() }}">{{ ucfirst($meeting->status) }}</span>
            @can('meetings.update')
            <a href="{{ route('meetings.edit', $meeting) }}"
               class="px-4 py-2 border border-gray-200 hover:border-gray-300 text-gray-700 text-sm font-medium rounded-xl transition-colors">Edit</a>
            @endcan
            @can('attendance.manage')
            <a href="{{ route('meetings.attendance', $meeting) }}"
               class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors">Attendance</a>
            @endcan
            @can('reports.view')
            <a href="{{ route('meetings.report', $meeting) }}"
               class="px-4 py-2 bg-gray-800 hover:bg-gray-900 text-white text-sm font-medium rounded-xl transition-colors">Report</a>
            @endcan
        </div>
    </div>

    {{-- Meeting Info Card --}}
    @if($meeting->theme || $meeting->venue || $meeting->notes)
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            @if($meeting->theme)
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Theme</p>
                <p class="text-gray-900 font-medium">{{ $meeting->theme }}</p>
            </div>
            @endif
            @if($meeting->venue)
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Venue</p>
                <p class="text-gray-900 font-medium">{{ $meeting->venue }}</p>
            </div>
            @endif
            @if($meeting->creator)
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Created By</p>
                <p class="text-gray-900 font-medium">{{ $meeting->creator->name }}</p>
            </div>
            @endif
        </div>
        @if($meeting->notes)
        <div class="mt-4 pt-4 border-t border-gray-100">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Notes</p>
            <p class="text-gray-600 text-sm">{{ $meeting->notes }}</p>
        </div>
        @endif
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Fixed Meeting Roles --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-4">Meeting Roles</h2>
            @if($meeting->roles->isEmpty())
                <p class="text-gray-400 text-sm">No roles assigned yet.</p>
            @else
            <div class="space-y-3">
                @foreach($meeting->roles->sortBy('roleType.sort_order') as $role)
                <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                    <span class="text-sm font-medium text-gray-500 w-36">{{ $role->roleType->name }}</span>
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center">
                            <span class="text-indigo-600 text-xs font-semibold">{{ strtoupper(substr($role->user->name, 0, 1)) }}</span>
                        </div>
                        <span class="text-sm text-gray-900">{{ $role->user->name }}</span>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Prepared Speakers --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-4">Prepared Speakers</h2>
            @if($meeting->speakers->isEmpty())
                <p class="text-gray-400 text-sm">No speakers assigned.</p>
            @else
            <div class="space-y-3">
                @foreach($meeting->speakers as $speaker)
                <div class="p-3 bg-gray-50 rounded-xl">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-indigo-600 bg-indigo-100 px-2 py-0.5 rounded-full">#{{ $speaker->slot }}</span>
                        <span class="font-medium text-gray-900 text-sm">{{ $speaker->user->name }}</span>
                    </div>
                    @if($speaker->topic)
                    <p class="text-xs text-gray-500 mt-1 ml-8">{{ $speaker->topic }}</p>
                    @endif
                    @if($speaker->evaluation)
                    <p class="text-xs text-green-600 mt-1 ml-8">Evaluator: {{ $speaker->evaluation->evaluator->name }}</p>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- TTM Speakers --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-4">Table Topics Speakers</h2>
            @if($meeting->ttmSpeakers->isEmpty())
                <p class="text-gray-400 text-sm">No TTM speakers assigned.</p>
            @else
            <div class="space-y-3">
                @foreach($meeting->ttmSpeakers as $ttm)
                <div class="flex items-center gap-3 py-2 border-b border-gray-50 last:border-0">
                    <span class="text-xs font-bold text-purple-600 bg-purple-100 px-2 py-0.5 rounded-full">#{{ $ttm->slot }}</span>
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $ttm->user->name }}</p>
                        @if($ttm->topic) <p class="text-xs text-gray-500">{{ $ttm->topic }}</p> @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Attendance Summary --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-4">Attendance</h2>
            @if($meeting->attendance->isEmpty())
                <p class="text-gray-400 text-sm">Attendance not recorded yet.</p>
                @can('attendance.manage')
                <a href="{{ route('meetings.attendance', $meeting) }}" class="mt-3 inline-flex items-center text-sm text-indigo-600 font-medium hover:text-indigo-800">
                    Record attendance →
                </a>
                @endcan
            @else
            @php
                $present = $meeting->attendance->where('status', 'present')->count();
                $absent  = $meeting->attendance->where('status', 'absent')->count();
                $late    = $meeting->attendance->where('status', 'late')->count();
                $total   = $meeting->attendance->count();
            @endphp
            <div class="grid grid-cols-3 gap-4 mb-4">
                <div class="text-center">
                    <p class="text-2xl font-bold text-green-600">{{ $present }}</p>
                    <p class="text-xs text-gray-500">Present</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-red-500">{{ $absent }}</p>
                    <p class="text-xs text-gray-500">Absent</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-yellow-500">{{ $late }}</p>
                    <p class="text-xs text-gray-500">Late</p>
                </div>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-2">
                <div class="bg-green-500 h-2 rounded-full transition-all" style="width: {{ $total > 0 ? ($present / $total * 100) : 0 }}%"></div>
            </div>
            <p class="text-xs text-gray-400 mt-2">{{ $present }} of {{ $total }} attended</p>
            @endif
        </div>

    </div>

</div>
