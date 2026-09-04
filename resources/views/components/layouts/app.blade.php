<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? ('Presensi Digital ' . (\App\Models\PengaturanSekolah::current()?->school_name ?? 'Sekolah')) }}</title>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        <!-- Favicon -->
        @php
            $sekolah = \App\Models\PengaturanSekolah::current();
            $favicon = $sekolah?->school_logo_path ? asset('storage/' . $sekolah->school_logo_path) : asset('favicon.ico');
        @endphp
        <link rel="icon" type="image/png" href="{{ $favicon }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @php
            $themeSettings = \App\Models\PengaturanSekolah::current();
        @endphp

        @if($themeSettings)
            <style>
                :root {
                    @if($themeSettings->theme_primary) --color-brand-primary: {{ $themeSettings->theme_primary }}; @endif
                    @if($themeSettings->theme_secondary) --color-brand-secondary: {{ $themeSettings->theme_secondary }}; @endif
                    @if($themeSettings->theme_accent) --color-brand-accent: {{ $themeSettings->theme_accent }}; @endif
                    @if($themeSettings->theme_warning) --color-brand-warning: {{ $themeSettings->theme_warning }}; @endif
                    @if($themeSettings->theme_danger) --color-brand-danger: {{ $themeSettings->theme_danger }}; @endif
                    @if($themeSettings->theme_info) --color-brand-info: {{ $themeSettings->theme_info }}; @endif
                }
            </style>
        @endif
        @livewireStyles
    </head>
    <body class="font-sans antialiased text-gray-900 bg-gray-50">
        {{ $slot }}
        @livewireScripts
    </body>
</html>
