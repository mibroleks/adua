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

Status: ✅ Production Ready
Version: 4.2 (Removed manual submit, payment-driven submission)
--}}

@extends('layouts.app')

@section('title', 'Application Status')

@section('content')

<div class="admission-page admission-page--status">

    <div class="admission-atmosphere admission-atmosphere--left" aria-hidden="true"></div>

    <main class="admission-shell admission-shell--wide">

        {{-- Feedback alerts --}}
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

        {{-- Header --}}
        <header class="admission-page-header">
            <div class="admission-eyebrow">
                <span class="admission-eyebrow__dot" aria-hidden="true"></span>
                Application
            </div>
            <h1 class="admission-page-title">Application Status</h1>
            <p class="admission-page-description">
                Follow your application journey and review everything submitted
                to the admissions office.
            </p>
        </header>

        {{-- Application identity --}}
        <section class="status-identity">
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
            <div>
                <span class="status-identity__label">Current Status</span>
                <span class="admission-status admission-status--{{ strtolower($application->application_status) }}">
                    {{ str_replace('_', ' ', $application->application_status) }}
                </span>
            </div>
        </section>

        {{-- Application lifecycle --}}
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

            <div class="status-timeline">
                @php
                    $statusOrder = [
                        'DRAFT',
                        'SUBMITTED',
                        'UNDER_REVIEW',
                        'APPROVED',
                        'REJECTED',
                    ];
                    $currentStatus = $application->application_status;
                    $currentIndex = array_search($currentStatus, $statusOrder, true);
                @endphp

                @foreach($statusOrder as $index => $status)
                    @php
                        $isCurrent = $currentStatus === $status;
                        $isCompleted = $currentIndex !== false && $index < $currentIndex;
                        $isDecision = in_array($status, ['APPROVED', 'REJECTED'], true);
                    @endphp

                    <div class="status-timeline__item
                        @if($isCurrent) status-timeline__item--current @endif
                        @if($isCompleted) status-timeline__item--completed @endif
                        @if($isDecision) status-timeline__item--decision @endif
                    ">
                        <div class="status-timeline__marker">
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
                                    @case('DRAFT')
                                        Application saved but not yet submitted.
                                        @break
                                    @case('SUBMITTED')
                                        Application received by the admissions office.
                                        @break
                                    @case('UNDER_REVIEW')
                                        Your application is currently being reviewed.
                                        @break
                                    @case('APPROVED')
                                        Your application has been approved.
                                        @break
                                    @case('REJECTED')
                                        A final decision has been issued.
                                        @break
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
                    <div class="status-payment__details">
                        <span class="admission-status admission-status--{{ strtolower($application->applicationFeePayment->status) }}">
                            {{ $application->applicationFeePayment->status }}
                        </span>
                        <div>
                            <span>Reference</span>
                            <strong>{{ $application->applicationFeePayment->reference }}</strong>
                        </div>
                        <div>
                            <span>Paid</span>
                            <strong>{{ $application->applicationFeePayment->paid_at?->format('d M Y, H:i') ?? '—' }}</strong>
                        </div>
                    </div>

                    @if($application->applicationFeePayment->status === 'FAILED')
                        <form method="POST" action="{{ route('payment.initialize', $application) }}" class="status-payment__action">
                            @csrf
                            <x-portal-button variant="danger" type="submit">Retry Payment</x-portal-button>
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
                        <div class="status-document-table__row">
                            <strong>{{ $doc->documentType->name }}</strong>
                            <span class="admission-status admission-status--{{ strtolower($doc->status) }}">
                                {{ $doc->status }}
                            </span>
                            <span>{{ optional($doc->uploaded_at)->format('d M Y, H:i') ?? '—' }}</span>
                            <span>{{ $doc->rejection_reason ?? 'No remarks' }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="admission-inline-empty">No documents have been uploaded yet.</div>
            @endif
        </section>

        {{-- Decision --}}
        @if($application->decision)
            <section class="admission-panel status-decision status-decision--{{ strtolower($application->decision->status) }}">
                <div class="admission-panel__header">
                    <div>
                        <span class="admission-panel__kicker">Final decision</span>
                        <h2 class="admission-panel__title">Admission Decision</h2>
                    </div>
                    <span class="admission-status admission-status--{{ strtolower($application->decision->status) }}">
                        {{ $application->decision->status }}
                    </span>
                </div>

                @if($application->decision->remarks)
                    <div class="status-decision__remarks">
                        {{ $application->decision->remarks }}
                    </div>
                @endif

                <p class="status-decision__meta">
                    Decision issued
                    {{ $application->decision->decided_at?->format('d M Y, H:i') ?? '—' }}
                    by
                    {{ optional($application->decision->officer)->name ?? 'Admissions Officer' }}.
                </p>

                @if($application->application_status === 'APPROVED')
                    <div class="status-decision__action">
                        <x-portal-button
                            variant="primary"
                            href="{{ route('admission.letter', $application) }}"
                        >
                            View Admission Letter
                        </x-portal-button>
                    </div>
                @endif
            </section>
        @endif

        {{-- History --}}
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
                            <div class="status-history-item__marker">
                                <span></span>
                            </div>
                            <div class="status-history-item__content">
                                <div class="status-history-item__top">
                                    <strong>{{ $record->new_status }}</strong>
                                    <time>{{ optional($record->changed_at)->format('d M Y, H:i') ?? '—' }}</time>
                                </div>
                                <p>
                                    {{ $record->old_status ?? 'Application created' }}
                                    →
                                    {{ $record->new_status }}
                                </p>
                                <span>
                                    Changed by {{ optional($record->officer)->name ?? 'System' }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="admission-inline-empty">
                    No status changes have been recorded yet.
                </div>
            @endif
        </section>

    </main>
</div>

@endsection

