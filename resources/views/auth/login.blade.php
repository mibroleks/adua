{{-- 
Component: Applicant Login
File Path: resources/views/auth/login.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Provides secure applicant authentication.

Design:
- Uses admissions landing page as the visual language reference.
- Focused, editorial authentication experience.
- Fully preset/theme driven.
- Uses semantic theme tokens only.

Architecture:
- Laravel/Fortify authentication
- Shared application layout
- Shared auth stylesheet
--}}

@extends('layouts.app')

@section('title', 'Applicant Login')

@section('content')

<section class="auth-page">

    {{-- Atmospheric background --}}
    <div class="auth-atmosphere auth-atmosphere--primary" aria-hidden="true"></div>
    <div class="auth-atmosphere auth-atmosphere--accent" aria-hidden="true"></div>

    <div class="portal-container">

        <div class="auth-layout">

            {{-- ====================================================
                 EDITORIAL PANEL
            ===================================================== --}}
            <div class="auth-editorial">

                <span class="section-eyebrow">
                    APPLICANT PORTAL
                </span>

                <h1>
                    Your journey
                    <em>continues here.</em>
                </h1>

                <p class="auth-editorial__description">
                    Sign in to access your application, review outstanding
                    requirements, monitor your admission progress and stay
                    connected with the university.
                </p>

                <div class="auth-editorial__line">
                    <span></span>
                    <p>
                        One secure place for your admission journey.
                    </p>
                </div>

                <div class="auth-editorial__steps">
                    <div>
                        <strong>01</strong>
                        <span>Apply</span>
                    </div>

                    <div>
                        <strong>02</strong>
                        <span>Track</span>
                    </div>

                    <div>
                        <strong>03</strong>
                        <span>Become</span>
                    </div>
                </div>

            </div>


            {{-- ====================================================
                 LOGIN PANEL
            ===================================================== --}}
            <div class="auth-panel">

                <div class="auth-card">

                    <div class="auth-card__header">

                        <span class="auth-card__eyebrow">
                            WELCOME BACK
                        </span>

                        <h2>
                            Sign in
                        </h2>

                        <p>
                            Continue your admission journey.
                        </p>

                    </div>


                    {{-- Session status --}}
                    @if (session('status'))
                        <div class="auth-alert auth-alert--success">
                            {{ session('status') }}
                        </div>
                    @endif


                    {{-- Authentication form --}}
                    <form
                        method="POST"
                        action="{{ route('login') }}"
                        class="auth-form"
                    >
                        @csrf


                        {{-- Email --}}
                        <div class="auth-field">

                            <label for="email">
                                Email address
                            </label>

                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                autocomplete="email"
                                placeholder="you@example.com"
                                class="auth-input @error('email') is-invalid @enderror"
                            >

                            @error('email')
                                <p class="auth-error">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- Password --}}
                        <div class="auth-field">

                            <div class="auth-field__label-row">

                                <label for="password">
                                    Password
                                </label>

                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}">
                                        Forgot password?
                                    </a>
                                @endif

                            </div>

                            <input
                                id="password"
                                type="password"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="Enter your password"
                                class="auth-input @error('password') is-invalid @enderror"
                            >

                            @error('password')
                                <p class="auth-error">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- Remember --}}
                        <label class="auth-checkbox">

                            <input
                                type="checkbox"
                                name="remember"
                                {{ old('remember') ? 'checked' : '' }}
                            >

                            <span>
                                Remember me
                            </span>

                        </label>


                        {{-- Submit --}}
                        <button
                            type="submit"
                            class="auth-submit"
                        >
                            <span>Sign in</span>
                            <span aria-hidden="true">→</span>
                        </button>

                    </form>


                    {{-- Register --}}
                    @if (Route::has('register'))
                        <div class="auth-card__footer">

                            <p>
                                New applicant?

                                <a href="{{ route('register') }}">
                                    Create an account
                                </a>
                            </p>

                        </div>
                    @endif

                </div>


                <p class="auth-security-note">
                    <span aria-hidden="true">●</span>
                    Secure applicant access
                </p>

            </div>

        </div>

    </div>

</section>

@endsection