<?php

namespace App\Livewire\Clubs;

use App\Models\Club;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Clubs')]
class ClubIndex extends Component
{
    use WithPagination;

    public string $search  = '';
    public string $status  = '';
    public int    $perPage = 15;

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatus(): void { $this->resetPage(); }

    public function deleteClub(int $clubId): void
    {
        $this->authorize('clubs.delete');
        Club::findOrFail($clubId)->delete();
        $this->dispatch('flash', message: 'Club deleted.', type: 'success');
    }

    public function render()
    {
        $user = auth()->user();

        $clubs = Club::withCount('users')
            ->when(! $user->isSuperAdmin(), function ($q) use ($user) {
                $q->whereHas('users', fn ($u) => $u->where('users.id', $user->id));
            })
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('code', 'like', "%{$this->search}%");
            }))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->orderBy('name')
            ->paginate($this->perPage);

        return view('livewire.clubs.club-index', compact('clubs'));
    }
}
