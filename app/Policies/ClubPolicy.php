<?php

namespace App\Policies;

use App\Models\Club;
use App\Models\User;

class ClubPolicy
{
    /**
     * Determine whether the user can view any clubs.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('clubs.view');
    }

    /**
     * Determine whether the user can view the club.
     */
    public function view(User $user, Club $club): bool
    {
        if (! $user->can('clubs.view')) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->clubs()->where('clubs.id', $club->id)->exists();
    }

    /**
     * Determine whether the user can create clubs.
     */
    public function create(User $user): bool
    {
        return $user->can('clubs.create');
    }

    /**
     * Determine whether the user can update the club.
     */
    public function update(User $user, Club $club): bool
    {
        if (! $user->can('clubs.update')) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->clubs()->where('clubs.id', $club->id)->exists();
    }

    /**
     * Determine whether the user can delete the club.
     */
    public function delete(User $user, Club $club): bool
    {
        return $user->can('clubs.delete');
    }
}
