<?php

namespace App\Livewire\Users;

use App\Livewire\Concerns\WithClubContext;
use App\Models\User;
use App\Services\ClubAccessService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Edit Member')]
class UserEdit extends Component
{
    use WithClubContext;

    public User $user;

    public string $name   = '';
    public string $email  = '';
    public string $phone  = '';
    public string $role   = '';
    public string $status = 'active';
    public string $password = '';

    public function mount(User $user, ClubAccessService $access): void
    {
        $currentUser = auth()->user();

        if ($currentUser->isClubUser()) {
            $club = $currentUser->primaryClub();
            if ($club && ! $user->belongsToClub($club->id)) {
                abort(403, 'This member does not belong to your club.');
            }
        } elseif (! $currentUser->isSuperAdmin()) {
            // Global user: must share at least one assigned club
            $commonClubs = $currentUser->clubs()->whereIn('clubs.id', $user->clubs()->pluck('clubs.id'))->exists();
            if (! $commonClubs) {
                abort(403, 'You do not have permission to edit this member.');
            }
        }

        $this->user   = $user;
        $this->name   = $user->name;
        $this->email  = $user->email;
        $this->phone  = $user->phone ?? '';
        $this->role   = $user->roles()->pluck('name')->first() ?? 'Member';
        $this->status = $user->status;
    }

    public function save(): void
    {
        $this->authorize('users.update');

        $rules = [
            'name'   => 'required|string|max:255',
            'email'  => 'required|email|max:255|unique:users,email,' . $this->user->id,
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

        $this->dispatch('flash', message: 'Member updated successfully.', type: 'success');
        $this->redirect(route('members.index'), navigate: true);
    }

    public function render()
    {
        $clubRoles = config('speech-club.club_roles', []);
        $club      = $this->getCurrentClub();

        return view('livewire.users.user-edit', compact('clubRoles', 'club'));
    }
}
