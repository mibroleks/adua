{{--
Component: Admissions Landing Page
File Path: resources/views/landing.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Premium institutional admissions landing experience.

Architecture:
- Editorial hero
- Application window
- Programme discovery
- Reusable programme detail modal
- Institutional story
- Application journey
- Final CTA

Version: 4.2 (hero slides full height + media fit)
--}}

@extends('layouts.app')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;

    $theme = app(\App\Services\ThemeService::class);
    $institutionName = $theme->institutionName() ?? setting('institution.name') ?? 'University';

    // Normalize hero media to always be an array
    $rawMedia = setting('institution.hero_media');
    if (is_string($rawMedia)) {
        $decoded = json_decode($rawMedia, true);
        $mediaItems = is_array($decoded) ? $decoded : [$rawMedia];
    } elseif (is_array($rawMedia)) {
        $mediaItems = $rawMedia;
    } else {
        $mediaItems = [$rawMedia];
    }

    // Fallback to single hero image if empty
    if (empty($mediaItems)) {
        $mediaItems = [setting('institution.hero_image')];
    }

    // Countdown logic
    $openDate = Carbon::parse(setting('admissions.application_start'));
    $closeDate = Carbon::parse(setting('admissions.application_deadline'));
    $now = Carbon::now();
    $isOpen = $now->between($openDate, $closeDate);
@endphp

@section('title', 'Admissions')

@section('content')

{{-- ============================================================
     HERO
============================================================ --}}
<section class="admissions-hero">
    <div class="portal-container admissions-hero__grid">

        {{-- Copy --}}
        <div class="admissions-hero__content">
            <div class="admissions-eyebrow">
                <span class="admissions-eyebrow__dot"></span>
                Admissions {{ date('Y') }}/{{ date('Y') + 1 }}
            </div>

            <h1>
                Begin the next <em>chapter</em> of your story.
            </h1>

            <p class="admissions-hero__description">
                Discover an education designed to challenge your thinking,
                expand your perspective and prepare you to make an impact
                far beyond the classroom.
            </p>

            <div class="admissions-hero__actions">
                <x-portal-button href="{{ route('application.create') }}" variant="primary">
                    Begin Application
                </x-portal-button>

                <x-portal-button href="#programmes" variant="secondary" :icon="false">
                    Explore Programmes
                </x-portal-button>
            </div>

            <div class="admissions-hero__meta">
                <div><strong>01</strong><span>Explore</span></div>
                <div><strong>02</strong><span>Apply</span></div>
                <div><strong>03</strong><span>Become</span></div>
            </div>
        </div>

        {{-- Visual with carousel behaviour --}}
        <div class="admissions-hero__visual" data-carousel>
            <div class="admissions-hero__image">
                <div class="admissions-hero__slides">
                    @forelse($mediaItems as $index => $media)
                        @php
                            $url = is_array($media) ? ($media['url'] ?? null) : $media;
                            $type = is_array($media) ? ($media['type'] ?? null) : null;

                            // Convert relative paths to full public URLs
                            if ($url && !Str::startsWith($url, ['http://', 'https://', '/'])) {
                                $url = asset('storage/' . ltrim($url, '/'));
                            }
                        @endphp

                        <div class="admissions-hero__slide {{ $index === 0 ? 'active' : '' }}" data-slide data-slide-index="{{ $index }}" @if($index > 0) hidden @endif>
                            @if($type === 'video' || ($url && Str::endsWith(strtolower($url), ['.mp4', '.webm', '.ogg'])))
                                <video 
                                    src="{{ $url }}" 
                                    autoplay 
                                    muted 
                                    loop 
                                    playsinline
                                    preload="metadata"
                                    class="admissions-hero__media"
                                ></video>
                            @elseif($url)
                                <img 
                                    src="{{ $url }}" 
                                    alt="{{ $institutionName }} visual {{ $index+1 }}"
                                    class="admissions-hero__media"
                                >
                            @else
                                <div class="admissions-hero__placeholder">
                                    <span>Your university.<br>Your future.</span>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="admissions-hero__slide active">
                            <div class="admissions-hero__placeholder">
                                <span>Your university.<br>Your future.</span>
                            </div>
                        </div>
                    @endforelse
                </div>

                {{-- Overlay --}}
                <div class="admissions-hero__image-overlay"></div>

                {{-- Carousel controls --}}
                @if(count($mediaItems) > 1)
                    <button type="button" class="carousel-control prev" data-carousel-prev aria-label="Previous slide">‹</button>
                    <button type="button" class="carousel-control next" data-carousel-next aria-label="Next slide">›</button>
                @endif
            </div>

            {{-- Indicators --}}
            @if(count($mediaItems) > 1)
                <div class="admissions-hero__indicators" aria-label="Hero media navigation">
                    @foreach($mediaItems as $index => $media)
                        <button type="button" data-carousel-indicator data-slide-to="{{ $index }}" class="{{ $index === 0 ? 'active' : '' }}" aria-label="Go to slide {{ $index+1 }}" aria-current="{{ $index === 0 ? 'true' : 'false' }}"></button>
                    @endforeach
                </div>
            @endif

            {{-- Floating application card (dynamic) --}}
            <div class="admissions-hero__floating">
                <span>APPLICATIONS</span>
                @if($isOpen)
                    <strong>NOW OPEN</strong>
                    <small>Closes {{ $closeDate->toDayDateTimeString() }}</small>
                @else
                    <strong>COMING SOON</strong>
                    <small>Opens {{ $openDate->toDayDateTimeString() }}</small>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     APPLICATION WINDOW
