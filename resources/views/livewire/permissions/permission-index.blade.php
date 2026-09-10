<div class="p-6 lg:p-8 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">System Permissions</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Overview of all system permissions registered across modules</p>
        </div>
    </div>

    <div class="space-y-6">
        @forelse($permissions as $group => $perms)
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6">
            <div class="flex items-center gap-3.5 mb-5 pb-4 border-b border-slate-100 dark:border-slate-800">
                <div class="w-9 h-9 rounded-xl bg-primary-50 dark:bg-primary-950/60 text-primary-600 dark:text-primary-400 flex items-center justify-center font-bold text-xs ring-1 ring-primary-500/10 dark:ring-primary-400/20">
                    {{ strtoupper(substr($group, 0, 2)) }}
                </div>
                <div>
                    <h2 class="font-bold text-slate-900 dark:text-white text-base">{{ ucfirst(str_replace('-', ' ', $group)) }} Module</h2>
                    <p class="text-xs text-slate-400 dark:text-slate-500">{{ $perms->count() }} permissions registered</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($perms as $perm)
                <div class="p-3.5 bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-slate-200/70 dark:border-slate-800/80 flex items-center gap-3">
                    <svg class="w-4 h-4 text-primary-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-semibold text-slate-900 dark:text-white font-mono truncate">{{ $perm->name }}</p>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Guard: {{ $perm->guard_name }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @empty
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-12 text-center text-slate-400 dark:text-slate-500">
            No permissions registered.
        </div>
        @endforelse
    </div>
</div>
