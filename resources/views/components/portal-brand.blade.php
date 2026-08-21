{{-- 
Component: Portal Brand (Preset-Driven)
File Path: resources/views/components/portal-brand.blade.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Reusable brand component for the student portal.
Displays institution logo + name with dynamic colours from ThemeService.
Ensures officers can change branding without touching code.

Status: ✅ Production Ready
Version: 2.0 (semantic theme tokens integration)
--}}

@php
    $theme = app(\App\Services\ThemeService::class);
@endphp

<div {{ $attributes->merge(['class' => 'portal-brand']) }}>

    @if($theme->logoUrl())
        <img
            src="{{ $theme->logoUrl() }}"
            alt="{{ $theme->institutionName() }}"
            class="portal-brand__logo"
        >
    @endif

    <div class="portal-brand__identity">

        <div class="portal-brand__institution">
            {{ $theme->institutionName() ?? 'University Name' }}
        </div>

        <div class="portal-brand__label">
            Admissions
        </div>

    </div>

</div>
