{{-- 
Component: Forgot Password
File Path: resources/views/auth/forgot-password.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Allows applicants to request a password reset link.

Design:
- Same visual language as admissions landing page.
- Semantic theme tokens.
- Focused recovery experience.
--}}

@extends('layouts.app')

@section('title', 'Forgot Password')

@section('content')

<section class="auth-page auth-page--compact">

    <div class="auth-atmosphere auth-atmosphere--primary" aria-hidden="true"></div>
    <div class="auth-atmosphere auth-atmosphere--accent" aria-hidden="true"></div>

    <div class="portal-container">

        <div class="auth-layout auth-layout--single">

            <div class="auth-panel">

                <div class="auth-card">

                    <div class="auth-card__header auth-card__header--center">

                        <span class="auth-card__eyebrow">
                            ACCOUNT RECOVERY
                        </span>

                        <h2>
                            Forgot password?
                        </h2>

                        <p>
                            Enter your email address and we'll send you
                            a secure link to create a new password.
                        </p>

                    </div>


                    @if (session('status'))
                        <div class="auth-alert auth-alert--success">
                            {{ session('status') }}
                        </div>
                    @endif


                    <form
                        method="POST"
                        action="{{ route('password.email') }}"
                        class="auth-form"
                    >
                        @csrf


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


                        <button
                            type="submit"
                            class="auth-submit"
                        >
                            <span>Email reset link</span>
                            <span aria-hidden="true">→</span>
                        </button>

                    </form>


                    <div class="auth-card__footer">

                        <p>
                            Remember your password?

                            <a href="{{ route('login') }}">
                                Back to sign in
                            </a>
                        </p>

                    </div>

                </div>


                <p class="auth-security-note">
                    <span aria-hidden="true">●</span>
                    Secure password recovery
                </p>

            </div>

        </div>

    </div>

</section>

@endsection