{{-- 
Component: Applicant Registration
File Path: resources/views/auth/register.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Creates a new applicant account.

Design:
- Matches Admissions Landing visual language.
- Preset/theme driven.
- Focused registration experience.
--}}

@extends('layouts.app')

@section('title', 'Create Applicant Account')

@section('content')

<section class="auth-page auth-page--register">

    <div class="auth-atmosphere auth-atmosphere--primary" aria-hidden="true"></div>
    <div class="auth-atmosphere auth-atmosphere--accent" aria-hidden="true"></div>

    <div class="portal-container">

        <div class="auth-layout">

            {{-- ====================================================
                 EDITORIAL PANEL
            ===================================================== --}}
            <div class="auth-editorial">

                <span class="section-eyebrow">
                    NEW APPLICANT
                </span>

                <h1>
                    Begin your
                    <em>journey.</em>
                </h1>

                <p class="auth-editorial__description">
                    Create your applicant account to explore programmes,
                    submit your application and follow your admission journey
                    from one secure place.
                </p>

                <div class="auth-editorial__line">
                    <span></span>

                    <p>
                        Create once. Apply securely. Stay informed.
                    </p>
                </div>

                <div class="auth-editorial__steps">

                    <div>
                        <strong>01</strong>
                        <span>Create</span>
                    </div>

                    <div>
                        <strong>02</strong>
                        <span>Apply</span>
                    </div>

                    <div>
                        <strong>03</strong>
                        <span>Track</span>
                    </div>

                </div>

            </div>


            {{-- ====================================================
                 REGISTRATION PANEL
            ===================================================== --}}
            <div class="auth-panel">

                <div class="auth-card auth-card--register">

                    <div class="auth-card__header">

                        <span class="auth-card__eyebrow">
                            GET STARTED
                        </span>

                        <h2>
                            Create account
                        </h2>

                        <p>
                            Start your admission journey today.
                        </p>

                    </div>


                    <form
                        method="POST"
                        action="{{ route('register') }}"
                        class="auth-form"
                    >
                        @csrf


                        {{-- Full name --}}
                        <div class="auth-field">

                            <label for="name">
                                Full name
                            </label>

                            <input
                                id="name"
                                type="text"
                                name="name"
                                value="{{ old('name') }}"
                                required
                                autofocus
                                autocomplete="name"
                                placeholder="Your full name"
                                class="auth-input @error('name') is-invalid @enderror"
                            >

                            @error('name')
                                <p class="auth-error">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


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

                            <label for="password">
                                Password
                            </label>

                            <input
                                id="password"
                                type="password"
                                name="password"
                                required
                                autocomplete="new-password"
                                placeholder="Create a secure password"
                                class="auth-input @error('password') is-invalid @enderror"
                            >

                            @error('password')
                                <p class="auth-error">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- Password confirmation --}}
                        <div class="auth-field">

                            <label for="password_confirmation">
                                Confirm password
                            </label>

                            <input
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                required
                                autocomplete="new-password"
                                placeholder="Re-enter your password"
                                class="auth-input"
                            >

                        </div>


                        {{-- Submit --}}
                        <button
                            type="submit"
                            class="auth-submit"
                        >
                            <span>Create account</span>
                            <span aria-hidden="true">→</span>
                        </button>

                    </form>


                    {{-- Login --}}
                    <div class="auth-card__footer">

                        <p>
                            Already registered?

                            <a href="{{ route('login') }}">
                                Sign in
                            </a>
                        </p>

                    </div>

                </div>


                <p class="auth-security-note">
                    <span aria-hidden="true">●</span>
                    Secure applicant registration
                </p>

            </div>

        </div>

    </div>

</section>

@endsection