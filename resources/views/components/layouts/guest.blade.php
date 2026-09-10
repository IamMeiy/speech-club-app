<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Speech Club') }} — Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,300;0,14..32,400;0,14..32,500;0,14..32,600;0,14..32,700;1,14..32,400&display=swap" rel="stylesheet">
    
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
<body class="h-full bg-slate-900 font-sans antialiased">
    {{ $slot }}
    @livewireScripts
</body>
</html>
