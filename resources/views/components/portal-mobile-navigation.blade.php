{{--
Component: Portal Mobile Navigation
File Path: resources/views/components/portal-mobile-navigation.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Mobile drawer navigation for the authenticated student portal.
Mirrors sidebar links, accessible and responsive.
Triggered by the mobile toggle in portal-header.

Status: ✅ Production Ready
Version: 1.2 (added Application Status link)
--}}

<nav
    id="portal-mobile-navigation"
    class="portal-mobile-nav"
    aria-label="Mobile student navigation"
    data-mobile-menu
    hidden
>
    <div class="portal-mobile-nav__inner">

        {{-- Close button --}}
        <button
            type="button"
            class="portal-mobile-nav__close"
            aria-label="Close navigation menu"
            data-mobile-menu-toggle
        >
            ✕
        </button>

        <ul class="portal-mobile-nav__list">
            <li>
                <a href="{{ route('dashboard') }}"
                   class="portal-mobile-nav__link @if(request()->routeIs('dashboard')) portal-mobile-nav__link--active @endif">
                    Dashboard
                </a>
            </li>

            {{-- Apply --}}
            <li>
                <a href="{{ route('application.create') }}"
                   class="portal-mobile-nav__link @if(request()->routeIs('application.create')) portal-mobile-nav__link--active @endif">
                    Apply
                </a>
            </li>

            {{-- Application Status (conditional) --}}
            @if(isset($application) && $application)
                <li>
                    <a href="{{ route('application.status', $application) }}"
                       class="portal-mobile-nav__link @if(request()->routeIs('application.status')) portal-mobile-nav__link--active @endif">
                        Application Status
                    </a>
                </li>
            @endif

            <li>
                <a href="{{ route('applications.my') }}"
                   class="portal-mobile-nav__link @if(request()->routeIs('applications.my')) portal-mobile-nav__link--active @endif">
                    My Application
                </a>
            </li>
            <li>
                <a href="{{ route('applications.progress') }}"
                   class="portal-mobile-nav__link @if(request()->routeIs('applications.progress')) portal-mobile-nav__link--active @endif">
                    Progress
                </a>
            </li>
            <li>
                <a href="{{ route('applications.documents') }}"
                   class="portal-mobile-nav__link @if(request()->routeIs('applications.documents')) portal-mobile-nav__link--active @endif">
                    Documents
                </a>
            </li>
            <li>
                <a href="{{ route('applications.payment') }}"
                   class="portal-mobile-nav__link @if(request()->routeIs('applications.payment')) portal-mobile-nav__link--active @endif">
                    Payment
                </a>
            </li>
            <li>
                <a href="{{ route('applications.decision') }}"
                   class="portal-mobile-nav__link @if(request()->routeIs('applications.decision')) portal-mobile-nav__link--active @endif">
                    Decision
                </a>
            </li>
            <li>
                <a href="{{ route('profile') }}"
                   class="portal-mobile-nav__link @if(request()->routeIs('profile')) portal-mobile-nav__link--active @endif">
                    Profile
                </a>
            </li>
            <li>
                <a href="{{ route('notifications.index') }}"
                   class="portal-mobile-nav__link @if(request()->routeIs('notifications.index')) portal-mobile-nav__link--active @endif">
                    Notifications
                </a>
            </li>
            <li>
                <a href="{{ route('support.index') }}"
                   class="portal-mobile-nav__link @if(request()->routeIs('support.index')) portal-mobile-nav__link--active @endif">
                    Support
                </a>
            </li>
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="portal-mobile-nav__link">
                        Logout
                    </button>
                </form>
            </li>
        </ul>
    </div>
</nav>
