<div class="p-6 lg:p-8 space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">System Roles</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Manage system authorization roles and their assigned permissions</p>
        </div>
        @can('roles.create')
        <a href="{{ route('roles.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl shadow-sm shadow-primary-500/20 transition-all duration-150">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Role
        </a>
        @endcan
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($roles as $role)
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-md transition-all duration-200 p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-primary-50 dark:bg-primary-950/60 text-primary-600 dark:text-primary-400 flex items-center justify-center font-semibold text-sm ring-1 ring-primary-500/10 dark:ring-primary-400/20">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <div>
                            <h2 class="font-bold text-slate-900 dark:text-white text-base">{{ $role->name }}</h2>
                            <span class="inline-flex items-center text-xs {{ in_array($role->name, config('speech-club.global_roles', [])) ? 'text-purple-700 bg-purple-50 dark:text-purple-300 dark:bg-purple-950/60 ring-1 ring-purple-500/20' : 'text-blue-700 bg-blue-50 dark:text-blue-300 dark:bg-blue-950/60 ring-1 ring-blue-500/20' }} px-2.5 py-0.5 rounded-full font-medium mt-1">
                                {{ in_array($role->name, config('speech-club.global_roles', [])) ? 'Global Role' : 'Club Role' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 py-3 border-y border-slate-100 dark:border-slate-800 my-4 text-center">
                    <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                        <span class="block text-xl font-bold text-slate-900 dark:text-white">{{ $role->users_count }}</span>
                        <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Users</span>
                    </div>
                    <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                        <span class="block text-xl font-bold text-slate-900 dark:text-white">{{ $role->permissions_count }}</span>
                        <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Permissions</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800/60">
                @can('roles.update')
                <a href="{{ route('roles.edit', $role) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-950/50 rounded-lg transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </a>
                @endcan

                @can('roles.delete')
                @if(!in_array($role->name, ['Super Admin', 'Admin', 'Member']))
                <button x-data @click="$confirm({
                            title: 'Delete Role',
                            message: 'Are you sure you want to delete role {{ addslashes($role->name) }}? This cannot be undone.',
                            type: 'danger',
                            confirmText: 'Delete Role',
                            onConfirm: () => $wire.deleteRole({{ $role->id }})
                        })"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/50 rounded-lg transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Delete
                </button>
                @endif
                @endcan
            </div>
        </div>
        @empty
        <div class="col-span-full bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-12 text-center text-slate-400 dark:text-slate-500">
            No roles configured.
        </div>
        @endforelse
    </div>
</div>
