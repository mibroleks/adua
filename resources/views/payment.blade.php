{{-- 
Component: Payment Page (Preset-Driven)
File Path: resources/views/payment.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Displays the application fee and allows the student to initiate payment.
Fee is pulled dynamically from settings, not hardcoded.
Styled with semantic theme tokens for consistency.

Status: ✅ Production Ready
Version: 3.1 (semantic theme tokens integration, consolidated)
--}}

@extends('layouts.app')

@section('title', 'Application Payment')

@section('content')
<div class="mx-auto max-w-2xl p-7 sm:p-9 portal-card animate-portal-fade-up">

    {{-- Page Heading --}}
    <h1 class="text-3xl font-black mb-6 theme-heading">Application Payment</h1>

    {{-- Application Info --}}
    <div class="mb-4">
        <p class="text-sm theme-muted">Application Number:</p>
        <p class="text-lg font-semibold theme-text">
            {{ $application->application_number }}
        </p>
    </div>

    {{-- Fee --}}
    <div class="mb-4">
        <p class="text-sm theme-muted">Application Fee:</p>
        <p class="text-lg font-semibold theme-primary">
            ₦{{ number_format(setting('admissions.application_fee'), 0) }}
        </p>
    </div>

    {{-- Pay Now Button --}}
    <div class="mt-6">
        <form method="POST" action="{{ route('payment.initialize', $application) }}">
            @csrf
            <x-portal-button variant="success" type="submit">
                Pay Now
            </x-portal-button>
        </form>
    </div>

    {{-- Status Message --}}
    @if(session('success'))
        <x-alert type="success" class="mt-4">
            {{ session('success') }}
        </x-alert>
    @endif

    @if($errors->any())
        <x-alert type="danger" class="mt-4">
            {{ $errors->first() }}
        </x-alert>
    @endif
</div>
@endsection
