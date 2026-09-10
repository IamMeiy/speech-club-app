<?php

namespace App\Livewire\Users;

use App\Livewire\Concerns\WithClubContext;
use App\Models\Club;
use App\Models\User;
use App\Services\ClubAccessService;
use App\Services\ClubContextService;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Add Member')]
class UserCreate extends Component
{
    use WithClubContext;

    public ?int $selectedClubId = null;

    #[Rule('required|string|max:255')]
    public string $name = '';

    #[Rule('required|email|max:255|unique:users,email')]
    public string $email = '';

    #[Rule('nullable|string|max:20')]
    public string $phone = '';

    #[Rule('required|string|exists:roles,name')]
    public string $role = 'Member';

    #[Rule('required|string|min:8')]
    public string $password = '';

    #[Rule('required|string|in:active,inactive')]
    public string $status = 'active';

    public function mount(ClubContextService $clubContext, ClubAccessService $access): void
    {
        $user = auth()->user();

        if ($user->isClubUser()) {
            $club = $user->primaryClub();
            $this->selectedClubId = $club?->id;
        } else {
            // Global user / Super Admin
            $currentClub = $clubContext->currentClub();
            if ($currentClub) {
                $this->selectedClubId = $currentClub->id;
            } else {
                $accessible = $access->getAccessibleClubs($user);
                $this->selectedClubId = $accessible->first()?->id;
            }
        }
    }

    public function save(ClubAccessService $access): void
    {
        $this->authorize('users.create');
        $this->validate();

        $user = auth()->user();
        $clubId = $user->isClubUser() ? $user->primaryClub()?->id : $this->selectedClubId;

        if (! $clubId) {
            $this->addError('selectedClubId', 'Please select a club for this member.');
            return;
        }

        $club = Club::findOrFail($clubId);

        // Security check
        if (! $user->isSuperAdmin() && ! $access->validateUserBelongsToClub($user->id, $club->id)) {
            abort(403, 'You do not have permission to add members to this club.');
        }

        $newMember = User::create([
            'name'     => $this->name,
            'email'    => $this->email,
            'phone'    => $this->phone ?: null,
            'password' => Hash::make($this->password),
            'status'   => $this->status,
        ]);

        // Assign to club
        $newMember->clubs()->attach($club->id);

        // Assign role
        $newMember->assignRole($this->role);

        $this->dispatch('flash', message: 'Member created successfully.', type: 'success');
        $this->redirect(route('members.index'), navigate: true);
    }

    public function render(ClubAccessService $access)
    {
        $user            = auth()->user();
        $isGlobal        = $user->isGlobalUser();
        $accessibleClubs = $access->getAccessibleClubs($user);

        $activeClubId = $user->isClubUser() ? $user->primaryClub()?->id : $this->selectedClubId;
        $currentClub  = $activeClubId ? Club::find($activeClubId) : null;
        $clubRoles    = config('speech-club.club_roles', []);

        return view('livewire.users.user-create', compact(
            'isGlobal',
            'accessibleClubs',
            'currentClub',
            'clubRoles'
        ));
    }
}
