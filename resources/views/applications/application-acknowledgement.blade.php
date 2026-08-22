<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @php
        $theme = app(\App\Services\ThemeService::class);

        $institutionName = $theme->institutionName() ?? 'Institution Name';
        $shortName = $theme->shortName() ?? '';
        $logoUrl = $theme->logoUrl();

        $tokens = $theme->tokens();

        $primary = $tokens['primary'] ?? '#1F3A5F';
        $secondary = $tokens['secondary'] ?? '#16324F';
        $accent = $tokens['accent'] ?? '#B08D57';

        $address = setting('institution.address') ?? '';
        $email = setting('institution.email') ?? '';
        $phone = setting('institution.phone') ?? '';

        $applicantName = $application->user?->name
            ?? trim(
                $application->first_name . ' ' .
                $application->middle_name . ' ' .
                $application->last_name
            );

        $programme = $application->programme?->name ?? 'Programme';
        $faculty = $application->programme?->faculty?->name;
        $department = $application->programme?->department?->name;

        $submittedDate = $application->submitted_at
            ? $application->submitted_at->format('d F Y')
            : now()->format('d F Y');
    @endphp

    <title>
        Application Acknowledgement —
        {{ $application->application_number }}
    </title>

    <style>
        @page {
            size: A4;
            margin: 18mm 18mm 20mm 18mm;
        }

        :root {
            --primary: {{ $primary }};
            --secondary: {{ $secondary }};
            --accent: {{ $accent }};
            --text: #222;
            --muted: #666;
            --border: #dfe3e8;
            --surface: #f7f8fa;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Georgia, "Times New Roman", serif;
            color: var(--text);
            background: #fff;
            font-size: 11.5pt;
            line-height: 1.65;
        }

        .document {
            max-width: 820px;
            margin: 0 auto;
        }

        /* -----------------------------------------
           SCREEN / PRINT ACTIONS
        ----------------------------------------- */

        .print-actions {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 18px;
        }

        .print-button {
            border: 0;
            border-radius: 5px;
            padding: 9px 16px;
            background: var(--primary);
            color: #fff;
            font-family: Arial, sans-serif;
            font-size: 13px;
            cursor: pointer;
        }

        /* -----------------------------------------
           UNIVERSITY HEADER
        ----------------------------------------- */

        .letterhead {
            border-bottom: 3px solid var(--primary);
            padding-bottom: 14px;
            margin-bottom: 34px;
        }

        .letterhead-inner {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .logo {
            width: 72px;
            height: 72px;
            object-fit: contain;
        }

        .institution {
            flex: 1;
        }

        .institution-name {
            margin: 0;
            color: var(--primary);
            font-family: Arial, Helvetica, sans-serif;
            font-size: 21px;
            font-weight: 700;
            line-height: 1.2;
            letter-spacing: .15px;
        }

        .institution-short {
            margin-top: 3px;
            color: var(--secondary);
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 1.4px;
            text-transform: uppercase;
        }

        .institution-contact {
            margin-top: 7px;
            color: var(--muted);
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9.5px;
            line-height: 1.5;
        }

        /* -----------------------------------------
           DOCUMENT META
        ----------------------------------------- */

        .document-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 28px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: var(--muted);
        }

        .reference {
            font-weight: 700;
            color: var(--secondary);
        }

        /* -----------------------------------------
           TITLE
        ----------------------------------------- */

        .document-title {
            text-align: center;
            margin-bottom: 32px;
        }

        .document-title h1 {
            margin: 0;
            color: var(--primary);
            font-family: Arial, Helvetica, sans-serif;
            font-size: 18px;
            font-weight: 700;
            letter-spacing: .5px;
            text-transform: uppercase;
        }

        .title-rule {
            width: 54px;
            height: 2px;
            margin: 10px auto 0;
            background: var(--accent);
        }

        /* -----------------------------------------
           RECIPIENT
        ----------------------------------------- */

        .recipient {
            margin-bottom: 24px;
        }

        .recipient-name {
            margin: 0;
            font-weight: 700;
        }

        .recipient-line {
            margin: 1px 0;
        }

        /* -----------------------------------------
           BODY
        ----------------------------------------- */

        .subject {
            margin: 24px 0 18px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            font-weight: 700;
            color: var(--secondary);
            text-transform: uppercase;
        }

        .body-copy {
            text-align: justify;
        }

        .body-copy p {
            margin: 0 0 15px;
        }

        .application-summary {
            margin: 24px 0;
            padding: 16px 18px;
            border-left: 3px solid var(--accent);
            background: var(--surface);
            font-family: Arial, Helvetica, sans-serif;
        }

        .application-summary table {
            width: 100%;
            border-collapse: collapse;
        }

        .application-summary td {
            padding: 5px 0;
            vertical-align: top;
        }

        .application-summary td:first-child {
            width: 34%;
            color: var(--muted);
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .3px;
        }

        .application-summary td:last-child {
            font-size: 11px;
            font-weight: 600;
            color: var(--secondary);
        }

        /* -----------------------------------------
           IMPORTANT NOTICE
        ----------------------------------------- */

        .notice {
            margin: 25px 0;
            padding: 13px 16px;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10.5px;
            line-height: 1.6;
        }

        .notice strong {
            color: var(--secondary);
        }

        /* -----------------------------------------
           CLOSING
        ----------------------------------------- */

        .closing {
            margin-top: 28px;
        }

        .signature {
            margin-top: 36px;
        }

        .signature-line {
            width: 190px;
            border-top: 1px solid #555;
            margin-bottom: 7px;
        }

        .signature-name {
            font-weight: 700;
        }

        .signature-title {
            color: var(--muted);
            font-size: 10.5pt;
        }

        /* -----------------------------------------
           FOOTER
        ----------------------------------------- */

        .footer {
            margin-top: 42px;
            padding-top: 10px;
            border-top: 1px solid var(--border);
            text-align: center;
            color: var(--muted);
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.5px;
            line-height: 1.5;
        }

        /* -----------------------------------------
           PRINT
        ----------------------------------------- */

        @media print {

            body {
                background: #fff !important;
                color: var(--text) !important;

                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .print-actions {
                display: none !important;
            }

            .document {
                max-width: none;
                width: 100%;
            }

            .letterhead,
            .application-summary,
            .notice,
            .title-rule {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .letterhead {
                break-inside: avoid;
            }

            .application-summary,
            .notice {
                break-inside: avoid;
            }

            .signature {
                break-inside: avoid;
            }

            a {
                color: inherit !important;
                text-decoration: none !important;
            }
        }
    </style>
</head>

<body>

<div class="document">

    {{-- Screen-only action --}}
    <div class="print-actions">
        <button
            type="button"
            class="print-button"
            onclick="window.print()"
        >
            Print / Save as PDF
        </button>
    </div>

    {{-- University Letterhead --}}
    <header class="letterhead">
        <div class="letterhead-inner">

            @if($logoUrl)
                <img
                    src="{{ $logoUrl }}"
                    class="logo"
                    alt="{{ $institutionName }}"
                >
            @endif

            <div class="institution">

                <h2 class="institution-name">
                    {{ $institutionName }}
                </h2>

                @if($shortName)
                    <div class="institution-short">
                        {{ $shortName }}
                    </div>
                @endif

                <div class="institution-contact">
                    {{ $address }}

                    @if($email)
                        &nbsp; | &nbsp; {{ $email }}
                    @endif

                    @if($phone)
                        &nbsp; | &nbsp; {{ $phone }}
                    @endif
                </div>

            </div>
        </div>
    </header>

    {{-- Metadata --}}
    <div class="document-meta">
        <div>
            <span class="reference">
                Ref: {{ $application->application_number }}
            </span>
        </div>

        <div>
            {{ $submittedDate }}
        </div>
    </div>

    {{-- Title --}}
    <div class="document-title">
        <h1>Application Acknowledgement</h1>
        <div class="title-rule"></div>
    </div>

    {{-- Applicant --}}
    <div class="recipient">
        <p class="recipient-name">
            {{ $applicantName }}
        </p>

        @if($application->user?->email)
            <p class="recipient-line">
                {{ $application->user->email }}
            </p>
        @endif

        @if($application->user?->phone)
            <p class="recipient-line">
                {{ $application->user->phone }}
            </p>
        @endif
    </div>

    {{-- Subject --}}
    <div class="subject">
        Subject: Acknowledgement of Application
    </div>

    {{-- Letter --}}
    <div class="body-copy">

        <p>
            Dear {{ $application->first_name ?? $applicantName }},
        </p>

        <p>
            We are pleased to acknowledge the successful submission of your
            application to <strong>{{ $institutionName }}</strong>.
            Your application has been received and recorded in our admissions
            system under the reference number
            <strong>{{ $application->application_number }}</strong>.
        </p>

        <p>
            Your application is currently being processed in accordance with
            the University's admissions procedures. The information and
            supporting documents submitted with your application will be
            reviewed by the appropriate academic and admissions authorities.
        </p>

        {{-- Application Summary --}}
        <div class="application-summary">

            <table>
                <tr>
                    <td>Application Number</td>
                    <td>{{ $application->application_number }}</td>
                </tr>

                <tr>
                    <td>Programme</td>
                    <td>{{ $programme }}</td>
                </tr>

                @if($faculty)
                    <tr>
                        <td>Faculty</td>
                        <td>{{ $faculty }}</td>
                    </tr>
                @endif

                @if($department)
                    <tr>
                        <td>Department</td>
                        <td>{{ $department }}</td>
                    </tr>
                @endif

                <tr>
                    <td>Application Date</td>
                    <td>{{ $submittedDate }}</td>
                </tr>

                <tr>
                    <td>Application Status</td>
                    <td>
                        {{ str_replace('_', ' ', ucfirst(strtolower($application->application_status))) }}
                    </td>
                </tr>
            </table>

        </div>

        <p>
            Please retain this acknowledgement and your application number for
            future reference. You may use the University's admissions portal
            to monitor the progress of your application and to respond to any
            further requirements communicated by the Admissions Office.
        </p>

        {{-- Critical legal/academic distinction --}}
        <div class="notice">
            <strong>Important:</strong>
            This document confirms receipt and successful processing of your
            application. It is <strong>not an offer of admission</strong> and
            should not be interpreted as confirmation that admission has been
            granted. An official admission offer will be issued separately
            if your application is approved by the appropriate University
            authority.
        </div>

        <p>
            We appreciate your interest in
            <strong>{{ $institutionName }}</strong> and wish you success
            throughout the admissions process.
        </p>

    </div>

    {{-- Closing --}}
    <div class="closing">

        <p>
            Yours faithfully,
        </p>

        <div class="signature">

            <div class="signature-line"></div>

            <div class="signature-name">
                Admissions Office
            </div>

            <div class="signature-title">
                For: {{ $institutionName }}
            </div>

        </div>

    </div>

    {{-- Footer --}}
    <footer class="footer">
        This is an electronically generated application acknowledgement.
        Please retain this document together with your application records.
        <br>
        {{ $institutionName }}
    </footer>

</div>

</body>
</html>