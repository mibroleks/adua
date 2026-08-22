{{--
Component: Application Header (Public Navigation)
File Path: resources/views/layouts/header.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Version: 3.3 (split public vs portal shells, preserved Apply link, added mobile nav include)
Design: Premium Institutional Shell (theme‑driven, accessible, responsive)
--}}

@php
    $theme = app(\App\Services\ThemeService::class);
    $institutionName = $theme->institutionName() ?? 'University';
@endphp

<header class="portal-header">

    {{-- ============================================================
         ANNOUNCEMENT BAR
    ============================================================= --}}
    <div class="portal-header__announcement">
        <div class="portal-header__announcement-inner">

            <p class="portal-header__announcement-message">
                Admissions are now open
            </p>

            <a
                href="{{ route('application.create') }}"
                class="portal-header__announcement-action"
            >
                Apply now
            </a>

        </div>
    </div>

    {{-- ============================================================
         PRIMARY NAVIGATION (Public)
    ============================================================= --}}
    <nav class="portal-header__nav">
        <div class="portal-header__nav-inner">

            {{-- Brand --}}
            <a
                href="{{ url('/') }}"
                class="portal-header__brand"
                aria-label="{{ $institutionName }} Admissions"
            >
                @if($theme->logoUrl())
                    <span class="portal-header__brand-mark">
                        <img
                            src="{{ $theme->logoUrl() }}"
                            alt="{{ $institutionName }}"
                            class="portal-header__logo"
                        >
                    </span>
                @endif

                <span class="portal-header__identity">
                    <span class="portal-header__institution">
                        {{ $institutionName }}
                    </span>
                    <span class="portal-header__department">
                        Admissions
                    </span>
                </span>
            </a>

            {{-- Desktop navigation --}}
            <div class="portal-header__navigation">
                <a
                    href="{{ url('/') }}"
                    class="portal-header__link"
                    @if(request()->is('/')) aria-current="page" @endif
                >
                    Home
                </a>

                <a
                    href="{{ route('programmes.index') }}"
                    class="portal-header__link"
                    @if(request()->routeIs('programmes.*')) aria-current="page" @endif
                >
                    Programmes
                </a>

                {{-- Public applicant entry point --}}
                <a
                    href="{{ route('login') }}"
                    class="portal-header__link"
                    @if(request()->routeIs('login')) aria-current="page" @endif
                >
                    Applicant Login
                </a>

                {{-- Public CTA preserved --}}
                <a
                    href="{{ route('application.create') }}"
                    class="portal-header__cta"
                >
                    Start Application
                </a>
            </div>

            {{-- Mobile navigation trigger --}}
            <button
                type="button"
                class="portal-header__mobile-toggle"
                aria-label="Open navigation menu"
                aria-expanded="false"
                aria-controls="portal-mobile-navigation"
                data-mobile-menu-toggle
            >
                <span aria-hidden="true">☰</span>
            </button>
        </div>
    </nav>

    {{-- ============================================================
         MOBILE NAVIGATION INCLUDE
    ============================================================= --}}
    @include('components.public-mobile-navigation')
</header>
