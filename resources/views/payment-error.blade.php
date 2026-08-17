{{-- 
Component: Payment Error (Preset-Driven)
File Path: resources/views/payment-error.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Displays a failure page when payment does not succeed.
Shows reference, amount, date, and provides retry option.
Styled with semantic theme tokens for consistency.

Status: ✅ Production Ready
Version: 1.0 (semantic theme tokens integration)
--}}

@extends('layouts.app')

@section('title', 'Payment Failed')

@section('content')
<div class="mx-auto max-w-2xl p-7 sm:p-9 portal-card animate-portal-fade-up bg-[var(--theme-danger-soft)]">

    {{-- Page Heading --}}
    <h1 class="text-3xl font-black mb-6 theme-danger">Payment Failed</h1>

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
        </div>
    @endif

    {{-- Error Message --}}
    <x-alert type="danger">
        Your payment could not be processed. Please try again to complete your application fee payment.
    </x-alert>

    {{-- Retry Action --}}
    <div class="mt-8 text-center">
        <form method="POST" action="{{ route('payment.initialize', $application) }}">
            @csrf
            <x-portal-button variant="danger" type="submit">
                Retry Payment
            </x-portal-button>
        </form>
    </div>

    {{-- Back to Dashboard --}}
    <div class="mt-6 text-center">
        <x-portal-button variant="secondary" href="{{ route('dashboard') }}">
            Back to Dashboard
        </x-portal-button>
    </div>
</div>
@endsection
