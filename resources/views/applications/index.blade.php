{{--
|--------------------------------------------------------------------------
| MY APPLICATIONS — APPLICATION LIST
|--------------------------------------------------------------------------
| File:
| resources/views/applications/index.blade.php
|
| Company: Ygrace Tech
| Author: Ibrahim Olalekan
|
| Purpose:
| Displays all applications belonging to the authenticated applicant.
| Supports multiple applications across programmes and admission cycles.
|
| Features:
| - Application status and submission date.
| - Processing decision and admission outcome.
| - Direct access to each application dossier.
| - Access to application outcome letters.
| - Direct access to issued official admission offers.
| - Responsive application listing.
|
| Important:
| - Uses application_status, not the nonexistent status field.
| - Does not modify application or admission records.
| - Official offers are supplied by ApplicationListController.
|--------------------------------------------------------------------------
| Version: 2.0
|--------------------------------------------------------------------------
--}}

@extends('layouts.portal')

@section('title', 'My Applications')

@section('content')

@php
    $applicationCount = $applications->count();
    $issuedOffers = $issuedOffers ?? collect();
@endphp

<div class="admission-page admission-page--list">

    <main class="admission-shell admission-shell--wide">

        {{-- Page heading --}}
        <header class="admission-page-header admission-list-header">

            <div class="admission-list-header__content">

                <div class="admission-eyebrow">
                    <span class="admission-eyebrow__dot" aria-hidden="true"></span>
                    Applications Overview
                </div>

                <h1 class="admission-page-title">
                    My Applications
                </h1>

                <p class="admission-page-description">
                    View and track all your applications across programmes
                    and admission cycles.
                </p>

            </div>

            <div class="admission-list-header__action">
                <x-portal-button
                    variant="primary"
                    href="{{ route('application.create') }}"
                >
                    Start New Application
                </x-portal-button>
            </div>

        </header>

        {{-- Application summary --}}
        <section
            class="application-list-summary"
            aria-label="Application summary"
        >

            <div class="application-list-summary__main">

                <span class="application-list-summary__label">
                    Total Applications
                </span>

                <strong class="application-list-summary__value">
                    {{ $applicationCount }}
                </strong>

                <p>
                    {{ $applicationCount === 1
                        ? 'Application associated with your account.'
                        : 'Applications associated with your account.' }}
                </p>

            </div>

            <div
                class="application-list-summary__icon"
                aria-hidden="true"
            >
                <span>&#9776;</span>
            </div>

        </section>

        @if ($applications->isEmpty())

            {{-- Empty state --}}
            <section class="admission-empty-state application-list-empty">

                <div
                    class="admission-empty-state__icon"
                    aria-hidden="true"
                >
                    +
                </div>

                <span class="application-list-empty__eyebrow">
                    GET STARTED
                </span>

                <h2>No applications found</h2>

                <p>
                    You have not started an application yet. Begin by
                    selecting your preferred programme.
                </p>

                <x-portal-button
                    variant="primary"
                    href="{{ route('application.create') }}"
                >
                    Start Application
                </x-portal-button>

            </section>

        @else

            {{-- Applications table --}}
            <section class="application-list-panel">

                <div class="application-list-panel__heading">

                    <div>
                        <span class="application-list-panel__eyebrow">
                            YOUR RECORDS
                        </span>

                        <h2>Application history</h2>

                        <p>
                            Review the progress and admission outcome of
                            each application.
                        </p>
                    </div>

                    <span class="application-list-panel__count">
                        {{ $applicationCount }}
                        {{ \Illuminate\Support\Str::plural('application', $applicationCount) }}
                    </span>

                </div>

                <div class="application-list-table-wrap">

                    <table class="admission-table application-list-table">

                        <thead>
                            <tr>
                                <th>Application No.</th>
                                <th>Programme</th>
                                <th>Application Status</th>
                                <th>Processing Decision</th>
                                <th>Admission Outcome</th>
                                <th>Submitted</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach ($applications as $application)

                                @php
                                    $decision = $application->decision;

                                    $applicationStatus = $application->application_status
                                        ?: 'PENDING';

                                    $applicationStatusLabel = \Illuminate\Support\Str::headline(
                                        strtolower($applicationStatus)
                                    );

                                    $processingApproved = $decision
                                        && method_exists($decision, 'isApplicationApproved')
                                        && $decision->isApplicationApproved();

                                    $decisionValue = $decision
                                        ? ($decision->decision ?? $decision->status ?? null)
                                        : null;

                                    $decisionLabel = $processingApproved
                                        ? 'Approved'
                                        : \Illuminate\Support\Str::headline(
                                            strtolower($decisionValue ?: 'Pending')
                                        );

                                    $admissionOutcome = $decision?->admission_status;

                                    $issuedOffer = $issuedOffers->get($application->id);
                                @endphp

                                <tr>

                                    {{-- Application number --}}
                                    <td data-label="Application No.">

                                        <span class="application-list-number">
                                            {{ $application->application_number }}
                                        </span>

                                    </td>

                                    {{-- Programme --}}
                                    <td data-label="Programme">

                                        <div class="application-list-programme">

                                            <strong>
                                                {{ $application->programme?->name ?? 'Programme unavailable' }}
                                            </strong>

                                            @if ($application->programme?->code)
                                                <span>
                                                    {{ $application->programme->code }}
                                                </span>
                                            @endif

                                        </div>

                                    </td>

                                    {{-- Application status --}}
                                    <td data-label="Application Status">

                                        <span class="application-list-status application-list-status--{{ \Illuminate\Support\Str::slug($applicationStatus) }}">
                                            <span aria-hidden="true"></span>
                                            {{ $applicationStatusLabel }}
                                        </span>

                                    </td>

                                    {{-- Processing decision --}}
                                    <td data-label="Processing Decision">

                                        <span class="application-list-decision {{ $processingApproved ? 'application-list-decision--approved' : '' }}">
                                            {{ $decisionLabel }}
                                        </span>

                                    </td>

                                    {{-- Admission outcome --}}
                                    <td data-label="Admission Outcome">

                                        @if ($admissionOutcome)

                                            <span class="application-list-outcome application-list-outcome--{{ \Illuminate\Support\Str::slug($admissionOutcome) }}">
                                                {{ \Illuminate\Support\Str::headline(strtolower($admissionOutcome)) }}
                                            </span>

                                        @else

                                            <span class="application-list-muted">
                                                Not decided
                                            </span>

                                        @endif

                                    </td>

                                    {{-- Submission date --}}
                                    <td data-label="Submitted">

                                        <span class="application-list-date">
                                            {{ $application->submitted_at?->format('d M Y') ?? 'Not submitted' }}
                                        </span>

                                    </td>

                                    {{-- Actions --}}
                                    <td data-label="Actions">

                                        <div class="application-list-actions">

                                            <a
                                                href="{{ route('applications.show', $application) }}"
                                                class="application-list-action application-list-action--primary"
                                            >
                                                View Dossier
                                            </a>

                                            @if ($processingApproved)

                                                <a
                                                    href="{{ route('admission.letter', $application) }}"
                                                    class="application-list-action application-list-action--secondary"
                                                >
                                                    Application Outcome
                                                </a>

                                            @endif

                                            @if ($issuedOffer)

                                                <a
                                                    href="{{ route('admission.offer', $application) }}"
                                                    class="application-list-action application-list-action--offer"
                                                >
                                                    Official Offer
                                                </a>

                                            @endif

                                        </div>

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

                <footer class="application-list-panel__footer">

                    <span>
                        Showing {{ $applicationCount }}
                        {{ \Illuminate\Support\Str::plural('application', $applicationCount) }}
                    </span>

                    <span>
                        Your applications are linked to your applicant account.
                    </span>

                </footer>

            </section>

        @endif

    </main>

</div>

@endsection
