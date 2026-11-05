{{--
|--------------------------------------------------------------------------
| Student Dashboard
|--------------------------------------------------------------------------
| File: resources/views/dashboard.blade.php
| Company: Ygrace Tech
| Author: Ibrahim Olalekan
| Purpose: Applicant-wide admission journey dashboard.
|
| Version: 3.1 (Multi-Application Dashboard Hardening)
|--------------------------------------------------------------------------
--}}

@extends('layouts.portal')

@section('title', 'Student Dashboard')

@section('meta_description')
    Track all your applications, payments, documents and admission progress from one place.
@endsection

@section('content')

<div class="admission-page admission-page--dashboard">

    <div class="admission-atmosphere admission-atmosphere--right" aria-hidden="true"></div>
    <div class="admission-atmosphere admission-atmosphere--left" aria-hidden="true"></div>

    <div class="admission-shell admission-shell--wide">

        {{-- Dashboard Header --}}
        <header class="dashboard-header">

            <div class="dashboard-header__content">

                <div class="admission-eyebrow">
                    <span class="admission-eyebrow__dot" aria-hidden="true"></span>
                    Applicant Portal
                </div>

                <h1 class="admission-page-title">
                    Welcome back{{ auth()->user()->name ? ', ' . auth()->user()->name : '' }}
                </h1>

                <p class="admission-page-description">
                    Your central overview of applications, payments,
                    document verification and admission decisions.
                </p>

            </div>

            <div class="dashboard-header__action">
                <x-portal-button
                    variant="primary"
                    href="{{ route('application.create') }}"
                >
                    Start New Application
                </x-portal-button>
            </div>

        </header>

        {{-- ==========================================================
             APPLICANT OVERVIEW
        =========================================================== --}}

        <section
            class="dashboard-metrics"
            aria-label="Applicant overview"
        >

            <article class="dashboard-metric dashboard-metric--applications">

                <div class="dashboard-metric__top">
                    <span class="dashboard-metric__label">
                        My Applications
                    </span>

                    <span class="dashboard-metric__icon" aria-hidden="true">
                        01
                    </span>
                </div>

                <strong class="dashboard-metric__value">
                    {{ $summary['applications_count'] ?? 0 }}
                </strong>

                <span class="dashboard-metric__description">
                    Applications started or submitted
                </span>

                <a
                    href="{{ route('applications.index') }}"
                    class="dashboard-metric__link"
                >
                    View applications <span aria-hidden="true">→</span>
                </a>

            </article>

            <article class="dashboard-metric dashboard-metric--offers">

                <div class="dashboard-metric__top">
                    <span class="dashboard-metric__label">
                        Official Offers
                    </span>

                    <span class="dashboard-metric__icon" aria-hidden="true">
                        02
                    </span>
                </div>

                <strong class="dashboard-metric__value">
                    {{ $summary['official_offers_count'] ?? 0 }}
                </strong>

                <span class="dashboard-metric__description">
                    Currently issued admission offers
                </span>

                <a
                    href="{{ route('admission.offers.index') }}"
                    class="dashboard-metric__link"
                >
                    View admission offers <span aria-hidden="true">→</span>
                </a>

            </article>

            <article class="dashboard-metric dashboard-metric--payments">

                <div class="dashboard-metric__top">
                    <span class="dashboard-metric__label">
                        Payment Records
                    </span>

                    <span class="dashboard-metric__icon" aria-hidden="true">
                        03
                    </span>
                </div>

                <strong class="dashboard-metric__value">
                    {{ $summary['applications_with_payment'] ?? 0 }}
                </strong>

                <span class="dashboard-metric__description">
                    Applications with payment records
                </span>

                <a
                    href="{{ route('applications.payment') }}"
                    class="dashboard-metric__link"
                >
                    Review payments <span aria-hidden="true">→</span>
                </a>

            </article>

            <article class="dashboard-metric dashboard-metric--documents">

                <div class="dashboard-metric__top">
                    <span class="dashboard-metric__label">
                        Uploaded Documents
                    </span>

                    <span class="dashboard-metric__icon" aria-hidden="true">
                        04
                    </span>
                </div>

                <strong class="dashboard-metric__value">
                    {{ $summary['documents_count'] ?? 0 }}
                </strong>

                <span class="dashboard-metric__description">
                    Documents across all applications
                </span>

                <a
                    href="{{ route('applications.documents') }}"
                    class="dashboard-metric__link"
                >
                    Manage documents <span aria-hidden="true">→</span>
                </a>

            </article>

        </section>

        {{-- ==========================================================
             NO APPLICATIONS
        =========================================================== --}}

        @if($applications->isEmpty())

            <section
                class="admission-empty-state admission-empty-state--dashboard"
                aria-labelledby="empty-dashboard-title"
            >

                <div class="admission-empty-state__icon" aria-hidden="true">
                    +
                </div>

                <span class="admission-panel__kicker">
                    Your Admission Journey
                </span>

                <h2 id="empty-dashboard-title">
                    Your application journey starts here
                </h2>

                <p>
                    You have not started an application yet.
                    Begin by selecting your preferred programme.
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

            {{-- ======================================================
                 ALL APPLICANT APPLICATIONS
            ======================================================= --}}

            <section
                class="admission-panel dashboard-section dashboard-applications"
                aria-labelledby="applications-title"
            >

                <div class="admission-panel__header">

                    <div>
                        <span class="admission-panel__kicker">
                            Application Centre
                        </span>

                        <h2
                            id="applications-title"
                            class="admission-panel__title"
                        >
                            My Applications
                        </h2>

                        <p class="admission-panel__description">
                            Monitor the progress of each programme application independently.
                        </p>
                    </div>

                    <span class="admission-offers-list__count">
                        {{ $applications->count() }}
                        {{ \Illuminate\Support\Str::plural('Application', $applications->count()) }}
                    </span>

                </div>

                <div class="dashboard-applications-list">

                    @foreach($applications as $application)

                        @php
                            $applicationStatus = $application->application_status ?? 'draft';

                            $statusClass = \Illuminate\Support\Str::slug(
                                (string) $applicationStatus
                            );

                            $programme = $application->programme;
                            $decision = $application->decision;
                            $payment = $application->payment;

                            $offer = $issuedOffers->get($application->id);

                            $isProcessingApproved = strtoupper(
                                (string) ($decision?->decision ?? '')
                            ) === 'APPROVED';

                            $applicationRoute = $applicationStatus ===
                                \App\Models\Application::STATUS_CORRECTION_REQUIRED
                                ? route('application.correct', $application)
                                : route('applications.show', $application);

                            $documentCount = $application->documents->count();
                        @endphp

                        <article class="dashboard-application-item">

                            {{-- Application Identity --}}
                            <div class="dashboard-application-item__header">

                                <div class="dashboard-application-item__identity">

                                    <span class="dashboard-application-item__eyebrow">
                                        Application Number
                                    </span>

                                    <h3 class="dashboard-application-item__number">
                                        {{ $application->application_number }}
                                    </h3>

                                    <p class="dashboard-application-item__programme">
                                        {{ $programme?->name ?? 'Programme information unavailable' }}

                                        @if($programme?->degree_type)
                                            <span>· {{ $programme->degree_type }}</span>
                                        @endif
                                    </p>

                                </div>

                                <div class="dashboard-status">

                                    <span class="dashboard-status__label">
                                        Application Status
                                    </span>

                                    <span class="admission-status admission-status--{{ $statusClass }}">
                                        {{ $application->statusLabel() }}
                                    </span>

                                </div>

                            </div>

                            {{-- Application Information --}}
                            <div class="dashboard-application-item__details">

                                <div class="dashboard-detail">
                                    <span>Submitted</span>

                                    <strong>
                                        {{ $application->submitted_at?->format('d M Y, H:i') ?? 'Not submitted' }}
                                    </strong>
                                </div>

                                <div class="dashboard-detail">
                                    <span>Payment</span>

                                    <strong>
                                        {{ $payment
                                            ? $application->paymentLabel()
                                            : 'No payment record' }}
                                    </strong>
                                </div>

                                <div class="dashboard-detail">
                                    <span>Documents</span>

                                    <strong>
                                        {{ $documentCount }}
                                        {{ \Illuminate\Support\Str::plural('file', $documentCount) }}
                                    </strong>
                                </div>

                                <div class="dashboard-detail">
                                    <span>Processing Decision</span>

                                    <strong>
                                        {{ $decision?->decision ?? 'Pending' }}
                                    </strong>
                                </div>

                                <div class="dashboard-detail">
                                    <span>Institutional Admission Outcome</span>

                                    <strong>
                                        {{ $decision?->admission_status ?? 'Pending' }}
                                    </strong>
                                </div>

                            </div>

                            {{-- Application Actions --}}
                            <div class="dashboard-application-item__actions">

                                <x-portal-button
                                    variant="secondary"
                                    href="{{ $applicationRoute }}"
                                >
                                    {{ $applicationStatus === \App\Models\Application::STATUS_CORRECTION_REQUIRED
                                        ? 'Correct Application'
                                        : 'View Application' }}
                                </x-portal-button>

                                @if($isProcessingApproved)

                                    <x-portal-button
                                        variant="secondary"
                                        href="{{ route('admission.letter', $application) }}"
                                    >
                                        View Outcome Letter
                                    </x-portal-button>

                                @endif

                                @if($offer)

                                    <x-portal-button
                                        variant="primary"
                                        href="{{ route('admission.offer', $application) }}"
                                    >
                                        View Official Offer
                                    </x-portal-button>

                                @endif

                            </div>

                            {{-- Admissions Office Update --}}
                            @if($decision)

                                <div class="dashboard-application-item__decision">

                                    <div class="dashboard-application-item__decision-heading">

                                        <div>
                                            <span class="dashboard-application-item__subheading">
                                                Admissions Office
                                            </span>

                                            <strong>
                                                Decision Update
                                            </strong>
                                        </div>

                                        <span class="admission-status admission-status--{{ \Illuminate\Support\Str::slug((string) $decision->decision) }}">
                                            {{ $decision->decision }}
                                        </span>

                                    </div>

                                    @if($decision->remarks)

                                        <p class="dashboard-decision__remarks">
                                            {{ $decision->remarks }}
                                        </p>

                                    @endif

                                    <p class="dashboard-decision__meta">
                                        Decided
                                        {{ $decision->decided_at?->format('d M Y, H:i') ?? '—' }}

                                        by
                                        {{ optional($decision->officer)->name ?? 'Admissions Officer' }}
                                    </p>

                                </div>

                            @endif

                            {{-- Documents for This Application --}}
                            <div class="dashboard-application-item__documents">

                                <div class="dashboard-application-item__documents-heading">

                                    <div>
                                        <span class="dashboard-application-item__subheading">
                                            Verification
                                        </span>

                                        <strong>
                                            Application Documents
                                        </strong>
                                    </div>

                                    <span>
                                        {{ $documentCount }}
                                        {{ \Illuminate\Support\Str::plural('Document', $documentCount) }}
                                    </span>

                                </div>

                                @if($application->documents->isNotEmpty())

                                    <div class="dashboard-document-list">

                                        @foreach($application->documents as $doc)

                                            <div class="dashboard-document">

                                                <div
                                                    class="dashboard-document__icon"
                                                    aria-hidden="true"
                                                >
                                                    ↗
                                                </div>

                                                <div class="dashboard-document__content">

                                                    <strong>
                                                        {{ $doc->documentType?->name ?? 'Application Document' }}
                                                    </strong>

                                                    @if($doc->rejection_reason)

                                                        <span class="dashboard-document__reason">
                                                            {{ $doc->rejection_reason }}
                                                        </span>

                                                    @endif

                                                </div>

                                                <div class="dashboard-document__meta">

                                                    <span class="{{ $doc->statusCssClass() }}">
                                                        {{ $doc->statusLabel() }}
                                                    </span>

                                                    <a
                                                        href="{{ route('application.documents.view', [$application, $doc]) }}"
                                                        class="admission-link"
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                    >
                                                        View
                                                    </a>

                                                </div>

                                            </div>

                                        @endforeach

                                    </div>

                                @else

                                    <div class="admission-inline-empty">
                                        No documents have been uploaded for this application.
                                    </div>

                                @endif

                            </div>

                            {{-- Payment Details --}}
                            <div class="dashboard-application-item__payment">

                                <div class="dashboard-application-item__payment-heading">
                                    <div>
                                        <span class="dashboard-application-item__subheading">
                                            Financial Record
                                        </span>

                                        <strong>
                                            Application Payment
                                        </strong>
                                    </div>
                                </div>

                                @if($payment)

                                    <div class="dashboard-application-item__payment-details">

                                        <div class="dashboard-detail">
                                            <span>Payment Status</span>

                                            <strong>
                                                {{ $application->paymentLabel() }}
                                            </strong>
                                        </div>

                                        <div class="dashboard-detail">
                                            <span>Amount</span>

                                            <strong>
                                                ₦{{ number_format($payment->amountInNaira(), 2) }}
                                            </strong>
                                        </div>

                                        <div class="dashboard-detail">
                                            <span>Payment Date</span>

                                            <strong>
                                                {{ $payment->paid_at?->format('d M Y, H:i') ?? 'Not confirmed' }}
                                            </strong>
                                        </div>

                                    </div>

                                @else

                                    <div class="dashboard-payment-state dashboard-payment-state--empty">

                                        <strong>
                                            Payment not recorded
                                        </strong>

                                        <span>
                                            Complete the required admission form,
                                            administration charges and logistics fee payment
                                            where applicable.
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
                                                Continue to Payment
                                            </x-portal-button>
                                        </form>

                                    </div>

                                @endif

                            </div>

                        </article>

                    @endforeach

                </div>

            </section>

        @endif

        {{-- ==========================================================
             OFFICIAL ADMISSION OFFERS
        =========================================================== --}}

        @if($issuedOffers->isNotEmpty())

            <section
                class="admission-panel dashboard-section dashboard-official-offers"
                aria-labelledby="official-offers-title"
            >

                <div class="admission-panel__header">

                    <div>
                        <span class="admission-panel__kicker">
                            Institutional Admission
                        </span>

                        <h2
                            id="official-offers-title"
                            class="admission-panel__title"
                        >
                            Your Official Admission Offers
                        </h2>

                        <p class="admission-panel__description">
                            Official admission offers issued for your admitted applications.
                        </p>
                    </div>

                    <span class="admission-offers-list__count">
                        {{ $issuedOffers->count() }}
                    </span>

                </div>

                <div class="dashboard-document-list">

                    @foreach($issuedOffers as $offer)

                        <div class="dashboard-document">

                            <div
                                class="dashboard-document__icon"
                                aria-hidden="true"
                            >
                                ✓
                            </div>

                            <div class="dashboard-document__content">

                                <strong>
                                    {{ $offer->application?->application_number ?? 'Admission Offer' }}
                                </strong>

                                <span>
                                    {{ $offer->application?->programme?->name ?? 'Programme information unavailable' }}
                                </span>

                                <span>
                                    Offer Reference: {{ $offer->letter_number }}
                                </span>

                            </div>

                            <div class="dashboard-document__meta">

                                <span class="admission-status admission-status--approved">
                                    Issued
                                </span>

                                @if($offer->application)

                                    <a
                                        href="{{ route('admission.offer', $offer->application) }}"
                                        class="admission-link"
                                    >
                                        View Official Offer
                                    </a>

                                @endif

                            </div>

                        </div>

                    @endforeach

                </div>

                <div class="dashboard-card__action">

                    <x-portal-button
                        variant="secondary"
                        href="{{ route('admission.offers.index') }}"
                    >
                        View All Admission Offers
                    </x-portal-button>

                </div>

            </section>

        @endif

        {{-- ==========================================================
             QUICK ACTIONS
        =========================================================== --}}

        <section
            class="admission-panel dashboard-section dashboard-quick-actions"
            aria-labelledby="dashboard-actions-title"
        >

            <div class="admission-panel__header">

                <div>
                    <span class="admission-panel__kicker">
                        Applicant Services
                    </span>

                    <h2
                        id="dashboard-actions-title"
                        class="admission-panel__title"
                    >
                        Quick Actions
                    </h2>

                    <p class="admission-panel__description">
                        Access your application services and admission documents.
                    </p>
                </div>

            </div>

            <div class="dashboard-quick-actions__grid">

                <a
                    href="{{ route('applications.index') }}"
                    class="dashboard-quick-action"
                >
                    <span class="dashboard-quick-action__number">01</span>

                    <strong>My Applications</strong>

                    <span>View and manage all your applications</span>

                    <span class="dashboard-quick-action__arrow" aria-hidden="true">
                        →
                    </span>
                </a>

                <a
                    href="{{ route('applications.documents') }}"
                    class="dashboard-quick-action"
                >
                    <span class="dashboard-quick-action__number">02</span>

                    <strong>Documents</strong>

                    <span>Manage your uploaded application documents</span>

                    <span class="dashboard-quick-action__arrow" aria-hidden="true">
                        →
                    </span>
                </a>

                <a
                    href="{{ route('applications.payment') }}"
                    class="dashboard-quick-action"
                >
                    <span class="dashboard-quick-action__number">03</span>

                    <strong>Payments</strong>

                    <span>Review your application payment information</span>

                    <span class="dashboard-quick-action__arrow" aria-hidden="true">
                        →
                    </span>
                </a>

                <a
                    href="{{ route('admission.offers.index') }}"
                    class="dashboard-quick-action"
                >
                    <span class="dashboard-quick-action__number">04</span>

                    <strong>Admission Offers</strong>

                    <span>Access your official admission documents</span>

                    <span class="dashboard-quick-action__arrow" aria-hidden="true">
                        →
                    </span>
                </a>

            </div>

        </section>

    </div>

</div>

@endsection