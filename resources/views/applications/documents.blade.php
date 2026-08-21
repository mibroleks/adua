{{--  
Component: Application Documents
File Path: resources/views/applications/documents.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Displays the applicant's uploaded documents and verification status.

Status: 🚦 Integration / Hardening
Version: 2.3 (fixed VERIFIED counter + removed invalid inline comment)
--}}

@extends('layouts.portal')

@section('title', 'My Documents')

@section('content')

<div class="admission-page admission-page--documents">

    <main class="admission-shell admission-shell--wide">

        <header class="admission-page-header">
            <div class="admission-eyebrow">
                <span class="admission-eyebrow__dot" aria-hidden="true"></span>
                Application Documents
            </div>
            <h1 class="admission-page-title">Your documents</h1>
            <p class="admission-page-description">
                Review the documents submitted with your application and
                monitor their verification status.
            </p>
        </header>

        @if(!$application)
            <section class="admission-empty-state documents-empty-state">
                <div class="documents-empty-state__mark">DOC</div>
                <div>
                    <span class="documents-empty-state__eyebrow">APPLICATION REQUIRED</span>
                    <h2>No application found</h2>
                    <p>You have not started an application yet. Begin by selecting your programme.</p>
                    <x-portal-button variant="primary" href="{{ route('application.create') }}">
                        Start Application
                    </x-portal-button>
                </div>
            </section>
        @else
            {{-- Document Summary --}}
            @php
                $documentCount = $application->documents->count();
                $verifiedCount = $application->documents->where('status', 'VERIFIED')->count();
                $rejectedCount = $application->documents->where('status', 'REJECTED')->count();
            @endphp

            <section class="documents-summary">
                <div class="documents-summary__intro">
                    <span class="documents-summary__eyebrow">DOCUMENT CHECKLIST</span>
                    <h2>Application documents</h2>
                    <p>Keep your supporting documents complete and verified.</p>
                </div>
                <div class="documents-summary__stats">
                    <div class="documents-summary__stat">
                        <strong>{{ $documentCount }}</strong><span>Uploaded</span>
                    </div>
                    <div class="documents-summary__stat">
                        <strong>{{ $verifiedCount }}</strong><span>Verified</span>
                    </div>
                    <div class="documents-summary__stat">
                        <strong>{{ $rejectedCount }}</strong><span>Needs attention</span>
                    </div>
                </div>
            </section>

            {{-- Document List --}}
            <section class="documents-list-section">
                <div class="documents-list-section__heading">
                    <div>
                        <span class="documents-list-section__eyebrow">SUBMITTED FILES</span>
                        <h2>Document status</h2>
                    </div>
                </div>

                @if($documentCount)
                    <div class="documents-list">
                        @foreach($application->documents as $doc)
                            @php $status = strtoupper($doc->status); @endphp
                            <article class="document-card document-card--{{ strtolower($status) }}">
                                <div class="document-card__icon" aria-hidden="true"><span>DOC</span></div>
                                <div class="document-card__body">
                                    <div class="document-card__heading">
                                        <h3>{{ $doc->documentType?->name ?? 'Application Document' }}</h3>
                                        <span class="admission-status admission-status--{{ strtolower($status) }}">
                                            {{ $status }}
                                        </span>
                                    </div>
                                    @if($status === 'REJECTED' && $doc->rejection_reason)
                                        <div class="document-card__issue">
                                            <span class="document-card__issue-label">Correction required</span>
                                            <p>{{ $doc->rejection_reason }}</p>
                                        </div>
                                    @endif
                                </div>

                                <div class="document-card__actions">
                                    <a href="{{ route('application.documents.view', [$application, $doc]) }}"
                                       class="document-card__view"
                                       target="_blank"
                                       rel="noopener">
                                        View document
                                    </a>

                                    @if($status === 'REJECTED')
                                        <form method="POST"
                                              action="{{ route('application.documents.replace', [$application, $doc]) }}"
                                              enctype="multipart/form-data"
                                              class="document-replace-form">
                                            @csrf
                                            <label class="document-upload-control">
                                                <span>Choose replacement</span>
                                                <input type="file" name="document" required class="admission-file-input">
                                            </label>
                                            <x-portal-button variant="secondary" type="submit">
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
                        <span class="documents-empty-list__mark">+</span>
                        <h3>No documents uploaded yet</h3>
                        <p>Your required application documents will appear here once they have been uploaded.</p>
                    </div>
                @endif
            </section>
        @endif
    </main>
</div>

@endsection
