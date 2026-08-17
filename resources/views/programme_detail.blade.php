{{-- 
Component: Programme Detail (Preset-Driven)
File Path: resources/views/programme_detail.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Displays detailed information about a single programme.
Includes name, faculty, description, requirements, duration, and application CTA.
Styled with semantic theme tokens for consistency.

Status: ✅ Production Ready
Version: 1.2 (semantic tokens integration, faculty fix, CTA route fix, related programmes section)
--}}

@extends('layouts.app')

@section('title', $programme->name . ' — Admissions')

@section('content')

    {{-- Programme Header --}}
    <section class="theme-surface py-12">
        <div class="portal-container text-center">
            <h1 class="text-3xl font-bold theme-heading">
                {{ $programme->name }}
            </h1>
            <p class="mt-2 text-sm theme-muted">
                Faculty of {{ $programme->faculty?->name }}
            </p>
        </div>
    </section>

    {{-- Programme Details --}}
    <section class="portal-container py-16">
        <div class="prose max-w-none theme-text">
            {!! nl2br(e($programme->description)) !!}
        </div>

        {{-- Requirements --}}
        @if(!empty($programme->requirements))
            <div class="mt-10">
                <h2 class="text-xl font-semibold theme-heading">Admission Requirements</h2>
                <ul class="mt-4 list-disc pl-6 text-sm theme-text">
                    @foreach($programme->requirements as $requirement)
                        <li>{{ $requirement }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Duration --}}
        @if($programme->duration)
            <div class="mt-10">
                <h2 class="text-xl font-semibold theme-heading">Programme Duration</h2>
                <p class="mt-2 text-sm theme-text">
                    {{ $programme->duration }}
                </p>
            </div>
        @endif

        {{-- CTA --}}
        <div class="mt-12">
            <x-portal-button variant="primary" href="{{ route('application.create') }}">
                Apply Now
            </x-portal-button>
        </div>
    </section>

    {{-- Related Programmes --}}
    @if($relatedProgrammes->count())
        <section class="theme-surface-muted py-16">
            <div class="portal-container">
                <h2 class="text-2xl font-bold theme-heading text-center">
                    Related Programmes
                </h2>
                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($relatedProgrammes as $related)
                        <x-portal-card :title="$related->name" :subtitle="$related->faculty?->name">
                            {{ Str::limit($related->description, 100) }}
                            <x-slot:footer>
                                <x-portal-button variant="secondary" href="{{ route('programmes.show', $related) }}">
                                    View Details
                                </x-portal-button>
                            </x-slot:footer>
                        </x-portal-card>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

@endsection
