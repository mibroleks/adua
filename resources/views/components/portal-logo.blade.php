{{-- 
Component: Portal Logo (Preset-Driven)
File Path: resources/views/components/portal-logo.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Reusable logo component for the student portal.
Displays institution logo dynamically from ThemeService with fallback to settings.
Ensures officers can change branding without touching code.

Status: ✅ Production Ready
Version: 2.0 (semantic theme tokens integration)
--}}

@php
    $theme = app(\App\Services\ThemeService::class);
@endphp

@if($theme->logoUrl())
    <img
        src="{{ $theme->logoUrl() }}"
        alt="{{ $theme->institutionName() ?? setting('institution.name') ?? 'Institution Logo' }}"
        {{ $attributes->merge([
            'class' => 'h-12 w-auto object-contain'
        ]) }}
    >
@else
    <img
        src="{{ setting('institution.logo') ?? asset('images/default-logo.png') }}"
        alt="{{ setting('institution.name') ?? 'Institution Logo' }}"
        {{ $attributes->merge([
            'class' => 'h-12 w-auto object-contain'
        ]) }}
    >
@endif
