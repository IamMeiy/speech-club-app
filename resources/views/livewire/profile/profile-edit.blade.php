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

        {{-- ================================================================ --}}
        {{-- Speaking Journey & Milestone Badges --}}
        {{-- ================================================================ --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 sm:p-8 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                        Speaking Journey & Milestone Badges
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Track speeches delivered, evaluations completed, and club milestones unlocked.</p>
                </div>
                <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 rounded-xl text-xs font-bold text-amber-700 dark:text-amber-400 self-start sm:self-auto">
                    <span>🏆 {{ $unlockedCount }} of {{ count($badges) }} Badges Earned</span>
                </div>
            </div>

            {{-- Stat Counters Grid --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-200/60 dark:border-slate-800/80">
                    <p class="text-2xl font-extrabold text-primary-600 dark:text-primary-400">{{ $speechesCount }}</p>
                    <p class="text-xs font-semibold text-slate-600 dark:text-slate-300 mt-1">Prepared Speeches</p>
                    <p class="text-[10px] text-slate-400 dark:text-slate-500">Project presentations</p>
                </div>
                <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-200/60 dark:border-slate-800/80">
                    <p class="text-2xl font-extrabold text-purple-600 dark:text-purple-400">{{ $ttmCount }}</p>
                    <p class="text-xs font-semibold text-slate-600 dark:text-slate-300 mt-1">Table Topics</p>
                    <p class="text-[10px] text-slate-400 dark:text-slate-500">Impromptu speeches</p>
                </div>
                <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-200/60 dark:border-slate-800/80">
                    <p class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">{{ $evalsCount }}</p>
                    <p class="text-xs font-semibold text-slate-600 dark:text-slate-300 mt-1">Speech Evaluations</p>
                    <p class="text-[10px] text-slate-400 dark:text-slate-500">Peer feedback delivered</p>
                </div>
                <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-200/60 dark:border-slate-800/80">
                    <p class="text-2xl font-extrabold text-blue-600 dark:text-blue-400">{{ $rolesCount }}</p>
                    <p class="text-xs font-semibold text-slate-600 dark:text-slate-300 mt-1">Meeting Roles</p>
                    <p class="text-[10px] text-slate-400 dark:text-slate-500">Facilitator leadership</p>
                </div>
            </div>

            {{-- Milestone Badges Grid --}}
            <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-3.5">Achievements & Milestones</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                    @foreach($badges as $b)
                    <div class="p-3.5 rounded-2xl border transition-all flex flex-col justify-between {{ $b['unlocked'] ? 'bg-gradient-to-br from-white to-slate-50 dark:from-slate-800/90 dark:to-slate-900 border-primary-200 dark:border-primary-900/50 shadow-sm' : 'bg-slate-50/70 dark:bg-slate-950/40 border-slate-200/60 dark:border-slate-800/50 opacity-65' }}">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md {{ $b['unlocked'] ? 'bg-primary-50 text-primary-700 dark:bg-primary-950/60 dark:text-primary-300' : 'bg-slate-200/80 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                                    {{ $b['category'] }}
                                </span>
                                @if($b['unlocked'])
                                    <span class="text-xs text-amber-500" title="Unlocked">⭐</span>
                                @else
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                @endif
                            </div>
                            <h5 class="text-sm font-bold text-slate-900 dark:text-white">{{ $b['name'] }}</h5>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">{{ $b['description'] }}</p>
                        </div>
                        <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800/80">
                            <div class="flex items-center justify-between text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">
                                <span>{{ $b['unlocked'] ? 'Unlocked' : 'Progress' }}</span>
                                <span>{{ $b['progress'] }} / {{ $b['target'] }}</span>
                            </div>
                            <div class="w-full bg-slate-200/80 dark:bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                <div class="{{ $b['unlocked'] ? 'bg-primary-600 dark:bg-primary-400' : 'bg-slate-400 dark:bg-slate-600' }} h-1.5 rounded-full transition-all duration-300"
                                     style="width: {{ ($b['progress'] / $b['target']) * 100 }}%"></div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
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
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300" for="prof-email">Email Address</label>
                            <span class="text-[11px] font-medium text-slate-400 dark:text-slate-500 flex items-center gap-1">
                                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                Read-only
                            </span>
                        </div>
                        <div class="relative">
                            <input value="{{ $email }}" id="prof-email" type="email" readonly disabled
                                   class="w-full pl-4 pr-10 py-2.5 bg-slate-100/80 dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800 rounded-xl text-sm text-slate-500 dark:text-slate-400 cursor-not-allowed select-none focus:outline-none transition-all">
                            <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 pointer-events-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </div>
                        </div>
                        <p class="mt-1.5 text-[11px] text-slate-400 dark:text-slate-500">Email address cannot be changed from profile.</p>
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
