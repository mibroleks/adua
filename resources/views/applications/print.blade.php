<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <title>
        Application {{ $application->application_number }}
        — {{ setting('institution.name') ?? 'Institution Name' }}
    </title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="{{ asset('css/print.css') }}">

    <style>
        /*
        |--------------------------------------------------------------------------
        | Application Dossier — Print/PDF
        |--------------------------------------------------------------------------
        | This stylesheet is intentionally self-contained.
        | It is designed for browser printing and PDF generation.
        |
        | Do not depend on the portal's screen theme for print output.
        |--------------------------------------------------------------------------
        */

        @page {
            size: A4;
            margin: 16mm 15mm 18mm 15mm;

            @bottom-left {
                content: "Application Dossier";
                font-size: 8px;
                color: #6b7280;
            }

            @bottom-right {
                content: "Page " counter(page) " of " counter(pages);
                font-size: 8px;
                color: #6b7280;
            }
        }

        :root {
            --print-primary: #3f2a24;
            --print-primary-dark: #2d1d19;
            --print-accent: #b08d57;
            --print-heading: #1f2937;
            --print-text: #374151;
            --print-muted: #6b7280;
            --print-border: #d9dde3;
            --print-border-light: #e8ebef;
            --print-surface: #ffffff;
            --print-soft: #f6f4f1;
            --print-success: #166534;
            --print-warning: #92400e;
            --print-danger: #991b1b;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: #ffffff;
        }

        body {
            font-family:
                "Inter",
                "Segoe UI",
                Arial,
                Helvetica,
                sans-serif;

            font-size: 10.5pt;
            line-height: 1.55;
            color: var(--print-text);
            background: #ffffff;
        }

        .print-page {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
        }

        /* ------------------------------------------------------------------
           Print toolbar
        ------------------------------------------------------------------ */

        .print-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;

            margin: 0 0 20px;
            padding: 12px 14px;

            border: 1px solid var(--print-border);
            border-radius: 6px;

            background: #f8fafc;
        }

        .print-actions__hint {
            color: var(--print-muted);
            font-size: 12px;
        }

        .print-actions__buttons {
            display: flex;
            gap: 8px;
        }

        .print-button {
            appearance: none;
            border: 1px solid var(--print-border);
            border-radius: 5px;

            padding: 8px 13px;

            background: #ffffff;
            color: var(--print-heading);

            font-size: 12px;
            font-weight: 600;

            cursor: pointer;
            text-decoration: none;
        }

        .print-button--primary {
            border-color: var(--print-primary);
            background: var(--print-primary);
            color: #ffffff;
        }

        /* ------------------------------------------------------------------
           Institutional masthead
        ------------------------------------------------------------------ */

        .institution-header {
            display: table;
            width: 100%;

            padding-bottom: 15px;
            border-bottom: 2px solid var(--print-primary);
        }

        .institution-header__brand,
        .institution-header__document {
            display: table-cell;
            vertical-align: middle;
        }

        .institution-header__brand {
            width: 68%;
        }

        .institution-header__document {
            width: 32%;
            text-align: right;
        }

        .institution-brand {
            display: table;
        }

        .institution-brand__logo,
        .institution-brand__content {
            display: table-cell;
            vertical-align: middle;
        }

        .institution-brand__logo {
            width: 64px;
            padding-right: 14px;
        }

        .institution-brand__logo img {
            display: block;
            width: 54px;
            height: 54px;
            object-fit: contain;
        }

        .institution-name {
            margin: 0;

            color: var(--print-primary-dark);

            font-family:
                Georgia,
                "Times New Roman",
                serif;

            font-size: 18px;
            line-height: 1.2;
            font-weight: 700;
            letter-spacing: .01em;
        }

        .institution-address {
            margin: 4px 0 0;

            max-width: 480px;

            color: var(--print-muted);
            font-size: 9px;
            line-height: 1.45;
        }

        .document-label {
            margin: 0;

            color: var(--print-muted);

            font-size: 8px;
            font-weight: 700;
            letter-spacing: .16em;
            text-transform: uppercase;
        }

        .document-title {
            margin: 4px 0 0;

            color: var(--print-primary);

            font-size: 13px;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        /* ------------------------------------------------------------------
           Dossier identity
        ------------------------------------------------------------------ */

        .dossier-intro {
            display: table;
            width: 100%;

            margin: 18px 0 20px;
            padding-bottom: 14px;

            border-bottom: 1px solid var(--print-border);
        }

        .dossier-intro__title,
        .dossier-intro__number {
            display: table-cell;
            vertical-align: bottom;
        }

        .dossier-intro__title {
            width: 65%;
        }

        .dossier-intro__number {
            width: 35%;
            text-align: right;
        }

        .dossier-title {
            margin: 0;

            color: var(--print-heading);

            font-family:
                Georgia,
                "Times New Roman",
                serif;

            font-size: 21px;
            line-height: 1.2;
            font-weight: 700;
        }

        .dossier-subtitle {
            margin: 5px 0 0;

            color: var(--print-muted);
            font-size: 9px;
        }

        .application-number-label {
            display: block;

            margin-bottom: 3px;

            color: var(--print-muted);

            font-size: 7.5px;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .application-number {
            display: block;

            color: var(--print-primary);

            font-size: 11px;
            font-weight: 800;
            letter-spacing: .04em;
        }

        /* ------------------------------------------------------------------
           Summary strip
        ------------------------------------------------------------------ */

        .summary-strip {
            display: table;
            width: 100%;

            margin-bottom: 22px;

            border: 1px solid var(--print-border);
        }

        .summary-item {
            display: table-cell;
            width: 25%;

            padding: 10px 12px;

            border-right: 1px solid var(--print-border);
            vertical-align: top;
        }

        .summary-item:last-child {
            border-right: 0;
        }

        .summary-label {
            display: block;

            margin-bottom: 3px;

            color: var(--print-muted);

            font-size: 7.5px;
            font-weight: 700;
            letter-spacing: .09em;
            text-transform: uppercase;
        }

        .summary-value {
            display: block;

            color: var(--print-heading);

            font-size: 9.5px;
            font-weight: 700;
        }

        /* ------------------------------------------------------------------
           Sections
        ------------------------------------------------------------------ */

        .section {
            margin: 0 0 22px;
        }

        .section-header {
            display: table;
            width: 100%;

            margin-bottom: 8px;
            padding-bottom: 6px;

            border-bottom: 1.5px solid var(--print-primary);
        }

        .section-heading,
        .section-index {
            display: table-cell;
            vertical-align: bottom;
        }

        .section-heading {
            margin: 0;

            color: var(--print-primary-dark);

            font-size: 11px;
            font-weight: 800;
            letter-spacing: .03em;
            text-transform: uppercase;
        }

        .section-index {
            width: 40px;

            color: var(--print-accent);

            font-size: 8px;
            font-weight: 800;
            text-align: right;
        }

        /* ------------------------------------------------------------------
           Tables
        ------------------------------------------------------------------ */

        .data-table {
            width: 100%;

            border-collapse: collapse;
            border-spacing: 0;

            font-size: 9.5px;
        }

        .data-table th,
        .data-table td {
            padding: 7px 9px;

            border: 1px solid var(--print-border-light);

            text-align: left;
            vertical-align: top;

            line-height: 1.45;
        }

        .data-table th {
            width: 30%;

            background: var(--print-soft);

            color: #4b5563;

            font-weight: 700;
        }

        .data-table td {
            color: var(--print-text);
            background: #ffffff;
        }

        .data-table thead th {
            width: auto;

            background: var(--print-primary);
            color: #ffffff;

            border-color: var(--print-primary);

            font-size: 8px;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .data-table--documents th:first-child {
            width: 42%;
        }

        .data-table--history th:first-child {
            width: 22%;
        }

        .empty-row {
            color: var(--print-muted);
            font-style: italic;
            text-align: center !important;
        }

        /* ------------------------------------------------------------------
           Status badges
        ------------------------------------------------------------------ */

        .status {
            display: inline-block;

            padding: 3px 7px;

            border: 1px solid var(--print-border);
            border-radius: 999px;

            color: var(--print-heading);
            background: #f8fafc;

            font-size: 7.5px;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .status--success {
            border-color: #bbf7d0;
            color: var(--print-success);
            background: #f0fdf4;
        }

        .status--warning {
            border-color: #fde68a;
            color: var(--print-warning);
            background: #fffbeb;
        }

        .status--danger {
            border-color: #fecaca;
            color: var(--print-danger);
            background: #fef2f2;
        }

        /* ------------------------------------------------------------------
           Footer
        ------------------------------------------------------------------ */

        .document-footer {
            margin-top: 28px;
            padding-top: 10px;

            border-top: 1px solid var(--print-border);

            color: var(--print-muted);

            font-size: 8px;
            line-height: 1.5;
        }

        .document-footer__row {
            display: table;
            width: 100%;
        }

        .document-footer__left,
        .document-footer__right {
            display: table-cell;
            vertical-align: top;
        }

        .document-footer__right {
            text-align: right;
        }

        /* ------------------------------------------------------------------
           Print behaviour
        ------------------------------------------------------------------ */

        .avoid-break {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .page-break-before {
            page-break-before: always;
            break-before: page;
        }

        tr {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        thead {
            display: table-header-group;
        }

        @media print {

            html,
            body {
                background: #ffffff !important;
            }

            .print-actions {
                display: none !important;
            }

            .print-page {
                width: 100%;
                max-width: none;
            }

            /*
             * Preserve institutional colours in browser print/PDF.
             * Users should enable "Background graphics" in Chrome/Edge
             * when printing if the browser does not preserve backgrounds.
             */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .institution-header {
                break-inside: avoid;
                page-break-inside: avoid;
            }

            .section {
                break-inside: avoid;
            }
        }

        @media screen {

            body {
                background: #eef1f4;
            }

            .print-page {
                max-width: 1000px;
                margin: 28px auto;
                padding: 0 24px 40px;
            }

            .print-document {
                padding: 30px;
                background: #ffffff;
                box-shadow: 0 12px 40px rgba(0, 0, 0, .08);
            }
        }

        @media screen and (max-width: 700px) {

            .print-page {
                margin: 0;
                padding: 12px;
            }

            .print-document {
                padding: 18px;
            }

            .print-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .print-actions__buttons {
                flex-wrap: wrap;
            }

            .institution-header,
            .institution-header__brand,
            .institution-header__document,
            .dossier-intro,
            .dossier-intro__title,
            .dossier-intro__number,
            .summary-strip,
            .summary-item {
                display: block;
                width: 100%;
            }

            .institution-header__document,
            .dossier-intro__number {
                margin-top: 12px;
                text-align: left;
            }

            .summary-item {
                border-right: 0;
                border-bottom: 1px solid var(--print-border);
            }

            .summary-item:last-child {
                border-bottom: 0;
            }
        }
    </style>
</head>

<body>

@php
    $theme = app(\App\Services\ThemeService::class);

    $institutionName =
        $theme->institutionName()
        ?? setting('institution.name')
        ?? 'Institution Name';

    $institutionAddress =
        setting('institution.address')
        ?? '';

    $logoUrl = $theme->logoUrl();

    $applicationStatus =
        $application->application_status
        ?? '—';

    $paymentStatus =
        $application->payment_status
        ?? '—';

    $decision =
        $application->decision?->decision;

    $decisionLabel = $decision
        ? ucwords(str_replace('_', ' ', $decision))
        : 'Not yet decided';

    $statusClass = match (strtoupper((string) $applicationStatus)) {
        'APPROVED', 'SUCCESS', 'PAID', 'COMPLETED' => 'status status--success',
        'PENDING', 'UNDER_REVIEW', 'SUBMITTED' => 'status status--warning',
        'REJECTED', 'FAILED', 'CANCELLED' => 'status status--danger',
        default => 'status',
    };

    $paymentStatusClass = match (strtoupper((string) $paymentStatus)) {
        'SUCCESS', 'PAID', 'COMPLETED' => 'status status--success',
        'PENDING' => 'status status--warning',
        'FAILED', 'CANCELLED' => 'status status--danger',
        default => 'status',
    };

    $decisionClass = match (strtoupper((string) $decision)) {
        'APPROVED' => 'status status--success',
        'REJECTED' => 'status status--danger',
        default => 'status status--warning',
    };
@endphp

<div class="print-page">

    {{-- ================================================================
         Screen-only print controls
    ================================================================= --}}
    <div class="print-actions">

        <div class="print-actions__hint">
            Official application dossier · A4 print format
        </div>

        <div class="print-actions__buttons">

            <button
                type="button"
                class="print-button print-button--primary"
                onclick="window.print()"
            >
                Print / Save PDF
            </button>

        </div>

    </div>

    <main class="print-document">

        {{-- ============================================================
             Institutional Header
        ============================================================= --}}
        <header class="institution-header">

            <div class="institution-header__brand">

                <div class="institution-brand">

                    @if($logoUrl)
                        <div class="institution-brand__logo">
                            <img
                                src="{{ $logoUrl }}"
                                alt="{{ $institutionName }} Logo"
                            >
                        </div>
                    @endif

                    <div class="institution-brand__content">

                        <h1 class="institution-name">
                            {{ $institutionName }}
                        </h1>

                        @if($institutionAddress)
                            <p class="institution-address">
                                {{ $institutionAddress }}
                            </p>
                        @endif

                    </div>

                </div>

            </div>

            <div class="institution-header__document">

                <p class="document-label">
                    Official Record
                </p>

                <p class="document-title">
                    Application Dossier
                </p>

            </div>

        </header>


        {{-- ============================================================
             Dossier Identity
        ============================================================= --}}
        <div class="dossier-intro">

            <div class="dossier-intro__title">

                <h2 class="dossier-title">
                    Application Dossier
                </h2>

                <p class="dossier-subtitle">
                    Confidential admissions record
                </p>

            </div>

            <div class="dossier-intro__number">

                <span class="application-number-label">
                    Application Number
                </span>

                <span class="application-number">
                    {{ $application->application_number }}
                </span>

            </div>

        </div>


        {{-- ============================================================
             Summary
        ============================================================= --}}
        <div class="summary-strip avoid-break">

            <div class="summary-item">

                <span class="summary-label">
                    Application Status
                </span>

                <span class="{{ $statusClass }}">
                    {{ ucwords(str_replace('_', ' ', $applicationStatus)) }}
                </span>

            </div>

            <div class="summary-item">

                <span class="summary-label">
                    Payment Status
                </span>

                <span class="{{ $paymentStatusClass }}">
                    {{ ucwords(str_replace('_', ' ', $paymentStatus)) }}
                </span>

            </div>

            <div class="summary-item">

                <span class="summary-label">
                    Programme
                </span>

                <span class="summary-value">
                    {{ $application->programme?->name ?? '—' }}
                </span>

            </div>

            <div class="summary-item">

                <span class="summary-label">
                    Submitted
                </span>

                <span class="summary-value">
                    {{ $application->formatted_submitted_at ?? '—' }}
                </span>

            </div>

        </div>


        {{-- ============================================================
             01 — Application Overview
        ============================================================= --}}
        <section class="section avoid-break">

            <div class="section-header">
                <h2 class="section-heading">
                    Application Overview
                </h2>

                <span class="section-index">
                    01
                </span>
            </div>

            <table class="data-table">

                <tbody>

                    <tr>
                        <th>Programme</th>
                        <td>{{ $application->programme?->name ?? '—' }}</td>
                    </tr>

                    <tr>
                        <th>Faculty</th>
                        <td>
                            {{ $application->programme?->faculty?->name ?? '—' }}
                        </td>
                    </tr>

                    <tr>
                        <th>Department</th>
                        <td>
                            {{ $application->programme?->department?->name ?? '—' }}
                        </td>
                    </tr>

                    <tr>
                        <th>Application Fee</th>
                        <td>
                            {{ $application->formatted_application_fee ?? '—' }}
                        </td>
                    </tr>

                    <tr>
                        <th>Application Status</th>
                        <td>
                            {{ ucwords(str_replace('_', ' ', $applicationStatus)) }}
                        </td>
                    </tr>

                    <tr>
                        <th>Payment Status</th>
                        <td>
                            {{ ucwords(str_replace('_', ' ', $paymentStatus)) }}
                        </td>
                    </tr>

                    <tr>
                        <th>Submitted At</th>
                        <td>
                            {{ $application->formatted_submitted_at ?? '—' }}
                        </td>
                    </tr>

                </tbody>

            </table>

        </section>


        {{-- ============================================================
             02 — Applicant Information
        ============================================================= --}}
        <section class="section avoid-break">

            <div class="section-header">
                <h2 class="section-heading">
                    Applicant Information
                </h2>

                <span class="section-index">
                    02
                </span>
            </div>

            <table class="data-table">

                <tbody>

                    <tr>
                        <th>Full Name</th>
                        <td>
                            {{ $application->user?->name ?? '—' }}
                        </td>
                    </tr>

                    <tr>
                        <th>Email Address</th>
                        <td>
                            {{ $application->user?->email ?? '—' }}
                        </td>
                    </tr>

                    <tr>
                        <th>Phone Number</th>
                        <td>
                            {{ $application->user?->phone ?? '—' }}
                        </td>
                    </tr>

                </tbody>

            </table>

        </section>


        {{-- ============================================================
             03 — Application Fields
        ============================================================= --}}
        @if($application->fieldValues->count())

            <section class="section">

                <div class="section-header">
                    <h2 class="section-heading">
                        Application Information
                    </h2>

                    <span class="section-index">
                        03
                    </span>
                </div>

                <table class="data-table">

                    <tbody>

                        @foreach($application->fieldValues as $fv)

                            <tr>

                                <th>
                                    {{ $fv->formField?->label
                                        ?? $fv->formField?->key
                                        ?? 'Field' }}
                                </th>

                                <td>
                                    {{ $fv->value ?? '—' }}
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </section>

        @endif


        {{-- ============================================================
             Documents
        ============================================================= --}}
        <section class="section">

            <div class="section-header">

                <h2 class="section-heading">
                    Documents
                </h2>

                <span class="section-index">
                    04
                </span>

            </div>

            <table class="data-table data-table--documents">

                <thead>

                    <tr>
                        <th>Document</th>
                        <th>Status</th>
                        <th>Uploaded At</th>
                    </tr>

                </thead>

                <tbody>

                    @forelse($application->documents as $doc)

                        <tr>

                            <td>
                                {{ $doc->documentType?->name ?? 'Document' }}
                            </td>

                            <td>
                                {{ ucwords(str_replace('_', ' ', $doc->status ?? '—')) }}
                            </td>

                            <td>
                                {{ $doc->uploaded_at?->format('d M Y, H:i') ?? '—' }}
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="3" class="empty-row">
                                No documents uploaded.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </section>


        {{-- ============================================================
             Payment
        ============================================================= --}}
        @if($application->payment)

            <section class="section avoid-break">

                <div class="section-header">

                    <h2 class="section-heading">
                        Payment Record
                    </h2>

                    <span class="section-index">
                        05
                    </span>

                </div>

                <table class="data-table">

                    <tbody>

                        <tr>
                            <th>Reference</th>
                            <td>
                                {{ $application->payment->reference ?? '—' }}
                            </td>
                        </tr>

                        <tr>
                            <th>Amount</th>
                            <td>
                                ₦{{ number_format(
                                    $application->payment->amountInNaira(),
                                    2
                                ) }}
                            </td>
                        </tr>

                        <tr>
                            <th>Gateway</th>
                            <td>
                                {{ $application->payment->gateway ?? '—' }}
                            </td>
                        </tr>

                        <tr>
                            <th>Status</th>
                            <td>
                                {{ ucwords(str_replace(
                                    '_',
                                    ' ',
                                    $application->payment->status ?? '—'
                                )) }}
                            </td>
                        </tr>

                        <tr>
                            <th>Paid At</th>
                            <td>
                                {{ $application->payment->paid_at?->format(
                                    'd M Y, H:i'
                                ) ?? '—' }}
                            </td>
                        </tr>

                    </tbody>

                </table>

            </section>

        @endif


        {{-- ============================================================
             Admission Decision
        ============================================================= --}}
        @if($application->decision)

            <section class="section avoid-break">

                <div class="section-header">

                    <h2 class="section-heading">
                        Admission Decision
                    </h2>

                    <span class="section-index">
                        06
                    </span>

                </div>

                <table class="data-table">

                    <tbody>

                        <tr>
                            <th>Decision</th>

                            <td>
                                <span class="{{ $decisionClass }}">
                                    {{ $decisionLabel }}
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <th>Remarks</th>

                            <td>
                                {{ $application->decision->remarks ?? '—' }}
                            </td>
                        </tr>

                        <tr>
                            <th>Officer</th>

                            <td>
                                {{ $application->decision->officer?->name ?? '—' }}
                            </td>
                        </tr>

                        <tr>
                            <th>Decided At</th>

                            <td>
                                {{ $application->decision->decided_at?->format(
                                    'd M Y, H:i'
                                ) ?? '—' }}
                            </td>
                        </tr>

                    </tbody>

                </table>

            </section>

        @endif


        {{-- ============================================================
             Status History
        ============================================================= --}}
        <section class="section">

            <div class="section-header">

                <h2 class="section-heading">
                    Status History
                </h2>

                <span class="section-index">
                    07
                </span>

            </div>

            <table class="data-table data-table--history">

                <thead>

                    <tr>
                        <th>Date</th>
                        <th>Previous Status</th>
                        <th>New Status</th>
                        <th>Changed By</th>
                    </tr>

                </thead>

                <tbody>

                    @forelse($application->statusHistories as $history)

                        <tr>

                            <td>
                                {{ $history->changed_at?->format(
                                    'd M Y, H:i'
                                ) ?? '—' }}
                            </td>

                            <td>
                                {{ $history->old_status
                                    ? ucwords(str_replace(
                                        '_',
                                        ' ',
                                        $history->old_status
                                    ))
                                    : '—' }}
                            </td>

                            <td>
                                {{ ucwords(str_replace(
                                    '_',
                                    ' ',
                                    $history->new_status ?? '—'
                                )) }}
                            </td>

                            <td>
                                {{ $history->officer?->name ?? 'System' }}
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="4" class="empty-row">
                                No status history recorded.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </section>


        {{-- ============================================================
             Document Footer
        ============================================================= --}}
        <footer class="document-footer">

            <div class="document-footer__row">

                <div class="document-footer__left">

                    <strong>
                        {{ $institutionName }}
                    </strong>

                    <br>

                    Confidential admissions record.
                    For authorised institutional use only.

                </div>

                <div class="document-footer__right">

                    Application No.
                    <strong>
                        {{ $application->application_number }}
                    </strong>

                    <br>

                    Generated {{ now()->format('d M Y, H:i') }}

                </div>

            </div>

        </footer>

    </main>

</div>

</body>
</html>
```
