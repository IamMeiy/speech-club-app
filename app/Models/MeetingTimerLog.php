<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingTimerLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_id',
        'speaker_type',
        'reference_id',
        'user_id',
        'allotted_time',
        'time_taken',
        'status',
        'notes',
    ];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get human label and color classes for qualification status.
     */
    public function statusBadge(): array
    {
        return match ($this->status) {
            'within_time'  => ['label' => 'Within Time', 'class' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-200/80 dark:border-emerald-800'],
            'over_time'    => ['label' => 'Over Time', 'class' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border-rose-200/80 dark:border-rose-800'],
            'under_time'   => ['label' => 'Under Time', 'class' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border-amber-200/80 dark:border-amber-800'],
            'disqualified' => ['label' => 'Disqualified', 'class' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-slate-300 dark:border-slate-700'],
            default        => ['label' => 'Recorded', 'class' => 'bg-slate-50 text-slate-600 dark:bg-slate-800 dark:text-slate-300 border-slate-200'],
        };
    }
}
