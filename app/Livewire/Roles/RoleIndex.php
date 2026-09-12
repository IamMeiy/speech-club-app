<?php

namespace App\Livewire\Roles;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Layout('components.layouts.app')]
#[Title('Roles')]
class RoleIndex extends Component
{
    public function deleteRole(int $roleId): void
    {
        $this->authorize('roles.delete');
        $role = Role::findOrFail($roleId);

        // Prevent deleting system-critical roles
        if (in_array($role->name, ['Super Admin', 'Admin', 'Member'])) {
            $this->dispatch('flash', message: 'This role cannot be deleted.', type: 'error');
            return;
        }

        $role->delete();
        $this->dispatch('flash', message: 'Role deleted.', type: 'success');
    }

    public function render()
    {
        $roles = Role::withCount('users', 'permissions')->orderBy('name')->get(['id', 'name']);
        return view('livewire.roles.role-index', compact('roles'));
    }
}
