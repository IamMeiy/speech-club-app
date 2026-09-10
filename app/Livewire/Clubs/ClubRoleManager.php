<?php

namespace App\Livewire\Clubs;

use App\Models\Club;
use Spatie\Permission\Models\Role;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Club Role Manager')]
class ClubRoleManager extends Component
{
    public Club $club;
    public array $selectedRoles = [];

    public function mount(Club $club): void
    {
        $this->club = $club;
        // Pre-select club roles associated with this club
        // For now, all club roles are available for all clubs
        $clubRoleNames      = config('speech-club.club_roles', []);
        $this->selectedRoles = Role::whereIn('name', $clubRoleNames)->pluck('id')->toArray();
    }

    public function render()
    {
        $clubRoleNames = config('speech-club.club_roles', []);
        $clubRoles     = Role::whereIn('name', $clubRoleNames)->orderBy('name')->get();

        return view('livewire.clubs.club-role-manager', compact('clubRoles'));
    }
}
