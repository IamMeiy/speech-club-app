<div class="max-w-6xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-900 p-6 sm:p-7 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm transition-colors">
        <div class="flex items-center gap-4">
            <a href="{{ route('meetings.index') }}" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Meeting #{{ $meeting->meeting_number }}</h1>
                    <span class="text-xs font-semibold px-3 py-1 rounded-full {{ $meeting->statusColor() }}">{{ ucfirst($meeting->status) }}</span>
                </div>
                <p class="text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400 mt-1">{{ $meeting->club->name }} · {{ $meeting->meeting_date->format('d F Y') }}</p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @can('meetings.update')
            <a href="{{ route('meetings.edit', $meeting) }}"
               class="px-4 py-2 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs sm:text-sm font-semibold rounded-2xl transition-colors shadow-sm">
                Edit
            </a>
            @endcan
            @can('attendance.manage')
            <a href="{{ route('meetings.attendance', $meeting) }}"
               class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs sm:text-sm font-semibold rounded-2xl transition-all shadow-md shadow-primary-600/20 active:scale-[0.98]">
                Attendance
            </a>
            @endcan
            @can('reports.view')
            <a href="{{ route('meetings.report', $meeting) }}"
               class="px-4 py-2 bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 text-white text-xs sm:text-sm font-semibold rounded-2xl transition-colors shadow-sm">
                Report
            </a>
            @endcan
        </div>
    </div>

    {{-- Meeting Info Card --}}
    @if($meeting->theme || $meeting->venue || $meeting->notes)
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 sm:p-8 transition-colors">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            @if($meeting->theme)
            <div>
                <p class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Theme</p>
                <p class="text-slate-900 dark:text-white font-semibold text-sm sm:text-base">{{ $meeting->theme }}</p>
            </div>
            @endif
            @if($meeting->venue)
            <div>
                <p class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Venue</p>
                <p class="text-slate-900 dark:text-white font-semibold text-sm sm:text-base">{{ $meeting->venue }}</p>
            </div>
            @endif
            @if($meeting->creator)
            <div>
                <p class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Created By</p>
                <p class="text-slate-900 dark:text-white font-semibold text-sm sm:text-base">{{ $meeting->creator->name }}</p>
            </div>
            @endif
        </div>
        @if($meeting->notes)
        <div class="mt-6 pt-6 border-t border-slate-100 dark:border-slate-800">
            <p class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Notes</p>
            <div class="rich-text-content text-slate-600 dark:text-slate-300">
                {!! $meeting->notes !!}
            </div>
        </div>
        @endif
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Fixed Meeting Roles --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 sm:p-8 transition-colors">
            <h2 class="text-base font-bold text-slate-900 dark:text-white mb-5 pb-3 border-b border-slate-100 dark:border-slate-800">Meeting Roles</h2>
            @if($meeting->roles->isEmpty())
                <p class="text-slate-400 dark:text-slate-500 text-sm">No roles assigned yet.</p>
            @else
            <div class="space-y-3.5">
                @foreach($meeting->roles->sortBy('roleType.sort_order') as $role)
                <div class="flex items-center justify-between py-2 border-b border-slate-50 dark:border-slate-800/60 last:border-0">
                    <span class="text-xs sm:text-sm font-semibold text-slate-500 dark:text-slate-400 w-40">{{ $role->roleType->name }}</span>
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-xl bg-primary-50 dark:bg-primary-950/60 border border-primary-100 dark:border-primary-900/40 flex items-center justify-center text-primary-600 dark:text-primary-400 text-xs font-bold">
                            {{ strtoupper(substr($role->user->name, 0, 1)) }}
                        </div>
                        <span class="text-xs sm:text-sm font-semibold text-slate-900 dark:text-white">{{ $role->user->name }}</span>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Prepared Speakers --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 sm:p-8 transition-colors">
            <h2 class="text-base font-bold text-slate-900 dark:text-white mb-5 pb-3 border-b border-slate-100 dark:border-slate-800">Prepared Speakers</h2>
            @if($meeting->speakers->isEmpty())
                <p class="text-slate-400 dark:text-slate-500 text-sm">No speakers assigned.</p>
            @else
            <div class="space-y-3">
                @foreach($meeting->speakers as $speaker)
                <div class="p-4 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-200/60 dark:border-slate-700/50">
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-extrabold text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-950/60 px-2.5 py-0.5 rounded-full">#{{ $speaker->slot }}</span>
                        <span class="font-bold text-slate-900 dark:text-white text-sm">{{ $speaker->user->name }}</span>
                    </div>
                    @if($speaker->topic)
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5 ml-8">{{ $speaker->topic }}</p>
                    @endif
                    @if($speaker->evaluation)
                    <p class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold mt-1.5 ml-8">Evaluator: {{ $speaker->evaluation->evaluator->name }}</p>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- TTM Speakers --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 sm:p-8 transition-colors">
            <h2 class="text-base font-bold text-slate-900 dark:text-white mb-5 pb-3 border-b border-slate-100 dark:border-slate-800">Table Topics Speakers</h2>
            @if($meeting->ttmSpeakers->isEmpty())
                <p class="text-slate-400 dark:text-slate-500 text-sm">No TTM speakers assigned.</p>
            @else
            <div class="space-y-3">
                @foreach($meeting->ttmSpeakers as $ttm)
                <div class="flex items-center gap-3.5 py-2.5 border-b border-slate-50 dark:border-slate-800/60 last:border-0">
                    <span class="text-xs font-extrabold text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-950/60 px-2.5 py-0.5 rounded-full">#{{ $ttm->slot }}</span>
                    <div>
                        <p class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">{{ $ttm->user->name }}</p>
                        @if($ttm->topic) <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $ttm->topic }}</p> @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Attendance Summary --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 sm:p-8 transition-colors">
            <h2 class="text-base font-bold text-slate-900 dark:text-white mb-5 pb-3 border-b border-slate-100 dark:border-slate-800">Attendance Overview</h2>
            @if($meeting->attendance->isEmpty())
                <p class="text-slate-400 dark:text-slate-500 text-sm">Attendance not recorded yet.</p>
                @can('attendance.manage')
                <a href="{{ route('meetings.attendance', $meeting) }}" class="mt-3 inline-flex items-center text-xs sm:text-sm text-primary-600 dark:text-primary-400 font-semibold hover:underline">
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
            <div class="grid grid-cols-3 gap-4 mb-5">
                <div class="text-center p-3 bg-slate-50 dark:bg-slate-800/60 rounded-2xl">
                    <p class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">{{ $present }}</p>
                    <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">Present</p>
                </div>
                <div class="text-center p-3 bg-slate-50 dark:bg-slate-800/60 rounded-2xl">
                    <p class="text-2xl font-extrabold text-rose-500">{{ $absent }}</p>
                    <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">Absent</p>
                </div>
                <div class="text-center p-3 bg-slate-50 dark:bg-slate-800/60 rounded-2xl">
                    <p class="text-2xl font-extrabold text-amber-500">{{ $late }}</p>
                    <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">Late</p>
                </div>
            </div>
            <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2.5 overflow-hidden">
                <div class="bg-emerald-500 h-2.5 rounded-full transition-all duration-500" style="width: {{ $total > 0 ? ($present / $total * 100) : 0 }}%"></div>
            </div>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-2 font-medium">{{ $present }} of {{ $total }} members attended</p>
            @endif
        </div>

    </div>

</div>
