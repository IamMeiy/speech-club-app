<?php

namespace App\Livewire\Users;

use App\Livewire\Concerns\WithClubContext;
use App\Models\User;
use App\Services\ClubContextService;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Create Member')]
class UserCreate extends Component
{
    use WithClubContext;

    #[Rule('required|string|max:255')]
    public string $name = '';

    #[Rule('required|email|max:255|unique:users,email')]
    public string $email = '';

    #[Rule('nullable|string|max:20')]
    public string $phone = '';

    #[Rule('required|string|exists:roles,name')]
    public string $role = '';

    #[Rule('required|string|min:8')]
    public string $password = '';

    #[Rule('required|string|in:active,inactive')]
    public string $status = 'active';

    public function save(ClubContextService $clubContext): void
    {
        $this->authorize('users.create');
        $this->validate();

        $club = $clubContext->currentClub();

        if (! $club) {
            $this->addError('email', 'No club context found. Please select a club first.');
            return;
        }

        $user = User::create([
            'name'     => $this->name,
            'email'    => $this->email,
            'phone'    => $this->phone ?: null,
            'password' => Hash::make($this->password),
            'status'   => $this->status,
        ]);

        // Assign to current club (club determined from context, not user input)
        $user->clubs()->attach($club->id);

        // Assign role
        $user->assignRole($this->role);

        $this->dispatch('flash', message: 'Member created successfully.', type: 'success');
        $this->redirect(route('members.index'), navigate: true);
    }

    public function render()
    {
        $clubRoles = config('speech-club.club_roles', []);
        $club      = $this->getCurrentClub();

        return view('livewire.users.user-create', compact('clubRoles', 'club'));
    }
}
