<div class="space-y-6 max-w-7xl mx-auto">
    {{-- Header Skeleton --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Clubs Directory</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">Manage registered speech clubs and branches</p>
        </div>
        @can('clubs.create')
        <div class="h-10 w-32 bg-slate-200 dark:bg-slate-800 rounded-2xl animate-shimmer"></div>
        @endcan
    </div>

    {{-- Filter Bar Skeleton --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-4 transition-colors">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 h-10 bg-slate-100 dark:bg-slate-800 rounded-xl animate-shimmer"></div>
            <div class="h-10 w-40 bg-slate-100 dark:bg-slate-800 rounded-xl animate-shimmer"></div>
        </div>
    </div>

    {{-- Cards Skeleton Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        <x-skeleton.club-card :count="6" />
    </div>
</div>
