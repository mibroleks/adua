{{-- 
|--------------------------------------------------------------------------
| Component: Admin Application Dossier
| File Path: resources/views/admin/applications/show.blade.php
| Company: Ygrace Tech
| Author: Ibrahim Olalekan
|
| Purpose:
| Presents a complete application dossier for admissions officers.
|
| Design:
| - Professional administrative review workspace
| - Keeps existing routes and data relationships
| - No backend changes required
| - Uses semantic status classes for visual clarity
|
| Status: Production UI
|--------------------------------------------------------------------------
--}}

@extends('layouts.app')

@section('title', 'Application Dossier')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | Safe display helpers
    |--------------------------------------------------------------------------
    */

    $applicantName = $application->user?->name ?? 'Unknown Applicant';

    $programmeName = $application->programme?->name ?? 'Programme not assigned';

    $facultyName = $application->programme?->faculty?->name ?? '—';

    $departmentName = $application->programme?->department?->name ?? '—';

    $applicationStatus = strtoupper(
        str_replace('_', ' ', $application->application_status ?? 'UNKNOWN')
    );

    $paymentStatus = strtoupper(
        str_replace('_', ' ', $application->payment_status ?? 'UNKNOWN')
    );

    $decisionStatus = $application->decision?->decision
        ? strtoupper(str_replace('_', ' ', $application->decision->decision))
        : null;

    /*
    |--------------------------------------------------------------------------
    | Status presentation helpers
    |--------------------------------------------------------------------------
    */

    $applicationStatusClass = match ($application->application_status) {
        'approved' => 'status-badge--success',
        'rejected' => 'status-badge--danger',
        'under_review' => 'status-badge--warning',
        'submitted' => 'status-badge--info',
        default => 'status-badge--neutral',
    };

    $paymentStatusClass = match ($application->payment_status) {
        'success', 'paid' => 'status-badge--success',
        'failed' => 'status-badge--danger',
        'pending' => 'status-badge--warning',
        default => 'status-badge--neutral',
    };

    $decisionClass = match ($application->decision?->decision) {
        'APPROVED', 'approved' => 'status-badge--success',
        'REJECTED', 'rejected' => 'status-badge--danger',
        default => 'status-badge--neutral',
    };
@endphp


