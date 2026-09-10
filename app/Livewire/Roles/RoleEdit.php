<?php

namespace App\Livewire\Roles;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

#[Layout('components.layouts.app')]
#[Title('Edit Role')]
class RoleEdit extends Component
{
    public Role   $role;
    public string $name = '';
    public array  $selectedPermissions = [];

    public function mount(Role $role): void
    {
        $this->role                = $role;
        $this->name                = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
    }

    public function save(): void
    {
        $this->authorize('roles.update');
        $this->validate([
            'name' => 'required|string|max:100|unique:roles,name,' . $this->role->id,
        ]);

        $this->role->update(['name' => $this->name]);
        $this->role->syncPermissions($this->selectedPermissions);

        $this->dispatch('flash', message: 'Role updated.', type: 'success');
        $this->redirect(route('roles.index'), navigate: true);
    }

    public function render()
    {
        $permissions = Permission::orderBy('name')->get()->groupBy(fn ($p) => explode('.', $p->name)[0]);
        return view('livewire.roles.role-edit', compact('permissions'));
    }
}
