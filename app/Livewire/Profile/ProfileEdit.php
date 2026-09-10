<?php

namespace App\Livewire\Profile;

use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('My Profile')]
class ProfileEdit extends Component
{
    public string $name             = '';
    public string $email            = '';
    public string $phone            = '';
    public string $current_password = '';
    public string $new_password     = '';
    public string $confirm_password = '';

    public function mount(): void
    {
        $user        = auth()->user();
        $this->name  = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
    }

    public function saveProfile(): void
    {
        $this->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . auth()->id(),
            'phone' => 'nullable|string|max:20',
        ]);

        auth()->user()->update([
            'name'  => $this->name,
            'email' => $this->email,
            'phone' => $this->phone ?: null,
        ]);

        $this->dispatch('flash', message: 'Profile updated.', type: 'success');
    }

    public function changePassword(): void
    {
        $this->validate([
            'current_password' => 'required',
            'new_password'     => 'required|min:8',
            'confirm_password' => 'required|same:new_password',
        ]);

        if (! Hash::check($this->current_password, auth()->user()->password)) {
            $this->addError('current_password', 'The current password is incorrect.');
            return;
        }

        auth()->user()->update(['password' => Hash::make($this->new_password)]);

        $this->current_password = '';
        $this->new_password     = '';
        $this->confirm_password = '';

        $this->dispatch('flash', message: 'Password changed successfully.', type: 'success');
    }

    public function render()
    {
        $user = auth()->user()->load('roles', 'clubs');
        return view('livewire.profile.profile-edit', compact('user'));
    }
}
