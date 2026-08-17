{{-- 
Component: Payment Success (Preset-Driven)
File Path: resources/views/payment-success.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Displays a confirmation page after successful payment.
Shows reference, amount, date, and provides navigation back to application status.
Styled with semantic theme tokens for consistency.

Status: ✅ Production Ready
Version: 1.0 (semantic theme tokens integration)
--}}

@extends('layouts.app')

@section('title', 'Payment Successful')

@section('content')
<div class="mx-auto max-w-2xl p-7 sm:p-9 portal-card animate-portal-fade-up bg-[var(--theme-success-soft)]">

    {{-- Page Heading --}}
    <h1 class="text-3xl font-black mb-6 theme-success">Payment Successful</h1>

    {{-- Application Info --}}
    <div class="mb-4">
        <p class="text-sm theme-muted">Application Number:</p>
        <p class="text-lg font-semibold theme-text">{{ $application->application_number }}</p>
    </div>

    {{-- Payment Details --}}
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
    </div>

    {{-- Success Message --}}
    <x-alert type="success">
        Your application fee has been paid successfully. You may now continue with your admission process.
    </x-alert>

    {{-- Next Actions --}}
    <div class="mt-8 text-center">
        <x-portal-button variant="primary" href="{{ route('status', $application) }}">
            Back to Application Status
        </x-portal-button>
    </div>
</div>
@endsection
