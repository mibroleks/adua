{{--
|--------------------------------------------------------------------------
| Ygrace Tech — Admission Decision Hero
|--------------------------------------------------------------------------
| File: resources/views/filament/widgets/admission-decision-hero-widget.blade.php
|
| Purpose:
| Premium decision-focused identity and outcome summary for the
| Admission Decision View page.
|
| Architecture:
| - Application processing decision remains separate.
| - Institutional admission outcome remains separate.
| - Referral attribution is read from the original Application.
| - Admission letter issuance is not inferred from the outcome.
| - Presentation only; no record mutation.
|--------------------------------------------------------------------------
--}}

@php
    $record = $record?->fresh();

    $application = $record?->application;

    $applicant = $application?->user;

    $programme = $application?->programme;

    /*
    |--------------------------------------------------------------------------
    | Applicant Information
    |--------------------------------------------------------------------------
    */

    $applicantName = $applicant?->name
        ?: 'Applicant information unavailable';

    $applicantEmail = $applicant?->email;

    $applicantPhone = $applicant?->phone;

    $applicationNumber = $application?->application_number
        ?: 'Reference unavailable';

    $programmeName = $programme?->name
        ?: 'Programme unavailable';

    $programmeCode = $programme?->code;

    /*
    |--------------------------------------------------------------------------
    | Referral Attribution
    |--------------------------------------------------------------------------
    |
    | Referral attribution belongs to the original Application.
    | Do not derive or modify referral attribution from the
    | AdmissionDecision record.
    |
    */

    $referralPartner = $application?->referralPartner;

    $referralPartnerId = $application?->referral_partner_id;

    $referralPartnerName = $referralPartner?->name;

    $hasReferral = filled($referralPartnerId);

    $referralDisplayName = $hasReferral
        ? ($referralPartnerName ?: 'Referral Partner #' . $referralPartnerId)
        : 'Direct Application';

    $referralDescription = $hasReferral
        ? 'Referred application'
        : 'No referral partner recorded';

    /*
    |--------------------------------------------------------------------------
    | Application Processing Decision
    |--------------------------------------------------------------------------
    */

    $applicationDecision = strtoupper(
        (string) ($record?->decision ?? 'PENDING')
    );

    $applicationDecisionLabel = match ($applicationDecision) {
        'APPROVED' => 'Approved',
        'REJECTED' => 'Rejected',
        default => str($applicationDecision)->replace('_', ' ')->title()->toString(),
    };

    $applicationDecisionClass = match ($applicationDecision) {
        'APPROVED' => 'approved',
        'REJECTED' => 'rejected',
        default => 'neutral',
    };

    /*
    |--------------------------------------------------------------------------
    | Institutional Admission Outcome
    |--------------------------------------------------------------------------
    */

    $admissionStatus = strtoupper(
        (string) ($record?->admission_status ?? 'PENDING')
    );

    $admissionStatusLabel = match ($admissionStatus) {
        'ADMITTED' => 'Admitted',
        'NOT_ADMITTED' => 'Not Admitted',
        'WAITLISTED' => 'Waitlisted',
        'DEFERRED' => 'Deferred',
        default => 'Pending',
    };

    $admissionStatusClass = match ($admissionStatus) {
        'ADMITTED' => 'admitted',
        'NOT_ADMITTED' => 'not-admitted',
        'WAITLISTED' => 'waitlisted',
        'DEFERRED' => 'deferred',
        default => 'pending',
    };

    $hasAdmissionOutcome = $admissionStatus !== 'PENDING';

    /*
    |--------------------------------------------------------------------------
    | Decision Officers and Dates
    |--------------------------------------------------------------------------
    */

    $applicationOfficer = $record?->officer?->name
        ?: 'Not recorded';

    $admissionOfficer = $record?->admissionOfficer?->name
        ?: 'Not recorded';

    $applicationDecisionDate = $record?->decided_at
        ? \Illuminate\Support\Carbon::parse($record->decided_at)
            ->format('d M Y, h:i A')
        : 'Not recorded';

    $admissionDecisionDate = $record?->admission_decided_at
        ? \Illuminate\Support\Carbon::parse($record->admission_decided_at)
            ->format('d M Y, h:i A')
        : 'Awaiting outcome';

    /*
    |--------------------------------------------------------------------------
    | Applicant Initials
    |--------------------------------------------------------------------------
    */

    $initials = collect(
        preg_split('/\s+/', trim($applicantName))
    )
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_substr($part, 0, 1))
        ->implode('');

    $initials = mb_strtoupper($initials ?: 'AD');

    /*
    |--------------------------------------------------------------------------
    | Workflow State
    |--------------------------------------------------------------------------
    */

    $applicationProcessed = in_array(
        $applicationDecision,
        ['APPROVED', 'REJECTED'],
        true
    );

    $workflowStage = $hasAdmissionOutcome ? 2 : ($applicationProcessed ? 2 : 1);

    $workflowDescription = match (true) {
        $admissionStatus === 'ADMITTED' =>
            'The institution has recorded an admission outcome for this applicant.',

        $admissionStatus === 'NOT_ADMITTED' =>
            'The institution has recorded a decision not to admit this applicant.',

        $admissionStatus === 'WAITLISTED' =>
            'The applicant has been placed on the institutional admission waiting list.',

        $admissionStatus === 'DEFERRED' =>
            'The institutional admission decision has been deferred.',

        $applicationDecision === 'APPROVED' =>
            'Application processing is approved. The institutional admission outcome is still pending.',

        $applicationDecision === 'REJECTED' =>
            'Application processing is rejected. No institutional admission outcome should be inferred.',

        default =>
            'The application processing decision has not yet been finalised.',
    };

    $noticeClass = match (true) {
        $admissionStatus === 'ADMITTED' => 'admitted',
        $admissionStatus === 'NOT_ADMITTED' => 'not-admitted',
        $admissionStatus === 'WAITLISTED' => 'waitlisted',
        $admissionStatus === 'DEFERRED' => 'deferred',
        $applicationDecision === 'APPROVED' => 'awaiting',
        $applicationDecision === 'REJECTED' => 'not-admitted',
        default => 'pending',
    };