============================================================ --}}
<section class="application-window">
    <div class="portal-container">
        <div class="application-window__header">
            <div>
                <span class="section-eyebrow">APPLICATION WINDOW</span>
                <h2>Your opportunity starts here.</h2>
            </div>
            <x-portal-button href="{{ route('application.create') }}" variant="subtle">
                Apply now
            </x-portal-button>
        </div>
        <x-application-countdown />
    </div>
</section>

{{-- ============================================================
     PROGRAMMES
============================================================ --}}
<section id="programmes" class="programmes-section">
    <div class="portal-container">
        <div class="section-heading">
            <div>
                <span class="section-eyebrow">FIND YOUR PATH</span>
                <h2>Programmes designed for possibility.</h2>
                <p>Explore programmes built to develop knowledge, capability and purpose.</p>
            </div>
            <x-portal-button href="{{ route('programmes.index') }}" variant="subtle">
                View all programmes
            </x-portal-button>
        </div>

        <div class="programme-grid">
            @forelse($programmes->take(3) as $programme)
                <x-portal-card
                    class="programme-card"
                    :number="str_pad($loop->iteration, 2, '0', STR_PAD_LEFT)"
                    :eyebrow="$programme->faculty?->name"
                    :title="$programme->name"
                    :subtitle="$programme->code ?? null"
                >
                    <p>{{ Str::limit($programme->description, 145) }}</p>
                    <div class="programme-card__actions">
                        {{-- FIX: use data-modal-open instead of data-modal-target --}}
                        <button
                            type="button"
                            class="programme-card__details"
                            data-modal-open="programme-modal-{{ $programme->id }}"
                        >
                            Explore programme <span aria-hidden="true">→</span>
                        </button>
                        <a href="{{ route('application.create', ['programme' => $programme->id]) }}"
                           class="programme-card__apply">
                            Apply
                        </a>
                    </div>
                </x-portal-card>
            @empty
                <div class="programme-empty">
                    <h3>Programmes are being prepared.</h3>
                    <p>Our programme catalogue will appear here once published.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

{{-- ============================================================
     PROGRAMME MODALS
============================================================ --}}
@foreach($programmes->take(3) as $programme)
    <x-portal-modal
        id="programme-modal-{{ $programme->id }}"
        :eyebrow="$programme->faculty?->name ?? 'Programme'"
        :title="$programme->name"
        size="medium"
    >
        <div class="programme-modal-content">
            <div class="programme-modal-meta">
                @if($programme->code)
                    <div><span>PROGRAMME CODE</span><strong>{{ $programme->code }}</strong></div>
                @endif
                @if($programme->duration)
                    <div><span>DURATION</span><strong>{{ $programme->duration }} years</strong></div>
                @endif
            </div>

            <div class="programme-modal-description">
                <h3>About this programme</h3>
                <div>{!! nl2br(e($programme->description)) !!}</div>
            </div>

            @if(!empty($programme->requirements))
                <div class="programme-modal-requirements">
                    <h3>Admission requirements</h3>
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

        <x-slot:footer>
            <x-portal-button href="{{ route('application.create', ['programme' => $programme->id]) }}" variant="primary">
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
     INSTITUTIONAL STORY
============================================================ --}}
<section id="admissions-information" class="institution-section">
    <div class="portal-container institution-section__grid">
        <div class="institution-section__label">
            <span>WHY {{ strtoupper($institutionName) }}</span>
        </div>
        <div class="institution-section__content">
            <span class="section-eyebrow">THE UNIVERSITY EXPERIENCE</span>
            <h2>An education that goes beyond a degree.</h2>
            <p>
                At {{ $institutionName }}, education is about more than acquiring knowledge.
                It is about developing the confidence, curiosity and responsibility to shape
                the world around you.
            </p>
            <x-portal-button href="{{ route('programmes.index') }}" variant="subtle">
                Discover your possibilities
            </x-portal-button>
        </div>
    </div>
</section>
{{-- ============================================================
     APPLICATION JOURNEY
============================================================ --}}
<section id="how-it-works" class="application-journey">
    <div class="portal-container">
        <div class="section-heading centered">
            <span class="section-eyebrow">YOUR APPLICATION JOURNEY</span>
            <h2>Four steps. One new beginning.</h2>
        </div>

        <div class="journey-grid">

            <article class="journey-step">
                <span>01</span>
                <h3>Explore</h3>
                <p>
                    Find the programme that matches
                    your ambitions.
                </p>
            </article>

            <article class="journey-step">
                <span>02</span>
                <h3>Prepare</h3>
                <p>
                    Review requirements and prepare
                    your application materials.
                </p>
            </article>

            <article class="journey-step">
                <span>03</span>
                <h3>Apply</h3>
                <p>
                    Complete your application securely
                    through our online portal.
                </p>
            </article>

            <article class="journey-step">
                <span>04</span>
                <h3>Track</h3>
                <p>
                    Sign in anytime to follow the progress
                    of your application.
                </p>
            </article>

        </div>
    </div>
</section>


{{-- ============================================================
     FINAL CTA
============================================================ --}}
<section class="final-cta">
    <div class="portal-container">

        <span class="section-eyebrow">
            READY?
        </span>

        <h2>
            Your next chapter starts
            with one decision.
        </h2>

        <p>
            Take the first step toward your future.
        </p>

        <x-portal-button
            href="{{ route('application.create') }}"
            variant="primary"
        >
            Start your application
        </x-portal-button>

    </div>
</section>

@endsection
