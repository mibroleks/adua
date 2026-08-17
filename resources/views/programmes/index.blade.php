{{--
Component: Programmes Index
File Path: resources/views/programmes/index.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Full institutional programme catalogue.

Design language:
- Shares the Admissions Landing visual system
- Editorial typography
- Structured programme catalogue
- Reusable programme detail modal
- Theme-token driven

Version: 2.4 (modal trigger fix, requirements string split into list)
--}}

@extends('layouts.app')

@php
    $theme = app(\App\Services\ThemeService::class);
    $institutionName = $theme->institutionName()
        ?? setting('institution.name')
        ?? 'University';
@endphp

@section('title', 'Programmes')

@section('content')

{{-- ============================================================
     PROGRAMMES HERO
============================================================ --}}
<section class="programmes-page-hero">
    <div class="portal-container">
        <div class="programmes-page-hero__content">
            <span class="section-eyebrow">EXPLORE OUR PROGRAMMES</span>
            <h1>
                Find the path that
                <em>could shape your future.</em>
            </h1>
            <p>
                Explore our undergraduate and postgraduate programmes
                and discover an education designed around knowledge,
                capability and purpose.
            </p>
        </div>
    </div>
</section>


{{-- ============================================================
     PROGRAMME CATALOGUE
============================================================ --}}
<section class="programmes-catalogue">
    <div class="portal-container">

        <div class="section-heading">
            <div>
                <span class="section-eyebrow">PROGRAMME CATALOGUE</span>
                <h2>Discover where your curiosity can take you.</h2>
                <p>Browse the programmes offered by {{ $institutionName }}.</p>
            </div>
        </div>

        @if($programmes->count())
            <div class="programme-grid">
                @foreach($programmes as $programme)
                    <x-portal-card
                        class="programme-card"
                        :number="str_pad($loop->iteration, 2, '0', STR_PAD_LEFT)"
                        :eyebrow="$programme->faculty?->name"
                        :title="$programme->name"
                        :subtitle="$programme->code ?? null"
                    >
                        <p>{{ Str::limit($programme->description, 145) }}</p>

                        <div class="programme-card__meta">
                            <span>{{ $programme->degree_type }}</span>
                            @if($programme->duration)
                                <span>{{ $programme->duration }} years</span>
                            @endif
                        </div>

                        <div class="programme-card__actions">
                            <button
                                type="button"
                                class="programme-card__details"
                                data-modal-open="programme-modal-{{ $programme->id }}"
                            >
                                Explore programme <span aria-hidden="true">→</span>
                            </button>
                            <a
                                href="{{ route('application.create', ['programme' => $programme->id]) }}"
                                class="programme-card__apply"
                            >
                                Apply
                            </a>
                        </div>
                    </x-portal-card>
                @endforeach
            </div>

            @if($programmes->hasPages())
                <div class="programmes-pagination">
                    {{ $programmes->links() }}
                </div>
            @endif
        @else
            <div class="programme-empty">
                <span class="section-eyebrow">PROGRAMMES</span>
                <h3>Our programme catalogue is being prepared.</h3>
                <p>Programme listings will appear here once they have been published.</p>
                <x-portal-button href="{{ route('landing') }}" variant="subtle">
                    Back to admissions
                </x-portal-button>
            </div>
        @endif
    </div>
</section>


{{-- ============================================================
     PROGRAMME MODALS
============================================================ --}}
@foreach($programmes as $programme)
    <x-portal-modal
        id="programme-modal-{{ $programme->id }}"
        :eyebrow="$programme->faculty?->name ?? 'Programme'"
        :title="$programme->name"
        size="medium"
    >
        <div class="programme-modal-content">

            {{-- Meta --}}
            <div class="programme-modal-meta">
                @if($programme->code)
                    <div><span>PROGRAMME CODE</span><strong>{{ $programme->code }}</strong></div>
                @endif
                @if($programme->degree_type)
                    <div><span>DEGREE TYPE</span><strong>{{ $programme->degree_type }}</strong></div>
                @endif
                @if($programme->duration)
                    <div><span>DURATION</span><strong>{{ $programme->duration }} years</strong></div>
                @endif
                @if($programme->department)
                    <div><span>DEPARTMENT</span><strong>{{ $programme->department->name }}</strong></div>
                @endif
            </div>

            {{-- Description --}}
            <div class="programme-modal-description">
                <h3>About this programme</h3>
                <div>{!! nl2br(e($programme->description)) !!}</div>
            </div>

            {{-- Admissions --}}
            <div class="programme-modal-admissions">
                <h3>Admissions</h3>
                <div class="programme-modal-admissions__fee">
                    <span>APPLICATION FEE</span>
                    <strong>₦{{ number_format($programme->application_fee) }}</strong>
                </div>

                @if(!empty($programme->requirements))
                    <div class="programme-modal-requirements">
                        <h4>Admission requirements</h4>

                        {{-- Robust handling: string split into list or array --}}
                        @if(is_array($programme->requirements))
                            <ul>
                                @foreach($programme->requirements as $requirement)
                                    <li><span aria-hidden="true">✓</span>{{ $requirement }}</li>
                                @endforeach
                            </ul>
                        @else
                            <ul>
                                @foreach(explode(',', $programme->requirements) as $requirement)
                                    <li><span aria-hidden="true">✓</span>{{ trim($requirement) }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <x-slot:footer>
            <x-portal-button
                href="{{ route('application.create', ['programme' => $programme->id]) }}"
                variant="primary"
            >
                Apply for this programme
            </x-portal-button>
            <button
                type="button"
                class="portal-modal__close"
                data-modal-close="programme-modal-{{ $programme->id }}"
            >
                <span aria-hidden="true">&times;</span>
            </button>
        </x-slot:footer>
    </x-portal-modal>
@endforeach


{{-- ============================================================
     FINAL CTA
============================================================ --}}
<section class="final-cta">
    <div class="portal-container">
        <span class="section-eyebrow">FOUND YOUR PATH?</span>
        <h2>Your next chapter can start here.</h2>
        <p>Take the first step toward your future.</p>
        <x-portal-button href="{{ route('application.create') }}" variant="primary">
            Start your application
        </x-portal-button>
    </div>
</section>

@endsection
