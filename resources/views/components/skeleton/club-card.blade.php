@props(['count' => 6])

@for ($i = 0; $i < $count; $i++)
<div class="animate-pulse bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 sm:p-7 flex flex-col justify-between">
    <div>
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-slate-200 dark:bg-slate-800 animate-shimmer flex-shrink-0"></div>
                <div class="space-y-1.5">
                    <div class="h-4 w-32 bg-slate-200 dark:bg-slate-800 rounded animate-shimmer"></div>
                    <div class="h-3 w-16 bg-slate-150 dark:bg-slate-850 rounded animate-shimmer"></div>
                </div>
            </div>
            <div class="h-6 w-16 rounded-full bg-slate-200 dark:bg-slate-800 animate-shimmer"></div>
        </div>

        <div class="space-y-1.5 mb-5">
            <div class="h-3 w-full bg-slate-150 dark:bg-slate-850 rounded animate-shimmer"></div>
            <div class="h-3 w-3/4 bg-slate-150 dark:bg-slate-850 rounded animate-shimmer"></div>
        </div>

        <div class="grid grid-cols-2 gap-2.5 mb-5">
            <div class="bg-slate-50 dark:bg-slate-800/60 rounded-2xl p-3 flex flex-col items-center gap-1.5">
                <div class="h-6 w-12 bg-slate-200 dark:bg-slate-700 rounded animate-shimmer"></div>
                <div class="h-2.5 w-20 bg-slate-150 dark:bg-slate-750 rounded animate-shimmer"></div>
            </div>
            <div class="bg-slate-50 dark:bg-slate-800/60 rounded-2xl p-3 flex flex-col items-center gap-1.5">
                <div class="h-4 w-16 bg-slate-200 dark:bg-slate-700 rounded animate-shimmer"></div>
                <div class="h-2.5 w-16 bg-slate-150 dark:bg-slate-750 rounded animate-shimmer"></div>
            </div>
        </div>
    </div>

    <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex gap-2">
        <div class="flex-1 h-9 bg-slate-200 dark:bg-slate-800 rounded-2xl animate-shimmer"></div>
        <div class="w-9 h-9 bg-slate-200 dark:bg-slate-800 rounded-2xl animate-shimmer"></div>
    </div>
</div>
@endfor
