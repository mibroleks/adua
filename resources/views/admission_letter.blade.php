{{-- 
Component: Application Outcome Letter
File Path: resources/views/admission_letter.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Generates an official application outcome letter for applicants whose
application has been successful but who have not yet been formally admitted.

Important:
This document is NOT an admission offer.
It confirms that the applicant's application was successful and that
formal admission remains subject to the University's final admission
process, approval, and applicable conditions.

Status: ✅ Production Ready
Version: 6.0 (Successful Application Outcome)
--}}

@extends('layouts.app')

@section('title', 'Application Outcome')

@section('content')

<div class="admission-letter-page">

    {{-- ================================================================
         ACTIONS
         ================================================================ --}}
    <div class="admission-letter-actions">

        <a href="{{ route('dashboard') }}" class="admission-button admission-button--ghost">
            ← Back to Dashboard
        </a>

        <button
            type="button"
            class="admission-button admission-button--primary"
            onclick="window.print()"
        >
            Print Letter
        </button>

    </div>


    {{-- ================================================================
         OFFICIAL DOCUMENT
         ================================================================ --}}
    <main class="admission-letter">

        {{-- ============================================================
             INSTITUTION HEADER
             ============================================================ --}}
        <header class="admission-letter__header">

            <div class="admission-letter__identity">

                @if(setting('institution.logo'))

                    <img
                        src="{{ asset('storage/' . setting('institution.logo')) }}"
                        alt="{{ setting('institution.name') ?? 'Institution' }} Logo"
                        class="admission-letter__logo"
                    >

                @endif

                <div>

                    <h1>
                        {{ setting('institution.name') ?? 'Institution Name' }}
                    </h1>

                    @if(setting('institution.address'))
                        <p>
                            {{ setting('institution.address') }}
                        </p>
                    @endif

                </div>

            </div>


            {{-- Document classification --}}
            <div class="admission-letter__document-meta">

                <span>OFFICIAL DOCUMENT</span>

                <strong>
                    APPLICATION OUTCOME
                </strong>

            </div>

        </header>


        {{-- Institutional rule --}}
        <div class="admission-letter__rule"></div>


        {{-- ============================================================
             DATE
             ============================================================ --}}
        <div class="admission-letter__date">

            {{ 
                $application->decision?->decided_at?->format('d F Y')
                ?? $application->issued_at?->format('d F Y')
                ?? now()->format('d F Y')
            }}

        </div>


        {{-- ============================================================
             RECIPIENT
             ============================================================ --}}
        <section class="admission-letter__recipient">

            <strong>
                {{ $application->user?->name ?? 'Applicant' }}
            </strong>

            @if($application->address ?? null)

                <span>
                    {{ $application->address }}
                </span>

            @endif

            @if($application->user?->email)

                <span>
                    {{ $application->user->email }}
                </span>

            @endif

        </section>


        {{-- ============================================================
             DOCUMENT TITLE
             ============================================================ --}}
        <h2 class="admission-letter__title">
            Successful Application Outcome
        </h2>


        {{-- ============================================================
             SUCCESSFUL APPLICATION
             
             IMPORTANT:
             We deliberately DO NOT describe this as an admission offer.
             ============================================================ --}}
        @if(
            $application->decision &&
            strtoupper($application->decision->decision) === 'APPROVED'
        )

            <p>
                Dear <strong>{{ $application->user?->name ?? 'Applicant' }}</strong>,
            </p>


            <p>
                We are pleased to inform you that your application for
                consideration for admission to
                <strong>
                    {{ setting('institution.name') ?? 'the University' }}
                </strong>
                has been <strong>successful</strong>.
            </p>


            {{-- ========================================================
                 PROGRAMME
                 ======================================================== --}}
            <div class="admission-letter__programme">

                <span>
                    Programme Applied For
                </span>

                <strong>
                    {{ $application->programme?->name ?? 'Programme' }}
                </strong>

                @if($application->programme?->degree_type)

                    <small>
                        {{ $application->programme->degree_type }}
                    </small>

                @endif

            </div>


            {{-- ========================================================
                 APPLICATION NUMBER
                 ======================================================== --}}
            <p>
                Your application number is
                <strong>{{ $application->application_number }}</strong>.
                Please retain this number for all future correspondence
                concerning your application.
            </p>


            {{-- ========================================================
                 IMPORTANT STATUS CLARIFICATION
                 
                 This is the critical part.
                 It prevents the document from being interpreted as
                 an admission offer.
                 ======================================================== --}}
            <div class="admission-letter__remarks">

                <span>
                    Important Notice
                </span>

                <p>
                    This notification confirms the successful outcome of
                    your application assessment. It is <strong>not, by
                    itself, a formal offer of admission</strong>.
                    Formal admission remains subject to the University's
                    final admission process, approval, and any applicable
                    conditions or requirements.
                </p>

            </div>


            {{-- ========================================================
                 DECISION REMARKS
                 ======================================================== --}}
            @if($application->decision->remarks)

                <div class="admission-letter__remarks">

                    <span>
                        Admissions Office Remarks
                    </span>

                    <p>
                        {{ $application->decision->remarks }}
                    </p>

                </div>

            @endif


            {{-- ========================================================
                 NEXT STEPS
                 ======================================================== --}}
            <p>
                Please continue to monitor your applicant portal and
                official communication channels for further instructions
                regarding the completion of the admission process.
            </p>


            <p>
                You may be required to provide additional documentation,
                satisfy outstanding conditions, or complete other
                procedures before a formal admission decision is issued.
            </p>


            <p>
                We appreciate your interest in
                <strong>{{ setting('institution.name') ?? 'the University' }}</strong>
                and congratulate you on the successful outcome of your
                application.
            </p>


        {{-- ============================================================
             OTHER DECISIONS
             
             We intentionally do not automatically call every non-APPROVED
             decision "rejected". This prevents a pending/review state from
             being incorrectly presented as an unsuccessful application.
             ============================================================ --}}
        @else

            <p>
                Dear <strong>{{ $application->user?->name ?? 'Applicant' }}</strong>,
            </p>


            @if(
                $application->decision &&
                strtoupper($application->decision->decision) === 'REJECTED'
            )

                <p>
                    We regret to inform you that your application
                    <strong>{{ $application->application_number }}</strong>
                    was not successful for the current admission cycle.
                </p>


                @if($application->decision->remarks)

                    <div class="admission-letter__remarks">

                        <span>
                            Admissions Office Remarks
                        </span>

                        <p>
                            {{ $application->decision->remarks }}
                        </p>

                    </div>

                @endif


                <p>
                    We appreciate your interest in
                    {{ setting('institution.name') ?? 'the University' }}
                    and wish you every success in your future academic
                    pursuits.
                </p>


            @else

                {{-- ====================================================
                     PENDING / UNDECIDED SAFETY STATE
                     ==================================================== --}}

                <p>
                    Your application
                    <strong>{{ $application->application_number }}</strong>
                    is currently being processed by the Admissions Office.
                </p>


                <div class="admission-letter__remarks">

                    <span>
                        Current Status
                    </span>

                    <p>
                        No final admission outcome has been issued for this
                        application at this time. Please continue to monitor
                        your applicant portal and official communication
                        channels for updates.
                    </p>

                </div>


                <p>
                    We appreciate your interest in
                    {{ setting('institution.name') ?? 'the University' }}
                    and thank you for your patience throughout the
                    admissions process.
                </p>

            @endif

        @endif


        {{-- ============================================================
             SIGNATURE
             ============================================================ --}}
        <footer class="admission-letter__footer">

            <p>
                Yours sincerely,
            </p>


            <div class="admission-letter__signature-space"></div>


            <strong>
                {{ optional($application->decision?->officer)->name ?? 'Admissions Officer' }}
            </strong>


            <span>
                Admissions Office
            </span>

        </footer>


        {{-- ============================================================
             DOCUMENT FOOTER
             ============================================================ --}}
        <div class="admission-letter__footer-meta">

            <span>
                Application No.
                {{ $application->application_number }}
            </span>

            <span>
                {{ setting('institution.name') ?? 'Institution Name' }}
            </span>

        </div>

    </main>

</div>

@endsection

