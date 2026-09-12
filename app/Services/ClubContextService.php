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
    private ?Club $currentClubMemo = null;
    private bool $currentClubChecked = false;
    private ?\Illuminate\Support\Collection $availableClubsMemo = null;

    /**
     * Get the current active club for the authenticated user.
     * - Club user: always their single club
     * - Global user: from session (selected via switcher)
     * - Super Admin: from session or null (all clubs)
     */
    public function currentClub(): ?Club
    {
        if ($this->currentClubChecked) {
            return $this->currentClubMemo;
        }

        /** @var User $user */
        $user = Auth::user();

        if (! $user) {
            $this->currentClubChecked = true;
            return null;
        }

        // Super Admin
        if ($user->isSuperAdmin()) {
            $clubId = Session::get(self::SESSION_KEY);
            $this->currentClubMemo = $clubId ? Club::find($clubId) : null;
            $this->currentClubChecked = true;
            return $this->currentClubMemo;
        }

        // Club-scoped user: always their own club
        if ($user->isClubUser()) {
            $this->currentClubMemo = $user->primaryClub();
            $this->currentClubChecked = true;
            return $this->currentClubMemo;
        }

        // Global user: from session
        $clubId = Session::get(self::SESSION_KEY);
        if ($clubId) {
            // Make sure they still have access
            $club = Club::find($clubId);
            if ($club && $this->canAccess($user, $club)) {
                $this->currentClubMemo = $club;
                $this->currentClubChecked = true;
                return $this->currentClubMemo;
            }
        }

        $this->currentClubChecked = true;
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
        $this->currentClubChecked = false;
        $this->currentClubMemo = null;
        $this->availableClubsMemo = null;

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
        if ($this->availableClubsMemo !== null) {
            return $this->availableClubsMemo;
        }

        /** @var User $user */
        $user = Auth::user();

        if (! $user) {
            return $this->availableClubsMemo = collect();
        }

        // Super Admin can see all active clubs
        if ($user->isSuperAdmin()) {
            return $this->availableClubsMemo = Club::where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name', 'code', 'status']);
        }

        // Global user sees only their assigned clubs
        return $this->availableClubsMemo = $user->clubs()
            ->where('clubs.status', 'active')
            ->orderBy('name')
            ->get(['clubs.id', 'clubs.name', 'clubs.code', 'clubs.status']);
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
            $clubIds = $user->clubs()->where('clubs.status', 'active')->pluck('clubs.id');
            if ($clubIds->count() === 1) {
                Session::put(self::SESSION_KEY, $clubIds->first());
            }
        }
    }
}
