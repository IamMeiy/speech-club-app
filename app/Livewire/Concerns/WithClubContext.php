<?php

namespace App\Livewire\Concerns;

use App\Models\Club;
use App\Services\ClubContextService;

trait WithClubContext
{
    /**
     * The resolved current club for this request.
     */
    protected ?Club $resolvedClub = null;

    /**
     * Get the current club from ClubContextService.
     */
    public function getCurrentClub(): ?Club
    {
        if ($this->resolvedClub === null) {
            $this->resolvedClub = app(ClubContextService::class)->currentClub();
        }
        return $this->resolvedClub;
    }

    /**
     * Get the current club ID or null.
     */
    public function getCurrentClubId(): ?int
    {
        return $this->getCurrentClub()?->id;
    }

    /**
     * Abort with 403 if the user cannot access the resolved club.
     */
    protected function authorizeClubAccess(): void
    {
        $club = $this->getCurrentClub();

        if ($club === null && ! auth()->user()->isSuperAdmin()) {
            // Global users with no club selected — this is valid for "All Clubs" view
            return;
        }

        if ($club && ! app(ClubContextService::class)->authorizeClub($club)) {
            abort(403, 'You do not have access to this club.');
        }
    }
}
