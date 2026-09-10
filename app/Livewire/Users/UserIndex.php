<?php

namespace App\Livewire\Users;

use App\Livewire\Concerns\WithClubContext;
use App\Models\User;
use App\Services\ClubContextService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Members')]
class UserIndex extends Component
{
    use WithPagination, WithClubContext;

    public string $search   = '';
    public string $status   = '';
    public string $role     = '';
    public int    $perPage  = 15;

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatus(): void { $this->resetPage(); }
    public function updatingRole(): void   { $this->resetPage(); }

    public function deleteUser(int $userId): void
    {
        $this->authorize('users.delete');

        $currentUser = auth()->user();
        $user        = User::findOrFail($userId);

        if ($currentUser->isClubUser()) {
            $club = $currentUser->primaryClub();
            if ($club && ! $user->belongsToClub($club->id)) {
                abort(403);
            }
        } elseif (! $currentUser->isSuperAdmin()) {
            $commonClubs = $currentUser->clubs()->whereIn('clubs.id', $user->clubs->pluck('id'))->exists();
            if (! $commonClubs) {
                abort(403);
            }
        }

        $user->delete();

        $this->dispatch('flash', message: 'Member deleted successfully.', type: 'success');
    }

    public function render(ClubContextService $clubContext)
    {
        $user = auth()->user();
        $club = $clubContext->currentClub();

        $query = User::with('roles', 'clubs')
            ->when($club, fn ($q) => $q->inClub($club->id))
            ->when(! $club && ! $user->isSuperAdmin(), function ($q) use ($user) {
                $q->whereHas('clubs', fn ($c) => $c->whereIn('clubs.id', $user->clubs->pluck('id')));
            })
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%");
            }))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->role, fn ($q) => $q->whereHas('roles', fn ($r) => $r->where('name', $this->role)))
            ->orderBy('name');

        $users     = $query->paginate($this->perPage);
        $clubRoles = config('speech-club.club_roles', []);

        return view('livewire.users.user-index', compact('users', 'club', 'clubRoles'));
    }
}
