<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    /**
     * Clubs this user belongs to (via user_clubs pivot).
     */
    public function clubs(): BelongsToMany
    {
        return $this->belongsToMany(Club::class, 'user_clubs')->withTimestamps();
    }

    /**
     * Meetings created by this user.
     */
    public function createdMeetings(): HasMany
    {
        return $this->hasMany(Meeting::class, 'created_by');
    }

    /**
     * Fixed meeting role assignments for this user.
     */
    public function meetingRoles(): HasMany
    {
        return $this->hasMany(MeetingRole::class);
    }

    /**
     * Prepared speaker slots for this user.
     */
    public function meetingSpeakerSlots(): HasMany
    {
        return $this->hasMany(MeetingSpeaker::class);
    }

    /**
     * TTM speaker slots for this user.
     */
    public function meetingTtmSlots(): HasMany
    {
        return $this->hasMany(MeetingTtmSpeaker::class);
    }

    /**
     * Evaluations done by this user.
     */
    public function evaluations(): HasMany
    {
        return $this->hasMany(MeetingEvaluation::class, 'evaluator_user_id');
    }

    /**
     * Attendance records for this user.
     */
    public function attendance(): HasMany
    {
        return $this->hasMany(MeetingAttendance::class);
    }

    // -----------------------------------------------------------------------
    // Scoping helpers
    // -----------------------------------------------------------------------

    /**
     * Whether this user is a club-scoped user (has a club role via Spatie).
     * Club roles are those assigned to a single club.
     */
    public function isClubUser(): bool
    {
        // Club roles are specific roles that are tied to one club:
        // President, VPE, VPM, VPPR, Secretary, Treasurer, SAA, Member
        $clubRoleNames = config('speech-club.club_roles', []);
        foreach ($this->roles as $role) {
            if (in_array($role->name, $clubRoleNames)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Whether this user is a global user (no club role).
     */
    public function isGlobalUser(): bool
    {
        return ! $this->isClubUser();
    }

    /**
     * Whether this user is a Super Admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('Super Admin');
    }

    /**
     * Get the user's primary club (for club-scoped users, they belong to one club).
     */
    public function primaryClub(): ?Club
    {
        return $this->clubs()->first();
    }

    /**
     * Check if user belongs to a specific club.
     */
    public function belongsToClub(int $clubId): bool
    {
        return $this->clubs()->where('clubs.id', $clubId)->exists();
    }

    /**
     * Scope: active users only.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: users belonging to a specific club.
     */
    public function scopeInClub($query, int $clubId)
    {
        return $query->whereHas('clubs', function ($q) use ($clubId) {
            $q->where('clubs.id', $clubId);
        });
    }
}
