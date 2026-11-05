{{--
|--------------------------------------------------------------------------
| Component: Portal Header
|--------------------------------------------------------------------------
| File:
| resources/views/layouts/portal-header.blade.php
|
| Company: Ygrace Tech
| Author: Ibrahim Olalekan
|
| Purpose:
| Authenticated applicant/student portal top bar.
|
| Responsibilities:
| - Institutional identity
| - Notifications
| - Visible personal theme selector
| - User account menu
| - Responsive navigation
|
| Version: 2.5 (shared unread notification count)
|--------------------------------------------------------------------------
--}}

@php
    $theme = app(\App\Services\ThemeService::class);

    $institutionName = $theme->institutionName() ?? 'University';

    /*
    |--------------------------------------------------------------------------
    | Shared Unread Notification Count
    |--------------------------------------------------------------------------
    |
    | The portal layout passes this value to avoid repeating the
    | unread notification database query.
    |
    | The fallback also supports cases where this component is
    | rendered independently of the main portal layout.
    |
    */

    $unreadNotifications = $unreadNotificationCount
        ?? auth()->user()->unreadNotifications()->count();

    $currentThemeMode = $theme->portalMode(auth()->user());
@endphp

<header
    id="site-header"
    class="portal-topbar"
>
    <div class="portal-topbar__inner">

        {{-- Brand --}}
        <a
            href="{{ route('dashboard') }}"
            class="portal-topbar__brand"
            aria-label="{{ $institutionName }} Student Portal"
        >
            @if($theme->logoUrl())
                <span class="portal-topbar__brand-mark">
                    <img
                        src="{{ $theme->logoUrl() }}"
                        alt="{{ $institutionName }}"
                        class="portal-topbar__logo"
                    >
                </span>
            @endif

            <span class="portal-topbar__identity">
                <span class="portal-topbar__institution">
                    {{ $institutionName }}
                </span>

                <span class="portal-topbar__department">
                    Student Portal
                </span>
            </span>
        </a>

        {{-- Actions --}}
        <div class="portal-topbar__actions">

            {{-- Notifications --}}
            <a
                href="{{ route('notifications.index') }}"
                class="portal-topbar__action portal-topbar__notifications"
                aria-label="View notifications{{ $unreadNotifications ? ' (' . $unreadNotifications . ' unread)' : '' }}"
            >
                <span aria-hidden="true">🔔</span>

                @if($unreadNotifications > 0)
                    <span
                        class="portal-topbar__badge"
                        aria-hidden="true"
                    >
                        {{ $unreadNotifications > 99 ? '99+' : $unreadNotifications }}
                    </span>
                @endif
            </a>

            {{-- Personal Theme Selector --}}
            <div
                class="portal-topbar__theme-switch"
                data-theme-preference
                data-theme-preference-url="{{ route('theme.preference.update') }}"
                aria-label="Portal appearance"
            >
                <button
                    type="button"
                    class="portal-topbar__theme-button {{ $currentThemeMode === 'light' ? 'is-active' : '' }}"
                    data-theme-mode="light"
                    aria-label="Switch to light mode"
                    aria-pressed="{{ $currentThemeMode === 'light' ? 'true' : 'false' }}"
                    title="Light mode"
                >
                    <span aria-hidden="true">☀</span>
                    <span class="portal-topbar__theme-label">Light</span>
                </button>

                <button
                    type="button"
                    class="portal-topbar__theme-button {{ $currentThemeMode === 'dark' ? 'is-active' : '' }}"
                    data-theme-mode="dark"
                    aria-label="Switch to dark mode"
                    aria-pressed="{{ $currentThemeMode === 'dark' ? 'true' : 'false' }}"
                    title="Dark mode"
                >
                    <span aria-hidden="true">☾</span>
                    <span class="portal-topbar__theme-label">Dark</span>
                </button>
            </div>

            {{-- User Menu --}}
            <div class="portal-topbar__user">
                <button
                    type="button"
                    class="portal-topbar__user-toggle"
                    aria-label="Open user menu"
                    aria-expanded="false"
                    aria-controls="portal-user-menu"
                    data-user-menu-toggle
                >
                    {{ auth()->user()->name }}
                </button>

                <div
                    id="portal-user-menu"
                    class="portal-topbar__user-menu"
                    role="menu"
                    hidden
                    data-user-menu
                >
                    {{-- Profile --}}
                    <a
                        href="{{ route('profile') }}"
                        class="portal-topbar__user-link"
                        role="menuitem"
                    >
                        Profile
                    </a>

                    {{-- Logout --}}
                    <form
                        method="POST"
                        action="{{ route('logout') }}"
                        data-logout-form
                    >
                        @csrf

                        <button
                            type="submit"
                            class="portal-topbar__user-link"
                            role="menuitem"
                        >
                            Logout
                        </button>
                    </form>
                </div>
            </div>

            {{-- Mobile Menu Toggle --}}
            <button
                type="button"
                class="portal-topbar__mobile-toggle"
                aria-label="Open navigation"
                aria-expanded="false"
                aria-controls="portal-mobile-navigation"
                data-mobile-menu-toggle
            >
                ☰
            </button>

        </div>
    </div>
</header>
