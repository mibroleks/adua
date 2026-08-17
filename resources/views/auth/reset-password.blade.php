{{-- 
Component: Reset Password
File Path: resources/views/auth/reset-password.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Allows an applicant to establish a new password
using a valid password reset token.

Design:
- Matches admissions landing page visual language.
- Semantic theme tokens.
- Preset driven.
--}}

@extends('layouts.app')

@section('title', 'Reset Password')

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
                            PASSWORD RESET
                        </span>

                        <h2>
                            Set new password
                        </h2>

                        <p>
                            Choose a new password for your applicant account.
                        </p>

                    </div>


                    <form
                        method="POST"
                        action="{{ route('password.update') }}"
                        class="auth-form"
                    >
                        @csrf


                        <input
                            type="hidden"
                            name="token"
                            value="{{ $request->route('token') }}"
                        >


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


                        {{-- New password --}}
                        <div class="auth-field">

                            <label for="password">
                                New password
                            </label>

                            <input
                                id="password"
                                type="password"
                                name="password"
                                required
                                autocomplete="new-password"
                                placeholder="Enter a new password"
                                class="auth-input @error('password') is-invalid @enderror"
                            >

                            @error('password')
                                <p class="auth-error">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- Confirmation --}}
                        <div class="auth-field">

                            <label for="password_confirmation">
                                Confirm new password
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


                        <button
                            type="submit"
                            class="auth-submit"
                        >
                            <span>Reset password</span>
                            <span aria-hidden="true">→</span>
                        </button>

                    </form>


                    <div class="auth-card__footer">

                        <p>
                            Remembered your password?

                            <a href="{{ route('login') }}">
                                Back to sign in
                            </a>
                        </p>

                    </div>

                </div>


                <p class="auth-security-note">
                    <span aria-hidden="true">●</span>
                    Secure account recovery
                </p>

            </div>

        </div>

    </div>

</section>

@endsection