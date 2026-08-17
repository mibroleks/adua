{{-- 
Component: Payment Status (Preset-Driven)
File Path: resources/views/payment-status.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Displays the current payment status for an application.
Shows reference, amount, date, and outcome (success, pending, failed).
Styled with semantic theme tokens for consistency.

Status: ✅ Production Ready
Version: 1.0 (semantic theme tokens integration)
--}}

@extends('layouts.app')

@section('title', 'Payment Status')

@section('content')
<div class="mx-auto max-w-2xl p-7 sm:p-9 portal-card animate-portal-fade-up">

    {{-- Page Heading --}}
    <h1 class="text-3xl font-black mb-6 theme-heading">Payment Status</h1>

    {{-- Application Info --}}
    <div class="mb-4">
        <p class="text-sm theme-muted">Application Number:</p>
        <p class="text-lg font-semibold theme-text">{{ $application->application_number }}</p>
    </div>

    {{-- Payment Details --}}
    @if($payment)
        <div class="mb-6">
            <p class="text-sm theme-muted">Reference:</p>
            <p class="text-lg font-semibold theme-text">{{ $payment->reference }}</p>

            <p class="mt-3 text-sm theme-muted">Amount:</p>
            <p class="text-lg font-semibold theme-primary">
                ₦{{ number_format($payment->amountInNaira(), 2) }}
            </p>

            <p class="mt-3 text-sm theme-muted">Date:</p>
            <p class="text-lg font-semibold theme-text">
                {{ optional($payment->paid_at)->format('d M Y, H:i') ?? '—' }}
            </p>

            <p class="mt-3 text-sm theme-muted">Status:</p>
            <p class="text-lg font-bold 
                @if($payment->status === 'SUCCESS') theme-success 
                @elseif($payment->status === 'FAILED') theme-danger 
                @else theme-info @endif">
                {{ $payment->status }}
            </p>
        </div>
    @else
        <x-alert type="danger">
            No payment record found for this application.
        </x-alert>
    @endif

    {{-- Next Actions --}}
    <div class="mt-8 text-center">
        @if($payment && $payment->status === 'FAILED')
            <form method="POST" action="{{ route('payment.initialize', $application) }}">
                @csrf
                <x-portal-button variant="danger" type="submit">
                    Retry Payment
                </x-portal-button>
            </form>
        @elseif($payment && $payment->status === 'SUCCESS')
            <x-portal-button variant="primary" href="{{ route('status', $application) }}">
                Back to Application Status
            </x-portal-button>
        @else
            <x-portal-button variant="secondary" href="{{ route('dashboard') }}">
                Back to Dashboard
            </x-portal-button>
        @endif
    </div>
</div>
@endsection
