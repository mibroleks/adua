{{--
    Component: Officer Login Interface
    File Path: resources/views/filament/pages/auth/officer-login.blade.php
    Company: Ygrace Tech
    Author: Ibrahim Olalekan

    Purpose:
    Premium, institution-aware login interface for the Officer Panel.

    Responsibilities:
    - Display dynamic institution branding.
    - Provide an elegant institutional welcome experience.
    - Render Filament's native authentication schema.
    - Preserve Filament authentication actions and validation.
    - Support desktop, tablet and mobile layouts.
--}}

@php
    $theme = app(\App\Services\ThemeService::class);

    $institutionName = $theme->institutionName();
    $logoUrl = $theme->logoUrl();

    $institutionName = filled($institutionName)
        ? $institutionName
        : config('app.name', 'Institutional Management Portal');
@endphp

<x-filament-panels::page.simple heading="">

    <main class="officer-login">

        {{-- Institutional presentation panel --}}
        <section class="officer-login__visual">

            <div class="officer-login__visual-pattern"></div>

            <div class="officer-login__visual-content">

                {{-- Institutional identity --}}
                <div class="officer-login__identity">

                    @if (filled($logoUrl))
                        <img
                            src="{{ $logoUrl }}"
                            alt="{{ $institutionName }} logo"
                            class="officer-login__logo"
                        >
                    @else
                        <div class="officer-login__logo-fallback">
                            <x-filament::icon
                                icon="heroicon-o-academic-cap"
                                class="officer-login__logo-icon"
                            />
                        </div>
                    @endif

                    <div class="officer-login__identity-text">
                        <span class="officer-login__institution-name">
                            {{ $institutionName }}
                        </span>

                        <span class="officer-login__institution-caption">
                            INSTITUTIONAL MANAGEMENT PORTAL
                        </span>
                    </div>

                </div>

                {{-- Main welcome message --}}
                <div class="officer-login__message">

                    <span class="officer-login__eyebrow">
                        A NEW STANDARD OF INSTITUTIONAL EXCELLENCE
                    </span>

                    <h1>
                        Excellence in
                        <span>every operation.</span>
                    </h1>

                    <p>
                        A unified digital environment for managing
                        institutional operations, academic administration,
                        admissions and student services.
                    </p>

                </div>

                {{-- Security statement --}}
                <div class="officer-login__security">

                    <div class="officer-login__security-icon">
                        <x-filament::icon icon="heroicon-o-shield-check" />
                    </div>

                    <div>
                        <span class="officer-login__security-title">
                            Secure institutional access
                        </span>

                        <span class="officer-login__security-description">
                            Your workspace is protected by institutional
                            authentication.
                        </span>
                    </div>

                </div>

            </div>

            <div class="officer-login__visual-footer">
                <span>INSTITUTIONAL EXCELLENCE</span>
                <span class="officer-login__footer-divider"></span>
                <span>POWERED BY YGRACE TECH</span>
            </div>

        </section>


        {{-- Authentication panel --}}
        <section class="officer-login__form-panel">

            <div class="officer-login__form-container">

                {{-- Mobile institutional identity --}}
                <div class="officer-login__mobile-brand">

                    @if (filled($logoUrl))
                        <img
                            src="{{ $logoUrl }}"
                            alt="{{ $institutionName }} logo"
                        >
                    @else
                        <x-filament::icon
                            icon="heroicon-o-academic-cap"
                        />
                    @endif

                    <span>{{ $institutionName }}</span>

                </div>

                <div class="officer-login__form-heading">

                    <span class="officer-login__form-eyebrow">
                        OFFICER PORTAL
                    </span>

                    <h2>Welcome back.</h2>

                    <p>
                        Sign in with your authorised credentials
                        to access your institutional workspace.
                    </p>

                </div>

                {{-- Native Filament authentication form --}}
                <div class="officer-login__form">

                    {{ $this->content }}

                </div>

                <div class="officer-login__form-footer">

                    <div class="officer-login__form-footer-line"></div>

                    <div class="officer-login__form-footer-content">

                        <x-filament::icon
                            icon="heroicon-o-lock-closed"
                        />

                        <span>
                            Protected institutional access
                        </span>

                    </div>

                </div>

            </div>

            <div class="officer-login__copyright">
                <span>
                    © {{ date('Y') }} {{ $institutionName }}
                </span>

                <span>All rights reserved.</span>
            </div>

        </section>

    </main>

</x-filament-panels::page.simple>
