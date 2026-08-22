{{-- 
Component: Public Mobile Navigation
File Path: resources/views/components/public-mobile-navigation.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Mobile drawer navigation for the public admissions site.
Accessible and responsive, triggered by the mobile toggle in public header.
Reuses the same JS toggle logic as portal mobile navigation.

Status: ✅ Production Ready
Version: 1.0 (adapted from portal)
--}}

<nav
    id="portal-mobile-navigation"
    class="portal-mobile-nav"
    aria-label="Mobile public navigation"
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
                <a href="{{ url('/') }}"
                   class="portal-mobile-nav__link @if(request()->is('/')) portal-mobile-nav__link--active @endif">
                    Home
                </a>
            </li>
            <li>
                <a href="{{ route('programmes.index') }}"
                   class="portal-mobile-nav__link @if(request()->routeIs('programmes.*')) portal-mobile-nav__link--active @endif">
                    Programmes
                </a>
            </li>
            <li>
                <a href="{{ route('login') }}"
                   class="portal-mobile-nav__link @if(request()->routeIs('login')) portal-mobile-nav__link--active @endif">
                    Applicant Login
                </a>
            </li>
            <li>
                <a href="{{ route('application.create') }}"
                   class="portal-mobile-nav__cta">
                    Start Application
                </a>
            </li>
        </ul>
    </div>
</nav>
