<?php

namespace App\Livewire\Permissions;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Permission;

#[Layout('components.layouts.app')]
#[Title('Permissions')]
class PermissionIndex extends Component
{
    public function render()
    {
        $permissions = Permission::orderBy('name')->get(['id', 'name'])
            ->groupBy(fn ($p) => explode('.', $p->name)[0]);

        return view('livewire.permissions.permission-index', compact('permissions'));
    }
}
