{{--
Component: Admission Letter
File Path: resources/views/admission_letter.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Generates a printable official admission letter for approved applicants.

Status: Production Ready
Version: 4.0
--}}

@extends('layouts.app')

@section('title', 'Admission Letter')

@section('content')

<div class="admission-letter-page">

    <div class="admission-letter-actions">

        <a
            href="{{ route('dashboard') }}"
            class="admission-button admission-button--ghost"
        >
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

        {{-- Institution header --}}
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

                    <p>
                        {{ setting('institution.address') ?? 'Institution Address' }}
                    </p>

                </div>

            </div>

            <div class="admission-letter__document-meta">

                <span>
                    OFFICIAL DOCUMENT
                </span>

                <strong>
                    ADMISSION LETTER
                </strong>

            </div>

        </header>

        <div class="admission-letter__rule"></div>

        {{-- Date --}}
        <div class="admission-letter__date">
            {{ now()->format('d F Y') }}
        </div>

        {{-- Recipient --}}
        <section class="admission-letter__recipient">

            <strong>
                {{ $application->user->name }}
            </strong>

            @if($application->address ?? null)
                <span>
                    {{ $application->address }}
                </span>
            @endif

        </section>

        {{-- Title --}}
        <h2 class="admission-letter__title">
            Admission Decision
        </h2>

        {{-- Letter body --}}
        @if($application->decision && $application->decision->status === 'APPROVED')

            <p>
                Dear
                <strong>{{ $application->user->name }}</strong>,
            </p>

            <p>
                We are pleased to inform you that your application for admission
                has been <strong>approved</strong>.
            </p>

            <div class="admission-letter__programme">

                <span>
                    Programme of Admission
                </span>

                <strong>
                    {{ $application->programme->name }}
                </strong>

                @if($application->programme->degree_type)
                    <small>
                        {{ $application->programme->degree_type }}
                    </small>
                @endif

            </div>

            <p>
                Your application number is
                <strong>{{ $application->application_number }}</strong>.
                Please retain this number for future correspondence with the
                admissions office.
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
                Kindly proceed with the next steps as communicated by the
                admissions office and ensure that all registration requirements
                are completed within the stipulated period.
            </p>

            <p>
                We congratulate you and look forward to welcoming you to the
                institution.
            </p>

        @else

            <p>
                Dear
                <strong>{{ $application->user->name }}</strong>,
            </p>

            <p>
                We regret to inform you that your application
                <strong>{{ $application->application_number }}</strong>
                was not successful for this admission cycle.
            </p>

            @if($application->decision && $application->decision->remarks)

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
                You may consider applying again in a future admission cycle
                where eligible.
            </p>

        @endif

        {{-- Signature --}}
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

        {{-- Document footer --}}
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