{{-- 
Component: Application Progress Tracker (Preset-Driven)
File Path: resources/views/progress-tracker.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Displays a visual workflow tracker for a student’s application.
Shows each stage: Draft, Submitted, Payment, Documents, Review, Decision, Admission Letter.
Styled with semantic theme tokens for consistency.

Status: ✅ Production Ready
Version: 1.0 (semantic theme tokens integration)
--}}

@extends('layouts.app')

@section('title', 'Application Progress')

@section('content')
<div class="mx-auto max-w-4xl p-7 sm:p-9 portal-card animate-portal-fade-up">

    {{-- Page Heading --}}
    <h1 class="text-3xl font-black mb-6 theme-heading">Application Progress</h1>

    {{-- Progress Timeline --}}
    <x-application-timeline>
        {{-- Draft --}}
        <x-application-progress 
            step="Draft" 
            :active="$application->status === 'DRAFT'" 
            :completed="in_array($application->status, ['SUBMITTED','PAID','UNDER_REVIEW','APPROVED','REJECTED'])"
            description="Application created but not yet submitted." />

        {{-- Submitted --}}
        <x-application-progress 
            step="Submitted" 
            :active="$application->status === 'SUBMITTED'" 
            :completed="in_array($application->status, ['PAID','UNDER_REVIEW','APPROVED','REJECTED'])"
            description="Application submitted to the admissions office." />

        {{-- Payment --}}
        <x-application-progress 
            step="Payment" 
            :active="$application->status === 'PAID'" 
            :completed="in_array($application->status, ['UNDER_REVIEW','APPROVED','REJECTED'])"
            description="Application fee paid successfully." />

        {{-- Documents --}}
        <x-application-progress 
            step="Documents" 
            :active="$application->status === 'DOCUMENTS_VERIFIED'" 
            :completed="in_array($application->status, ['UNDER_REVIEW','APPROVED','REJECTED'])"
            description="Required documents uploaded and verified." />

        {{-- Review --}}
        <x-application-progress 
            step="Review" 
            :active="$application->status === 'UNDER_REVIEW'" 
            :completed="in_array($application->status, ['APPROVED','REJECTED'])"
            description="Application under review by admissions officers." />

        {{-- Decision --}}
        <x-application-progress 
            step="Decision" 
            :active="in_array($application->status, ['APPROVED','REJECTED'])" 
            :completed="$application->status === 'APPROVED'"
            description="Final admission decision issued." />

        {{-- Admission Letter --}}
        <x-application-progress 
            step="Admission Letter" 
            :active="$application->status === 'APPROVED'" 
            :completed="$application->status === 'APPROVED'"
            description="Download your official admission letter." />
    </x-application-timeline>

    {{-- Next Action --}}
    <div class="mt-8 text-center">
        @if($application->status === 'DRAFT')
            <x-portal-button variant="primary" href="{{ route('application.submit', $application) }}">
                Submit Application
            </x-portal-button>
        @elseif($application->status === 'SUBMITTED')
            <x-portal-button variant="success" href="{{ route('payment.initialize', $application) }}">
                Pay Application Fee
            </x-portal-button>
        @elseif($application->status === 'APPROVED')
            <x-portal-button variant="primary" href="{{ route('admission.letter', $application) }}">
                Download Admission Letter
            </x-portal-button>
        @endif
    </div>
</div>
@endsection
