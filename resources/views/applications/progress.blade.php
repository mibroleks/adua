{{--  
Component: Application Progress
File Path: resources/views/applications/progress.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Displays the applicant's admission journey.

Status: Production
Version: 2.0
--}}

@extends('layouts.portal')

@section('title', 'Application Progress')

@section('content')

<div class="admission-page admission-page--progress">

    <main class="admission-shell admission-shell--wide">

        <header class="admission-page-header">

            <div class="admission-eyebrow">
                <span class="admission-eyebrow__dot" aria-hidden="true"></span>
                Admission Journey
            </div>

            <h1 class="admission-page-title">
                Application Progress
            </h1>

            <p class="admission-page-description">
                Follow your application as it moves through each stage of
                the admissions process.
            </p>

        </header>


        @if(!$application)

            <section class="admission-empty-state">

                <div class="progress-empty-state__mark">
                    01
                </div>

                <div>
                    <span class="progress-empty-state__eyebrow">
                        APPLICATION REQUIRED
                    </span>

                    <h2>No application yet</h2>

                    <p>
                        You have not started an application. Begin by
                        selecting your programme.
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

            @php
                $applicationComplete = filled($application->submitted_at);
                $paymentComplete = $application->payment?->status === 'SUCCESS';
                $documentsComplete = $application->documents->count() > 0;
                $decisionComplete = (bool) $application->decision;
            @endphp

            <section class="progress-overview">

                <div>

                    <span class="progress-overview__eyebrow">
                        APPLICATION
                    </span>

                    <strong>
                        {{ $application->application_number }}
                    </strong>

                    <span class="progress-overview__programme">
                        {{ $application->programme?->name ?? 'Programme unavailable' }}
                    </span>

                </div>

                <div class="progress-overview__status">

                    <span>
                        CURRENT STATUS
                    </span>

                    <span class="admission-status admission-status--{{ strtolower($application->status) }}">
                        {{ str_replace('_', ' ', $application->status) }}
                    </span>

                </div>

            </section>


            <section class="progress-journey">

                <div class="progress-journey__line" aria-hidden="true"></div>


                {{-- ========================================================
                     APPLICATION
                ========================================================= --}}
                <article class="progress-stage {{ $applicationComplete ? 'is-complete' : 'is-current' }}">

                    <div class="progress-stage__marker">
                        <span>
                            {{ $applicationComplete ? '✓' : '1' }}
                        </span>
                    </div>

                    <div class="progress-stage__content">

                        <div class="progress-stage__heading">

                            <div>
                                <span class="progress-stage__eyebrow">
                                    STAGE 01
                                </span>

                                <h2>
                                    Application submitted
                                </h2>
                            </div>

                            <span class="progress-stage__state">
                                {{ $applicationComplete ? 'Complete' : 'Pending' }}
                            </span>

                        </div>

                        <p>
                            {{ $application->submitted_at?->format('d M Y, H:i') ?? 'Your application has not been submitted yet.' }}
                        </p>

                    </div>

                </article>


                {{-- ========================================================
                     PAYMENT
                ========================================================= --}}
                <article class="progress-stage {{ $paymentComplete ? 'is-complete' : 'is-current' }}">

                    <div class="progress-stage__marker">
                        <span>
                            {{ $paymentComplete ? '✓' : '2' }}
                        </span>
                    </div>

                    <div class="progress-stage__content">

                        <div class="progress-stage__heading">

                            <div>
                                <span class="progress-stage__eyebrow">
                                    STAGE 02
                                </span>

                                <h2>
                                    Application payment
                                </h2>
                            </div>

                            @if($application->payment)

                                <span class="admission-status admission-status--{{ strtolower($application->payment->status) }}">
                                    {{ $application->payment->status }}
                                </span>

                            @else

                                <span class="progress-stage__state">
                                    Pending
                                </span>

                            @endif

                        </div>

                        <p>
                            @if($application->payment)
                                {{ $application->payment->paid_at?->format('d M Y, H:i') ?? 'Payment recorded.' }}
                            @else
                                No payment has been recorded yet.
                            @endif
                        </p>

                    </div>

                </article>


                {{-- ========================================================
                     DOCUMENTS
                ========================================================= --}}
                <article class="progress-stage {{ $documentsComplete ? 'is-complete' : 'is-current' }}">

                    <div class="progress-stage__marker">
                        <span>
                            {{ $documentsComplete ? '✓' : '3' }}
                        </span>
                    </div>

                    <div class="progress-stage__content">

                        <div class="progress-stage__heading">

                            <div>
                                <span class="progress-stage__eyebrow">
                                    STAGE 03
                                </span>

                                <h2>
                                    Document verification
                                </h2>
                            </div>

                            <span class="progress-stage__state">
                                {{ $documentsComplete ? 'In progress' : 'Pending' }}
                            </span>

                        </div>

                        <p>
                            @if($documentsComplete)
                                {{ $application->documents->count() }}
                                document(s) submitted for verification.
                            @else
                                No supporting documents have been uploaded yet.
                            @endif
                        </p>

                    </div>

                </article>


                {{-- ========================================================
                     DECISION
                ========================================================= --}}
                <article class="progress-stage {{ $decisionComplete ? 'is-complete' : 'is-current' }}">

                    <div class="progress-stage__marker">
                        <span>
                            {{ $decisionComplete ? '✓' : '4' }}
                        </span>
                    </div>

                    <div class="progress-stage__content">

                        <div class="progress-stage__heading">

                            <div>
                                <span class="progress-stage__eyebrow">
                                    STAGE 04
                                </span>

                                <h2>
                                    Admission decision
                                </h2>
                            </div>

                            @if($application->decision)

                                @php
                                    $decisionValue =
                                        $application->decision->decision
                                        ?? $application->decision->status
                                        ?? 'PENDING';
                                @endphp

                                <span class="admission-status admission-status--{{ strtolower($decisionValue) }}">
                                    {{ str_replace('_', ' ', $decisionValue) }}
                                </span>

                            @else

                                <span class="progress-stage__state">
                                    Pending
                                </span>

                            @endif

                        </div>

                        <p>
                            @if($application->decision)
                                Decided
                                {{ $application->decision->decided_at?->format('d M Y, H:i') ?? '—' }}
                                by
                                {{ optional($application->decision->officer)->name ?? 'Admissions Office' }}.
                            @else
                                Your admission decision is pending.
                            @endif
                        </p>

                    </div>

                </article>

            </section>

        @endif

    </main>

</div>

@endsection