@endphp

@if ($record)
    <section class="admission-decision-hero" aria-label="Admission decision summary">

        {{-- =========================================================
             MAIN IDENTITY HEADER
        ========================================================== --}}

        <div class="admission-decision-hero__main">

            <div class="admission-decision-hero__identity">

                <div class="admission-decision-hero__avatar" aria-hidden="true">
                    {{ $initials }}
                </div>

                <div class="admission-decision-hero__identity-content">

                    <div class="admission-decision-hero__eyebrow">
                        <span class="admission-decision-hero__eyebrow-icon">
                            <x-filament::icon
                                icon="heroicon-o-academic-cap"
                            />
                        </span>

                        INSTITUTIONAL ADMISSION RECORD
                    </div>

                    <h2 class="admission-decision-hero__applicant-name">
                        {{ $applicantName }}
                    </h2>

                    <div class="admission-decision-hero__contact">

                        @if ($applicantEmail)
                            <span>
                                <x-filament::icon icon="heroicon-o-envelope" />
                                {{ $applicantEmail }}
                            </span>
                        @endif

                        @if ($applicantPhone)
                            <span>
                                <x-filament::icon icon="heroicon-o-phone" />
                                {{ $applicantPhone }}
                            </span>
                        @endif

                    </div>

                </div>

            </div>

            <div class="admission-decision-hero__status-area">

                <span class="admission-decision-hero__status-label">
                    INSTITUTIONAL ADMISSION OUTCOME
                </span>

                <span
                    class="admission-decision-hero__status admission-decision-hero__status--{{ $admissionStatusClass }}"
                >
                    <span class="admission-decision-hero__status-dot"></span>
                    {{ $admissionStatusLabel }}
                </span>

                <span class="admission-decision-hero__secondary-label">
                    Application processing:

                    <strong
                        class="admission-decision-hero__secondary-status admission-decision-hero__secondary-status--{{ $applicationDecisionClass }}"
                    >
                        {{ $applicationDecisionLabel }}
                    </strong>
                </span>

            </div>

        </div>


        {{-- =========================================================
             DECISION METADATA
        ========================================================== --}}

        <div class="admission-decision-hero__metadata">

            <div class="admission-decision-hero__meta-item admission-decision-hero__meta-item--primary">

                <span class="admission-decision-hero__meta-label">
                    APPLICATION REFERENCE
                </span>

                <strong class="admission-decision-hero__meta-value">
                    {{ $applicationNumber }}
                </strong>

                <span class="admission-decision-hero__meta-description">
                    Original application record
                </span>

            </div>


            <div class="admission-decision-hero__meta-item">

                <span class="admission-decision-hero__meta-label">
                    PROGRAMME
                </span>

                <strong class="admission-decision-hero__meta-value">
                    {{ $programmeName }}
                </strong>

                @if ($programmeCode)
                    <span class="admission-decision-hero__meta-description">
                        {{ $programmeCode }}
                    </span>
                @endif

            </div>


            <div class="admission-decision-hero__meta-item">

                <span class="admission-decision-hero__meta-label">
                    REFERRAL PARTNER
                </span>

                <strong class="admission-decision-hero__meta-value">
                    {{ $referralDisplayName }}
                </strong>

                <span class="admission-decision-hero__meta-description">
                    {{ $referralDescription }}
                </span>

            </div>


            <div class="admission-decision-hero__meta-item">

                <span class="admission-decision-hero__meta-label">
                    APPLICATION DECISION
                </span>

                <strong class="admission-decision-hero__meta-value">
                    {{ $applicationDecisionLabel }}
                </strong>

                <span class="admission-decision-hero__meta-description">
                    {{ $applicationDecisionDate }}
                </span>

            </div>


            <div class="admission-decision-hero__meta-item">

                <span class="admission-decision-hero__meta-label">
                    ADMISSION DECISION
                </span>

                <strong class="admission-decision-hero__meta-value">
                    {{ $admissionStatusLabel }}
                </strong>

                <span class="admission-decision-hero__meta-description">
                    {{ $admissionDecisionDate }}
                </span>

            </div>

        </div>


        {{-- =========================================================
             DECISION WORKFLOW
        ========================================================== --}}

        <div class="admission-decision-hero__progress">

            <div class="admission-decision-hero__progress-heading">

                <div>
                    <strong class="admission-decision-hero__progress-title">
                        Admission decision workflow
                    </strong>

                    <span class="admission-decision-hero__progress-description">
                        Application processing and institutional admission are separate decisions.
                    </span>
                </div>

                <span class="admission-decision-hero__progress-stage">
                    {{ $hasAdmissionOutcome ? '2 OF 2 STAGES RECORDED' : ($applicationProcessed ? '1 OF 2 STAGES RECORDED' : 'IN PROGRESS') }}
                </span>

            </div>


            <div class="admission-decision-hero__steps">

                {{-- Application processing --}}

                <div class="admission-decision-hero__step {{ $applicationProcessed ? 'is-complete' : 'is-current' }}">

                    <span class="admission-decision-hero__step-marker">

                        @if ($applicationProcessed)
                            <x-filament::icon icon="heroicon-o-check" />
                        @else
                            1
                        @endif

                    </span>

                    <span class="admission-decision-hero__step-label">
                        Application processing
                    </span>

                </div>


                <span class="admission-decision-hero__step-line {{ $hasAdmissionOutcome ? 'is-complete' : '' }}"></span>


                {{-- Institutional admission outcome --}}

                <div class="admission-decision-hero__step {{ $hasAdmissionOutcome ? 'is-complete' : 'is-current' }}">

                    <span class="admission-decision-hero__step-marker">

                        @if ($hasAdmissionOutcome)
                            <x-filament::icon icon="heroicon-o-check" />
                        @else
                            2
                        @endif

                    </span>

                    <span class="admission-decision-hero__step-label">
                        Institutional admission outcome
                    </span>

                </div>

            </div>


            {{-- Contextual workflow notice --}}

            <div class="admission-decision-hero__notice admission-decision-hero__notice--{{ $noticeClass }}">

                <x-filament::icon
                    :icon="$hasAdmissionOutcome
                        ? 'heroicon-o-information-circle'
                        : 'heroicon-o-clock'"
                />

                <span>
                    {{ $workflowDescription }}
                </span>

            </div>

        </div>


        {{-- =========================================================
             DECISION OFFICER INFORMATION
        ========================================================== --}}

        <div class="admission-decision-hero__officers">

            <div class="admission-decision-hero__officer">

                <span class="admission-decision-hero__officer-icon">
                    <x-filament::icon icon="heroicon-o-clipboard-document-check" />
                </span>

                <div>
                    <span class="admission-decision-hero__officer-label">
                        APPLICATION DECISION OFFICER
                    </span>

                    <strong>
                        {{ $applicationOfficer }}
                    </strong>
                </div>

            </div>


            <div class="admission-decision-hero__officer">

                <span class="admission-decision-hero__officer-icon">
                    <x-filament::icon icon="heroicon-o-academic-cap" />
                </span>

                <div>
                    <span class="admission-decision-hero__officer-label">
                        ADMISSION DECISION OFFICER
                    </span>

                    <strong>
                        {{ $admissionOfficer }}
                    </strong>
                </div>

            </div>

        </div>

    </section>

@else

    <div class="admission-decision-hero__empty">
        Admission decision information is currently unavailable.
    </div>

@endif
