<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Club extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'status',
        'logo',
        'meeting_day',
        'meeting_time',
        'location',
        'timezone',
    ];

    /**
     * Users belonging to this club (via user_clubs pivot).
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_clubs')->withTimestamps();
    }

    /**
     * Meetings belonging to this club.
     */
    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class);
    }

    /**
     * Active members of this club (non-deleted, active users).
     */
    public function activeUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_clubs')
            ->withTimestamps()
            ->where('users.status', 'active')
            ->whereNull('users.deleted_at');
    }

    /**
     * Upcoming meetings for this club.
     */
    public function upcomingMeetings(): HasMany
    {
        return $this->hasMany(Meeting::class)
            ->whereIn('status', ['draft', 'scheduled'])
            ->where('meeting_date', '>=', now()->toDateString())
            ->orderBy('meeting_date');
    }

    /**
     * Completed meetings for this club.
     */
    public function previousMeetings(): HasMany
    {
        return $this->hasMany(Meeting::class)
            ->where('status', 'completed')
            ->orderByDesc('meeting_date');
    }

    /**
     * Check if the club is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Next upcoming meeting number for auto-incrementing within club.
     */
    public function nextMeetingNumber(): int
    {
        return ($this->meetings()->max('meeting_number') ?? 0) + 1;
    }
}
