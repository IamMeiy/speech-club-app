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

    /**
     * Total prepared speeches count.
     */
    public function speechesCount(?int $clubId = null): int
    {
        return $this->meetingSpeakerSlots()
            ->when($clubId, fn ($q) => $q->whereHas('meeting', fn ($m) => $m->where('club_id', $clubId)))
            ->whereHas('meeting', fn ($m) => $m->whereIn('status', ['scheduled', 'completed']))
            ->count();
    }

    /**
     * Total table topics speeches count.
     */
    public function tableTopicsCount(?int $clubId = null): int
    {
        return $this->meetingTtmSlots()
            ->when($clubId, fn ($q) => $q->whereHas('meeting', fn ($m) => $m->where('club_id', $clubId)))
            ->whereHas('meeting', fn ($m) => $m->whereIn('status', ['scheduled', 'completed']))
            ->count();
    }

    /**
     * Total speech evaluations count.
     */
    public function evaluationsCount(?int $clubId = null): int
    {
        return $this->evaluations()
            ->when($clubId, fn ($q) => $q->whereHas('meeting', fn ($m) => $m->where('club_id', $clubId)))
            ->whereHas('meeting', fn ($m) => $m->whereIn('status', ['scheduled', 'completed']))
            ->count();
    }

    /**
     * Total meeting roles count.
     */
    public function meetingRolesCount(?int $clubId = null): int
    {
        return $this->meetingRoles()
            ->when($clubId, fn ($q) => $q->whereHas('meeting', fn ($m) => $m->where('club_id', $clubId)))
            ->whereHas('meeting', fn ($m) => $m->whereIn('status', ['scheduled', 'completed']))
            ->count();
    }

    /**
     * Milestone badges calculation.
     */
    public function getMilestoneBadges(?int $clubId = null): array
    {
        $speeches = $this->speechesCount($clubId);
        $ttm = $this->tableTopicsCount($clubId);
        $evals = $this->evaluationsCount($clubId);
        $roles = $this->meetingRolesCount($clubId);
        $meetingsAttended = $this->attendance()
            ->when($clubId, fn ($q) => $q->whereHas('meeting', fn ($m) => $m->where('club_id', $clubId)))
            ->whereIn('status', ['present', 'late'])
            ->count();

        return [
            [
                'id' => 'icebreaker',
                'name' => 'Icebreaker Achieved',
                'category' => 'Prepared Speaking',
                'description' => 'Delivered your first prepared speech to the club.',
                'unlocked' => $speeches >= 1,
                'progress' => min($speeches, 1),
                'target' => 1,
                'color' => 'indigo',
            ],
            [
                'id' => 'bronze_speaker',
                'name' => 'Bronze Speaker',
                'category' => 'Prepared Speaking',
                'description' => 'Completed 3 prepared project speeches.',
                'unlocked' => $speeches >= 3,
                'progress' => min($speeches, 3),
                'target' => 3,
                'color' => 'amber',
            ],
            [
                'id' => 'silver_speaker',
                'name' => 'Silver Speaker',
                'category' => 'Prepared Speaking',
                'description' => 'Delivered 5 project speeches with peer feedback.',
                'unlocked' => $speeches >= 5,
                'progress' => min($speeches, 5),
                'target' => 5,
                'color' => 'cyan',
            ],
            [
                'id' => 'gold_speaker',
                'name' => 'Competent Communicator',
                'category' => 'Mastery',
                'description' => 'Mastered 10 prepared speech projects.',
                'unlocked' => $speeches >= 10,
                'progress' => min($speeches, 10),
                'target' => 10,
                'color' => 'purple',
            ],
            [
                'id' => 'impromptu_prodigy',
                'name' => 'Impromptu Prodigy',
                'category' => 'Table Topics',
                'description' => 'Tackled 5 impromptu Table Topics challenges.',
                'unlocked' => $ttm >= 5,
                'progress' => min($ttm, 5),
                'target' => 5,
                'color' => 'rose',
            ],
            [
                'id' => 'master_evaluator',
                'name' => 'Master Evaluator',
                'category' => 'Evaluation',
                'description' => 'Delivered constructive evaluations for 5 speakers.',
                'unlocked' => $evals >= 5,
                'progress' => min($evals, 5),
                'target' => 5,
                'color' => 'emerald',
            ],
            [
                'id' => 'club_pillar',
                'name' => 'Club Pillar',
                'category' => 'Leadership',
                'description' => 'Took up 10 meeting facilitator & leadership roles.',
                'unlocked' => $roles >= 10,
                'progress' => min($roles, 10),
                'target' => 10,
                'color' => 'blue',
            ],
            [
                'id' => 'loyal_attendee',
                'name' => 'Dedicated Attendee',
                'category' => 'Commitment',
                'description' => 'Attended at least 5 club sessions.',
                'unlocked' => $meetingsAttended >= 5,
                'progress' => min($meetingsAttended, 5),
                'target' => 5,
                'color' => 'emerald',
            ],
        ];
    }
}
