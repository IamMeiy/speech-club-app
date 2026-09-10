<?php

namespace App\Services;

use App\Models\Club;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ClubAccessService
{
    public function __construct(
        private readonly ClubContextService $clubContext
    ) {}

    /**
     * Get all users belonging to a specific club (for dropdowns, etc.).
     * These are active, non-deleted users.
     */
    public function getClubUsers(int $clubId, bool $activeOnly = true): Collection
    {
        $query = User::inClub($clubId)->orderBy('name');

        if ($activeOnly) {
            $query->active();
        }

        return $query->get();
    }

    /**
     * Validate that a user belongs to the given club.
     * Throws an authorization exception if not.
     */
    public function validateUserBelongsToClub(int $userId, int $clubId): bool
    {
        return User::where('id', $userId)
            ->whereHas('clubs', fn ($q) => $q->where('clubs.id', $clubId))
            ->exists();
    }

    /**
     * Validate multiple user IDs all belong to the same club.
     */
    public function validateAllUsersBelongToClub(array $userIds, int $clubId): bool
    {
        $userIds = array_filter($userIds); // remove nulls/zeros

        if (empty($userIds)) {
            return true;
        }

        $count = User::whereIn('id', $userIds)
            ->whereHas('clubs', fn ($q) => $q->where('clubs.id', $clubId))
            ->count();

        return $count === count(array_unique($userIds));
    }

    /**
     * Get clubs the current user can manage.
     */
    public function getAccessibleClubs(User $user): Collection
    {
        if ($user->isSuperAdmin()) {
            return Club::where('status', 'active')->orderBy('name')->get();
        }

        return $user->clubs()->where('clubs.status', 'active')->orderBy('name')->get();
    }
}
