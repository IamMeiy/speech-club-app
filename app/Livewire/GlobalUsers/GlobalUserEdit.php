<?php

namespace App\Livewire\GlobalUsers;

use App\Models\Club;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Edit Global User')]
class GlobalUserEdit extends Component
{
    public User   $user;
    public string $name    = '';
    public string $email   = '';
    public string $phone   = '';
    public string $role    = '';
    public string $status  = 'active';
    public string $password = '';
    public array  $selectedClubs = [];

    public function mount(User $user): void
    {
        $this->user          = $user;
        $this->name          = $user->name;
        $this->email         = $user->email;
        $this->phone         = $user->phone ?? '';
        $this->role          = $user->roles->first()?->name ?? '';
        $this->status        = $user->status;
        $this->selectedClubs = $user->clubs->pluck('id')->toArray();
    }

    public function save(): void
    {
        $this->authorize('global-users.update');

        $rules = [
            'name'   => 'required|string|max:255',
            'email'  => 'required|email|unique:users,email,' . $this->user->id,
            'phone'  => 'nullable|string|max:20',
            'role'   => 'required|string|exists:roles,name',
            'status' => 'required|in:active,inactive,suspended',
        ];

        if ($this->password !== '') {
            $rules['password'] = 'min:8';
        }

        $this->validate($rules);

        $updateData = [
            'name'   => $this->name,
            'email'  => $this->email,
            'phone'  => $this->phone ?: null,
            'status' => $this->status,
        ];

        if ($this->password !== '') {
            $updateData['password'] = \Illuminate\Support\Facades\Hash::make($this->password);
        }

        $this->user->update($updateData);
        $this->user->syncRoles([$this->role]);
        $this->user->clubs()->sync($this->selectedClubs);

        $this->dispatch('flash', message: 'Global user updated.', type: 'success');
        $this->redirect(route('global-users.index'), navigate: true);
    }

    public function render()
    {
        $globalRoles = config('speech-club.global_roles', []);
        $clubs       = Club::where('status', 'active')->orderBy('name')->get();

        return view('livewire.global-users.global-user-edit', compact('globalRoles', 'clubs'));
    }
}
