<?php

namespace App\Livewire\GlobalUsers;

use App\Models\Club;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Create Global User')]
class GlobalUserCreate extends Component
{
    #[Rule('required|string|max:255')]
    public string $name = '';

    #[Rule('required|email|unique:users,email')]
    public string $email = '';

    #[Rule('nullable|string|max:20')]
    public string $phone = '';

    #[Rule('required|string|exists:roles,name')]
    public string $role = 'Admin';

    #[Rule('required|string|min:8')]
    public string $password = '';

    #[Rule('required|in:active,inactive')]
    public string $status = 'active';

    public array $selectedClubs = [];

    public function save(): void
    {
        $this->authorize('global-users.create');
        $this->validate();

        $user = User::create([
            'name'     => $this->name,
            'email'    => $this->email,
            'phone'    => $this->phone ?: null,
            'password' => Hash::make($this->password),
            'status'   => $this->status,
        ]);

        $user->assignRole($this->role);

        // Assign selected clubs
        if (! empty($this->selectedClubs)) {
            $user->clubs()->sync($this->selectedClubs);
        }

        $this->dispatch('flash', message: 'Global user created successfully.', type: 'success');
        $this->redirect(route('global-users.index'), navigate: true);
    }

    public function render()
    {
        $globalRoles = config('speech-club.global_roles', []);
        $clubs       = Club::where('status', 'active')->orderBy('name')->get();

        return view('livewire.global-users.global-user-create', compact('globalRoles', 'clubs'));
    }
}
