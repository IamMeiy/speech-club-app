<div class="p-6 lg:p-8">
    <div class="max-w-2xl mx-auto">

        {{-- Header --}}
        <div class="flex items-center gap-4 mb-8">
            <a href="{{ route('global-users.index') }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-xl transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Create Global User</h1>
                <p class="text-gray-500 text-sm mt-1">Create an administrative user with multi-club access</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8">
            <form wire:submit="save" class="space-y-6">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5" for="global-name">Full Name</label>
                    <input wire:model="name" id="global-name" type="text" placeholder="e.g. Admin User"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-300 @enderror">
                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5" for="global-email">Email Address</label>
                        <input wire:model="email" id="global-email" type="email" placeholder="admin@example.com"
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('email') border-red-300 @enderror">
                        @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5" for="global-phone">Phone (optional)</label>
                        <input wire:model="phone" id="global-phone" type="text" placeholder="+91 98000 00000"
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5" for="global-role">Global Role</label>
                        <select wire:model="role" id="global-role"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('role') border-red-300 @enderror">
                            @foreach($globalRoles as $r)
                                <option value="{{ $r }}">{{ $r }}</option>
                            @endforeach
                        </select>
                        @error('role') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5" for="global-status">Status</label>
                        <select wire:model="status" id="global-status"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                {{-- Assigned Clubs --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Assigned Clubs</label>
                    <p class="text-xs text-gray-500 mb-3">Select the clubs this global user can access and manage (Super Admins have access to all clubs automatically).</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-4 bg-gray-50 rounded-xl border border-gray-100">
                        @foreach($clubs as $club)
                        <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-white transition-colors cursor-pointer">
                            <input type="checkbox" wire:model="selectedClubs" value="{{ $club->id }}"
                                   class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <div class="text-sm">
                                <span class="font-medium text-gray-800">{{ $club->name }}</span>
                                <span class="text-xs text-gray-500 ml-1">({{ $club->code }})</span>
                            </div>
                        </label>
                        @endforeach
                    </div>
                    @error('selectedClubs') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5" for="global-password">Password</label>
                    <input wire:model="password" id="global-password" type="password" placeholder="Min. 8 characters"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('password') border-red-300 @enderror">
                    @error('password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                    <a href="{{ route('global-users.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900 font-medium transition-colors">Cancel</a>
                    <button type="submit" wire:loading.attr="disabled"
                            class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors disabled:opacity-60">
                        <span wire:loading.remove>Create Global User</span>
                        <span wire:loading>Creating…</span>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
