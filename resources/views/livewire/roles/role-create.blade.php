<div class="p-6 lg:p-8">
    <div class="max-w-3xl mx-auto">

        <div class="flex items-center gap-4 mb-8">
            <a href="{{ route('roles.index') }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-xl transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Create Role</h1>
                <p class="text-gray-500 text-sm mt-1">Define a new system role and assign specific permissions</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8">
            <form wire:submit="save" class="space-y-6">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5" for="role-name">Role Name</label>
                    <input wire:model="name" id="role-name" type="text" placeholder="e.g. Content Manager"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-300 @enderror">
                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Permissions Grouped --}}
                <div class="pt-4 border-t border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <label class="block text-sm font-semibold text-gray-900">Assign Permissions</label>
                        <span class="text-xs text-gray-500">Grouped by module</span>
                    </div>

                    <div class="space-y-6">
                        @foreach($permissions as $group => $perms)
                        <div class="p-4 bg-gray-50/70 rounded-xl border border-gray-100" x-data="{ allSelected: false }">
                            <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                {{ ucfirst(str_replace('-', ' ', $group)) }}
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
                                @foreach($perms as $permission)
                                <label class="flex items-center gap-2.5 p-2 bg-white rounded-lg border border-gray-100 hover:border-indigo-200 cursor-pointer text-xs">
                                    <input type="checkbox" wire:model="selectedPermissions" value="{{ $permission->name }}"
                                           class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-gray-700 font-medium">{{ $permission->name }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100">
                    <a href="{{ route('roles.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900 font-medium transition-colors">Cancel</a>
                    <button type="submit" wire:loading.attr="disabled"
                            class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors disabled:opacity-60">
                        <span wire:loading.remove>Create Role</span>
                        <span wire:loading>Saving…</span>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
