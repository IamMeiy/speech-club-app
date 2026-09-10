@props(['cols' => 5, 'rows' => 6])

@for ($i = 0; $i < $rows; $i++)
<tr class="animate-pulse border-b border-slate-100 dark:border-slate-800/60 last:border-0">
    @for ($c = 0; $c < $cols; $c++)
    <td class="px-6 py-4">
        @if ($c === 0)
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-2xl bg-slate-200 dark:bg-slate-800 animate-shimmer flex-shrink-0"></div>
            <div class="space-y-1.5 flex-1">
                <div class="h-3.5 w-28 bg-slate-200 dark:bg-slate-800 rounded animate-shimmer"></div>
                <div class="h-2.5 w-16 bg-slate-150 dark:bg-slate-850 rounded animate-shimmer"></div>
            </div>
        </div>
        @elseif ($c === $cols - 1)
        <div class="flex items-center justify-end gap-1.5">
            <div class="w-8 h-8 rounded-xl bg-slate-200 dark:bg-slate-800 animate-shimmer"></div>
            <div class="w-8 h-8 rounded-xl bg-slate-200 dark:bg-slate-800 animate-shimmer"></div>
        </div>
        @else
        <div class="h-3.5 w-{{ [20, 28, 36, 24][$c % 4] }} bg-slate-200 dark:bg-slate-800 rounded animate-shimmer"></div>
        @endif
    </td>
    @endfor
</tr>
@endfor
