{{--
|--------------------------------------------------------------------------
| Portal Layout
|--------------------------------------------------------------------------
| File:
| resources/views/layouts/portal.blade.php
|
| Company: Ygrace Tech
| Author: Ibrahim Olalekan
|
| Purpose:
| Main authenticated applicant/student portal shell.
|
| Structure:
| Header
| Sidebar
| Main content
| Footer
| Mobile navigation
|
| Status: ✅ Production Ready
| Version: 2.1 (footer updated to portal-student-footer)
|--------------------------------------------------------------------------
--}}

@php
    $theme = app(\App\Services\ThemeService::class);

    $institutionName = $theme->institutionName() ?? 'University';
    $faviconUrl = $theme->faviconUrl();
    $themeTokens = $theme->tokens();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-portal="student">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Browser theme --}}
    <meta name="theme-color" content="{{ $themeTokens['primary'] ?? '#73152A' }}">

    {{-- SEO --}}
    <title>
        @yield('title', 'Student Portal') — {{ $institutionName }}
    </title>
    <meta name="description" content="@yield('meta_description', 'Student portal for ' . $institutionName)">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">

    {{-- Application CSS + JS --}}
    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    {{-- Dynamic institutional theme --}}
    <link rel="stylesheet" href="{{ route('theme.css') }}">

    {{-- Favicon --}}
    @if($faviconUrl)
        <link rel="icon" href="{{ $faviconUrl }}" type="image/png">
    @endif

    @stack('head')
</head>

<body class="portal-body">

    {{-- Accessibility --}}
    <a href="#main-content" class="skip-link">Skip to content</a>

    {{-- Authenticated Header --}}
    @include('layouts.portal-header')

    {{-- Portal Shell --}}
    <div class="portal-shell">

        {{-- Desktop Sidebar --}}
        @include('layouts.portal-sidebar')

        {{-- Page Content --}}
        <main id="main-content" class="portal-main">
            @yield('content')
        </main>

    </div>

    {{-- Portal Footer (student-specific) --}}
    @include('layouts.portal-student-footer')

    {{-- Mobile Navigation --}}
    @include('components.portal-mobile-navigation')

    @stack('scripts')

</body>
</html>
