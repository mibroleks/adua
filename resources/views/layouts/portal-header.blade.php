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
| Status: ✅ Production Ready
| Version: 2.3 (fixed mobile menu toggle integration)
|--------------------------------------------------------------------------
--}}

@php
    $theme = app(\App\Services\ThemeService::class);

    $institutionName =
        $theme->institutionName() ?? 'University';

    $unreadNotifications =
        auth()->user()->unreadNotifications->count();
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

{{-- Mobile Navigation --}}
@include('components.portal-mobile-navigation')
