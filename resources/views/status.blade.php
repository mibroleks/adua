{{--  
Component: Application Status
File Path: resources/views/status.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Displays the current application lifecycle, payment state,
documents, admission decision and status history.

Important:
Application status and payment status are deliberately separated.

Canonical application lifecycle:
DRAFT → SUBMITTED → UNDER_REVIEW → APPROVED / REJECTED

Document lifecycle:
PENDING → VERIFIED → REJECTED

Payment lifecycle:
PENDING / SUCCESS / FAILED

Status: ✅ Production Ready
Version: 5.4 (timeline + decision integrated)
--}}

@extends('layouts.portal')

@section('title', 'Application Status')

@section('content')

<div class="admission-page admission-page--status">

    <div class="admission-atmosphere admission-atmosphere--left" aria-hidden="true"></div>

    <main class="admission-shell admission-shell--wide">

        {{-- Feedback --}}
        @if(session('status'))
            <x-alert variant="success" title="Success" :message="session('status')" />
        @endif

        @if($errors->any())
            <x-alert variant="danger" title="There were some problems">
                <ul class="mt-2 list-disc pl-5 text-xs">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        {{-- Page Header --}}
        <header class="admission-page-header">
            <div class="admission-eyebrow">
                <span class="admission-eyebrow__dot" aria-hidden="true"></span>
                Application
            </div>
            <h1 class="admission-page-title">Application Status</h1>
            <p class="admission-page-description">
                Follow your application journey and review everything submitted to the admissions office.
            </p>
        </header>

        {{-- Application Identity --}}
        <section class="status-identity" aria-labelledby="application-identity-title">
            <h2 id="application-identity-title" class="sr-only">Application information</h2>

            <div>
                <span class="status-identity__label">Application Number</span>
                <strong class="status-identity__number">{{ $application->application_number }}</strong>
            </div>

            <div class="status-identity__programme">
                <span>Programme</span>
                <strong>{{ $application->programme->name }}</strong>
                @if($application->programme->degree_type)
                    <small>{{ $application->programme->degree_type }}</small>
                @endif
            </div>

            @php
                $currentStatus = strtoupper((string) ($application->application_status ?? 'DRAFT'));
                $statusClass   = strtolower(str_replace('_', '-', $currentStatus));
            @endphp

            <div>
                <span class="status-identity__label">Current Status</span>
                <span class="admission-status admission-status--{{ $statusClass }}">
                    {{ str_replace('_', ' ', $currentStatus) }}
                </span>
            </div>
        </section>

        {{-- Application Lifecycle --}}
        <section class="admission-panel status-lifecycle">
            <div class="admission-panel__header">
                <div>
                    <span class="admission-panel__kicker">Journey</span>
                    <h2 class="admission-panel__title">Application Journey</h2>
                    <p class="admission-panel__description">
                        Your application moves through several stages before a final decision is issued.
                    </p>
                </div>
            </div>

            @php
                $statusOrder   = ['DRAFT','SUBMITTED','UNDER_REVIEW','APPROVED','REJECTED'];
                $currentIndex  = array_search($currentStatus, $statusOrder, true);
            @endphp

            <div class="status-timeline" aria-label="Application lifecycle">
                @foreach($statusOrder as $index => $status)
                    @php
                        $isCurrent   = $currentStatus === $status;
                        $isDecision  = in_array($status, ['APPROVED','REJECTED'], true);
                        $isCompleted = $currentIndex !== false && $index < $currentIndex && !$isDecision;
                    @endphp

                    <div class="status-timeline__item
                        @if($isCurrent) status-timeline__item--current @endif
                        @if($isCompleted) status-timeline__item--completed @endif
                        @if($isDecision) status-timeline__item--decision @endif">

                        <div class="status-timeline__marker" aria-hidden="true">
                            @if($isCompleted)
                                ✓
                            @elseif($isCurrent)
                                <span></span>
                            @else
                                {{ $index + 1 }}
                            @endif
                        </div>

                        <div class="status-timeline__content">
                            <strong>{{ str_replace('_', ' ', $status) }}</strong>
                            <span>
                                @switch($status)
                                    @case('DRAFT') Application saved but not yet submitted. @break
                                    @case('SUBMITTED') Application received by the admissions office. @break
                                    @case('UNDER_REVIEW') Your application is currently being reviewed. @break
                                    @case('APPROVED') Your application has been approved. @break
                                    @case('REJECTED') A final decision has been issued. @break
                                    @default Application status update. @break
                                @endswitch
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Payment --}}
        <section class="admission-panel status-payment">
            <div class="admission-panel__header">
                <div>
                    <span class="admission-panel__kicker">Payment</span>
                    <h2 class="admission-panel__title">Application Fee</h2>
                    <p class="admission-panel__description">
                        Payment is tracked separately from your application status.
                    </p>
                </div>
            </div>

            <div class="status-payment__body">
                <div class="status-payment__amount">
                    <span>Application Fee</span>
                    <strong>{{ $application->formatted_application_fee }}</strong>
                </div>

                @if($application->applicationFeePayment)
                    @php
                        $paymentStatus = strtoupper((string) $application->applicationFeePayment->status);
                        $paymentStatusClass = strtolower(str_replace('_', '-', $paymentStatus));
                    @endphp

                    <div class="status-payment__details">
                        <span class="admission-status admission-status--{{ $paymentStatusClass }}">
                            {{ $paymentStatus }}
                        </span>
                        <div>
                            <span>Reference</span>
                            <strong>{{ $application->applicationFeePayment->reference }}</strong>
                        </div>
                        <div>
                            <span>Amount</span>
                            <strong>{{ $application->applicationFeePayment->formatted_amount }}</strong>
                        </div>
                        <div>
                            <span>Paid</span>
                            <strong>{{ $application->applicationFeePayment->paid_at?->format('d M Y, H:i') ?? '—' }}</strong>
                        </div>
                    </div>

                    @if($paymentStatus === 'FAILED')
                        <form method="POST" action="{{ route('payment.initialize', $application) }}" class="status-payment__action">
                            @csrf
                            <x-portal-button variant="danger" type="submit">Retry Payment</x-portal-button>
                        </form>
                    @endif

                    @if($paymentStatus === 'PENDING')
                        <form method="POST" action="{{ route('payment.initialize', $application) }}" class="status-payment__action">
                            @csrf
                            <x-portal-button variant="success" type="submit">Complete Payment</x-portal-button>
                        </form>
                    @endif
                @else
                    <div class="status-payment__pending">
                        <p>Your application fee has not been paid yet.</p>
                        <form method="POST" action="{{ route('payment.initialize', $application) }}">
                            @csrf
                            <x-portal-button variant="success" type="submit">Pay Application Fee</x-portal-button>
                        </form>
                    </div>
                @endif
            </div>
        </section>

        {{-- Documents --}}
        <section class="admission-panel status-documents">
            <div class="admission-panel__header">
                <div>
                    <span class="admission-panel__kicker">Verification</span>
                    <h2 class="admission-panel__title">Uploaded Documents</h2>
                    <p class="admission-panel__description">
                        Review the verification status of your submitted documents.
                    </p>
                </div>
            </div>

            @if($application->documents->count())
                <div class="status-document-table">
                    <div class="status-document-table__header">
                        <span>Document</span>
                        <span>Status</span>
                        <span>Uploaded</span>
                        <span>Remarks</span>
                    </div>
                    @foreach($application->documents as $doc)
                        @php
                            $documentStatus = strtoupper((string) ($doc->status ?? 'PENDING'));
                            $documentStatusClass = strtolower(str_replace('_', '-', $documentStatus));
                        @endphp
                        <div class="status-document-table__row">
                            <strong>{{ $doc->documentType->name }}</strong>
                            <span class="admission-status admission-status--{{ $documentStatusClass }}">
                                {{ $documentStatus }}
                            </span>
                            <span>{{ optional($doc->uploaded_at)->format('d M Y, H:i') ?? '—' }}</span>
                            <span>
                                @if($documentStatus === 'REJECTED' && $doc->rejection_reason)
                                    Correction required: {{ $doc->rejection_reason }}
                                @else
                                    {{ $doc->rejection_reason ?? 'No remarks' }}
                                @endif
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="admission-inline-empty">No documents have been uploaded yet.</div>
            @endif
        </section>

        {{-- ================================================================
             ADMISSION DECISION
        ================================================================= --}}
        @if($application->decision)
            @php
                $decisionStatus = strtoupper((string) ($application->decision->decision ?? 'PENDING'));
                $decisionStatusClass = strtolower(str_replace('_', '-', $decisionStatus));
            @endphp

            <section class="admission-panel status-decision status-decision--{{ $decisionStatusClass }}">
                <div class="admission-panel__header">
                    <div>
                        <span class="admission-panel__kicker">Admissions Office</span>
                        <h2 class="admission-panel__title">Admission Decision</h2>
                    </div>
                    <span class="admission-status admission-status--{{ $decisionStatusClass }}">
                        {{ $decisionStatus }}
                    </span>
                </div>

                @if($application->decision->remarks)
                    <div class="status-decision__remarks">{{ $application->decision->remarks }}</div>
                @endif

                <p class="status-decision__meta">
                    Decision issued
                    {{ $application->decision->decided_at?->format('d M Y, H:i') ?? '—' }}
                    by
                    {{ optional($application->decision->officer)->name ?? 'Admissions Officer' }}.
                </p>

                @if($currentStatus === 'APPROVED' && $decisionStatus === 'APPROVED')
                    <div class="status-decision__action">
                        <x-portal-button variant="primary" href="{{ route('admission.letter', $application) }}">
                            View Admission Letter
                        </x-portal-button>
                    </div>
                @endif
            </section>
        @endif





        {{-- ================================================================
             STATUS HISTORY
        ================================================================= --}}
        <section class="admission-panel status-history">
            <div class="admission-panel__header">
                <div>
                    <span class="admission-panel__kicker">Transparency</span>
                    <h2 class="admission-panel__title">Status History</h2>
                    <p class="admission-panel__description">
                        A record of changes made to your application status.
                    </p>
                </div>
            </div>

            @if($history->count())
                <div class="status-history-list">
                    @foreach($history as $record)
                        <div class="status-history-item">
                            <div class="status-history-item__marker" aria-hidden="true"><span></span></div>
                            <div class="status-history-item__content">
                                <div class="status-history-item__top">
                                    <strong>{{ $record->new_status }}</strong>
                                    <time>{{ optional($record->changed_at)->format('d M Y, H:i') ?? '—' }}</time>
                                </div>
                                <p>
                                    {{ $record->old_status ?? 'Application created' }} →
                                    {{ $record->new_status }}
                                </p>
                                <span>Changed by {{ optional($record->officer)->name ?? 'System' }}</span>

                                {{-- ✅ Show remarks if present --}}
                                @if($record->remarks)
                                    <p class="status-history-item__remarks">
                                        {{ $record->remarks }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="admission-inline-empty">No status changes have been recorded yet.</div>
            @endif
        </section>

    </main>
</div>

@endsection

