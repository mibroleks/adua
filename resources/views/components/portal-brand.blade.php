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

<div {{ $attributes->merge(['class' => 'flex items-center gap-3']) }}>
    @if($theme->logoUrl())
        <img
            src="{{ $theme->logoUrl() }}"
            alt="{{ $theme->institutionName() }}"
            class="h-12 w-auto"
        >
    @endif

    <div>
        <div class="font-bold tracking-tight theme-heading">
            {{ $theme->institutionName() ?? setting('institution.name') ?? 'University Name' }}
        </div>

        <div class="text-[10px] font-semibold uppercase tracking-[0.18em] theme-muted">
            Admissions
        </div>
    </div>
</div>
