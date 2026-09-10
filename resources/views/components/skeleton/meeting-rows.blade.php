@props(['rows' => 6, 'showClub' => true])

@for ($i = 0; $i < $rows; $i++)
<tr class="animate-pulse border-b border-slate-100 dark:border-slate-800/60 last:border-0">
    {{-- # Meeting Number --}}
    <td class="px-6 py-4">
        <div class="h-6 w-10 bg-slate-200 dark:bg-slate-800 rounded-lg animate-shimmer"></div>
    </td>

    {{-- Date & Venue --}}
    <td class="px-6 py-4">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-slate-200 dark:bg-slate-800 rounded-2xl animate-shimmer flex-shrink-0"></div>
            <div class="space-y-1.5">
                <div class="h-3.5 w-28 bg-slate-200 dark:bg-slate-800 rounded animate-shimmer"></div>
                <div class="h-2.5 w-20 bg-slate-150 dark:bg-slate-850 rounded animate-shimmer"></div>
            </div>
        </div>
    </td>

    {{-- Theme --}}
    <td class="px-6 py-4">
        <div class="h-3.5 w-44 sm:w-56 bg-slate-200 dark:bg-slate-800 rounded animate-shimmer"></div>
    </td>

    {{-- Club (conditional) --}}
    @if($showClub)
    <td class="px-6 py-4">
        <div class="h-3.5 w-28 bg-slate-200 dark:bg-slate-800 rounded animate-shimmer"></div>
    </td>
    @endif

    {{-- Status Badge --}}
    <td class="px-6 py-4">
        <div class="h-6 w-20 bg-slate-200 dark:bg-slate-800 rounded-full animate-shimmer"></div>
    </td>

    {{-- Actions --}}
    <td class="px-6 py-4 text-right">
        <div class="flex items-center justify-end gap-1.5">
            <div class="w-8 h-8 rounded-xl bg-slate-200 dark:bg-slate-800 animate-shimmer"></div>
            <div class="w-8 h-8 rounded-xl bg-slate-200 dark:bg-slate-800 animate-shimmer"></div>
            <div class="w-8 h-8 rounded-xl bg-slate-200 dark:bg-slate-800 animate-shimmer"></div>
        </div>
    </td>
</tr>
@endfor
