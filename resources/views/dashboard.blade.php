{{--
Component: Student Dashboard
File Path: resources/views/dashboard.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Provides the applicant with a clear overview of their admission journey,
payment, documents, decision and admission letter.

Status: Production Ready
Version: 4.0
--}}

@extends('layouts.app')

@section('title', 'Student Dashboard')

@section('content')

<div class="admission-page admission-page--dashboard">

    <div class="admission-atmosphere admission-atmosphere--right" aria-hidden="true"></div>

    <main class="admission-shell admission-shell--wide">

        {{-- Header --}}
        <header class="dashboard-header">

            <div>

                <div class="admission-eyebrow">
                    <span class="admission-eyebrow__dot" aria-hidden="true"></span>
                    Applicant Portal
                </div>

                <h1 class="admission-page-title">
                    Welcome back{{ auth()->user()->name ? ', ' . auth()->user()->name : '' }}
                </h1>

                <p class="admission-page-description">
                    Track your application, payment and admission progress from one place.
                </p>

            </div>

            <form
                method="POST"
                action="{{ route('logout') }}"
                class="dashboard-header__logout"
            >
                @csrf

                <button
                    type="submit"
                    class="admission-button admission-button--ghost admission-button--danger"
                >
                    Logout
                </button>
            </form>

        </header>

        @if(!$application)

            <section class="admission-empty-state admission-empty-state--dashboard">

                <div class="admission-empty-state__icon" aria-hidden="true">
                    +
                </div>

                <h2>
                    Your application starts here
                </h2>

                <p>
                    You have not started an application yet. Begin by selecting
                    your preferred programme.
                </p>

                <div class="admission-empty-state__action">
                    <x-portal-button
                        variant="primary"
                        href="{{ route('application.create') }}"
                    >
                        Start Application
                    </x-portal-button>
                </div>

            </section>

        @else

            {{-- Application hero --}}
            <section class="dashboard-application-hero">

                <div class="dashboard-application-hero__main">

                    <span class="dashboard-application-hero__label">
                        Application Number
                    </span>

                    <div class="dashboard-application-hero__number">
                        {{ $application->application_number }}
                    </div>

                    <p>
                        {{ $application->programme->name }}
                        @if($application->programme->degree_type)
                            <span>· {{ $application->programme->degree_type }}</span>
                        @endif
                    </p>

                </div>

                <div class="dashboard-status">

                    <span class="dashboard-status__label">
                        Application status
                    </span>

                    <span class="admission-status admission-status--{{ strtolower($application->status) }}">
                        {{ str_replace('_', ' ', $application->status) }}
                    </span>

                </div>

            </section>

            {{-- Quick overview --}}
            <section class="dashboard-overview-grid">

                <x-portal-card
                    title="Application"
                    subtitle="Current progress"
                    class="dashboard-card"
                >

                    <div class="dashboard-detail-list">

                        <div class="dashboard-detail">
                            <span>Programme</span>
                            <strong>
                                {{ $application->programme->name }}
                            </strong>
                        </div>

                        <div class="dashboard-detail">
                            <span>Submitted</span>
                            <strong>
                                {{ $application->submitted_at?->format('d M Y, H:i') ?? 'Not submitted' }}
                            </strong>
                        </div>

                    </div>

                    <div class="dashboard-card__action">

                        <x-portal-button
                            variant="secondary"
                            href="{{ route('application.status', $application) }}"
                        >
                            View Application
                        </x-portal-button>

                    </div>

                </x-portal-card>

                {{-- Payment --}}
                <x-portal-card
                    title="Payment"
                    subtitle="Application fee"
                    class="dashboard-card"
                >

                    @if($application->payment)

                        <div class="dashboard-payment-state">

                            <span class="admission-status admission-status--{{ strtolower($application->payment->status) }}">
                                {{ $application->payment->status }}
                            </span>

                            <strong>
                                ₦{{ number_format($application->payment->amountInNaira(), 2) }}
                            </strong>

                            <span>
                                {{ $application->payment->paid_at?->format('d M Y, H:i') ?? 'Payment date unavailable' }}
                            </span>

                        </div>

                    @else

                        <div class="dashboard-payment-state dashboard-payment-state--empty">

                            <strong>
                                Payment required
                            </strong>

                            <span>
                                Complete your application fee payment to continue.
                            </span>

                            <form
                                method="POST"
                                action="{{ route('payment.initialize', $application) }}"
                            >
                                @csrf

                                <x-portal-button
                                    variant="success"
                                    type="submit"
                                >
                                    Pay Application Fee
                                </x-portal-button>
                            </form>

                        </div>

                    @endif

                </x-portal-card>

            </section>

            {{-- Documents --}}
            <section class="admission-panel dashboard-section">

                <div class="admission-panel__header">

                    <div>
                        <span class="admission-panel__kicker">
                            Verification
                        </span>

                        <h2 class="admission-panel__title">
                            Your Documents
                        </h2>

                        <p class="admission-panel__description">
                            Review the documents attached to your application and their verification status.
                        </p>
                    </div>

                </div>

                @if($application->documents->count())

                    <div class="dashboard-document-list">

                        @foreach($application->documents as $doc)

                            <div class="dashboard-document">

                                <div class="dashboard-document__icon" aria-hidden="true">
                                    ↗
                                </div>

                                <div class="dashboard-document__content">

                                    <strong>
                                        {{ $doc->type->name }}
                                    </strong>

                                    @if($doc->rejection_reason)
                                        <span class="dashboard-document__reason">
                                            {{ $doc->rejection_reason }}
                                        </span>
                                    @endif

                                </div>

                                <div class="dashboard-document__meta">

                                    <span class="admission-status admission-status--{{ strtolower($doc->status) }}">
                                        {{ $doc->status }}
                                    </span>

                                    <a
                                        href="{{ asset('storage/' . $doc->path) }}"
                                        class="admission-link"
                                        target="_blank"
                                        rel="noopener"
                                    >
                                        View
                                    </a>

                                </div>

                            </div>

                        @endforeach

                    </div>

                @else

                    <div class="admission-inline-empty">
                        No documents have been uploaded yet.
                    </div>

                @endif

            </section>

            {{-- Admission decision --}}
            @if($application->decision)

                <section class="admission-panel dashboard-decision admission-decision--{{ strtolower($application->decision->status) }}">

                    <div class="admission-panel__header">

                        <div>

                            <span class="admission-panel__kicker">
                                Admissions Office
                            </span>

                            <h2 class="admission-panel__title">
                                Admission Decision
                            </h2>

                        </div>

                        <span class="admission-status admission-status--{{ strtolower($application->decision->status) }}">
                            {{ $application->decision->status }}
                        </span>

                    </div>

                    @if($application->decision->remarks)

                        <div class="dashboard-decision__remarks">
                            {{ $application->decision->remarks }}
                        </div>

                    @endif

                    <p class="dashboard-decision__meta">
                        Decided
                        {{ $application->decision->decided_at?->format('d M Y, H:i') ?? '—' }}
                        by
                        {{ optional($application->decision->officer)->name ?? 'Admissions Officer' }}
                    </p>

                </section>

            @endif

            {{-- Admission letter --}}
            @if($application->status === 'APPROVED' && $application->decision)

                <section class="dashboard-letter">

                    <div class="dashboard-letter__content">

                        <span class="dashboard-letter__eyebrow">
                            Admission confirmed
                        </span>

                        <h2>
                            Your admission letter is ready
                        </h2>

                        <p>
                            View and print your official admission letter.
                        </p>

                    </div>

                    <x-portal-button
                        variant="primary"
                        href="{{ route('admission.letter', $application) }}"
                    >
                        View Admission Letter
                    </x-portal-button>

                </section>

            @endif

        @endif

    </main>

</div>

@endsection