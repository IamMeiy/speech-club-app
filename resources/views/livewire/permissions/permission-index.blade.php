<div class="p-6 lg:p-8 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">System Permissions</h1>
            <p class="text-gray-500 text-sm mt-1">Overview of all system permissions registered across modules</p>
        </div>
    </div>

    <div class="space-y-6">
        @forelse($permissions as $group => $perms)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center gap-3 mb-4 pb-3 border-b border-gray-50">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs">
                    {{ strtoupper(substr($group, 0, 2)) }}
                </div>
                <div>
                    <h2 class="font-bold text-gray-900 text-base">{{ ucfirst(str_replace('-', ' ', $group)) }} Module</h2>
                    <p class="text-xs text-gray-400">{{ $perms->count() }} permissions</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($perms as $perm)
                <div class="p-3 bg-gray-50 rounded-xl border border-gray-100/80 flex items-center gap-3">
                    <svg class="w-4 h-4 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-semibold text-gray-800 font-mono">{{ $perm->name }}</p>
                        <p class="text-[11px] text-gray-400">Guard: {{ $perm->guard_name }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @empty
        <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center text-gray-400">
            No permissions found.
        </div>
        @endforelse
    </div>
</div>
