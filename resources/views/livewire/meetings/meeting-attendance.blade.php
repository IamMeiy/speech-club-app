<div class="max-w-6xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('meetings.show', $meeting) }}" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-white dark:hover:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Attendance — Meeting #{{ $meeting->meeting_number }}</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ $meeting->club->name ?? '' }} · {{ $meeting->meeting_date->format('d F Y') }}</p>
        </div>
    </div>

    @error('attendance')
    <div class="bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/50 rounded-2xl p-4 text-xs sm:text-sm text-rose-600 dark:text-rose-400 font-medium">{{ $message }}</div>
    @enderror

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Attendance Table --}}
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 sm:p-8 transition-colors">
            <div class="flex items-center justify-between mb-6 pb-3 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Member Attendance</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Mark member check-ins for the session</p>
                </div>
                <button wire:click="saveAttendance" wire:loading.attr="disabled"
                        class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-xs sm:text-sm font-semibold rounded-2xl transition-all shadow-md shadow-primary-600/20 disabled:opacity-60 active:scale-[0.98]">
                    <span wire:loading.remove>Save Attendance</span>
                    <span wire:loading class="flex items-center gap-1.5">
                        <svg class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        Saving…
                    </span>
                </button>
            </div>

            <div class="space-y-3">
                @foreach($members as $member)
                @php $userId = $member->id; @endphp
                <div class="flex items-center justify-between py-3 border-b border-slate-100 dark:border-slate-800/60 last:border-0">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center flex-shrink-0 shadow-sm text-white text-xs font-bold">
                            {{ strtoupper(substr($member->name, 0, 2)) }}
                        </div>
                        <div>
                            <p class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm">{{ $member->name }}</p>
                            <p class="text-[11px] text-slate-400 dark:text-slate-500">{{ $member->roles->first()?->name ?? 'Member' }}</p>
                        </div>
                    </div>
                    <select wire:model="attendance.{{ $userId }}"
                            class="px-3.5 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm font-medium text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500 shadow-sm">
                        <option value="present">✅ Present</option>
                        <option value="absent">❌ Absent</option>
                        <option value="late">⏰ Late</option>
                        <option value="excused">📋 Excused</option>
                    </select>
                </div>
                @endforeach
            </div>

        </div>

        {{-- Role Replacements for Absent Members --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 sm:p-8 transition-colors">
            <h2 class="text-base font-bold text-slate-900 dark:text-white mb-1">Role Replacements</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-5">Assign an on-the-spot substitute if a role taker is absent.</p>

            @if($meeting->roles->isEmpty())
                <p class="text-slate-400 dark:text-slate-500 text-xs sm:text-sm">No roles assigned for this meeting.</p>
            @else
            <div class="space-y-4">
                @foreach($meeting->roles->sortBy('roleType.sort_order') as $role)
                <div class="p-4 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-200/60 dark:border-slate-700/50">
                    <p class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">{{ $role->roleType->name }}</p>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-200">{{ $role->user->name }}</span>
                        @if(($attendance[$role->user_id] ?? '') === 'absent' || ($attendance[$role->user_id] ?? '') === 'excused')
                            <span class="text-[10px] font-bold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/60 px-2 py-0.5 rounded-full">Absent</span>
                        @endif
                    </div>
                    @can('meeting-roles.manage')
                    <div class="flex gap-2">
                        <select wire:model="roleReplacements.{{ $role->id }}"
                                class="flex-1 px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="">— Substitute with —</option>
                            @foreach($members as $m)
                                @if($m->id !== $role->user_id)
                                <option value="{{ $m->id }}">{{ $m->name }}</option>
                                @endif
                            @endforeach
                        </select>
                        <button wire:click="replaceRole({{ $role->id }})"
                                class="px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl transition-colors shadow-sm">
                            ✓
                        </button>
                    </div>
                    @endcan
                </div>
                @endforeach
            </div>
            @endif
        </div>

    </div>

</div>
