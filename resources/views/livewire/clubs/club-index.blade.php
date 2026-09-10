<div class="p-6 lg:p-8 space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Clubs</h1>
        @can('clubs.create')
        <a href="{{ route('clubs.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Club
        </a>
        @endcan
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex gap-3">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search clubs…"
               class="flex-1 px-4 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <select wire:model.live="status" class="px-4 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">All Statuses</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse($clubs as $club)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center">
                        <span class="text-white font-bold text-sm">{{ strtoupper(substr($club->code, 0, 2)) }}</span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">{{ $club->name }}</h3>
                        <span class="text-xs text-gray-500">{{ $club->code }}</span>
                    </div>
                </div>
                <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $club->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                    {{ ucfirst($club->status) }}
                </span>
            </div>

            @if($club->description)
            <p class="text-xs text-gray-500 mb-4">{{ Str::limit($club->description, 80) }}</p>
            @endif

            <div class="grid grid-cols-2 gap-3 mb-4">
                <div class="bg-gray-50 rounded-xl p-3 text-center">
                    <p class="text-lg font-bold text-gray-900">{{ $club->users_count }}</p>
                    <p class="text-xs text-gray-400">Members</p>
                </div>
                @if($club->meeting_day)
                <div class="bg-gray-50 rounded-xl p-3 text-center">
                    <p class="text-sm font-semibold text-gray-700">{{ $club->meeting_day }}</p>
                    <p class="text-xs text-gray-400">Meet Day</p>
                </div>
                @endif
            </div>

            <div class="flex items-center gap-2">
                @can('clubs.update')
                <a href="{{ route('clubs.edit', $club) }}" class="flex-1 py-2 text-center border border-gray-200 text-gray-700 text-sm font-medium rounded-xl hover:bg-gray-50 transition-colors">
                    Edit
                </a>
                @endcan
                @can('clubs.delete')
                <button x-data @click="if(confirm('Delete {{ $club->name }}?')) $wire.deleteClub({{ $club->id }})"
                        class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
                @endcan
            </div>
        </div>
        @empty
        <div class="col-span-3 py-16 text-center text-gray-400">
            <p class="text-sm">No clubs found.</p>
        </div>
        @endforelse
    </div>

    @if($clubs->hasPages())
    <div>{{ $clubs->links() }}</div>
    @endif
</div>