<div class="admin-dossier-page">

    {{-- ================================================================
         TOP BAR
         ================================================================ --}}
    <div class="admin-dossier-topbar">

        <div class="admin-dossier-topbar__left">

            <a
                href="{{ route('admin.applications.index') }}"
                class="dossier-back-link"
            >
                <span aria-hidden="true">←</span>
                <span>Applications</span>
            </a>

            <span class="dossier-topbar-divider" aria-hidden="true"></span>

            <span class="dossier-topbar-label">
                Application Dossier
            </span>

        </div>


        <div class="admin-dossier-actions">

            <a
                href="{{ route('admin.applications.print', $application) }}"
                target="_blank"
                class="dossier-action dossier-action--secondary"
            >
                <span aria-hidden="true">↗</span>
                Print
            </a>

            <a
                href="{{ route('admin.applications.pdf', $application) }}"
                target="_blank"
                class="dossier-action dossier-action--secondary"
            >
                <span aria-hidden="true">↓</span>
                PDF
            </a>

            <a
                href="{{ route('admin.applications.export.excel', $application) }}"
                class="dossier-action dossier-action--secondary"
            >
                Excel
            </a>

            <a
                href="{{ route('admin.applications.export.csv', $application) }}"
                class="dossier-action dossier-action--secondary"
            >
                CSV
            </a>

        </div>

    </div>


    {{-- ================================================================
         APPLICANT HERO
         ================================================================ --}}
    <section class="dossier-hero">

        <div class="dossier-hero__identity">

            <div class="dossier-avatar" aria-hidden="true">
                {{ strtoupper(mb_substr($applicantName, 0, 1)) }}
            </div>

            <div>

                <div class="dossier-eyebrow">
                    APPLICATION
                </div>

                <h1>
                    {{ $applicantName }}
                </h1>

                <div class="dossier-identity-meta">

                    <span>
                        {{ $application->application_number }}
                    </span>

                    @if($application->user?->email)
                        <span class="dossier-meta-dot" aria-hidden="true">•</span>

                        <span>
                            {{ $application->user->email }}
                        </span>
                    @endif

                    @if($application->user?->phone)
                        <span class="dossier-meta-dot" aria-hidden="true">•</span>

                        <span>
                            {{ $application->user->phone }}
                        </span>
                    @endif

                </div>

            </div>

        </div>


        <div class="dossier-hero__status">

            <span class="status-badge {{ $applicationStatusClass }}">
                <span class="status-badge__dot"></span>
                {{ $applicationStatus }}
            </span>

            @if($decisionStatus)
                <span class="status-badge {{ $decisionClass }}">
                    {{ $decisionStatus }}
                </span>
            @endif

        </div>

    </section>


    {{-- ================================================================
         APPLICATION SUMMARY
         ================================================================ --}}
    <section class="dossier-summary-grid">

        <article class="dossier-summary-card dossier-summary-card--primary">

            <div class="dossier-summary-card__label">
                PROGRAMME
            </div>

            <div class="dossier-summary-card__value">
                {{ $programmeName }}
            </div>

            <div class="dossier-summary-card__subvalue">
                {{ $departmentName }}
            </div>

        </article>


        <article class="dossier-summary-card">

            <div class="dossier-summary-card__label">
                FACULTY
            </div>

            <div class="dossier-summary-card__value">
                {{ $facultyName }}
            </div>

        </article>


        <article class="dossier-summary-card">

            <div class="dossier-summary-card__label">
                APPLICATION FEE
            </div>

            <div class="dossier-summary-card__value">
                {{ $application->formatted_application_fee }}
            </div>

        </article>


        <article class="dossier-summary-card">

            <div class="dossier-summary-card__label">
                PAYMENT
            </div>

            <div class="dossier-summary-card__value">
                <span class="status-badge status-badge--small {{ $paymentStatusClass }}">
                    {{ $paymentStatus }}
                </span>
            </div>

        </article>

    </section>


    {{-- ================================================================
         MAIN CONTENT
         ================================================================ --}}
    <div class="dossier-layout">

        <main class="dossier-main">


            {{-- ========================================================
                 APPLICATION OVERVIEW
                 ======================================================== --}}
            <section class="dossier-card">

                <header class="dossier-card__header">

                    <div>
                        <span class="dossier-card__eyebrow">
                            APPLICATION
                        </span>

                        <h2>
                            Application Overview
                        </h2>
                    </div>

                    <span class="dossier-card__index">
                        01
                    </span>

                </header>


                <div class="dossier-info-grid">

                    <div class="dossier-info-item">
                        <span>Application Number</span>
                        <strong>
                            {{ $application->application_number }}
                        </strong>
                    </div>

                    <div class="dossier-info-item">
                        <span>Programme</span>
                        <strong>
                            {{ $programmeName }}
                        </strong>
                    </div>

                    <div class="dossier-info-item">
                        <span>Faculty</span>
                        <strong>
                            {{ $facultyName }}
                        </strong>
                    </div>

                    <div class="dossier-info-item">
                        <span>Department</span>
                        <strong>
                            {{ $departmentName }}
                        </strong>
                    </div>

                    <div class="dossier-info-item">
                        <span>Application Status</span>
                        <strong>
                            <span class="status-badge status-badge--small {{ $applicationStatusClass }}">
                                {{ $applicationStatus }}
                            </span>
                        </strong>
                    </div>

                    <div class="dossier-info-item">
                        <span>Submitted At</span>
                        <strong>
                            {{ $application->formatted_submitted_at }}
                        </strong>
                    </div>

                </div>

            </section>


            {{-- ========================================================
                 APPLICANT INFORMATION
                 ======================================================== --}}
            <section class="dossier-card">

                <header class="dossier-card__header">

                    <div>
                        <span class="dossier-card__eyebrow">
                            APPLICANT
                        </span>

                        <h2>
                            Applicant Information
                        </h2>
                    </div>

                    <span class="dossier-card__index">
                        02
                    </span>

                </header>


                <div class="dossier-info-grid">

                    <div class="dossier-info-item dossier-info-item--wide">
                        <span>Full Name</span>
                        <strong>
                            {{ $applicantName }}
                        </strong>
                    </div>

                    <div class="dossier-info-item">
                        <span>Email Address</span>
                        <strong>
                            {{ $application->user?->email ?? '—' }}
                        </strong>
                    </div>

                    <div class="dossier-info-item">
                        <span>Phone Number</span>
                        <strong>
                            {{ $application->user?->phone ?? '—' }}
                        </strong>
                    </div>

                </div>

            </section>


            {{-- ========================================================
                 DYNAMIC APPLICATION FIELDS
                 ======================================================== --}}
            @if($application->fieldValues->count())

                <section class="dossier-card">

                    <header class="dossier-card__header">

                        <div>
                            <span class="dossier-card__eyebrow">
                                APPLICATION DATA
                            </span>

                            <h2>
                                Application Fields
                            </h2>
                        </div>

                        <span class="dossier-card__index">
                            03
                        </span>

                    </header>


                    <div class="dossier-field-list">

                        @foreach($application->fieldValues as $fv)

                            <div class="dossier-field-row">

                                <div class="dossier-field-row__label">
                                    {{ $fv->formField?->label ?? $fv->formField?->key }}
                                </div>

                                <div class="dossier-field-row__value">
                                    {{ $fv->value ?: '—' }}
                                </div>

                            </div>

                        @endforeach

                    </div>

                </section>

            @endif


            {{-- ========================================================
                 DOCUMENTS
                 ======================================================== --}}
            <section class="dossier-card">

                <header class="dossier-card__header">

                    <div>
                        <span class="dossier-card__eyebrow">
                            VERIFICATION
                        </span>

                        <h2>
                            Documents
                        </h2>
                    </div>

                    <span class="dossier-card__index">
                        04
                    </span>

                </header>


                <div class="dossier-table-wrap">

                    <table class="dossier-table">

                        <thead>
                            <tr>
                                <th>Document</th>
                                <th>Status</th>
                                <th>Uploaded</th>
                                <th class="dossier-table__action-cell">
                                    Action
                                </th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse($application->documents as $doc)

                                <tr>

                                    <td>
                                        <div class="document-cell">

                                            <div class="document-cell__icon">
                                                DOC
                                            </div>

                                            <div>
                                                <strong>
                                                    {{ $doc->documentType?->name ?? 'Document' }}
                                                </strong>

                                                @if($doc->path)
                                                    <span>
                                                        Application document
                                                    </span>
                                                @endif
                                            </div>

                                        </div>
                                    </td>

                                    <td>
                                        <span class="status-badge status-badge--small status-badge--neutral">
                                            {{ strtoupper(str_replace('_', ' ', $doc->status ?? 'UNKNOWN')) }}
                                        </span>
                                    </td>

                                    <td>
                                        {{ $doc->uploaded_at?->format('d M Y, H:i') ?? '—' }}
                                    </td>

                                    <td class="dossier-table__action-cell">

                                        @if($doc->path)

                                            <a
                                                href="{{ Storage::url($doc->path) }}"
                                                target="_blank"
                                                rel="noopener"
                                                class="dossier-view-link"
                                            >
                                                View
                                                <span aria-hidden="true">↗</span>
                                            </a>

                                        @else
                                            <span class="dossier-muted">
                                                Unavailable
                                            </span>
                                        @endif

                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td colspan="4">
                                        <div class="dossier-empty">
                                            <strong>No documents uploaded</strong>
                                            <span>
                                                There are currently no documents attached to this application.
                                            </span>
                                        </div>
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </section>


            {{-- ========================================================
                 PAYMENT
                 ======================================================== --}}
            @if($application->payment)

                <section class="dossier-card">

                    <header class="dossier-card__header">

                        <div>
                            <span class="dossier-card__eyebrow">
                                FINANCE
                            </span>

                            <h2>
                                Payment
                            </h2>
                        </div>

                        <span class="dossier-card__index">
                            05
                        </span>

                    </header>


                    <div class="dossier-payment">

                        <div class="dossier-payment__amount">

                            <span>
                                Amount Paid
                            </span>

                            <strong>
                                ₦{{ number_format($application->payment->amountInNaira(), 2) }}
                            </strong>

                        </div>


                        <div class="dossier-info-grid dossier-info-grid--payment">

                            <div class="dossier-info-item">
                                <span>Reference</span>
                                <strong>
                                    {{ $application->payment->reference }}
                                </strong>
                            </div>

                            <div class="dossier-info-item">
                                <span>Gateway</span>
                                <strong>
                                    {{ strtoupper($application->payment->gateway ?? '—') }}
                                </strong>
                            </div>

                            <div class="dossier-info-item">
                                <span>Status</span>
                                <strong>
                                    <span class="status-badge status-badge--small {{ $paymentStatusClass }}">
                                        {{ strtoupper($application->payment->status ?? 'UNKNOWN') }}
                                    </span>
                                </strong>
                            </div>

                            <div class="dossier-info-item">
                                <span>Paid At</span>
                                <strong>
                                    {{ $application->payment->paid_at?->format('d M Y, H:i') ?? '—' }}
                                </strong>
                            </div>

                        </div>

                    </div>

                </section>

            @endif


            {{-- ========================================================
                 ADMISSION DECISION
                 ======================================================== --}}
            @if($application->decision)

                <section class="dossier-card dossier-card--decision">

                    <header class="dossier-card__header">

                        <div>
                            <span class="dossier-card__eyebrow">
                                ADMISSIONS
                            </span>

                            <h2>
                                Admission Decision
                            </h2>
                        </div>

                        <span class="dossier-card__index">
                            06
                        </span>

                    </header>


                    <div class="decision-panel">

                        <div class="decision-panel__status">

                            <span class="decision-panel__label">
                                Decision
                            </span>

                            <span class="status-badge {{ $decisionClass }}">
                                <span class="status-badge__dot"></span>
                                {{ $decisionStatus }}
                            </span>

                        </div>


                        <div class="dossier-info-grid">

                            <div class="dossier-info-item">
                                <span>Officer</span>
                                <strong>
                                    {{ $application->decision->officer?->name ?? '—' }}
                                </strong>
                            </div>

                            <div class="dossier-info-item">
                                <span>Decided At</span>
                                <strong>
                                    {{ $application->decision->decided_at?->format('d M Y, H:i') ?? '—' }}
                                </strong>
                            </div>

                        </div>


                        @if($application->decision->remarks)

                            <div class="decision-remarks">

                                <span>
                                    Officer Remarks
                                </span>

                                <p>
                                    {{ $application->decision->remarks }}
                                </p>

                            </div>

                        @endif

                    </div>

                </section>

            @endif


            {{-- ========================================================
                 STATUS HISTORY
                 ======================================================== --}}
            <section class="dossier-card">

                <header class="dossier-card__header">

                    <div>
                        <span class="dossier-card__eyebrow">
                            AUDIT TRAIL
                        </span>

                        <h2>
                            Status History
                        </h2>
                    </div>

                    <span class="dossier-card__index">
                        07
                    </span>

                </header>


                <div class="dossier-timeline">

                    @forelse($application->statusHistories->sortByDesc('changed_at') as $history)

                        <div class="dossier-timeline__item">

                            <div class="dossier-timeline__marker">
                                <span></span>
                            </div>

                            <div class="dossier-timeline__content">

                                <div class="dossier-timeline__top">

                                    <strong>
                                        {{ strtoupper(str_replace('_', ' ', $history->new_status)) }}
                                    </strong>

                                    <time>
                                        {{ $history->changed_at?->format('d M Y, H:i') }}
                                    </time>

                                </div>


                                <div class="dossier-timeline__transition">

                                    <span>
                                        {{ $history->old_status
                                            ? strtoupper(str_replace('_', ' ', $history->old_status))
                                            : 'INITIAL STATUS' }}
                                    </span>

                                    <span aria-hidden="true">→</span>

                                    <span>
                                        {{ strtoupper(str_replace('_', ' ', $history->new_status)) }}
                                    </span>

                                </div>


                                <div class="dossier-timeline__actor">

                                    Changed by
                                    <strong>
                                        {{ $history->officer?->name ?? 'System' }}
                                    </strong>

                                </div>

                            </div>

                        </div>

                    @empty

                        <div class="dossier-empty">
                            <strong>No status history recorded</strong>
                            <span>
                                No application lifecycle events have been recorded yet.
                            </span>
                        </div>

                    @endforelse

                </div>

            </section>


        </main>


        {{-- ================================================================
             RIGHT SIDEBAR
             ================================================================ --}}
        <aside class="dossier-sidebar">


            {{-- Application identity --}}
            <section class="dossier-side-card">

                <span class="dossier-side-card__eyebrow">
                    APPLICATION
                </span>

                <h3>
                    {{ $application->application_number }}
                </h3>

                <p>
                    Submitted
                    {{ $application->formatted_submitted_at }}
                </p>

            </section>


            {{-- Programme --}}
            <section class="dossier-side-card">

                <span class="dossier-side-card__eyebrow">
                    PROGRAMME
                </span>

                <h3>
                    {{ $programmeName }}
                </h3>

                <div class="dossier-side-list">

                    <div>
                        <span>Faculty</span>
                        <strong>{{ $facultyName }}</strong>
                    </div>

                    <div>
                        <span>Department</span>
                        <strong>{{ $departmentName }}</strong>
                    </div>

                </div>

            </section>


            {{-- Current state --}}
            <section class="dossier-side-card">

                <span class="dossier-side-card__eyebrow">
                    CURRENT STATE
                </span>

                <div class="dossier-side-status-row">

                    <span>
                        Application
                    </span>

                    <span class="status-badge status-badge--small {{ $applicationStatusClass }}">
                        {{ $applicationStatus }}
                    </span>

                </div>


                <div class="dossier-side-status-row">

                    <span>
                        Payment
                    </span>

                    <span class="status-badge status-badge--small {{ $paymentStatusClass }}">
                        {{ $paymentStatus }}
                    </span>

                </div>

            </section>


            {{-- Export --}}
            <section class="dossier-side-card dossier-side-card--actions">

                <span class="dossier-side-card__eyebrow">
                    RECORD ACTIONS
                </span>

                <a
                    href="{{ route('admin.applications.print', $application) }}"
                    target="_blank"
                    class="dossier-side-action"
                >
                    <span>Print application</span>
                    <span aria-hidden="true">↗</span>
                </a>

                <a
                    href="{{ route('admin.applications.pdf', $application) }}"
                    target="_blank"
                    class="dossier-side-action"
                >
                    <span>Download PDF</span>
                    <span aria-hidden="true">↓</span>
                </a>

                <a
                    href="{{ route('admin.applications.export.excel', $application) }}"
                    class="dossier-side-action"
                >
                    <span>Export Excel</span>
                    <span aria-hidden="true">↓</span>
                </a>

                <a
                    href="{{ route('admin.applications.export.csv', $application) }}"
                    class="dossier-side-action"
                >
                    <span>Export CSV</span>
                    <span aria-hidden="true">↓</span>
                </a>

            </section>

        </aside>

    </div>

</div>

@endsection