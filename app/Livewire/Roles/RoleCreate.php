<?php

namespace App\Livewire\Roles;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

#[Layout('components.layouts.app')]
#[Title('Create Role')]
class RoleCreate extends Component
{
    #[Rule('required|string|max:100|unique:roles,name')]
    public string $name = '';

    public array $selectedPermissions = [];

    public function save(): void
    {
        $this->authorize('roles.create');
        $this->validate();

        $role = Role::create(['name' => $this->name]);
        $role->syncPermissions($this->selectedPermissions);

        $this->dispatch('flash', message: 'Role created successfully.', type: 'success');
        $this->redirect(route('roles.index'), navigate: true);
    }

    public function render()
    {
        $permissions = Permission::orderBy('name')->get()->groupBy(fn ($p) => explode('.', $p->name)[0]);
        return view('livewire.roles.role-create', compact('permissions'));
    }
}
