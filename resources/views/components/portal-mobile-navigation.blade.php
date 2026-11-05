{{--
Component: Portal Mobile Navigation
File Path: resources/views/components/portal-mobile-navigation.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Mobile drawer navigation for the authenticated student portal.
My Application opens the complete application list.
My Admission Offers opens the applicant's issued official offers.
Mirrors desktop sidebar navigation.

Responsibilities:
- Responsive mobile navigation
- Active route indication
- Shared unread notification count
- Accessible navigation controls

Status: Production Ready
Version: 1.4 (mobile notification unread badge)
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
            &times;
        </button>

        <ul class="portal-mobile-nav__list">

            {{-- Dashboard --}}
            <li>
                <a
                    href="{{ route('dashboard') }}"
                    class="portal-mobile-nav__link @if(request()->routeIs('dashboard')) portal-mobile-nav__link--active @endif"
                    @if(request()->routeIs('dashboard'))
                        aria-current="page"
                    @endif
                >
                    Dashboard
                </a>
            </li>

            {{-- Apply --}}
            <li>
                <a
                    href="{{ route('application.create') }}"
                    class="portal-mobile-nav__link @if(request()->routeIs('application.create')) portal-mobile-nav__link--active @endif"
                    @if(request()->routeIs('application.create'))
                        aria-current="page"
                    @endif
                >
                    Apply
                </a>
            </li>

            {{-- Application Status --}}
            @if(isset($application) && $application)
                <li>
                    <a
                        href="{{ route('application.status', $application) }}"
                        class="portal-mobile-nav__link @if(request()->routeIs('application.status')) portal-mobile-nav__link--active @endif"
                        @if(request()->routeIs('application.status'))
                            aria-current="page"
                        @endif
                    >
                        Application Status
                    </a>
                </li>
            @endif

            {{-- My Application: complete application list and individual dossiers --}}
            <li>
                <a
                    href="{{ route('applications.index') }}"
                    class="portal-mobile-nav__link @if(request()->routeIs('applications.index', 'applications.show')) portal-mobile-nav__link--active @endif"
                    @if(request()->routeIs('applications.index', 'applications.show'))
                        aria-current="page"
                    @endif
                >
                    My Application
                </a>
            </li>

            {{-- My Admission Offers --}}
            <li>
                <a
                    href="{{ route('admission.offers.index') }}"
                    class="portal-mobile-nav__link @if(request()->routeIs('admission.offers.index', 'admission.offer')) portal-mobile-nav__link--active @endif"
                    @if(request()->routeIs('admission.offers.index', 'admission.offer'))
                        aria-current="page"
                    @endif
                >
                    My Admission Offers
                </a>
            </li>

            {{-- Progress --}}
            <li>
                <a
                    href="{{ route('applications.progress') }}"
                    class="portal-mobile-nav__link @if(request()->routeIs('applications.progress')) portal-mobile-nav__link--active @endif"
                    @if(request()->routeIs('applications.progress'))
                        aria-current="page"
                    @endif
                >
                    Progress
                </a>
            </li>

            {{-- Documents --}}
            <li>
                <a
                    href="{{ route('applications.documents') }}"
                    class="portal-mobile-nav__link @if(request()->routeIs('applications.documents')) portal-mobile-nav__link--active @endif"
                    @if(request()->routeIs('applications.documents'))
                        aria-current="page"
                    @endif
                >
                    Documents
                </a>
            </li>

            {{-- Payment --}}
            <li>
                <a
                    href="{{ route('applications.payment') }}"
                    class="portal-mobile-nav__link @if(request()->routeIs('applications.payment')) portal-mobile-nav__link--active @endif"
                    @if(request()->routeIs('applications.payment'))
                        aria-current="page"
                    @endif
                >
                    Payment
                </a>
            </li>

            {{-- Decision --}}
            <li>
                <a
                    href="{{ route('applications.decision') }}"
                    class="portal-mobile-nav__link @if(request()->routeIs('applications.decision')) portal-mobile-nav__link--active @endif"
                    @if(request()->routeIs('applications.decision'))
                        aria-current="page"
                    @endif
                >
                    Decision
                </a>
            </li>

            {{-- Profile --}}
            <li>
                <a
                    href="{{ route('profile') }}"
                    class="portal-mobile-nav__link @if(request()->routeIs('profile')) portal-mobile-nav__link--active @endif"
                    @if(request()->routeIs('profile'))
                        aria-current="page"
                    @endif
                >
                    Profile
                </a>
            </li>

            {{-- Notifications --}}
            <li>
                <a
                    href="{{ route('notifications.index') }}"
                    class="portal-mobile-nav__link @if(request()->routeIs('notifications.*')) portal-mobile-nav__link--active @endif"
                    @if(request()->routeIs('notifications.*'))
                        aria-current="page"
                    @endif
                    aria-label="Notifications{{ ($unreadNotificationCount ?? 0) > 0 ? ' (' . $unreadNotificationCount . ' unread)' : '' }}"
                >
                    <span>Notifications</span>

                    @if(($unreadNotificationCount ?? 0) > 0)
                        <span
                            class="portal-mobile-nav__badge"
                            aria-hidden="true"
                        >
                            {{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}
                        </span>
                    @endif
                </a>
            </li>

            {{-- Support --}}
            <li>
                <a
                    href="{{ route('support.index') }}"
                    class="portal-mobile-nav__link @if(request()->routeIs('support.*')) portal-mobile-nav__link--active @endif"
                    @if(request()->routeIs('support.*'))
                        aria-current="page"
                    @endif
                >
                    Support
                </a>
            </li>

            {{-- Logout --}}
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button
                        type="submit"
                        class="portal-mobile-nav__link"
                    >
                        Logout
                    </button>
                </form>
            </li>

        </ul>
    </div>
</nav>
