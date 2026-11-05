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
| Responsibilities:
| - Institutional branding
| - Personal light/dark theme support
| - Responsive portal layout
| - Accessibility
| - Application asset loading
| - Shared unread notification count
|
| Status: Production Ready
| Version: 2.3 (shared portal notification count)
|--------------------------------------------------------------------------
--}}

@php
    $theme = app(\App\Services\ThemeService::class);

    $institutionName = $theme->institutionName() ?? 'University';

    $faviconUrl = $theme->faviconUrl();

    /*
    |--------------------------------------------------------------------------
    | Shared Unread Notification Count
    |--------------------------------------------------------------------------
    |
    | Calculate the authenticated user's unread notification count once.
    | The same value is passed to the header and mobile navigation.
    |
    */

    $unreadNotificationCount = auth()->user()
        ->unreadNotifications()
        ->count();

    /*
    |--------------------------------------------------------------------------
    | Effective Portal Theme
    |--------------------------------------------------------------------------
    |
    | Resolve the authenticated user's personal theme preference.
    | If no personal preference exists, ThemeService falls back to
    | the institution's configured theme mode.
    |
    */

    $portalThemeMode = $theme->portalMode(auth()->user());

    $themeTokens = $theme->tokens($portalThemeMode);
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-portal="student">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- CSRF protection for authenticated portal interactions --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Browser theme --}}
    <meta
        name="theme-color"
        content="{{ $themeTokens['primary'] ?? '#73152A' }}"
    >

    {{-- SEO --}}
    <title>
        @yield('title', 'Student Portal') — {{ $institutionName }}
    </title>

    <meta
        name="description"
        content="@yield('meta_description', 'Student portal for ' . $institutionName)"
    >

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap"
        rel="stylesheet"
    >

    {{-- Application CSS + JS --}}
    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    {{-- Dynamic institutional and personal theme --}}
    <link
        id="portal-theme-stylesheet"
        rel="stylesheet"
        href="{{ route('theme.css') }}"
        data-portal-theme-stylesheet
        data-base-url="{{ route('theme.css') }}"
    >

    {{-- Favicon --}}
    @if($faviconUrl)
        <link
            rel="icon"
            href="{{ $faviconUrl }}"
            type="image/png"
        >
    @endif

    @stack('head')
</head>

<body class="portal-body">

    {{-- Accessibility --}}
    <a
        href="#main-content"
        class="skip-link"
    >
        Skip to content
    </a>

    {{-- Authenticated Header --}}
    @include('layouts.portal-header', [
        'unreadNotificationCount' => $unreadNotificationCount,
    ])

    {{-- Portal Shell --}}
    <div class="portal-shell">

        {{-- Desktop Sidebar --}}
        @include('layouts.portal-sidebar')

        {{-- Page Content --}}
        <main
            id="main-content"
            class="portal-main"
        >
            @yield('content')
        </main>

    </div>

    {{-- Portal Footer (student-specific) --}}
    @include('layouts.portal-student-footer')

    {{-- Mobile Navigation --}}
    @include('components.portal-mobile-navigation', [
        'unreadNotificationCount' => $unreadNotificationCount,
    ])

    {{-- Page-specific scripts --}}
    @stack('scripts')

</body>
</html>
