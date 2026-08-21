{{--
|--------------------------------------------------------------------------
| Component: Applicant Profile
|--------------------------------------------------------------------------
| File:
| resources/views/profile.blade.php
|
| Company: Ygrace Tech
| Author: Ibrahim Olalekan
|
| Purpose:
| Displays the applicant's profile information and account settings.
| Separate from application form data.
|
| Status: 🚦 Hardened
| Version: 2.0
|--------------------------------------------------------------------------
--}}

@extends('layouts.portal')

@section('title', 'My Profile')

@section('content')

<div class="admission-page admission-page--profile">

    <main class="admission-shell admission-shell--wide">

        {{-- ==========================================================
             PAGE HEADER
        =========================================================== --}}

        <header class="admission-page-header">

            <div class="admission-eyebrow">
                <span
                    class="admission-eyebrow__dot"
                    aria-hidden="true"
                ></span>

                Account Settings
            </div>

            <h1 class="admission-page-title">
                Profile
            </h1>

            <p class="admission-page-description">
                Manage your personal information and account details.
                Your profile information is separate from your admission
                application.
            </p>

        </header>


        <div class="profile-content">

            {{-- ======================================================
                 PERSONAL INFORMATION
            ======================================================= --}}

            <section
                class="admission-panel profile-panel"
                aria-labelledby="profile-information-title"
            >

                <div class="admission-panel__header">

                    <div class="admission-panel__icon" aria-hidden="true">
                        P
                    </div>

                    <div>
                        <span class="admission-panel__kicker">
                            Your account
                        </span>

                        <h2
                            id="profile-information-title"
                            class="admission-panel__title"
                        >
                            Personal Information
                        </h2>

                        <p class="admission-panel__description">
                            Basic information associated with your applicant
                            account.
                        </p>
                    </div>

                </div>


                <div class="profile-panel__body">

                    <div class="profile-detail-list">

                        <div class="profile-detail">

                            <span class="profile-detail__label">
                                Full Name
                            </span>

                            <strong class="profile-detail__value">
                                {{ auth()->user()->name }}
                            </strong>

                        </div>


                        <div class="profile-detail">

                            <span class="profile-detail__label">
                                Email
                            </span>

                            <strong class="profile-detail__value">
                                {{ auth()->user()->email }}
                            </strong>

                        </div>


                        <div class="profile-detail">

                            <span class="profile-detail__label">
                                Phone
                            </span>

                            <strong
                                class="profile-detail__value
                                {{ !auth()->user()->phone ? 'profile-detail__value--muted' : '' }}"
                            >
                                {{ auth()->user()->phone ?? 'Not provided' }}
                            </strong>

                        </div>


                        <div class="profile-detail">

                            <span class="profile-detail__label">
                                Address
                            </span>

                            <strong
                                class="profile-detail__value
                                {{ !auth()->user()->address ? 'profile-detail__value--muted' : '' }}"
                            >
                                {{ auth()->user()->address ?? 'Not provided' }}
                            </strong>

                        </div>

                    </div>

                </div>

            </section>


            {{-- ======================================================
                 ACCOUNT & SECURITY
            ======================================================= --}}

            <section
                class="admission-panel profile-panel"
                aria-labelledby="account-security-title"
            >

                <div class="admission-panel__header">

                    <div class="admission-panel__icon" aria-hidden="true">
                        S
                    </div>

                    <div>
                        <span class="admission-panel__kicker">
                            Security
                        </span>

                        <h2
                            id="account-security-title"
                            class="admission-panel__title"
                        >
                            Account & Security
                        </h2>

                        <p class="admission-panel__description">
                            Review your account verification and password
                            security.
                        </p>
                    </div>

                </div>


                <div class="profile-account-grid">

                    {{-- Email verification --}}

                    <div class="profile-account-card">

                        <div class="profile-account-card__top">

                            <div>

                                <span class="profile-account-card__label">
                                    Email Verification
                                </span>

                                <strong class="profile-account-card__value">
                                    {{ auth()->user()->email }}
                                </strong>

                            </div>

                            @if(auth()->user()->hasVerifiedEmail())

                                <span class="profile-verification profile-verification--verified">
                                    Verified
                                </span>

                            @else

                                <span class="profile-verification profile-verification--unverified">
                                    Not Verified
                                </span>

                            @endif

                        </div>


                        <p class="profile-account-card__description">

                            @if(auth()->user()->hasVerifiedEmail())

                                Your email address has been verified.

                            @else

                                Your email address still needs to be verified.

                            @endif

                        </p>

                    </div>


                    {{-- Password --}}

                    <div class="profile-account-card">

                        <div class="profile-account-card__top">

                            <div>

                                <span class="profile-account-card__label">
                                    Password
                                </span>

                                <strong class="profile-password-mask">
                                    ••••••••
                                </strong>

                            </div>

                        </div>


                        <p class="profile-account-card__description">
                            Keep your account secure by using a strong,
                            private password.
                        </p>


                        <div class="profile-account-card__action">

                            <a
                                href="{{ route('password.request') }}"
                                class="admission-button admission-button--ghost"
                            >
                                Change Password
                            </a>

                        </div>

                    </div>

                </div>


                <div class="profile-security-note">

                    <div
                        class="profile-security-note__icon"
                        aria-hidden="true"
                    >
                        i
                    </div>

                    <p>
                        Your account credentials are separate from your
                        admission application information. Never share your
                        password with another person.
                    </p>

                </div>

            </section>

        </div>

    </main>

</div>

@endsection