@php
    use App\Models\Application;
    use Illuminate\Support\Str;

    /*
    |--------------------------------------------------------------------------
    | Refresh Application Record
    |--------------------------------------------------------------------------
    |
    | Always use the latest persisted application status when rendering
    | the hero widget. This prevents stale status information from
    | being displayed after workflow operations.
    |
    */

    $record = $record->fresh() ?? $record;

    $record->loadMissing([
        'user',
        'programme',
        'referralPartner',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Applicant and Programme Information
    |--------------------------------------------------------------------------
    */

    $applicantName = data_get($record, 'user.name')
        ?: 'Applicant information unavailable';

    $applicantEmail = data_get($record, 'user.email');
    $applicantPhone = data_get($record, 'user.phone');

    $programmeName = data_get($record, 'programme.name')
        ?: 'Programme not specified';

    $programmeCode = data_get($record, 'programme.code');

    $applicationNumber = $record->application_number
        ?: 'Application number unavailable';

    /*
    |--------------------------------------------------------------------------
    | Application Status
    |--------------------------------------------------------------------------
    */

    $status = $record->application_status;

    $statusLabel = $record->statusLabel();

    $statusTone = match ($status) {
        Application::STATUS_DRAFT => 'draft',
        Application::STATUS_SUBMITTED => 'submitted',
        Application::STATUS_UNDER_REVIEW => 'review',
        Application::STATUS_CORRECTION_REQUIRED => 'correction',
        Application::STATUS_APPROVED => 'approved',
        Application::STATUS_REJECTED => 'rejected',
        default => 'neutral',
    };

    /*
    |--------------------------------------------------------------------------
    | Workflow Stage
    |--------------------------------------------------------------------------
    |
    | 0 = Draft
    | 1 = Submitted
    | 2 = Review / Correction
    | 3 = Final Decision
    |
    */

    $workflowStage = match ($status) {
        Application::STATUS_SUBMITTED => 1,

        Application::STATUS_UNDER_REVIEW,
        Application::STATUS_CORRECTION_REQUIRED => 2,

        Application::STATUS_APPROVED,
        Application::STATUS_REJECTED => 3,

        default => 0,
    };

    /*
    |--------------------------------------------------------------------------
    | Workflow Descriptions
    |--------------------------------------------------------------------------
    */

    $workflowDescription = match ($status) {
        Application::STATUS_DRAFT =>
            'This application has not yet been submitted.',

        Application::STATUS_SUBMITTED =>
            'Your application has been submitted and is awaiting review by an admissions officer.',

        Application::STATUS_UNDER_REVIEW =>
            'Your application is currently being reviewed by an admissions officer.',

        Application::STATUS_CORRECTION_REQUIRED =>
            'Corrections have been requested. The application must be updated before the admission process can continue.',

        Application::STATUS_APPROVED =>
            'The application has successfully passed the admission decision stage.',

        Application::STATUS_REJECTED =>
            'The admission decision has been completed. This application was not approved.',

        default =>
            'The application workflow status is currently unavailable.',
    };

    /*
    |--------------------------------------------------------------------------
    | Workflow Stage Labels
    |--------------------------------------------------------------------------
    */

    $workflowStageLabel = match ($status) {
        Application::STATUS_DRAFT =>
            'Not submitted',

        Application::STATUS_SUBMITTED =>
            'Stage 1 of 3 · Submitted',

        Application::STATUS_UNDER_REVIEW =>
            'Stage 2 of 3 · Under Review',

        Application::STATUS_CORRECTION_REQUIRED =>
            'Action Required · Corrections',

        Application::STATUS_APPROVED =>
            'Completed · Approved',

        Application::STATUS_REJECTED =>
            'Completed · Rejected',

        default =>
            'Status unavailable',
    };

    /*
    |--------------------------------------------------------------------------
    | Submission Date
    |--------------------------------------------------------------------------
    */

    $submittedAt = $record->submitted_at
        ? \Illuminate\Support\Carbon::parse($record->submitted_at)
            ->format('d M Y, h:i A')
        : 'Not available';

    /*
    |--------------------------------------------------------------------------
    | Referral Attribution
    |--------------------------------------------------------------------------
    |
    | Display the recorded referral partner.
    | Do not recalculate or modify referral attribution.
    |
    */

    $referralPartner = $record->referralPartner;

    $referralPartnerName = $referralPartner
        ? (
            data_get($referralPartner, 'name')
                ?: 'Referral Partner #' . $record->referral_partner_id
        )
        : 'Direct Application';

    $referralDescription = $referralPartner
        ? 'Referred application'
        : 'No referral partner recorded';

    /*
    |--------------------------------------------------------------------------
    | Applicant Initials
    |--------------------------------------------------------------------------
    */

    $initials = collect(explode(' ', trim($applicantName)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => Str::upper(Str::substr($part, 0, 1)))
        ->implode('');

    /*
    |--------------------------------------------------------------------------
    | Workflow Step States
    |--------------------------------------------------------------------------
    |
    | Each step now has a distinct completed/current state.
    |
    */

    $isFinalDecision = in_array($status, [
        Application::STATUS_APPROVED,
        Application::STATUS_REJECTED,
    ], true);

    $submittedStepComplete = $workflowStage > 1;
    $submittedStepCurrent = $status === Application::STATUS_SUBMITTED;

    $reviewStepComplete = $isFinalDecision;
    $reviewStepCurrent = in_array($status, [
        Application::STATUS_UNDER_REVIEW,
        Application::STATUS_CORRECTION_REQUIRED,
    ], true);

    /*
     * A recorded approval or rejection completes the decision stage.
     * Previously this was permanently false, leaving the marker as 3.
     */
    $decisionStepComplete = $isFinalDecision;
    $decisionStepCurrent = false;

    $decisionStepLabel = match ($status) {
        Application::STATUS_APPROVED => 'Approved',
        Application::STATUS_REJECTED => 'Rejected',
        default => 'Decision',
    };

@endphp

<div class="application-hero-widget">

    {{-- Identity and status header --}}
    <div class="application-hero-widget__main">

        <div class="application-hero-widget__identity">

            <div class="application-hero-widget__avatar">
                {{ $initials ?: 'A' }}
            </div>

            <div class="application-hero-widget__identity-content">

                <div class="application-hero-widget__eyebrow">
                    <span class="application-hero-widget__eyebrow-icon">
                        <x-heroicon-o-academic-cap />
                    </span>

                    ADMISSION APPLICATION
                </div>

                <h2 class="application-hero-widget__applicant-name">
                    {{ $applicantName }}
                </h2>

                <div class="application-hero-widget__contact">

                    @if ($applicantEmail)
                        <span>
                            <x-heroicon-o-envelope />
                            {{ $applicantEmail }}
                        </span>
                    @endif

                    @if ($applicantPhone)
                        <span>
                            <x-heroicon-o-phone />
                            {{ $applicantPhone }}
                        </span>
                    @endif

                </div>

            </div>

        </div>

        <div class="application-hero-widget__status-area">

            <span class="application-hero-widget__status-label">
                CURRENT STATUS
            </span>

            <span class="application-hero-widget__status application-hero-widget__status--{{ $statusTone }}">
                <span class="application-hero-widget__status-dot"></span>
                {{ $statusLabel }}
            </span>

        </div>

    </div>


    {{-- Application metadata --}}
    <div class="application-hero-widget__metadata">

        <div class="application-hero-widget__meta-item application-hero-widget__meta-item--primary">

            <span class="application-hero-widget__meta-label">
                APPLICATION NUMBER
            </span>

            <strong class="application-hero-widget__meta-value">
                {{ $applicationNumber }}
            </strong>

        </div>

        <div class="application-hero-widget__meta-item">

            <span class="application-hero-widget__meta-label">
                PROGRAMME
            </span>

            <strong class="application-hero-widget__meta-value">
                {{ $programmeName }}
            </strong>

            @if ($programmeCode)
                <span class="application-hero-widget__meta-description">
                    {{ $programmeCode }}
                </span>
            @endif

        </div>

        <div class="application-hero-widget__meta-item">

            <span class="application-hero-widget__meta-label">
                SUBMISSION DATE
            </span>

            <strong class="application-hero-widget__meta-value">
                {{ $submittedAt }}
            </strong>

        </div>

        <div class="application-hero-widget__meta-item application-hero-widget__meta-item--referral">

            <span class="application-hero-widget__meta-label">
                REFERRAL PARTNER
            </span>

            <strong class="application-hero-widget__meta-value">
                {{ $referralPartnerName }}
            </strong>

            <span class="application-hero-widget__meta-description">
                {{ $referralDescription }}
            </span>

        </div>

    </div>


    {{-- Application workflow progress --}}
    <div class="application-hero-widget__progress">

        <div class="application-hero-widget__progress-heading">

            <div>
                <span class="application-hero-widget__progress-title">
                    Application workflow
                </span>

                <span class="application-hero-widget__progress-description">
                    {{ $workflowDescription }}
                </span>
            </div>

            <span class="application-hero-widget__progress-stage">
                {{ $workflowStageLabel }}
            </span>

        </div>


        <div class="application-hero-widget__steps">

            {{-- Step 1: Submitted --}}
            <div class="application-hero-widget__step
                {{ $submittedStepComplete ? 'is-complete' : '' }}
                {{ $submittedStepCurrent ? 'is-current' : '' }}">

                <span class="application-hero-widget__step-marker">

                    @if ($submittedStepComplete)
                        <x-heroicon-o-check />
                    @else
                        1
                    @endif

                </span>

                <span class="application-hero-widget__step-label">
                    Submitted
                </span>

            </div>


            <div class="application-hero-widget__step-line
                {{ $workflowStage >= 2 ? 'is-complete' : '' }}">
            </div>


            {{-- Step 2: Review --}}
            <div class="application-hero-widget__step
                {{ $reviewStepComplete ? 'is-complete' : '' }}
                {{ $reviewStepCurrent ? 'is-current' : '' }}">

                <span class="application-hero-widget__step-marker">

                    @if ($reviewStepComplete)
                        <x-heroicon-o-check />
                    @else
                        2
                    @endif

                </span>

                <span class="application-hero-widget__step-label">

                    @if ($status === Application::STATUS_CORRECTION_REQUIRED)
                        Corrections
                    @else
                        Review
                    @endif

                </span>

            </div>


            <div class="application-hero-widget__step-line
                {{ $isFinalDecision ? 'is-complete' : '' }}">
            </div>


            {{-- Step 3: Final Decision --}}
            <div class="application-hero-widget__step
                {{ $decisionStepComplete ? 'is-complete' : '' }}
                {{ $decisionStepCurrent ? 'is-current' : '' }}">

                <span class="application-hero-widget__step-marker">

                    @if ($decisionStepComplete)
                        <x-heroicon-o-check />
                    @else
                        3
                    @endif

                </span>

                <span class="application-hero-widget__step-label">
                    {{ $decisionStepLabel }}
                </span>

            </div>

        </div>


        {{-- Status-specific notices --}}
        @if ($status === Application::STATUS_CORRECTION_REQUIRED)

            <div class="application-hero-widget__notice application-hero-widget__notice--correction">

                <x-heroicon-o-information-circle />

                <span>
                    Corrections have been requested. The application must be updated and resubmitted before the admission process can continue.
                </span>

            </div>

        @elseif ($status === Application::STATUS_REJECTED)

            <div class="application-hero-widget__notice application-hero-widget__notice--rejected">

                <x-heroicon-o-exclamation-circle />

                <span>
                    A rejection decision has been recorded for this application.
                </span>

            </div>

        @elseif ($status === Application::STATUS_APPROVED)

            <div class="application-hero-widget__notice application-hero-widget__notice--approved">

                <x-heroicon-o-check-circle />

                <span>
                    An approval decision has been recorded for this application.
                </span>

            </div>

        @elseif ($status === Application::STATUS_DRAFT)

            <div class="application-hero-widget__notice application-hero-widget__notice--draft">

                <x-heroicon-o-pencil-square />

                <span>
                    This application is still a draft and has not entered the formal admission workflow.
                </span>

            </div>

        @endif

    </div>

</div>
