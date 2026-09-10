<div class="space-y-6 max-w-7xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Clubs Directory</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">Manage registered speech clubs and branches</p>
        </div>
        @can('clubs.create')
        <a href="{{ route('clubs.create') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-2xl transition-all shadow-md shadow-primary-600/20 active:scale-[0.98]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            New Club
        </a>
        @endcan
    </div>

    {{-- Filters --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-4 flex flex-col sm:flex-row gap-3 transition-colors">
        <div class="relative flex-1">
            <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search clubs by name or code…"
                   class="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
        </div>
        <select wire:model.live="status" class="px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm font-medium text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
            <option value="">All Statuses</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse($clubs as $club)
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 sm:p-7 hover:shadow-md transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 bg-gradient-to-br from-primary-500 to-primary-700 rounded-2xl flex items-center justify-center text-white font-extrabold text-sm shadow-sm shadow-primary-500/20 flex-shrink-0">
                            {{ strtoupper(substr($club->code, 0, 2)) }}
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-bold text-slate-900 dark:text-white text-base truncate">{{ $club->name }}</h3>
                            <span class="text-xs text-slate-400 dark:text-slate-500 font-mono">{{ $club->code }}</span>
                        </div>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $club->status === 'active' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                        {{ ucfirst($club->status) }}
                    </span>
                </div>

                @if($club->description)
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-5 line-clamp-2 leading-relaxed">{{ $club->description }}</p>
                @endif

                <div class="grid grid-cols-2 gap-2.5 mb-5">
                    <div class="bg-slate-50 dark:bg-slate-800/60 rounded-2xl p-3 text-center">
                        <p class="text-xl font-extrabold text-slate-900 dark:text-white">{{ $club->users_count }}</p>
                        <p class="text-[11px] font-medium text-slate-400 dark:text-slate-500">Enrolled Members</p>
                    </div>
                    @if($club->meeting_day)
                    <div class="bg-slate-50 dark:bg-slate-800/60 rounded-2xl p-3 text-center">
                        <p class="text-sm font-bold text-slate-800 dark:text-slate-200 truncate">{{ $club->meeting_day }}</p>
                        <p class="text-[11px] font-medium text-slate-400 dark:text-slate-500">Meeting Day</p>
                    </div>
                    @else
                    <div class="bg-slate-50 dark:bg-slate-800/60 rounded-2xl p-3 text-center">
                        <p class="text-sm font-bold text-slate-800 dark:text-slate-200">—</p>
                        <p class="text-[11px] font-medium text-slate-400 dark:text-slate-500">Meeting Day</p>
                    </div>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                @can('clubs.update')
                <a href="{{ route('clubs.edit', $club) }}" class="flex-1 py-2 text-center border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs sm:text-sm font-semibold rounded-2xl transition-colors shadow-sm">
                    Edit Club
                </a>
                @endcan
                @can('clubs.delete')
                <button x-data @click="if(confirm('Delete {{ $club->name }}?')) $wire.deleteClub({{ $club->id }})"
                        class="p-2 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-2xl border border-slate-200/80 dark:border-slate-800 transition-colors" title="Delete Club">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
                @endcan
            </div>
        </div>
        @empty
        <div class="col-span-full py-16 text-center text-slate-400">
            <p class="text-sm font-medium">No clubs found matching your search.</p>
        </div>
        @endforelse
    </div>

    @if($clubs->hasPages())
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 p-4">
        {{ $clubs->links() }}
    </div>
    @endif
</div>
