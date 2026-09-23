<div class="p-6 lg:p-8">
    <div class="max-w-4xl mx-auto">

        <div class="flex items-center gap-4 mb-8">
            <a href="{{ route('roles.index') }}" class="p-2.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Edit Role</h1>
                <p class="text-slate-500 dark:text-slate-400 text-sm mt-0.5">{{ $role->name }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 sm:p-8">
            <form wire:submit="save" class="space-y-6">

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5" for="role-name">Role Name</label>
                    <input wire:model="name" id="role-name" type="text"
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all @error('name') border-rose-300 dark:border-rose-700 @enderror">
                    @error('name') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>

                {{-- Permissions Grouped --}}
                <div class="pt-6 border-t border-slate-100 dark:border-slate-800">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-900 dark:text-white">Assigned Permissions</label>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Select the permissions that users with this role will inherit</p>
                        </div>
                    </div>

                    <div class="space-y-5">
                        @foreach($permissions as $group => $perms)
                        <div class="p-4 sm:p-5 bg-slate-50/70 dark:bg-slate-800/40 rounded-2xl border border-slate-200/70 dark:border-slate-800/80">
                            <div class="flex items-center justify-between mb-3.5">
                                <h3 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-primary-500"></span>
                                    {{ ucfirst(str_replace('-', ' ', $group)) }} Module
                                </h3>
                                <span class="text-xs text-slate-400 font-medium">{{ count($perms) }} permissions</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
                                @foreach($perms as $permission)
                                <label class="flex items-center gap-3 p-2.5 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 hover:border-primary-300 dark:hover:border-primary-700 cursor-pointer transition-all">
                                    <input type="checkbox" wire:model="selectedPermissions" value="{{ $permission->name }}"
                                           class="w-4 h-4 rounded border-slate-300 dark:border-slate-700 text-primary-600 focus:ring-primary-500 bg-white dark:bg-slate-950">
                                    <span class="text-xs font-medium text-slate-700 dark:text-slate-300 select-none">{{ $permission->name }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100 dark:border-slate-800">
                    <a href="{{ route('roles.index') }}" class="px-4 py-2.5 text-sm text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-semibold transition-colors">Cancel</a>
                    <button type="submit" wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl shadow-sm shadow-primary-500/20 transition-all disabled:opacity-60">
                        <span wire:loading.remove>Save Changes</span>
                        <span wire:loading.inline-flex class="items-center gap-1.5">
                            <svg class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            Saving…
                        </span>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
