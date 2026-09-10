<div class="p-6 lg:p-8 space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('meetings.show', $meeting) }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-xl transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Attendance — Meeting #{{ $meeting->meeting_number }}</h1>
            <p class="text-gray-500 text-sm mt-1">{{ $meeting->club->name ?? '' }} · {{ $meeting->meeting_date->format('d F Y') }}</p>
        </div>
    </div>

    @error('attendance')
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-600">{{ $message }}</div>
    @enderror

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Attendance Table --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-base font-semibold text-gray-900">Member Attendance</h2>
                <button wire:click="saveAttendance" wire:loading.attr="disabled"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors disabled:opacity-60">
                    <span wire:loading.remove>Save Attendance</span>
                    <span wire:loading>Saving…</span>
                </button>
            </div>

            <div class="space-y-2">
                @foreach($members as $member)
                @php $userId = $member->id; @endphp
                <div class="flex items-center justify-between py-3 border-b border-gray-50 last:border-0">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center flex-shrink-0">
                            <span class="text-white text-xs font-semibold">{{ strtoupper(substr($member->name, 0, 2)) }}</span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900 text-sm">{{ $member->name }}</p>
                            <p class="text-xs text-gray-400">{{ $member->roles->first()?->name ?? '' }}</p>
                        </div>
                    </div>
                    <select wire:model="attendance.{{ $userId }}"
                            class="px-3 py-1.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
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
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-4">Role Replacements</h2>
            <p class="text-xs text-gray-500 mb-4">If a role holder is absent, assign a replacement here.</p>

            @if($meeting->roles->isEmpty())
                <p class="text-gray-400 text-sm">No roles assigned for this meeting.</p>
            @else
            <div class="space-y-4">
                @foreach($meeting->roles->sortBy('roleType.sort_order') as $role)
                <div class="p-3 bg-gray-50 rounded-xl">
                    <p class="text-xs font-semibold text-gray-500 mb-1">{{ $role->roleType->name }}</p>
                    <p class="text-sm text-gray-800 mb-2">
                        <span class="font-medium">{{ $role->user->name }}</span>
                        @if(($attendance[$role->user_id] ?? '') === 'absent' || ($attendance[$role->user_id] ?? '') === 'excused')
                            <span class="ml-1 text-xs font-medium text-red-600 bg-red-100 px-1.5 py-0.5 rounded">Absent</span>
                        @endif
                    </p>
                    @can('meeting-roles.manage')
                    <div class="flex gap-2">
                        <select wire:model="roleReplacements.{{ $role->id }}"
                                class="flex-1 px-2 py-1.5 border border-gray-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                            <option value="">— Replace with —</option>
                            @foreach($members as $m)
                                @if($m->id !== $role->user_id)
                                <option value="{{ $m->id }}">{{ $m->name }}</option>
                                @endif
                            @endforeach
                        </select>
                        <button wire:click="replaceRole({{ $role->id }})"
                                class="px-2 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs rounded-lg transition-colors">
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
