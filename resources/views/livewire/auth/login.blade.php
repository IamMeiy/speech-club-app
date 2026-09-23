<div class="min-h-screen flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-slate-950 relative overflow-hidden">

    {{-- Background glowing accents --}}
    <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-primary-600/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-1/4 left-1/3 -translate-x-1/2 translate-y-1/2 w-80 h-80 bg-purple-600/10 rounded-full blur-3xl pointer-events-none"></div>

    {{-- Logo + Heading --}}
    <div class="text-center mb-8 relative z-10">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-primary-500 to-primary-700 rounded-2xl shadow-xl shadow-primary-500/25 mb-4 ring-1 ring-white/20">
            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/>
            </svg>
        </div>
        <h1 class="text-3xl font-extrabold text-white tracking-tight">Speech Club</h1>
        <p class="mt-1 text-slate-400 text-sm">Sign in to manage meetings, roles, and members</p>
    </div>

    {{-- Card --}}
    <div class="w-full max-w-md relative z-10">
        <div class="bg-slate-900/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-slate-800 p-8">
            <h2 class="text-xl font-bold text-white mb-6">Welcome back</h2>

            <form wire:submit="login" class="space-y-5">

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-300 mb-1.5">Email address</label>
                    <input
                        wire:model="email"
                        id="email"
                        type="email"
                        autocomplete="email"
                        placeholder="you@example.com"
                        class="w-full px-4 py-2.5 bg-slate-950/60 border border-slate-800 rounded-xl text-white placeholder-slate-500
                               focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all text-sm @error('email') border-rose-500/50 @enderror"
                    >
                    @error('email')
                        <p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-300 mb-1.5">Password</label>
                    <input
                        wire:model="password"
                        id="password"
                        type="password"
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="w-full px-4 py-2.5 bg-slate-950/60 border border-slate-800 rounded-xl text-white placeholder-slate-500
                               focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all text-sm @error('password') border-rose-500/50 @enderror"
                    >
                    @error('password')
                        <p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember me --}}
                <div class="flex items-center justify-between">
                    <label class="flex items-center cursor-pointer">
                        <input
                            wire:model="remember"
                            id="remember"
                            type="checkbox"
                            class="h-4 w-4 rounded border-slate-700 bg-slate-950 text-primary-600 focus:ring-primary-500"
                        >
                        <span class="ml-2 text-sm text-slate-400 select-none">Remember me</span>
                    </label>
                </div>

                {{-- Submit --}}
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="w-full py-3 px-4 bg-primary-600 hover:bg-primary-500 text-white font-semibold rounded-xl shadow-lg shadow-primary-500/25 transition-all duration-200 text-sm
                           disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer inline-flex items-center justify-center gap-2"
                >
                    <span wire:loading.remove>Sign in</span>
                    <span wire:loading.inline-flex class="items-center justify-center gap-2">
                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        Signing in…
                    </span>
                </button>

            </form>

        </div>

        {{-- Demo credentials hint --}}
        <div class="mt-6 bg-slate-900/60 backdrop-blur-md border border-slate-800/80 rounded-2xl p-5 text-xs text-slate-400">
            <p class="font-bold text-slate-200 mb-2.5 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Demo Accounts
            </p>
            <div class="space-y-1.5 font-mono text-[11px]">
                <p class="flex items-center justify-between"><span class="text-slate-300 font-sans">Super Admin:</span> <span class="text-slate-400">superadmin@speechclub.local</span></p>
                <p class="flex items-center justify-between"><span class="text-slate-300 font-sans">Admin:</span> <span class="text-slate-400">admin@speechclub.local</span></p>
                <p class="flex items-center justify-between"><span class="text-slate-300 font-sans">President:</span> <span class="text-slate-400">arun@speechclub.local</span></p>
                <p class="flex items-center justify-between"><span class="text-slate-300 font-sans">Member:</span> <span class="text-slate-400">suresh@speechclub.local</span></p>
                <div class="pt-2 mt-2 border-t border-slate-800 flex items-center justify-between font-sans">
                    <span class="text-slate-500">Password for all:</span>
                    <span class="text-primary-400 font-mono font-bold">password</span>
                </div>
            </div>
        </div>

    </div>
</div>
