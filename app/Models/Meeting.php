<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_id',
        'meeting_number',
        'meeting_date',
        'start_time',
        'end_time',
        'theme',
        'venue',
        'status',
        'notes',
        'word_of_the_day',
        'word_part_of_speech',
        'word_definition',
        'word_example_sentence',
        'created_by',
    ];

    protected $casts = [
        'meeting_date' => 'date',
    ];

    /**
     * Club this meeting belongs to.
     */
    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * User who created the meeting.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Fixed role assignments for this meeting.
     */
    public function roles(): HasMany
    {
        return $this->hasMany(MeetingRole::class)->with(['roleType:id,name,slug,sort_order', 'user:id,name,email']);
    }

    /**
     * Prepared speakers for this meeting.
     */
    public function speakers(): HasMany
    {
        return $this->hasMany(MeetingSpeaker::class)->orderBy('slot');
    }

    /**
     * TTM speakers for this meeting.
     */
    public function ttmSpeakers(): HasMany
    {
        return $this->hasMany(MeetingTtmSpeaker::class)->orderBy('slot');
    }

    /**
     * Evaluations for this meeting.
     */
    public function evaluations(): HasMany
    {
        return $this->hasMany(MeetingEvaluation::class)->with(['speaker.user:id,name,email', 'evaluator:id,name,email']);
    }

    /**
     * Attendance records for this meeting.
     */
    public function attendance(): HasMany
    {
        return $this->hasMany(MeetingAttendance::class)->with('user:id,name,email');
    }

    /**
     * Ah-Counter tracking logs for this meeting.
     */
    public function ahCounterLogs(): HasMany
    {
        return $this->hasMany(MeetingAhCounterLog::class)->with('user:id,name,email');
    }

    /**
     * Grammarian tracking logs for this meeting.
     */
    public function grammarianLogs(): HasMany
    {
        return $this->hasMany(MeetingGrammarianLog::class)->with('user:id,name,email');
    }

    /**
     * Timer tracking logs for this meeting.
     */
    public function timerLogs(): HasMany
    {
        return $this->hasMany(MeetingTimerLog::class)->with('user:id,name,email');
    }

    /**
     * Whether this meeting has a Word of the Day defined.
     */
    public function hasWordOfTheDay(): bool
    {
        return ! empty($this->word_of_the_day);
    }

    /**
     * Formatted meeting schedule timing string.
     */
    public function formattedTime(): ?string
    {
        if ($this->start_time && $this->end_time) {
            return "{$this->start_time} - {$this->end_time}";
        }
        return $this->start_time ?: null;
    }

    /**
     * Status display labels.
     */
    public static function statuses(): array
    {
        return [
            'draft'     => 'Draft',
            'scheduled' => 'Scheduled',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];
    }

    /**
     * Status color classes for UI badges.
     */
    public function statusColor(): string
    {
        return match ($this->status) {
            'draft'     => 'bg-gray-100 text-gray-700',
            'scheduled' => 'bg-blue-100 text-blue-700',
            'completed' => 'bg-green-100 text-green-700',
            'cancelled' => 'bg-red-100 text-red-700',
            default     => 'bg-gray-100 text-gray-700',
        };
    }

    /**
     * Check if meeting is upcoming.
     */
    public function isUpcoming(): bool
    {
        return in_array($this->status, ['draft', 'scheduled'])
            && $this->meeting_date >= now()->toDateString();
    }

    /**
     * Scope: meetings for a specific club.
     */
    public function scopeForClub($query, int $clubId)
    {
        return $query->where('club_id', $clubId);
    }

    /**
     * Scope: upcoming meetings.
     */
    public function scopeUpcoming($query)
    {
        return $query->whereIn('status', ['draft', 'scheduled'])
            ->where('meeting_date', '>=', now()->toDateString())
            ->orderBy('meeting_date');
    }

    /**
     * Scope: past completed meetings.
     */
    public function scopePast($query)
    {
        return $query->where('status', 'completed')
            ->orderByDesc('meeting_date');
    }
}
