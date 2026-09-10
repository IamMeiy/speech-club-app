<?php

namespace App\Services;

use App\Models\Club;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ClubContextService
{
    private const SESSION_KEY = 'current_club_id';

    /**
     * Get the current active club for the authenticated user.
     * - Club user: always their single club
     * - Global user: from session (selected via switcher)
     * - Super Admin: from session or null (all clubs)
     */
    public function currentClub(): ?Club
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user) {
            return null;
        }

        // Super Admin
        if ($user->isSuperAdmin()) {
            $clubId = Session::get(self::SESSION_KEY);
            return $clubId ? Club::find($clubId) : null;
        }

        // Club-scoped user: always their own club
        if ($user->isClubUser()) {
            return $user->primaryClub();
        }

        // Global user: from session
        $clubId = Session::get(self::SESSION_KEY);
        if ($clubId) {
            // Make sure they still have access
            $club = Club::find($clubId);
            if ($club && $this->canAccess($user, $club)) {
                return $club;
            }
        }

        return null;
    }

    /**
     * Get the current club ID, or null.
     */
    public function currentClubId(): ?int
    {
        return $this->currentClub()?->id;
    }

    /**
     * Set the active club for a global/super admin user.
     */
    public function setCurrentClub(?int $clubId): void
    {
        if ($clubId === null) {
            Session::forget(self::SESSION_KEY);
        } else {
            Session::put(self::SESSION_KEY, $clubId);
        }
    }

    /**
     * Get clubs available to the current user for the switcher.
     */
    public function availableClubs(): \Illuminate\Support\Collection
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user) {
            return collect();
        }

        // Super Admin can see all active clubs
        if ($user->isSuperAdmin()) {
            return Club::where('status', 'active')->orderBy('name')->get();
        }

        // Global user sees only their assigned clubs
        return $user->clubs()->where('clubs.status', 'active')->orderBy('name')->get();
    }

    /**
     * Check if the current user can access a given club.
     */
    public function canAccess(User $user, Club $club): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->clubs()->where('clubs.id', $club->id)->exists();
    }

    /**
     * Ensure the current user is authorized to access the given club.
     * Returns false if not authorized.
     */
    public function authorizeClub(?Club $club): bool
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user || ! $club) {
            return false;
        }

        return $this->canAccess($user, $club);
    }

    /**
     * Initialize default club context after login.
     */
    public function initializeForUser(User $user): void
    {
        // Club-scoped users don't need session — their club is derived directly
        if ($user->isClubUser()) {
            return;
        }

        // If already has a valid session, keep it
        $existing = Session::get(self::SESSION_KEY);
        if ($existing && Club::find($existing)) {
            return;
        }

        // For global users, auto-select if only one club
        if (! $user->isSuperAdmin()) {
            $clubs = $user->clubs()->where('clubs.status', 'active')->get();
            if ($clubs->count() === 1) {
                Session::put(self::SESSION_KEY, $clubs->first()->id);
            }
        }
    }
}
