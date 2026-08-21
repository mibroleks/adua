{{--
|--------------------------------------------------------------------------
| Component: Portal Sidebar
|--------------------------------------------------------------------------
| File:
| resources/views/layouts/portal-sidebar.blade.php
|
| Company: Ygrace Tech
| Author: Ibrahim Olalekan
|
| Purpose:
| Authenticated applicant/student navigation.
|
| Status: ✅ Production Ready
| Version: 2.0
|--------------------------------------------------------------------------
--}}

<aside
    class="portal-sidebar"
    aria-label="Student navigation"
>
    <nav class="portal-sidebar__nav">
        <ul class="portal-sidebar__list">

            {{-- Dashboard --}}
            <li>
                <a
                    href="{{ route('dashboard') }}"
                    class="portal-sidebar__link @if(request()->routeIs('dashboard')) portal-sidebar__link--active @endif"
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
                    class="portal-sidebar__link @if(request()->routeIs('application.create')) portal-sidebar__link--active @endif"
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
                        class="portal-sidebar__link @if(request()->routeIs('application.status')) portal-sidebar__link--active @endif"
                        @if(request()->routeIs('application.status'))
                            aria-current="page"
                        @endif
                    >
                        Application Status
                    </a>
                </li>
            @endif

            {{-- My Application --}}
            <li>
                <a
                    href="{{ route('applications.my') }}"
                    class="portal-sidebar__link @if(request()->routeIs('applications.my')) portal-sidebar__link--active @endif"
                    @if(request()->routeIs('applications.my'))
                        aria-current="page"
                    @endif
                >
                    My Application
                </a>
            </li>

            {{-- Progress --}}
            <li>
                <a
                    href="{{ route('applications.progress') }}"
                    class="portal-sidebar__link @if(request()->routeIs('applications.progress')) portal-sidebar__link--active @endif"
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
                    class="portal-sidebar__link @if(request()->routeIs('applications.documents')) portal-sidebar__link--active @endif"
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
                    class="portal-sidebar__link @if(request()->routeIs('applications.payment')) portal-sidebar__link--active @endif"
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
                    class="portal-sidebar__link @if(request()->routeIs('applications.decision')) portal-sidebar__link--active @endif"
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
                    class="portal-sidebar__link @if(request()->routeIs('profile')) portal-sidebar__link--active @endif"
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
                    class="portal-sidebar__link @if(request()->routeIs('notifications.*')) portal-sidebar__link--active @endif"
                    @if(request()->routeIs('notifications.*'))
                        aria-current="page"
                    @endif
                >
                    Notifications
                </a>
            </li>

            {{-- Support --}}
            <li>
                <a
                    href="{{ route('support.index') }}"
                    class="portal-sidebar__link @if(request()->routeIs('support.*')) portal-sidebar__link--active @endif"
                    @if(request()->routeIs('support.*'))
                        aria-current="page"
                    @endif
                >
                    Support
                </a>
            </li>

        </ul>
    </nav>
</aside>