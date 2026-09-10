<div class="min-h-screen flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8">

    {{-- Logo + Heading --}}
    <div class="text-center mb-10">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl shadow-xl mb-6">
            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-white">Speech Club</h1>
        <p class="mt-2 text-slate-400 text-sm">Sign in to manage your club</p>
    </div>

    {{-- Card --}}
    <div class="w-full max-w-md">
        <div class="bg-white/10 backdrop-blur-md rounded-2xl shadow-2xl border border-white/10 p-8">
            <h2 class="text-xl font-semibold text-white mb-6">Welcome back</h2>

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
                        class="w-full px-4 py-2.5 bg-white/10 border border-white/20 rounded-xl text-white placeholder-slate-500
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm"
                    >
                    @error('email')
                        <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p>
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
                        class="w-full px-4 py-2.5 bg-white/10 border border-white/20 rounded-xl text-white placeholder-slate-500
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm"
                    >
                    @error('password')
                        <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember me --}}
                <div class="flex items-center">
                    <input
                        wire:model="remember"
                        id="remember"
                        type="checkbox"
                        class="h-4 w-4 rounded border-slate-600 bg-white/10 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-0"
                    >
                    <label for="remember" class="ml-2 text-sm text-slate-300">Remember me</label>
                </div>

                {{-- Submit --}}
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="w-full py-2.5 px-4 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500
                           text-white font-semibold rounded-xl shadow-lg transition-all duration-200 text-sm
                           disabled:opacity-60 disabled:cursor-not-allowed"
                >
                    <span wire:loading.remove>Sign in</span>
                    <span wire:loading class="flex items-center justify-center gap-2">
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
        <div class="mt-6 bg-white/5 border border-white/10 rounded-xl p-4 text-xs text-slate-400">
            <p class="font-semibold text-slate-300 mb-2">Demo accounts:</p>
            <div class="space-y-1">
                <p><span class="text-slate-300">Super Admin:</span> superadmin@speechclub.local</p>
                <p><span class="text-slate-300">Admin:</span> admin@speechclub.local</p>
                <p><span class="text-slate-300">President (Chola):</span> arun@speechclub.local</p>
                <p><span class="text-slate-300">Member (Chola):</span> suresh@speechclub.local</p>
                <p class="mt-2 text-slate-500">All passwords: <span class="text-slate-400">password</span></p>
            </div>
        </div>

    </div>
</div>
