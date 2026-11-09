{{--
|--------------------------------------------------------------------------
| Component: Application Outcome Letter
| File Path: resources/views/admission_letter.blade.php
| Company: Ygrace Tech
| Author: Ibrahim Olalekan
|
| Purpose:
| Displays application outcome notifications.
| Approved applicants receive the specified ADUA notification.
|
| Version: 9.0 (Simplified Approval Notification)
|--------------------------------------------------------------------------
--}}

@extends('layouts.app')

@section('title', 'Application Notification')

@section('content')

<div class="admission-letter-page">

    {{-- ACTIONS --}}
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

    <main class="admission-letter">

        {{-- INSTITUTION INFORMATION --}}
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

        </header>

        <div class="admission-letter__rule"></div>

        {{-- DATE --}}
        <div class="admission-letter__date">

            {{
                $application->decision?->decided_at?->format('d F Y')
                ?? $application->issued_at?->format('d F Y')
                ?? now()->format('d F Y')
            }}

        </div>

        {{-- APPLICANT INFORMATION --}}
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

        {{-- APPROVED APPLICATION --}}
        @if(
            $application->decision &&
            strtoupper($application->decision->decision) === 'APPROVED'
        )

            <div class="admission-letter__message">

                <p>
                    <strong>Congratulations,</strong>
                </p>

                <p>
                    I am pleased to inform you that your application has been
                    approved, please proceed to change of institution TO
                    <strong>ABDULFATTAH DUROJAIYE UNIVERSITY (ADUA)</strong>
                    as your first choice on Jamb portal, once that is done,
                    kindly contact the Admission office on
                </p>

                <p>
                    <strong>09136064487 (MTN)</strong><br>
                    <strong>08076801943 (Glo).</strong>
                </p>

                <p>
                    Thank you.
                </p>

            </div>

            {{-- APPLICATION INFORMATION --}}
            <div class="admission-letter__programme">

                <span>
                    Application Information
                </span>

                <p>
                    <strong>Application Number:</strong>
                    {{ $application->application_number }}
                </p>

                <p>
                    <strong>Applicant:</strong>
                    {{ $application->user?->name ?? 'Applicant' }}
                </p>

                <p>
                    <strong>Programme:</strong>
                    {{ $application->programme?->name ?? 'Not specified' }}
                </p>

                @if($application->programme?->degree_type)

                    <p>
                        <strong>Qualification:</strong>
                        {{ $application->programme->degree_type }}
                    </p>

                @endif

            </div>

        {{-- OTHER APPLICATION DECISIONS --}}
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

                        <span>Admissions Office Remarks</span>

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

                <p>
                    Your application
                    <strong>{{ $application->application_number }}</strong>
                    is currently being processed by the Admissions Office.
                </p>

                <div class="admission-letter__remarks">

                    <span>Current Status</span>

                    <p>
                        No final admission outcome has been issued for this
                        application at this time. Please continue to monitor
                        your applicant portal and official communication
                        channels for updates.
                    </p>

                </div>

            @endif

            {{-- SIGNATURE FOR NON-APPROVED OUTCOMES --}}
            <footer class="admission-letter__footer">

                <p>Yours sincerely,</p>

                <div class="admission-letter__signature-space"></div>

                <strong>
                    {{ optional($application->decision?->officer)->name ?? 'Admissions Officer' }}
                </strong>

                <span>Admissions Office</span>

            </footer>

        @endif

        {{-- APPLICATION REFERENCE --}}
        <div class="admission-letter__footer-meta">

            <span>
                Application No. {{ $application->application_number }}
            </span>

            <span>
                {{ setting('institution.name') ?? 'Institution Name' }}
            </span>

        </div>

    </main>

</div>

@endsection
