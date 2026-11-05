{{--
|--------------------------------------------------------------------------
| Official Admission Offer
|--------------------------------------------------------------------------
| File:
| resources/views/official_admission_offer.blade.php
|
| Company: Ygrace Tech
| Author: Ibrahim Olalekan
|
| Purpose:
| Displays the official institutional admission offer issued after an
| applicant has been formally admitted.
|
| Important:
| - This is separate from the Application Outcome Letter.
| - All offer content is rendered from the immutable content snapshot.
| - The original issued letter reference and version are preserved.
| - No live programme or applicant data is used to rewrite the offer.
|--------------------------------------------------------------------------
--}}

@extends('layouts.app')

@php
    /*
    |--------------------------------------------------------------------------
    | Immutable offer snapshot
    |--------------------------------------------------------------------------
    */

    $document = data_get($snapshot, 'document', []);
    $institution = data_get($snapshot, 'institution', []);
    $applicant = data_get($snapshot, 'applicant', []);
    $application = data_get($snapshot, 'application', []);
    $programme = data_get($snapshot, 'programme', []);
    $admission = data_get($snapshot, 'admission', []);
    $signatory = data_get($snapshot, 'signatory', []);

    /*
    |--------------------------------------------------------------------------
    | Document information
    |--------------------------------------------------------------------------
    |
    | The snapshot is the primary source. The AdmissionLetter record is
    | used only as a fallback for legacy or incomplete snapshots.
    */

    $letterNumber = data_get($document, 'letter_number')
        ?: $letter->letter_number;

    $letterVersion = data_get($document, 'version')
        ?? $letter->version
        ?? 1;

    $issuedAt = data_get($document, 'issued_at')
        ?: $letter->issued_at;

    /*
    |--------------------------------------------------------------------------
    | Institutional branding
    |--------------------------------------------------------------------------
    */

    $institutionName = data_get($institution, 'name') ?: 'Institution';
    $institutionShortName = data_get($institution, 'short_name');
    $institutionAddress = data_get($institution, 'address');
    $institutionEmail = data_get($institution, 'email');
    $institutionPhone = data_get($institution, 'phone');
    $institutionWebsite = data_get($institution, 'website');
    $institutionLogo = data_get($institution, 'logo');

    $logoUrl = null;

    if (filled($institutionLogo)) {
        if (
            \Illuminate\Support\Str::startsWith(
                $institutionLogo,
                ['http://', 'https://']
            )
        ) {
            $logoUrl = $institutionLogo;
        } else {
            $logoUrl = \Illuminate\Support\Facades\Storage::disk('public')
                ->url(ltrim($institutionLogo, '/'));
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Applicant information
    |--------------------------------------------------------------------------
    */

    $applicantName = data_get($applicant, 'name') ?: 'Applicant';
    $applicantEmail = data_get($applicant, 'email');

    /*
    |--------------------------------------------------------------------------
    | Application information
    |--------------------------------------------------------------------------
    |
    | application_number is stored under the application snapshot section,
    | not under applicant.
    */

    $applicationNumber = data_get($application, 'application_number');

    /*
    |--------------------------------------------------------------------------
    | Programme information
    |--------------------------------------------------------------------------
    */

    $programmeName = data_get($programme, 'name');
    $programmeCode = data_get($programme, 'code');
    $degreeType = data_get($programme, 'degree_type');
    $programmeDuration = data_get($programme, 'duration');
    $departmentName = data_get($programme, 'department');
    $facultyName = data_get($programme, 'faculty');

    /*
    |--------------------------------------------------------------------------
    | Admission information
    |--------------------------------------------------------------------------
    */

    $admissionRemarks = data_get($admission, 'remarks');
    $admissionDate = data_get($admission, 'decided_at');

    /*
    |--------------------------------------------------------------------------
    | Signatory
    |--------------------------------------------------------------------------
    */

    $signatoryName = data_get($signatory, 'name');
    $signatoryDesignation = data_get(
        $signatory,
        'designation'
    ) ?: 'Admissions Officer';

    /*
    |--------------------------------------------------------------------------
    | Display formatting
    |--------------------------------------------------------------------------
    */

    $formattedIssueDate = $issuedAt
        ? \Illuminate\Support\Carbon::parse($issuedAt)->format('F j, Y')
        : 'Date not available';

    $formattedAdmissionDate = $admissionDate
        ? \Illuminate\Support\Carbon::parse($admissionDate)->format('F j, Y')
        : null;
@endphp

@section('title', 'Official Admission Offer')

@section('content')
<main class="official-offer-page">

    <div class="official-offer-toolbar">
        <div class="official-offer-toolbar__context">
            <span class="official-offer-toolbar__eyebrow">Admissions Portal</span>

            <span class="official-offer-toolbar__description">
                Your official institutional admission document
            </span>
        </div>

        <div class="official-offer-toolbar__actions">

            <a
                href="{{ route('dashboard') }}"
                class="official-offer-button official-offer-button--secondary"
            >
                <span aria-hidden="true">&larr;</span>
                Dashboard
            </a>

            <button
                type="button"
                class="official-offer-button official-offer-button--primary"
                onclick="window.print()"
            >
                <span aria-hidden="true">&#128438;</span>
                Print offer
            </button>

        </div>
    </div>

    <article class="official-offer-document">

        {{-- Institutional letterhead --}}
        <header class="official-offer-letterhead">

            <div class="official-offer-brand">

                @if ($logoUrl)
                    <img
                        src="{{ $logoUrl }}"
                        alt="{{ $institutionName }} logo"
                        class="official-offer-brand__logo"
                    >
                @else
                    <div
                        class="official-offer-brand__monogram"
                        aria-hidden="true"
                    >
                        {{ \Illuminate\Support\Str::upper(
                            \Illuminate\Support\Str::substr(
                                $institutionShortName ?: $institutionName,
                                0,
                                2
                            )
                        ) }}
                    </div>
                @endif

                <div class="official-offer-brand__identity">

                    <p class="official-offer-brand__name">
                        {{ $institutionName }}
                    </p>

                    @if ($institutionShortName)
                        <p class="official-offer-brand__short-name">
                            {{ $institutionShortName }}
                        </p>
                    @endif

                    @if ($institutionAddress)
                        <p class="official-offer-brand__contact">
                            {{ $institutionAddress }}
                        </p>
                    @endif

                    <div class="official-offer-brand__contact-line">

                        @if ($institutionEmail)
                            <span>{{ $institutionEmail }}</span>
                        @endif

                        @if ($institutionPhone)
                            <span>{{ $institutionPhone }}</span>
                        @endif

                        @if ($institutionWebsite)
                            <span>{{ $institutionWebsite }}</span>
                        @endif

                    </div>
                </div>
            </div>

            <div class="official-offer-letterhead__rule"></div>

            <div class="official-offer-document-heading">

                <div>
                    <span class="official-offer-document-heading__eyebrow">
                        Institutional Correspondence
                    </span>

                    <h1>Official Admission Offer</h1>

                    <p>
                        Formal notification of institutional admission
                    </p>
                </div>

                <div class="official-offer-document-heading__status">

                    <span class="official-offer-status">
                        <span aria-hidden="true"></span>
                        ADMITTED
                    </span>

                    <small>Official admission outcome</small>

                </div>
            </div>

        </header>

        {{-- Document reference --}}
        <section
            class="official-offer-reference"
            aria-label="Document reference"
        >

            <div class="official-offer-reference__item">
                <span>Offer reference</span>
                <strong>{{ $letterNumber }}</strong>
            </div>

            <div class="official-offer-reference__item">
                <span>Issue date</span>
                <strong>{{ $formattedIssueDate }}</strong>
            </div>

            <div class="official-offer-reference__item">
                <span>Document version</span>
                <strong>{{ $letterVersion }}</strong>
            </div>

        </section>

        {{-- Main letter --}}
        <section class="official-offer-body">

            <div class="official-offer-recipient">

                <span class="official-offer-section-label">
                    Issued to
                </span>

                <h2>{{ $applicantName }}</h2>

                @if ($applicantEmail)
                    <p>{{ $applicantEmail }}</p>
                @endif

                @if ($applicationNumber)
                    <p>
                        Application number:
                        <strong>{{ $applicationNumber }}</strong>
                    </p>
                @endif

            </div>

            <div class="official-offer-salutation">
                Dear {{ $applicantName }},
            </div>

            <div class="official-offer-message">

                <h2>Congratulations on your admission.</h2>

                <p>
                    We are pleased to formally inform you that you have been
                    admitted to <strong>{{ $institutionName }}</strong>.
                </p>

                <p>
                    Following the institution's admission process, your
                    application has received the institutional admission
                    outcome of <strong>ADMITTED</strong>.
                </p>

                <p>
                    Your admission is for the academic programme stated
                    below. Please retain this letter as your official
                    admission offer document.
                </p>

            </div>

            {{-- Programme details --}}
            <section class="official-offer-programme">

                <div class="official-offer-programme__heading">

                    <span class="official-offer-section-label">
                        Admission details
                    </span>

                    <h3>Programme of admission</h3>

                </div>

                <div class="official-offer-programme__grid">

                    <div class="official-offer-detail official-offer-detail--featured">
                        <span>Programme</span>
                        <strong>
                            {{ $programmeName ?: 'Not specified' }}
                        </strong>
                    </div>

                    @if ($programmeCode)
                        <div class="official-offer-detail">
                            <span>Programme code</span>
                            <strong>{{ $programmeCode }}</strong>
                        </div>
                    @endif

                    @if ($degreeType)
                        <div class="official-offer-detail">
                            <span>Qualification</span>
                            <strong>{{ $degreeType }}</strong>
                        </div>
                    @endif

                    @if ($programmeDuration)
                        <div class="official-offer-detail">
                            <span>Programme duration</span>
                            <strong>{{ $programmeDuration }}</strong>
                        </div>
                    @endif

                    @if ($facultyName)
                        <div class="official-offer-detail">
                            <span>Faculty</span>
                            <strong>{{ $facultyName }}</strong>
                        </div>
                    @endif

                    @if ($departmentName)
                        <div class="official-offer-detail">
                            <span>Department</span>
                            <strong>{{ $departmentName }}</strong>
                        </div>
                    @endif

                    @if ($formattedAdmissionDate)
                        <div class="official-offer-detail">
                            <span>Admission decision date</span>
                            <strong>{{ $formattedAdmissionDate }}</strong>
                        </div>
                    @endif

                </div>
            </section>

            {{-- Institutional remarks --}}
            @if (filled($admissionRemarks))
                <section class="official-offer-remarks">

                    <span class="official-offer-section-label">
                        Institutional remarks
                    </span>

                    <p>{{ $admissionRemarks }}</p>

                </section>
            @endif

            {{-- Closing --}}
            <div class="official-offer-closing">

                <p>
                    On behalf of {{ $institutionName }}, we congratulate you
                    on this achievement and look forward to welcoming you
                    to our academic community.
                </p>

                <p>
                    Please retain this document for your admission records
                    and future institutional reference.
                </p>

                <p class="official-offer-closing__salutation">
                    Yours faithfully,
                </p>

            </div>

            {{-- Signatory --}}
            <section class="official-offer-signatory">

                <div class="official-offer-signatory__signature-space">
                    <span> </span>
                </div>

                <strong>
                    {{ $signatoryName ?: 'Admissions Office' }}
                </strong>

                <span>{{ $signatoryDesignation }}</span>

                <span>{{ $institutionName }}</span>

            </section>

        </section>

        {{-- Footer --}}
        <footer class="official-offer-footer">

            <div class="official-offer-footer__rule"></div>

            <p>
                This is an official admission offer issued by
                {{ $institutionName }}.
            </p>

            <div class="official-offer-footer__meta">

                <span>Reference: {{ $letterNumber }}</span>

                <span>Version {{ $letterVersion }}</span>

            </div>

        </footer>

    </article>

    <div class="official-offer-security-note">

        <span aria-hidden="true">&#10003;</span>

        <p>
            This document is displayed from the institution's recorded
            admission offer. Its original reference and issued content
            are preserved in the admissions record.
        </p>

    </div>

</main>
@endsection
