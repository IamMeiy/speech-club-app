<div class="max-w-6xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('meetings.show', $meeting) }}" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-white dark:hover:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Meeting Report</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">Meeting #{{ $meeting->meeting_number }} · {{ $meeting->club->name }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-4 py-2 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs sm:text-sm font-semibold rounded-2xl transition-colors shadow-sm flex items-center gap-2">
                <span>🖨️</span>
                <span>Print Report</span>
            </button>
        </div>
    </div>

    {{-- Meeting Header Info --}}
    <div class="bg-gradient-to-r from-primary-600 via-primary-700 to-primary-800 rounded-3xl p-6 sm:p-8 text-white shadow-lg shadow-primary-600/20 transition-all">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-6">
            <div>
                <p class="text-primary-200 text-[11px] font-bold uppercase tracking-wider">Meeting Number</p>
                <p class="text-3xl font-extrabold mt-1 tracking-tight">#{{ $meeting->meeting_number }}</p>
            </div>
            <div>
                <p class="text-primary-200 text-[11px] font-bold uppercase tracking-wider">Date</p>
                <p class="text-base sm:text-lg font-bold mt-1">{{ $meeting->meeting_date->format('d M Y') }}</p>
            </div>
            <div>
                <p class="text-primary-200 text-[11px] font-bold uppercase tracking-wider">Club</p>
                <p class="text-base sm:text-lg font-bold mt-1 truncate">{{ $meeting->club->name }}</p>
            </div>
            <div>
                <p class="text-primary-200 text-[11px] font-bold uppercase tracking-wider">Status</p>
                <p class="text-base sm:text-lg font-bold mt-1">{{ ucfirst($meeting->status) }}</p>
            </div>
        </div>
        @if($meeting->theme)
        <div class="mt-6 pt-6 border-t border-white/20">
            <p class="text-primary-200 text-[11px] font-bold uppercase tracking-wider">Meeting Theme</p>
            <p class="text-xl sm:text-2xl font-bold mt-1">{{ $meeting->theme }}</p>
        </div>
        @endif
    </div>

    {{-- Attendance Stats --}}
    @if(!$meeting->attendance->isEmpty())
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 sm:p-8 transition-colors">
        <h2 class="text-base font-bold text-slate-900 dark:text-white mb-5">Attendance Summary</h2>
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3.5">
            <div class="bg-emerald-50 dark:bg-emerald-950/50 rounded-2xl p-4 text-center border border-emerald-100 dark:border-emerald-900/40">
                <p class="text-2xl font-extrabold text-emerald-700 dark:text-emerald-300">{{ $stats['present'] }}</p>
                <p class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold mt-1">Present</p>
            </div>
            <div class="bg-rose-50 dark:bg-rose-950/50 rounded-2xl p-4 text-center border border-rose-100 dark:border-rose-900/40">
                <p class="text-2xl font-extrabold text-rose-700 dark:text-rose-300">{{ $stats['absent'] }}</p>
                <p class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1">Absent</p>
            </div>
            <div class="bg-amber-50 dark:bg-amber-950/50 rounded-2xl p-4 text-center border border-amber-100 dark:border-amber-900/40">
                <p class="text-2xl font-extrabold text-amber-700 dark:text-amber-300">{{ $stats['late'] }}</p>
                <p class="text-xs text-amber-600 dark:text-amber-400 font-semibold mt-1">Late</p>
            </div>
            <div class="bg-slate-50 dark:bg-slate-800/60 rounded-2xl p-4 text-center border border-slate-100 dark:border-slate-700/50">
                <p class="text-2xl font-extrabold text-slate-700 dark:text-slate-200">{{ $stats['excused'] }}</p>
                <p class="text-xs text-slate-600 dark:text-slate-400 font-semibold mt-1">Excused</p>
            </div>
            <div class="bg-primary-50 dark:bg-primary-950/50 rounded-2xl p-4 text-center border border-primary-100 dark:border-primary-900/40">
                <p class="text-2xl font-extrabold text-primary-700 dark:text-primary-300">
                    {{ $stats['total'] > 0 ? round($stats['present'] / $stats['total'] * 100) : 0 }}%
                </p>
                <p class="text-xs text-primary-600 dark:text-primary-400 font-semibold mt-1">Attendance Rate</p>
            </div>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Meeting Roles --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 sm:p-8 transition-colors">
            <h2 class="text-base font-bold text-slate-900 dark:text-white mb-5 pb-3 border-b border-slate-100 dark:border-slate-800">Meeting Roles</h2>
            @if($meeting->roles->isEmpty())
                <p class="text-slate-400 dark:text-slate-500 text-sm">No roles assigned.</p>
            @else
            <div class="space-y-3">
                @foreach($meeting->roles->sortBy('roleType.sort_order') as $role)
                <div class="flex items-center justify-between py-2 border-b border-slate-50 dark:border-slate-800/60 last:border-0">
                    <span class="text-xs sm:text-sm font-semibold text-slate-500 dark:text-slate-400 w-40">{{ $role->roleType->name }}</span>
                    <span class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">{{ $role->user->name }}</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Prepared Speakers --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 sm:p-8 transition-colors">
            <h2 class="text-base font-bold text-slate-900 dark:text-white mb-5 pb-3 border-b border-slate-100 dark:border-slate-800">Prepared Speakers</h2>
            @if($meeting->speakers->isEmpty())
                <p class="text-slate-400 dark:text-slate-500 text-sm">No prepared speakers.</p>
            @else
            <div class="space-y-3">
                @foreach($meeting->speakers as $speaker)
                <div class="p-4 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-200/60 dark:border-slate-700/50">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-bold text-slate-900 dark:text-white text-sm">{{ $speaker->user->name }}</p>
                            @if($speaker->topic) <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $speaker->topic }}</p> @endif
                            @if($speaker->speech_type) <p class="text-xs text-primary-600 dark:text-primary-400 font-semibold mt-1">{{ $speaker->speech_type }}</p> @endif
                        </div>
                        @if($speaker->evaluation)
                        <div class="text-right">
                            <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Evaluator</p>
                            <p class="text-xs font-bold text-emerald-600 dark:text-emerald-400">{{ $speaker->evaluation->evaluator->name }}</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- TTM Speakers --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 sm:p-8 transition-colors">
            <h2 class="text-base font-bold text-slate-900 dark:text-white mb-5 pb-3 border-b border-slate-100 dark:border-slate-800">Table Topics Speakers</h2>
            @if($meeting->ttmSpeakers->isEmpty())
                <p class="text-slate-400 dark:text-slate-500 text-sm">No TTM speakers.</p>
            @else
            <div class="space-y-3">
                @foreach($meeting->ttmSpeakers as $ttm)
                <div class="flex items-center gap-3.5 py-2 border-b border-slate-50 dark:border-slate-800/60 last:border-0">
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

        {{-- Attendance List --}}
        @if(!$meeting->attendance->isEmpty())
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 sm:p-8 transition-colors">
            <h2 class="text-base font-bold text-slate-900 dark:text-white mb-5 pb-3 border-b border-slate-100 dark:border-slate-800">Attendance Roll</h2>
            <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
                @foreach($meeting->attendance->sortBy('user.name') as $att)
                <div class="flex items-center justify-between py-2 border-b border-slate-50 dark:border-slate-800/60 last:border-0">
                    <p class="text-xs sm:text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $att->user->name }}</p>
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full {{ $att->statusColor() }}">
                        {{ ucfirst($att->status) }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>

    @if($meeting->notes)
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 sm:p-8 transition-colors">
        <h2 class="text-base font-bold text-slate-900 dark:text-white mb-3">Meeting Notes & Takeaways</h2>
        <p class="text-slate-600 dark:text-slate-300 text-sm whitespace-pre-wrap leading-relaxed">{{ $meeting->notes }}</p>
    </div>
    @endif

</div>
