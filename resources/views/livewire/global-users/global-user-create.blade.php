<div class="max-w-2xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('global-users.index') }}" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-white dark:hover:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Create Global User</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">Create an administrative user with multi-club access</p>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 sm:p-8 transition-colors">
        <form wire:submit="save" class="space-y-6">

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5" for="global-name">Full Name <span class="text-rose-500">*</span></label>
                <input wire:model="name" id="global-name" type="text" placeholder="e.g. Regional Director"
                       class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 @error('name') border-rose-300 @enderror">
                @error('name') <p class="mt-1.5 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5" for="global-email">Email Address <span class="text-rose-500">*</span></label>
                    <input wire:model="email" id="global-email" type="email" placeholder="admin@example.com"
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 @error('email') border-rose-300 @enderror">
                    @error('email') <p class="mt-1.5 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5" for="global-phone">Phone Number</label>
                    <input wire:model="phone" id="global-phone" type="text" placeholder="+91 98000 00000"
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5" for="global-role">Global Role <span class="text-rose-500">*</span></label>
                    <select wire:model="role" id="global-role"
                            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 font-medium @error('role') border-rose-300 @enderror">
                        @foreach($globalRoles as $r)
                            <option value="{{ $r }}">{{ $r }}</option>
                        @endforeach
                    </select>
                    @error('role') <p class="mt-1.5 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5" for="global-status">Account Status</label>
                    <select wire:model="status" id="global-status"
                            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 font-medium">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            {{-- Assigned Clubs --}}
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Assigned Clubs</label>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">Select the clubs this global user can access and manage (Super Admins have access to all clubs automatically).</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 p-4 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-200/60 dark:border-slate-700/50">
                    @foreach($clubs as $club)
                    <label class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-white dark:hover:bg-slate-800 transition-colors cursor-pointer">
                        <input type="checkbox" wire:model="selectedClubs" value="{{ $club->id }}"
                               class="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                        <div class="text-xs sm:text-sm">
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $club->name }}</span>
                            <span class="text-[11px] text-slate-400 dark:text-slate-500 ml-1">({{ $club->code }})</span>
                        </div>
                    </label>
                    @endforeach
                </div>
                @error('selectedClubs') <p class="mt-1.5 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5" for="global-password">Password <span class="text-rose-500">*</span></label>
                <input wire:model="password" id="global-password" type="password" placeholder="Min. 8 characters"
                       class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 @error('password') border-rose-300 @enderror">
                @error('password') <p class="mt-1.5 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('global-users.index') }}" class="px-5 py-2.5 text-xs sm:text-sm text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-semibold transition-colors">Cancel</a>
                <button type="submit" wire:loading.attr="disabled"
                        class="px-7 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-xs sm:text-sm font-semibold rounded-2xl transition-all shadow-md shadow-primary-600/20 disabled:opacity-60 active:scale-[0.98]">
                    <span wire:loading.remove>Create Global User</span>
                    <span wire:loading>Creating…</span>
                </button>
            </div>

        </form>
    </div>
</div>
