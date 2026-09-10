<?php

namespace App\Livewire\GlobalUsers;

use App\Models\Club;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Global Users')]
class GlobalUserIndex extends Component
{
    use WithPagination;

    public string $search  = '';
    public string $status  = '';
    public int    $perPage = 15;

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatus(): void { $this->resetPage(); }

    public function deleteUser(int $userId): void
    {
        $this->authorize('global-users.delete');
        User::findOrFail($userId)->delete();
        $this->dispatch('flash', message: 'User deleted.', type: 'success');
    }

    public function render()
    {
        $globalRoles = config('speech-club.global_roles', []);

        $users = User::with('roles', 'clubs')
            ->whereHas('roles', fn ($q) => $q->whereIn('name', $globalRoles))
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%");
            }))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->orderBy('name')
            ->paginate($this->perPage);

        return view('livewire.global-users.global-user-index', compact('users'));
    }
}
