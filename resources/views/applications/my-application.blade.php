{{--  
Component: My Application
File Path: resources/views/applications/my-application.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Applicant's complete application dossier.

Status: 🚦 Integration / Hardening
Version: 2.1 (secure document view route)
--}}

@extends('layouts.portal')

@section('title', 'My Application')

@section('content')

<div class="admission-page admission-page--dossier">

    <main class="admission-shell admission-shell--wide">

        <header class="admission-page-header">
            <div class="admission-eyebrow">
                <span class="admission-eyebrow__dot" aria-hidden="true"></span>
                Applicant Dossier
            </div>
            <h1 class="admission-page-title">My Application</h1>
            <p class="admission-page-description">
                Your complete application record, including programme,
                payment, documents, and admission outcome.
            </p>
        </header>

        @if(!$application)
            <section class="admission-empty-state dossier-empty-state">
                <div class="dossier-empty-state__mark">APP</div>
                <div>
                    <span class="dossier-empty-state__eyebrow">APPLICATION REQUIRED</span>
                    <h2>No application found</h2>
                    <p>You have not started an application yet. Begin by selecting your programme.</p>
                    <x-portal-button variant="primary" href="{{ route('application.create') }}">
                        Start Application
                    </x-portal-button>
                </div>
            </section>
        @else

            {{-- Application Hero --}}
            <section class="dossier-hero">
                <div class="dossier-hero__main">
                    <span class="dossier-hero__eyebrow">APPLICATION NUMBER</span>
                    <h2>{{ $application->application_number }}</h2>
                    <p>{{ $application->programme?->name ?? 'Programme unavailable' }}</p>
                </div>
                <div class="dossier-hero__status">
                    <span class="dossier-hero__status-label">CURRENT STATUS</span>
                    <span class="admission-status admission-status--{{ strtolower($application->status) }}">
                        {{ $application->statusLabel() }}
                    </span>
                </div>
            </section>

            {{-- Overview --}}
            <section class="dossier-section">
                <div class="dossier-section__heading">
                    <div>
                        <span class="dossier-section__eyebrow">APPLICATION</span>
                        <h2>Application overview</h2>
                    </div>
                </div>
                <div class="dossier-overview">
                    <div class="dossier-overview__item">
                        <span>Programme</span>
                        <strong>{{ $application->programme?->name ?? '—' }}</strong>
                    </div>
                    <div class="dossier-overview__item">
                        <span>Degree Type</span>
                        <strong>{{ $application->programme?->degree_type ?? '—' }}</strong>
                    </div>
                    <div class="dossier-overview__item">
                        <span>Submitted</span>
                        <strong>{{ $application->submitted_at?->format('d M Y, H:i') ?? 'Not submitted' }}</strong>
                    </div>
                    <div class="dossier-overview__item">
                        <span>Application Status</span>
                        <strong>{{ $application->statusLabel() }}</strong>
                    </div>
                </div>
            </section>

            {{-- Payment --}}
            <section class="dossier-section">
                <div class="dossier-section__heading">
                    <div>
                        <span class="dossier-section__eyebrow">FINANCIAL</span>
                        <h2>Application payment</h2>
                    </div>
                    <a href="{{ route('applications.payment') }}" class="dossier-section__link">
                        View payment
                    </a>
                </div>
                @if($application->payment)
                    <div class="dossier-payment">
                        <div class="dossier-payment__amount">
                            <span>Application fee</span>
                            <strong>₦{{ number_format($application->payment->amountInNaira(), 2) }}</strong>
                        </div>
                        <div class="dossier-payment__status">
                            <span class="admission-status admission-status--{{ strtolower($application->payment->status) }}">
                                {{ $application->paymentLabel() }}
                            </span>
                            <small>{{ $application->payment->paid_at?->format('d M Y, H:i') ?? 'Payment date unavailable' }}</small>
                        </div>
                    </div>
                @else
                    <div class="dossier-inline-empty">
                        <strong>Payment required</strong>
                        <span>No application payment has been recorded yet.</span>
                    </div>
                @endif
            </section>

            {{-- Documents --}}
            <section class="dossier-section">
                <div class="dossier-section__heading">
                    <div>
                        <span class="dossier-section__eyebrow">VERIFICATION</span>
                        <h2>Supporting documents</h2>
                    </div>
                    <a href="{{ route('applications.documents') }}" class="dossier-section__link">
                        Manage documents
                    </a>
                </div>
                @if($application->documents->count())
                    <div class="dossier-documents">
                        @foreach($application->documents as $doc)
                            <div class="dossier-document">
                                <div class="dossier-document__identity">
                                    <span class="dossier-document__icon">DOC</span>
                                    <strong>{{ $doc->documentType?->name ?? 'Application Document' }}</strong>
                                </div>
                                <span class="admission-status admission-status--{{ strtolower($doc->status) }}">
                                    {{ $doc->status }}
                                </span>
                                <a href="{{ route('application.documents.view', [$application, $doc]) }}"
                                   target="_blank"
                                   rel="noopener"
                                   class="dossier-document__view">
                                    View
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="dossier-inline-empty">
                        <strong>No documents uploaded</strong>
                        <span>Supporting documents will appear here once uploaded.</span>
                    </div>
                @endif
            </section>

            {{-- Decision --}}
            @if($application->decision)
                @php
                    $decisionValue = $application->decision->decision
                        ?? $application->decision->status
                        ?? 'PENDING';
                @endphp
                <section class="dossier-section">
                    <div class="dossier-section__heading">
                        <div>
                            <span class="dossier-section__eyebrow">ADMISSIONS</span>
                            <h2>Admission decision</h2>
                        </div>
                        <span class="admission-status admission-status--{{ strtolower($decisionValue) }}">
                            {{ str_replace('_', ' ', $decisionValue) }}
                        </span>
                    </div>
                    <div class="dossier-decision">
                        @if($application->decision->remarks)
                            <p class="dossier-decision__remarks">{{ $application->decision->remarks }}</p>
                        @endif
                        <div class="dossier-decision__meta">
                            <span>Decided {{ $application->decision->decided_at?->format('d M Y, H:i') ?? '—' }}</span>
                            <span>By {{ optional($application->decision->officer)->name ?? 'Admissions Office' }}</span>
                        </div>
                    </div>
                </section>
            @endif

            {{-- Letter --}}
            @if($application->status === 'APPROVED' && $application->decision)
                <section class="dossier-letter">
                    <div class="dossier-letter__mark">✓</div>
                    <div class="dossier-letter__content">
                        <span>ADMISSION CONFIRMED</span>
                        <h2>Your admission letter is ready</h2>
                        <p>View and print your official admission letter.</p>
                    </div>
                    <x-portal-button variant="primary" href="{{ route('admission.letter', $application) }}">
                        View Admission Letter
                    </x-portal-button>
                </section>
            @endif

        @endif
    </main>
</div>

@endsection
