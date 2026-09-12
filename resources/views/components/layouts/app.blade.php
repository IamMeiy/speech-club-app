<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} — {{ config('app.name', 'Speech Club') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,300;0,14..32,400;0,14..32,500;0,14..32,600;0,14..32,700;1,14..32,400&display=swap"
        rel="stylesheet">

    {{-- Anti-FOUC theme & dark mode loader with Livewire wire:navigate persistence --}}
    <script>
        function applySpeechClubTheme() {
            var isDark = localStorage.getItem('theme-dark') === 'true' ||
                (!('theme-dark' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches);
            if (isDark) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
            var themes = ['indigo', 'emerald', 'blue', 'purple', 'rose', 'amber', 'cyan'];
            var savedTheme = localStorage.getItem('theme-color') || 'indigo';
            themes.forEach(function(t) {
                document.documentElement.classList.remove('theme-' + t);
            });
            if (savedTheme && savedTheme !== 'indigo') {
                document.documentElement.classList.add('theme-' + savedTheme);
            }
        }
        applySpeechClubTheme();
        document.addEventListener('livewire:navigated', applySpeechClubTheme);
        document.addEventListener('DOMContentLoaded', applySpeechClubTheme);
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body
    class="h-full font-sans antialiased bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 transition-colors duration-150"
    x-data="{
        darkMode: localStorage.getItem('theme-dark') === 'true' || (!('theme-dark' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
        currentTheme: localStorage.getItem('theme-color') || 'indigo',
        sidebarCollapsed: localStorage.getItem('sidebar-collapsed') === 'true',
        sidebarOpen: false,
        themePickerOpen: false,
        userMenuOpen: false,
        clubPickerOpen: false,
        themes: [
            { id: 'indigo', name: 'Indigo', hex: '#6366f1' },
            { id: 'emerald', name: 'Emerald', hex: '#10b981' },
            { id: 'blue', name: 'Ocean Blue', hex: '#3b82f6' },
            { id: 'purple', name: 'Royal Purple', hex: '#a855f7' },
            { id: 'rose', name: 'Rose', hex: '#f43f5e' },
            { id: 'amber', name: 'Amber', hex: '#f59e0b' },
            { id: 'cyan', name: 'Cyan', hex: '#06b6d4' }
        ],
        init() {
            this.syncTheme();
        },
        syncTheme() {
            if (typeof applySpeechClubTheme === 'function') {
                applySpeechClubTheme();
            }
        },
        toggleDarkMode() {
            this.darkMode = !this.darkMode;
            localStorage.setItem('theme-dark', this.darkMode);
            this.syncTheme();
        },
        setTheme(themeId) {
            this.currentTheme = themeId;
            localStorage.setItem('theme-color', themeId);
            this.syncTheme();
            this.themePickerOpen = false;
        },
        toggleSidebar() {
            this.sidebarCollapsed = !this.sidebarCollapsed;
            localStorage.setItem('sidebar-collapsed', this.sidebarCollapsed);
        }
    }">

    <div class="flex h-full">

        {{-- ------------------------------------------------------------------ --}}
        {{-- Mobile sidebar backdrop --}}
        {{-- ------------------------------------------------------------------ --}}
        <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 z-40 bg-slate-950/70 backdrop-blur-sm lg:hidden"
            @click="sidebarOpen = false" style="display: none;"></div>

        {{-- ------------------------------------------------------------------ --}}
        {{-- Sidebar --}}
        {{-- ------------------------------------------------------------------ --}}
        <aside
            :class="[
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
                sidebarCollapsed ? 'lg:w-20' : 'lg:w-64'
            ]"
            class="fixed inset-y-0 left-0 z-50 flex flex-col bg-slate-900 dark:bg-slate-900/95 border-r border-slate-800/80 transform transition-all duration-200 ease-in-out lg:translate-x-0 lg:static lg:z-auto select-none">
            {{-- App Logo / Name --}}
            <div class="flex items-center h-16 border-b border-slate-800 px-4 flex-shrink-0"
                :class="sidebarCollapsed ? 'justify-center' : 'justify-between'">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 overflow-hidden">
                    <div
                        class="w-9 h-9 bg-gradient-to-br from-primary-500 to-primary-700 rounded-xl flex items-center justify-center flex-shrink-0 shadow-md shadow-primary-500/20">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z" />
                        </svg>
                    </div>
                    <div x-show="!sidebarCollapsed" x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="min-w-0">
                        <p class="text-white font-bold text-sm tracking-tight leading-tight truncate">Speech Club</p>
                        <p class="text-primary-400 text-xs font-medium truncate">
                            @if ($currentClub)
                                {{ $currentClub->name }}
                            @else
                                All Clubs
                            @endif
                        </p>
                    </div>
                </a>

                {{-- Desktop collapse toggle in header --}}
                <button x-show="!sidebarCollapsed" @click="toggleSidebar()" type="button"
                    class="hidden lg:flex p-1.5 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-colors"
                    title="Collapse sidebar">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                    </svg>
                </button>
            </div>

            {{-- Navigation Items --}}
            <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1.5">

                {{-- Dashboard --}}
                <a href="{{ route('dashboard') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all group relative
                          {{ request()->routeIs('dashboard') ? 'bg-primary-600 text-white shadow-md shadow-primary-600/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                    :class="sidebarCollapsed ? 'justify-center' : ''" :title="sidebarCollapsed ? 'Dashboard' : ''">
                    <svg class="w-5 h-5 flex-shrink-0 transition-transform group-hover:scale-110" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Dashboard</span>
                </a>

                {{-- Meetings --}}
                @canany(['meetings.view', 'meetings.create'])
                    <a href="{{ route('meetings.index') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all group relative
                          {{ request()->routeIs('meetings.*') ? 'bg-primary-600 text-white shadow-md shadow-primary-600/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                        :class="sidebarCollapsed ? 'justify-center' : ''" :title="sidebarCollapsed ? 'Meetings' : ''">
                        <svg class="w-5 h-5 flex-shrink-0 transition-transform group-hover:scale-110" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="truncate">Meetings</span>
                    </a>
                @endcanany

                {{-- Speech Projects --}}
                <a href="{{ route('projects.index') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all group relative
                      {{ request()->routeIs('projects.*') ? 'bg-primary-600 text-white shadow-md shadow-primary-600/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                    :class="sidebarCollapsed ? 'justify-center' : ''" :title="sidebarCollapsed ? 'Speech Projects' : ''">
                    <svg class="w-5 h-5 flex-shrink-0 transition-transform group-hover:scale-110" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Speech Projects</span>
                </a>

                {{-- My Progress & Feedback --}}
                <a href="{{ route('progress.index') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all group relative
                      {{ request()->routeIs('progress.*') ? 'bg-primary-600 text-white shadow-md shadow-primary-600/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                    :class="sidebarCollapsed ? 'justify-center' : ''" :title="sidebarCollapsed ? 'My Progress & Feedback' : ''">
                    <svg class="w-5 h-5 flex-shrink-0 transition-transform group-hover:scale-110" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">My Progress & Feedback</span>
                </a>

                {{-- Members (club-scoped) --}}
                @can('users.view')
                    <a href="{{ route('members.index') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all group relative
                          {{ request()->routeIs('members.*') ? 'bg-primary-600 text-white shadow-md shadow-primary-600/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                        :class="sidebarCollapsed ? 'justify-center' : ''" :title="sidebarCollapsed ? 'Members' : ''">
                        <svg class="w-5 h-5 flex-shrink-0 transition-transform group-hover:scale-110" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="truncate">Members</span>
                    </a>
                @endcan

                {{-- Clubs (global users) --}}
                @can('clubs.view')
                    <a href="{{ route('clubs.index') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all group relative
                          {{ request()->routeIs('clubs.*') ? 'bg-primary-600 text-white shadow-md shadow-primary-600/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                        :class="sidebarCollapsed ? 'justify-center' : ''" :title="sidebarCollapsed ? 'Clubs' : ''">
                        <svg class="w-5 h-5 flex-shrink-0 transition-transform group-hover:scale-110" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="truncate">Clubs</span>
                    </a>
                @endcan

                {{-- Global Users (global admins) --}}
                @can('global-users.view')
                    <a href="{{ route('global-users.index') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all group relative
                          {{ request()->routeIs('global-users.*') ? 'bg-primary-600 text-white shadow-md shadow-primary-600/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                        :class="sidebarCollapsed ? 'justify-center' : ''" :title="sidebarCollapsed ? 'Global Users' : ''">
                        <svg class="w-5 h-5 flex-shrink-0 transition-transform group-hover:scale-110" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="truncate">Global Users</span>
                    </a>
                @endcan

                {{-- Administration Divider --}}
                @canany(['roles.view', 'permissions.manage'])
                    <div class="pt-3 pb-1" x-show="!sidebarCollapsed">
                        <p class="px-3 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Administration
                        </p>
                    </div>
                    <div class="my-2 border-t border-slate-800/60" x-show="sidebarCollapsed"></div>
                @endcanany

                @can('roles.view')
                    <a href="{{ route('roles.index') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all group relative
                          {{ request()->routeIs('roles.*') ? 'bg-primary-600 text-white shadow-md shadow-primary-600/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                        :class="sidebarCollapsed ? 'justify-center' : ''" :title="sidebarCollapsed ? 'Roles' : ''">
                        <svg class="w-5 h-5 flex-shrink-0 transition-transform group-hover:scale-110" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="truncate">Roles</span>
                    </a>
                @endcan

                @can('permissions.manage')
                    <a href="{{ route('permissions.index') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all group relative
                          {{ request()->routeIs('permissions.*') ? 'bg-primary-600 text-white shadow-md shadow-primary-600/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                        :class="sidebarCollapsed ? 'justify-center' : ''" :title="sidebarCollapsed ? 'Permissions' : ''">
                        <svg class="w-5 h-5 flex-shrink-0 transition-transform group-hover:scale-110" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="truncate">Permissions</span>
                    </a>
                @endcan

            </nav>

            {{-- Sidebar Footer with Profile & Collapse Toggle --}}
            <div class="p-3 border-t border-slate-800 flex flex-col gap-2 flex-shrink-0">
                <a href="{{ route('profile') }}"
                    class="flex items-center gap-3 p-2 rounded-xl hover:bg-slate-800/80 transition-colors group"
                    :class="sidebarCollapsed ? 'justify-center' : ''"
                    :title="sidebarCollapsed ? '{{ auth()->user()->name }}' : ''">
                    <div
                        class="w-8 h-8 rounded-xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center flex-shrink-0 font-bold text-xs text-white shadow-sm">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div x-show="!sidebarCollapsed" class="flex-1 min-w-0">
                        <p class="text-white text-xs font-semibold truncate">{{ auth()->user()->name }}</p>
                        <p class="text-slate-400 text-[11px] truncate">
                            {{ auth()->user()->roles->first()?->name ?? 'User' }}</p>
                    </div>
                </a>

                {{-- Expand toggle button when collapsed --}}
                <button x-show="sidebarCollapsed" @click="toggleSidebar()" type="button"
                    class="hidden lg:flex w-full items-center justify-center p-2 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-colors"
                    title="Expand sidebar">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </aside>

        {{-- ------------------------------------------------------------------ --}}
        {{-- Main Content Area --}}
        {{-- ------------------------------------------------------------------ --}}
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

            {{-- Top Navbar / Header --}}
            <header
                class="h-16 bg-white/85 dark:bg-slate-900/85 backdrop-blur-md border-b border-gray-200/80 dark:border-slate-800/80 flex items-center justify-between px-4 lg:px-8 flex-shrink-0 z-30 transition-colors">

                {{-- Left: Mobile menu toggle + breadcrumb --}}
                <div class="flex items-center gap-3">
                    {{-- Mobile menu button --}}
                    <button @click="sidebarOpen = !sidebarOpen"
                        class="lg:hidden p-2 rounded-xl text-gray-500 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors"
                        aria-label="Toggle menu">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    @isset($header)
                        <div class="text-sm font-medium text-gray-600 dark:text-slate-300">{{ $header }}</div>
                    @endisset
                </div>

                {{-- Right: Theme Selector + Dark Mode Toggle + Club Switcher + Profile Menu --}}
                <div class="flex items-center gap-2 sm:gap-3">

                    {{-- Theme Palette Picker --}}
                    <div class="relative">
                        <button @click="themePickerOpen = !themePickerOpen"
                            class="relative p-2 rounded-xl text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-800 dark:hover:text-slate-200 transition-colors"
                            title="Change Theme Color" aria-label="Change Theme Color">
                            {{-- Modern Color Palette Icon --}}
                            <svg class="w-5 h-5 transition-transform duration-200 hover:rotate-12" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75 0 4.638 3.238 8.52 7.575 9.537.494.116.925-.264.925-.772 0-.274-.112-.524-.294-.705-.285-.285-.456-.673-.456-1.11 0-.828.672-1.5 1.5-1.5h1.75a6.75 6.75 0 006.75-6.75C20.25 6.015 16.56 2.25 12 2.25z" />
                                <circle cx="8" cy="10" r="1.25" fill="currentColor" />
                                <circle cx="12" cy="7.5" r="1.25" fill="currentColor" />
                                <circle cx="16" cy="10" r="1.25" fill="currentColor" />
                                <circle cx="15.5" cy="14.5" r="1.25" fill="currentColor" />
                            </svg>
                            {{-- Active Theme Pip --}}
                            <span
                                class="absolute bottom-1 right-1 w-2.5 h-2.5 rounded-full ring-2 ring-white dark:ring-slate-900 bg-primary-500 transition-colors shadow-sm"></span>
                        </button>

                        {{-- Theme Picker Dropdown --}}
                        <div x-show="themePickerOpen" @click.outside="themePickerOpen = false"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-52 bg-white dark:bg-slate-900 border border-gray-100 dark:border-slate-800 rounded-2xl shadow-xl z-50 p-3"
                            style="display: none;">
                            <p
                                class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider mb-2 px-1">
                                Theme Accent</p>
                            <div class="space-y-1">
                                <template x-for="t in themes" :key="t.id">
                                    <button @click="setTheme(t.id)"
                                        class="w-full flex items-center justify-between px-2.5 py-2 rounded-xl text-xs font-medium transition-colors"
                                        :class="currentTheme === t.id ?
                                            'bg-primary-50 dark:bg-primary-950/50 text-primary-700 dark:text-primary-300 font-semibold' :
                                            'text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-800'">
                                        <div class="flex items-center gap-2.5">
                                            <span
                                                class="w-3.5 h-3.5 rounded-full ring-2 ring-white dark:ring-slate-900 flex-shrink-0"
                                                :style="'background-color: ' + t.hex"></span>
                                            <span x-text="t.name"></span>
                                        </div>
                                        <svg x-show="currentTheme === t.id"
                                            class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Dark / Light Mode Toggle --}}
                    <button @click="toggleDarkMode()"
                        class="p-2 rounded-xl text-gray-500 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-slate-800 hover:text-gray-800 dark:hover:text-slate-200 transition-colors"
                        title="Toggle Dark/Light Mode" aria-label="Toggle Dark/Light Mode">
                        {{-- Sun icon for dark mode --}}
                        <svg x-show="darkMode" class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        {{-- Moon icon for light mode --}}
                        <svg x-show="!darkMode" class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>

                    <div class="h-5 w-px bg-gray-200 dark:bg-slate-800 mx-1"></div>

                    {{-- Club Switcher (global users only) --}}
                    @if (auth()->user()->isGlobalUser())
                        <div class="relative">
                            <button @click="clubPickerOpen = !clubPickerOpen"
                                class="flex items-center gap-2 px-3 py-1.5 bg-gray-100 dark:bg-slate-800 hover:bg-gray-200 dark:hover:bg-slate-700 rounded-xl text-xs sm:text-sm font-semibold text-gray-700 dark:text-slate-200 transition-colors">
                                <svg class="w-4 h-4 text-primary-500 flex-shrink-0" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16" />
                                </svg>
                                <span
                                    class="truncate max-w-[130px] sm:max-w-none">{{ $currentClub ? $currentClub->name : 'All Clubs' }}</span>
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="clubPickerOpen" @click.outside="clubPickerOpen = false"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="absolute right-0 mt-2 w-56 bg-white dark:bg-slate-900 border border-gray-100 dark:border-slate-800 rounded-2xl shadow-xl z-50 py-1.5 overflow-hidden"
                                style="display: none;">
                                @if (auth()->user()->isSuperAdmin() || auth()->user()->clubs->count() > 1)
                                    <form method="POST" action="{{ route('switch-club') }}">
                                        @csrf
                                        <input type="hidden" name="club_id" value="">
                                        <button type="submit"
                                            class="w-full text-left px-4 py-2.5 text-xs sm:text-sm text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-800 flex items-center gap-2.5 transition-colors">
                                            <span
                                                class="w-2 h-2 rounded-full {{ !$currentClub ? 'bg-primary-500' : 'bg-gray-300 dark:bg-slate-700' }}"></span>
                                            All Clubs
                                        </button>
                                    </form>
                                @endif

                                @foreach ($availableClubs as $club)
                                    <form method="POST" action="{{ route('switch-club') }}">
                                        @csrf
                                        <input type="hidden" name="club_id" value="{{ $club->id }}">
                                        <button type="submit"
                                            class="w-full text-left px-4 py-2.5 text-xs sm:text-sm text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-800 flex items-center gap-2.5 transition-colors">
                                            <span
                                                class="w-2 h-2 rounded-full {{ $currentClub?->id === $club->id ? 'bg-primary-500' : 'bg-gray-300 dark:bg-slate-700' }}"></span>
                                            <span class="truncate">{{ $club->name }}</span>
                                        </button>
                                    </form>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- User Profile Menu --}}
                    <div class="relative">
                        <button @click="userMenuOpen = !userMenuOpen"
                            class="flex items-center gap-2 p-1 rounded-xl hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors">
                            <div
                                class="w-8 h-8 rounded-xl bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center shadow-sm">
                                <span
                                    class="text-white text-xs font-bold">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                            </div>
                            <svg class="w-3.5 h-3.5 text-gray-400 hidden sm:block" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="userMenuOpen" @click.outside="userMenuOpen = false"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-56 bg-white dark:bg-slate-900 border border-gray-100 dark:border-slate-800 rounded-2xl shadow-xl z-50 py-1.5 overflow-hidden"
                            style="display: none;">
                            <div class="px-4 py-3 border-b border-gray-100 dark:border-slate-800">
                                <p class="text-xs font-semibold text-gray-900 dark:text-white truncate">
                                    {{ auth()->user()->name }}</p>
                                <p class="text-[11px] text-gray-500 dark:text-slate-400 truncate">
                                    {{ auth()->user()->email }}</p>
                            </div>
                            <a href="{{ route('profile') }}"
                                class="flex items-center gap-2.5 px-4 py-2.5 text-xs sm:text-sm text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Profile
                            </a>
                            <a href="{{ route('progress.index') }}"
                                class="flex items-center gap-2.5 px-4 py-2.5 text-xs sm:text-sm text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors">
                                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                My Progress & Feedback
                            </a>
                            <div class="border-t border-gray-100 dark:border-slate-800 my-1"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="flex items-center gap-2.5 w-full text-left px-4 py-2 text-xs sm:text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/40 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    Sign out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </header>

            {{-- Main Scrollable Content --}}
            <main class="flex-1 overflow-y-auto bg-slate-50 dark:bg-slate-950 p-4 sm:p-6 lg:p-8 transition-colors">
                {{ $slot }}
            </main>

        </div>

    </div>

    @livewireScripts

    {{-- Custom Alert & Confirm Modal --}}
    <x-custom-alert-modal />

    {{-- Flash Notifications Toast --}}
    <div x-data="{ show: false, message: '', type: 'success' }"
        x-on:flash.window="message = $event.detail.message; type = $event.detail.type ?? 'success'; show = true; setTimeout(() => show = false, 4000)"
        x-show="show" x-transition:enter="transform ease-out duration-300 transition"
        x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed bottom-5 right-5 z-50 max-w-sm w-full shadow-2xl"
        style="display: none;">
        <div :class="type === 'success' ?
            'bg-white dark:bg-slate-900 border-emerald-500/40 text-emerald-900 dark:text-emerald-200' :
            'bg-white dark:bg-slate-900 border-red-500/40 text-red-900 dark:text-red-200'"
            class="flex items-start gap-3 p-4 rounded-2xl border shadow-xl backdrop-blur-md">
            <svg x-show="type === 'success'" class="w-5 h-5 flex-shrink-0 text-emerald-500 mt-0.5" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <svg x-show="type === 'error'" class="w-5 h-5 flex-shrink-0 text-red-500 mt-0.5" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wider mb-0.5"
                    x-text="type === 'success' ? 'Success' : 'Error'"></p>
                <p class="text-sm font-medium text-slate-700 dark:text-slate-300" x-text="message"></p>
            </div>
            <button @click="show = false"
                class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

</body>

</html>
