{{--
Component: Application Documents
File Path: resources/views/applications/documents.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Displays documents belonging to all applications owned by the
authenticated applicant, grouped by application.

Features:
- Multiple application document lists.
- Per-application document statistics.
- Secure document preview.
- Rejected document replacement.
- Empty states for applicants and individual applications.

Status: Integration / Hardening
Version: 3.0 (Multi-Application Document Listing)
--}}

@extends('layouts.portal')

@section('title', 'My Documents')

@section('content')

<div class="admission-page admission-page--documents">

    <main class="admission-shell admission-shell--wide">

        {{-- PAGE HEADER --}}
        <header class="admission-page-header">

            <div class="admission-eyebrow">
                <span class="admission-eyebrow__dot" aria-hidden="true"></span>
                Application Documents
            </div>

            <h1 class="admission-page-title">
                Your documents
            </h1>

            <p class="admission-page-description">
                Review documents submitted across your applications,
                open uploaded files, and monitor their verification status.
            </p>

        </header>

        {{-- NO APPLICATIONS --}}
        @if($applications->isEmpty())

            <section class="admission-empty-state documents-empty-state">

                <div class="documents-empty-state__mark">
                    DOC
                </div>

                <div>

                    <span class="documents-empty-state__eyebrow">
                        APPLICATION REQUIRED
                    </span>

                    <h2>No application found</h2>

                    <p>
                        You have not started an application yet.
                        Begin by selecting your programme.
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

            {{-- OVERALL DOCUMENT SUMMARY --}}
            @php
                $totalDocuments = $applications->sum(
                    fn ($application) => $application->documents->count()
                );

                $totalVerified = $applications->sum(
                    fn ($application) => $application->documents
                        ->where('status', 'VERIFIED')
                        ->count()
                );

                $totalRejected = $applications->sum(
                    fn ($application) => $application->documents
                        ->where('status', 'REJECTED')
                        ->count()
                );
            @endphp

            <section class="documents-summary">

                <div class="documents-summary__intro">

                    <span class="documents-summary__eyebrow">
                        DOCUMENT OVERVIEW
                    </span>

                    <h2>All application documents</h2>

                    <p>
                        Documents across {{ $applications->count() }}
                        {{ \Illuminate\Support\Str::plural('application', $applications->count()) }}.
                    </p>

                </div>

                <div class="documents-summary__stats">

                    <div class="documents-summary__stat">
                        <strong>{{ $totalDocuments }}</strong>
                        <span>Uploaded</span>
                    </div>

                    <div class="documents-summary__stat">
                        <strong>{{ $totalVerified }}</strong>
                        <span>Verified</span>
                    </div>

                    <div class="documents-summary__stat">
                        <strong>{{ $totalRejected }}</strong>
                        <span>Needs attention</span>
                    </div>

                </div>

            </section>

            {{-- APPLICATION DOCUMENT GROUPS --}}
            @foreach($applications as $application)

                @php
                    $documents = $application->documents;

                    $documentCount = $documents->count();

                    $verifiedCount = $documents
                        ->where('status', 'VERIFIED')
                        ->count();

                    $rejectedCount = $documents
                        ->where('status', 'REJECTED')
                        ->count();

                    $pendingCount = $documents
                        ->where('status', 'PENDING')
                        ->count();
                @endphp

                <section class="documents-list-section">

                    {{-- APPLICATION HEADING --}}
                    <div class="documents-list-section__heading">

                        <div>

                            <span class="documents-list-section__eyebrow">
                                APPLICATION DOCUMENTS
                            </span>

                            <h2>
                                {{ $application->application_number }}
                            </h2>

                            <p>
                                {{ $application->programme?->name ?? 'Programme not specified' }}
                            </p>

                        </div>

                        <div class="documents-summary__stats">

                            <div class="documents-summary__stat">
                                <strong>{{ $documentCount }}</strong>
                                <span>Uploaded</span>
                            </div>

                            <div class="documents-summary__stat">
                                <strong>{{ $verifiedCount }}</strong>
                                <span>Verified</span>
                            </div>

                            <div class="documents-summary__stat">
                                <strong>{{ $pendingCount }}</strong>
                                <span>Pending</span>
                            </div>

                            <div class="documents-summary__stat">
                                <strong>{{ $rejectedCount }}</strong>
                                <span>Rejected</span>
                            </div>

                        </div>

                    </div>

                    {{-- DOCUMENT LIST --}}
                    @if($documentCount)

                        <div class="documents-list">

                            @foreach($documents as $doc)

                                @php
                                    $status = strtoupper($doc->status ?? 'PENDING');
                                @endphp

                                <article class="document-card document-card--{{ strtolower($status) }}">

                                    <div
                                        class="document-card__icon"
                                        aria-hidden="true"
                                    >
                                        <span>DOC</span>
                                    </div>

                                    <div class="document-card__body">

                                        <div class="document-card__heading">

                                            <h3>
                                                {{ $doc->documentType?->name ?? 'Application Document' }}
                                            </h3>

                                            <span class="admission-status admission-status--{{ strtolower($status) }}">
                                                {{ $status }}
                                            </span>

                                        </div>

                                        @if($doc->original_name)

                                            <p>
                                                {{ $doc->original_name }}
                                            </p>

                                        @endif

                                        @if($status === 'REJECTED' && $doc->rejection_reason)

                                            <div class="document-card__issue">

                                                <span class="document-card__issue-label">
                                                    Correction required
                                                </span>

                                                <p>
                                                    {{ $doc->rejection_reason }}
                                                </p>

                                            </div>

                                        @endif

                                    </div>

                                    {{-- DOCUMENT ACTIONS --}}
                                    <div class="document-card__actions">

                                        <a
                                            href="{{ route('application.documents.view', [$application, $doc]) }}"
                                            class="document-card__view"
                                            target="_blank"
                                            rel="noopener"
                                        >
                                            View document
                                        </a>

                                        @if($status === 'REJECTED')

                                            <form
                                                method="POST"
                                                action="{{ route('application.documents.replace', [$application, $doc]) }}"
                                                enctype="multipart/form-data"
                                                class="document-replace-form"
                                            >

                                                @csrf

                                                <label class="document-upload-control">

                                                    <span>Choose replacement</span>

                                                    <input
                                                        type="file"
                                                        name="document"
                                                        required
                                                        accept=".pdf,.jpg,.jpeg,.png"
                                                        class="admission-file-input"
                                                    >

                                                </label>

                                                <x-portal-button
                                                    variant="secondary"
                                                    type="submit"
                                                >
                                                    Replace Document
                                                </x-portal-button>

                                            </form>

                                        @endif

                                    </div>

                                </article>

                            @endforeach

                        </div>

                    @else

                        <div class="documents-empty-list">

                            <span class="documents-empty-list__mark">
                                +
                            </span>

                            <h3>No documents uploaded yet</h3>

                            <p>
                                Documents for this application will appear
                                here once they have been uploaded.
                            </p>

                        </div>

                    @endif

                </section>

            @endforeach

        @endif

    </main>

</div>

@endsection
