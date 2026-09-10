<div class="space-y-6 max-w-7xl mx-auto">
    {{-- Header Skeleton --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Global Users</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">Multi-club administrators and regional managers</p>
        </div>
        @can('global-users.create')
        <div class="h-10 w-36 bg-slate-200 dark:bg-slate-800 rounded-2xl animate-shimmer"></div>
        @endcan
    </div>

    {{-- Filter Bar Skeleton --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-4 transition-colors">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 h-10 bg-slate-100 dark:bg-slate-800 rounded-xl animate-shimmer"></div>
            <div class="h-10 w-44 bg-slate-100 dark:bg-slate-800 rounded-xl animate-shimmer"></div>
        </div>
    </div>

    {{-- Table Skeleton --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden transition-colors">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40">
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Administrator</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Assigned Clubs</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                        <th class="text-right px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <x-skeleton.global-user-rows :rows="6" />
                </tbody>
            </table>
        </div>
    </div>
</div>
