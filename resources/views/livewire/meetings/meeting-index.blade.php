<div class="space-y-6 max-w-7xl mx-auto">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Meetings</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-0.5">
                @if($club) {{ $club->name }} @else All Clubs @endif
            </p>
        </div>
        @can('meetings.create')
        <a href="{{ route('meetings.create') }}"
           class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-2xl transition-all shadow-md shadow-primary-600/20 active:scale-[0.98]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            Create Meeting
        </a>
        @endcan
    </div>

    {{-- Filters --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-4 transition-colors">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <div class="relative">
                    <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search meetings by theme, number…"
                           class="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-colors">
                </div>
            </div>
            <div class="flex gap-1.5 p-1 bg-slate-100 dark:bg-slate-800 rounded-2xl">
                <button wire:click="$set('filter', 'all')"
                        class="px-3.5 py-1.5 text-xs font-semibold rounded-xl transition-all {{ $filter === 'all' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                    All
                </button>
                <button wire:click="$set('filter', 'upcoming')"
                        class="px-3.5 py-1.5 text-xs font-semibold rounded-xl transition-all {{ $filter === 'upcoming' ? 'bg-primary-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                    Upcoming
                </button>
                <button wire:click="$set('filter', 'past')"
                        class="px-3.5 py-1.5 text-xs font-semibold rounded-xl transition-all {{ $filter === 'past' ? 'bg-slate-700 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                    Past
                </button>
            </div>
            <select wire:model.live="status" class="px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm font-medium text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">All Statuses</option>
                <option value="draft">Draft</option>
                <option value="scheduled">Scheduled</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden transition-colors">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40">
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">#</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Theme</th>
                        @if(!$club)
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Club</th>
                        @endif
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                        <th class="text-right px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>

                {{-- Skeleton Loading state during search, filter, and pagination --}}
                <tbody wire:loading.table-row-group wire:target="search, filter, status, previousPage, nextPage, gotoPage" style="display: none;">
                    <x-skeleton.meeting-rows :rows="8" :show-club="!$club" />
                </tbody>

                {{-- Real Data Body --}}
                <tbody wire:loading.remove wire:target="search, filter, status, previousPage, nextPage, gotoPage" class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($meetings as $meeting)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors">
                        <td class="px-6 py-4">
                            <span class="font-extrabold text-slate-900 dark:text-white text-sm">#{{ $meeting->meeting_number }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 bg-primary-50 dark:bg-primary-950/60 border border-primary-100 dark:border-primary-900/50 rounded-2xl flex flex-col items-center justify-center flex-shrink-0">
                                    <span class="text-primary-600 dark:text-primary-400 text-[10px] font-bold uppercase">{{ $meeting->meeting_date->format('M') }}</span>
                                    <span class="text-primary-800 dark:text-primary-200 text-sm font-extrabold leading-none">{{ $meeting->meeting_date->format('d') }}</span>
                                </div>
                                <div>
                                    <p class="text-xs sm:text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $meeting->meeting_date->format('D, d M Y') }}</p>
                                    @if($meeting->venue)
                                    <p class="text-xs text-slate-400 dark:text-slate-500">{{ $meeting->venue }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-xs sm:text-sm font-medium text-slate-700 dark:text-slate-300">{{ $meeting->theme ?: '—' }}</td>
                        @if(!$club)
                        <td class="px-6 py-4 text-xs sm:text-sm font-medium text-slate-600 dark:text-slate-400">{{ $meeting->club->name }}</td>
                        @endif
                        <td class="px-6 py-4">
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $meeting->statusColor() }}">
                                {{ ucfirst($meeting->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('meetings.show', $meeting) }}"
                                   class="p-2 text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-950/40 rounded-xl transition-colors"
                                   title="View Details">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                @can('meetings.update')
                                <a href="{{ route('meetings.edit', $meeting) }}"
                                   class="p-2 text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-950/40 rounded-xl transition-colors"
                                   title="Edit Meeting">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                @endcan
                                @can('attendance.manage')
                                <a href="{{ route('meetings.attendance', $meeting) }}"
                                   class="p-2 text-slate-400 hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 rounded-xl transition-colors"
                                   title="Take Attendance">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </a>
                                @endcan
                                @can('meetings.delete')
                                <button x-data @click="$confirm({
                                            title: 'Delete Meeting',
                                            message: 'Are you sure you want to delete meeting #{{ $meeting->meeting_number }}? This cannot be undone.',
                                            type: 'danger',
                                            confirmText: 'Delete Meeting',
                                            onConfirm: () => $wire.deleteMeeting({{ $meeting->id }})
                                        })"
                                        class="p-2 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-xl transition-colors"
                                        title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center">
                            <div class="w-12 h-12 bg-slate-100 dark:bg-slate-800 rounded-2xl flex items-center justify-center mx-auto mb-4 text-slate-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">No meetings found</p>
                            @can('meetings.create')
                            <a href="{{ route('meetings.create') }}" class="mt-2 inline-flex items-center text-sm text-primary-600 dark:text-primary-400 font-semibold hover:underline">
                                Create the first meeting →
                            </a>
                            @endcan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($meetings->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800">
            {{ $meetings->links() }}
        </div>
        @endif
    </div>

</div>
