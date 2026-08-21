{{-- 
Component: Application Layout (Public Shell)
File Path: resources/views/layouts/app.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Main layout for the public admissions pages.
Loads dynamic branding (colors, logo, institution name, favicon) from settings via ThemeService.
Ensures officers can change branding without touching code.
Portal navigation is excluded here (moved to layouts/portal.blade.php).

Status: ✅ Production Ready
Version: 3.3 (split public vs portal shells)
--}}

@php
    $theme = app(\App\Services\ThemeService::class);
    $institutionName = $theme->institutionName() ?? 'University';
    $logoUrl = $theme->logoUrl();
    $faviconUrl = $theme->faviconUrl();
@endphp

<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    data-portal="admissions-public"
>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Dynamic theme color for browser UI --}}
    <meta name="theme-color" content="{{ $theme->tokens()['primary'] ?? '#73152A' }}">

    <title>@yield('title', 'Admissions') — {{ $institutionName }}</title>
    <meta name="description" content="@yield('meta_description', 'Admissions portal for ' . $institutionName)">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap"
        rel="stylesheet"
    >

    {{-- Tailwind + Dynamic Branding CSS --}}
    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])
    <link rel="stylesheet" href="{{ route('theme.css') }}">

    {{-- Dynamic Favicon --}}
    @if($faviconUrl)
        <link rel="icon" href="{{ $faviconUrl }}" type="image/png">
    @endif

    @stack('head')
</head>
<body class="portal-body theme-background theme-text antialiased">

    {{-- Accessibility --}}
    <a href="#main-content" class="skip-link">
        Skip to content
    </a>

    {{-- Public Navigation --}}
    @include('layouts.header')

    {{-- Main Content --}}
    <main id="main-content" class="min-h-[70vh]">
        @yield('content')
    </main>

    {{-- Institutional Footer --}}
    @include('layouts.footer')

    @stack('scripts')
</body>
</html>
