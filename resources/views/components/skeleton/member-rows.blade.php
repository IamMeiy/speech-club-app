@props(['rows' => 6])

@for ($i = 0; $i < $rows; $i++)
<tr class="animate-pulse border-b border-slate-100 dark:border-slate-800/60 last:border-0">
    {{-- Member Avatar & Name --}}
    <td class="px-6 py-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-slate-200 dark:bg-slate-800 animate-shimmer flex-shrink-0"></div>
            <div class="space-y-1.5">
                <div class="h-3.5 w-32 bg-slate-200 dark:bg-slate-800 rounded animate-shimmer"></div>
                <div class="h-2.5 w-20 bg-slate-150 dark:bg-slate-850 rounded animate-shimmer"></div>
            </div>
        </div>
    </td>

    {{-- Email --}}
    <td class="px-6 py-4">
        <div class="h-3.5 w-40 bg-slate-200 dark:bg-slate-800 rounded animate-shimmer"></div>
    </td>

    {{-- Club Role --}}
    <td class="px-6 py-4">
        <div class="h-6 w-24 bg-slate-200 dark:bg-slate-800 rounded-xl animate-shimmer"></div>
    </td>

    {{-- Status --}}
    <td class="px-6 py-4">
        <div class="h-6 w-16 bg-slate-200 dark:bg-slate-800 rounded-full animate-shimmer"></div>
    </td>

    {{-- Actions --}}
    <td class="px-6 py-4 text-right">
        <div class="flex items-center justify-end gap-1.5">
            <div class="w-8 h-8 rounded-xl bg-slate-200 dark:bg-slate-800 animate-shimmer"></div>
            <div class="w-8 h-8 rounded-xl bg-slate-200 dark:bg-slate-800 animate-shimmer"></div>
        </div>
    </td>
</tr>
@endfor
