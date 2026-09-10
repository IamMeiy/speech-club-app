<div class="p-6 lg:p-8">
    <div class="max-w-3xl mx-auto space-y-8">

        {{-- Header --}}
        <div>
            <h1 class="text-2xl font-bold text-gray-900">My Profile</h1>
            <p class="text-gray-500 text-sm mt-1">Manage your personal information and account security</p>
        </div>

        {{-- Profile Summary Card --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex flex-col sm:flex-row items-center sm:items-start gap-6">
            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-2xl font-bold flex-shrink-0 shadow-md">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <div class="text-center sm:text-left flex-1 min-w-0">
                <h2 class="text-xl font-bold text-gray-900">{{ $user->name }}</h2>
                <p class="text-sm text-gray-500">{{ $user->email }}</p>
                <div class="flex flex-wrap gap-2 mt-3 justify-center sm:justify-start">
                    <span class="text-xs px-3 py-1 bg-indigo-50 text-indigo-700 font-medium rounded-full">
                        {{ $user->roles->first()?->name ?? 'User' }}
                    </span>
                    @if($user->isGlobalUser())
                        <span class="text-xs px-3 py-1 bg-purple-50 text-purple-700 font-medium rounded-full">
                            Global User
                        </span>
                    @endif
                    <span class="text-xs px-3 py-1 {{ $user->status === 'active' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }} font-medium rounded-full">
                        {{ ucfirst($user->status) }}
                    </span>
                </div>
                @if($user->clubs->isNotEmpty())
                <div class="mt-3 pt-3 border-t border-gray-100 flex flex-wrap gap-1.5 items-center justify-center sm:justify-start">
                    <span class="text-xs text-gray-400 font-medium">Clubs:</span>
                    @foreach($user->clubs as $club)
                        <span class="text-xs bg-gray-100 text-gray-700 px-2.5 py-0.5 rounded-md font-medium">{{ $club->name }}</span>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- Edit Info Form --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8">
            <h3 class="text-base font-bold text-gray-900 mb-6">Personal Information</h3>
            <form wire:submit="saveProfile" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5" for="prof-name">Full Name</label>
                    <input wire:model="name" id="prof-name" type="text"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-300 @enderror">
                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5" for="prof-email">Email Address</label>
                        <input wire:model="email" id="prof-email" type="email"
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('email') border-red-300 @enderror">
                        @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5" for="prof-phone">Phone</label>
                        <input wire:model="phone" id="prof-phone" type="text" placeholder="+91 98000 00000"
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-gray-100">
                    <button type="submit" wire:loading.attr="disabled"
                            class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors disabled:opacity-60">
                        <span wire:loading.remove wire:target="saveProfile">Update Profile</span>
                        <span wire:loading wire:target="saveProfile">Saving…</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- Change Password Form --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8">
            <h3 class="text-base font-bold text-gray-900 mb-6">Change Password</h3>
            <form wire:submit="changePassword" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5" for="curr-pw">Current Password</label>
                    <input wire:model="current_password" id="curr-pw" type="password"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('current_password') border-red-300 @enderror">
                    @error('current_password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5" for="new-pw">New Password</label>
                        <input wire:model="new_password" id="new-pw" type="password" placeholder="Min. 8 characters"
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('new_password') border-red-300 @enderror">
                        @error('new_password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5" for="conf-pw">Confirm New Password</label>
                        <input wire:model="confirm_password" id="conf-pw" type="password" placeholder="Re-type password"
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('confirm_password') border-red-300 @enderror">
                        @error('confirm_password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-gray-100">
                    <button type="submit" wire:loading.attr="disabled"
                            class="px-6 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-medium rounded-xl transition-colors disabled:opacity-60">
                        <span wire:loading.remove wire:target="changePassword">Change Password</span>
                        <span wire:loading wire:target="changePassword">Updating…</span>
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
