<div class="p-6 lg:p-8">
    <div class="max-w-xl mx-auto">
        <div class="flex items-center gap-4 mb-8">
            <a href="{{ route('clubs.index') }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-xl transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h1 class="text-2xl font-bold text-gray-900">{{ $club->name }} — Roles</h1>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <p class="text-sm text-gray-600 mb-4">Club roles assigned to this club:</p>
            <div class="space-y-2">
                @foreach($clubRoles as $role)
                <div class="flex items-center gap-3 py-2 border-b border-gray-50 last:border-0">
                    <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <p class="text-sm font-medium text-gray-800">{{ $role->name }}</p>
                    <span class="ml-auto text-xs text-gray-400">{{ $role->users_count ?? 0 }} users</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
