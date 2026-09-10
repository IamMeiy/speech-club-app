<div class="p-6 lg:p-8">
    <div class="max-w-4xl mx-auto space-y-8">

        {{-- Header --}}
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">My Profile</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Manage your personal details, credentials, and account settings</p>
        </div>

        {{-- Profile Summary Card --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 sm:p-8 flex flex-col sm:flex-row items-center sm:items-start gap-6">
            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 text-white flex items-center justify-center text-2xl font-bold flex-shrink-0 shadow-lg shadow-primary-500/20">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <div class="text-center sm:text-left flex-1 min-w-0">
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ $user->name }}</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $user->email }}</p>
                <div class="flex flex-wrap gap-2 mt-3 justify-center sm:justify-start">
                    <span class="inline-flex items-center text-xs px-3 py-1 bg-primary-50 text-primary-700 dark:bg-primary-950/60 dark:text-primary-300 font-semibold rounded-full ring-1 ring-primary-500/20">
                        {{ $user->roles->first()?->name ?? 'User' }}
                    </span>
                    @if($user->isGlobalUser())
                        <span class="inline-flex items-center text-xs px-3 py-1 bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 font-semibold rounded-full ring-1 ring-purple-500/20">
                            Global User
                        </span>
                    @endif
                    <span class="inline-flex items-center text-xs px-3 py-1 {{ $user->status === 'active' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 ring-1 ring-emerald-500/20' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 ring-1 ring-rose-500/20' }} font-semibold rounded-full">
                        <span class="w-1.5 h-1.5 rounded-full {{ $user->status === 'active' ? 'bg-emerald-500' : 'bg-rose-500' }} mr-1.5"></span>
                        {{ ucfirst($user->status) }}
                    </span>
                </div>
                @if($user->clubs->isNotEmpty())
                <div class="mt-4 pt-3.5 border-t border-slate-100 dark:border-slate-800 flex flex-wrap gap-1.5 items-center justify-center sm:justify-start">
                    <span class="text-xs text-slate-400 font-medium">Clubs:</span>
                    @foreach($user->clubs as $club)
                        <span class="text-xs bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 px-2.5 py-0.5 rounded-lg font-medium">{{ $club->name }}</span>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- Edit Info Form --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 sm:p-8">
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-6">Personal Information</h3>
            <form wire:submit="saveProfile" class="space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5" for="prof-name">Full Name</label>
                    <input wire:model="name" id="prof-name" type="text"
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all @error('name') border-rose-300 dark:border-rose-700 @enderror">
                    @error('name') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5" for="prof-email">Email Address</label>
                        <input wire:model="email" id="prof-email" type="email"
                               class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all @error('email') border-rose-300 dark:border-rose-700 @enderror">
                        @error('email') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5" for="prof-phone">Phone</label>
                        <input wire:model="phone" id="prof-phone" type="text" placeholder="+91 98000 00000"
                               class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-slate-100 dark:border-slate-800">
                    <button type="submit" wire:loading.attr="disabled"
                            class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl shadow-sm shadow-primary-500/20 transition-all disabled:opacity-60">
                        <span wire:loading.remove wire:target="saveProfile">Update Profile</span>
                        <span wire:loading wire:target="saveProfile" class="inline-flex items-center gap-1.5">
                            <svg class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            Saving…
                        </span>
                    </button>
                </div>
            </form>
        </div>

        {{-- Change Password Form --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 sm:p-8">
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-6">Change Password</h3>
            <form wire:submit="changePassword" class="space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5" for="curr-pw">Current Password</label>
                    <input wire:model="current_password" id="curr-pw" type="password"
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all @error('current_password') border-rose-300 dark:border-rose-700 @enderror">
                    @error('current_password') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5" for="new-pw">New Password</label>
                        <input wire:model="new_password" id="new-pw" type="password" placeholder="Min. 8 characters"
                               class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all @error('new_password') border-rose-300 dark:border-rose-700 @enderror">
                        @error('new_password') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5" for="conf-pw">Confirm New Password</label>
                        <input wire:model="confirm_password" id="conf-pw" type="password" placeholder="Re-type password"
                               class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all @error('confirm_password') border-rose-300 dark:border-rose-700 @enderror">
                        @error('confirm_password') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-slate-100 dark:border-slate-800">
                    <button type="submit" wire:loading.attr="disabled"
                            class="px-6 py-2.5 bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 text-white text-sm font-semibold rounded-xl shadow-sm transition-all disabled:opacity-60">
                        <span wire:loading.remove wire:target="changePassword">Change Password</span>
                        <span wire:loading wire:target="changePassword" class="inline-flex items-center gap-1.5">
                            <svg class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            Updating…
                        </span>
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
