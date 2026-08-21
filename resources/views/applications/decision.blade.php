{{--  
Component: Application Decision
File Path: resources/views/applications/decision.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Displays the applicant's admission decision in a premium institutional
workspace presentation.

Status: Production
Version: 2.0
--}}

@extends('layouts.portal')

@section('title', 'Admission Decision')

@section('content')

<div class="admission-page admission-page--decision">

    <main class="admission-shell admission-shell--wide">

        {{-- ================================================================
             PAGE INTRO
        ================================================================= --}}
        <header class="admission-page-header decision-page-header">

            <div class="admission-eyebrow">
                <span class="admission-eyebrow__dot" aria-hidden="true"></span>
                Admission Decision
            </div>

            <h1 class="admission-page-title">
                Your admission outcome
            </h1>

            <p class="admission-page-description">
                Review the official outcome of your application as determined
                by the admissions office.
            </p>

        </header>


        {{-- ================================================================
             NO APPLICATION
        ================================================================= --}}
        @if(!$application)

            <section class="admission-empty-state decision-empty-state">

                <div class="admission-empty-state__icon" aria-hidden="true">
                    <span>01</span>
                </div>

                <div class="decision-empty-state__content">
                    <span class="decision-empty-state__label">
                        APPLICATION REQUIRED
                    </span>

                    <h2>No application found</h2>

                    <p>
                        You have not started an application yet. Begin by
                        selecting your programme and completing your application.
                    </p>

                    <x-portal-button
                        variant="primary"
                        href="{{ route('application.create') }}"
                    >
                        Start Application
                    </x-portal-button>
                </div>

            </section>

        @else

            {{-- ============================================================
                 APPLICATION CONTEXT
            ============================================================= --}}
            <section class="decision-context">

                <div class="decision-context__identity">

                    <span class="decision-context__label">
                        APPLICATION
                    </span>

                    <strong class="decision-context__number">
                        {{ $application->application_number }}
                    </strong>

                </div>

                <div class="decision-context__programme">

                    <span class="decision-context__label">
                        PROGRAMME
                    </span>

                    <strong>
                        {{ $application->programme?->name ?? 'Programme unavailable' }}
                    </strong>

                </div>

                <div class="decision-context__status">

                    <span class="decision-context__label">
                        APPLICATION STATUS
                    </span>

                    <span class="admission-status admission-status--{{ strtolower($application->status) }}">
                        {{ str_replace('_', ' ', $application->status) }}
                    </span>

                </div>

            </section>


            {{-- ============================================================
                 DECISION
            ============================================================= --}}
            @if($application->decision)

                @php
                    $decisionValue =
                        $application->decision->decision
                        ?? $application->decision->status
                        ?? 'PENDING';

                    $decisionClass = strtolower(
                        str_replace([' ', '_'], '-', $decisionValue)
                    );
                @endphp

                <section class="decision-card decision-card--{{ $decisionClass }}">

                    <div class="decision-card__top">

                        <div class="decision-card__heading">

                            <span class="decision-card__eyebrow">
                                OFFICIAL ADMISSION OUTCOME
                            </span>

                            <h2>
                                Admission Decision
                            </h2>

                        </div>

                        <span class="admission-status admission-status--{{ strtolower($decisionValue) }}">
                            {{ str_replace('_', ' ', $decisionValue) }}
                        </span>

                    </div>


                    <div class="decision-card__body">

                        <div class="decision-card__signal" aria-hidden="true">

                            @if(strtoupper($decisionValue) === 'APPROVED')
                                <span class="decision-card__signal-mark">✓</span>
                            @elseif(strtoupper($decisionValue) === 'REJECTED')
                                <span class="decision-card__signal-mark">×</span>
                            @else
                                <span class="decision-card__signal-mark">•</span>
                            @endif

                        </div>


                        <div class="decision-card__message">

                            @if(strtoupper($decisionValue) === 'APPROVED')

                                <span class="decision-card__message-label">
                                    Admission confirmed
                                </span>

                                <h3>
                                    Congratulations on your admission.
                                </h3>

                                <p>
                                    Your application has been approved by the
                                    admissions office. Your admission letter
                                    will be available from your applicant
                                    workspace.
                                </p>

                            @elseif(strtoupper($decisionValue) === 'REJECTED')

                                <span class="decision-card__message-label">
                                    Application outcome
                                </span>

                                <h3>
                                    Your application was not successful.
                                </h3>

                                <p>
                                    Please review the admissions office remarks
                                    below for further information.
                                </p>

                            @else

                                <span class="decision-card__message-label">
                                    Under review
                                </span>

                                <h3>
                                    Your application is still being reviewed.
                                </h3>

                                <p>
                                    No final admission decision has been
                                    recorded yet. Please check your portal
                                    periodically for updates.
                                </p>

                            @endif

                        </div>

                    </div>


                    @if($application->decision->remarks)

                        <div class="decision-card__remarks">

                            <span class="decision-card__remarks-label">
                                Admissions Office Remarks
                            </span>

                            <p>
                                {{ $application->decision->remarks }}
                            </p>

                        </div>

                    @endif


                    <div class="decision-card__footer">

                        <div>
                            <span class="decision-card__footer-label">
                                Decision date
                            </span>

                            <strong>
                                {{ $application->decision->decided_at?->format('d M Y, H:i') ?? '—' }}
                            </strong>
                        </div>

                        <div>
                            <span class="decision-card__footer-label">
                                Admissions officer
                            </span>

                            <strong>
                                {{ optional($application->decision->officer)->name ?? 'Admissions Office' }}
                            </strong>
                        </div>

                    </div>

                </section>


                {{-- ========================================================
                     ADMISSION LETTER
                ========================================================= --}}
                @if($application->status === 'APPROVED')

                    <section class="decision-letter-callout">

                        <div class="decision-letter-callout__content">

                            <span class="decision-letter-callout__eyebrow">
                                NEXT STEP
                            </span>

                            <h2>
                                Your admission letter is ready
                            </h2>

                            <p>
                                View, save, or print your official admission
                                letter.
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

            @else

                {{-- ========================================================
                     PENDING
                ========================================================= --}}
                <section class="decision-pending">

                    <div class="decision-pending__visual" aria-hidden="true">
                        <span></span>
                    </div>

                    <div class="decision-pending__content">

                        <span class="decision-pending__eyebrow">
                            UNDER REVIEW
                        </span>

                        <h2>
                            Your decision is pending
                        </h2>

                        <p>
                            The admissions office is currently reviewing your
                            application. No final decision has been recorded.
                        </p>

                    </div>

                </section>

            @endif

        @endif

    </main>

</div>

@endsection